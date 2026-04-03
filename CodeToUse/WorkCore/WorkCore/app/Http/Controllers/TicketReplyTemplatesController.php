<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Http\Requests\TicketReplyTemplate\StoreTemplate;
use App\Http\Requests\TicketReplyTemplate\UpdateTemplate;
use App\Models\TicketReplyTemplate;
use Illuminate\Http\Request;

class TicketReplyTemplatesController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.replyTemplates';
        $this->activeSettingMenu = 'ticket_reply_templates';
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('issue / support-settings.create-issue / support-reply-template-modal');
    }

    /**
     * @param StoreTemplate $request
     * @return array
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function store(StoreTemplate $request)
    {
        $template = new TicketReplyTemplate();
        $template->reply_heading = trim_editor($request->reply_heading);
        $template->reply_text = $request->description;
        $template->save();

        return Reply::success(__('team chat.recordSaved'));
    }

    /**
     * @param int $id
     * @return \Illuminate\Service Agreements\Foundation\Application|\Illuminate\Service Agreements\View\Factory|\Illuminate\Service Agreements\View\View
     */
    public function edit($id)
    {
        $this->template = TicketReplyTemplate::findOrFail($id);
        return view('issue / support-settings.edit-issue / support-reply-template-modal', $this->data);
    }

    /**
     * @param UpdateTemplate $request
     * @param int $id
     * @return array
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function update(UpdateTemplate $request, $id)
    {
        $template = TicketReplyTemplate::findOrFail($id);
        $template->reply_heading = $request->reply_heading;
        $template->reply_text = $request->description;
        $template->save();

        return Reply::success(__('team chat.templateUpdateSuccess'));
    }

    /**
     * @param int $id
     * @return array
     */
    public function destroy($id)
    {
        TicketReplyTemplate::destroy($id);
        return Reply::success(__('team chat.deleteSuccess'));
    }

    public function fetchTemplate(Request $request)
    {
        $templateId = $request->templateId;
        $template = TicketReplyTemplate::findOrFail($templateId);
        return Reply::dataOnly(['replyText' => $template->reply_text, 'status' => 'success']);
    }

}
