@extends('inventory::layout')
@section('content')
<h2>Stocktake #{{ $stocktake->id }}</h2>
<form method="POST" action="{{ route('inventory.st.addLine',$stocktake) }}">
@csrf
<label>Item
<select name="item_id">
@foreach($items as $it)<option value="{{ $it->id }}">{{ $it->name }}</option>@endforeach
</select></label>
<label>Counted Qty <input type="number" name="counted_qty" value="0"></label>
<button>Add Line</button>
</form>

<p>
<a href="{{ route('inventory.st.export',$stocktake) }}">Export CSV</a>
</p>
<form method="POST" action="{{ route('inventory.st.import',$stocktake) }}" enctype="multipart/form-data">
@csrf
<input type="file" name="csv" accept=".csv">
<button>Import CSV</button>
</form>

<table border="1" cellspacing="0" cellpadding="6">
<tr><th>Item</th><th>Counted</th></tr>
@foreach($stocktake->lines as $ln)
<tr><td>{{ optional($ln->item)->name }} (#{{ $ln->item_id }})</td><td>{{ $ln->counted_qty }}</td></tr>
@endforeach
</table>

<form method="POST" action="{{ route('inventory.st.finalize',$stocktake) }}">
@csrf
<button onclick="return confirm('Finalize stocktake?')">Finalize</button>
</form>
@endsection
