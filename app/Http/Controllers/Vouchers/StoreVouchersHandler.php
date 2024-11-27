<?php

namespace App\Http\Controllers\Vouchers;
use App\Http\Resources\Vouchers\VoucherResource;
use App\Jobs\ProcessVoucherJob;
use App\Services\VoucherService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StoreVouchersHandler
{
    public function __construct(private readonly VoucherService $voucherService)
    {
    }

    public function __invoke(Request $request): JsonResponse|AnonymousResourceCollection
    {
        try {
            $xmlFiles = $request->file('files');

            if (!is_array($xmlFiles)) {
                $xmlFiles = [$xmlFiles];
            }

            $xmlContents = [];
            foreach ($xmlFiles as $xmlFile) {
                $xmlContents[] = file_get_contents($xmlFile->getRealPath());
            }

            $user = auth()->user();

            //$vouchers = $this->voucherService->storeVouchersFromXmlContents($xmlContents, $user);

            foreach ($xmlContents as $xmlContent) {
                ProcessVoucherJob::dispatch($xmlContent, $user); // Job enviado a la cola
            }

            
            return response()->json([
                'message' => 'Los comprobantes están siendo procesados en segundo plano.',
            ]);


        } catch (Exception $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 400);
        }
    }
}
