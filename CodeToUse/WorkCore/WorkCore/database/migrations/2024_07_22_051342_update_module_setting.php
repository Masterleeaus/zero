<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\ModuleSetting;

return new class extends Migration {

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        ModuleSetting::where('type', 'customer')->where('module_name', 'customers')->delete();
    }

};
