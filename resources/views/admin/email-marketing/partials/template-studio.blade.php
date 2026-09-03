@php
    $studioId = $studioId ?? 'body_html';
    $studioValue = $studioValue ?? old('body_html', $bodyHtml ?? '');
    $studioValue = app(\App\Services\EmailMarketing\HtmlSanitizer::class)->sanitize($studioValue);
    $showGallery = $showGallery ?? false; $templates = $templates ?? collect();
    $selectedTemplateId = old('template_id', $selectedTemplateId ?? '');
@endphp
<div class="em-template-studio em-builder" data-template-studio data-image-upload-url="{{ route('admin.email.templates.image-upload') }}">
 @if($showGallery)
 <input type="hidden" name="template_id" value="{{ $selectedTemplateId }}" data-template-id-input>
 <section class="em-builder__templates"><div class="em-builder__section-head"><strong>Choose a starting point</strong><span>Start blank or customize a saved design.</span></div><div class="em-template-gallery">
  <button type="button" class="em-template-card" data-template-pick=""><iconify-icon icon="solar:document-linear"></iconify-icon><span>Blank email</span><small>Start from scratch</small></button>
  @foreach($templates as $template)<button type="button" class="em-template-card" data-template-pick="{{ $template->id }}" data-template-url="{{ route('admin.email.templates.body',$template) }}"><iconify-icon icon="solar:clipboard-list-linear"></iconify-icon><span>{{ $template->name }}</span><small>{{ $template->category ?: 'Saved template' }}</small></button>@endforeach
 </div></section>
 @endif
 <div class="em-builder__shell">
  <aside class="em-builder__blocks"><div class="em-builder__aside-head"><strong>Content blocks</strong><span>Click to insert</span></div>
   @foreach(['heading'=>'text-bold','paragraph'=>'text','button'=>'cursor-square','image'=>'gallery-add','columns'=>'widget-4','divider'=>'minus-square','spacer'=>'sort-vertical','footer'=>'letter'] as $block=>$icon)
    <button type="button" data-insert-block="{{ $block }}"><iconify-icon icon="solar:{{ $icon }}-linear"></iconify-icon><span>{{ ucfirst($block === 'paragraph' ? 'text' : $block) }}</span></button>
   @endforeach
  </aside>
  <section class="em-builder__main">
   <div class="em-builder__topbar"><div class="em-builder__formatbar" role="toolbar">
    <select data-format-block><option value="p">Paragraph</option><option value="h1">Heading 1</option><option value="h2">Heading 2</option><option value="h3">Heading 3</option></select>
    <button type="button" data-command="bold" title="Bold"><strong>B</strong></button><button type="button" data-command="italic" title="Italic"><em>I</em></button><button type="button" data-command="underline" title="Underline"><u>U</u></button>
    <label class="em-builder__color" title="Text color"><iconify-icon icon="solar:palette-linear"></iconify-icon><input type="color" value="#0f274a" data-text-color></label>
    <label class="em-builder__color" title="Highlight"><iconify-icon icon="solar:highlighter-linear"></iconify-icon><input type="color" value="#fff3bf" data-bg-color></label>
    <button type="button" data-command="justifyLeft" title="Align left"><iconify-icon icon="solar:align-left-linear"></iconify-icon></button><button type="button" data-command="justifyCenter" title="Center"><iconify-icon icon="solar:align-horizontal-center-linear"></iconify-icon></button><button type="button" data-command="insertUnorderedList" title="List"><iconify-icon icon="solar:list-linear"></iconify-icon></button><button type="button" data-action="link" title="Link"><iconify-icon icon="solar:link-linear"></iconify-icon></button>
   </div><div class="em-builder__view-actions"><button type="button" class="is-active" data-preview-size="desktop"><iconify-icon icon="solar:monitor-linear"></iconify-icon></button><button type="button" data-preview-size="mobile"><iconify-icon icon="solar:smartphone-linear"></iconify-icon></button><button type="button" data-toggle-source><iconify-icon icon="solar:code-linear"></iconify-icon> HTML</button></div></div>
   <div class="em-builder__mergebar"><span>Personalize:</span>@foreach(['name','email','company','unsubscribe_url'] as $tag)<button type="button" data-insert-tag="{{ $tag }}">&#123;&#123;{{ $tag }}&#125;&#125;</button>@endforeach</div>
   <div class="em-builder__workspace"><div class="em-builder__canvas-wrap"><div class="em-builder__preview-label"><strong>Email content</strong><span>Click anywhere to edit</span></div><div class="em-builder__canvas" contenteditable="true" data-visual-editor role="textbox" aria-label="Visual email editor"></div><textarea id="{{ $studioId }}" name="body_html" class="em-builder__source" rows="22" required data-studio-editor>{{ $studioValue }}</textarea></div><div class="em-builder__preview-pane"><div class="em-builder__preview-label"><strong>Live preview</strong><span>Sample recipient</span></div><div class="em-builder__preview-viewport" data-preview-viewport><div data-studio-preview></div></div></div></div>
  </section>
 </div>
 <dialog class="em-builder-modal" data-builder-modal>
  <div class="em-builder-modal__card" data-modal-form role="document">
   <div class="em-builder-modal__head"><div><span class="em-builder-modal__eyebrow" data-modal-eyebrow>Content block</span><h3 data-modal-title>Configure block</h3></div><button type="button" class="em-builder-modal__close" data-modal-close aria-label="Close dialog" onclick="this.closest('dialog').close(); return false;"><span aria-hidden="true">&times;</span></button></div>
   <div class="em-builder-modal__body" data-modal-body></div>
   <p class="em-builder-modal__error" data-modal-error hidden></p>
   <div class="em-builder-modal__actions"><button type="button" class="btn btn-outline-neutral-500" data-modal-close onclick="this.closest('dialog').close(); return false;">Cancel</button><button type="button" class="btn btn-primary-600" data-modal-submit>Insert</button></div>
  </div>
 </dialog>
</div>
