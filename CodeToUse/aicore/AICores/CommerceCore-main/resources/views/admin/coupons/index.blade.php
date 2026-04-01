<x-layouts.admin>
    <x-slot:header>Marketing Coupons</x-slot:header>

    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-bold text-slate-800 dark:text-white">Discount Codes</h2>
            <a href="{{ route('admin.coupons.create') }}" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors shadow-sm">
                Create Coupon
            </a>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-700">
                            <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Code</th>
                            <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Value</th>
                            <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Usage</th>
                            <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Expires</th>
                            <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                        @forelse($coupons as $coupon)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400 text-xs font-medium rounded-lg border border-blue-100 dark:border-blue-800/30 font-mono">{{ $coupon->code }}</span>
                            </td>
                            <td class="px-6 py-4 text-sm font-medium text-slate-600 dark:text-slate-400 capitalize">{{ $coupon->type }}</td>
                            <td class="px-6 py-4 text-sm font-bold text-slate-900 dark:text-white">
                                {{ $coupon->type === 'percentage' ? $coupon->value . '%' : '$' . number_format($coupon->value, 2) }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-16 h-1.5 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden">
                                        <div class="bg-blue-500 h-full rounded-full" style="width: {{ $coupon->usage_limit ? ($coupon->used_count / $coupon->usage_limit * 100) : 0 }}%"></div>
                                    </div>
                                    <span class="text-xs text-slate-500 font-medium">{{ $coupon->used_count }} / {{ $coupon->usage_limit ?? '∞' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-500">{{ $coupon->expires_at ? $coupon->expires_at->format('M d, Y') : 'Never' }}</td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.coupons.edit', $coupon) }}" class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg></a>
                                    <form action="{{ route('admin.coupons.destroy', $coupon) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this coupon?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-lg transition-colors"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                                <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                                <p class="text-base font-medium text-slate-900 dark:text-white mb-1">No coupons found</p>
                                <p class="text-sm">Create a discount code to generate more sales.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.admin>
