import 'package:get/get.dart';
import 'package:demandium_serviceman/utils/core_export.dart';


class BookingDetailsBinding extends Bindings{
  @override
  void dependencies() {
    Get.lazyPut(() => BookingDetailsController(bookingDetailsRepo: BookingDetailsRepo(apiClient: Get.find())));
    Get.lazyPut(() => BookingEditController(bookingDetailsRepo: BookingDetailsRepo(apiClient: Get.find())));
  }
}