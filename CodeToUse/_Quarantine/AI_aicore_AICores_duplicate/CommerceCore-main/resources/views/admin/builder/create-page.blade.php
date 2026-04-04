<x-layouts.admin>
    <x-slot:header>Create Page</x-slot:header>
    <div class="max-w-xl mx-auto">
        <div class="bg-white dark:bg-surface-800 rounded-2xl border border-surface-200 dark:border-surface-700 p-6">
            <h2 class="text-lg font-display font-semibold text-surface-800 dark:text-white mb-6">Create New Page</h2>
            <form method="POST" action="{{ route('admin.builder.store') }}" class="space-y-5">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1.5">Page Name *</label>
                    <input type="text" name="page_name" required class="w-full px-4 py-2.5 bg-surface-50 dark:bg-surface-700 border-0 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 dark:text-white">
                </div>
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="is_homepage" value="1" class="w-5 h-5 rounded-lg border-surface-300 text-primary-600 focus:ring-primary-500">
                    <span class="text-sm font-medium text-surface-700 dark:text-surface-300">Set as Homepage</span>
                </label>
                <button type="submit" class="w-full px-5 py-2.5 bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white text-sm font-semibold rounded-xl shadow-lg shadow-primary-500/25 transition-all">Create Page</button>
            </form>
        </div>
    </div>
</x-layouts.admin>
