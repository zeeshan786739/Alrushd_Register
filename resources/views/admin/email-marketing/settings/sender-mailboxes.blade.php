<div class="em-panel em-settings-panel mt-24" id="sender-mailboxes">
    <div class="em-panel__head">
        <div>
            <h2 class="em-panel__title">Sender mailboxes</h2>
            <p class="em-panel__desc">Choose which verified addresses can send mail and optionally connect each address to its own inbox.</p>
        </div>
        <span class="badge bg-primary-focus text-primary-main">{{ $senderMailboxes->count() }} added</span>
    </div>

    <div class="p-24">
        <div class="alert alert-info border-0 radius-8 mb-16">
            Authenticate each address or its domain in the connected SendGrid account first, then mark it verified here. IMAP credentials are only required when you also want to receive that address's inbox in this application.
        </div>
        @forelse($senderMailboxes as $sender)
            <details class="em-form-block em-form-block--collapsible mb-16" @if($errors->any() && (int) old('sender_mailbox_id') === $sender->id) open @endif>
                <summary class="em-form-block__summary">
                    <span class="em-form-block__head em-form-block__head--inline">
                        <span class="em-form-block__icon"><iconify-icon icon="solar:letter-linear"></iconify-icon></span>
                        <span>
                            <span class="em-form-block__title">{{ $sender->name ?: $sender->email }}</span>
                            <span class="em-form-block__desc mb-0">{{ $sender->email }}</span>
                        </span>
                    </span>
                    <span class="d-flex align-items-center gap-8 ms-auto me-12">
                        @if($sender->is_default)<span class="badge bg-primary-focus text-primary-main">Default</span>@endif
                        <span class="badge {{ $sender->is_verified ? 'bg-success-focus text-success-main' : 'bg-warning-focus text-warning-main' }}">{{ $sender->is_verified ? 'Verified' : 'Pending' }}</span>
                        @if($sender->last_sync_status === 'success')
                            <span class="badge bg-success-focus text-success-main">Inbox connected</span>
                        @elseif($sender->last_sync_status === 'failed')
                            <span class="badge bg-danger-focus text-danger-main">Inbox connection failed</span>
                        @elseif($sender->isImapConfigured())
                            <span class="badge bg-warning-focus text-warning-main">Inbox configured — test required</span>
                        @else
                            <span class="badge bg-neutral-200 text-secondary-light">Sending only</span>
                        @endif
                    </span>
                    <iconify-icon icon="solar:alt-arrow-down-linear" class="em-form-block__chevron"></iconify-icon>
                </summary>
                <div class="em-form-block__collapse-body">
                    @if($sender->last_sync_status === 'failed')
                        <div class="alert alert-danger radius-8 mb-16">
                            <strong>Last inbox connection failed.</strong>
                            {{ $sender->last_sync_error ?: 'Check the IMAP username and mailbox password.' }}
                        </div>
                    @endif
                    <form method="POST" action="{{ route('admin.email.mailbox.senders.update', $sender) }}">
                        @csrf @method('PUT')
                        <input type="hidden" name="sender_mailbox_id" value="{{ $sender->id }}">
                        @include('admin.email-marketing.settings.sender-mailbox-fields', ['sender' => $sender, 'prefix' => 'sender_'.$sender->id])
                        <div class="d-flex justify-content-end mt-20">
                            <button class="btn btn-primary-600 radius-8" type="submit">Save mailbox</button>
                        </div>
                    </form>
                    @unless($sender->is_default)
                        <form method="POST" action="{{ route('admin.email.mailbox.senders.destroy', $sender) }}" class="mt-12 text-end" onsubmit="return confirm('Remove this sender mailbox?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-outline-danger-600 radius-8" type="submit">Remove mailbox</button>
                        </form>
                    @endunless
                </div>
            </details>
        @empty
            <div class="alert alert-info">No sender mailboxes have been added yet.</div>
        @endforelse

        <details class="em-form-block em-form-block--collapsible" @if($errors->any() && ! old('sender_mailbox_id')) open @endif>
            <summary class="em-form-block__summary">
                <span class="em-form-block__head em-form-block__head--inline">
                    <span class="em-form-block__icon"><iconify-icon icon="solar:add-circle-linear"></iconify-icon></span>
                    <span><span class="em-form-block__title">Add sender mailbox</span><span class="em-form-block__desc mb-0">Add a verified sender, with optional inbox access.</span></span>
                </span>
                <iconify-icon icon="solar:alt-arrow-down-linear" class="em-form-block__chevron"></iconify-icon>
            </summary>
            <div class="em-form-block__collapse-body">
                <form method="POST" action="{{ route('admin.email.mailbox.senders.store') }}">
                    @csrf
                    @include('admin.email-marketing.settings.sender-mailbox-fields', ['sender' => null, 'prefix' => 'new_sender'])
                    <div class="d-flex justify-content-end mt-20">
                        <button class="btn btn-primary-600 radius-8" type="submit"><iconify-icon icon="solar:add-circle-linear"></iconify-icon> Add mailbox</button>
                    </div>
                </form>
            </div>
        </details>
    </div>
</div>
