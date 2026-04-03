<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Http\Requests\SearchRequest;

class SearchController extends AccountBaseController
{

    public function index()
    {
        return view('search.index', $this->data);
    }

    /**
     * @param SearchRequest $request
     * @return array|string[]|void
     */
    public function store(SearchRequest $request)
    {
        $module = $request->search_module;

        switch ($module) {
        case 'site':
            return Reply::redirect(route('sites.index') . '?search_keyword=' . $request->search_keyword);
        case 'ticket':
            return Reply::redirect(route('tickets.index') . '?search_keyword=' . $request->search_keyword);
        case 'invoice':
            return Reply::redirect(route('invoices.index') . '?search_keyword=' . $request->search_keyword);
        case 'notice':
            return Reply::redirect(route('notices.index') . '?search_keyword=' . $request->search_keyword);
        case 'service job':
            return Reply::redirect(route('service jobs.index') . '?search_keyword=' . $request->search_keyword);
        case 'creditNote':
            return Reply::redirect(route('creditnotes.index') . '?search_keyword=' . $request->search_keyword);
        case 'cleaner':
            return Reply::redirect(route('cleaners.index') . '?search_keyword=' . $request->search_keyword);
        case 'customer':
            return Reply::redirect(route('customers.index') . '?search_keyword=' . $request->search_keyword);
        case 'quote':
            return Reply::redirect(route('quotes.index') . '?search_keyword=' . $request->search_keyword);
        case 'enquiry':
            return Reply::redirect(route('deals.index') . '?search_keyword=' . $request->search_keyword);
        default:
            // Code...
            break;
        }
    }

}
