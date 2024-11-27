<?php

namespace App\Http\Controllers\Vouchers;

use App\Http\Requests\Vouchers\GetVouchersRequest;
use App\Http\Resources\Vouchers\VoucherCountResource;
use App\Http\Resources\Vouchers\VoucherResource;
use App\Models\Voucher;
use App\Services\VoucherService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class CountVouchersHandler
{
    //VoucherService ->  centraliza la lógica de negocio relacionada con los comprobantes.
    public function __construct(private readonly VoucherService $voucherService) {}

    public function __invoke(): Response
    {
        $voucherCount = $this->voucherService->getVoucherCountWithUser();

        return response((new VoucherCountResource($voucherCount))->toArray(request()), 200);
    }
}
