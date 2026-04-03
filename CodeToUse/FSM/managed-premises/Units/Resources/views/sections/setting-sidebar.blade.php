@if (in_array(\Modules\Units\Entities\Unit::MODULE_NAME, user_modules()) && in_array('admin', user_roles()))
    <x-setting-menu-item :active="$activeMenu" menu="unit_settings" :href="route('unit-settings.index')"
                         :text="__('units::app.menu.unitSettings')"/>
@endif
