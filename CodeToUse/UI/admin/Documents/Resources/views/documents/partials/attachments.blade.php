<div class="mb-3">
  <label class="form-label">{{ __('Attachments') }}</label>
  <input type="file" name="attachments[]" class="form-control" multiple>
  <small class="text-muted">{{ __('Upload PDFs, DOCX, images, etc. These will be stored against this document.') }}</small>
</div>

@if(isset($document) && $document->files && $document->files->count())
  <div class="mb-3">
    <label class="form-label">{{ __('Existing Files') }}</label>
    <ul class="list-group">
      @foreach($document->files as $file)
        <li class="list-group-item d-flex justify-content-between align-items-center">
          <span>{{ $file->original_name ?? $file->name }}</span>
          <a class="btn btn-sm btn-outline-dark" href="{{ route('documents.attachments.download', $file->id) }}">
            {{ __('Download') }}
          </a>
        </li>
      @endforeach
    </ul>
  </div>
@endif
