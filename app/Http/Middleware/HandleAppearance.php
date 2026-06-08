<?php

namespace App\Http\Middleware;

use App\Enums\SiteFont;
use App\Settings\GeneralSettings;
use App\Settings\StyleSettings;
use App\Support\Branding\BrandingAssetResolver;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class HandleAppearance
{
    public function __construct(
        private readonly GeneralSettings $generalSettings,
        private readonly StyleSettings $styleSettings,
        private readonly BrandingAssetResolver $brandingAssetResolver,
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $branding = $this->brandingAssetResolver->resolve();

        View::share('appearance', $request->cookie('appearance') ?? 'system');
        View::share('siteName', $this->generalSettings->site_name);
        View::share('siteTheme', $this->styleSettings->site_theme);
        View::share('siteFont', $this->styleSettings->site_font);
        View::share('siteFontUrl', SiteFont::from($this->styleSettings->site_font)->googleFontsUrl());
        View::share('siteFaviconAnyUrl', $branding['favicon_any']);
        View::share('siteFaviconSvgUrl', $branding['favicon_svg']);
        View::share('siteAppleTouchIconUrl', $branding['apple_touch_icon']);

        return $next($request);
    }
}
