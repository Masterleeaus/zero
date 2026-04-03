<x-layouts.admin>
    <x-slot:header>Branch Management</x-slot:header>

    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-bold text-slate-800 dark:text-white">Store Locations</h2>
            <a href="{{ route('admin.branches.create') }}" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors shadow-sm">
                Add New Branch
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach($branches as $branch)
            <div class="bg-white dark:bg-slate-800 rounded-xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm relative overflow-hidden group hover:shadow-md transition-shadow">
                @if($branch->is_primary)
                <div class="absolute top-0 right-0 px-3 py-1 bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 text-xs font-semibold rounded-bl-lg">Primary</div>
                @endif

                <div class="w-12 h-12 bg-blue-50 dark:bg-blue-900/20 rounded-lg flex items-center justify-center mb-4 transition-transform group-hover:scale-105">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                </div>

                <h3 class="text-lg font-bold text-slate-800 dark:text-white mb-1">{{ $branch->name }}</h3>
                <p class="text-sm text-slate-500 mb-4">{{ $branch->address }}</p>

                <div class="space-y-2 border-t border-slate-100 dark:border-slate-700 pt-4 mb-6">
                    <div class="flex items-center text-sm text-slate-500">
                        <svg class="w-4 h-4 mr-2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                        {{ $branch->phone ?? 'N/A' }}
                    </div>
                </div>

                <div class="flex gap-3">
                    <a href="{{ route('admin.branches.edit', $branch) }}" class="flex-1 py-2 text-center text-sm font-medium text-slate-600 hover:text-slate-800 bg-slate-50 hover:bg-slate-100 dark:bg-slate-700/50 dark:text-slate-300 dark:hover:bg-slate-700 rounded-lg transition-colors">Edit</a>
                    <form action="{{ route('admin.branches.destroy', $branch) }}" method="POST" class="flex-1">
                        @csrf @method('DELETE')
                        <button type="submit" class="w-full py-2 text-center text-sm font-medium text-red-600 hover:text-red-700 bg-red-50 hover:bg-red-100 dark:bg-red-500/10 dark:text-red-400 dark:hover:bg-red-500/20 rounded-lg transition-colors" onclick="return confirm('Are you sure you want to delete this branch?');">Delete</button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</x-layouts.admin>
