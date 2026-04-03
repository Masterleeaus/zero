<x-layouts.admin>
    <x-slot:header>Edit Supplier</x-slot:header>

    <div class="max-w-2xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-display font-bold text-surface-800 dark:text-white">Edit: {{ $supplier->name }}</h2>
            <a href="{{ route('admin.suppliers.index') }}" class="text-sm text-surface-500 hover:text-surface-700 dark:hover:text-surface-300 font-medium">← Back</a>
        </div>

        <form action="{{ route('admin.suppliers.update', $supplier) }}" method="POST" class="bg-white dark:bg-surface-800 p-6 rounded-2xl border border-surface-200 dark:border-surface-700 shadow-sm space-y-4">
            @csrf @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-surface-500 mb-1">Name *</label>
                    <input type="text" name="name" required value="{{ old('name', $supplier->name) }}" class="w-full px-4 py-2.5 bg-surface-50 dark:bg-surface-700 border-0 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-surface-500 mb-1">Company</label>
                    <input type="text" name="company" value="{{ old('company', $supplier->company) }}" class="w-full px-4 py-2.5 bg-surface-50 dark:bg-surface-700 border-0 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-surface-500 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email', $supplier->email) }}" class="w-full px-4 py-2.5 bg-surface-50 dark:bg-surface-700 border-0 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-surface-500 mb-1">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone', $supplier->phone) }}" class="w-full px-4 py-2.5 bg-surface-50 dark:bg-surface-700 border-0 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 dark:text-white">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-surface-500 mb-1">Address</label>
                <textarea name="address" rows="2" class="w-full px-4 py-2.5 bg-surface-50 dark:bg-surface-700 border-0 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 dark:text-white">{{ old('address', $supplier->address) }}</textarea>
            </div>
            <div>
                <label class="block text-xs font-semibold text-surface-500 mb-1">Status</label>
                <select name="status" class="w-full px-4 py-2.5 bg-surface-50 dark:bg-surface-700 border-0 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 dark:text-white">
                    <option value="active" {{ $supplier->status === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ $supplier->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <a href="{{ route('admin.suppliers.index') }}" class="px-6 py-2.5 text-sm font-semibold text-surface-600 dark:text-surface-400 bg-surface-100 dark:bg-surface-700 rounded-xl hover:bg-surface-200 dark:hover:bg-surface-600 transition-colors">Cancel</a>
                <button type="submit" class="px-8 py-2.5 bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white text-sm font-semibold rounded-xl shadow-lg shadow-primary-500/25 transition-all">Update Supplier</button>
            </div>
        </form>
    </div>
</x-layouts.admin>
