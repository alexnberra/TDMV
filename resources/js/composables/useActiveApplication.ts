const KEY = 'active_application_id';

function parseId(value: string | null): number | null {
    if (!value) {
        return null;
    }

    const parsed = Number.parseInt(value, 10);

    return Number.isNaN(parsed) ? null : parsed;
}

export function getActiveApplicationId(): number | null {
    const url = new URL(window.location.href);
    const fromQuery = parseId(url.searchParams.get('application_id'));
    if (fromQuery) {
        localStorage.setItem(KEY, String(fromQuery));

        return fromQuery;
    }

    return parseId(localStorage.getItem(KEY));
}

export function setActiveApplicationId(applicationId: number): void {
    localStorage.setItem(KEY, String(applicationId));
}

export function clearActiveApplicationId(): void {
    localStorage.removeItem(KEY);
}
