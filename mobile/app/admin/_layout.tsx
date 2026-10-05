import { Redirect, Stack } from 'expo-router';
import { colors } from '@/theme/colors';
import { useAuthStore } from '@/store/auth';

export default function AdminLayout() {
  const user = useAuthStore((state) => state.user);
  const role = user?.primary_role ?? user?.roles?.[0];

  if (!['admin', 'manager'].includes(role ?? '')) {
    return <Redirect href="/(tabs)" />;
  }

  return (
    <Stack
      screenOptions={{
        headerStyle: { backgroundColor: colors.primary },
        headerTintColor: '#FFFFFF',
        headerTitleStyle: { fontWeight: '800' },
        contentStyle: { backgroundColor: colors.background },
      }}
    />
  );
}
