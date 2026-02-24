<?php

namespace App\Http\Controllers;

use App\Models\RiwayatPembayaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class RiwayatPembayaranController extends Controller
{
    public function index()
    {
        $url = config('app.simaku_url');
        $npm = auth('web')->user()->npm;

        $timestamp = time();
        $body = json_encode([
            'npm' => $npm,
        ]);

        $data = $timestamp . 'POST' . 'api/riwayat-pembayaran' . $body;

        $signature = hash_hmac('sha256', $data, 'supersecret123');

        $response = Http::withHeaders([
            'X-API-KEY' => 'kampus-client-01',
            'X-TIMESTAMP' => $timestamp,
            'X-SIGNATURE' => $signature,
        ])->post($url, json_decode($body, true));

        $responseData = $response->json();

        $data = $responseData['data'] ?? [];

        $d['riwayat_pembayaran'] = $data;
        return view('riwayat-pembayaran.view', $d);
    }
}
