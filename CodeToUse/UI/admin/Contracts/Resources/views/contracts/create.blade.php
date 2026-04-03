@extends('layouts.app')
@section('content')
<div class="container py-4">
  @include('contracts::partials.brand')
  <h3>Create Contract</h3>
  <form method="post" action="{{ route('contracts.store') }}">
    @csrf
    <div class="row">
      <div class="col-md-6 mb-3">
        <label class="form-label">Title</label>
        <input name="title" class="form-control" required>
      </div>
      <div class="col-md-3 mb-3">
        <label class="form-label">Client ID (optional)</label>
        <input name="client_id" type="number" class="form-control">
      </div>
      <div class="col-md-3 mb-3">
        <label class="form-label">Effective Date</label>
        <input name="effective_date" type="date" class="form-control">
      </div>
      <div class="col-md-3 mb-3">
        <label class="form-label">Expiry Date</label>
        <input name="expiry_date" type="date" class="form-control">
      </div>
    </div>
    <div class="mb-3">
      <label class="form-label">Notes</label>
      <textarea name="notes" class="form-control" rows="2"></textarea>
    </div>
    <div class="mb-3">
      <label class="form-label">Body (HTML allowed)</label>
      <textarea name="body_html" class="form-control" rows="10" placeholder="<h2>Agreement</h2>..."></textarea>
    </div>

    <h5>Signers</h5>
    <div id="signers"></div>
    <button type="button" class="btn btn-sm btn-outline-primary mb-3" id="add-signer">Add Signer</button>

    <button class="btn btn-success">Save Draft</button>
  </form>
</div>

<script>
(function(){
  const wrap = document.getElementById('signers');
  const add = document.getElementById('add-signer');
  function row(i){
    return `<div class="row g-2 mb-2">
      <div class="col-md-4"><input class="form-control" name="signers[${i}][name]" placeholder="Signer Name" required></div>
      <div class="col-md-4"><input class="form-control" name="signers[${i}][email]" type="email" placeholder="email@example.com" required></div>
      <div class="col-md-3"><input class="form-control" name="signers[${i}][role]" placeholder="signer/witness/approver"></div>
    </div>`;
  }
  let i=0; add.addEventListener('click', ()=>{ wrap.insertAdjacentHTML('beforeend', row(i++)); });
  add.click();
})();
</script>
@endsection
