<?php

namespace App\Listeners;

use App\Events\Vouchers\VouchersCreated;
use App\Mail\VouchersCreatedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;

//class SendVoucherAddedNotification implements ShouldQueue
class SendVoucherAddedNotification
{
    public function handle(VouchersCreated $event)
    {
        
        $mail = new VouchersCreatedMail($event->vouchers, $event->user, $event->vouchersFail);
        Mail::to($event->user->email)->send($mail);

        
    }
}
