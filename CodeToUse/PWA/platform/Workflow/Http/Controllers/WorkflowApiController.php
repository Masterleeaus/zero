<?php

namespace Modules\Workflow\Http\Controllers;

use Illuminate\Http\Request;

use Modules\Workflow\Entities\Workflow;
use App\Http\Controllers\Controller;

class WorkflowApiController extends Controller
{
    /**
     * Store a new workflow.
     */
    public function store(Request $request)
    {
       // dd($request);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'project_category_id' => 'nullable|exists:project_category,id',
            'company_id' => 'nullable|exists:companies,id',
            'workflow_data' => 'nullable|array', // Validate JSON input as an array
        ]);
    
        $workflow = Workflow::create($validated);
    
        return response()->json([
            'success' => true,
            'message' => 'Workflow created successfully!',
            'data' => $workflow,
        ], 201);
    }

    public function update(Request $request, Workflow $workflow)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'project_category_id' => 'nullable|exists:project_category,id',
        'company_id' => 'required|exists:companies,id',
        'workflow_data' => 'nullable|array', // Validate JSON input as an array
    ]);

    $workflow->update($validated);

    return response()->json([
        'success' => true,
        'message' => 'Workflow updated successfully!',
        'data' => $workflow,
    ]);
}

    
}
