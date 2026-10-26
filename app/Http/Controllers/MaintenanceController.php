<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
use App\Services\Navigation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Throwable;

class MaintenanceController extends Controller
{
    /**
     * Authorize maintenance action using constant-time token comparison.
     */
    protected function authorizeMaintenance(string $token): void
    {
        $enabled = (bool) config('bina.maintenance_enabled', false);
        $configuredToken = config('bina.maintenance_token');

        if (! $enabled || blank($configuredToken)) {
            abort(404);
        }

        if (! hash_equals((string) $configuredToken, $token)) {
            abort(403, 'Unauthorized maintenance request.');
        }
    }

    /**
     * Web-triggerable cache clear endpoint.
     */
    public function cacheClear(Request $request, string $token): Response|JsonResponse
    {
        $this->authorizeMaintenance($token);

        try {
            Artisan::call('optimize:clear');
            SiteSetting::flushCache();
            Navigation::flushCache();
            Cache::forget('sitemap.xml');

            $output = trim(Artisan::output());

            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Cache cleared successfully.',
                    'output' => $output,
                    'timestamp' => now()->toIso8601String(),
                ]);
            }

            return response()->view('maintenance.action', [
                'title' => 'Cache Cleared Successfully',
                'action' => 'cache-clear',
                'output' => $output,
                'error' => null,
                'timestamp' => now()->toDateTimeString(),
            ]);
        } catch (Throwable $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $e->getMessage(),
                ], 500);
            }

            return response()->view('maintenance.action', [
                'title' => 'Cache Clear Failed',
                'action' => 'cache-clear',
                'output' => null,
                'error' => $e->getMessage(),
                'timestamp' => now()->toDateTimeString(),
            ], 500);
        }
    }

    /**
     * Web-triggerable database migrations endpoint.
     */
    public function migrate(Request $request, string $token): Response|JsonResponse
    {
        $this->authorizeMaintenance($token);

        try {
            Artisan::call('migrate', ['--force' => true]);
            $output = trim(Artisan::output());

            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Migrations executed successfully.',
                    'output' => $output,
                    'timestamp' => now()->toIso8601String(),
                ]);
            }

            return response()->view('maintenance.action', [
                'title' => 'Migrations Executed Successfully',
                'action' => 'migrate',
                'output' => $output,
                'error' => null,
                'timestamp' => now()->toDateTimeString(),
            ]);
        } catch (Throwable $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $e->getMessage(),
                ], 500);
            }

            return response()->view('maintenance.action', [
                'title' => 'Migration Failed',
                'action' => 'migrate',
                'output' => null,
                'error' => $e->getMessage(),
                'timestamp' => now()->toDateTimeString(),
            ], 500);
        }
    }
}
