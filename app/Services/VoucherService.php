<?php

namespace App\Services;

use App\Events\Vouchers\VouchersCreated;
use App\Models\User;
use App\Models\Voucher;
use App\Models\VoucherLine;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use SimpleXMLElement;

class VoucherService
{
    public function getVouchers(int $page, int $paginate, User $user, array $filters): LengthAwarePaginator
    {
        $query = Voucher::with(['lines', 'user'])
            ->where('user_id', $user->id);
    
        // Aplicar filtro de rango de fechas (obligatorio)
        if (isset($filters['start_date'], $filters['end_date'])) {
            $query->whereBetween('created_at', [
                Carbon::parse($filters['start_date'])->startOfDay(),
                Carbon::parse($filters['end_date'])->endOfDay(),
            ]);
        }
    
        // Aplicar otros filtros opcionales
        if (!empty($filters['series'])) {
            $query->where('series', $filters['series']);
        }
    
        if (!empty($filters['number'])) {
            $query->where('number', $filters['number']);
        }
    
        if (!empty($filters['voucher_type'])) {
            $query->where('voucher_type', $filters['voucher_type']);
        }
    
        if (!empty($filters['currency'])) {
            $query->where('currency', $filters['currency']);
        }
    
        return $query->paginate(perPage: $paginate, page: $page);
    }
    
    

    public function getVoucherCountWithUser(): array
    {
        $user = auth()->user();
        $count = Voucher::where('user_id', $user->id)->count();

        return [
            'user' => $user,
            'voucher_count' => $count,
        ];
    }

    public function getVoucherAccumulatedCurrency(User $user)
    {

        // Obtener totales acumulados directamente desde la base de datos
        $totalPEN = Voucher::where('user_id', $user->id)
            ->where('currency', 'PEN')
            ->sum('total_amount');

        $totalUSD = Voucher::where('user_id', $user->id)
            ->where('currency', 'USD')
            ->sum('total_amount');


        if ($totalPEN == 0 && $totalUSD == 0) {
            return [
                'user' => $user,
                'PEN_currency_amount' => $totalPEN,
                'USD_currency_amount' => $totalUSD,
                'message' => "Es posible que no haya actualizado su tipo de moneda."
            ];
        }

        return [
            'user' => $user,
            'PEN_currency_amount' => $totalPEN,
            'USD_currency_amount' => $totalUSD,
            'message' => "Montos desglosados en soles y dólares"
        ];
    }

    public function deleteVouchers(User $user, string $id)
    {
        // Incluir registros eliminados
        $voucher = Voucher::withTrashed()->where('id', $id)->first();

        if (!$voucher) {
            return [
                'message' => "El voucher con id: " . $id . ", no existe",
            ];
        }

        if ($voucher->user_id !== $user->id) {
            return [
                'message' => "El voucher con id: " . $id . ", no te pertenece",
            ];
        }

        if ($voucher->trashed()) {
            return [
                'message' => "El voucher con id: " . $id . ", ya estaba eliminado",
            ];
        }

        $voucher->delete();

        return [
            'message' => "El voucher con id: " . $id . ", ha sido eliminado exitosamente",
        ];
    }



    public function updateExistingVouchers(User $user): array
    {

        //$vouchersArray = Voucher::where('user_id', $user->id)->get();

        $vouchers = Voucher::where('user_id', $user->id)
            ->where(function ($query) {
                $query->whereNull('series')
                    ->orWhereNull('number')
                    ->orWhereNull('voucher_type')
                    ->orWhereNull('currency');
            })->get();




        if ($vouchers->isEmpty()) {

            //event(new VouchersCreated($vouchersArray, $user));

            return [
                'message' => 'No hay nada que actualizar.',
                'user' => $user,
            ];
        }

        $seriesBase = 'F'; // Prefijo de la serie
        $maxVouchersPerSeries = 10; // Cantidad máxima de vouchers por serie
        $currentSerieCounter = 1; // Contador inicial de la serie
        $currentNumberInSerie = 1; // Contador inicial dentro de la serie
        $currencies = ['PEN', 'USD']; // Valores posibles para currency

        foreach ($vouchers as $voucher) {

            // Generar serie actual con formato (F001, F002, etc.)
            $serie = $seriesBase . str_pad($currentSerieCounter, 3, '0', STR_PAD_LEFT);

            // Asignar los valores al voucher
            $randomCurrency = Arr::random($currencies);

            $voucher->update([
                'series' => $serie,
                'number' => $currentNumberInSerie,
                'voucher_type' => '01',
                'currency' => $randomCurrency,
            ]);

            // Incrementar el número dentro de la serie
            $currentNumberInSerie++;

            // Si llegamos al límite de la serie, incrementar la serie y reiniciar el número
            if ($currentNumberInSerie > $maxVouchersPerSeries) {
                $currentSerieCounter++;
                $currentNumberInSerie = 1;
            }
        }

        // event(new VouchersCreated($vouchersArray->toArray(), $user));

        return [
            'message' => count($vouchers) . ' vouchers actualizados correctamente.',
            'user' => $user,
        ];
    }


    /**
     * @param string[] $xmlContents
     * @param User $user
     * @return Voucher[]
     */
    public function storeVouchersFromXmlContents(array $xmlContents, User $user): array
    {
        $vouchers = [];
        foreach ($xmlContents as $xmlContent) {
            $vouchers[] = $this->storeVoucherFromXmlContent($xmlContent, $user);
        }

        VouchersCreated::dispatch($vouchers, $user);

        return $vouchers;
    }

    public function storeVoucherFromXmlContent(string $xmlContent, User $user): Voucher
    {
        $xml = new SimpleXMLElement($xmlContent);

        $issuerName = (string) $xml->xpath('//cac:AccountingSupplierParty/cac:Party/cac:PartyName/cbc:Name')[0];
        $issuerDocumentType = (string) $xml->xpath('//cac:AccountingSupplierParty/cac:Party/cac:PartyIdentification/cbc:ID/@schemeID')[0];
        $issuerDocumentNumber = (string) $xml->xpath('//cac:AccountingSupplierParty/cac:Party/cac:PartyIdentification/cbc:ID')[0];

        $receiverName = (string) $xml->xpath('//cac:AccountingCustomerParty/cac:Party/cac:PartyLegalEntity/cbc:RegistrationName')[0];
        $receiverDocumentType = (string) $xml->xpath('//cac:AccountingCustomerParty/cac:Party/cac:PartyIdentification/cbc:ID/@schemeID')[0];
        $receiverDocumentNumber = (string) $xml->xpath('//cac:AccountingCustomerParty/cac:Party/cac:PartyIdentification/cbc:ID')[0];

        $totalAmount = (string) $xml->xpath('//cac:LegalMonetaryTotal/cbc:TaxInclusiveAmount')[0];

        $seriesAndNumberCorrelative = (string) $xml->xpath('//cbc:ID')[0];
        $values = explode('-', $seriesAndNumberCorrelative);
        $series = $values[0];
        $number = $values[0];

        $voucherType = (string) $xml->xpath('//cbc:InvoiceTypeCode')[0];

        $currency = (string) $xml->xpath('//cbc:DocumentCurrencyCode')[0];


        $voucher = new Voucher([
            'issuer_name' => $issuerName,
            'issuer_document_type' => $issuerDocumentType,
            'issuer_document_number' => $issuerDocumentNumber,
            'receiver_name' => $receiverName,
            'receiver_document_type' => $receiverDocumentType,
            'receiver_document_number' => $receiverDocumentNumber,
            'total_amount' => $totalAmount,
            'xml_content' => $xmlContent,
            'user_id' => $user->id,
            'series' => $series,
            'number' => $number,
            'voucher_type' => $voucherType,
            'currency' => $currency
        ]);
        $voucher->save();

        foreach ($xml->xpath('//cac:InvoiceLine') as $invoiceLine) {
            $name = (string) $invoiceLine->xpath('cac:Item/cbc:Description')[0];
            $quantity = (float) $invoiceLine->xpath('cbc:InvoicedQuantity')[0];
            $unitPrice = (float) $invoiceLine->xpath('cac:Price/cbc:PriceAmount')[0];

            $voucherLine = new VoucherLine([
                'name' => $name,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'voucher_id' => $voucher->id,
            ]);

            $voucherLine->save();
        }

        return $voucher;
    }
}
