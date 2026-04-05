@extends('panel.layout.app')
@section('title', __('Issue Material to Job'))

@section('content')
    <div class="py-6 max-w-xl">
        <form method="POST" action="{{ route('dashboard.inventory.stock-issue.store') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium">{{ __('Item') }}</label>
                <select name="item_id" required class="mt-1 block w-full rounded border-gray-300">
                    <option value="">-- {{ __('Select Item') }} --</option>
                    @foreach($items as $item)
                        <option value="{{ $item->id }}">{{ $item->name }} ({{ $item->sku }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium">{{ __('Warehouse') }}</label>
                <select name="warehouse_id" required class="mt-1 block w-full rounded border-gray-300">
                    <option value="">-- {{ __('Select Warehouse') }} --</option>
                    @foreach($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium">{{ __('Service Job ID') }}</label>
                <input type="number" name="service_job_id" required min="1"
                       class="mt-1 block w-full rounded border-gray-300"
                       value="{{ old('service_job_id') }}" />
            </div>

            <div>
                <label class="block text-sm font-medium">{{ __('Quantity') }}</label>
                <input type="number" name="qty" required min="1"
                       class="mt-1 block w-full rounded border-gray-300"
                       value="{{ old('qty', 1) }}" />
            </div>

            <div>
                <label class="block text-sm font-medium">{{ __('Cost per Unit (optional)') }}</label>
                <input type="number" step="0.0001" name="cost_per_unit" min="0"
                       class="mt-1 block w-full rounded border-gray-300"
                       value="{{ old('cost_per_unit') }}" />
            </div>

            <div>
                <label class="block text-sm font-medium">{{ __('Note') }}</label>
                <textarea name="note" rows="2"
                          class="mt-1 block w-full rounded border-gray-300">{{ old('note') }}</textarea>
            </div>

            <x-button type="submit">{{ __('Issue Material') }}</x-button>
        </form>
    </div>
@endsection
