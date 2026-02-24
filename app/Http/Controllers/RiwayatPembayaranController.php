<?php

namespace App\Http\Controllers;

use App\Models\RiwayatPembayaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class RiwayatPembayaranController extends Controller
{
    public function index()
    {
        $url = config('services.simaku_url');
        $npm = auth('web')->user()->npm;

        $timestamp = time();
        $nonce = Str::uuid()->toString();
        $path = 'api/riwayat-pembayaran';

        $body = json_encode([
            'npm' => $npm,
        ]);

        $data = $timestamp . $nonce . 'POST' . $path . $body;
        $signature = hash_hmac('sha256', $data, config('services.hmac_secret'));
        $response = Http::withHeaders([
            'X-API-KEY'   => config('services.hmac_api_key'),
            'X-TIMESTAMP' => $timestamp,
            'X-NONCE'     => $nonce,
            'X-SIGNATURE' => $signature,
        ])->withBody($body, 'application/json')
            ->post($url . $path);

        $responseData = $response->json();

        $data = $responseData['data'] ?? [];

        if (empty($data)) {
            return [];
        }

        $d['riwayat_pembayaran'] = $data;
        return view('riwayat-pembayaran.view', $d);
    }
}
