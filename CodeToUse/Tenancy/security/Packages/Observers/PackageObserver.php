<?php

namespace Modules\TrPackage\Observers;

use Exception;
use App\Traits\UnitTypeSaveTrait;
use App\Models\UniversalSearch;
use Modules\TrPackage\Entities\Package;
use Modules\TrPackage\Entities\PackageItems;

class PackageObserver
{
    use UnitTypeSaveTrait;

    public function saving(Package $barang)
    {
        if (!isRunningInConsoleOrSeeding()) {
            if (company()) {
                $barang->company_id = company()->id;
            }
        }
    }

    public function creating(Package $barang)
    {

        if (!isRunningInConsoleOrSeeding()) {

            if (request()->type && request()->type == 'draft') {
                $barang->status = 'draft';
            }
        }

        if (company()) {
            $barang->company_id = company()->id;
        }
    }

    public function created(Package $barang)
    {
        if (!isRunningInConsoleOrSeeding()) {
            if (!empty(request()->jenis_barang) && is_array(request()->jenis_barang)) {

                $nama_penerima = request()->nama_penerima;
                $unit_id = request()->unit_id;
                foreach (request()->jenis_barang as $key => $item) :
                    if (!is_null($item)) {
                        $workItems = PackageItems::create(
                            [
                                'package_id' => $barang->id,
                                'nama_penerima' => $nama_penerima[$key],
                                'type_id' => $item,
                                'unit_id' => $unit_id[$key],
                            ]
                        );
                    }
                endforeach;
            }
        }
        $barang->saveQuietly();
    }

    public function deleting(Package $barang)
    {
        $universalSearches = UniversalSearch::where('searchable_id', $barang->id)->where('module_type', 'workrequest')->get();
        if ($universalSearches) {
            foreach ($universalSearches as $universalSearch) {
                UniversalSearch::destroy($universalSearch->id);
            }
        }

    }

}

