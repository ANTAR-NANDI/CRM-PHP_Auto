import { create } from 'zustand';
import { api, setAccessToken } from '@/api/client';
import { tokenStorage } from '@/lib/token-storage';
import type { Employee } from '@/types/api';

const TOKEN_KEY = 'pharmacy_access_token';

type AuthState = {
  user: Employee | null;
  token: string | null;
  isReady: boolean;
  login: (login: string, password: string) => Promise<void>;
  hydrate: () => Promise<void>;
  logout: () => Promise<void>;
};

export const useAuthStore = create<AuthState>((set, get) => ({
  user: null,
  token: null,
  isReady: false,
  login: async (login, password) => {
    const response = await api.post('/auth/login', { login, password, device_name: 'PharmaPOS mobile' });
    const { token, user } = response.data.data as { token: string; user: Employee };
    await tokenStorage.set(TOKEN_KEY, token);
    setAccessToken(token);
    set({ token, user });
  },
  hydrate: async () => {
    if (get().isReady) return;
    const token = await tokenStorage.get(TOKEN_KEY);
    if (!token) {
      set({ isReady: true });
      return;
    }
    try {
      setAccessToken(token);
      const response = await api.get('/auth/me');
      set({ token, user: response.data.data, isReady: true });
    } catch {
      await tokenStorage.remove(TOKEN_KEY);
      setAccessToken(null);
      set({ token: null, user: null, isReady: true });
    }
  },
  logout: async () => {
    try {
      await api.post('/auth/logout');
    } finally {
      await tokenStorage.remove(TOKEN_KEY);
      setAccessToken(null);
      set({ token: null, user: null });
    }
  },
}));
