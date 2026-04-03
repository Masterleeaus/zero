<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - {{ $order->order_number }}</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            background: #f4f4f4;
            color: #000;
            font-size: 14px;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
        }

        .receipt-container {
            width: 80mm; /* Standard 80mm thermal paper */
            background: #fff;
            padding: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .border-t { border-top: 1px dashed #000; }
        .border-b { border-bottom: 1px dashed #000; }
        .my-2 { margin: 8px 0; }
        .py-2 { padding: 8px 0; }
        .w-full { width: 100%; }
        
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        th, td {    
            padding: 4px 0;
        }

        .controls {
            position: fixed;
            top: 20px;
            right: 20px;
            display: flex;
            gap: 10px;
            background: #fff;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        button, .btn {
            padding: 8px 16px;
            background: #2563eb;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-family: sans-serif;
            font-size: 14px;
        }

        .btn-secondary {
            background: #475569;
        }

        @media print {
            body { 
                background: none; 
                padding: 0; 
                display: block;
            }
            .receipt-container { 
                box-shadow: none; 
                width: 100%; /* Printer driver handles paper width */
                padding: 0;
                margin: 0;
            }
            .controls { display: none !important; }
        }
    </style>
</head>
<body>

    <div class="controls">
        <a href="{{ url()->previous() }}" class="btn btn-secondary">Back</a>
        <a href="{{ route('admin.orders.invoice', [$order, 'a4']) }}" class="btn btn-secondary">A4 Format</a>
        <button onclick="window.print()">Print Receipt</button>
    </div>

    <div class="receipt-container">
        <!-- Header -->
        <div class="text-center py-2">
            <h2 style="margin:0;">{{ $order->store->name ?? config('app.name') }}</h2>
            <p style="margin:5px 0;">Order #{{ $order->order_number }}</p>
            <p style="margin:5px 0;">{{ $order->created_at->format('M d, Y H:i A') }}</p>
        </div>

        <div class="border-t border-b py-2 my-2">
            <p style="margin:2px 0;">Customer: {{ $order->customer_name }}</p>
            @if($order->phone)
            <p style="margin:2px 0;">Phone: {{ $order->phone }}</p>
            @endif
            <p style="margin:2px 0;">Served by: {{ $order->user->name ?? 'Online' }}</p>
        </div>

        <!-- Items -->
        <table>
            <tr style="border-bottom: 1px solid #000;">
                <th style="text-align:left;">Item</th>
                <th>Qty</th>
                <th class="text-right">Total</th>
            </tr>
            @foreach($order->items as $item)
            <tr>
                <td>{{ \Illuminate\Support\Str::limit($item->product->name ?? 'Item', 15) }}</td>
                <td class="text-center">{{ $item->quantity }}</td>
                <td class="text-right">${{ number_format($item->quantity * $item->price, 2) }}</td>
            </tr>
            @endforeach
        </table>

        <!-- Totals -->
        <div class="border-t my-2 py-2">
            <table style="width:100%;">
                <tr>
                    <td>Subtotal:</td>
                    <td class="text-right">${{ number_format($order->total_price, 2) }}</td>
                </tr>
                <tr>
                    <td class="font-bold pb-2" style="font-size:16px;">TOTAL:</td>
                    <td class="font-bold text-right" style="font-size:16px;">${{ number_format($order->total_price, 2) }}</td>
                </tr>
            </table>
        </div>

        <!-- Footer -->
        <div class="text-center py-2 border-t">
            <p style="margin:5px 0 10px 0;">Thank you for shopping with us!</p>
            @if($order->notes)
            <p style="margin:0; font-style:italic;">Note: {{ $order->notes }}</p>
            @endif
            <p style="margin:5px 0 0 0; font-size: 11px;">Powered by CommerceCore</p>
        </div>
    </div>

</body>
</html>
