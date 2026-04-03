<x-layouts.admin>
    <x-slot:header>Log New Expense</x-slot:header>

    <div class="max-w-2xl mx-auto py-10">
        <div class="bg-white dark:bg-slate-800 p-8 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm">
            <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-6">Expense Details</h2>
            
            <form action="{{ route('admin.expenses.store') }}" method="POST" class="space-y-6">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Category</label>
                        <select name="category" required class="w-full text-sm border-slate-200 dark:border-slate-700 rounded-lg dark:bg-slate-900 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                            <option value="Rent">Rent</option>
                            <option value="Staff">Staff / Salaries</option>
                            <option value="Marketing">Marketing / Ads</option>
                            <option value="Logistics">Logistics / Shipping</option>
                            <option value="Supplies">Supplies / Packaging</option>
                            <option value="Utilities">Utilities / Internet</option>
                            <option value="Taxes">Taxes</option>
                            <option value="Others">Others</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Amount</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 dark:text-slate-400 font-semibold">$</span>
                            <input type="number" step="0.01" name="amount" required class="w-full pl-8 text-sm border-slate-200 dark:border-slate-700 rounded-lg dark:bg-slate-900 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Description</label>
                    <textarea name="description" rows="3" class="w-full text-sm border-slate-200 dark:border-slate-700 rounded-lg dark:bg-slate-900 dark:text-white focus:ring-blue-500 focus:border-blue-500" placeholder="e.g. Paid Facebook ad campaign for summer collection"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Expense Date</label>
                    <input type="date" name="date" value="{{ date('Y-m-d') }}" required class="w-full text-sm border-slate-200 dark:border-slate-700 rounded-lg dark:bg-slate-900 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div class="pt-6 border-t border-slate-100 dark:border-slate-700/50 flex flex-col gap-3">
                    <button type="submit" class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors shadow-sm focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 dark:focus:ring-offset-slate-800">
                        Save Expense
                    </button>
                    <a href="{{ route('admin.expenses.index') }}" class="text-center text-sm font-medium text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 transition-colors">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-layouts.admin>
