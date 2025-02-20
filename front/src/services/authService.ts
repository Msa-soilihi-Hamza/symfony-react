const API_URL = 'http://localhost:80/api';

export interface LoginResponse {
    user: string;
    roles: string[];
    token: string;
}

const TOKEN_KEY = 'jwt_token';

export const setAuthToken = (token: string) => {
    localStorage.setItem(TOKEN_KEY, token);
};

export const getAuthToken = (): string | null => {
    return localStorage.getItem(TOKEN_KEY);
};

export const removeAuthToken = () => {
    localStorage.removeItem(TOKEN_KEY);
};

const getAuthHeaders = () => {
    const token = getAuthToken();
    return {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': token ? `Bearer ${token}` : '',
    };
};

export const login = async (username: string, password: string): Promise<LoginResponse> => {
    const response = await fetch(`${API_URL}/login`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
        body: JSON.stringify({ username, password }),
    });

    if (!response.ok) {
        const error = await response.json().catch(() => null);
        throw new Error(error?.message || 'Échec de la connexion');
    }

    const data = await response.json();
    setAuthToken(data.token);
    return data;
};

export const logout = async (): Promise<void> => {
    const token = getAuthToken();
    if (!token) {
        removeAuthToken();
        return;
    }

    try {
        const response = await fetch(`${API_URL}/logout`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`
            },
            body: JSON.stringify({})
        });

        if (!response.ok) {
            console.error('Erreur de déconnexion:', response.status, response.statusText);
            const error = await response.json().catch(() => ({ message: `Erreur HTTP: ${response.status}` }));
            throw new Error(error.message || 'Échec de la déconnexion');
        }

        await response.json();
    } catch (error) {
        console.error('Erreur lors de la déconnexion:', error);
        throw error;
    } finally {
        removeAuthToken();
    }
};

export const getCurrentUser = async (): Promise<LoginResponse> => {
    const response = await fetch(`${API_URL}/me`, {
        headers: getAuthHeaders(),
    });

    if (!response.ok) {
        throw new Error('Échec de la récupération des informations utilisateur');
    }

    return response.json();
}; 