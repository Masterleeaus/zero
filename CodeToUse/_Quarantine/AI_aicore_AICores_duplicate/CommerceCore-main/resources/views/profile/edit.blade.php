<x-layouts.admin title="Profile Settings" header="Profile Settings">
    <div class="space-y-8 animate-in fade-in slide-in-from-bottom-6 duration-700">
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            {{-- Update Profile Info --}}
            <div class="bg-white dark:bg-slate-900 p-8 md:p-12 rounded-[3rem] border border-slate-200/60 dark:border-slate-800 shadow-sm relative overflow-hidden group">
                <div class="absolute -right-16 -top-16 w-48 h-48 bg-blue-500/5 blur-3xl rounded-full"></div>
                <div class="relative z-10 w-full">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            {{-- Update Password --}}
            <div class="bg-white dark:bg-slate-900 p-8 md:p-12 rounded-[3rem] border border-slate-200/60 dark:border-slate-800 shadow-sm relative overflow-hidden group">
                <div class="absolute -right-16 -top-16 w-48 h-48 bg-emerald-500/5 blur-3xl rounded-full"></div>
                <div class="relative z-10 w-full">
                    @include('profile.partials.update-password-form')
                </div>
            </div>
        </div>

        {{-- Danger Zone --}}
        <div class="bg-rose-500/5 dark:bg-rose-950/10 p-8 md:p-12 rounded-[3rem] border border-rose-100 dark:border-rose-900/30 overflow-hidden">
            <div class="relative z-10 w-full">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</x-layouts.admin>
