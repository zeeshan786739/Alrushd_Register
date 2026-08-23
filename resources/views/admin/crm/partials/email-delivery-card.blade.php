@php
    /** @var \App\Support\CrmEmailDeliverySummary|null $emailDelivery */
    $emailDelivery = $emailDelivery ?? null;
@endphp
@if($emailDelivery)
    <div class="card radius-12 shadow-2 border-0 mb-20">
        <div class="card-body p-24">
            <h6 class="crm-section-title"><iconify-icon icon="solar:letter-linear"></iconify-icon> Email Delivery</h6>
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="crm-lead-meta">Sent to</div>
                    <div>{{ $emailDelivery->message->to ?: '—' }}</div>
                </div>
                <div class="col-md-6">
                    <div class="crm-lead-meta">Sent</div>
                    <div>{{ optional($emailDelivery->message->sent_at)->format('M j, Y g:i A') ?? '—' }}</div>
                </div>
                <div class="col-md-6">
                    <div class="crm-lead-meta">Status</div>
                    <div>
                        <span class="crm-status-pill crm-status-pill--tone-{{ $emailDelivery->statusTone() }}">
                            {{ $emailDelivery->statusLabel() }}
                        </span>
                    </div>
                </div>
                @if($emailDelivery->message->opened_at)
                    <div class="col-md-6">
                        <div class="crm-lead-meta">Opened</div>
                        <div>{{ $emailDelivery->message->opened_at->format('M j, Y g:i A') }}</div>
                    </div>
                @endif
                @if($emailDelivery->message->clicked_at)
                    <div class="col-md-6">
                        <div class="crm-lead-meta">Clicked</div>
                        <div>{{ $emailDelivery->message->clicked_at->format('M j, Y g:i A') }}</div>
                    </div>
                @endif
                @if($emailDelivery->message->delivered_at)
                    <div class="col-md-6">
                        <div class="crm-lead-meta">Delivered</div>
                        <div>{{ $emailDelivery->message->delivered_at->format('M j, Y g:i A') }}</div>
                    </div>
                @endif
            </div>
            @php $timeline = $emailDelivery->timeline(); @endphp
            @if(count($timeline) > 1)
                <div class="mt-16">
                    <div class="crm-lead-meta mb-8">Timeline</div>
                    <ul class="list-unstyled mb-0">
                        @foreach($timeline as $row)
                            <li class="d-flex justify-content-between gap-12 py-4 border-bottom">
                                <span>{{ $row['label'] }}</span>
                                <span class="crm-lead-meta text-nowrap">{{ $row['at'] ?? '—' }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>
@endif
