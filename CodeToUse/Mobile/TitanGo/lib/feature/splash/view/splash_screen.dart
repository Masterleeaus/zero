import 'package:demandium_serviceman/utils/core_export.dart';
import 'package:get/get.dart';


class SplashScreen extends StatefulWidget {
  final NotificationBody? body;
  const SplashScreen({super.key, required this.body});

  @override
  SplashScreenState createState() => SplashScreenState();
}

class SplashScreenState extends State<SplashScreen> {
  final GlobalKey<ScaffoldState> _globalKey = GlobalKey();
  StreamSubscription<List<ConnectivityResult>>? _onConnectivityChanged;
  double opacity = 0.5;

  @override
  void initState() {
    super.initState();

    bool firstTime = true;
    _onConnectivityChanged = Connectivity().onConnectivityChanged.listen((List<ConnectivityResult> result) {
      if(!firstTime) {
        bool isNotConnected = result.first != ConnectivityResult.wifi && result.first != ConnectivityResult.mobile;
        isNotConnected ? const SizedBox() : ScaffoldMessenger.of(Get.context!).hideCurrentSnackBar();
        ScaffoldMessenger.of(Get.context!).showSnackBar(SnackBar(
          backgroundColor: isNotConnected ? Colors.red : Colors.green,
          duration: Duration(seconds: isNotConnected ? 6000 : 3),
          content: Text(
            isNotConnected ? 'no_connection'.tr : 'connected'.tr,
            textAlign: TextAlign.center,
          ),
        ));
        if(!isNotConnected) {
          _route();
        }
      }
      firstTime = false;
    });

    Get.find<SplashController>().initSharedData();
    _route();
  }

  @override
  void dispose() {
    super.dispose();
    _onConnectivityChanged!.cancel();
  }

  void _route() {
    Future.delayed(const Duration(milliseconds: 500),(){
      setState(() {
        opacity = 1;
      });
    });

    Get.find<SplashController>().getConfigData().then((isSuccess) {
      if(isSuccess){

        PriceConverter.getCurrency(Get.context!);

        Timer(const Duration(seconds: 1), () async {
          if(_checkAvailableUpdate()) {
            Get.offNamed(RouteHelper.getUpdateRoute(true));
          }
          else if(_checkMaintenanceModeActive() && !AppConstants.avoidMaintenanceMode){
            Get.offAllNamed(RouteHelper.getMaintenanceRoute());
            Get.find<AuthController>().unsubscribeToken();
          }
          else if(widget.body != null){
            _notificationRoute();
          }
          else{

            if( await _checkLanguageScreen() == true) {
              Get.offNamed(RouteHelper.getLanguageRoute());
            } else if(Get.find<AuthController>().isLoggedIn()){
              Get.offNamed(RouteHelper.getInitialRoute());
            }else{
              Get.offAllNamed(RouteHelper.getSignInRoute("LogIn"));
            }

          }
        });
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Theme.of(context).colorScheme.surface,
      key: _globalKey,
      body: GetBuilder<SplashController>(builder: (splashController) {
        return AnimatedOpacity(
          opacity: opacity,
          duration: const Duration(milliseconds: 500),
          child: Center(
            child: splashController.hasConnection ? Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                Image.asset(Images.logo,width: Dimensions.logoWidth,),
                const SizedBox(height: Dimensions.paddingSizeLarge),
                Text(AppConstants.appUser, style: robotoBold.copyWith(
                  fontSize: 20,
                  color: Theme.of(context).primaryColor,
                )),
              ],
            ) : NoInternetScreen(child: SplashScreen(body: widget.body)),
          ),
        );
      }),
    );
  }


  bool _checkAvailableUpdate (){
    ConfigModel? configModel = Get.find<SplashController>().configModel;
    final localVersion = Version.parse(AppConstants.appVersion);
    final serverVersion = Version.parse(GetPlatform.isAndroid
        ? configModel?.content?.minimumVersion?.minVersionForAndroid ?? ""
        :  configModel?.content?.minimumVersion?.minVersionForIos ?? ""
    );
    return localVersion.compareTo(serverVersion) == -1;
  }

  bool _checkMaintenanceModeActive(){
    final ConfigModel? configModel = Get.find<SplashController>().configModel;
    return (configModel?.content?.maintenanceMode?.maintenanceStatus == 1 && configModel?.content?.maintenanceMode?.selectedMaintenanceSystem?.servicemanApp == 1);
  }

  void _notificationRoute(){

    String notificationType = widget.body?.notificationType ?? "";

    switch(notificationType) {
      case "chatting": {
        Get.offAllNamed(RouteHelper.getInboxScreenRoute(fromNotification: "fromNotification"));
      } break;

      case "booking": {
        if( widget.body!.bookingId!=null&& widget.body!.bookingId!=""){
          Get.offAllNamed(RouteHelper.getBookingDetailsRoute( bookingId : widget.body!.bookingId!, fromPage :'fromNotification', isSubBooking: widget.body?.bookingType == "repeat"));
        }else{
          Get.offAllNamed(RouteHelper.getInitialRoute());
        }
      } break;

      case "privacy_policy": {
        Get.offAllNamed(RouteHelper.getHtmlRoute(HtmlType.privacyPolicy.value));
      } break;

      case "terms_and_conditions": {
        Get.offAllNamed(RouteHelper.getHtmlRoute(HtmlType.termsAndCondition.value));
      } break;

      default: {
        Get.offAllNamed(RouteHelper.getNotificationRoute());
      } break;
    }
  }

  Future<bool?> _checkLanguageScreen() async {

    bool? status;

    if( Get.find<SplashController>().showIntro()!){
      List<Language>  adminLanguageList = Get.find<SplashController>().configModel?.content?.languageList ?? [];

      List<String> localLanguageCode = [];
      for (var element in AppConstants.languages) {
        localLanguageCode.add(element.languageCode!);
      }
      if( adminLanguageList.length == 1 && localLanguageCode.contains(adminLanguageList[0].languageCode)){

        int index = AppConstants.languages.indexWhere((element) => element.languageCode == adminLanguageList[0].languageCode);

        if(index != -1){
          Locale locale = Locale( AppConstants.languages[index].languageCode!,AppConstants.languages[index].countryCode);

          Future.delayed(const Duration(milliseconds: 100), (){
            Get.find<LocalizationController>().setLanguage(locale, isInitial: true);
            status = false;
          });
        }

      }else{
        status = true;
      }
    }else{
      status = false;
    }

    return status;
  }
}


