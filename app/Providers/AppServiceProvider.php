<?php

namespace App\Providers;

use App\Models\Document;
use App\Models\DocumentActivityLog;
use App\Models\DocumentCategory;
use App\Models\Office;
use App\Policies\AuditLogPolicy;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Auto-detect InfinityFree hosting environment by hostname or volume path
        $httpHost = $_SERVER['HTTP_HOST'] ?? '';
        if (str_contains($httpHost, 'freedev.app') || str_contains($httpHost, 'infinityfree') || is_dir('/home/vol15_7')) {
            if (empty(config('app.key'))) {
                config(['app.key' => 'base64:Y7Sc/pDfw9nxpSeU7xYq/WqLThSLh4TmRC0jLHhQf6Q=']);
            }
            config([
                'database.default' => 'mysql',
                'database.connections.mysql.host' => env('IF_DB_HOST', 'sql306.infinityfree.com'),
                'database.connections.mysql.port' => env('IF_DB_PORT', '3306'),
                'database.connections.mysql.database' => env('IF_DB_DATABASE', 'if0_43464969_lmo_records'),
                'database.connections.mysql.username' => env('IF_DB_USERNAME', 'if0_43464969'),
                'database.connections.mysql.password' => env('IF_DB_PASSWORD', 'Josbert001'),
                'session.driver' => 'database',
                'cache.default' => 'database',
                'queue.default' => 'sync',
                'app.debug' => (bool) env('IF_APP_DEBUG', false),
            ]);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        Gate::policy(DocumentActivityLog::class, AuditLogPolicy::class);

        // Share sidebar counts & grouped categories/offices for authenticated shell
        View::composer('layouts.app', function ($view) {
            $user = auth()->user();
            if (! $user) {
                return;
            }

            $baseQuery = Document::query()->visibleTo($user);
            $activeCount = (clone $baseQuery)->where('is_archived', false)->count();
            $archivedCount = (clone $baseQuery)->where('is_archived', true)->count();

            $sidebarCategories = DocumentCategory::query()
                ->where('is_active', true)
                ->withCount(['documents' => fn ($q) => $q->visibleTo($user)->where('is_archived', false)])
                ->orderBy('name')
                ->get();

            $sidebarOffices = Office::query()
                ->where('is_active', true)
                ->withCount(['documents' => fn ($q) => $q->visibleTo($user)->where('is_archived', false)])
                ->orderBy('name')
                ->get();

            $view->with([
                'navCounts' => [
                    'active' => $activeCount,
                    'archived' => $archivedCount,
                ],
                'sidebarCategories' => $sidebarCategories,
                'sidebarOffices' => $sidebarOffices,
            ]);
        });

        // Automatically adapt root URL & HTTPS scheme when accessed via proxy, tunnel, or subdirectory (/register)
        if (! $this->app->runningInConsole() && isset($_SERVER['HTTP_HOST'])) {
            $isHttps = (! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
                || (($_SERVER['SERVER_PORT'] ?? '') == 443);

            $scheme = $isHttps ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'];
            $scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');

            // Strip trailing /public if Apache rewrote to public/index.php
            if (str_ends_with($scriptDir, '/public')) {
                $scriptDir = substr($scriptDir, 0, -7);
            }

            $dynamicRoot = "{$scheme}://{$host}{$scriptDir}";
            config(['app.url' => $dynamicRoot]);
            URL::forceRootUrl($dynamicRoot);

            if ($isHttps) {
                URL::forceScheme('https');
            }

            // Auto-migrate & seed on first visit on InfinityFree if database is empty
            if (str_contains($host, 'freedev.app') || str_contains($host, 'infinityfree') || is_dir('/home/vol15_7')) {
                try {
                    if (! Schema::hasTable('users')) {
                        Artisan::call('migrate', ['--force' => true]);
                        Artisan::call('db:seed', ['--force' => true]);
                    }
                } catch (Throwable) {
                    // Ignore during initial connection setup
                }
            }
        }
    }
}
