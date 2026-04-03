<x-layouts.admin>
    <x-slot:header>Asset Management</x-slot:header>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        {{-- Add Asset Form --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6 h-fit shadow-sm">
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white uppercase tracking-wider mb-4 border-b border-slate-100 dark:border-slate-700 pb-2">Add Business Asset</h3>
            <form action="{{ route('admin.assets.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Asset Name</label>
                    <input type="text" name="name" required placeholder="Laptop, Warehouse Rack, etc." class="w-full text-sm border-slate-300 dark:border-slate-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-900 dark:text-white">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Purchase Price</label>
                        <input type="number" step="0.01" name="purchase_price" required class="w-full text-sm border-slate-300 dark:border-slate-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Purchase Date</label>
                        <input type="date" name="purchase_date" value="{{ date('Y-m-d') }}" required class="w-full text-sm border-slate-300 dark:border-slate-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-900 dark:text-white">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Depreciation %</label>
                        <input type="number" step="0.1" name="depreciation_percentage" value="0.0" class="w-full text-sm border-slate-300 dark:border-slate-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Status</label>
                        <select name="status" class="w-full text-sm border-slate-300 dark:border-slate-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-900 dark:text-white">
                            <option value="in_use">In Use</option>
                            <option value="maintenance">Maintenance</option>
                            <option value="sold">Sold</option>
                            <option value="disposed">Disposed</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="w-full py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors flex justify-center items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Register Asset
                </button>
            </form>
        </div>

        {{-- Assets List --}}
        <div class="md:col-span-2 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden shadow-sm">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 flex justify-between items-center">
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white uppercase tracking-wider">Business Assets</h3>
                <span class="text-xs font-semibold text-slate-500 bg-white dark:bg-slate-800 px-3 py-1 rounded-full border border-slate-200 dark:border-slate-700">Total Value: ${{ number_format($assets->sum('current_value'), 2) }}</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-700">
                            <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Asset</th>
                            <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider text-center">Purchased</th>
                            <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right">Value (Current)</th>
                            <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                        @forelse($assets as $asset)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="px-6 py-4">
                                <p class="text-sm font-medium text-slate-900 dark:text-white">{{ $asset->name }}</p>
                                <p class="text-xs text-slate-500 mt-0.5">Depreciation: {{ $asset->depreciation_percentage }}%</p>
                            </td>
                            <td class="px-6 py-4 text-center text-sm text-slate-500">
                                {{ $asset->purchase_date->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <p class="text-sm font-semibold text-blue-600 dark:text-blue-500">${{ number_format($asset->current_value, 2) }}</p>
                                <p class="text-xs text-slate-500 mt-0.5">Orig: ${{ number_format($asset->purchase_price, 2) }}</p>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium uppercase tracking-wide
                                    {{ $asset->status === 'in_use' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400' }}">
                                    {{ str_replace('_', ' ', $asset->status) }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-slate-500">
                                <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                <p class="text-base font-medium text-slate-900 mb-1">No assets found</p>
                                <p class="text-sm">Register your first business asset using the form.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($assets->hasPages())
            <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50">
                {{ $assets->links() }}
            </div>
            @endif
        </div>
    </div>
</x-layouts.admin>
