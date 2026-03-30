<?php
namespace Modules\Engineerings\Observers;

use App\Traits\UnitTypeSaveTrait;
use Exception;
use App\Models\UniversalSearch;
use Modules\Engineerings\Entities\WorkRequest;
use Modules\Engineerings\Entities\WorkItems;
use Modules\Engineerings\Entities\WorkServices;

class WorkRequestObserver
{
    use UnitTypeSaveTrait;

    public function saving(WorkRequest $wr)
    {
        if (!isRunningInConsoleOrSeeding()) {
            if (company()) {
                $wr->company_id = company()->id;
            }
        }
    }

    public function creating(WorkRequest $wr)
    {

        if (!isRunningInConsoleOrSeeding()) {

            if (request()->type && request()->type == 'draft') {
                $wr->status = 'draft';
            }
        }

        if (company()) {
            $wr->company_id = company()->id;
        }
    }

    public function created(WorkRequest $wr)
    {
        if (!isRunningInConsoleOrSeeding()) {
            if (!empty(request()->items_id) || !empty(request()->services_id)) {
                $items_id      = request()->items_id;
                $item_qty      = request()->items_qty;
                $item_harga    = request()->items_harga;
                $item_tax      = request()->items_tax;
                $services_id   = request()->services_id;
                $service_qty   = request()->services_qty;
                $service_harga = request()->services_harga;
                $service_tax   = request()->services_tax;

                foreach ($items_id as $key => $item_id): 
                    if (!is_null($item_id)) {
                        $workItems = WorkItems::create(
                            [
                                'workrequest_id' => $wr->id,
                                'qty'            => $item_qty[$key],
                                'items_id'       => $item_id,
                                'harga'          => $item_harga[$key],
                                'tax'            => $item_tax[$key],
                            ]
                        );
                    }
                endforeach;

                foreach ($services_id as $key => $service_id): 
                    if (!is_null($item_id)) {
                        $workServices = WorkServices::create(
                            [
                                'workrequest_id' => $wr->id,
                                'qty'            => $service_qty[$key],
                                'services_id'    => $service_id,
                                'harga'          => $service_harga[$key],
                                'tax'            => $service_tax[$key],
                            ]
                        );
                    }
                endforeach;
            }
        }
        $wr->saveQuietly();
    }

    public function deleting(WorkRequest $wr)
    {
        $universalSearches = UniversalSearch::where('searchable_id', $wr->id)->where('module_type', 'workrequest')->get();
        if ($universalSearches) {
            foreach ($universalSearches as $universalSearch) {
                UniversalSearch::destroy($universalSearch->id);
            }
        }

    }

}


