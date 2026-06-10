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

export type NotificationItem = {
    id: string;
    type: string;
    ticket_id: string | null;
    ticket_number: string | null;
    subject: string | null;
    message: string;
    read_at?: string | null;
    created_at: string;
};

export type SharedPageProps = {
    name: string;
    locale: string;
    auth: {
        user: User | null;
        abilities: AuthAbilities;
        unreadNotificationsCount: number;
        notifications: NotificationItem[];
    };
    style: SharedStyleSettings;
    branding: BrandingAssets;
    sidebarOpen: boolean;
    [key: string]: unknown;
};

export type AuthenticatedSharedPageProps = SharedPageProps & {
    auth: {
        user: User;
        abilities: AuthAbilities;
        unreadNotificationsCount: number;
        notifications: NotificationItem[];
    };
};
