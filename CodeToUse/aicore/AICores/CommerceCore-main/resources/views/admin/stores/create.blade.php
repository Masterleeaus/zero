<x-layouts.admin>
    <x-slot:header>{{ isset($store) && $store->exists ? 'Edit Store' : 'Create Store' }}</x-slot:header>
    <div class="max-w-2xl mx-auto">
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6">
            <form method="POST" action="{{ isset($store) && $store->exists ? route('admin.stores.update', $store) : route('admin.stores.store') }}" enctype="multipart/form-data" class="space-y-5">
                @csrf
                @if(isset($store) && $store->exists) @method('PUT') @endif
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Store Name *</label>
                    <input type="text" name="name" value="{{ old('name', $store->name ?? '') }}" required class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border-0 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">URL Slug</label>
                    <input type="text" name="slug" value="{{ old('slug', $store->slug ?? '') }}" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border-0 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 dark:text-white" placeholder="auto-generated">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Description</label>
                    <textarea name="description" rows="3" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border-0 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 dark:text-white">{{ old('description', $store->description ?? '') }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Plan</label>
                    <select name="plan_id" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border-0 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 dark:text-white">
                        <option value="">Free</option>
                        @foreach($plans as $plan)
                        <option value="{{ $plan->id }}" {{ old('plan_id', $store->plan_id ?? '') == $plan->id ? 'selected' : '' }}>{{ $plan->name }} — ${{ $plan->price }}/mo</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Logo</label>
                    <input type="file" name="logo" accept="image/*" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border-0 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 dark:text-white file:mr-4 file:py-1 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-600">
                </div>
                @if(isset($store) && $store->exists)
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Status</label>
                    <select name="status" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border-0 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 dark:text-white">
                        <option value="active" {{ $store->status === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ $store->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="suspended" {{ $store->status === 'suspended' ? 'selected' : '' }}>Suspended</option>
                    </select>
                </div>
                @endif
                <button type="submit" class="w-full px-5 py-2.5 bg-gradient-to-r from-primary-500 to-primary-600 text-white text-sm font-semibold rounded-xl shadow-lg shadow-primary-500/25">{{ isset($store) && $store->exists ? 'Update Store' : 'Create Store' }}</button>
            </form>
        </div>
    </div>
</x-layouts.admin>
