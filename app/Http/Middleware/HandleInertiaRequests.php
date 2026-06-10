<?php

namespace App\Http\Middleware;

use App\Enums\AdminPermission;
use App\Enums\SiteFont;
use App\Models\User;
use App\Settings\GeneralSettings;
use App\Settings\StyleSettings;
use App\Support\Branding\BrandingAssetResolver;
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
        $generalSettings = app(GeneralSettings::class);
        $styleSettings = app(StyleSettings::class);
        $font = SiteFont::from($styleSettings->site_font);
        $branding = app(BrandingAssetResolver::class)->resolve();

        return [
            ...parent::share($request),
            'name' => $generalSettings->site_name,
            'locale' => app()->getLocale(),
            'auth' => [
                'user' => $request->user(),
                'abilities' => $this->abilities($request->user()),
            ],
            'style' => [
                ...$styleSettings->toArray(),
                'font_url' => $font->googleFontsUrl(),
            ],
            'branding' => $branding,
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }

    /**
     * @return array<string, bool>
     */
    private function abilities(mixed $user): array
    {
        if (! $user instanceof User) {
            return [
                'manage_settings' => false,
                'view_users' => false,
                'create_users' => false,
                'update_users' => false,
                'manage_branches' => false,
                'manage_departments' => false,
                'manage_queues' => false,
                'manage_categories' => false,
                'view_tickets' => false,
                'create_tickets' => false,
                'update_tickets' => false,
                'manage_sla_policies' => false,
                'manage_approvals' => false,
            ];
        }

        return [
            'manage_settings' => $user->can(AdminPermission::ManageSettings->value),
            'view_users' => $user->can(AdminPermission::ViewUsers->value),
            'create_users' => $user->can(AdminPermission::CreateUsers->value),
            'update_users' => $user->can(AdminPermission::UpdateUsers->value),
            'manage_branches' => $user->can(AdminPermission::ManageBranches->value),
            'manage_departments' => $user->can(AdminPermission::ManageDepartments->value),
            'manage_queues' => $user->can(AdminPermission::ManageQueues->value),
            'manage_categories' => $user->can(AdminPermission::ManageCategories->value),
            'view_tickets' => $user->can(AdminPermission::ViewTickets->value),
            'create_tickets' => $user->can(AdminPermission::CreateTickets->value),
            'update_tickets' => $user->can(AdminPermission::UpdateTickets->value),
            'manage_sla_policies' => $user->can(AdminPermission::ManageSlaPolicies->value),
            'manage_approvals' => $user->can(AdminPermission::ManageApprovals->value),
        ];
    }
}
