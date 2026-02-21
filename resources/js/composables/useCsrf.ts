type CsrfHeaders = Record<string, string>;

function getMetaCsrfToken(): string | null {
    return (
        document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute('content') ?? null
    );
}

function getXsrfTokenFromCookie(): string | null {
    const match = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]+)/);
    if (!match?.[1]) {
        return null;
    }

    try {
        return decodeURIComponent(match[1]);
    } catch {
        return match[1];
    }
}

export function useCsrf() {
    const csrfToken = () => getMetaCsrfToken();
    const xsrfTokenFromCookie = () => getXsrfTokenFromCookie();

    const csrfHeaders = (): CsrfHeaders => {
        const token = csrfToken();
        if (token) {
            return { 'X-CSRF-TOKEN': token };
        }

        const xsrf = xsrfTokenFromCookie();
        if (xsrf) {
            return { 'X-XSRF-TOKEN': xsrf };
        }

        return {};
    };

    const buildHeaders = (extra: CsrfHeaders = {}): CsrfHeaders => ({
        'X-Requested-With': 'XMLHttpRequest',
        ...csrfHeaders(),
        ...extra,
    });

    return {
        csrfToken,
        xsrfTokenFromCookie,
        csrfHeaders,
        buildHeaders,
    };
}