<?php

namespace App\Jobs;

use App\Events\Vouchers\VouchersCreated;
use App\Http\Resources\Vouchers\VoucherResource;
use App\Models\Voucher;
use App\Models\VoucherLine;
use App\Services\VoucherService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Queue\SerializesModels;
use SimpleXMLElement;

class ProcessVoucherJob implements ShouldQueue
{
    use Dispatchable, Queueable, SerializesModels;

    protected $xmlContent;
    protected $user;

    public function __construct(string $xmlContent, $user)
    {
        $this->xmlContent = $xmlContent;
        $this->user = $user;
    }

    public function handle(VoucherService $voucherService):AnonymousResourceCollection
    {
        //$vouchers = $voucherService->storeVoucherFromXmlContent($this->xmlContent, $this->user);
        $user = auth()->user();
        $successVouchers = collect();
        $failedVouchers = [];
    
        try {
            // Intentar procesar y almacenar el voucher
            $voucher = $voucherService->storeVoucherFromXmlContent($this->xmlContent, $this->user);
            $successVouchers->push($voucher);// Almacenado exitosamente
        } catch (\Exception $e) {
            // Si ocurre un error, solo guardar el mensaje de error
            $failedVouchers[] = [
                'error' => $e->getMessage(),
            ];
        }


        event(new VouchersCreated($successVouchers, $user,$failedVouchers ));

        
        return VoucherResource::collection($successVouchers);
    }
}