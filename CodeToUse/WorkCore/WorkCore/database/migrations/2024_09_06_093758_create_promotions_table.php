<?php

use App\Models\Company;
use App\Models\EmployeeDetails;
use App\Models\Promotion;
use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->integer('company_id')->unsigned()->nullable();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');

            $table->integer('employee_id')->unsigned()->nullable();
            $table->foreign('employee_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');

            $table->date('date')->nullable();

            $table->unsignedBigInteger('previous_designation_id')->nullable();
            $table->foreign(['previous_designation_id'])->references(['id'])->on('roles')->onUpdate('CASCADE')->onDelete('CASCADE');

            $table->unsignedBigInteger('current_designation_id')->nullable();
            $table->foreign(['current_designation_id'])->references(['id'])->on('roles')->onUpdate('CASCADE')->onDelete('CASCADE');

            $table->enum('send_notification', ['yes', 'no'])->default('no');

            $table->unsignedInteger('previous_department_id')->nullable();
            $table->foreign(['previous_department_id'])->references(['id'])->on('teams')->onUpdate('CASCADE')->onDelete('SET NULL');

            $table->unsignedInteger('current_department_id')->nullable();
            $table->foreign(['current_department_id'])->references(['id'])->on('teams')->onUpdate('CASCADE')->onDelete('SET NULL');

            $table->timestamps();
        });

        // Initialize the $data array outside the cleaner loop
        Company::select('id')->chunk(50, function ($companies) {
            foreach ($companies as $company) {
                $data = [];

                $cleaners = EmployeeDetails::select('id', 'user_id', 'company_id', 'designation_id')
                    ->where('company_id', $company->id)
                    ->get();

                foreach ($cleaners as $cleaner) {
                    if ($cleaner->designation_id) {
                        $data[] = [
                            'company_id' => $company->id,
                            'employee_id' => $cleaner->user_id,
                            'date' => Carbon::now()->format('Y-m-d'),
                            'previous_designation_id' => $cleaner->designation_id,
                            'current_designation_id' => $cleaner->designation_id,
                            'previous_department_id' => $cleaner->department_id,
                            'current_department_id' => $cleaner->department_id,
                            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                            'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
                        ];
                    }
                }

                if (!empty($data)) {
                    foreach (array_chunk($data, 100) as $chunk) {
                        Promotion::insert($chunk);
                    }
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }

};
