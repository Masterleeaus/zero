<x-layouts.admin>
    <x-slot:header>Customer Intelligence Center</x-slot:header>

    <div class="bg-white dark:bg-surface-800 rounded-2xl border border-surface-200 dark:border-surface-700 overflow-hidden shadow-sm">
        <div class="px-6 py-4 border-b border-surface-200 dark:border-surface-700">
            <h3 class="text-sm font-bold text-surface-800 dark:text-white uppercase tracking-wider">Top Performing Customers</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-surface-50 dark:bg-surface-900/30 border-b border-surface-200 dark:border-surface-700">
                        <th class="px-6 py-4 text-[10px] font-bold text-surface-500 uppercase">Customer</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-surface-500 uppercase text-center">Rank</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-surface-500 uppercase text-right">Orders</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-surface-500 uppercase text-right">Life Spending</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-surface-500 uppercase text-right">Value Insight</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-100 dark:divide-surface-700">
                    @foreach($customers as $c)
                    <tr class="hover:bg-surface-50 dark:hover:bg-surface-700/50 transition-colors">
                        <td class="px-6 py-4">
                            <p class="text-sm font-bold text-surface-800 dark:text-white">{{ $c->name }}</p>
                            <p class="text-[10px] text-surface-400">{{ $c->email }}</p>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-tighter shadow-sm
                                {{ $c->customer_rank === 'VIP' ? 'bg-indigo-600 text-white' : ($c->customer_rank === 'regular' ? 'bg-emerald-100 text-emerald-700' : 'bg-surface-100 text-surface-600') }}">
                                {{ $c->customer_rank }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <p class="text-sm font-black text-surface-800 dark:text-white">{{ $c->order_count }}</p>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <p class="text-sm font-black text-indigo-600">${{ number_format($c->total_spent, 2) }}</p>
                        </td>
                        <td class="px-6 py-4 text-right">
                            @if($c->total_spent > 5000)
                                <span class="text-[10px] font-bold text-indigo-500 uppercase">High LTV Core</span>
                            @elseif($c->total_spent > 1000)
                                <span class="text-[10px] font-bold text-emerald-500 uppercase">Steady Growth</span>
                            @else
                                <span class="text-[10px] font-bold text-surface-400 uppercase">Onboarding</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-surface-200 dark:border-surface-700">
            {{ $customers->links() }}
        </div>
    </div>
</x-layouts.admin>
