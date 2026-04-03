<x-layouts.admin>
    <x-slot:header>Move Stock Between Branches</x-slot:header>

    <div class="max-w-3xl mx-auto py-10">
        <div class="bg-white dark:bg-surface-800 p-8 rounded-3xl border border-surface-200 dark:border-surface-700 shadow-xl shadow-surface-500/10">
            <h2 class="text-xl font-bold text-surface-800 dark:text-white uppercase mb-8 italic italic underline decoration-indigo-500 decoration-4 underline-offset-8 text-center sm:text-left">Branch Inventory Transfer</h2>
            
            <form action="{{ route('admin.inventory-transfers.store') }}" method="POST" class="space-y-6">
                @csrf
                
                <div>
                    <label class="block text-[10px] font-black uppercase text-surface-400 mb-2">Select Product to Move</label>
                    <select name="product_id" required class="w-full bg-surface-50 dark:bg-surface-900 border-surface-100 dark:border-surface-700 rounded-xl text-xs font-bold focus:ring-indigo-500 focus:border-indigo-500 dark:text-white">
                        <option value="">-- Choose Product --</option>
                        @foreach($products as $product)
                        <option value="{{ $product->id }}">{{ $product->name }} (Total SKU: {{ $product->stock }})</option>
                        @endforeach
                    </select>
                    @error('product_id') <p class="text-rose-500 text-[10px] mt-1 italic font-bold uppercase">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 p-6 bg-surface-50 dark:bg-surface-900/50 rounded-2xl border border-surface-100 dark:border-surface-700 relative">
                    <div class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 hidden md:block">
                        <svg class="w-10 h-10 text-surface-200 dark:text-surface-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black uppercase text-surface-400 mb-2 italic">Source (From)</label>
                        <select name="from_branch_id" required class="w-full bg-white dark:bg-surface-800 border-surface-100 dark:border-surface-700 rounded-xl text-[10px] font-black uppercase focus:ring-rose-500 focus:border-rose-500 dark:text-white">
                            @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                        @error('from_branch_id') <p class="text-rose-500 text-[10px] mt-1 italic font-bold uppercase">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-[10px] font-black uppercase text-surface-400 mb-2 italic text-right">Destination (To)</label>
                        <select name="to_branch_id" required class="w-full bg-white dark:bg-surface-800 border-surface-100 dark:border-surface-700 rounded-xl text-[10px] font-black uppercase focus:ring-indigo-500 focus:border-indigo-500 dark:text-white">
                            @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                        @error('to_branch_id') <p class="text-rose-500 text-[10px] mt-1 italic font-bold uppercase">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-end">
                    <div>
                        <label class="block text-[10px] font-black uppercase text-surface-400 mb-2">Transfer Quantity</label>
                        <input type="number" name="quantity" min="1" required class="w-full bg-surface-50 dark:bg-surface-900 border-surface-100 dark:border-surface-700 rounded-xl text-xs font-bold focus:ring-indigo-500 focus:border-indigo-500 dark:text-white">
                        @error('quantity') <p class="text-rose-500 text-[10px] mt-1 italic font-bold uppercase">{{ $message }}</p> @enderror
                    </div>
                    <div class="text-xs font-bold text-surface-400 italic">
                        * Moving stock reduces availability in Source and increases in Destination instantly.
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-black uppercase text-surface-400 mb-2">Internal Notes</label>
                    <textarea name="notes" rows="2" class="w-full bg-surface-50 dark:bg-surface-900 border-surface-100 dark:border-surface-700 rounded-xl text-xs font-bold focus:ring-indigo-500 focus:border-indigo-500 dark:text-white" placeholder="Reason for transfer..."></textarea>
                </div>

                <div class="pt-6 border-t border-surface-50 dark:border-surface-700">
                    <button type="submit" class="w-full py-4 bg-indigo-600 text-white text-xs font-black rounded-2xl shadow-xl shadow-indigo-500/20 hover:scale-[1.02] transition-all uppercase tracking-widest">VALIDATE & EXECUTE X-FER</button>
                    <a href="{{ route('admin.inventory-transfers.index') }}" class="block text-center text-[10px] font-bold text-surface-400 uppercase mt-4 hover:underline">Cancel process</a>
                </div>
            </form>
        </div>
    </div>
</x-layouts.admin>
