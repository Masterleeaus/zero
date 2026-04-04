import 'package:get/get.dart';
import 'package:demandium_serviceman/utils/core_export.dart';

class BookingRequestRepo{
  final ApiClient apiClient;
  BookingRequestRepo({required this.apiClient});

  Future<Response> getBookingList(String requestType, int offset) async {
    return await apiClient.getData("${AppConstants.bookingRequestUrl}?limit=7&offset=$offset&booking_status=$requestType&service_type=all");
  }
  Future<Response> getBookingHistoryList(String requestType,int offset) async {
    return await apiClient.getData("${AppConstants.bookingRequestUrl}?limit=10&offset=$offset&booking_status=$requestType&service_type=all");
  }
}
