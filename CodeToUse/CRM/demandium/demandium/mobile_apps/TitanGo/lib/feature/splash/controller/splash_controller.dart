
import 'package:get/get.dart';
import 'package:demandium_serviceman/utils/core_export.dart';

class SplashController extends GetxController implements GetxService {
  final SplashRepo splashRepo;
  SplashController({required this.splashRepo});

  ConfigModel? _configModel;
  ConfigModel? get configModel => _configModel;

  bool _hasConnection = true;
  bool get hasConnection => _hasConnection;


  Future<bool> getConfigData() async {
    _hasConnection = true;
    Response response = await splashRepo.getConfigData();
    bool isSuccess = false;
    if(response.statusCode == 200) {
      _configModel = ConfigModel.fromJson(response.body);

      if(_configModel?.content?.maintenanceMode?.maintenanceStatus == 1 && _configModel?.content?.maintenanceMode?.selectedMaintenanceSystem?.servicemanApp == 1 && !AppConstants.avoidMaintenanceMode){
        if(!Get.currentRoute.contains(RouteHelper.maintenanceRoute)){
          Get.offAllNamed(RouteHelper.getMaintenanceRoute());
          Get.find<AuthController>().unsubscribeToken();
        }
      } else if((Get.currentRoute.contains(RouteHelper.maintenanceRoute) &&  (_configModel?.content?.maintenanceMode?.maintenanceStatus == 0 || _configModel?.content?.maintenanceMode?.selectedMaintenanceSystem?.servicemanApp == 0))){
        Get.offAllNamed(RouteHelper.getInitialRoute());
      }
      else if(_configModel?.content?.maintenanceMode?.maintenanceStatus == 0){
        if(_configModel?.content?.maintenanceMode?.selectedMaintenanceSystem?.servicemanApp == 1){
          if(_configModel?.content?.maintenanceMode?.maintenanceTypeAndDuration?.maintenanceDuration == 'customize'){

            DateTime now = DateTime.now();
            DateTime specifiedDateTime = DateTime.parse(_configModel!.content!.maintenanceMode!.maintenanceTypeAndDuration!.startDate!);

            Duration difference = specifiedDateTime.difference(now);

            if(difference.inMinutes > 0 && (difference.inMinutes < 60 || difference.inMinutes == 60)){
              _startTimer(specifiedDateTime);
            }
          }
        }
      }

      isSuccess = true;
    }else {
      ApiChecker.checkApi(response);
      if(response.statusText == ApiClient.noInternetMessage){
        _hasConnection = false;
        _hasConnection = false;
      }
    }
    update();
    return isSuccess;
  }

  void _startTimer (DateTime startTime){
    Timer.periodic(const Duration(seconds: 30), (Timer timer){
      DateTime now = DateTime.now();
      if (now.isAfter(startTime) || now.isAtSameMomentAs(startTime)) {
        timer.cancel();
        Get.offAllNamed( RouteHelper.getMaintenanceRoute());
        Get.find<AuthController>().unsubscribeToken();
      }
    });
  }

  Future<bool> initSharedData() {
    return splashRepo.initSharedData();
  }

  bool? showIntro() {
    return splashRepo.showIntro();
  }

  void disableIntro() {
    splashRepo.disableIntro();
  }

  Future<void> updateLanguage(bool isInitial) async {
    Response response = await splashRepo.updateLanguage();

    if(!isInitial){
      if(response.statusCode == 200 && response.body['response_code'] == "default_200"){

      }else{
        showCustomSnackBar("${response.body['message']}");
      }
    }

  }

}
