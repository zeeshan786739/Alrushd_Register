@php
    /** @var list<\App\Support\CrmEmailDeliverySummary> $emailHistory */
    $emailHistory = $emailHistory ?? [];
@endphp
@if($emailHistory !== [])
    <div class="card radius-12 shadow-2 border-0 mb-20">
        <div class="card-body p-24">
            <h6 class="crm-section-title"><iconify-icon icon="solar:letter-linear"></iconify-icon> Recent emails</h6>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Recipient</th>
                            <th>Subject</th>
                            <th>Sent</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($emailHistory as $item)
                            <tr>
                                <td class="text-truncate" style="max-width:160px">{{ $item->message->to ?: '—' }}</td>
                                <td class="text-truncate" style="max-width:220px">{{ $item->message->subject ?: '(no subject)' }}</td>
                                <td class="text-nowrap">{{ optional($item->message->sent_at)->format('M j, Y g:i A') ?? '—' }}</td>
                                <td>
                                    <span class="crm-status-pill crm-status-pill--tone-{{ $item->statusTone() }}">
                                        {{ $item->statusLabel() }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif
