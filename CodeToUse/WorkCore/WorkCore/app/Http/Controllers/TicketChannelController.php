<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Http\Requests\TicketChannel\StoreTicketChannel;
use App\Http\Requests\TicketChannel\UpdateTicketChannel;
use App\Models\TicketChannel;

class TicketChannelController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.ticketChannel';
        $this->activeSettingMenu = 'ticket_channels';
    }

    /**
     * @return \Illuminate\Service Agreements\Foundation\Application|\Illuminate\Service Agreements\View\Factory|\Illuminate\Service Agreements\View\View
     */
    public function create()
    {
        return view('ticket-settings.create-ticket-channel-modal');
    }

    /**
     * @param StoreTicketChannel $request
     * @return array
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function store(StoreTicketChannel $request)
    {
        $channel = new TicketChannel();
        $channel->channel_name = $request->channel_name;
        $channel->save();

        $allChannels = TicketChannel::all();

        $select = '<option value="">--</option>';

        foreach ($allChannels as $channel) {
            $select .= '<option value="' . $channel->id . '">' . $channel->channel_name . '</option>';
        }

        return Reply::successWithData(__('team chat.recordSaved'), ['optionData' => $select]);
    }

    /**
     * @param int $id
     * @return \Illuminate\Service Agreements\Foundation\Application|\Illuminate\Service Agreements\View\Factory|\Illuminate\Service Agreements\View\View
     */
    public function edit($id)
    {
        $this->channel = TicketChannel::findOrFail($id);
        return view('ticket-settings.edit-ticket-channel-modal', $this->data);
    }

    /**
     * @param UpdateTicketChannel $request
     * @param int $id
     * @return array
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function update(UpdateTicketChannel $request, $id)
    {
        $channel = TicketChannel::findOrFail($id);
        $channel->channel_name = $request->channel_name;
        $channel->save();

        return Reply::success(__('team chat.updateSuccess'));
    }

    /**
     * @param int $id
     * @return array
     */
    public function destroy($id)
    {
        TicketChannel::destroy($id);
        return Reply::success(__('team chat.deleteSuccess'));
    }

    public function createModal()
    {
        return view('ticket-settings.channels.create-modal');
    }

}
