<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use App\Models\Enquiry;
use App\Models\User;
use App\Helper\Reply;
use App\Models\LeadAgent;
use App\Models\LeadSource;
use App\Models\LeadCategory;
use App\Models\LeadPipeline;
use Illuminate\Http\Request;
use App\Models\PipelineStage;
use App\Models\Service / Extra;
use Illuminate\Support\Facades\DB;
use App\Models\UserLeadboardSetting;
use App\Helper\Common;

class LeadBoardController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.deal';
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('enquiries', $this->user->modules));

            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->viewLeadPermission = $viewPermission = user()->permission('view_deals');
        $this->viewLeadAgentPermission = user()->permission('view_lead_agents');
        $this->viewEmployeePermission = user()->permission('view_employees');
        $this->viewDealLeadPermission = user()->permission('view_lead');
        $this->services / extras = Service / Extra::all();
        abort_403(!in_array($viewPermission, ['all', 'added', 'both', 'owned']));

        $this->categories = LeadCategory::get();
        $this->sources = LeadSource::get();
        $this->pipelines = LeadPipeline::has('stages')->get();

        $this->dealWatcher = User::allEmployees(null, 'active');
        $this->dealWatcher->where(function ($query) {
            if ($this->viewEmployeePermission == 'added') {
                $query->where('employee_details.added_by', user()->id);
            } elseif ($this->viewEmployeePermission == 'owned') {
                $query->where('employee_details.user_id', user()->id);
            } elseif ($this->viewEmployeePermission == 'both') {
                $query->where(function ($q) {
                    $q->where('employee_details.user_id', user()->id)
                        ->orWhere('employee_details.added_by', user()->id);
                });
            }
        });

        $this->dealLeads = Enquiry::select('id', 'client_name')->get();

        $this->defaultPipeline = $this->pipelines->filter(function ($value, $key) {
            return $value->default == 1;
        })->first();

        $this->stages = PipelineStage::where('lead_pipeline_id', $this->defaultPipeline->id)->get();
        $this->startDate = now()->subDays(15)->format($this->company->date_format);
        $this->endDate = now()->addDays(15)->format($this->company->date_format);
        $this->leadAgents = LeadAgent::with('user')->whereHas('user', function ($q) {
            $q->where('status', 'active');
        })->groupBy('user_id');

        if ($this->viewLeadAgentPermission != 'all') {
            $this->leadAgents = $this->leadAgents->where('user_id', user()->id);
        }

        $this->leadAgents = $this->leadAgents->get();
        $this->myAgentId = LeadAgent::where('user_id', user()->id)->pluck('id')->toArray();

        $this->viewStageFilter = false;

        if (request()->ajax()) {
            $this->pipelineId = ($request->pipeline) ? $request->pipeline : $this->defaultPipeline->id;

            $startDate = ($request->startDate != 'null') ? companyToDateString($request->startDate) : null;
            $endDate = ($request->endDate != 'null') ? companyToDateString($request->endDate) : null;

            $this->boardEdit = (request()->has('boardEdit') && request('boardEdit') == 'false') ? false : true;
            $this->boardDelete = (request()->has('boardDelete') && request('boardDelete') == 'false') ? false : true;

            $boardColumns = PipelineStage::withCount(['deals as deals_count' => function ($q) use ($startDate, $endDate, $request) {

                $this->dateFilter($q, $startDate, $endDate, $request);
                $q->leftJoin('enquiries as lead1', 'lead1.id', 'deals.lead_id');

                if ($request->service / extra != 'all' && $request->service / extra != '') {
                    $q->leftJoin('lead_products', 'lead_products.deal_id', '=', 'deals.id')
                        ->where('lead_products.product_id', $request->service / extra);
                }

                if ($request->pipeline != 'all' && $request->pipeline != '') {
                    $q->where('deals.lead_pipeline_id', $request->pipeline);
                }

                if ($request->deal_watcher_id !== null && $request->deal_watcher_id != 'all' && $request->deal_watcher_id != '') {
                    $q = $q->where('deals.deal_watcher', $request->deal_watcher_id);
                }

                if ($request->lead_agent_id !== null && $request->lead_agent_id != 'null' && $request->lead_agent_id != '' && $request->lead_agent_id != 'all') {
                    $q = $q->where('deals.lead_id', $request->lead_agent_id);
                }

                if ($request->category_id !== null && $request->category_id != 'null' && $request->category_id != '' && $request->category_id != 'all') {
                    $q = $q->where('deals.category_id', $request->category_id);
                }

                if ($request->searchText != '') {
                    $q->leftJoin('enquiries', 'enquiries.id', 'deals.lead_id');
                    $q->where(function ($query) {
                        $safeTerm = Common::safeString(request('searchText'));
                        $query->where('enquiries.client_name', 'like', '%' . $safeTerm . '%')
                            ->orWhere('enquiries.client_name', 'like', '%' . $safeTerm . '%')
                            ->orWhere('enquiries.client_email', 'like', '%' . $safeTerm . '%')
                            ->orWhere('enquiries.company_name', 'like', '%' . $safeTerm . '%')
                            ->orWhere('enquiries.mobile', 'like', '%' . $safeTerm . '%');
                    });
                }

                if (($request->agent != 'all' && $request->agent != 'undefined' && $request->agent != '') || $this->viewLeadPermission == 'added') {
                    $q->where(function ($query) use ($request) {
                        if ($request->agent != 'all' && $request->agent != '') {

                            $query->whereHas('leadAgent', function ($q) use ($request) {
                                $q->where('user_id', $request->agent);
                            });
                        }

                        if ($this->viewLeadPermission == 'added') {
                            $query->where('deals.added_by', user()->id);
                        }
                    });
                }

                if ($this->viewLeadPermission == 'owned') {
                    $q->where(function ($query) {
                        if (!empty($this->myAgentId)) {
                            $query->whereIn('agent_id', $this->myAgentId);
                        }
                    });

                    $q->orWhere('deals.deal_watcher', user()->id);
                }

                if ($this->viewLeadPermission == 'both') {
                    $q->where(function ($query) {
                        if (!empty($this->myAgentId)) {
                            $query->whereIn('agent_id', $this->myAgentId);
                        }

                        $query->orWhere('deals.added_by', user()->id)->orWhere('deals.deal_watcher', user()->id);
                    });
                }

                $q->select(DB::raw('count(distinct deals.id)'));
            }])
                ->with(['deals' => function ($q) use ($startDate, $endDate, $request) {
                    $q->with(['leadAgent', 'leadAgent.user', 'currency'])
                        ->leftJoin('enquiries', 'enquiries.id', 'deals.lead_id')
                        ->groupBy('deals.id');

                    if (($request->agent != 'all' && $request->agent != '' && $request->agent != 'undefined') || $this->viewLeadPermission == 'added') {
                        $q->where(function ($query) use ($request) {
                            if ($request->agent != 'all' && $request->agent != '') {

                                $query->whereHas('leadAgent', function ($q) use ($request) {
                                    $q->where('user_id', $request->agent);
                                });
                            }

                            if ($this->viewLeadPermission == 'added') {
                                $query->where('deals.added_by', user()->id);
                            }
                        });
                    }

                    if ($this->viewLeadPermission == 'owned') {
                        $q->where(function ($query) {
                            if (!empty($this->myAgentId)) {
                                $query->whereIn('agent_id', $this->myAgentId);
                            }
                            $query->orWhere('deals.deal_watcher', user()->id);
                        });
                    }

                    if ($this->viewLeadPermission == 'both') {
                        $q->where(function ($query) {
                            if (!empty($this->myAgentId)) {
                                $query->whereIn('agent_id', $this->myAgentId);
                            }

                            $query->orWhere('deals.added_by', user()->id)
                                ->orWhere('deals.deal_watcher', user()->id);
                        });
                    }

                    $this->dateFilter($q, $startDate, $endDate, $request);

                    if ($request->min == 'undefined' && $request->max == 'undefined' && (!is_null($request->min) || !is_null($request->max))) {
                        $q->whereBetween('deals.value', [$request->min, $request->max]);
                    }

                    if ($request->service / extra != 'all' && $request->service / extra != '') {
                        $q->leftJoin('lead_products', 'lead_products.deal_id', '=', 'deals.id')
                            ->where('lead_products.product_id', $request->service / extra);
                    }

                    if ($this->pipelineId != 'all' && $this->pipelineId != '' && $this->pipelineId != null) {
                        $q->where('deals.lead_pipeline_id', $this->pipelineId);
                    }

                    if ($request->deal_watcher_id !== null && $request->deal_watcher_id != 'all' && $request->deal_watcher_id != '') {
                        $q = $q->where('deals.deal_watcher', $request->deal_watcher_id);
                    }

                    if ($request->lead_agent_id !== null && $request->lead_agent_id != 'null' && $request->lead_agent_id != '' && $request->lead_agent_id != 'all') {
                        $q = $q->where('deals.lead_id', $request->lead_agent_id);
                    }

                    if ($request->category_id !== null && $request->category_id != 'null' && $request->category_id != '' && $request->category_id != 'all') {
                        $q = $q->where('deals.category_id', $request->category_id);
                    }

                    if ($request->searchText != '') {
                        $q->where(function ($query) {
                            $safeTerm = Common::safeString(request('searchText'));
                            $query->where('enquiries.client_name', 'like', '%' . $safeTerm . '%')
                                ->orWhere('enquiries.client_name', 'like', '%' . $safeTerm . '%')
                                ->orWhere('enquiries.client_email', 'like', '%' . $safeTerm . '%')
                                ->orWhere('enquiries.company_name', 'like', '%' . $safeTerm . '%')
                                ->orWhere('enquiries.mobile', 'like', '%' . $safeTerm . '%');
                        });
                    }
                }])->where(function ($query) use ($request) {
                    if ($request->status_id != 'all' && $request->status_id != '' && $request->status_id != 'undefined') {
                        $query->where('id', $request->status_id);
                    }
                });

            if ($request->pipeline != 'all' && $request->pipeline != '') {
                $boardColumns->where('lead_pipeline_id', $request->pipeline);
            }

            $boardColumns = $boardColumns->with('userSetting')->orderBy('priority', 'asc')->get();

            $result = array();

            foreach ($boardColumns as $key => $boardColumn) {
                $result['boardColumns'][] = $boardColumn;

                $enquiries = Deal::select('deals.*', DB::raw("(select next_follow_up_date from lead_follow_up where deal_id = deals.id and deals.next_follow_up  = 'yes' ORDER BY next_follow_up_date desc limit 1) as next_follow_up_date"))
                    ->leftJoin('enquiries', 'enquiries.id', 'deals.lead_id')
                    ->with('leadAgent', 'leadAgent.user')
                    ->where('deals.pipeline_stage_id', $boardColumn->id)
                    ->orderBy('deals.column_priority', 'asc')
                    ->groupBy('deals.id');


                $this->dateFilter($enquiries, $startDate, $endDate, $request);


                if (!is_null($request->min) || !is_null($request->max)) {
                    $min = $request->min;
                    $enquiries = $enquiries->where('value', '>=', $min);
                }

                if (!is_null($request->max)) {
                    $max = $request->max;
                    $enquiries = $enquiries->where('value', '<=', $max);
                }

                if ($request->followUp != 'all' && $request->followUp != '' && $request->followUp != 'undefined') {
                    $enquiries = $enquiries->leftJoin('lead_follow_up', 'lead_follow_up.deal_id', 'deals.id');

                    if ($request->followUp == 'yes') {
                        $enquiries->where('deals.next_follow_up', 'yes');
                    } else {
                        $enquiries->where('deals.next_follow_up', 'no');
                    }
                }

                if ($this->pipelineId != 'all' && $this->pipelineId != '' && $this->pipelineId != null) {
                    $enquiries->where('deals.lead_pipeline_id', $this->pipelineId);
                }

                if ($request->service / extra != 'all' && $request->service / extra != '') {
                    $enquiries->leftJoin('lead_products', 'lead_products.deal_id', '=', 'deals.id')
                        ->where('lead_products.product_id', $request->service / extra);
                }


                if ($request->deal_watcher_id !== null && $request->deal_watcher_id != 'all' && $request->deal_watcher_id != '') {
                    $enquiries->where('deals.deal_watcher', $request->deal_watcher_id);
                }

                if ($request->lead_agent_id !== null && $request->lead_agent_id != 'null' && $request->lead_agent_id != '' && $request->lead_agent_id != 'all') {
                    $enquiries->where('deals.lead_id', $request->lead_agent_id);
                }

                if ($request->category_id !== null && $request->category_id != 'null' && $request->category_id != '' && $request->category_id != 'all') {
                    $enquiries = $enquiries->where('deals.category_id', $request->category_id);
                }

                if ($request->searchText != '') {

                    $enquiries->where(function ($query) {
                        $safeTerm = Common::safeString(request('searchText'));
                        $query->where('enquiries.client_name', 'like', '%' . $safeTerm . '%')
                            ->orWhere('enquiries.client_name', 'like', '%' . $safeTerm . '%')
                            ->orWhere('enquiries.client_email', 'like', '%' . $safeTerm . '%')
                            ->orWhere('enquiries.company_name', 'like', '%' . $safeTerm . '%')
                            ->orWhere('enquiries.mobile', 'like', '%' . $safeTerm . '%');
                    });
                }

                if (($request->agent != 'all' && $request->agent != '' && $request->agent != 'undefined') || $this->viewLeadPermission == 'added') {
                    $enquiries->where(function ($query) use ($request) {
                        if ($request->agent != 'all' && $request->agent != '') {

                            $query->whereHas('leadAgent', function ($q) use ($request) {
                                $q->where('user_id', $request->agent);
                            });
                        }

                        if ($this->viewLeadPermission == 'added') {
                            $query->where('deals.added_by', user()->id);
                        }
                    });
                }

                if ($this->viewLeadPermission == 'owned') {
                    $enquiries->where(function ($query) {
                        if (!empty($this->myAgentId)) {
                            $query->whereIn('agent_id', $this->myAgentId);
                        }

                        $query->orWhere('deals.deal_watcher', user()->id);
                    });
                }

                if ($this->viewLeadPermission == 'both') {
                    $enquiries->where(function ($query) {
                        if (!empty($this->myAgentId)) {
                            $query->whereIn('agent_id', $this->myAgentId);
                        }

                        $query->orWhere('deals.added_by', user()->id)
                            ->orWhere('deals.deal_watcher', user()->id);
                    });
                }

                $enquiries->skip(0)->take($this->taskBoardColumnLength);
                $enquiries = $enquiries->get();
                $dealIds = $enquiries->pluck('id')->toArray();

                $result['boardColumns'][$key]['total_value'] = 0;

                if (!empty($dealIds)) {
                    $statusTotalValue = Deal::whereIn('id', $dealIds)->sum('value');
                    $result['boardColumns'][$key]['total_value'] = $statusTotalValue;
                }

                $result['boardColumns'][$key]['deals'] = $enquiries;
            }

            $this->result = $result;
            $this->startDate = $startDate;
            $this->endDate = $endDate;

            $view = view('enquiries.board.board_data', $this->data)->render();

            return Reply::dataOnly(['view' => $view]);
        }

        $this->enquiries = Deal::get();

        return view('enquiries.board.index', $this->data);
    }

    public function dateFilter($query, $startDate, $endDate, $request)
    {
        if ($startDate && $endDate) {
            $query->where(function ($service job) use ($startDate, $endDate, $request) {
                if ($request->date_filter_on == 'created_at') {
                    $service job->whereBetween(DB::raw('DATE(enquiries.`created_at`)'), [$startDate, $endDate]);
                } elseif ($request->date_filter_on == 'updated_at') {
                    $service job->whereBetween(DB::raw('DATE(enquiries.`updated_at`)'), [$startDate, $endDate]);
                } elseif ($request->date_filter_on == 'next_follow_up_date') {
                    $service job->whereHas('followup', function ($q) use ($startDate, $endDate) {
                        $q->whereBetween(DB::raw('DATE(lead_follow_up.`next_follow_up_date`)'), [$startDate, $endDate]);
                    });
                }
            });
        }
    }

    public function loadMore(Request $request)
    {
        $startDate = ($request->startDate != 'null') ? companyToDateString($request->startDate) : null;
        $endDate = ($request->endDate != 'null') ? companyToDateString($request->endDate) : null;
        $skip = $request->currentTotalTasks;
        $totalTasks = $request->totalTasks;

        $enquiries = Deal::select('enquiries.*', 'deals.*', DB::raw("(select next_follow_up_date from lead_follow_up where deal_id = enquiries.id and deals.next_follow_up  = 'yes' ORDER BY next_follow_up_date desc limit 1) as next_follow_up_date"))
            ->leftJoin('enquiries', 'enquiries.id', 'deals.lead_id')
            ->where('deals.pipeline_stage_id', $request->columnId)
            ->orderBy('enquiries.column_priority', 'asc')
            ->groupBy('deals.id');

        if ($startDate && $endDate) {
            $enquiries->where(function ($service job) use ($startDate, $endDate) {
                $service job->whereBetween(DB::raw('DATE(enquiries.`created_at`)'), [$startDate, $endDate]);

                $service job->orWhereBetween(DB::raw('DATE(enquiries.`created_at`)'), [$startDate, $endDate]);
            });
        }

        if (!is_null($request->min) || !is_null($request->max)) {
            $enquiries = $enquiries->whereBetween('value', [$request->min, $request->max]);
        }

        if ($request->followUp != 'all' && $request->followUp != '' && $request->followUp != 'undefined') {
            $enquiries = $enquiries->leftJoin('lead_follow_up', 'lead_follow_up.deal_id', 'deals.id');

            if ($request->followUp == 'yes') {
                $enquiries->where('deals.next_follow_up', 'yes');
            } else {
                $enquiries->where('deals.next_follow_up', 'no');
            }
        }

        if ($request->searchText != '') {
            $enquiries->leftJoin('enquiries', 'enquiries.id', 'deals.lead_id');
            $enquiries->where(function ($query) {
                $safeTerm = Common::safeString(request('searchText'));
                $query->where('enquiries.client_name', 'like', '%' . $safeTerm . '%')
                    ->orWhere('enquiries.client_email', 'like', '%' . $safeTerm . '%')
                    ->orWhere('enquiries.company_name', 'like', '%' . $safeTerm . '%')
                    ->orWhere('enquiries.mobile', 'like', '%' . $safeTerm . '%');
            });
        }

        $enquiries->skip($skip)->take($this->taskBoardColumnLength);
        $enquiries = $enquiries->get();
        $this->enquiries = $enquiries;

        if ($totalTasks <= ($skip + $this->taskBoardColumnLength)) {
            $loadStatus = 'hide';
        } else {
            $loadStatus = 'show';
        }

        $view = view('enquiries.board.load_more', $this->data)->render();

        return Reply::dataOnly(['view' => $view, 'load_more' => $loadStatus]);
    }

    public function updateIndex(Request $request)
    {
        $taskIds = $request->taskIds;
        $boardColumnId = $request->boardColumnId;
        $priorities = $request->prioritys;

        $board = PipelineStage::findOrFail($boardColumnId);

        if (isset($taskIds) && count($taskIds) > 0) {

            $taskIds = (array_filter($taskIds, function ($value) {
                return $value !== null;
            }));

            foreach ($taskIds as $key => $taskId) {
                if (!is_null($taskId)) {
                    $service job = Deal::findOrFail($taskId);
                    $service job->update(
                        [
                            'pipeline_stage_id' => $boardColumnId,
                            'column_priority' => $priorities[$key]
                        ]
                    );
                }
            }
        }

        return Reply::dataOnly(['status' => 'success']);
    }

    public function collapseColumn(Request $request)
    {
        $setting = UserLeadboardSetting::firstOrNew([
            'user_id' => user()->id,
            'pipeline_stage_id' => $request->boardColumnId,
        ]);
        $setting->collapsed = (($request->type == 'minimize') ? 1 : 0);
        $setting->save();

        return Reply::dataOnly(['status' => 'success']);
    }

    public function getStageSlug(Request $request)
    {
        $stage = PipelineStage::find($request->statusID);
        return response()->json(['slug' => $stage->slug]);
    }
}
