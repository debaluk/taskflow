<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
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
        $state = Str::random(64);

        session([
            'clickup_oauth_state' => $state,
        ]);

        $query = http_build_query([
            'client_id' => config('services.clickup.client_id'),
            'redirect_uri' => config('services.clickup.redirect_uri'),
            'state' => $state,
        ]);

        return redirect(
            'https://app.clickup.com/api?' . $query
        );
    }

    public function clickupCallback(Request $request)
    {
        $state = $request->input('state');
        $expectedState = session('clickup_oauth_state');

        if (
            ! is_string($state) ||
            ! is_string($expectedState) ||
            $expectedState === '' ||
            ! hash_equals($expectedState, $state)
        ) {
            return redirect()
                ->route('settings')
                ->with('error', 'Validasi keamanan koneksi ClickUp gagal. Silakan coba hubungkan kembali.');
        }

        $request->session()->forget('clickup_oauth_state');

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

        try {
            $response = Http::connectTimeout(5)
                ->timeout(10)
                ->post(
                    'https://api.clickup.com/api/v2/oauth/token',
                    [
                        'client_id' => config('services.clickup.client_id'),
                        'client_secret' => config('services.clickup.client_secret'),
                        'code' => $code,
                    ]
                );
        } catch (ConnectionException $exception) {
            Log::warning('ClickUp OAuth token request connection failed', [
                'exception' => $exception::class,
            ]);

            return redirect()
                ->route('settings')
                ->with('error', 'Tidak dapat terhubung ke ClickUp. Silakan coba lagi.');
        }

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