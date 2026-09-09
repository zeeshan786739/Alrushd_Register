<form method="GET" class="crm-filter-workspace em-inbox-finder">
    @if(($senderMailboxes ?? collect())->isNotEmpty())
        <div class="em-inbox-finder__mailbox">
            <label class="em-inbox-finder__label" for="em-inbox-mailbox">Mailbox</label>
            <select id="em-inbox-mailbox" name="sender_mailbox_id" class="form-select radius-8" aria-label="Filter by mailbox" onchange="this.form.submit()">
                <option value="">All mailboxes</option>
                @foreach($senderMailboxes as $sender)
                    <option value="{{ $sender->id }}" @selected(($selectedSenderMailbox?->id ?? null) === $sender->id)>
                        {{ $sender->email }}{{ $sender->isImapConfigured() ? '' : ' (sending only)' }}
                    </option>
                @endforeach
            </select>
        </div>
    @endif

    <div class="em-inbox-finder__lookup">
        <div class="crm-ai-search">
            <div class="crm-ai-search__head">
                <span class="crm-ai-search__badge">
                    <iconify-icon icon="solar:magnifer-linear" aria-hidden="true"></iconify-icon>
                    Find
                </span>
                <span class="crm-ai-search__hint">Search subject, sender, or recipient</span>
            </div>
            <div class="crm-smart-search__shell crm-ai-search__shell">
                <span class="crm-ai-search__icon" aria-hidden="true">
                    <iconify-icon icon="solar:magnifer-linear"></iconify-icon>
                </span>
                <label class="visually-hidden" for="em-inbox-search">Search emails</label>
                <input type="search"
                       id="em-inbox-search"
                       name="search"
                       class="crm-smart-search__input crm-ai-search__input"
                       value="{{ request('search') }}"
                       placeholder="Try: parent name · invoice · admission"
                       autocomplete="off"
                       aria-label="Search emails">
                <button type="submit" class="crm-smart-search__go crm-ai-search__go">
                    <iconify-icon icon="solar:magnifer-linear"></iconify-icon>
                    <span>Search</span>
                </button>
            </div>
        </div>
    </div>

    @if(request()->filled('search'))
        <div class="crm-lead-finder__active">
            <span class="crm-lead-finder__active-label">Active filters</span>
            <div class="crm-lead-finder__active-list">
                <a href="{{ request()->url() }}" class="crm-active-filter">
                    <span>Search:</span>
                    <strong>{{ request('search') }}</strong>
                    <iconify-icon icon="solar:close-circle-linear" aria-hidden="true"></iconify-icon>
                </a>
            </div>
        </div>
    @endif
</form>
