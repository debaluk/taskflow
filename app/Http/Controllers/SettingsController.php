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

        $code = $request->input('code');

        if (! is_string($code) || trim($code) === '' || strlen($code) > 2048) {
            return redirect()
                ->route('settings')
                ->with('error', 'Authorization code ClickUp tidak valid.');
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
            Log::warning('ClickUp OAuth token request failed', [
                'status' => $response->status(),
            ]);

            return redirect()
                ->route('settings')
                ->with('error', 'Gagal mendapatkan access token ClickUp.');
        }

        $token = $response->json('access_token');

        if (! is_string($token) || trim($token) === '' || strlen($token) > 4096) {
            Log::warning('ClickUp OAuth response did not contain a valid access token', [
                'status' => $response->status(),
            ]);

            return redirect()
                ->route('settings')
                ->with('error', 'Respons koneksi ClickUp tidak valid.');
        }

        session([
            'clickup_access_token' => $token,
            'clickup_connected' => true,
        ]);

        return redirect()
            ->route('settings')
            ->with('success', 'ClickUp berhasil terhubung.');
    }
}