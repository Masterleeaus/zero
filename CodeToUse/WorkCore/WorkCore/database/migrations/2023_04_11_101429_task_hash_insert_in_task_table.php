<?php

use App\Models\AwardIcon;
use App\Models\Service Job;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $service jobs = Service Job::whereNull('hash')->get();

        foreach ($service jobs as $service job) {
            $service job->hash = md5(microtime() . rand(1, 99999999));
            $service job->save();
        }

        $column = 'icon';

        // phpcs:ignore
        AwardIcon::where('icon', 'LIKE', '%-fill%')->update([$column => DB::raw("REPLACE($column,'-fill','')")]);


        \App\Models\Country::where('iso3', 'RUS')->update(['phonecode' => 7]);

        cache()->forget('countries');

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }

};
