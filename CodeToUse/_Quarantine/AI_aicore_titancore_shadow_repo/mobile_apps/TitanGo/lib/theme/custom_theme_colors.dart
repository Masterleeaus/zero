import 'package:flutter/material.dart';

class CustomThemeColors extends ThemeExtension<CustomThemeColors> {
  final Map<String, Color> buttonBackgroundColorMap;
  final Map<String, Color> buttonTextColorMap;
  final Color error;
  final Color success;
  final Color info;
  final Color warning;
  final List<BoxShadow>? cardShadow;
  final Color? orderStatisticBorderColor;
  final Color canceledBusinessSummaryCardColor;
  final Color canceledBusinessSummaryCurveColor;
  final Color assignedBusinessSummaryCardColor;
  final Color assignedBusinessSummaryCurveColor;
  final Color ongoingBusinessSummaryCardColor;
  final Color ongoingBusinessSummaryCurveColor;

  const CustomThemeColors({
    required this.buttonBackgroundColorMap,
    required this.buttonTextColorMap,
    required this.error,
    required this.success,
    required this.info,
    required this.warning,
    required this.cardShadow,
    required this.orderStatisticBorderColor,
    required this.canceledBusinessSummaryCardColor,
    required this.canceledBusinessSummaryCurveColor,
    required this.assignedBusinessSummaryCardColor,
    required this.assignedBusinessSummaryCurveColor,
    required this.ongoingBusinessSummaryCardColor,
    required this.ongoingBusinessSummaryCurveColor,
  });

  // Predefined themes for light and dark modes
  factory CustomThemeColors.light() =>  CustomThemeColors(
    buttonBackgroundColorMap: {
      'pending': Color(0xffE6EFF6),
      'accepted': Color(0xffEAF4FF),
      'ongoing': Color(0xffEAF4FF),
      'completed': Color(0xffE8F8EE),
      'canceled': Color(0xffFFEBEB),
      'approved': Color(0xffFFEBEB),
      'denied': Color(0xffFFEBEB),
    },
    buttonTextColorMap: {
      'pending': Color(0xff0461A5),
      'accepted': Color(0xff2B95FF),
      'ongoing': Color(0xff2B95FF),
      'completed': Color(0xff16B559),
      'canceled': Color(0xffFF3737),
      'approved': Color(0xff16B559),
      'denied':  Color(0xffFF3737),
    },
    error: Color(0xffFF4040),
    success: Color(0xff04BB7B),
    info: Color(0xff3C76F1),
    warning: Color(0xffFFBB38),
    cardShadow: [
      BoxShadow(
        // color: Theme.of(context).textTheme.titleLarge!.color!.withValues(alpha: 0.05),
        color: Colors.black.withValues(alpha:0.05),
        blurRadius: 10,
        offset: Offset(0, 2),
      ),

      BoxShadow(
        color: Colors.black.withValues(alpha:0.04),
        // color: Theme.of(context).textTheme.titleLarge!.color!.withValues(alpha: 0.04),
        blurRadius: 2,
      ),

      //   BoxShadow(
      //   offset: const Offset(0, 1),
      //   blurRadius: 6,
      //   spreadRadius: 1,
      //   color: Colors.black.withValues(alpha:0.05),
      // ),
    ],
    orderStatisticBorderColor: Color(0xffEFF1F4),
    canceledBusinessSummaryCardColor: Color(0xffE93E3E),
    canceledBusinessSummaryCurveColor: Color(0xffE75D5D),
    assignedBusinessSummaryCardColor: Color(0xff5869FF),
    assignedBusinessSummaryCurveColor: Color(0xff6776FF),
    ongoingBusinessSummaryCardColor: Color(0xff223DCA),
    ongoingBusinessSummaryCurveColor: Color(0xff2542DA),
  );

  factory CustomThemeColors.dark() =>  CustomThemeColors(
    buttonBackgroundColorMap: {
      'pending': Color(0xffE6EFF6),
      'accepted': Color(0xffEAF4FF),
      'ongoing': Color(0xffEAF4FF),
      'completed': Color(0xffE8F8EE),
      'canceled': Color(0xffFFEBEB),
      'approved': Color(0xffFFEBEB),
      'denied': Color(0xffFFEBEB),
    },
    buttonTextColorMap: {
      'pending': Color(0xff0461A5),
      'accepted': Color(0xff2B95FF),
      'ongoing': Color(0xff2B95FF),
      'completed': Color(0xff16B559),
      'canceled': Color(0xffFF3737),
      'approved': Color(0xff16B559),
      'denied':  Color(0xffFF3737),
    },
    error: Color(0xffC33D3D),
    success: Color(0xff019463),
    info: Color(0xff245BD1),
    warning: Color(0xffE6A832),
    cardShadow: [BoxShadow()],
    orderStatisticBorderColor: Color(0xff252d39),
    canceledBusinessSummaryCardColor: Color(0xffE93E3E),
    canceledBusinessSummaryCurveColor: Color(0xffE75D5D),
    assignedBusinessSummaryCardColor: Color(0xff5869FF),
    assignedBusinessSummaryCurveColor: Color(0xff6776FF),
    ongoingBusinessSummaryCardColor: Color(0xff223DCA),
    ongoingBusinessSummaryCurveColor: Color(0xff2542DA),
  );

  @override
  CustomThemeColors copyWith({
    Map<String, Color>? buttonBackgroundColorMap,
    Map<String, Color>? buttonTextColorMap,
  }) {
    return CustomThemeColors(
      buttonBackgroundColorMap: buttonBackgroundColorMap ?? this.buttonBackgroundColorMap,
      buttonTextColorMap: buttonTextColorMap ?? this.buttonTextColorMap,
      error: error,
      success: success,
      info: info,
      warning: warning,
      cardShadow: cardShadow,
      orderStatisticBorderColor: orderStatisticBorderColor,
      canceledBusinessSummaryCardColor: canceledBusinessSummaryCardColor,
      canceledBusinessSummaryCurveColor: canceledBusinessSummaryCurveColor,
      assignedBusinessSummaryCardColor: assignedBusinessSummaryCardColor,
      assignedBusinessSummaryCurveColor: assignedBusinessSummaryCurveColor,
      ongoingBusinessSummaryCardColor: ongoingBusinessSummaryCardColor,
      ongoingBusinessSummaryCurveColor: ongoingBusinessSummaryCurveColor,
    );
  }

  @override
  CustomThemeColors lerp(ThemeExtension<CustomThemeColors>? other, double t) {
    if (other is! CustomThemeColors) return this;

    return CustomThemeColors(
      buttonBackgroundColorMap: buttonBackgroundColorMap,
      buttonTextColorMap: buttonTextColorMap,
      error: error,
      success: success,
      info: info,
      warning: warning,
      cardShadow: cardShadow,
      orderStatisticBorderColor: orderStatisticBorderColor,
      canceledBusinessSummaryCardColor: canceledBusinessSummaryCardColor,
      canceledBusinessSummaryCurveColor: canceledBusinessSummaryCurveColor,
      assignedBusinessSummaryCardColor: assignedBusinessSummaryCardColor,
      assignedBusinessSummaryCurveColor: assignedBusinessSummaryCurveColor,
      ongoingBusinessSummaryCardColor: ongoingBusinessSummaryCardColor,
      ongoingBusinessSummaryCurveColor: ongoingBusinessSummaryCurveColor,
    );
  }
}