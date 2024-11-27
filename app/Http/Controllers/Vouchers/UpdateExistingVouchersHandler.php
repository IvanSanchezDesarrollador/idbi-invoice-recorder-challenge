<?php

namespace App\Http\Controllers\Vouchers;

use App\Http\Requests\Vouchers\GetVouchersRequest;
use App\Http\Resources\Vouchers\VoucherResource;
use App\Http\Resources\Vouchers\VoucherUpdateExistingResource;
use App\Models\Voucher;
use App\Services\VoucherService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class UpdateExistingVouchersHandler
{
    //VoucherService ->  centraliza la lógica de negocio relacionada con los comprobantes.
    public function __construct(private readonly VoucherService $voucherService) {}

    public function __invoke():Response
    {
        $user = auth()->user();

        $voucherUpdateExisting =  $this->voucherService->updateExistingVouchers($user);
        
        return response((new VoucherUpdateExistingResource($voucherUpdateExisting))->toArray(request()), 200);
        
    }
}
