import { SafeAreaView, StyleSheet, Text, View } from 'react-native';
import { colors } from '@/theme/colors';

export function PageHeader({ title, subtitle }: { title: string; subtitle: string }) {
  return (
    <SafeAreaView style={styles.safe}>
      <View style={styles.header}>
        <Text style={styles.title}>{title}</Text>
        <Text style={styles.subtitle}>{subtitle}</Text>
      </View>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safe: { backgroundColor: colors.primary },
  header: { paddingHorizontal: 18, paddingVertical: 17 },
  title: { color: colors.surface, fontSize: 23, fontWeight: '800' },
  subtitle: { color: '#D7F3EC', fontSize: 11, marginTop: 2 },
});
