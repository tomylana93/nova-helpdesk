export type GeneralSettings = {
    site_name: string;
    site_description: string;
    site_locale: string;
    [key: string]: unknown;
};

export type StyleSettings = {
    site_logo_style: LogoStyle;
    site_auth_layout: AuthLayout;
    site_layout: AdminLayout;
    site_theme: Theme;
    site_font: Font;
    site_icon_path: string;
    site_icon_alt_path: string;
    site_logo_path: string;
    site_logo_alt_path: string;
    site_favicon_path: string;
    [key: string]: unknown;
};

type LogoStyle = 'icon' | 'logo';

export type AuthLayout = 'simple' | 'split' | 'card';

type AdminLayout = 'sidebar' | 'header';

export type Theme =
    | 'zinc'
    | 'slate'
    | 'emerald'
    | 'rose'
    | 'indigo'
    | 'violet'
    | 'cyan'
    | 'orange'
    | 'teal'
    | 'fuchsia';

export type BrandingAssets = {
    icon: string;
    icon_alt: string;
    logo: string;
    logo_alt: string;
    favicon: string;
    favicon_any: string;
    favicon_svg: string;
    apple_touch_icon: string;
};

export type SharedStyleSettings = StyleSettings & {
    font_url: string;
};

type Font =
    | 'inter'
    | 'sora-inter'
    | 'plus-jakarta-dm-sans'
    | 'space-grotesk-inter'
    | 'nunito-plus-jakarta';
