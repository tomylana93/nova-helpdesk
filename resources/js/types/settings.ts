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
