<?php

namespace App\Http\Resources\Vouchers;

use App\Http\Resources\Users\UserResource;
use App\Http\Resources\VoucherLines\VoucherLineResource;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VoucherAccumulatedCurrency extends JsonResource
{
    /**
     * @var Voucher
     */
    public $resource;

    public function toArray(Request $request): array
    {
        return [
            'user' => UserResource::make($this->resource['user']),
            'PEN_currency_amount' => $this->resource['PEN_currency_amount'],
            'USD_currency_amount' => $this->resource['USD_currency_amount'],
            'message' => $this->resource['message'],
        ];
    }
}
