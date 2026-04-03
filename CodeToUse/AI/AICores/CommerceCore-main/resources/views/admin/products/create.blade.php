<x-layouts.admin>
    <x-slot:header>{{ isset($product) ? 'Edit Product' : 'Add Product' }}</x-slot:header>

    <div class="max-w-3xl mx-auto">
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden shadow-sm">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">
                <h2 class="text-lg font-bold text-slate-900 dark:text-white">{{ isset($product) ? 'Edit Product' : 'New Product' }}</h2>
            </div>
            <form method="POST" action="{{ isset($product) ? route('admin.products.update', $product) : route('admin.products.store') }}" enctype="multipart/form-data" class="p-6 space-y-6">
                @csrf
                @if(isset($product)) @method('PUT') @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Product Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $product->name ?? '') }}" required
                               class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-slate-50 dark:bg-slate-900/50 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:text-white outline-none transition-shadow">
                        @error('name') <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Price <span class="text-red-500">*</span></label>
                        <input type="number" name="price" value="{{ old('price', $product->price ?? '') }}" step="0.01" required
                               class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-slate-50 dark:bg-slate-900/50 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:text-white outline-none transition-shadow">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Compare Price</label>
                        <input type="number" name="compare_price" value="{{ old('compare_price', $product->compare_price ?? '') }}" step="0.01"
                               class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-slate-50 dark:bg-slate-900/50 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:text-white outline-none transition-shadow">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">SKU</label>
                        <input type="text" name="sku" value="{{ old('sku', $product->sku ?? '') }}"
                               class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-slate-50 dark:bg-slate-900/50 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:text-white outline-none transition-shadow">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Stock <span class="text-red-500">*</span></label>
                        <input type="number" name="stock" value="{{ old('stock', $product->stock ?? 0) }}" required
                               class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-slate-50 dark:bg-slate-900/50 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:text-white outline-none transition-shadow">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Category</label>
                        <select name="category_id" class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-slate-50 dark:bg-slate-900/50 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:text-white outline-none transition-shadow">
                            <option value="">No Category</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id ?? '') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Status</label>
                        <select name="status" class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-slate-50 dark:bg-slate-900/50 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:text-white outline-none transition-shadow">
                            <option value="active" {{ old('status', $product->status ?? 'active') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="draft" {{ old('status', $product->status ?? '') === 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="archived" {{ old('status', $product->status ?? '') === 'archived' ? 'selected' : '' }}>Archived</option>
                        </select>
                    </div>

                    <div class="md:col-span-2" x-data="{ generating: false }">
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Description</label>
                            <button type="button" @click="
                                generating = true;
                                fetch('{{ route('ai.generate') }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                                    },
                                    body: JSON.stringify({
                                        name: document.querySelector('input[name=name]').value,
                                        category: document.querySelector('select[name=category_id] option:checked').text,
                                        tags: 'product, commerce, ' + document.querySelector('input[name=name]').value
                                    })
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if(data.description) {
                                        document.querySelector('textarea[name=description]').value = data.description;
                                    }
                                })
                                .finally(() => { generating = false; });
                            " class="text-xs font-semibold px-3 py-1 bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400 rounded transition hover:bg-purple-200 dark:hover:bg-purple-900/50 flex items-center gap-1 disabled:opacity-50 border border-purple-200 dark:border-purple-800">
                                <svg x-show="!generating" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                <svg x-show="generating" class="animate-spin w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                <span x-text="generating ? 'Thinking...' : 'AI Generate'"></span>
                            </button>
                        </div>
                        <textarea name="description" rows="5" class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-slate-50 dark:bg-slate-900/50 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:text-white outline-none transition-shadow">{{ old('description', $product->description ?? '') }}</textarea>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Product Image</label>
                        <input type="file" name="image" accept="image/*"
                               class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-slate-50 dark:bg-slate-900/50 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:text-white outline-none transition-shadow file:mr-4 file:py-1 file:px-3 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 dark:file:bg-slate-700 dark:file:text-slate-300 hover:file:bg-blue-100 dark:hover:file:bg-slate-600 cursor-pointer">
                    </div>

                    <div class="md:col-span-2">
                        <label class="flex items-center gap-3 cursor-pointer p-3 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/30 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors w-max">
                            <input type="checkbox" name="featured" value="1" {{ old('featured', $product->featured ?? false) ? 'checked' : '' }}
                                   class="w-5 h-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500 bg-white dark:bg-slate-900">
                            <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">Featured Product</span>
                        </label>
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-4 border-t border-slate-200 dark:border-slate-700 mt-6">
                    <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg shadow-sm transition-colors">
                        {{ isset($product) ? 'Update Product' : 'Create Product' }}
                    </button>
                    <a href="{{ route('admin.products.index') }}" class="px-5 py-2.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 text-sm font-semibold rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition shadow-sm">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-layouts.admin>
