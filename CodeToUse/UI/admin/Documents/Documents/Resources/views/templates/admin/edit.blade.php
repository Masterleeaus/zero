@extends('layouts.app')

@section('content')
<div class="container">
  <h3>Edit Template</h3>

  <form method="POST" action="{{ route('documents.templates.admin.update', $template->id) }}">
    @csrf

    <div class="mb-3">
      <label class="form-label">Title</label>
      <input name="title" class="form-control" value="{{ old('title', $template->title) }}" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Category</label>
      <input name="category" class="form-control" value="{{ old('category', $template->category) }}">
    </div>

    <div class="mb-3">
      <label class="form-label">Trade</label>
      <input name="trade" class="form-control" value="{{ old('trade', $template->trade) }}">
    </div>

    <div class="mb-3">
      <label class="form-label">Role Key</label>
      <input name="role_key" class="form-control" value="{{ old('role_key', $template->role_key) }}">
    </div>

    <div class="mb-3">
      <label class="form-label">Tags</label>
      <input name="tags" class="form-control" value="{{ old('tags', $template->tags) }}">
    </div>

    <div class="mb-3">
      <label class="form-label">Description</label>
      <textarea name="description" class="form-control" rows="2">{{ old('description', $template->description) }}</textarea>
    </div>

    <div class="mb-3">
      <label class="form-label">Content</label>
      <textarea name="content" class="form-control" rows="10">{{ old('content', $template->content) }}</textarea>
    </div>

    <button class="btn btn-primary">Save</button>
    <a href="{{ route('documents.templates.admin.index') }}" class="btn btn-link">Back</a>
  </form>
</div>
@endsection
