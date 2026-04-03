<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWorkflowsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('workflows')) {
            Schema::create('workflows', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();

                /**
                 * IMPORTANT (MySQL errno:150 fix)
                 * - Worksuite/Laravel default primary keys are BIGINT UNSIGNED ($table->id()).
                 * - Foreign key columns must match referenced column type exactly.
                 */
                $table->foreignId('project_category_id')
                    ->nullable()
                    ->constrained('project_categories')
                    ->nullOnDelete();

                $table->foreignId('company_id')
                    ->constrained('companies')
                    ->cascadeOnDelete();

                $table->json('workflow_data')->nullable(); // JSON field to store workflow data
                $table->timestamps();
            });
        }
    }
    

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('workflows');
    }
}
