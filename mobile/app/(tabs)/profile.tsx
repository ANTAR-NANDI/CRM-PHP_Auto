import { Ionicons } from '@expo/vector-icons';
import { useRouter } from 'expo-router';
import { Pressable, StyleSheet, Text, View } from 'react-native';
import { PageHeader } from '@/components/PageHeader';
import { useAuthStore } from '@/store/auth';
import { colors } from '@/theme/colors';

export default function ProfileScreen() {
  const router = useRouter();
  const user = useAuthStore((state) => state.user);
  const logout = useAuthStore((state) => state.logout);

  const signOut = async () => {
    await logout();
    router.replace('/login');
  };

  return (
    <View style={styles.screen}>
      <PageHeader title="Profile" subtitle="Your employee account and mobile session" />
      <View style={styles.content}>
        <View style={styles.avatar}><Text style={styles.initial}>{user?.name?.[0]?.toUpperCase() ?? 'E'}</Text></View>
        <Text style={styles.name}>{user?.name}</Text>
        <Text style={styles.role}>{user?.roles.join(' · ')}</Text>
        <View style={styles.card}>
          <Info icon="id-card-outline" label="Employee ID" value={user?.employee_code ?? 'Not assigned'} />
          <View style={styles.divider} />
          <Info icon="mail-outline" label="Email" value={user?.email ?? '—'} />
          <View style={styles.divider} />
          <Info icon="briefcase-outline" label="Designation" value={user?.designation ?? 'Not assigned'} />
        </View>
        <Pressable style={styles.logout} onPress={signOut}>
          <Ionicons name="log-out-outline" size={21} color={colors.danger} />
          <Text style={styles.logoutText}>Log out from this device</Text>
        </Pressable>
      </View>
    </View>
  );
}

function Info({ icon, label, value }: { icon: keyof typeof Ionicons.glyphMap; label: string; value: string }) {
  return <View style={styles.info}><Ionicons name={icon} size={21} color={colors.primary} /><View><Text style={styles.label}>{label}</Text><Text style={styles.value}>{value}</Text></View></View>;
}

const styles = StyleSheet.create({
  screen: { flex: 1, backgroundColor: colors.background },
  content: { alignItems: 'center', padding: 20 },
  avatar: { width: 86, height: 86, borderRadius: 30, backgroundColor: colors.primarySoft, alignItems: 'center', justifyContent: 'center', marginTop: 8 },
  initial: { color: colors.primary, fontSize: 35, fontWeight: '900' },
  name: { color: colors.text, fontSize: 21, fontWeight: '900', marginTop: 12 },
  role: { color: colors.primaryDark, fontSize: 12, fontWeight: '700', textTransform: 'capitalize', marginTop: 3 },
  card: { alignSelf: 'stretch', backgroundColor: colors.surface, borderWidth: 1, borderColor: colors.border, borderRadius: 18, marginTop: 25, paddingHorizontal: 16 },
  info: { flexDirection: 'row', alignItems: 'center', gap: 12, paddingVertical: 15 },
  label: { color: colors.muted, fontSize: 10 },
  value: { color: colors.text, fontSize: 13, fontWeight: '700', marginTop: 2 },
  divider: { height: 1, backgroundColor: colors.border },
  logout: { alignSelf: 'stretch', height: 52, borderWidth: 1, borderColor: '#F1C8CE', borderRadius: 14, flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 9, marginTop: 18, backgroundColor: '#FFF7F8' },
  logoutText: { color: colors.danger, fontSize: 13, fontWeight: '800' },
});
