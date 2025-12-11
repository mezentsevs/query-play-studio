import type { AuthResponse, User } from '@/types';

import api from './api';

class AuthService {
    public async login(email: string, password: string): Promise<AuthResponse> {
        const response = await api.post<{ token: string; user: User }>('/auth/login', {
            email,
            password,
        });

        if (response.status === 'success') {
            api.setAuthToken(response.data.token);
            localStorage.setItem('user', JSON.stringify(response.data.user));
        }

        return {
            status: response.status,
            message: response.message || 'Login successful',
            token: response.data.token,
            user: response.data.user,
        };
    }

    public async register(
        email: string,
        password: string,
        username: string,
    ): Promise<AuthResponse> {
        const response = await api.post<{ token: string; user: User }>('/auth/register', {
            email,
            password,
            username,
        });

        if (response.status === 'success') {
            api.setAuthToken(response.data.token);
            localStorage.setItem('user', JSON.stringify(response.data.user));
        }

        return {
            status: response.status,
            message: response.message || 'User registered successfully',
            token: response.data.token,
            user: response.data.user,
        };
    }

    public async logout(): Promise<void> {
        api.removeAuthToken();
        localStorage.removeItem('user');
    }

    public async getCurrentUser(): Promise<User | null> {
        try {
            const response = await api.get<{ user: User }>('/auth/me');
            if (response.status === 'success') {
                localStorage.setItem('user', JSON.stringify(response.data.user));
                return response.data.user;
            }
        } catch (error) {
            console.error('Failed to get current user:', error);
        }
        return null;
    }

    public getStoredUser(): User | null {
        const userStr = localStorage.getItem('user');
        if (userStr) {
            try {
                return JSON.parse(userStr);
            } catch (error) {
                console.error('Failed to parse stored user:', error);
            }
        }
        return null;
    }

    public isLoggedIn(): boolean {
        return api.isAuthenticated();
    }
}

export default new AuthService();
