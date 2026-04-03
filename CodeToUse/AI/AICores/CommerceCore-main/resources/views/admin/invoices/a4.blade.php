<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - {{ $order->order_number }}</title>
    @vite(['resources/css/app.css'])
    <style>
        @media print {
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body class="bg-slate-100 font-sans text-slate-900 py-8">
    <div class="max-w-4xl mx-auto bg-white shadow-lg print:shadow-none print:w-full print:max-w-none print:m-0">
        
        <!-- Controls -->
        <div class="no-print bg-slate-800 text-white p-4 flex justify-between items-center rounded-t-lg print:hidden">
            <div>
                <a href="{{ url()->previous() }}" class="text-slate-300 hover:text-white flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Back
                </a>
            </div>
            <div class="flex gap-4">
                <a href="{{ route('admin.orders.invoice', [$order, 'thermal']) }}" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 rounded text-sm font-medium transition-colors">Switch to Thermal</a>
                <button onclick="window.print()" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 rounded text-sm font-bold shadow transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    Print A4
                </button>
            </div>
        </div>

        <!-- A4 Page Content -->
        <div class="p-10">
            <!-- Header -->
            <div class="flex justify-between items-start border-b border-slate-200 pb-8 mb-8">
                <div>
                    <h1 class="text-4xl font-extrabold text-blue-900 tracking-tight">INVOICE</h1>
                    <p class="text-slate-500 mt-1">Order #{{ $order->order_number }}</p>
                </div>
                <div class="text-right">
                    <h2 class="text-2xl font-bold text-slate-800">{{ $order->store->name ?? config('app.name') }}</h2>
                    <p class="text-slate-600 mt-1">Generated: {{ now()->format('M d, Y H:i') }}</p>
                </div>
            </div>

            <!-- Info Grid -->
            <div class="grid grid-cols-2 gap-12 border-b border-slate-200 pb-8 mb-8">
                <div>
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Billed To</h3>
                    <p class="text-lg font-semibold text-slate-900">{{ $order->customer_name }}</p>
                    @if($order->customer_email)
                        <p class="text-slate-600">{{ $order->customer_email }}</p>
                    @endif
                    @if($order->phone)
                        <p class="text-slate-600">{{ $order->phone }}</p>
                    @endif
                    <p class="text-slate-600 whitespace-pre-line mt-2">{{ $order->address }}</p>
                </div>
                <div>
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Order Details</h3>
                    <table class="w-full text-sm">
                        <tr>
                            <td class="font-medium text-slate-500 py-1">Order Date:</td>
                            <td class="text-right text-slate-900 font-semibold">{{ $order->created_at->format('M d, Y') }}</td>
                        </tr>
                        <tr>
                            <td class="font-medium text-slate-500 py-1">Status:</td>
                            <td class="text-right text-slate-900 font-semibold uppercase">{{ $order->status }}</td>
                        </tr>
                        <tr>
                            <td class="font-medium text-slate-500 py-1">Sales Rep:</td>
                            <td class="text-right text-slate-900 font-semibold">{{ $order->user->name ?? 'Online' }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Items Table -->
            <table class="w-full mb-8">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="py-3 px-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider rounded-l-lg">Item</th>
                        <th class="py-3 px-4 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Qty</th>
                        <th class="py-3 px-4 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Price</th>
                        <th class="py-3 px-4 text-right text-xs font-bold text-slate-500 uppercase tracking-wider rounded-r-lg">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach($order->items as $item)
                    <tr>
                        <td class="py-4 px-4">
                            <p class="font-semibold text-slate-900">{{ $item->product->name ?? 'Unknown Product' }}</p>
                        </td>
                        <td class="py-4 px-4 text-center font-medium">{{ $item->quantity }}</td>
                        <td class="py-4 px-4 text-right text-slate-600">${{ number_format($item->price, 2) }}</td>
                        <td class="py-4 px-4 text-right font-bold text-slate-900">${{ number_format($item->quantity * $item->price, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Totals -->
            <div class="flex justify-end">
                <div class="w-1/2">
                    <table class="w-full text-sm">
                        <tr>
                            <td class="py-2 text-slate-600">Subtotal</td>
                            <td class="py-2 text-right font-semibold text-slate-900">${{ number_format($order->total_price, 2) }}</td>
                        </tr>
                        <tr class="border-t border-slate-200">
                            <td class="py-3 text-lg font-bold text-slate-900">Total</td>
                            <td class="py-3 text-right text-2xl font-black text-blue-600">${{ number_format($order->total_price, 2) }}</td>
                        </tr>
                    </table>
                </div>
            </div>
            
            @if($order->notes)
            <div class="mt-12 bg-slate-50 p-4 rounded-lg border border-slate-200">
                <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Notes</h4>
                <p class="text-slate-700 text-sm">{{ $order->notes }}</p>
            </div>
            @endif

            <!-- Footer -->
            <div class="mt-16 text-center text-slate-400 text-sm">
                <p>Thank you for your business!</p>
                <p class="mt-1">{{ config('app.url') }}</p>
            </div>
        </div>
    </div>
</body>
</html>
