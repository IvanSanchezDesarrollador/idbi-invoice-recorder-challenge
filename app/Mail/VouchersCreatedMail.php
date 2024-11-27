<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VouchersCreatedMail extends Mailable
{
    //use Queueable;
    use SerializesModels;

    public Collection $vouchers;
    public User $user;

    public array $vouchersFail;

    public function __construct(Collection $vouchers, User $user, array $vouchersFail)
    {
        $this->vouchers = $vouchers;
        $this->user = $user;
        $this->vouchersFail = $vouchersFail;
    }

    public function build(): self
    {
        return $this->view('emails.vouchers')
            ->subject('Subida de comprobantes')
            ->with(['vouchers' => $this->vouchers, 'user' => $this->user, 'vouchersFail'=> $this->vouchersFail]);
    }
}
