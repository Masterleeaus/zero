<?php

use App\Models\Site;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */

    public function up()
    {
        if (!Schema::hasColumn('sites', 'project_short_code')) {
            Schema::table('sites', function (Blueprint $table) {
                $table->string('project_short_code')->after('project_name')->nullable();
            });

            $sites = Site::withTrashed()->select(['project_name', 'id'])->get();

            foreach ($sites as $site) {
                try {

                    $site->project_short_code = $this->initials($site->project_name);
                    $site->saveQuietly();
                // @codingStandardsIgnoreLine
                }catch (\Exception $e){
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */

    public function down()
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->dropColumn('project_short_code');
        });
    }

    protected function initials($str): string
    {
        $str = preg_replace('/\s+/', ' ', $str);
        $ret = '';

        $array = explode(' ', $str);

        if (count($array) === 1 || count($array) === 0) {
            return $this->clean(strtoupper(substr($str, -4)));
        }

        foreach ($array as $word) {
            $ret .= strtoupper($word[0]);
        }

        return $this->clean($ret);
    }

    protected function clean($string) {
        $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
        $string = preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.

        return preg_replace('/-+/', '-', $string); // Replaces multiple hyphens with single one.
    }

};
