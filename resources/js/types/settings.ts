export type EmptyFormData = Record<string, never>;

export type ProfileFormData = {
    name: string;
    email: string;
};

export type PasswordFormData = {
    current_password: string;
    password: string;
    password_confirmation: string;
};

export type TwoFactorConfirmFormData = {
    code: string;
};

export type GeneralSettings = {
    app_name: string;
    app_logo: string | null;
    app_icon: string | null;
    app_favicon: string | null;
    header_style: headerStyle;
    language: appLanguage;
};

export type headerStyle = 'icon' | 'logo';

export type appLanguage = 'en' | 'id';

export type GeneralSettingsForm = {
    app_name: string;
    app_logo: string | null;
    app_icon: string | null;
    app_favicon: string | null;
    header_style: headerStyle;
    language: appLanguage;
};
