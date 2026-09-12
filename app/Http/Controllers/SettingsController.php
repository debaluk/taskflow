<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        return view('settings.index');
    }

    public function clickup(): View
    {
        return view('settings.clickup');
    }

    public function clickupConnect()
    {
        $query = http_build_query([
            'client_id' => config('services.clickup.client_id'),
            'redirect_uri' => config('services.clickup.redirect_uri'),
        ]);

        return redirect(
            'https://app.clickup.com/api?' . $query
        );
    }

    public function clickupCallback(Request $request)
    {
        if ($request->has('error')) {
            return redirect()
                ->route('settings')
                ->with('error', 'Koneksi ClickUp dibatalkan.');
        }

        $code = $request->get('code');

        if (!$code) {
            return redirect()
                ->route('settings')
                ->with('error', 'Authorization code ClickUp tidak ditemukan.');
        }

        $response = Http::post(
            'https://api.clickup.com/api/v2/oauth/token',
            [
                'client_id' => config('services.clickup.client_id'),
                'client_secret' => config('services.clickup.client_secret'),
                'code' => $code,
            ]
        );

        if ($response->failed()) {
            Log::error('ClickUp OAuth Token Error', [
                'status' => $response->status(),
                'response' => $response->json(),
            ]);

            return redirect()
                ->route('settings')
                ->with('error', 'Gagal mendapatkan access token ClickUp.');
        }

        $token = $response->json('access_token');

        session([
            'clickup_access_token' => $token,
            'clickup_connected' => true,
        ]);

        return redirect()
            ->route('settings')
            ->with('success', 'ClickUp berhasil terhubung.');
    }
}