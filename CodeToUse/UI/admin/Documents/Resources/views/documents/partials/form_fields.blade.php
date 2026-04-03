<div class="row">
  <div class="col-md-6 mb-3">
    <label class="form-label">{{ __('Title') }}</label>
    <input type="text" class="form-control" name="title" value="{{ old('title', $document->title ?? '') }}" required>
  </div>

  <div class="col-md-3 mb-3">
    <label class="form-label">{{ __('Type') }}</label>
    <select class="form-select" name="type">
      <option value="general" @selected(old('type', $document->type ?? 'general') === 'general')>{{ __('General') }}</option>
      <option value="swms" @selected(old('type', $document->type ?? '') === 'swms')>{{ __('SWMS') }}</option>
    </select>
  </div>

  <div class="col-md-3 mb-3">
    <label class="form-label">{{ __('Status') }}</label>
    <select class="form-select" name="status">
      @php $st = old('status', $document->status ?? 'draft'); @endphp
      <option value="draft" @selected($st==='draft')>{{ __('Draft') }}</option>
      <option value="review" @selected($st==='review')>{{ __('Review') }}</option>
      <option value="approved" @selected($st==='approved')>{{ __('Approved') }}</option>
      <option value="archived" @selected($st==='archived')>{{ __('Archived') }}</option>
    </select>
  </div>

  <div class="col-md-4 mb-3">
    <label class="form-label">{{ __('Category') }}</label>
    <input type="text" class="form-control" name="category" value="{{ old('category', $document->category ?? '') }}" placeholder="{{ __('e.g., SWMS, Form, Policy') }}">
  </div>

  <div class="col-md-4 mb-3">
    <label class="form-label">{{ __('Subcategory') }}</label>
    <input type="text" class="form-control" name="subcategory" value="{{ old('subcategory', $document->subcategory ?? '') }}" placeholder="{{ __('e.g., Electrical • Installations') }}">
  </div>

  <div class="col-md-4 mb-3">
    <label class="form-label">{{ __('Template Slug') }}</label>
    <input type="text" class="form-control" name="template_slug" value="{{ old('template_slug', $document->template_slug ?? '') }}" placeholder="{{ __('Optional') }}">
  </div>

  <div class="col-md-3 mb-3">
    <label class="form-label">{{ __('Trade') }}</label>
    <input type="text" class="form-control" name="trade" value="{{ old('trade', $document->trade ?? '') }}" placeholder="{{ __('e.g., plumber') }}">
  </div>

  <div class="col-md-3 mb-3">
    <label class="form-label">{{ __('Role') }}</label>
    <input type="text" class="form-control" name="role" value="{{ old('role', $document->role ?? '') }}" placeholder="{{ __('e.g., supervisor') }}">
  </div>

  <div class="col-md-3 mb-3">
    <label class="form-label">{{ __('Effective') }}</label>
    <input type="date" class="form-control" name="effective_at" value="{{ old('effective_at', isset($document) && $document->effective_at ? $document->effective_at->format('Y-m-d') : '') }}">
  </div>

  <div class="col-md-3 mb-3">
    <label class="form-label">{{ __('Review') }}</label>
    <input type="date" class="form-control" name="review_at" value="{{ old('review_at', isset($document) && $document->review_at ? $document->review_at->format('Y-m-d') : '') }}">
  </div>
</div>

<div class="mb-3">
  <label class="form-label">{{ __('Body (Markdown)') }}</label>
  <textarea class="form-control" name="body_markdown" rows="14">{{ old('body_markdown', $document->body_markdown ?? '') }}</textarea>
</div>
