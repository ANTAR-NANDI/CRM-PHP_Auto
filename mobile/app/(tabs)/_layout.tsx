import { Ionicons } from '@expo/vector-icons';
import { Tabs } from 'expo-router';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { colors } from '@/theme/colors';
import { useAuthStore } from '@/store/auth';

const icons: Record<string, keyof typeof Ionicons.glyphMap> = {
  index: 'cart-outline',
  sales: 'receipt-outline',
  stock: 'file-tray-stacked-outline',
  held: 'pause-circle-outline',
  admin: 'grid-outline',
  profile: 'person-outline',
};

export default function TabLayout() {
  const insets = useSafeAreaInsets();
  const user = useAuthStore((state) => state.user);
  const role = user?.primary_role ?? user?.roles[0];
  const canViewSales = true;
  const isAdmin = ['admin', 'manager'].includes(role ?? '');
  return (
    <Tabs
      screenOptions={({ route }) => ({
        headerShown: false,
        tabBarActiveTintColor: colors.primary,
        tabBarInactiveTintColor: '#7B8493',
        tabBarLabelStyle: { fontSize: 11, fontWeight: '600', marginBottom: 2 },
        tabBarStyle: {
          height: 60 + Math.max(insets.bottom, 8),
          paddingTop: 7,
          paddingBottom: Math.max(insets.bottom, 8),
          borderTopColor: colors.border,
          backgroundColor: colors.surface,
        },
        tabBarIcon: ({ color, size }) => <Ionicons name={icons[route.name] ?? 'ellipse-outline'} color={color} size={size} />,
      })}
    >
      <Tabs.Screen name="index" options={{ title: 'POS' }} />
      <Tabs.Screen name="held" options={{ title: 'Held' }} />
      <Tabs.Screen name="sales" options={{ title: 'Sales', href: canViewSales ? undefined : null }} />
      <Tabs.Screen name="stock" options={{ title: 'Stock', href: role === 'salesperson' ? null : undefined }} />
      <Tabs.Screen name="admin" options={{ title: 'Admin', href: isAdmin ? undefined : null }} />
      <Tabs.Screen name="profile" options={{ title: 'Profile' }} />
    </Tabs>
  );
}
