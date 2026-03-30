<?php
namespace Modules\FixedAssets\Database\Seeders;
use Illuminate\Database\Seeder; use Illuminate\Support\Facades\DB; use Illuminate\Support\Facades\Schema;
class MenuSeeder extends Seeder {
  public function run(): void {
    $target = null;
    if (Schema::hasTable('admin_menu')) $target = 'admin_menu';
    elseif (Schema::hasTable('menus')) $target = 'menus';
    elseif (Schema::hasTable('menu_items')) $target = 'menu_items';
    if (!$target) return;
    $exists = $target === 'menu_items' ? DB::table($target)->where('url','/fixedassets')->exists()
                                       : DB::table($target)->where('slug','fixedassets')->exists();
    if (!$exists) {
      if ($target === 'menu_items') DB::table('menu_items')->insert(['title'=>'Fixed Assets','url'=>'/fixedassets','icon':'asset','sort':92]);
      else DB::table($target)->insert(['title':'Fixed Assets','slug':'fixedassets','icon':'asset','url':'/fixedassets','sort':92]);
    }
  }
}
