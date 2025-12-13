import axios, { AxiosInstance, AxiosRequestConfig } from 'axios';

import type { ApiResponse } from '@/types';

class ApiService {
    private client: AxiosInstance;
    private baseURL = '/api';

    constructor() {
        this.client = axios.create({
            baseURL: this.baseURL,
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
            },
        });

        this.setupInterceptors();
    }

    private setupInterceptors(): void {
        this.client.interceptors.request.use(
            config => {
                const token = localStorage.getItem('auth_token');

                if (token && config.headers) {
                    config.headers.Authorization = `Bearer ${token}`;
                }

                return config;
            },
            error => Promise.reject(error),
        );

        this.client.interceptors.response.use(
            response => response,
            error => {
                if (error.response?.status === 401) {
                    localStorage.removeItem('auth_token');
                    localStorage.removeItem('user');

                    window.location.href = '/login';
                }

                return Promise.reject(error);
            },
        );
    }

    public async get<T = any>(
        endpoint: string,
        config?: AxiosRequestConfig,
    ): Promise<ApiResponse<T>> {
        const response = await this.client.get<ApiResponse<T>>(endpoint, config);

        return response.data;
    }

    public async post<T = any>(
        endpoint: string,
        data?: any,
        config?: AxiosRequestConfig,
    ): Promise<ApiResponse<T>> {
        const response = await this.client.post<ApiResponse<T>>(endpoint, data, config);

        return response.data;
    }

    public async put<T = any>(
        endpoint: string,
        data?: any,
        config?: AxiosRequestConfig,
    ): Promise<ApiResponse<T>> {
        const response = await this.client.put<ApiResponse<T>>(endpoint, data, config);

        return response.data;
    }

    public async patch<T = any>(
        endpoint: string,
        data?: any,
        config?: AxiosRequestConfig,
    ): Promise<ApiResponse<T>> {
        const response = await this.client.patch<ApiResponse<T>>(endpoint, data, config);

        return response.data;
    }

    public async delete<T = any>(
        endpoint: string,
        config?: AxiosRequestConfig,
    ): Promise<ApiResponse<T>> {
        const response = await this.client.delete<ApiResponse<T>>(endpoint, config);

        return response.data;
    }

    public setAuthToken(token: string): void {
        localStorage.setItem('auth_token', token);
    }

    public removeAuthToken(): void {
        localStorage.removeItem('auth_token');
    }

    public getAuthToken(): string | null {
        return localStorage.getItem('auth_token');
    }

    public isAuthenticated(): boolean {
        return !!this.getAuthToken();
    }
}

export default new ApiService();
