<x-layouts.admin>
    <x-slot:header>
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">
                {{ __('Agent Dashboard') }}
            </h2>
            <form action="{{ route('admin.agent.assign') }}" method="POST">
                @csrf
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg shadow-sm transition-colors flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Fetch New Order
                </button>
            </form>
        </div>
    </x-slot:header>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if(session('success'))
                <div class="mb-4 bg-green-50 text-green-700 p-4 rounded-lg border border-green-200">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 bg-yellow-50 text-yellow-700 p-4 rounded-lg border border-yellow-200">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-slate-200">
                <div class="p-6 text-slate-900 border-b border-slate-200 bg-slate-50 flex justify-between items-center">
                    <h3 class="text-lg font-medium">Your Active Assignments</h3>
                    <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded-full">{{ $assignedOrders->total() }} Orders</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-slate-500">
                        <thead class="text-xs text-slate-700 uppercase bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th scope="col" class="px-6 py-3">Order ID</th>
                                <th scope="col" class="px-6 py-3">Customer Info</th>
                                <th scope="col" class="px-6 py-3">Total</th>
                                <th scope="col" class="px-6 py-3">Lifecycle Status</th>
                                <th scope="col" class="px-6 py-3">Assigned Time</th>
                                <th scope="col" class="px-6 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($assignedOrders as $order)
                                <tr class="bg-white border-b border-slate-200 hover:bg-slate-50">
                                    <td class="px-6 py-4 font-medium text-slate-900 whitespace-nowrap">
                                        {{ $order->order_number }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-slate-900">{{ $order->customer_name }}</div>
                                        <div class="text-xs text-slate-500">{{ $order->phone }}</div>
                                    </td>
                                    <td class="px-6 py-4 font-medium">
                                        ${{ number_format($order->total_price, 2) }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
                                            {{ $order->lifecycle_status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ $order->locked_at->diffForHumans() }}
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('admin.orders.show', $order) }}" class="text-blue-600 hover:text-blue-900 font-medium">
                                            Manage Order &rarr;
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-slate-500">
                                        <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                                        <p class="text-base font-medium text-slate-900">No active assignments</p>
                                        <p class="text-sm mt-1">Click "Fetch New Order" to start handling the queue.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if($assignedOrders->hasPages())
                    <div class="p-4 border-t border-slate-200">
                        {{ $assignedOrders->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.admin>
