@extends('panel.layout.app')
@section('title', __('Aged Receivables'))

@section('content')
    <div class="py-6 space-y-4">
        <h1 class="text-xl font-semibold">{{ __('Aged Receivables') }}</h1>

        <div class="grid grid-cols-5 gap-4">
            @foreach([
                'current' => __('Current'),
                '1_30'    => __('1–30 days'),
                '31_60'   => __('31–60 days'),
                '61_90'   => __('61–90 days'),
                'over_90' => __('Over 90 days'),
            ] as $key => $label)
                <div class="bg-white border rounded p-4 text-center">
                    <p class="text-xs text-gray-500">{{ $label }}</p>
                    <p class="text-lg font-semibold {{ $key !== 'current' ? 'text-red-600' : 'text-gray-800' }}">
                        {{ number_format($buckets[$key] ?? 0, 2) }}
                    </p>
                </div>
            @endforeach
        </div>

        <div class="text-sm text-gray-500 mt-2">
            {{ __('Total outstanding') }}: <strong>{{ number_format(array_sum($buckets), 2) }}</strong>
        </div>
    </div>
@endsection
