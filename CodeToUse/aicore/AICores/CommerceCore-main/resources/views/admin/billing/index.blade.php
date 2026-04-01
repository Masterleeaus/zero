<x-layouts.admin>
    <x-slot:header>Billing & Subscription</x-slot:header>

    <div class="space-y-8 animate-fade-in">
        {{-- Current Plan Status --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm relative overflow-hidden">
            <div class="p-8 flex flex-col md:flex-row items-center justify-between gap-8">
                <div class="flex items-center gap-6">
                    <div class="w-16 h-16 rounded-2xl bg-blue-100 dark:bg-blue-900/30 text-blue-600 flex items-center justify-center flex-shrink-0 border border-blue-200 dark:border-blue-800/30">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Current Plan</p>
                        <h2 class="text-2xl font-bold text-slate-900 dark:text-white leading-none">
                            {{ $activeSubscription ? $activeSubscription->plan->name : 'Community Edition' }}
                        </h2>
                        <div class="flex items-center gap-3 mt-3">
                            <span class="px-3 py-1 rounded-full bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 text-xs font-semibold border border-green-200 dark:border-green-800/30">
                                {{ $activeSubscription ? 'Active' : 'Free' }}
                            </span>
                            @if($activeSubscription)
                            <p class="text-xs font-medium text-slate-500">Renews on {{ $activeSubscription->expires_at->format('M d, Y') }}</p>
                            @endif
                        </div>
                    </div>
                </div>
                
                <div class="flex flex-col items-end gap-2 text-right">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Products Usage</p>
                    <div class="flex items-baseline gap-2">
                        <span class="text-2xl font-bold text-slate-900 dark:text-white">{{ $store->products()->count() }}</span>
                        <span class="text-slate-500 font-medium text-sm">/ {{ $activeSubscription ? $activeSubscription->plan->max_products : '50' }} Limits</span>
                    </div>
                    <div class="w-48 h-2 bg-slate-100 dark:bg-slate-700 rounded-full mt-1 overflow-hidden">
                        <div class="h-full bg-blue-600 rounded-full" style="width: {{ ($store->products()->count() / ($activeSubscription ? $activeSubscription->plan->max_products : 50)) * 100 }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Pricing Tiers --}}
        <div>
            <div class="text-center mb-8">
                <h3 class="text-sm font-semibold uppercase tracking-wider text-blue-600 mb-2">Available Plans</h3>
                <h2 class="text-3xl font-bold text-slate-900 dark:text-white">Choose the right plan for your business</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($plans as $plan)
                <div class="group relative">
                    <div class="bg-white dark:bg-slate-800 rounded-xl p-8 border border-slate-200 dark:border-slate-700 flex flex-col h-full transform transition-all duration-300 hover:-translate-y-1 hover:shadow-lg">
                        
                        <div class="mb-6">
                            <h4 class="text-lg font-semibold text-slate-900 dark:text-white mb-2">{{ $plan->name }}</h4>
                            <div class="flex items-baseline gap-1">
                                <span class="text-4xl font-bold text-slate-900 dark:text-white">${{ number_format($plan->price) }}</span>
                                <span class="text-sm font-medium text-slate-500">/mo</span>
                            </div>
                        </div>

                        <div class="space-y-4 flex-1 mb-8">
                            @foreach($plan->features ?? [] as $feature)
                            <div class="flex items-center gap-3 text-sm font-medium text-slate-600 dark:text-slate-300">
                                <div class="w-5 h-5 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-blue-600">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                {{ $feature }}
                            </div>
                            @endforeach
                            <div class="flex items-center gap-3 text-sm font-medium text-slate-600 dark:text-slate-300">
                                <div class="w-5 h-5 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 text-[10px]">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                Up to {{ $plan->max_products }} Products
                            </div>
                        </div>

                        <a href="{{ route('admin.billing.checkout', $plan) }}" 
                           class="w-full py-3 text-center rounded-lg font-semibold text-sm transition-all
                                  {{ $activeSubscription && $activeSubscription->plan_id === $plan->id ? 'bg-slate-100 dark:bg-slate-700 text-slate-500 cursor-not-allowed' : 'bg-blue-600 hover:bg-blue-700 text-white shadow-sm' }}">
                            {{ $activeSubscription && $activeSubscription->plan_id === $plan->id ? 'Current Plan' : 'Select Plan' }}
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Transaction History --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700 shadow-sm">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between bg-slate-50 dark:bg-slate-900/50">
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white uppercase tracking-wider">Billing History</h3>
                <span class="px-3 py-1 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-600 text-xs font-semibold border border-blue-200 dark:border-blue-800/30">Transactions</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-700">
                            <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Transaction ID</th>
                            <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Amount</th>
                            <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                        @forelse($payments as $payment)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="px-6 py-4">
                                <p class="font-mono text-sm text-slate-700 dark:text-slate-300">#{{ $payment->transaction_id }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm font-semibold text-slate-900 dark:text-white">${{ number_format($payment->amount, 2) }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-slate-500">{{ $payment->created_at->format('M d, Y') }}</p>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="px-2.5 py-1 rounded-full text-xs font-medium uppercase tracking-wide
                                    {{ $payment->status === 'completed' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 
                                       ($payment->status === 'pending' ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400') }}">
                                    {{ $payment->status }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-slate-500">
                                <div class="w-12 h-12 mx-auto text-slate-300 mb-3 flex items-center justify-center bg-slate-100 dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <p class="text-base font-medium text-slate-900 mb-1">No transactions found</p>
                                <p class="text-sm">You have not made any payments yet.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.admin>
