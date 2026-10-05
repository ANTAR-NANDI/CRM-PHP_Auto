import { useState } from 'react';
import { Ionicons } from '@expo/vector-icons';
import { useRouter } from 'expo-router';
import { StatusBar } from 'expo-status-bar';
import {
  ActivityIndicator, KeyboardAvoidingView, Platform, Pressable, SafeAreaView,
  ScrollView, StyleSheet, Text, TextInput, View,
} from 'react-native';
import { apiErrorMessage } from '@/api/client';
import { useAuthStore } from '@/store/auth';
import { colors } from '@/theme/colors';

export default function LoginScreen() {
  const router = useRouter();
  const loginEmployee = useAuthStore((state) => state.login);
  const [login, setLogin] = useState('');
  const [password, setPassword] = useState('');
  const [showPassword, setShowPassword] = useState(false);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');

  const submit = async () => {
    if (!login.trim() || !password) {
      setError('Enter your employee ID/email and password.');
      return;
    }

    try {
      setLoading(true);
      setError('');
      await loginEmployee(login.trim(), password);
      router.replace('/(tabs)');
    } catch (requestError) {
      setError(apiErrorMessage(requestError));
    } finally {
      setLoading(false);
    }
  };

  return (
    <SafeAreaView style={styles.safeArea}>
      <StatusBar style="dark" />
      <KeyboardAvoidingView style={styles.flex} behavior={Platform.OS === 'ios' ? 'padding' : undefined}>
        <ScrollView contentContainerStyle={styles.content} keyboardShouldPersistTaps="handled">
          <View style={styles.hero}>
            <View style={styles.glowOne} />
            <View style={styles.glowTwo} />
            <Text style={styles.brand}>PharmaPOS</Text>
            <Text style={styles.subtitle}>Employee Login</Text>
            <View style={styles.logoShadow}>
              <View style={styles.logo}>
                <View style={[styles.cross, styles.crossVertical]} />
                <View style={[styles.cross, styles.crossHorizontal]} />
              </View>
            </View>
            <Text style={styles.heroNote}>Fast, accurate pharmacy sales from your phone</Text>
          </View>

          <View style={styles.formCard}>
            <Text style={styles.welcome}>Welcome back</Text>
            <Text style={styles.formHint}>Sign in with the account created by your administrator.</Text>

            <Text style={styles.label}>Employee ID or email</Text>
            <View style={[styles.inputRow, error && !login.trim() ? styles.inputError : null]}>
              <Ionicons name="person-outline" size={21} color={colors.muted} />
              <TextInput
                value={login}
                onChangeText={setLogin}
                autoCapitalize="none"
                autoCorrect={false}
                placeholder="EMP-001 or employee@email.com"
                placeholderTextColor="#98A1AF"
                style={styles.input}
                returnKeyType="next"
              />
            </View>

            <Text style={styles.label}>Password</Text>
            <View style={styles.inputRow}>
              <Ionicons name="lock-closed-outline" size={21} color={colors.muted} />
              <TextInput
                value={password}
                onChangeText={setPassword}
                placeholder="Enter your password"
                placeholderTextColor="#98A1AF"
                secureTextEntry={!showPassword}
                style={styles.input}
                returnKeyType="done"
                onSubmitEditing={submit}
              />
              <Pressable onPress={() => setShowPassword((value) => !value)} hitSlop={12}>
                <Ionicons name={showPassword ? 'eye-off-outline' : 'eye-outline'} size={22} color={colors.muted} />
              </Pressable>
            </View>

            {error ? (
              <View style={styles.errorBox}>
                <Ionicons name="alert-circle-outline" size={18} color={colors.danger} />
                <Text style={styles.errorText}>{error}</Text>
              </View>
            ) : null}

            <Pressable style={({ pressed }) => [styles.button, pressed && styles.buttonPressed]} onPress={submit} disabled={loading}>
              {loading ? <ActivityIndicator color={colors.surface} /> : <Text style={styles.buttonText}>Login securely</Text>}
            </Pressable>
          </View>

          <Text style={styles.version}>PharmaPOS mobile · v1.0.0</Text>
        </ScrollView>
      </KeyboardAvoidingView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  flex: { flex: 1 },
  safeArea: { flex: 1, backgroundColor: colors.surface },
  content: { flexGrow: 1, backgroundColor: colors.surface, paddingBottom: 24 },
  hero: { minHeight: 330, alignItems: 'center', justifyContent: 'center', overflow: 'hidden', paddingTop: 26 },
  glowOne: { position: 'absolute', width: 230, height: 230, borderRadius: 115, backgroundColor: '#E4F6F1', top: -75, right: -70 },
  glowTwo: { position: 'absolute', width: 170, height: 170, borderRadius: 85, backgroundColor: '#EFF9F6', bottom: -45, left: -60 },
  brand: { color: colors.primaryDark, fontSize: 38, fontWeight: '800', letterSpacing: -1.2 },
  subtitle: { color: colors.navy, fontSize: 21, fontWeight: '700', marginTop: 3 },
  logoShadow: { marginTop: 24, padding: 7, borderRadius: 30, backgroundColor: '#D8F1EA' },
  logo: { width: 82, height: 82, borderRadius: 26, backgroundColor: colors.primary, position: 'relative' },
  cross: { position: 'absolute', backgroundColor: colors.surface, borderRadius: 5, left: '50%', top: '50%' },
  crossVertical: { width: 16, height: 48, marginLeft: -8, marginTop: -24 },
  crossHorizontal: { width: 48, height: 16, marginLeft: -24, marginTop: -8 },
  heroNote: { color: colors.muted, fontSize: 13, marginTop: 19 },
  formCard: { marginHorizontal: 20, borderWidth: 1, borderColor: colors.border, borderRadius: 22, padding: 20, backgroundColor: colors.surface, shadowColor: '#0D3F34', shadowOffset: { width: 0, height: 8 }, shadowOpacity: 0.08, shadowRadius: 18, elevation: 4 },
  welcome: { color: colors.text, fontSize: 22, fontWeight: '800' },
  formHint: { color: colors.muted, fontSize: 13, lineHeight: 19, marginTop: 4, marginBottom: 18 },
  label: { color: colors.text, fontSize: 13, fontWeight: '700', marginBottom: 7, marginTop: 3 },
  inputRow: { height: 56, borderWidth: 1, borderColor: colors.border, borderRadius: 14, paddingHorizontal: 15, flexDirection: 'row', alignItems: 'center', gap: 10, marginBottom: 15, backgroundColor: '#FBFDFC' },
  inputError: { borderColor: colors.danger },
  input: { flex: 1, height: '100%', color: colors.text, fontSize: 14 },
  errorBox: { flexDirection: 'row', alignItems: 'flex-start', gap: 7, backgroundColor: '#FFF0F2', borderRadius: 11, padding: 11, marginBottom: 14 },
  errorText: { flex: 1, color: colors.danger, fontSize: 12, lineHeight: 17 },
  button: { height: 55, borderRadius: 14, alignItems: 'center', justifyContent: 'center', backgroundColor: colors.primary, shadowColor: colors.primaryDark, shadowOffset: { width: 0, height: 6 }, shadowOpacity: 0.22, shadowRadius: 10, elevation: 4 },
  buttonPressed: { opacity: 0.88, transform: [{ scale: 0.995 }] },
  buttonText: { color: colors.surface, fontSize: 16, fontWeight: '800' },
  version: { textAlign: 'center', color: '#9AA4B1', fontSize: 11, marginTop: 24 },
});
