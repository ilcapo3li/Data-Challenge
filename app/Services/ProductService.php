<?php

namespace App\Services;

use Throwable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class ProductService
{
    public function getProducts()
    {
        Log::info( 'System tries to hit product');
        try {
            $response =  Http::withHeaders([
                'accept' => 'application/json',
            ])->get(config('services.products.url'));
            if ($response->ok()) { 
                return $response->json();
            }
            Log::emergency(sprintf("service emerge", $response->getMessage()));
        } catch (Throwable $e) {
            Log::error( sprintf("service down", $e->getMessage()));
            return null;
        }
    }
}
