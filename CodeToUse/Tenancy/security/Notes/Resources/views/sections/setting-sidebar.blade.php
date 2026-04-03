@if (in_array(\Modules\TrNotes\Entities\Notes::MODULE_NAME, user_modules()) && in_array('admin', user_roles()))
    <x-setting-menu-item :active="$activeMenu" menu="tenancy_settings" :href="route('tenancy-settings.index')"
                         :text="__('Tenancy Settings')"/>
@endif
