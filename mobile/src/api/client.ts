import axios from 'axios';

const baseURL = process.env.EXPO_PUBLIC_API_URL ?? 'https://pharmacy.w3xplorers.com/api/v1';
let accessToken: string | null = null;

export const api = axios.create({
  baseURL,
  timeout: 15000,
  headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
});

api.interceptors.request.use((config) => {
  if (accessToken) config.headers.Authorization = `Bearer ${accessToken}`;
  return config;
});

export function setAccessToken(token: string | null) {
  accessToken = token;
}

export function apiErrorMessage(error: unknown): string {
  if (axios.isAxiosError(error)) {
    const errors = error.response?.data?.errors as Record<string, string[]> | undefined;
    const firstError = errors ? Object.values(errors).flat()[0] : null;
    return firstError ?? error.response?.data?.message ?? (error.code === 'ECONNABORTED'
      ? 'The server took too long to respond.'
      : 'Could not connect to the pharmacy server.');
  }

  return 'Something went wrong. Please try again.';
}
