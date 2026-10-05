import { ActivityIndicator, StyleSheet, Text, View } from 'react-native';
import { colors } from '@/theme/colors';

export function ScreenLoader({ label = 'Preparing your pharmacy…' }: { label?: string }) {
  return (
    <View style={styles.container}>
      <View style={styles.logo}>
        <View style={[styles.cross, styles.crossVertical]} />
        <View style={[styles.cross, styles.crossHorizontal]} />
      </View>
      <ActivityIndicator color={colors.primary} size="large" />
      <Text style={styles.label}>{label}</Text>
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, alignItems: 'center', justifyContent: 'center', gap: 16, backgroundColor: colors.background },
  logo: { width: 68, height: 68, borderRadius: 22, backgroundColor: colors.primary, position: 'relative', marginBottom: 4 },
  cross: { position: 'absolute', backgroundColor: colors.surface, borderRadius: 4, left: '50%', top: '50%' },
  crossVertical: { width: 14, height: 40, marginLeft: -7, marginTop: -20 },
  crossHorizontal: { width: 40, height: 14, marginLeft: -20, marginTop: -7 },
  label: { color: colors.muted, fontSize: 14 },
});
