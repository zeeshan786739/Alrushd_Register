@php $t = $template; @endphp
<div class="row g-3">
    <div class="col-md-6"><label class="form-label" for="name">Name</label><input id="name" name="name" class="form-control radius-8" value="{{ old('name', $t->name ?? '') }}" required></div>
    <div class="col-md-6"><label class="form-label" for="subject">Subject</label><input id="subject" name="subject" class="form-control radius-8" value="{{ old('subject', $t->subject ?? '') }}"></div>
    <div class="col-md-6"><label class="form-label" for="category">Category</label><input id="category" name="category" class="form-control radius-8" value="{{ old('category', $t->category ?? '') }}"></div>
    <div class="col-md-6 d-flex align-items-end">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" @checked(old('is_active', $t->is_active ?? true))>
            <label class="form-check-label" for="is_active">Active</label>
        </div>
    </div>
    <div class="col-12"><label class="form-label" for="body_html">HTML body</label><textarea id="body_html" name="body_html" class="form-control radius-8" rows="12" required>{{ old('body_html', $t->body_html ?? '') }}</textarea></div>
    <div class="col-12"><label class="form-label" for="body_text">Plain text</label><textarea id="body_text" name="body_text" class="form-control radius-8" rows="4">{{ old('body_text', $t->body_text ?? '') }}</textarea></div>
</div>
