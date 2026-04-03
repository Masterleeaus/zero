<?php

namespace Modules\Contracts\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\Contracts\Entities\Contract;

class ContractSent extends Mailable
{
    use Queueable, SerializesModels;

    public Contract $contract;
    public string $publicUrl;

    public function __construct(Contract $contract, string $publicUrl)
    {
        $this->contract = $contract;
        $this->publicUrl = $publicUrl;
    }

    public function build()
    {
        return $this->subject('Contract: ' . $this->contract->title)
            ->view('contracts::contracts.email_sent', ['contract' => $this->contract, 'publicUrl' => $this->publicUrl]);
    }
}
