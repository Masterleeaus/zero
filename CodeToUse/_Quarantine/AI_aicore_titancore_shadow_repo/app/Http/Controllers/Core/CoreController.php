<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

abstract class CoreController extends Controller
{
    protected function placeholder(string $title, string $subtitle = ''): View
    {
        return view('core.placeholder', [
            'title'    => $title,
            'subtitle' => $subtitle,
        ]);
    }
}
