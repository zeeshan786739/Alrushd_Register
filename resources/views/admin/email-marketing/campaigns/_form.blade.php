@php $c = $campaign; $filters = $c->recipient_filters ?? []; @endphp
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label" for="name">Campaign name</label>
        <input id="name" type="text" name="name" class="form-control radius-8" value="{{ old('name', $c->name ?? '') }}" required>
    </div>
    <div class="col-md-6">
        <label class="form-label" for="subject">Subject</label>
        <input id="subject" type="text" name="subject" class="form-control radius-8" value="{{ old('subject', $c->subject ?? '') }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label" for="from_name">From name</label>
        <input id="from_name" type="text" name="from_name" class="form-control radius-8" value="{{ old('from_name', $c->from_name ?? '') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label" for="from_email">From email</label>
        <input id="from_email" type="email" name="from_email" class="form-control radius-8" value="{{ old('from_email', $c->from_email ?? '') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label" for="template_id">Template</label>
        <select id="template_id" name="template_id" class="form-select radius-8">
            <option value="">None</option>
            @foreach($templates as $template)
                <option value="{{ $template->id }}" @selected(old('template_id', $c->template_id ?? '')==$template->id)>{{ $template->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label" for="recipient_source">Recipient source</label>
        <select id="recipient_source" name="recipient_source" class="form-select radius-8" required>
            @foreach(['leads'=>'All CRM Leads','customers'=>'All CRM Customers','form_entries'=>'Form Entries','manual'=>'Manual list'] as $value=>$label)
                <option value="{{ $value }}" @selected(old('recipient_source', $c->recipient_source ?? 'manual')==$value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label" for="lead_status">Lead status filter (optional)</label>
        <input id="lead_status" type="text" name="lead_status" class="form-control radius-8" value="{{ old('lead_status', $filters['lead_status'] ?? '') }}" placeholder="e.g. new">
    </div>
    <div class="col-12">
        <label class="form-label" for="manual_emails">Manual emails (one per line)</label>
        <textarea id="manual_emails" name="manual_emails" class="form-control radius-8" rows="4">{{ old('manual_emails', $filters['manual_emails'] ?? '') }}</textarea>
    </div>
    <div class="col-12">
        <label class="form-label" for="body_html">HTML body</label>
        <textarea id="body_html" name="body_html" class="form-control radius-8" rows="12" required>{{ old('body_html', $c->body_html ?? '') }}</textarea>
        <div class="form-text">Placeholders: {{ '{{name}}' }}, {{ '{{email}}' }}, {{ '{{company}}' }}, {{ '{{unsubscribe_url}}' }}</div>
    </div>
    <div class="col-12">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="tracking_enabled" value="1" id="tracking_enabled" @checked(old('tracking_enabled', $c->tracking_enabled ?? true))>
            <label class="form-check-label" for="tracking_enabled">Enable open/click tracking</label>
        </div>
    </div>
</div>
