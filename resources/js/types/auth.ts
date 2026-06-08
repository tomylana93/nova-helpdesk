import type { BrandingAssets, SharedStyleSettings } from './settings';

export type UserStatus = 'active' | 'disable' | 'suspend';
export type UserRoleName =
    | 'super_admin'
    | 'admin'
    | 'operations_manager'
    | 'branch_manager'
    | 'dispatcher'
    | 'warehouse_supervisor'
    | 'fleet_coordinator'
    | 'procurement_officer'
    | 'sales_executive'
    | 'customer_service'
    | 'finance_officer'
    | 'compliance_officer';

export type User = {
    id: string;
    name: string;
    email: string;
    status: UserStatus;
    role?: UserRoleName | null;
    roleLabel?: string | null;
    statusLabel?: string;
    avatar?: string;
    created_at: string;
    updated_at: string;
    [key: string]: unknown;
};

export type PasswordRulesProps = {
    passwordRules: string;
};

export type AuthAbilities = {
    manage_settings: boolean;
    view_users: boolean;
    create_users: boolean;
    update_users: boolean;
};

export type SharedPageProps = {
    name: string;
    locale: string;
    auth: {
        user: User | null;
        abilities: AuthAbilities;
    };
    style: SharedStyleSettings;
    branding: BrandingAssets;
    sidebarOpen: boolean;
    [key: string]: unknown;
};

export type AuthenticatedSharedPageProps = SharedPageProps & {
    auth: {
        user: User;
    };
};
