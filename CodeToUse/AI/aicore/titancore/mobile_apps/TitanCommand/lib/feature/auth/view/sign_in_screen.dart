import 'package:get/get.dart';
import 'package:demandium_provider/util/core_export.dart';

class SignInScreen extends StatefulWidget {
  final bool exitFromApp;
  const SignInScreen({super.key, required this.exitFromApp});

  @override
  SignInScreenState createState() => SignInScreenState();
}

class SignInScreenState extends State<SignInScreen> {
  final TextEditingController _emailController = TextEditingController();
  final FocusNode _emailFocus = FocusNode();
  final FocusNode _passwordFocus = FocusNode();
  final TextEditingController _passwordController = TextEditingController();
  bool _canExit = GetPlatform.isWeb ? true : false;
  final GlobalKey<FormState> signInFormKey = GlobalKey<FormState>();
  
  String? emailErrorText;
  String? passwordErrorText;

  @override
  void initState() {
    _initializeController();
    _requestNotificationPermission();

    super.initState();
  }

  Future<void> _requestNotificationPermission() async {
    if(defaultTargetPlatform == TargetPlatform.android) {
      await Future.delayed(Duration(seconds: 1));
      await FirebaseMessaging.instance.requestPermission();
    }
  }

  @override
  Widget build(BuildContext context) {
    return CustomPopScopeWidget(
      onPopInvoked: () {
        if (_canExit) {
          SystemNavigator.pop();
        } else {
          showCustomSnackBar('back_press_again_to_exit'.tr,  type: ToasterMessageType.info);
          _canExit = true;
          Timer(const Duration(seconds: 2), () {
            _canExit = false;
          });
        }
      },
      child: Scaffold(
        backgroundColor: Theme.of(context).colorScheme.surface,
        body: SafeArea(child: Center(
          child: SingleChildScrollView(
            physics: const BouncingScrollPhysics(),
            padding: const EdgeInsets.all(Dimensions.paddingSizeLarge),
            child: Center(
              child: GetBuilder<AuthController>(builder: (authController) {

                return Form(
                  key: signInFormKey,
                  autovalidateMode: AutovalidateMode.onUserInteraction,
                  child: Column(children: [
                    Image.asset(Images.logo,width: Dimensions.logoWidth,),
                    const SizedBox(height: Dimensions.paddingSizeExtraLarge),

                    const SizedBox(height: Dimensions.paddingSizeLarge),
                    CustomTextField(
                      onCountryChanged: (countryCode) => authController.countryDialCode = countryCode.dialCode!,
                      countryDialCode: authController.isNumberLogin  ? authController.countryDialCode : null,
                      title: 'email_or_phone'.tr,
                      hintText: 'enter_email_or_password'.tr,
                      controller: _emailController,
                      focusNode: _emailFocus,
                      nextFocus: _passwordFocus,
                      inputType: TextInputType.emailAddress,
                      onChanged: (String text){
                        final numberRegExp = RegExp(r'^[+]?[0-9]+$');

                        if(text.isEmpty && authController.isNumberLogin){
                          authController.toggleIsNumberLogin();
                        }
                        if(text.startsWith(numberRegExp) && !authController.isNumberLogin ){
                          authController.toggleIsNumberLogin();
                          _emailController.text = text.replaceAll("+", "");
                        }
                        final emailRegExp = RegExp(r'@');
                        if(text.contains(emailRegExp) && authController.isNumberLogin){
                          authController.toggleIsNumberLogin();
                        }

                      },
                      onValidate: (String? value){

                        if(authController.isNumberLogin && ValidationHelper.getValidPhone(authController.countryDialCode+value!) == ""){
                        return "enter_valid_phone_number".tr;
                        }
                        return (GetUtils.isPhoneNumber(value!.tr) || GetUtils.isEmail(value.tr)) ? null : 'enter_email_address_or_phone_number'.tr;
                      },
                    ),

                    const SizedBox(height: Dimensions.paddingSizeExtraMoreLarge),
                    CustomTextField(
                      title: 'password'.tr,
                      hintText: '********'.tr,
                      controller: _passwordController,
                      focusNode: _passwordFocus,
                      inputType: TextInputType.visiblePassword,
                      isPassword: true,
                      inputAction: TextInputAction.done,
                      onValidate: (String? value){
                        return FormValidationHelper().isValidPassword(value);
                      },
                    ),


                    const SizedBox(height: Dimensions.paddingSizeSmall),
                    Row(mainAxisAlignment: MainAxisAlignment.end,
                      children: [
                        Expanded(
                          child: ListTile(
                            onTap: () => authController.toggleRememberMe(),
                            title: Row(
                              children: [
                                SizedBox(
                                  width: 20.0,
                                  child: Checkbox(
                                    activeColor: Theme.of(context).primaryColor,
                                    value: authController.isActiveRememberMe,
                                    checkColor: Colors.white,
                                    onChanged: (bool? isChecked) => authController.toggleRememberMe(),
                                  ),
                                ),
                                const SizedBox(width: Dimensions.paddingSizeSmall),
                                Text(
                                  'remember_me'.tr,
                                  style: robotoRegular.copyWith(fontSize: Dimensions.fontSizeDefault),
                                ),
                              ],
                            ),
                            contentPadding: EdgeInsets.zero,
                            dense: true,
                            horizontalTitleGap: 0,
                          ),
                        ),
                        TextButton(
                          style: TextButton.styleFrom(minimumSize: const Size(1, 40),backgroundColor: Theme.of(context).colorScheme.surface),
                          onPressed: () => Get.toNamed(RouteHelper.getSendOtpScreen()),
                          child: Text('forgot_password?'.tr, style: robotoRegular.copyWith(
                            fontSize: Dimensions.fontSizeDefault, color: Theme.of(context).colorScheme.tertiary.withValues(alpha:0.8),)
                          ),
                        ),
                      ],
                    ),

                    const SizedBox(height: Dimensions.paddingSizeLarge),

                    CustomButton(btnTxt: 'sign_in'.tr, onPressed:  () => _login(authController) , isLoading: authController.isLoading!,),

                    Get.find<SplashController>().configModel.content?.providerSelfRegistration == 1 ?
                    Column(
                      children: [
                        const SizedBox(height: Dimensions.paddingSizeDefault),

                        Text("or".tr, style: robotoRegular.copyWith(fontSize: Dimensions.fontSizeSmall,
                              color: Theme.of(context).textTheme.bodyLarge!.color,)),
                        Row(mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Text('do_not_have_an_account'.tr, style: robotoRegular.copyWith(fontSize: Dimensions.fontSizeDefault,
                                    color: Theme.of(context).primaryColor)),
                            TextButton(
                              style: TextButton.styleFrom(minimumSize: const Size(1, 40),
                                  backgroundColor: Theme.of(context).colorScheme.surface),
                              onPressed: () => Get.toNamed(RouteHelper.signUp),

                              child:Text('register_here'.tr,
                                style: robotoRegular.copyWith(
                                    color: Theme.of(context).colorScheme.tertiary.withValues(alpha:.8),
                                    fontSize: Dimensions.fontSizeDefault
                                ),
                              ),
                            )
                          ],
                        ),
                      ],
                    ) :const SizedBox.shrink(),

                  ]),
                );
              }),
            ),
          ),
        )),
      ),
    );
  }


  void _initializeController(){
    var authController  = Get.find<AuthController>();
    String phoneWithoutCountryCode =  ValidationHelper.getValidPhone(Get.find<AuthController>().getUserNumber());
    String countryCode = ValidationHelper.getCountryCode(Get.find<AuthController>().getUserNumber());

    WidgetsBinding.instance.addPostFrameCallback((_){
      if(countryCode != "" && phoneWithoutCountryCode !=""){
        authController.toggleIsNumberLogin(value: true);
      }else{
        authController.toggleIsNumberLogin(value: false);
      }
      authController.initCountryCode(countryCode: countryCode !="" ? countryCode : null);

    });

    _emailController.text = phoneWithoutCountryCode != "" ? phoneWithoutCountryCode : authController.isNumberLogin ? "" : Get.find<AuthController>().getUserNumber();
    _passwordController.text = Get.find<AuthController>().getUserPassword();
  }

  void _login(AuthController authController) async {
    if(signInFormKey.currentState!.validate()){
      String phone = ValidationHelper.getValidPhone(authController.countryDialCode + _emailController.text.trim(), withCountryCode: true);
      await authController.login( phone !="" ? phone : _emailController.text.trim(), _passwordController.text.trim(), phone !="" ? "phone" : "email");
    }
  }


}

