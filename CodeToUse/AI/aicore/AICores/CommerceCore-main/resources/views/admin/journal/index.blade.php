<x-layouts.admin>
    <x-slot:header>Journal Entries</x-slot:header>

    <div x-data="journalEntryForm()" class="space-y-6">
        {{-- Header Actions --}}
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-slate-900 dark:text-white">General Journal</h2>
            <button @click="showForm = !showForm" class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg shadow-sm transition-colors focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 dark:focus:ring-offset-slate-900" :class="showForm ? 'bg-slate-600 hover:bg-slate-700' : 'bg-blue-600 hover:bg-blue-700'">
                <span x-text="showForm ? 'Cancel Entry' : '+ New Journal Entry'"></span>
            </button>
        </div>

        {{-- Create Journal Entry Form (Toggleable) --}}
        <div x-show="showForm" x-collapse x-cloak class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm">
            <form action="{{ route('admin.journal.store') }}" method="POST">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div>
                        <label class="block text-xs text-slate-500 mb-1 font-bold uppercase tracking-wider">Date</label>
                        <input type="date" name="date" value="{{ date('Y-m-d') }}" required class="w-full text-sm border-slate-200 dark:border-slate-700 rounded-lg dark:bg-slate-900 dark:text-white transition-colors focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-500 mb-1 font-bold uppercase tracking-wider">Reference #</label>
                        <input type="text" name="reference" value="JE-{{ date('Ymd') }}-{{ rand(100,999) }}" required class="w-full text-sm border-slate-200 dark:border-slate-700 rounded-lg dark:bg-slate-900 dark:text-white transition-colors focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-500 mb-1 font-bold uppercase tracking-wider">Description</label>
                        <input type="text" name="description" placeholder="Record purpose..." class="w-full text-sm border-slate-200 dark:border-slate-700 rounded-lg dark:bg-slate-900 dark:text-white transition-colors focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                    </div>
                </div>

                {{-- Lines --}}
                <div class="border rounded-xl border-slate-200 dark:border-slate-700 overflow-hidden mb-6 shadow-sm">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-slate-50 dark:bg-slate-900/50 text-xs font-semibold text-slate-500 uppercase tracking-wider border-b border-slate-200 dark:border-slate-700">
                            <tr>
                                <th class="px-4 py-3 w-1/3">Account</th>
                                <th class="px-4 py-3 w-1/3">Line Description</th>
                                <th class="px-4 py-3 text-right w-32">Debit</th>
                                <th class="px-4 py-3 text-right w-32">Credit</th>
                                <th class="px-4 py-3 text-center w-16"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(line, index) in lines" :key="line.id">
                                <tr class="border-b border-slate-100 dark:border-slate-700/50 last:border-0 hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors group">
                                    <td class="px-4 py-2">
                                        <select :name="'lines['+index+'][account_id]'" x-model="line.account_id" required class="w-full text-sm border-0 bg-transparent py-1.5 focus:ring-0 dark:text-white">
                                            <option value="">Select Account...</option>
                                            @foreach($accounts as $acc)
                                                <option value="{{ $acc->id }}">{{ $acc->code ? $acc->code.' - ' : '' }}{{ $acc->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="px-4 py-2">
                                        <input type="text" :name="'lines['+index+'][description]'" x-model="line.description" placeholder="Optional note..." class="w-full text-sm border-0 bg-transparent py-1.5 focus:ring-0 dark:text-white placeholder-slate-300">
                                    </td>
                                    <td class="px-4 py-2">
                                        <input type="number" step="0.01" min="0" :name="'lines['+index+'][debit]'" x-model.number="line.debit" @input="line.credit = line.debit > 0 ? 0 : line.credit" class="w-full text-sm text-right border-0 bg-transparent py-1.5 focus:ring-0 dark:text-white placeholder-slate-300" placeholder="0.00">
                                    </td>
                                    <td class="px-4 py-2">
                                        <input type="number" step="0.01" min="0" :name="'lines['+index+'][credit]'" x-model.number="line.credit" @input="line.debit = line.credit > 0 ? 0 : line.debit" class="w-full text-sm text-right border-0 bg-transparent py-1.5 focus:ring-0 dark:text-white placeholder-slate-300" placeholder="0.00">
                                    </td>
                                    <td class="px-4 py-2 text-center">
                                        <button type="button" @click="removeLine(line.id)" class="p-1.5 text-slate-400 hover:text-red-500 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors opacity-0 group-hover:opacity-100" x-show="lines.length > 2">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                        <tfoot class="bg-slate-50 dark:bg-slate-900/30 border-t border-slate-200 dark:border-slate-700">
                            <tr>
                                <td colspan="2" class="px-4 py-3">
                                    <button type="button" @click="addLine()" class="text-xs font-bold text-blue-600 dark:text-blue-400 hover:text-blue-700 transition-colors tracking-wide uppercase">+ Add Row</button>
                                </td>
                                <td class="px-4 py-3 text-right font-bold" :class="totalsMatch ? 'text-emerald-600' : 'text-red-600'" x-text="formatCurrency(totalDebit)"></td>
                                <td class="px-4 py-3 text-right font-bold" :class="totalsMatch ? 'text-emerald-600' : 'text-red-600'" x-text="formatCurrency(totalCredit)"></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                    <div class="text-xs font-bold uppercase tracking-wider" :class="totalsMatch ? 'text-emerald-500' : 'text-red-500'">
                        <span x-show="!totalsMatch">⚠ Credits and Debits must balance to zero deviation.</span>
                        <span x-show="totalsMatch && totalDebit > 0">✓ Entry is perfectly balanced.</span>
                    </div>
                    <button type="submit" class="px-8 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg shadow-sm transition-all focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 dark:focus:ring-offset-slate-900 disabled:opacity-50 disabled:cursor-not-allowed uppercase text-xs tracking-widest" :disabled="!totalsMatch || totalDebit === 0">Post Journal Entry</button>
                </div>
            </form>
        </div>

        {{-- Journal Entries List --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left border-collapse">
                    <thead class="bg-slate-50 dark:bg-slate-900/50 text-xs font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200 dark:border-slate-700">
                        <tr>
                            <th class="px-6 py-4">Reference & Date</th>
                            <th class="px-6 py-4 w-1/2">Line Items</th>
                            <th class="px-6 py-4 text-right">Debit</th>
                            <th class="px-6 py-4 text-right">Credit</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                        @forelse($entries as $entry)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                                <td class="px-6 py-4 align-top">
                                    <div class="font-bold text-slate-900 dark:text-white">{{ $entry->reference }}</div>
                                    <div class="text-[10px] text-slate-500 font-bold uppercase mt-1">{{ $entry->date->format('M d, Y') }}</div>
                                    @if($entry->description)
                                        <div class="text-[10px] text-slate-400 mt-2 italic leading-relaxed">{{ $entry->description }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 align-top p-0">
                                    <div class="flex flex-col gap-1 my-3">
                                        @foreach($entry->lines as $line)
                                            <div class="flex justify-between text-[11px] py-1 px-4 {{ $line->credit > 0 ? 'pl-8' : '' }} hover:bg-slate-100 dark:hover:bg-slate-700/30 rounded mx-2 transition-colors">
                                                <div class="flex items-center gap-2">
                                                    <span class="font-bold text-slate-700 dark:text-slate-300">{{ $line->account->name }}</span>
                                                    @if($line->description)
                                                        <span class="text-slate-400 font-medium"> - {{ $line->description }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-6 py-4 align-top text-right">
                                    <div class="flex flex-col gap-1 mt-3">
                                        @foreach($entry->lines as $line)
                                            <div class="text-[11px] py-1 {{ $line->debit > 0 ? 'font-bold text-slate-900 dark:text-white' : 'text-transparent' }}">
                                                {{ $line->debit > 0 ? number_format($line->debit, 2) : '-' }}
                                            </div>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-6 py-4 align-top text-right">
                                    <div class="flex flex-col gap-1 mt-3">
                                        @foreach($entry->lines as $line)
                                            <div class="text-[11px] py-1 {{ $line->credit > 0 ? 'font-bold text-slate-900 dark:text-white' : 'text-transparent' }}">
                                                {{ $line->credit > 0 ? number_format($line->credit, 2) : '-' }}
                                            </div>
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-slate-400">
                                    <svg class="w-12 h-12 mx-auto text-slate-300 dark:text-slate-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                                    <p class="font-bold uppercase tracking-widest text-[10px]">No historical journal records</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($entries->hasPages())
                <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50">
                    {{ $entries->links() }}
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('journalEntryForm', () => ({
                showForm: false,
                lines: [
                    { id: Date.now(), account_id: '', description: '', debit: '', credit: '' },
                    { id: Date.now() + 1, account_id: '', description: '', debit: '', credit: '' }
                ],
                addLine() {
                    this.lines.push({ id: Date.now(), account_id: '', description: '', debit: '', credit: '' });
                },
                removeLine(id) {
                    if (this.lines.length > 2) {
                        this.lines = this.lines.filter(l => l.id !== id);
                    }
                },
                get totalDebit() {
                    return this.lines.reduce((sum, line) => sum + (parseFloat(line.debit) || 0), 0);
                },
                get totalCredit() {
                    return this.lines.reduce((sum, line) => sum + (parseFloat(line.credit) || 0), 0);
                },
                get totalsMatch() {
                    return Math.abs(this.totalDebit - this.totalCredit) < 0.01;
                },
                formatCurrency(val) {
                    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(val);
                }
            }));
        });
    </script>
    @endpush
</x-layouts.admin>
