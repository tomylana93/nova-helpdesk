export type User = {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    [key: string]: unknown;
};

export type Auth = {
    user: User;
};

export type TwoFactorConfigContent = {
    title: string;
    description: string;
    buttonText: string;
};

export type LoginFormData = {
    email: string;
    password: string;
    remember: boolean;
};

export type RegisterFormData = {
    name: string;
    email: string;
    password: string;
    password_confirmation: string;
};
