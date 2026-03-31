<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Crm;

use App\Http\Controllers\Core\CoreController;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CustomerDocumentController extends CoreController
{
    public function index(string $customer): View
    {
        return $this->placeholder(
            __('Customer documents'),
            __('Uploaded files for customer :customer will surface here.', ['customer' => $customer])
        );
    }

    public function store(Request $request, string $customer): RedirectResponse
    {
        return back()->with([
            'type'    => 'success',
            'message' => __('Document uploaded for customer :customer.', ['customer' => $customer]),
        ]);
    }

    public function download(string $customer, string $document): StreamedResponse
    {
        return response()->streamDownload(
            static function () use ($customer, $document) {
                echo __('Download placeholder for :document (customer :customer).', [
                    'document' => $document,
                    'customer' => $customer,
                ]);
            },
            sprintf('customer-%s-document-%s.txt', $customer, $document)
        );
    }

    public function destroy(string $customer, string $document): RedirectResponse
    {
        return back()->with([
            'type'    => 'success',
            'message' => __('Document :document removed.', ['document' => $document]),
        ]);
    }
}
