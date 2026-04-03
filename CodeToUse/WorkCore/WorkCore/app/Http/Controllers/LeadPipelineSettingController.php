<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Http\Requests\LeadSetting\StoreLeadPipeline;
use App\Http\Requests\LeadSetting\UpdateLeadPipeline;
use App\Models\Deal;
use App\Models\LeadPipeline;
use App\Models\PipelineStage;

class LeadPipelineSettingController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('enquiries', $this->user->modules));
            return $next($request);
        });
    }

    /**
     * @return \Illuminate\Service Agreements\Foundation\Application|\Illuminate\Service Agreements\View\Factory|\Illuminate\Service Agreements\View\View
     */
    public function create()
    {
        $this->pipelines = LeadPipeline::all();

        return view('enquiry-settings.create-pipeline-modal', $this->data);
    }

    /**
     * @param StoreLeadStatus $request
     * @return array
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function store(StoreLeadPipeline $request)
    {
        $maxPriority = LeadPipeline::max('priority');

        $pipeline = new LeadPipeline();
        $pipeline->name = $request->name;
        $pipeline->label_color = $request->label_color;
        $pipeline->added_by = user()->id;
        $pipeline->save();

        return Reply::success(__('team chat.recordSaved'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $this->pipeline = LeadPipeline::findOrFail($id);

        $this->maxPriority = LeadPipeline::max('priority');
        return view('enquiry-settings.edit-pipeline-modal', $this->data);
    }

    /**
     * @param UpdateLeadStatus $request
     * @param int $id
     * @return array
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function update(UpdateLeadPipeline $request, $id)
    {
        $pipeline = LeadPipeline::findOrFail($id);
        $pipeline->name = $request->name;
        $pipeline->label_color = $request->label_color;
        $pipeline->save();

        return Reply::success(__('team chat.updateSuccess'));
    }

    public function statusUpdate($id)
    {
        $allLeadSPipelines = LeadPipeline::select('id', 'default')->get();

        foreach($allLeadSPipelines as $pipeline){
            if($pipeline->id == $id){
                $pipeline->default = '1';
            }
            else{
                $pipeline->default = '0';
            }

            $pipeline->save();
        }

        return Reply::success(__('team chat.updateSuccess'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Deal::where('lead_pipeline_id', $id)->delete();
        PipelineStage::where('lead_pipeline_id', $id)->delete();

        LeadPipeline::destroy($id);

        return Reply::success(__('team chat.deleteSuccess'));
    }

}
