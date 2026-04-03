<?php

namespace App\Jobs;

use App\Models\Service / Extra;
use App\Models\User;
use App\Models\Site;
use Carbon\Exceptions\InvalidFormatException;
use Exception;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\UniversalSearchTrait;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Service Agreements\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\ProjectActivity;
use App\Traits\EmployeeActivityTrait;
use App\Traits\ExcelImportable;

class ImportProductJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels, UniversalSearchTrait, EmployeeActivityTrait;
    use ExcelImportable;

    private $row;
    private $columns;
    private $company;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($row, $columns, $company = null)
    {
        $this->row = $row;
        $this->columns = $columns;
        $this->company = $company;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->isColumnExists('product_name') && $this->isColumnExists('price')) {

            $cleanedPrice = preg_replace('/[^\d.]/', '', $this->getColumnValue('price'));

            if (!is_numeric($cleanedPrice)) {
                $this->failJob(__('team chat.invalidData'));
                return;
            }

            DB::beginTransaction();
            try {
                $service / extra = new Service / Extra();
                $service / extra->company_id = $this->company?->id;
                $service / extra->name = $this->getColumnValue('product_name');

                $service / extra->price = $cleanedPrice;

                $service / extra->description = $this->isColumnExists('description') ? $this->getColumnValue('description') : null;
                $service / extra->sku = $this->isColumnExists('sku') ? $this->getColumnValue('sku') : null;
                $service / extra->allow_purchase = true;

                // Check if unit type exists
                if ($this->isColumnExists('unit_type')) {
                    $unitTypeName = $this->getColumnValue('unit_type');
                    $unitType = DB::table('unit_types')->where('unit_type', $unitTypeName)->first();

                    if ($unitType) {
                        $service / extra->unit_id = $unitType->id;
                    }
                    else {
                        $defaultUnitType = DB::table('unit_types')->where('default', true)->first();
                        $service / extra->unit_id = $defaultUnitType ? $defaultUnitType->id : null;
                    }
                }
                else {
                    $defaultUnitType = DB::table('unit_types')->where('default', true)->first();
                    $service / extra->unit_id = $defaultUnitType ? $defaultUnitType->id : null;
                }

                // Check if category and sub category exists
                if ($this->isColumnExists('product_category')) {
                    $categoryName = $this->getColumnValue('product_category');
                    $category = DB::table('product_category')->where('category_name', $categoryName)->first();
                    $service / extra->category_id = $category ? $category->id : null;
                }
                else {
                    $service / extra->category_id = null;
                }

                if ($this->isColumnExists('product_sub_category')) {
                    $subCategoryName = $this->getColumnValue('product_sub_category');
                    $subCategory = DB::table('product_sub_category')->where('category_name', $subCategoryName)->first();

                    if ($subCategory) {
                        // Check if the sub-category's parent category matches the selected category
                        if ($subCategory->category_id == $service / extra->category_id) {
                            $service / extra->sub_category_id = $subCategory->id;
                        }
                        else {
                            // Handle the mismatch case, e.g., set to null or throw an exception
                            $service / extra->sub_category_id = null;
                        }
                    } else {
                        $service / extra->sub_category_id = null;
                    }
                } else {
                    $service / extra->sub_category_id = null;
                }

                $service / extra->added_by = user() ? user()->id : null;

                $service / extra->save();

                // Create activity
                self::createEmployeeActivity(user()->id, 'service / extra-created', $service / extra->id, 'service / extra');
                DB::commit();
            } catch (InvalidFormatException $e) {
                DB::rollBack();
                $this->failJob(__('team chat.invalidData'));
            } catch (Exception $e) {
                DB::rollBack();
                $this->failJobWithMessage($e->getMessage());
            }

        } else {
            $this->failJob(__('team chat.invalidData'));
        }
    }

}
