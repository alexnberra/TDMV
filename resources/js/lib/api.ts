import axios from 'axios';

export interface ApiUser {
    id: number;
    first_name: string;
    last_name: string;
    email: string;
    role: 'member' | 'staff' | 'admin';
    tribe?: { id: number; name: string };
}

export interface PaginatedResponse<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

const api = axios.create({
    baseURL: '/api',
    headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
    },
});

api.interceptors.request.use((config) => {
    const token = localStorage.getItem('auth_token');

    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }

    return config;
});

api.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 401) {
            localStorage.removeItem('auth_token');

            if (!window.location.pathname.startsWith('/login')) {
                window.location.href = '/login';
            }
        }

        return Promise.reject(error);
    },
);

export const authApi = {
    register: (data: Record<string, unknown>) => api.post('/register', data),
    login: (data: { email: string; password: string }) => api.post('/login', data),
    logout: () => api.post('/logout'),
    user: () => api.get('/user'),
};

export const vehicleApi = {
    all: () => api.get('/vehicles'),
    one: (id: string | number) => api.get(`/vehicles/${id}`),
    create: (payload: Record<string, unknown>) => api.post('/vehicles', payload),
    update: (id: string | number, payload: Record<string, unknown>) => api.put(`/vehicles/${id}`, payload),
    remove: (id: string | number) => api.delete(`/vehicles/${id}`),
    renewalHistory: (id: string | number) => api.get(`/vehicles/${id}/renewal-history`),
};

export const applicationApi = {
    all: () => api.get('/applications'),
    one: (id: string | number) => api.get(`/applications/${id}`),
    create: (payload: Record<string, unknown>) => api.post('/applications', payload),
    update: (id: string | number, payload: Record<string, unknown>) => api.put(`/applications/${id}`, payload),
    submit: (id: string | number, payload: Record<string, unknown>) => api.post(`/applications/${id}/submit`, payload),
    cancel: (id: string | number) => api.post(`/applications/${id}/cancel`),
    timeline: (id: string | number) => api.get(`/applications/${id}/timeline`),
};

export const documentApi = {
    upload: (applicationId: string | number, formData: FormData) =>
        api.post(`/applications/${applicationId}/documents`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        }),
    remove: (id: string | number) => api.delete(`/documents/${id}`),
    download: (id: string | number) => api.get(`/documents/${id}/download`, { responseType: 'blob' }),
};

export const paymentApi = {
    create: (applicationId: string | number, payload: Record<string, unknown>) =>
        api.post(`/applications/${applicationId}/payments`, payload),
    one: (id: string | number) => api.get(`/payments/${id}`),
};

export const householdApi = {
    all: () => api.get('/households'),
    create: (payload: Record<string, unknown>) => api.post('/households', payload),
    one: (id: string | number) => api.get(`/households/${id}`),
    update: (id: string | number, payload: Record<string, unknown>) => api.put(`/households/${id}`, payload),
    remove: (id: string | number) => api.delete(`/households/${id}`),
    addMember: (id: string | number, payload: Record<string, unknown>) => api.post(`/households/${id}/members`, payload),
    removeMember: (id: string | number, memberId: string | number) => api.delete(`/households/${id}/members/${memberId}`),
};

export const appointmentApi = {
    all: (params?: Record<string, string | number>) => api.get('/appointments', { params }),
    create: (payload: Record<string, unknown>) => api.post('/appointments', payload),
    one: (id: string | number) => api.get(`/appointments/${id}`),
    update: (id: string | number, payload: Record<string, unknown>) => api.put(`/appointments/${id}`, payload),
    cancel: (id: string | number, cancel_reason: string) => api.post(`/appointments/${id}/cancel`, { cancel_reason }),
    remove: (id: string | number) => api.delete(`/appointments/${id}`),
};

export const notificationApi = {
    all: () => api.get<PaginatedResponse<Record<string, unknown>>>('/notifications'),
    markRead: (id: string) => api.post(`/notifications/${id}/read`),
    markAllRead: () => api.post('/notifications/read-all'),
    preferences: () => api.get('/notification-preferences'),
    updatePreferences: (payload: Record<string, boolean>) => api.put('/notification-preferences', payload),
};

export const assistantApi = {
    query: (payload: { query: string; application_id?: number | null; channel?: string; context?: Record<string, unknown> }) =>
        api.post('/assistant/query', payload),
};

export const referenceApi = {
    officeLocations: () => api.get('/office-locations'),
    faqs: () => api.get('/faqs'),
};

export const adminApi = {
    stats: () => api.get('/admin/dashboard/stats'),
    applications: (params?: Record<string, string | number>) => api.get('/admin/applications', { params }),
    updateApplicationStatus: (id: string | number, payload: Record<string, unknown>) =>
        api.put(`/admin/applications/${id}/status`, payload),
    requestMoreInfo: (id: string | number, message: string) =>
        api.post(`/admin/applications/${id}/request-info`, { message }),
    phase3Insights: () => api.get('/admin/phase3/insights'),
    runPhase3Automation: (payload: { dry_run?: boolean; rule_keys?: string[] }) => api.post('/admin/phase3/automation/run', payload),
};

export const businessApi = {
    all: () => api.get('/business-accounts'),
    create: (payload: Record<string, unknown>) => api.post('/business-accounts', payload),
    one: (id: string | number) => api.get(`/business-accounts/${id}`),
    update: (id: string | number, payload: Record<string, unknown>) => api.put(`/business-accounts/${id}`, payload),
    addMember: (id: string | number, payload: Record<string, unknown>) => api.post(`/business-accounts/${id}/members`, payload),
    assignFleetVehicle: (id: string | number, payload: Record<string, unknown>) => api.post(`/business-accounts/${id}/fleet-vehicles`, payload),
    removeFleetVehicle: (id: string | number, fleetVehicleId: string | number) =>
        api.delete(`/business-accounts/${id}/fleet-vehicles/${fleetVehicleId}`),
};

export const insuranceApi = {
    all: (params?: Record<string, string | number>) => api.get('/insurance-policies', { params }),
    create: (payload: Record<string, unknown>) => api.post('/insurance-policies', payload),
    one: (id: string | number) => api.get(`/insurance-policies/${id}`),
    update: (id: string | number, payload: Record<string, unknown>) => api.put(`/insurance-policies/${id}`, payload),
};

export const complianceApi = {
    emissions: (params?: Record<string, string | number>) => api.get('/emissions-tests', { params }),
    createEmissions: (payload: Record<string, unknown>) => api.post('/emissions-tests', payload),
    inspections: (params?: Record<string, string | number>) => api.get('/vehicle-inspections', { params }),
    createInspection: (payload: Record<string, unknown>) => api.post('/vehicle-inspections', payload),
};

export const benefitsApi = {
    memberBenefits: (params?: Record<string, string | number>) => api.get('/member-benefits', { params }),
    createMemberBenefit: (payload: Record<string, unknown>) => api.post('/member-benefits', payload),
    updateMemberBenefit: (id: string | number, payload: Record<string, unknown>) => api.put(`/member-benefits/${id}`, payload),
    disabilityPlacards: (params?: Record<string, string | number>) => api.get('/disability-placards', { params }),
    createDisabilityPlacard: (payload: Record<string, unknown>) => api.post('/disability-placards', payload),
    updateDisabilityPlacard: (id: string | number, payload: Record<string, unknown>) => api.put(`/disability-placards/${id}`, payload),
};

export default api;
