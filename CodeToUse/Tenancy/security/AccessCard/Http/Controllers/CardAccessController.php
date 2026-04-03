<?php

namespace Modules\TrAccessCard\Http\Controllers;

use Exception;
use Carbon\Carbon;
use App\Helper\Reply;
use App\Models\BaseModel;
use Illuminate\Http\Request;
use Modules\Units\Entities\Unit;
use Illuminate\Support\Facades\DB;
use Modules\TrAccessCard\Entities\TrAccessCard;
use Modules\TrAccessCard\Entities\CardItems;
use Modules\TrAccessCard\DataTables\CardDataTable;
use Modules\TrAccessCard\Http\Requests\CardRequest;
use App\Http\Controllers\AccountBaseController;

class CardAccessController extends AccountBaseController
{
    public $arr = [];

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'traccesscard::app.menu.card';
        $this->pageIcon  = 'ti-settings';
    }
      /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(CardDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_access_card');
        abort_403(in_array($viewPermission, ['none']));

        $this->card  = TrAccessCard::all();
        $this->units = Unit::all();
        return $dataTable->render('traccesscard::card.index', $this->data);
    }

      /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $viewPermission = user()->permission('add_access_card');
        abort_403(in_array($viewPermission, ['none']));

        $this->pageTitle = __('traccesscard::app.card.addCard');
        $this->units     = Unit::all();
        $this->notes     = DB::table('notes')
            ->where('table_name', 'tr_access_card')
            ->get();

        if (request()->ajax()) {
            $html = view('traccesscard::card.ajax.create', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'traccesscard::card.ajax.create';
        return view('traccesscard::card.create', $this->data);
    }

      /**
     * @param StoreRequest $request
     * @return array
     */
    public function store(CardRequest $request)
    {
        $name_card = $request->name_card;
        if (empty($name_card)) {
            return Reply::error(__('messages.addItem'));
        }

        $card             = new TrAccessCard();
        $card->date       = Carbon::createFromFormat('d-m-Y', $request->date)->format('Y-m-d');
        $card->name       = $request->input('name');
        $card->unit_id    = $request->input('unit_id');
        $card->no_hp      = $request->input('no_hp');
        $card->fee        = $request->input('charge_card');
        $card->created_by = user()->id;
        $card->save();

        $redirectUrl = urldecode($request->redirect_url);
        if ($redirectUrl == '') {
            $redirectUrl = route('card-access.index');
        }

        return Reply::successWithData(__('traccesscard::messages.addCard'), ['redirectUrl' => $redirectUrl]);
    }

      /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $viewPermission = user()->permission('view_access_card');
        abort_403(in_array($viewPermission, ['none']));

        $this->pageTitle = __('traccesscard::app.card.showCard');
        $this->card      = TrAccessCard::with('unit', 'items')->findOrFail($id);

        if (request()->ajax()) {
            $html = view('traccesscard::card.ajax.show', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'traccesscard::card.ajax.show';
        return view('traccesscard::card.create', $this->data);
    }

      /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        $viewPermission = user()->permission('edit_access_card');
        abort_403(in_array($viewPermission, ['none']));

        $this->pageTitle = __('traccesscard::app.card.editCard');
        $this->card      = TrAccessCard::with('items')->findOrFail($id);
        $this->units     = Unit::all();


        if (request()->ajax()) {
            $html = view('traccesscard::card.ajax.edit', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'traccesscard::card.ajax.edit';
        return view('traccesscard::card.create', $this->data);
    }

      /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(CardRequest $request, $id)
    {
        $editUnit = user()->permission('edit_access_card');
        abort_403($editUnit != 'all');

        $card          = TrAccessCard::find($id);
        $card->date    = Carbon::createFromFormat('d-m-Y', $request->date)->format('Y-m-d');
        $card->name    = $request->input('name');
        $card->unit_id = $request->input('unit_id');
        $card->no_hp   = $request->input('no_hp');
        $card->fee     = $request->input('charge_card');
        $card->save();

        if (!empty(request()->name_card) && is_array(request()->name_card)) {
            $names       = request()->name_card;
            $card_number = request()->card_number;
            $status_card = request()->status_card;
            $item_ids    = request()->item_ids;

              // Step1 - Delete all invoice items which are not avaialable
            if (!empty($item_ids)) {
                CardItems::whereNotIn('id', $item_ids)->where('card_id', $card->id)->delete();
            }

              // Step2&3 - Find old invoices items, update it and check if images are newer or older
            foreach ($names as $key => $name) {
                $card_items_id = isset($item_ids[$key]) ? $item_ids[$key] : 0;

                try {
                    $cardItems = CardItems::findOrFail($card_items_id);
                } catch (Exception) {
                    $cardItems = new CardItems();
                }

                $cardItems->name     = $name;
                $cardItems->card_id  = $card->id;
                $cardItems->no_kartu = $card_number[$key];
                $cardItems->status   = $status_card[$key];

                if ($status_card[$key] === 'approved') {
                    $cardItems->approved_by = user()->id;
                    $cardItems->approved_at = Carbon::now();
                }

                $cardItems->saveQuietly();
            }
        }

        $redirectUrl = route('card-access.index');
        return Reply::successWithData(__('traccesscard::messages.updateCard'), ['redirectUrl' => $redirectUrl]);
    }

    public function applyQuickAction(Request $request)
    {
        switch ($request->action_type) {
            case 'delete':
                $this->deleteRecords($request);
                return Reply::success(__('traccesscard::messages.deleteCard'));
            default:
                return Reply::error(__('traccesscard::messages.selectAction'));
        }
    }

    protected function deleteRecords($request)
    {
        TrAccessCard::whereIn('id', explode(',', $request->row_ids))->forceDelete();
    }

      /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        $viewPermission = user()->permission('delete_access_card');
        abort_403(in_array($viewPermission, ['none']));

        TrAccessCard::destroy($id);

        $redirectUrl = route('accountings.index');
        return Reply::successWithData(__('traccesscard::messages.deleteCard'), ['redirectUrl' => $redirectUrl]);
    }

    public function download($id)
    {
        $this->card        = TrAccessCard::with('unit', 'items')->findOrFail($id);
        $this->unit_detail = Unit::with('floor', 'tower')->findOrFail($this->card->unit_id);
        $pdfOption         = $this->domPdfObjectForDownload($id);
        $pdf               = $pdfOption['pdf'];
        $filename          = $pdfOption['fileName'];

        return request()->view ? $pdf->stream($filename . '.pdf'): $pdf->download($filename . '.pdf');
    }

    public function domPdfObjectForDownload($id)
    {
        $this->card        = TrAccessCard::with('unit', 'items')->findOrFail($id);
        $this->unit_detail = Unit::findOrFail($this->card->unit_id);
        $pdf               = app('dompdf.wrapper');

        $pdf->setOption('enable_php', true);
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', true);
        $pdf->loadView('traccesscard::card.pdf.invoice', $this->data);

        $filename = 'card-access-' . $this->card->name;
        return [
            'pdf'      => $pdf,
            'fileName' => $filename
        ];
    }
}
