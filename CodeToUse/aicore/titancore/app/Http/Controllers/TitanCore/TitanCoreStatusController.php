<?php

namespace App\Http\Controllers\TitanCore;

use App\Http\Controllers\Controller;
use App\TitanCore\Zero\CoreKernel;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;

class TitanCoreStatusController extends Controller
{
    public function __construct(
        protected CoreKernel $kernel,
    ) {
    }

    public function index(): View
    {
        return view('panel.user.business-suite.core.index', [
            'coreStatus' => $this->kernel->status(),
        ]);
    }

    public function api(): JsonResponse
    {
        return response()->json($this->kernel->status());
    }
}
