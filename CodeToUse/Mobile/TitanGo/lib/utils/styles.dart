import 'package:demandium_serviceman/utils/core_export.dart';

const robotoLight = TextStyle(
  fontFamily: 'Roboto',
  fontWeight: FontWeight.w300,
);

const robotoRegular = TextStyle(
  fontFamily: 'Roboto',
  fontWeight: FontWeight.w400,
);

TextStyle robotoRegularLow = TextStyle(
  fontFamily: 'Roboto',
  fontWeight: FontWeight.w400,
  fontSize: Dimensions.fontSizeSmall,
);


const robotoMedium = TextStyle(
  fontFamily: 'Roboto',
  fontWeight: FontWeight.w500,
);

TextStyle robotoMediumLow = TextStyle(
  fontFamily: 'Roboto',
  fontWeight: FontWeight.w500,
  fontSize: Dimensions.fontSizeSmall,
);


TextStyle robotoMediumHigh = TextStyle(
  fontFamily: 'Roboto',
  fontWeight: FontWeight.w500,
  fontSize: Dimensions.fontSizeLarge,
);



const robotoBold = TextStyle(
  fontFamily: 'Roboto',
  fontWeight: FontWeight.w700,
);


List<BoxShadow>? shadow =  [BoxShadow(offset: const Offset(0, 1), blurRadius: 2, color: Colors.black.withValues(alpha:0.15),)];

List<BoxShadow>? lightShadow = [const BoxShadow(offset: Offset(0, 1), blurRadius: 3, spreadRadius: 1, color: Color(0x20D6D8E6),)];
