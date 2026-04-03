

import 'package:demandium_serviceman/utils/core_export.dart';

class BookingHelper{
  static double getSubTotalCost(BookingDetailsContent booking) {
    double subTotal = 0;
    for (var element in booking.details!) {
      subTotal = subTotal + ((element.serviceCost ?? 1) * (element.quantity ?? 1));
    }
    return subTotal;
  }

  static double getBookingServiceUnitConst(ItemService? item) {
    return  (item?.serviceCost ?? 0) * (item?.quantity ?? 1);
  }

}