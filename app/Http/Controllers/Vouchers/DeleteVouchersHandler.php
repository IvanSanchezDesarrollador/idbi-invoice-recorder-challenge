<?php

namespace App\Http\Controllers\Vouchers;

use App\Http\Requests\Vouchers\DeleteVoucherRequest;
use App\Http\Requests\Vouchers\GetVouchersRequest;
use App\Http\Resources\Vouchers\VoucherDeleteResource;
use App\Http\Resources\Vouchers\VoucherResource;
use App\Models\Voucher;
use App\Services\VoucherService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class DeleteVouchersHandler
{
    //VoucherService ->  centraliza la lógica de negocio relacionada con los comprobantes.
    public function __construct(private readonly VoucherService $voucherService) {}

    public function __invoke(DeleteVoucherRequest $request): Response
    {
        $user = auth()->user();

        $vouchers = $this->voucherService->deleteVouchers(
       
            $user,
            $request->query('id'),
             
        );

        return response((new VoucherDeleteResource($vouchers))->toArray(request()), 200);
    }
}
