<x-layouts.admin>
    <x-slot:header>Edit Store: {{ $store->name }}</x-slot:header>

    <div class="max-w-4xl mx-auto space-y-6">
        <div class="bg-white dark:bg-surface-800 rounded-2xl border border-surface-200 dark:border-surface-700 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-surface-200 dark:border-surface-700">
                <h3 class="text-sm font-bold text-surface-800 dark:text-white uppercase tracking-wider">General Information</h3>
            </div>
            
            <form action="{{ route('admin.stores.update', $store) }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs text-surface-400 mb-1 font-bold uppercase tracking-widest">Store Name</label>
                        <input type="text" name="name" value="{{ old('name', $store->name) }}" required
                            class="w-full text-sm border-surface-200 rounded-lg dark:bg-surface-900 dark:text-white dark:border-surface-700">
                        @error('name')<p class="text-[10px] text-rose-500 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs text-surface-400 mb-1 font-bold uppercase tracking-widest">Store Slug (Domain Segment)</label>
                        <input type="text" name="slug" value="{{ old('slug', $store->slug) }}" placeholder="my-awesome-store"
                            class="w-full text-sm border-surface-200 rounded-lg dark:bg-surface-900 dark:text-white dark:border-surface-700">
                        @error('slug')<p class="text-[10px] text-rose-500 mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <label class="block text-xs text-surface-400 mb-1 font-bold uppercase tracking-widest">Store Description</label>
                    <textarea name="description" rows="4" 
                        class="w-full text-sm border-surface-200 rounded-lg dark:bg-surface-900 dark:text-white dark:border-surface-700">{{ old('description', $store->description) }}</textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-xs text-surface-400 mb-1 font-bold uppercase tracking-widest">Pricing Plan</label>
                        <select name="plan_id" class="w-full text-sm border-surface-200 rounded-lg dark:bg-surface-900 dark:text-white dark:border-surface-700">
                            @foreach($plans as $plan)
                                <option value="{{ $plan->id }}" {{ old('plan_id', $store->plan_id) == $plan->id ? 'selected' : '' }}>{{ $plan->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-surface-400 mb-1 font-bold uppercase tracking-widest">Store Status</label>
                        <select name="status" class="w-full text-sm border-surface-200 rounded-lg dark:bg-surface-900 dark:text-white dark:border-surface-700">
                            <option value="active" {{ $store->status === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ $store->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="suspended" {{ $store->status === 'suspended' ? 'selected' : '' }}>Suspended</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-surface-400 mb-1 font-bold uppercase tracking-widest">Logo</label>
                        <input type="file" name="logo" class="w-full text-xs text-surface-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-[10px] file:font-semibold file:bg-primary-50 file:text-primary-700">
                        @if($store->logo)
                            <p class="text-[10px] text-surface-400 mt-1">Current logo exists</p>
                        @endif
                    </div>
                </div>

                <div class="pt-6 border-t border-surface-200 dark:border-surface-700 flex justify-end gap-4">
                    <a href="{{ route('admin.stores.index') }}" 
                       class="px-6 py-2.5 text-xs font-bold text-surface-600 hover:text-surface-900 transition-colors uppercase tracking-widest">
                        Cancel
                    </a>
                    <button type="submit" class="px-8 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-xs font-black rounded-xl transition-all shadow-lg shadow-primary-500/20 active:scale-95">
                        UPDATE STORE DETAILS
                    </button>
                </div>
            </form>
        </div>
        
        {{-- Danger Zone --}}
        <div class="bg-rose-50 dark:bg-rose-900/10 rounded-2xl border border-rose-200 dark:border-rose-900/30 p-6 flex items-center justify-between">
            <div>
                <h4 class="text-xs font-black text-rose-800 dark:text-rose-200 uppercase tracking-widest mb-1">Danger Zone</h4>
                <p class="text-[10px] text-rose-700 dark:text-rose-300">Deleting this store will permanently erase all products, orders, and customer data associated with it.</p>
            </div>
            <form action="{{ route('admin.stores.destroy', $store) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this store forever?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-6 py-2 bg-rose-600 hover:bg-rose-700 text-white text-[10px] font-black rounded-lg transition-all shadow-lg shadow-rose-500/20">
                    DELETE STORE
                </button>
            </form>
        </div>
    </div>
</x-layouts.admin>
