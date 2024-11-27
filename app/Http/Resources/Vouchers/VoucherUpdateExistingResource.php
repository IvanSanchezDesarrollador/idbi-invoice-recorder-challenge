<?php

namespace App\Http\Resources\Vouchers;

use App\Http\Resources\Users\UserResource;
use App\Http\Resources\VoucherLines\VoucherLineResource;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VoucherUpdateExistingResource extends JsonResource
{
    /**
     * @var Voucher
     */
    public $resource;

    public function toArray(Request $request): array
    {
        return [
            'message' => $this->resource['message'],
            'user' => UserResource::make($this->resource['user']),
        ];
    }
}
