@php
    $mentionAdmins = ($admins ?? collect())->map(fn ($admin) => [
        'id' => (int) $admin->id,
        'name' => $admin->name,
        'initials' => \App\Support\UserManagementHelper::initials($admin->name),
    ])->values();
@endphp
<form method="POST"
      action="{{ route('admin.crm.leads.notes.store', $lead) }}"
      class="crm-ticket-comment-editor"
      data-crm-comment-form
      data-lead-id="{{ $lead->id }}"
      data-mention-admins='@json($mentionAdmins)'>
    @csrf
    <div class="crm-ticket-comment-editor__composer">
        <span class="crm-ticket-comment-editor__avatar" aria-hidden="true">{{ \App\Support\UserManagementHelper::initials(auth('admin')->user()?->name ?? 'You') }}</span>
        <div class="crm-ticket-comment-editor__field">
            <div class="crm-ticket-comment-editor__toolbar" role="toolbar" aria-label="Comment formatting">
                <button type="button" class="crm-ticket-comment-editor__tool" data-crm-comment-cmd="bold" title="Bold">
                    <iconify-icon icon="solar:text-bold-linear"></iconify-icon>
                </button>
                <button type="button" class="crm-ticket-comment-editor__tool" data-crm-comment-cmd="italic" title="Italic">
                    <iconify-icon icon="solar:text-italic-linear"></iconify-icon>
                </button>
                <button type="button" class="crm-ticket-comment-editor__tool" data-crm-comment-cmd="insertUnorderedList" title="Bullet list">
                    <iconify-icon icon="solar:list-linear"></iconify-icon>
                </button>
                <button type="button" class="crm-ticket-comment-editor__tool" data-crm-comment-cmd="mention" title="Mention someone">
                    <iconify-icon icon="solar:mention-circle-linear"></iconify-icon>
                </button>
            </div>
            <div class="crm-ticket-comment-editor__input"
                 contenteditable="true"
                 role="textbox"
                 aria-multiline="true"
                 aria-label="Add a comment"
                 data-crm-comment-input
                 data-placeholder="Add a comment… Use @ to mention a teammate"></div>
            <input type="hidden" name="note" value="" data-crm-comment-hidden>
            <div class="crm-ticket-comment-editor__mention-menu" data-crm-mention-menu hidden></div>
            <div class="crm-ticket-comment-editor__actions">
                <span class="crm-ticket-comment-editor__hint">Pro tip: type <kbd>@</kbd> to mention someone</span>
                <button type="submit" class="crm-ticket-comment-editor__submit" data-crm-comment-submit>Comment</button>
            </div>
        </div>
    </div>
</form>
