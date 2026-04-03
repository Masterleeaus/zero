
import 'package:demandium_serviceman/utils/core_export.dart';
import 'package:get/get.dart';

class ApiChecker {
  static void checkApi(Response response) {
    bool isAppNotActive = response.statusCode == 503 && '${response.body['code']}'.contains('activation-503');

    if(response.statusCode == 401 || isAppNotActive) {
      _executeUnAuthorized(response, isAppNotActive ? response.body['message'] : null);

    }if(response.statusCode == 500){
      showCustomSnackBar(response.statusText);
    }else {
      showCustomSnackBar(response.statusText);
    }
  }

  static void _executeUnAuthorized(Response response, String? errorMessage) {
    Get.find<AuthController>().clearSharedData();
    if(Get.currentRoute!=RouteHelper.getSignInRoute('splash')){
      Get.offAllNamed(RouteHelper.getSignInRoute('splash'));

      if(errorMessage != null) {
        showCustomSnackBar(errorMessage);
      }
    }
  }
}