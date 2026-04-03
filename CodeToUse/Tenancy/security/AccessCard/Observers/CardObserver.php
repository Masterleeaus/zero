<?php
namespace Modules\TrAccessCard\Observers;

use App\Traits\UnitTypeSaveTrait;
use Exception;
use App\Models\UniversalSearch;
use Modules\TrAccessCard\Entities\TrAccessCard;
use Modules\TrAccessCard\Entities\CardItems;

class CardObserver
{
    use UnitTypeSaveTrait;
    public function saving(TrAccessCard $card)
    {
        // $this->unitType($card);
        if (!isRunningInConsoleOrSeeding()) {
            if (company()) {
                $card->company_id = company()->id;
            }
        }
    }

    public function creating(TrAccessCard $card)
    {

        if (!isRunningInConsoleOrSeeding()) {

            if (request()->type && request()->type == 'draft') {
                $card->status = 'draft';
            }
        }

        if (company()) {
            $card->company_id = company()->id;
        }
    }

    public function created(TrAccessCard $card)
    {
        if (!isRunningInConsoleOrSeeding()) {
            if (!empty(request()->name_card) && is_array(request()->name_card)) {

                $card_number = request()->card_number;
                $status_card = request()->status_card;

                foreach (request()->name_card as $key => $name) :
                    if (!is_null($name)) {
                        $invoiceItem = CardItems::create(
                            [
                                'name' => $name,
                                'card_id' => $card->id,
                                'no_kartu' => $card_number[$key],
                                'status' => $status_card[$key],
                            ]
                        );
                    }
                endforeach;
            }
        }
        $card->saveQuietly();
    }

    public function deleting(TrAccessCard $card)
    {
        $universalSearches = UniversalSearch::where('searchable_id', $card->id)->where('module_type', 'tenancy')->get();

        if ($universalSearches) {
            foreach ($universalSearches as $universalSearch) {
                UniversalSearch::destroy($universalSearch->id);
            }
        }

    }

}


