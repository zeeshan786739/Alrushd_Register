@php
    use App\Enums\EmailMarketing\ProviderStatus;
    $status = strtolower((string) ($status ?? 'pending'));
    $enum = ProviderStatus::tryFrom($status);
    $label = $enum?->label() ?? str_replace('_', ' ', ucfirst($status));
    // Distinguish provider-accepted from delivered.
    if (in_array($status, ['sent', 'accepted', 'processed'], true)) {
        $label = in_array($status, ['sent', 'accepted'], true) ? 'Accepted' : 'Processed';
    }
    $tone = $enum?->tone() ?? match ($status) {
        'sent', 'accepted', 'processed' => 'info',
        'failed', 'bounce', 'dropped', 'spamreport' => 'danger',
        'deferred', 'sending', 'queued', 'pending' => 'warning',
        'delivered', 'open', 'click' => 'success',
        default => 'neutral',
    };
    $class = match ($tone) {
        'success' => 'text-bg-success',
        'danger' => 'text-bg-danger',
        'warning' => 'text-bg-warning',
        'info' => 'text-bg-info',
        default => 'text-bg-secondary',
    };
@endphp
<span class="badge {{ $class }} text-capitalize">{{ $label }}</span>
