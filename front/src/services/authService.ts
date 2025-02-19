const API_URL = 'http://localhost/api';

export interface LoginResponse {
    user: string;
    roles: string[];
}

export const login = async (username: string, password: string): Promise<LoginResponse> => {
    const response = await fetch(`${API_URL}/login`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ username, password }),
        credentials: 'include',
    });

    if (!response.ok) {
        const error = await response.json().catch(() => null);
        throw new Error(error?.message || 'Login failed');
    }

    return response.json();
};

export const logout = async (): Promise<void> => {
    const response = await fetch(`${API_URL}/logout`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'include',
    });

    if (!response.ok) {
        throw new Error('Logout failed');
    }
};

export const getCurrentUser = async (): Promise<LoginResponse> => {
    const response = await fetch(`${API_URL}/me`, {
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'include',
    });

    if (!response.ok) {
        throw new Error('Failed to get current user');
    }

    return response.json();
}; 