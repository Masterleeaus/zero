<x-layouts.admin>
    <x-slot:header>Stores</x-slot:header>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-display font-bold text-surface-800 dark:text-white">Stores</h2>
            <a href="{{ route('admin.stores.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-primary-500 to-primary-600 text-white text-sm font-semibold rounded-xl shadow-lg shadow-primary-500/25">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add Store
            </a>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @forelse($stores as $store)
            <div class="card-hover bg-white dark:bg-surface-800 rounded-2xl border border-surface-200 dark:border-surface-700 p-5">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center text-white font-display font-bold text-lg">{{ strtoupper(substr($store->name, 0, 1)) }}</div>
                    <div>
                        <h3 class="text-sm font-display font-semibold text-surface-800 dark:text-white">{{ $store->name }}</h3>
                        <p class="text-xs text-surface-400">/store/{{ $store->slug }}</p>
                    </div>
                </div>
                <div class="flex items-center justify-between text-xs text-surface-400 mb-4">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md font-semibold {{ $store->status === 'active' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-red-100 text-red-700' }}">{{ ucfirst($store->status) }}</span>
                    <span>{{ $store->plan?->name ?? 'Free' }}</span>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('admin.stores.edit', $store) }}" class="flex-1 text-center px-3 py-2 text-sm font-medium text-primary-600 bg-primary-50 dark:bg-primary-900/20 dark:text-primary-400 rounded-xl hover:bg-primary-100 transition-colors">Edit</a>
                    <a href="{{ route('storefront.home', $store->slug) }}" target="_blank" class="flex-1 text-center px-3 py-2 text-sm font-medium text-surface-600 bg-surface-100 dark:bg-surface-700 dark:text-surface-300 rounded-xl hover:bg-surface-200 transition-colors">Visit</a>
                </div>
            </div>
            @empty
            <div class="col-span-full text-center py-16">
                <p class="text-surface-500 mb-4">No stores yet</p>
                <a href="{{ route('admin.stores.create') }}" class="text-primary-600 hover:underline font-medium">Create your first store →</a>
            </div>
            @endforelse
        </div>
        @if($stores->hasPages())<div class="mt-6">{{ $stores->links() }}</div>@endif
    </div>
</x-layouts.admin>
