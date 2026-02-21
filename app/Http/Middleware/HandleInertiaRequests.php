<?php

namespace App\Http\Middleware;

use App\Settings\GeneralSettings;
use Diglactic\Breadcrumbs\Breadcrumbs;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        /** @var GeneralSettings $generalSettings */
        $generalSettings = app(GeneralSettings::class);
        
        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $request->user(),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'breadcrumbs' => fn (): array => $this->resolveBreadcrumbs($request),
             'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'warning' => fn () => $request->session()->get('warning'),
                'info' => fn () => $request->session()->get('info'),
            ],
            'settings' => [
                'general' => [
                    'app_name' => $generalSettings->app_name,
                    'app_logo' => $generalSettings->app_logo ?? asset('assets/images/logo.png'),
                    'app_icon' => $generalSettings->app_icon ?? asset('assets/images/icon.png'),
                    'app_favicon' => $generalSettings->app_favicon ?? asset('assets/images/favicon.ico'),
                    'header_style' => $generalSettings->header_style,
                    'language' => $generalSettings->language,
                ],
            ],
        ];
    }

    /**
     * Resolve the breadcrumb trail for the current route.
     *
     * @return array<int, array{title: string, href: string|null}>
     */
    protected function resolveBreadcrumbs(Request $request): array
    {
        $route = $request->route();

        if (! $route) {
            return [];
        }

        $routeName = $route->getName();

        if (! is_string($routeName) || ! Breadcrumbs::exists($routeName)) {
            return [];
        }

        return Breadcrumbs::generate($routeName, ...array_values($route->parametersWithoutNulls()))
            ->map(fn (object $breadcrumb): array => [
                'title' => (string) $breadcrumb->title,
                'href' => $breadcrumb->url ?: null,
            ])
            ->values()
            ->all();
    }
}
