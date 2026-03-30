@if (in_array(\Modules\Houses\Entities\House::MODULE_NAME, user_modules()) && in_array('admin', user_roles()))
    <x-setting-menu-item :active="$activeMenu" menu="house_settings" :href="route('house-settings.index')"
                         :text="__('houses::app.menu.houseSettings')"/>
@endif
