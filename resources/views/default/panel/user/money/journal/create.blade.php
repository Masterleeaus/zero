@extends('panel.layout.app')
@section('title', __('New Journal Entry'))

@section('content')
    <div class="py-6 max-w-4xl">
        <form method="POST" action="{{ route('dashboard.money.journal.store') }}" id="journal-form">
            @csrf

            <div class="space-y-4 mb-6">
                <div class="grid md:grid-cols-2 gap-4">
                    <x-form.group>
                        <x-form.label for="entry_date">{{ __('Entry Date') }}</x-form.label>
                        <x-form.input type="date" id="entry_date" name="entry_date"
                                      value="{{ old('entry_date', now()->toDateString()) }}" required />
                        <x-form.error field="entry_date" />
                    </x-form.group>

                    <x-form.group>
                        <x-form.label for="reference">{{ __('Reference') }}</x-form.label>
                        <x-form.input id="reference" name="reference" value="{{ old('reference') }}" placeholder="e.g. JE-001" />
                        <x-form.error field="reference" />
                    </x-form.group>
                </div>

                <x-form.group>
                    <x-form.label for="description">{{ __('Description') }}</x-form.label>
                    <x-form.input id="description" name="description" value="{{ old('description') }}" required />
                    <x-form.error field="description" />
                </x-form.group>
            </div>

            {{-- Journal Lines --}}
            <div class="mb-4">
                <h3 class="text-sm font-semibold mb-2">{{ __('Lines') }}</h3>
                @error('lines')
                    <p class="text-red-600 text-sm mb-2">{{ $message }}</p>
                @enderror

                <x-table>
                    <x-slot:head>
                        <tr>
                            <th>{{ __('Account') }}</th>
                            <th>{{ __('Description') }}</th>
                            <th class="text-end">{{ __('Debit') }}</th>
                            <th class="text-end">{{ __('Credit') }}</th>
                            <th></th>
                        </tr>
                    </x-slot:head>
                    <x-slot:body id="lines-body">
                        @for($i = 0; $i < max(2, count(old('lines', [[],[]]))); $i++)
                            <tr class="line-row">
                                <td>
                                    <x-select name="lines[{{ $i }}][account_id]">
                                        <option value="">{{ __('Select account') }}</option>
                                        @foreach($accounts->groupBy('type') as $type => $group)
                                            <optgroup label="{{ ucfirst($type) }}">
                                                @foreach($group as $account)
                                                    <option value="{{ $account->id }}"
                                                            @selected(old("lines.{$i}.account_id") == $account->id)>
                                                        {{ $account->code ? "[{$account->code}] " : '' }}{{ $account->name }}
                                                    </option>
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    </x-select>
                                </td>
                                <td>
                                    <x-form.input name="lines[{{ $i }}][description]"
                                                  value="{{ old("lines.{$i}.description") }}" />
                                </td>
                                <td>
                                    <x-form.input type="number" step="0.01" min="0"
                                                  name="lines[{{ $i }}][debit]"
                                                  value="{{ old("lines.{$i}.debit", '0.00') }}"
                                                  class="text-end" />
                                </td>
                                <td>
                                    <x-form.input type="number" step="0.01" min="0"
                                                  name="lines[{{ $i }}][credit]"
                                                  value="{{ old("lines.{$i}.credit", '0.00') }}"
                                                  class="text-end" />
                                </td>
                                <td>
                                    <button type="button" class="text-red-500 text-sm remove-line">✕</button>
                                </td>
                            </tr>
                        @endfor
                    </x-slot:body>
                </x-table>

                <div class="mt-2">
                    <button type="button" id="add-line" class="text-sm text-blue-600 hover:underline">
                        + {{ __('Add line') }}
                    </button>
                </div>
            </div>

            <div class="flex gap-3">
                <x-button type="submit">{{ __('Post Journal Entry') }}</x-button>
                <x-button href="{{ route('dashboard.money.journal.index') }}" variant="ghost">{{ __('Cancel') }}</x-button>
            </div>
        </form>
    </div>

    <script>
        let lineCount = {{ max(2, count(old('lines', [[],[]]))) }};

        document.getElementById('add-line').addEventListener('click', function () {
            const i = lineCount++;
            const tbody = document.getElementById('lines-body');
            const row = document.createElement('tr');
            row.className = 'line-row';
            row.innerHTML = `
                <td>
                    <select name="lines[${i}][account_id]" class="form-select w-full">
                        <option value="">{{ __('Select account') }}</option>
                        @foreach($accounts->groupBy('type') as $type => $group)
                            <optgroup label="{{ ucfirst($type) }}">
                                @foreach($group as $account)
                                    <option value="{{ $account->id }}">{{ addslashes(($account->code ? "[{$account->code}] " : '') . $account->name) }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </td>
                <td><input type="text" name="lines[${i}][description]" class="form-input w-full" /></td>
                <td><input type="number" step="0.01" min="0" name="lines[${i}][debit]" value="0.00" class="form-input w-full text-end" /></td>
                <td><input type="number" step="0.01" min="0" name="lines[${i}][credit]" value="0.00" class="form-input w-full text-end" /></td>
                <td><button type="button" class="text-red-500 text-sm remove-line">✕</button></td>
            `;
            tbody.appendChild(row);
            bindRemove(row.querySelector('.remove-line'));
        });

        function bindRemove(btn) {
            btn.addEventListener('click', function () {
                const rows = document.querySelectorAll('.line-row');
                if (rows.length > 2) {
                    btn.closest('tr').remove();
                }
            });
        }

        document.querySelectorAll('.remove-line').forEach(bindRemove);
    </script>
@endsection
