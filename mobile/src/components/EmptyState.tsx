import { Ionicons } from '@expo/vector-icons';
import { StyleSheet, Text, View } from 'react-native';
import { colors } from '@/theme/colors';

export function EmptyState({ title, message, icon = 'search-outline' }: { title: string; message: string; icon?: keyof typeof Ionicons.glyphMap }) {
  return (
    <View style={styles.container}>
      <View style={styles.icon}><Ionicons name={icon} size={27} color={colors.primary} /></View>
      <Text style={styles.title}>{title}</Text>
      <Text style={styles.message}>{message}</Text>
    </View>
  );
}

const styles = StyleSheet.create({
  container: { alignItems: 'center', paddingVertical: 55, paddingHorizontal: 34 },
  icon: { width: 58, height: 58, borderRadius: 20, alignItems: 'center', justifyContent: 'center', backgroundColor: colors.primarySoft },
  title: { color: colors.text, fontSize: 17, fontWeight: '800', marginTop: 14 },
  message: { color: colors.muted, fontSize: 13, lineHeight: 19, textAlign: 'center', marginTop: 5 },
});
