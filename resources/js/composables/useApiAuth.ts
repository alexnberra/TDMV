import { computed, reactive } from 'vue';
import { authApi, type ApiUser } from '@/lib/api';

interface AuthState {
    user: ApiUser | null;
    loading: boolean;
    initialized: boolean;
}

const state = reactive<AuthState>({
    user: null,
    loading: false,
    initialized: false,
});

const tokenKey = 'auth_token';

function getToken(): string | null {
    return localStorage.getItem(tokenKey);
}

function setToken(token: string): void {
    localStorage.setItem(tokenKey, token);
}

function clearAuth(): void {
    localStorage.removeItem(tokenKey);
    state.user = null;
}

async function fetchUser(): Promise<ApiUser | null> {
    if (!getToken()) {
        state.user = null;
        state.initialized = true;

        return null;
    }

    state.loading = true;

    try {
        const response = await authApi.user();
        state.user = response.data.user;
        state.initialized = true;

        return state.user;
    } catch {
        clearAuth();
        state.initialized = true;

        return null;
    } finally {
        state.loading = false;
    }
}

async function ensureUser(): Promise<ApiUser | null> {
    if (state.initialized && state.user) {
        return state.user;
    }

    if (state.loading) {
        return state.user;
    }

    return fetchUser();
}

async function login(email: string, password: string): Promise<void> {
    state.loading = true;

    try {
        const response = await authApi.login({ email, password });
        setToken(response.data.token);
        state.user = response.data.user;
        state.initialized = true;
    } finally {
        state.loading = false;
    }
}

async function register(data: Record<string, unknown>): Promise<void> {
    state.loading = true;

    try {
        const response = await authApi.register(data);
        setToken(response.data.token);
        state.user = response.data.user;
        state.initialized = true;
    } finally {
        state.loading = false;
    }
}

async function logout(): Promise<void> {
    try {
        await authApi.logout();
    } catch {
        // Token may already be invalid.
    } finally {
        clearAuth();
    }
}

export function useApiAuth() {
    return {
        user: computed(() => state.user),
        isLoading: computed(() => state.loading),
        isAuthenticated: computed(() => !!state.user),
        login,
        register,
        logout,
        ensureUser,
        fetchUser,
    };
}
