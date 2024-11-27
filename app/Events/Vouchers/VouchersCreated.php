<?php

namespace App\Events\Vouchers;

use App\Models\User;
use App\Models\Voucher;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Database\Eloquent\Collection;  // Importa la clase Collection

class VouchersCreated
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * @param Collection $vouchers
     * @param User $user
     */
    public function __construct(
        public readonly Collection $vouchers,  // Cambia array por Collection
        public readonly User $user,
        public readonly array $vouchersFail,
    ) {
    }
}
