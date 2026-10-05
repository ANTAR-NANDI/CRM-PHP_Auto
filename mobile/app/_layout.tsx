import { useEffect } from 'react';
import { Stack, useRouter, useSegments } from 'expo-router';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { StatusBar } from 'expo-status-bar';
import { SafeAreaProvider } from 'react-native-safe-area-context';
import { ScreenLoader } from '@/components/ScreenLoader';
import { useAuthStore } from '@/store/auth';

const queryClient = new QueryClient({
  defaultOptions: { queries: { staleTime: 30_000, retry: 1, refetchOnReconnect: true } },
});

function AuthGate() {
  const router = useRouter();
  const segments = useSegments();
  const { token, isReady, hydrate } = useAuthStore();

  useEffect(() => { void hydrate(); }, [hydrate]);
  useEffect(() => {
    if (!isReady) return;
    const isLoginRoute = segments[0] === 'login';
    if (!token && !isLoginRoute) router.replace('/login');
    if (token && isLoginRoute) router.replace('/(tabs)');
  }, [isReady, router, segments, token]);

  if (!isReady) return <ScreenLoader />;

  return (
    <>
      <StatusBar style="light" />
      <Stack screenOptions={{ headerShown: false, animation: 'fade' }} />
    </>
  );
}

export default function RootLayout() {
  return <SafeAreaProvider><QueryClientProvider client={queryClient}><AuthGate /></QueryClientProvider></SafeAreaProvider>;
}
