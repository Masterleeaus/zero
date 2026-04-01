import 'package:demandium_serviceman/utils/core_export.dart';
import 'package:get/get.dart';

class ForgetPassScreen extends StatefulWidget {
  const ForgetPassScreen({super.key,});

  @override
  State<ForgetPassScreen> createState() => _ForgetPassScreenState();
}



class _ForgetPassScreenState extends State<ForgetPassScreen> {
  final TextEditingController _identityController = TextEditingController();
  final FocusNode _identityFocus = FocusNode();
  String countryDialCode = "";
  bool _isNumberLogin = false;
  String _forgetPasswordMethod = "both";

  @override
  void initState() {
    super.initState();

    countryDialCode = CountryCode.fromCountryCode(Get.find<SplashController>().configModel?.content?.countryCode?? "BD").dialCode?? "+880";
    var config = Get.find<SplashController>().configModel?.content;
    if(config?.forgetPasswordVerificationMethod?.phone == 1 && config?.forgetPasswordVerificationMethod?.email == 1){
      _forgetPasswordMethod = "both";
    }else if(config?.forgetPasswordVerificationMethod?.phone == 1){
      _forgetPasswordMethod = "phone";
    }else{
      _forgetPasswordMethod = "email";
    }
    toggleIsNumberLogin(value: false,isUpdate: false);

  }

  @override
  Widget build(BuildContext context) {

    return Scaffold(
      backgroundColor: Theme.of(context).colorScheme.surface,
      appBar: CustomAppBar(title: "forgot_password".tr),

      body: SafeArea(child: Center(child: Scrollbar(child: SingleChildScrollView(
        physics: const BouncingScrollPhysics(),

        padding: const EdgeInsets.all(Dimensions.paddingSizeSmall),
        child: Center(child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: Dimensions.paddingSizeSmall),
          child: Column(children: [

            Image.asset(Images.forgetPassword,height: 100,width: 100,),

            Padding(padding: const EdgeInsets.only(top: Dimensions.paddingSizeLarge),
              child: Center(child: Text('${"verify_your".tr} ${_forgetPasswordMethod == "email"? "email_address".tr.toLowerCase()
                  : _forgetPasswordMethod == "phone" ? "phone_number".tr.toLowerCase() : "email_or_phone".tr}',
                style: robotoMedium.copyWith(fontSize: Dimensions.fontSizeLarge,color: Theme.of(context).textTheme.bodyMedium!.color!.withValues(alpha:0.9)),textAlign: TextAlign.center,
              )),
            ),

            Padding(padding: const EdgeInsets.symmetric(vertical: Dimensions.paddingSizeLarge*1.5),
              child: Center(child: Text('${"please_enter_your".tr} ${_forgetPasswordMethod == "email"?"email_address".tr.toLowerCase()
                  :  _forgetPasswordMethod == "phone" ? "phone_number".tr.toLowerCase() :"email_or_phone".tr} ${"to_receive_a_verification_code".tr}',
                style: robotoRegular.copyWith(fontSize: Dimensions.fontSizeDefault,color: Theme.of(context).textTheme.bodyMedium!.color!.withValues(alpha:0.7)),textAlign: TextAlign.center,
              )),
            ),


            CustomTextField(
              onCountryChanged: (countryCode) => countryDialCode = countryCode.dialCode!,
              countryDialCode: (_forgetPasswordMethod != "email" && _isNumberLogin)  ? countryDialCode : null,
              title: _forgetPasswordMethod == "email" ? "email".tr : _forgetPasswordMethod == "phone" ? "phone".tr: 'email_or_phone'.tr,
              hintText: _forgetPasswordMethod == "email" ? "enter_email_address".tr : _forgetPasswordMethod == "phone" ? 'ex : 1234567890'.tr : 'enter_email_address_or_phone_number'.tr,
              controller: _identityController,
              focusNode: _identityFocus,
              inputType: TextInputType.emailAddress,
              onChanged: (String text){
                final numberRegExp = RegExp(r'^-?[0-9]+$');

                if(text.isEmpty && _isNumberLogin){
                  toggleIsNumberLogin();
                }
                if(text.startsWith(numberRegExp) && !_isNumberLogin ){
                  toggleIsNumberLogin();
                }
                final emailRegExp = RegExp(r'@');
                if(text.contains(emailRegExp) && _isNumberLogin){
                  toggleIsNumberLogin();
                }

              },
            ),

            // (identityType=="email")?
            // CustomTextField(
            //   hintText: "enter_email_address".tr,
            //   controller: _identityController,
            //   inputType: TextInputType.emailAddress,
            //   title: "email".tr,
            // ): CustomTextField(
            //   hintText: 'phone_humber_hint'.tr,
            //   controller: _identityController,
            //   inputType: TextInputType.phone,
            //   countryDialCode: countryDialCode,
            //   onCountryChanged: (CountryCode countryCode) => countryDialCode = countryCode.dialCode!,
            // ),

            const SizedBox(height: Dimensions.paddingSizeLarge),

            GetBuilder<AuthController>(builder: (authController) {
              return CustomButton(btnTxt: "send_otp".tr,
                isLoading: authController.isLoading!,
                onPressed: (){
                _forgetPass(countryDialCode,authController);
              },);
            }),
            const SizedBox(height: 150),

          ]),
        )),
      )))),
    );
  }

  void _forgetPass(String countryDialCode,AuthController authController) async {
    String phone = ValidationHelper.getValidPhone(countryDialCode + _identityController.text.trim(), withCountryCode: true);
    String identity = phone !="" ? phone : _identityController.text.trim();

    var config = Get.find<SplashController>().configModel?.content;
    SendOtpType  type = config?.firebaseOtpVerification == 1 && phone != "" ? SendOtpType.firebase : SendOtpType.forgetPassword;


    if (_identityController.text.isEmpty && _forgetPasswordMethod == "email") {
      showCustomSnackBar('enter_email_address'.tr, type: ToasterMessageType.info);
    } else if(_identityController.text.isNotEmpty && _forgetPasswordMethod == "email" && !GetUtils.isEmail(_identityController.text)){
      showCustomSnackBar('enter_valid_email_address'.tr, type: ToasterMessageType.info);
    }
    else if(_identityController.text.isEmpty && _forgetPasswordMethod == "phone"){
      showCustomSnackBar('phone_humber_hint'.tr, type: ToasterMessageType.info);
    }
    else if(_identityController.text.isNotEmpty && _forgetPasswordMethod == "phone" && phone == ""){
      showCustomSnackBar('invalid_phone_number'.tr);
    }
    else if((_identityController.text.isEmpty && _forgetPasswordMethod == "both") || (_identityController.text.isNotEmpty && _forgetPasswordMethod == "both" && !_isNumberLogin && !GetUtils.isEmail(_identityController.text))){
      showCustomSnackBar('enter_email_address_or_phone_number'.tr, type: ToasterMessageType.info);
    }
    else if(_identityController.text.isNotEmpty && _forgetPasswordMethod == "both" && _isNumberLogin && phone == ""){
      showCustomSnackBar('invalid_phone_number'.tr);
    }
    else {
      authController.sendVerificationCode(identity: identity,  identityType: phone !="" ? "phone" : "email", type: type).then((status){
        if(status != null){
          if(status.isSuccess!){
            Get.toNamed(RouteHelper.getVerificationRoute(
              identity: identity, identityType: phone !="" ? "phone" : "email",
              firebaseSession: type == SendOtpType.firebase ? status.message : null,
            ));
          }else{
            showCustomSnackBar(status.message.toString().capitalizeFirst ?? "" );
          }
        }
      });
      }
    }

  void toggleIsNumberLogin ({bool? value, bool isUpdate = true}){
    if(_forgetPasswordMethod == "both"){
      if(isUpdate){
        setState(() {
          if(value == null){
            _isNumberLogin = !_isNumberLogin;
          }else{
            _isNumberLogin = value;
          }
        });
      }else{
        if(value == null){
          _isNumberLogin = !_isNumberLogin;
        }else{
          _isNumberLogin = value;
        }
      }
    } else if(_forgetPasswordMethod == "phone"){
      _isNumberLogin= true;
    }
  }
}


