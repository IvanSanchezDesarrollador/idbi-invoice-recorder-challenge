<?php

use App\Http\Controllers\Vouchers\CountVouchersHandler;
use App\Http\Controllers\Vouchers\DeleteVouchersHandler;
use App\Http\Controllers\Vouchers\GetAccumulatedCurrencyVoucherHandler;
use App\Http\Controllers\Vouchers\GetVouchersHandler;
use App\Http\Controllers\Vouchers\StoreVouchersHandler;
use App\Http\Controllers\Vouchers\UpdateExistingVouchersHandler;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;

Route::prefix('vouchers')->group(
    function () {
        Route::get('/', GetVouchersHandler::class);
        Route::get('/count', CountVouchersHandler::class);
        Route::get('/accumulated-currency', GetAccumulatedCurrencyVoucherHandler::class);
        Route::post('/', StoreVouchersHandler::class);
        Route::put('/update-existing', UpdateExistingVouchersHandler::class);
        Route::delete('/delete', DeleteVouchersHandler::class);


        Route::get('/test-mail', function (): Response {

            Mail::raw('Este es un correo de prueba', function ($message) {
                $message->to('test@domain.com')  // Usar un correo que puedas verificar en MailHog
                        ->subject('Correo de prueba');
            });
        
            return response([
                'data' => [
                    'mesaje' => "Correo enviado ....",
                ],
            ], 200);
        });

        //UpDateVouchers
    }
);
