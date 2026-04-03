import 'package:get/get.dart';
import 'package:demandium_serviceman/utils/core_export.dart';

class UpdateScreen extends StatefulWidget {
  final bool? isUpdate;

  const UpdateScreen({super.key, required this.isUpdate});

  @override
  State<UpdateScreen> createState() => _UpdateScreenState();
}

class _UpdateScreenState extends State<UpdateScreen> {

  bool _canExit = GetPlatform.isWeb ? true : false;

  @override
  Widget build(BuildContext context) {
    return CustomPopScopeWidget(
      onPopInvoked: (){
        if(_canExit) {
          SystemNavigator.pop();
        }else {
          showCustomSnackBar('back_press_again_to_exit'.tr, type : ToasterMessageType.info);
          _canExit = true;
          Timer(const Duration(seconds: 2), () {
            _canExit = false;
          });
        }
      },
      child: Scaffold(
        body: Center(
          child: Padding(
            padding: const EdgeInsets.all(Dimensions.paddingSizeLarge),
            child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [

              Image.asset(widget.isUpdate! ? Images.update : Images.maintenance,
                width: MediaQuery.of(context).size.height * 0.4,
                height: MediaQuery.of(context).size.height * 0.4,
              ),
              SizedBox(height: MediaQuery.of(context).size.height * 0.01),
              const SizedBox(height: Dimensions.paddingSizeLarge,),
              Text(widget.isUpdate! ? 'update_is_available'.tr : 'we_are_under_maintenance'.tr,
                style: robotoBold.copyWith(fontSize: MediaQuery.of(context).size.height * 0.023, color: Theme.of(context).primaryColor),
                textAlign: TextAlign.center,
              ),
              SizedBox(height: MediaQuery.of(context).size.height * 0.01),

              Text(widget.isUpdate! ? 'your_app_needs_to_update'.tr : 'we_will_be_right_back'.tr,
                style: robotoRegular.copyWith(fontSize: MediaQuery.of(context).size.height * 0.0175, color: Theme.of(context).disabledColor),
                textAlign: TextAlign.center,
              ),
              SizedBox(height: widget.isUpdate! ? MediaQuery.of(context).size.height * 0.04 : 0),

              widget.isUpdate! ? CustomButton(
                btnTxt: 'update_now'.tr,
                onPressed: () async {
                  String appUrl = 'https://google.com';
                  if (GetPlatform.isAndroid) {
                    appUrl = Get.find<SplashController>().configModel?.content?.appUrlAndroid ?? "https://play.google.com/store/apps";
                  }
                  else if (GetPlatform.isIOS) {
                    appUrl = Get.find<SplashController>().configModel?.content?.appUrlIos ?? "https://www.apple.com/app-store/";
                  }
                  _launchUrl(Uri.parse(appUrl));

              },) : const SizedBox(),

            ]),
          ),
        ),
      ),
    );
  }

  Future<void> _launchUrl(Uri url) async {
    if (!await launchUrl(url)) {
      throw 'Could not launch $url';
    }
  }
}