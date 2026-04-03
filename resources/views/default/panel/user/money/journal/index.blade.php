@extends('panel.layout.app')
@section('title', __('Journal Entries'))
@section('titlebar_actions')
    <x-button href="{{ route('dashboard.money.accounts.index') }}" variant="ghost">
        <x-tabler-building-bank class="size-4" />
        {{ __('Chart of Accounts') }}
    </x-button>
@endsection

@section('content')
<div class="py-6" x-data="journalEntryForm()">
    @if(session('success'))
        <x-alert type="success" class="mb-4">{{ session('success') }}</x-alert>
    @endif
    @if(session('error'))
        <x-alert type="error" class="mb-4">{{ session('error') }}</x-alert>
    @endif

    {{-- Header --}}
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-bold text-slate-900 dark:text-white">{{ __('General Journal') }}</h2>
        <button @click="showForm = !showForm" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg shadow-sm transition-colors focus:ring-2 focus:ring-offset-1 dark:focus:ring-offset-slate-900"
            :class="showForm ? 'bg-slate-600 hover:bg-slate-700 text-white focus:ring-slate-500' : 'bg-indigo-600 hover:bg-indigo-700 text-white focus:ring-indigo-500'">
            <span x-text="showForm ? '{{ __('Cancel') }}' : '{{ __('+ New Journal Entry') }}'"></span>
        </button>
    </div>

    {{-- Create Form (Toggleable) --}}
    <div x-show="showForm" x-collapse x-cloak class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm mb-6">
        <form action="{{ route('dashboard.money.journal.store') }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div>
                    <label class="block text-xs text-slate-500 mb-1.5 font-semibold uppercase tracking-wider">{{ __('Date') }}</label>
                    <x-input type="date" name="date" value="{{ date('Y-m-d') }}" required />
                    @error('date') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs text-slate-500 mb-1.5 font-semibold uppercase tracking-wider">{{ __('Reference #') }}</label>
                    <x-input type="text" name="reference" value="JE-{{ date('Ymd') }}-{{ rand(100, 999) }}" required />
                    @error('reference') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs text-slate-500 mb-1.5 font-semibold uppercase tracking-wider">{{ __('Description') }}</label>
                    <x-input type="text" name="description" placeholder="{{ __('Record purpose…') }}" />
                </div>
            </div>

            {{-- Lines Table --}}
            <div class="border rounded-xl border-slate-200 dark:border-slate-700 overflow-hidden mb-4 shadow-sm">
                <table class="w-full text-sm text-left">
                    <thead class="bg-slate-50 dark:bg-slate-900/50 text-xs font-semibold text-slate-500 uppercase tracking-wider border-b border-slate-200 dark:border-slate-700">
                        <tr>
                            <th class="px-4 py-3 w-1/3">{{ __('Account') }}</th>
                            <th class="px-4 py-3 w-1/3">{{ __('Description') }}</th>
                            <th class="px-4 py-3 text-right w-28">{{ __('Debit') }}</th>
                            <th class="px-4 py-3 text-right w-28">{{ __('Credit') }}</th>
                            <th class="px-4 py-3 text-center w-12"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(line, index) in lines" :key="line.id">
                            <tr class="border-b border-slate-100 dark:border-slate-700/50 last:border-0 hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors group">
                                <td class="px-4 py-2">
                                    <select :name="'lines['+index+'][account_id]'" x-model="line.account_id" required
                                        class="w-full text-sm border-0 bg-transparent py-1.5 focus:ring-0 dark:text-white">
                                        <option value="">{{ __('Select Account…') }}</option>
                                        @foreach($accounts as $acc)
                                            <option value="{{ $acc->id }}">{{ $acc->code ? $acc->code . ' – ' : '' }}{{ $acc->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-4 py-2">
                                    <input type="text" :name="'lines['+index+'][description]'" x-model="line.description"
                                        placeholder="{{ __('Optional note…') }}"
                                        class="w-full text-sm border-0 bg-transparent py-1.5 focus:ring-0 dark:text-white placeholder-slate-300" />
                                </td>
                                <td class="px-4 py-2">
                                    <input type="number" step="0.01" min="0" :name="'lines['+index+'][debit]'" x-model.number="line.debit"
                                        @input="line.credit = line.debit > 0 ? 0 : line.credit"
                                        class="w-full text-sm text-right border-0 bg-transparent py-1.5 focus:ring-0 dark:text-white placeholder-slate-300"
                                        placeholder="0.00" />
                                </td>
                                <td class="px-4 py-2">
                                    <input type="number" step="0.01" min="0" :name="'lines['+index+'][credit]'" x-model.number="line.credit"
                                        @input="line.debit = line.credit > 0 ? 0 : line.debit"
                                        class="w-full text-sm text-right border-0 bg-transparent py-1.5 focus:ring-0 dark:text-white placeholder-slate-300"
                                        placeholder="0.00" />
                                </td>
                                <td class="px-4 py-2 text-center">
                                    <button type="button" @click="removeLine(line.id)"
                                        class="p-1.5 text-slate-400 hover:text-red-500 rounded hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors opacity-0 group-hover:opacity-100"
                                        x-show="lines.length > 2">
                                        <x-tabler-trash class="size-4" />
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                    <tfoot class="bg-slate-50 dark:bg-slate-900/30 border-t border-slate-200 dark:border-slate-700">
                        <tr>
                            <td colspan="2" class="px-4 py-3">
                                <button type="button" @click="addLine()"
                                    class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 uppercase tracking-wide">
                                    + {{ __('Add Row') }}
                                </button>
                            </td>
                            <td class="px-4 py-3 text-right font-bold text-sm"
                                :class="totalsMatch ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400'"
                                x-text="formatCurrency(totalDebit)"></td>
                            <td class="px-4 py-3 text-right font-bold text-sm"
                                :class="totalsMatch ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400'"
                                x-text="formatCurrency(totalCredit)"></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="text-xs font-semibold uppercase tracking-wider"
                    :class="totalsMatch && totalDebit > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-500'">
                    <span x-show="!totalsMatch || totalDebit === 0">⚠ {{ __('Credits and debits must balance before posting.') }}</span>
                    <span x-show="totalsMatch && totalDebit > 0">✓ {{ __('Entry is balanced.') }}</span>
                </p>
                <x-button type="submit" :disabled="!totalsMatch || totalDebit === 0">{{ __('Post Journal Entry') }}</x-button>
            </div>
        </form>
    </div>

    {{-- Entries List --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden shadow-sm">
        <table class="w-full text-sm text-left">
            <thead class="bg-slate-50 dark:bg-slate-900/50 text-xs font-semibold text-slate-500 uppercase tracking-wider border-b border-slate-200 dark:border-slate-700">
                <tr>
                    <th class="px-5 py-4">{{ __('Reference & Date') }}</th>
                    <th class="px-5 py-4 w-1/2">{{ __('Lines') }}</th>
                    <th class="px-5 py-4 text-right">{{ __('Debit') }}</th>
                    <th class="px-5 py-4 text-right">{{ __('Credit') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                @forelse($entries as $entry)
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                    <td class="px-5 py-4 align-top">
                        <div class="font-bold text-slate-900 dark:text-white">{{ $entry->reference }}</div>
                        <div class="text-[10px] text-slate-500 font-semibold uppercase mt-1">{{ $entry->date->format('M d, Y') }}</div>
                        @if($entry->description)
                            <div class="text-[11px] text-slate-400 mt-1 italic">{{ $entry->description }}</div>
                        @endif
                    </td>
                    <td class="px-5 py-4 align-top">
                        @foreach($entry->lines as $line)
                        <div class="flex justify-between text-[11px] py-0.5 {{ $line->credit > 0 ? 'pl-5' : '' }}">
                            <span class="font-medium text-slate-700 dark:text-slate-300">
                                {{ $line->account?->name ?? __('Unknown') }}
                                @if($line->description)
                                    <span class="text-slate-400"> – {{ $line->description }}</span>
                                @endif
                            </span>
                        </div>
                        @endforeach
                    </td>
                    <td class="px-5 py-4 align-top text-right">
                        @foreach($entry->lines as $line)
                        <div class="text-[11px] py-0.5 {{ $line->debit > 0 ? 'font-semibold text-slate-900 dark:text-white' : 'text-transparent select-none' }}">
                            {{ $line->debit > 0 ? number_format($line->debit, 2) : '–' }}
                        </div>
                        @endforeach
                    </td>
                    <td class="px-5 py-4 align-top text-right">
                        @foreach($entry->lines as $line)
                        <div class="text-[11px] py-0.5 {{ $line->credit > 0 ? 'font-semibold text-slate-900 dark:text-white' : 'text-transparent select-none' }}">
                            {{ $line->credit > 0 ? number_format($line->credit, 2) : '–' }}
                        </div>
                        @endforeach
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-5 py-16 text-center text-slate-400">
                        <x-tabler-book class="size-12 text-slate-300 dark:text-slate-600 mx-auto mb-3" />
                        <p class="font-semibold uppercase tracking-widest text-[10px]">{{ __('No journal entries yet') }}</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        @if($entries->hasPages())
        <div class="px-5 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50">
            {{ $entries->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('journalEntryForm', () => ({
        showForm: false,
        lines: [
            { id: 1, account_id: '', description: '', debit: 0, credit: 0 },
            { id: 2, account_id: '', description: '', debit: 0, credit: 0 },
        ],
        nextId: 3,
        addLine() {
            this.lines.push({ id: this.nextId++, account_id: '', description: '', debit: 0, credit: 0 });
        },
        removeLine(id) {
            if (this.lines.length > 2) {
                this.lines = this.lines.filter(l => l.id !== id);
            }
        },
        get totalDebit() {
            return this.lines.reduce((s, l) => s + (parseFloat(l.debit) || 0), 0);
        },
        get totalCredit() {
            return this.lines.reduce((s, l) => s + (parseFloat(l.credit) || 0), 0);
        },
        get totalsMatch() {
            return Math.abs(this.totalDebit - this.totalCredit) < 0.01;
        },
        formatCurrency(val) {
            return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(val);
        },
    }));
});
</script>
@endpush
