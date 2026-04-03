import 'package:get/get.dart';
import 'package:demandium_provider/util/core_export.dart';


class SignUpScreen extends StatefulWidget {
  const SignUpScreen({super.key});

  @override
  State<SignUpScreen> createState() => _SignUpScreenState();
}

class _SignUpScreenState extends State<SignUpScreen> {

  final GlobalKey<FormState> step1FormKey = GlobalKey<FormState>();
  final GlobalKey<FormState> step2FormKey = GlobalKey<FormState>();
  final GlobalKey<FormState> step3FormKey = GlobalKey<FormState>();


  @override
  void initState() {
    super.initState();

    _loadData();

    Get.find<LocationController>().setPickedLocation(shouldUpdate: false);
    Get.find<SignUpController>().checkOthersFieldValidity(shouldUpdate: false, isInitial: true);
  }

  void _loadData(){
    Get.find<SplashController>().getConfigData();
    Get.find<BusinessSubscriptionController>().getSubscriptionPackageList();
  }

  @override
  Widget build(BuildContext context)  {

    return Scaffold(
      backgroundColor: Theme.of(context).colorScheme.surface,
      appBar: const SignUpAppbar(),
      body: SafeArea(
        bottom: GetPlatform.isIOS ? true : false,
        child: GetBuilder<SplashController>(builder: (splashController){
          var config = splashController.configModel.content;
          return GetBuilder<SignUpController>(
            builder: (signUpController){
              return Column(
                children: [

                  signUpController.currentStep == SignUpPageStep.step1 ?
                  SignUpStep1(formKey: step1FormKey,) : signUpController.currentStep == SignUpPageStep.step2 ?
                  SignUpStep2(formKey: step2FormKey,) : signUpController.currentStep == SignUpPageStep.step3 ?
                  SignUpStep3(formKey: step3FormKey,) : signUpController.currentStep == SignUpPageStep.step4 ?
                  const SignUpStep4() : const SignUpStep5(),

                  const SizedBox(height : Dimensions.paddingSizeSmall),
                  Row(mainAxisAlignment: MainAxisAlignment.end, children: [

                    signUpController.currentStep != SignUpPageStep.step1 ? CustomButton(
                      height: 40, width: 100, btnTxt: 'back'.tr,
                      color: Theme.of(context).hintColor.withValues(alpha:0.4),
                      textColor: Theme.of(context).textTheme.bodySmall?.color,
                      fontSize: Dimensions.fontSizeSmall,
                      onPressed: () => signUpController.updateRegistrationStep(
                        signUpController.currentStep == SignUpPageStep.step5 ? SignUpPageStep.step4 :
                        signUpController.currentStep == SignUpPageStep.step4 ? SignUpPageStep.step3 :
                        signUpController.currentStep == SignUpPageStep.step3 ? SignUpPageStep.step2 :
                        signUpController.currentStep == SignUpPageStep.step2 ? SignUpPageStep.step1 :
                        SignUpPageStep.step1,
                      ),
                    ) : const SizedBox(),

                    const SizedBox(width: Dimensions.paddingSizeLarge),

                    CustomButton(
                      height: 40, width: 100, fontSize: Dimensions.fontSizeSmall, isLoading: signUpController.isLoading!,
                      btnTxt: (signUpController.currentStep == SignUpPageStep.step5 || (signUpController.currentStep == SignUpPageStep.step4
                          && signUpController.selectedBusinessPlan == BusinessPlanType.commissionBase)) ? "confirm".tr : 'next'.tr,
                      onPressed: (signUpController.currentStep == SignUpPageStep.step5 && config?.digitalPayment == 0 && config?.subscriptionFreeTrail == 0) ? null : () {
                        if(signUpController.currentStep==SignUpPageStep.step1){
                          validateStep1(signUpController);
                        }else if(signUpController.currentStep==SignUpPageStep.step2){
                          validateStep2(signUpController);
                        }else if(signUpController.currentStep==SignUpPageStep.step3){
                          validateStep3(signUpController);
                        }else if(signUpController.currentStep==SignUpPageStep.step4){
                          validateStep4(signUpController);
                        } else{
                          validateStep5(signUpController);
                        }
                      },
                    ),
                    const SizedBox(width: Dimensions.paddingSizeDefault),

                  ]),
                  SizedBox(height :  GetPlatform.isIOS ? 0 : Dimensions.paddingSizeSmall),
                ],
              );
            },
          );
        }),
      ),
    );
  }

  void validateStep1(SignUpController signUpController) async{

    signUpController.checkOthersFieldValidity(step1: true);
    if(step1FormKey.currentState!.validate() && signUpController.isLogoValid && signUpController.isCoverImageValidity){
      signUpController.updateRegistrationStep(SignUpPageStep.step2);
    }

  }

  void validateStep2(SignUpController signUpController) {

    signUpController.checkOthersFieldValidity(step2: true);
    if(step2FormKey.currentState!.validate() && signUpController.isZoneValid && signUpController.isIdentityTypeValid && signUpController.isIdentityImageValid){
      signUpController.updateRegistrationStep(SignUpPageStep.step3);
    }
  }

  void validateStep3(SignUpController signUpController) async {

    if(step3FormKey.currentState!.validate()){
      signUpController.updateRegistrationStep(SignUpPageStep.step4);
    }

  }

  void validateStep4(SignUpController signUpController) async {

    if(signUpController.selectedBusinessPlan == null){
      showCustomSnackBar("choose_business_plan".tr, type : ToasterMessageType.info);
    }
    else if(signUpController.selectedBusinessPlan == BusinessPlanType.commissionBase){
      signUpController.registration(_getSignUpBody(signUpController));
    } else if(signUpController.selectedBusinessPlan == BusinessPlanType.subscriptionBase && signUpController.selectedSubscriptionPackage == null){
      showCustomSnackBar("no_subscription_plan_available_at_this_moment".tr, type: ToasterMessageType.info);
    } else{
      signUpController.updateRegistrationStep(SignUpPageStep.step5);
    }

  }

  void validateStep5(SignUpController signUpController) async {

    if(signUpController.selectedSubscriptionPaymentType == null){
      showCustomSnackBar("free_trail_hint_text".tr,  type: ToasterMessageType.info);
    }
    else if(signUpController.selectedSubscriptionPaymentType == SubscriptionPaymentType.digital && signUpController.selectedDigitalPaymentMethodIndex == -1){
     showCustomSnackBar("select_payment_method".tr, type: ToasterMessageType.info);
    }else {
      signUpController.registration(_getSignUpBody(signUpController));
    }

  }


  SignUpBody _getSignUpBody(SignUpController signUpController){
    String companyNumberWithCountryCode = signUpController
        .countryDialCode + ValidationHelper.getValidPhone("${signUpController
        .countryDialCode}${signUpController.companyPhoneController.value.text}");

    String contactNumberWithCountryCode = signUpController
        .countryDialCode + ValidationHelper.getValidPhone("${signUpController
        .countryDialCode}${signUpController.contactPersonPhoneController.value.text}");

    DigitalPaymentMethod? paymentMethod;

    if(signUpController.selectedDigitalPaymentMethodIndex != -1 && signUpController.selectedBusinessPlan == BusinessPlanType.subscriptionBase){
      List<DigitalPaymentMethod> paymentMethodList = Get.find<SplashController>().configModel.content?.paymentMethodList ?? [];
      paymentMethod = paymentMethodList[signUpController.selectedDigitalPaymentMethodIndex];
    }


    SignUpBody signUpBody = SignUpBody(
      contactPersonEmail: signUpController.contactPersonEmailController.text,
      contactPersonName: signUpController.contactPersonNameController.text,
      contactPersonPhone: contactNumberWithCountryCode,
      password: signUpController.accountPasswordController.text,
      confirmedPassword: signUpController.accountConfirmPasswordController.text,
      companyName: signUpController.companyNameController.text,
      companyAddress: signUpController.companyAddressController.text,
      companyEmail: signUpController.companyEmailController.text,
      companyPhone: companyNumberWithCountryCode,
      accountEmail: signUpController.companyEmailController.text,
      accountPhone: companyNumberWithCountryCode,
      identityType: signUpController.selectedIdentityType,
      identityNumber: signUpController.identityNumberController.text,
      zoneId: signUpController.selectedZoneId,
      lat: "${Get.find<LocationController>().pickPosition.latitude}",
      lon: "${Get.find<LocationController>().pickPosition.longitude}",
      chooseBusinessPlan: signUpController.selectedBusinessPlan == BusinessPlanType.commissionBase ? "commission_base" : "subscription_base",
      subscriptionPackageId: signUpController.selectedSubscriptionPackage?.id,
      paymentMethod: paymentMethod?.gateway,
      freeTrialOrPayment: signUpController.selectedSubscriptionPaymentType == SubscriptionPaymentType.freeTrail ? "free_trial" : "payment",
      paymentPlatform: "app",

    );
    return signUpBody;
  }
}
