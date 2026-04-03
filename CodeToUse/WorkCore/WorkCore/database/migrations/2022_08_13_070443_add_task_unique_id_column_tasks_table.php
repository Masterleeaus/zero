<?php

use App\Models\Site;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */

    public function up()
    {
        if (!Schema::hasColumn('service jobs', 'task_short_code')) {
            Schema::table('service jobs', function (Blueprint $table) {
                $table->string('task_short_code')->after('id')->nullable();
            });
        }

        $sites = Site::whereHas('service jobs')->get();

        foreach ($sites as $value) {
            // phpcs:ignore
            DB::statement("UPDATE service jobs SET task_short_code = CONCAT( '$value->project_short_code', '-', id ) WHERE project_id = '" . $value->id . "'; ");
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('service jobs', function (Blueprint $table) {
            $table->dropColumn('task_short_code');
        });
    }

};
