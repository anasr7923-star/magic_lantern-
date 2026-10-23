import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:google_fonts/google_fonts.dart';

// 🟢 استيراداتك حسب الهيكلة اللي باينة في الصورة
import 'home_screen.dart';
import 'login_screen.dart';
import 'database_schema.dart';
import 'backend_api_engine.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  runApp(const MagicLanternApp());
}

class MagicLanternApp extends StatelessWidget {
  const MagicLanternApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MultiProvider(
      providers: [
        Provider<BackendApiEngine>(create: (_) => BackendApiEngine()),
        Provider<DatabaseSchema>(create: (_) => DatabaseSchema()),
      ],
      child: MaterialApp(
        title: 'Magic Lantern',
        debugShowCheckedModeBanner: false,
        theme: ThemeData(
          useMaterial3: true,
          textTheme: GoogleFonts.cairoTextTheme(),
          colorScheme: ColorScheme.fromSeed(seedColor: const Color(0xFF0F172A)),
        ),
        initialRoute: '/login',
        routes: {
          '/login': (context) => const LoginScreen(),
          '/home': (context) => const HomeScreen(),
        },
      ),
    );
  }
}