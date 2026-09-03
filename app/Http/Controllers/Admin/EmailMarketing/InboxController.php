<?php

namespace App\Http\Controllers\Admin\EmailMarketing;

use App\Http\Controllers\Controller;
use App\Models\EmailMarketing\Message;
use App\Models\EmailMarketing\MessageAttachment;
use App\Models\EmailMarketing\SenderMailbox;
use App\Services\EmailMarketing\AttachmentService;
use App\Services\EmailMarketing\ComposeService;
use App\Services\EmailMarketing\HtmlSanitizer;
use App\Services\EmailMarketing\InboxSyncService;
use App\Services\EmailMarketing\MailConfigResolver;
use App\Support\OrganizationContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InboxController extends Controller
{
    public function __construct(
        private ComposeService $compose,
        private InboxSyncService $sync,
        private AttachmentService $attachments,
        private HtmlSanitizer $sanitizer,
    ) {
        $this->middleware('permission:view inbox')->only(['inbox', 'show']);
        $this->middleware('permission:view sent emails')->only(['sent']);
        $this->middleware('permission:manage drafts')->only(['drafts', 'destroy']);
        $this->middleware('permission:star emails')->only(['starred', 'toggleStar']);
        $this->middleware('permission:compose emails')->only(['compose']);
        $this->middleware('permission:send emails')->only(['send', 'reply', 'forward']);
        $this->middleware('permission:sync inbox')->only(['sync']);
        $this->middleware('permission:download email attachments')->only(['downloadAttachment']);
    }

    public function inbox(Request $request): View
    {
        return $this->folderView($request, 'inbox', 'Inbox');
    }

    public function sent(Request $request): View
    {
        return $this->folderView($request, 'sent', 'Sent');
    }

    public function drafts(Request $request): View
    {
        return $this->folderView($request, 'draft', 'Drafts');
    }

    public function starred(Request $request): View
    {
        [$senderMailboxes, $selectedSenderMailbox] = $this->senderMailboxContext($request);
        $messages = Message::forCurrentOrganization()
            ->starred()
            ->when($selectedSenderMailbox, fn ($query) => $query->where('sender_mailbox_id', $selectedSenderMailbox->id))
            ->with('senderMailbox')
            ->withCount('attachments')
            ->when($request->search, fn ($q, $s) => $q->where(function ($inner) use ($s) {
                $inner->where('subject', 'like', "%{$s}%")
                    ->orWhere('from_email', 'like', "%{$s}%")
                    ->orWhere('to', 'like', "%{$s}%");
            }))
            ->orderByDesc('updated_at')
            ->paginate(20)
            ->withQueryString();

        $counts = $this->folderCounts();

        return view('admin.email-marketing.inbox.index', [
            'messages' => $messages,
            'folder' => 'starred',
            'title' => 'Starred',
            'counts' => $counts,
            'selected' => null,
            'senderMailboxes' => $senderMailboxes,
            'selectedSenderMailbox' => $selectedSenderMailbox,
            'imapClientAvailable' => class_exists(\Webklex\PHPIMAP\ClientManager::class),
        ]);
    }

    public function show(Message $emMessage): View
    {
        $this->authorize('view', $emMessage);

        if ($emMessage->folder === 'inbox' && ! $emMessage->is_read) {
            $emMessage->update(['is_read' => true]);
        }

        $emMessage->load(['attachments', 'lead', 'customer', 'quotation', 'invoice', 'parent']);
        $counts = $this->folderCounts();

        $replySubject = $emMessage->subject ?: '';
        if ($replySubject !== '' && ! str_starts_with(strtolower($replySubject), 're:')) {
            $replySubject = 'Re: '.$replySubject;
        }

        $providerEvents = \App\Models\EmailMarketing\ProviderEvent::query()
            ->where('em_message_id', $emMessage->id)
            ->where('organization_id', $emMessage->organization_id)
            ->orderBy('occurred_at')
            ->orderBy('id')
            ->get();

        return view('admin.email-marketing.inbox.show', [
            'message' => $emMessage,
            'counts' => $counts,
            'folder' => $emMessage->folder,
            'sanitizedBody' => $this->sanitizer->sanitize($emMessage->body_html),
            'replySubject' => $replySubject,
            'replyTo' => $emMessage->direction === 'inbound'
                ? ($emMessage->from_email ?: $emMessage->to)
                : $emMessage->to,
            'providerEvents' => $providerEvents,
        ]);
    }

    public function compose(Request $request): View
    {
        $this->authorize('compose', Message::class);

        $prefill = [
            'to' => $request->get('to'),
            'subject' => $request->get('subject'),
            'lead_id' => $request->get('lead_id'),
            'customer_id' => $request->get('customer_id'),
            'body_html' => '',
            'sender_mailbox_id' => $request->integer('sender_mailbox_id') ?: null,
        ];

        if ($request->filled('draft_id')) {
            $draft = Message::forCurrentOrganization()->draft()->findOrFail($request->draft_id);
            $prefill = [
                'to' => $draft->to,
                'cc' => $draft->cc,
                'bcc' => $draft->bcc,
                'subject' => $draft->subject,
                'body_html' => $draft->body_html,
                'lead_id' => $draft->lead_id,
                'customer_id' => $draft->customer_id,
                'draft_id' => $draft->id,
                'sender_mailbox_id' => $draft->sender_mailbox_id,
            ];
        }

        $senderMailboxes = SenderMailbox::forCurrentOrganization()->available()
            ->orderByDesc('is_default')->orderBy('email')->get();

        return view('admin.email-marketing.inbox.compose', [
            'prefill' => $prefill,
            'counts' => $this->folderCounts(),
            'senderMailboxes' => $senderMailboxes,
        ]);
    }

    public function send(Request $request): RedirectResponse
    {
        $this->authorize('send', Message::class);

        $validated = $request->validate([
            'to' => 'required|string',
            'cc' => 'nullable|string',
            'bcc' => 'nullable|string',
            'subject' => 'required|string|max:255',
            'body_html' => 'nullable|string',
            'lead_id' => 'nullable|integer',
            'customer_id' => 'nullable|integer',
            'draft_id' => 'nullable|integer',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240',
            'sender_mailbox_id' => $this->senderMailboxRule(),
        ]);

        $orgId = OrganizationContext::idOrFail();
        $files = $request->file('attachments', []) ?: [];

        try {
            $message = $this->compose->send($orgId, array_merge($validated, [
                'created_by' => auth('admin')->id(),
            ]), $files);

            if (! empty($validated['draft_id'])) {
                Message::forCurrentOrganization()->draft()->whereKey($validated['draft_id'])->delete();
            }

            return redirect()->route('admin.email.sent')
                ->with('success', 'Email sent successfully.');
        } catch (\Throwable $e) {
            report($e);

            return back()->withInput()->withErrors([
                'send' => 'Send failed. Please verify mailbox settings and try again.',
            ]);
        }
    }

    public function saveDraft(Request $request): RedirectResponse
    {
        $this->authorize('compose', Message::class);

        $validated = $request->validate([
            'to' => 'nullable|string',
            'cc' => 'nullable|string',
            'bcc' => 'nullable|string',
            'subject' => 'nullable|string|max:255',
            'body_html' => 'nullable|string',
            'lead_id' => 'nullable|integer',
            'customer_id' => 'nullable|integer',
            'draft_id' => 'nullable|integer',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240',
            'sender_mailbox_id' => $this->senderMailboxRule(false),
        ]);

        $existing = null;
        if (! empty($validated['draft_id'])) {
            $existing = Message::forCurrentOrganization()->draft()->findOrFail($validated['draft_id']);
        }

        $draft = $this->compose->saveDraft(
            OrganizationContext::idOrFail(),
            array_merge($validated, ['created_by' => auth('admin')->id()]),
            $request->file('attachments', []) ?: [],
            $existing
        );

        return redirect()->route('admin.email.compose', ['draft_id' => $draft->id])
            ->with('success', 'Draft saved.');
    }

    public function reply(Request $request, Message $emMessage): RedirectResponse
    {
        $this->authorize('view', $emMessage);
        $this->authorize('send', Message::class);

        $validated = $request->validate([
            'body_html' => 'required|string',
            'subject' => 'nullable|string|max:255',
        ]);

        $subject = $validated['subject'] ?? $emMessage->subject;
        if ($subject && ! str_starts_with(strtolower($subject), 're:')) {
            $subject = 'Re: '.$subject;
        }

        try {
            $threadId = $emMessage->thread_id ?: $emMessage->correlation_uuid;
            $this->compose->send(OrganizationContext::idOrFail(), [
                'to' => $emMessage->from_email ?: $emMessage->to,
                'subject' => $subject,
                'body_html' => $validated['body_html'].'<hr>'.$this->sanitizer->sanitize($emMessage->body_html),
                'parent_id' => $emMessage->id,
                'thread_id' => $threadId,
                'lead_id' => $emMessage->lead_id,
                'customer_id' => $emMessage->customer_id,
                'quotation_id' => $emMessage->quotation_id,
                'invoice_id' => $emMessage->invoice_id,
                'created_by' => auth('admin')->id(),
                'sender_mailbox_id' => $emMessage->sender_mailbox_id,
            ]);

            return redirect()->route('admin.email.sent')->with('success', 'Reply sent.');
        } catch (\Throwable $e) {
            return back()->withErrors(['send' => $e->getMessage()]);
        }
    }

    public function forward(Request $request, Message $emMessage): RedirectResponse
    {
        $this->authorize('view', $emMessage);
        $this->authorize('send', Message::class);

        $validated = $request->validate([
            'to' => 'required|string',
            'body_html' => 'nullable|string',
            'subject' => 'nullable|string|max:255',
        ]);

        $subject = $validated['subject'] ?? $emMessage->subject;
        if ($subject && ! str_starts_with(strtolower($subject), 'fwd:')) {
            $subject = 'Fwd: '.$subject;
        }

        $quoted = '<p>---------- Forwarded message ----------</p>'
            .'<p>From: '.e($emMessage->from_name.' <'.$emMessage->from_email.'>').'</p>'
            .'<p>Subject: '.e($emMessage->subject).'</p>'
            .$this->sanitizer->sanitize($emMessage->body_html);

        try {
            $this->compose->send(OrganizationContext::idOrFail(), [
                'to' => $validated['to'],
                'subject' => $subject,
                'body_html' => ($validated['body_html'] ?? '').$quoted,
                'parent_id' => $emMessage->id,
                'created_by' => auth('admin')->id(),
                'sender_mailbox_id' => $emMessage->sender_mailbox_id,
            ]);

            return redirect()->route('admin.email.sent')->with('success', 'Email forwarded.');
        } catch (\Throwable $e) {
            return back()->withErrors(['send' => $e->getMessage()]);
        }
    }

    public function toggleStar(Message $emMessage): JsonResponse|RedirectResponse
    {
        $this->authorize('update', $emMessage);
        $emMessage->update(['is_starred' => ! $emMessage->is_starred]);

        if (request()->expectsJson()) {
            return response()->json(['is_starred' => $emMessage->is_starred]);
        }

        return back()->with('success', $emMessage->is_starred ? 'Starred.' : 'Unstarred.');
    }

    public function markRead(Message $emMessage): JsonResponse|RedirectResponse
    {
        $this->authorize('view', $emMessage);
        $emMessage->update(['is_read' => true]);

        if (request()->expectsJson()) {
            return response()->json(['is_read' => true]);
        }

        return back()->with('success', 'Marked as read.');
    }

    public function markUnread(Message $emMessage): JsonResponse|RedirectResponse
    {
        $this->authorize('view', $emMessage);
        $emMessage->update(['is_read' => false]);

        if (request()->expectsJson()) {
            return response()->json(['is_read' => false]);
        }

        return back()->with('success', 'Marked as unread.');
    }

    public function destroy(Message $emMessage): RedirectResponse
    {
        $this->authorize('delete', $emMessage);
        $emMessage->delete();

        $route = $emMessage->folder === 'draft' ? 'admin.email.drafts' : 'admin.email.inbox';

        return redirect()->route($route)->with('success', 'Message deleted.');
    }

    public function sync(Request $request): RedirectResponse
    {
        $this->authorize('sync', Message::class);

        $senderMailboxId = $request->filled('sender_mailbox_id')
            ? SenderMailbox::forCurrentOrganization()->whereKey($request->integer('sender_mailbox_id'))->value('id')
            : null;

        if ($request->filled('sender_mailbox_id') && ! $senderMailboxId) {
            abort(404);
        }

        try {
            $result = $this->sync->syncOrganization(OrganizationContext::idOrFail(), $senderMailboxId);

            if ($result['skipped'] ?? false) {
                return back()->with('error', 'IMAP is not configured for this organization.');
            }

            return back()->with('success', 'Inbox synced. Imported '.$result['imported'].' message(s).');
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', $e->getMessage());
        }
    }

    public function downloadAttachment(Message $emMessage, MessageAttachment $attachment): StreamedResponse
    {
        $this->authorize('download', $emMessage);
        abort_unless($attachment->message_id === $emMessage->id, 404);
        abort_unless(
            (int) $attachment->organization_id === (int) $emMessage->organization_id
            && (int) $emMessage->organization_id === (int) OrganizationContext::idOrFail(),
            404
        );

        return $this->attachments->download($attachment);
    }

    private function folderView(Request $request, string $folder, string $title): View
    {
        [$senderMailboxes, $selectedSenderMailbox] = $this->senderMailboxContext($request);
        $messages = Message::forCurrentOrganization()
            ->where('folder', $folder)
            ->when($selectedSenderMailbox, fn ($query) => $query->where('sender_mailbox_id', $selectedSenderMailbox->id))
            ->with('senderMailbox')
            ->withCount('attachments')
            ->when($request->search, function ($q, $s) {
                $q->where(function ($inner) use ($s) {
                    $inner->where('subject', 'like', "%{$s}%")
                        ->orWhere('from_email', 'like', "%{$s}%")
                        ->orWhere('to', 'like', "%{$s}%");
                });
            })
            ->orderByDesc($folder === 'sent' ? 'sent_at' : 'created_at')
            ->paginate(20)
            ->withQueryString();

        $mailbox = app(MailConfigResolver::class)->forOrganization(OrganizationContext::idOrFail());

        return view('admin.email-marketing.inbox.index', [
            'messages' => $messages,
            'folder' => $folder,
            'title' => $title,
            'counts' => $this->folderCounts(),
            'selected' => null,
            'mailbox' => $mailbox,
            'senderMailboxes' => $senderMailboxes,
            'selectedSenderMailbox' => $selectedSenderMailbox,
            'imapClientAvailable' => class_exists(\Webklex\PHPIMAP\ClientManager::class),
        ]);
    }

    private function senderMailboxContext(Request $request): array
    {
        $mailboxes = SenderMailbox::forCurrentOrganization()
            ->available()
            ->orderByDesc('is_default')
            ->orderBy('email')
            ->get();

        $selected = null;
        if ($request->filled('sender_mailbox_id')) {
            $selected = $mailboxes->firstWhere('id', $request->integer('sender_mailbox_id'));
            abort_unless($selected, 404);
        }

        return [$mailboxes, $selected];
    }

    /** @return array<string, int> */
    private function folderCounts(): array
    {
        $base = Message::forCurrentOrganization();

        return [
            'inbox' => (clone $base)->inbox()->count(),
            'inbox_unread' => (clone $base)->inbox()->unread()->count(),
            'sent' => (clone $base)->sent()->count(),
            'draft' => (clone $base)->draft()->count(),
            'starred' => (clone $base)->starred()->count(),
        ];
    }

    private function senderMailboxRule(bool $required = true): array
    {
        // Legacy organizations without sender rows may still use their original
        // mailbox setting. Once sender rows exist, an available one is mandatory.
        $hasConfiguredSenders = SenderMailbox::forCurrentOrganization()->exists();

        return [
            $required && $hasConfiguredSenders ? 'required' : 'nullable',
            'integer',
            \Illuminate\Validation\Rule::exists('em_sender_mailboxes', 'id')->where(
                fn ($query) => $query
                    ->where('organization_id', OrganizationContext::idOrFail())
                    ->where('is_active', true)
                    ->where('is_verified', true)
            ),
        ];
    }
}
