import type { BrandingAssets, SharedStyleSettings } from './settings';

export type UserStatus = 'active' | 'disable' | 'suspend';
export type UserRoleName = 'super_admin' | 'it_agent' | 'requester';

export type User = {
    id: string;
    name: string;
    email: string;
    status: UserStatus;
    role?: UserRoleName | null;
    roleLabel?: string | null;
    statusLabel?: string;
    avatar?: string;
    branch_id?: string | null;
    department_id?: string | null;
    branchName?: string | null;
    departmentName?: string | null;
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
    manage_branches: boolean;
    manage_departments: boolean;
    manage_queues: boolean;
    manage_categories: boolean;
    view_tickets: boolean;
    create_tickets: boolean;
    update_tickets: boolean;
    manage_sla_policies: boolean;
    manage_approvals: boolean;
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
