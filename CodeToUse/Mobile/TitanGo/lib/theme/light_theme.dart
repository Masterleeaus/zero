
import '../utils/core_export.dart';

ThemeData light = ThemeData(
  fontFamily: 'Ubuntu',
  primaryColor: const Color(0xFF4153B3),
  primaryColorLight: const Color(0xFF4153B3),
  primaryColorDark: const Color(0xff34428F),
  secondaryHeaderColor: const Color(0xFF8797AB),
  cardColor: const Color(0xFFFFFFFF),

  disabledColor: const Color(0xFF9E9E9E),
  scaffoldBackgroundColor: const Color(0xFFF7F9FC),
  brightness: Brightness.light,
  hintColor: const Color(0xFF838383),
  focusColor: const Color(0xFFFFFFFF),
  hoverColor: const Color(0xFF033969),
  extensions: <ThemeExtension<CustomThemeColors>>[
    CustomThemeColors.light(),
  ],
  shadowColor: const Color(0xFFD1D5DB), colorScheme: const ColorScheme.light(
    primary: Color(0xFF0461A5),
    secondary: Color(0xFFFF9900),
    tertiary: Color(0xFFFF6767),
    onTertiary: Color(0xFFffda6d),
    surfaceTint: Color(0xff158a52)
).copyWith(surface: const Color(0xFFF7F9FC)).copyWith(error: const Color(0xFFFF6767)),
);