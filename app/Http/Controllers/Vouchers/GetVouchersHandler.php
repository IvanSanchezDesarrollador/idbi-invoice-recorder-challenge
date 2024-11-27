<?php

namespace App\Http\Controllers\Vouchers;

use App\Http\Requests\Vouchers\GetVouchersRequest;
use App\Http\Resources\Vouchers\VoucherResource;
use App\Models\Voucher;
use App\Services\VoucherService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class GetVouchersHandler
{
    public function __construct(private readonly VoucherService $voucherService) {}

    public function __invoke(GetVouchersRequest $request): AnonymousResourceCollection
    {
        $user = auth()->user();

        // Recoger los filtros desde los parámetros de la URL
        $filters = $request->filters();

        $vouchers = $this->voucherService->getVouchers(
            $request->query('page'),
            $request->query('paginate'),
            $user,
            $filters
        );

        return VoucherResource::collection($vouchers);
    }
}
