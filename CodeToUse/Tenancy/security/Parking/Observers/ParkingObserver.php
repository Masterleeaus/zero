<?php
namespace Modules\Parking\Observers;

use App\Traits\UnitTypeSaveTrait;
use Exception;
use App\Models\UniversalSearch;
use Modules\Parking\Entities\Parking;
use Modules\Parking\Entities\ParkingItems;

class ParkingObserver
{
    use UnitTypeSaveTrait;

    public function saving(Parking $parkir)
    {
        // $this->unitType($parkir);
        if (!isRunningInConsoleOrSeeding()) {
            if (company()) {
                $parkir->company_id = company()->id;
            }
        }
    }

    public function creating(Parking $parkir)
    {

        if (!isRunningInConsoleOrSeeding()) {

            if (request()->type && request()->type == 'draft') {
                $parkir->status = 'draft';
            }
        }

        if (company()) {
            $parkir->company_id = company()->id;
        }
    }

    public function created(Parking $parkir)
    {
        if (!isRunningInConsoleOrSeeding()) {
            if (!empty(request()->item_name) && is_array(request()->item_name)) {

                $jenis_kendaraan = request()->jenis_kendaraan;
                $no_plat_lama = request()->no_plat_lama;
                $no_plat_baru = request()->no_plat_baru;
                $cost_per_item = request()->cost_per_item;

                foreach (request()->item_name as $key => $item) :
                    if (!is_null($item)) {
                        $invoiceItem = ParkingItems::create(
                            [
                                'parkir_id' => $parkir->id,
                                'jenis_kendaraan' => $jenis_kendaraan[$key],
                                'jumlah_periode' => $item,
                                'no_plat_lama' => $no_plat_lama[$key],
                                'no_plat_baru' => $no_plat_baru[$key],
                                'biaya' => $cost_per_item[$key]
                            ]
                        );
                    }
                endforeach;
            }
        }
        $parkir->saveQuietly();
    }

    public function deleting(Parking $parkir)
    {
        $universalSearches = UniversalSearch::where('searchable_id', $parkir->id)->where('module_type', 'journal')->get();

        if ($universalSearches) {
            foreach ($universalSearches as $universalSearch) {
                UniversalSearch::destroy($universalSearch->id);
            }
        }

    }

}


