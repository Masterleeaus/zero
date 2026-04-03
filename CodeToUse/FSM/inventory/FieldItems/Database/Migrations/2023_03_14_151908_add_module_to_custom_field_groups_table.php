<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\CustomFieldGroup;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        $data = [
            'company_id' => 1,
            'name' => 'Item',
            'model' => 'Modules\FieldItems\Entities\Item'
        ];

        CustomFieldGroup::create($data);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        CustomFieldGroup::where('name', 'Item')->delete();
    }
};
