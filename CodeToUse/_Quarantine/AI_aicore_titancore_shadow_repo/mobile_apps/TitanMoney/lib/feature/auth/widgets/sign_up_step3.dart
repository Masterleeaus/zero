import 'package:demandium_provider/helper/extension_helper.dart';
import 'package:get/get.dart';
import 'package:demandium_provider/util/core_export.dart';

class SignUpStep3 extends StatefulWidget {
  final GlobalKey<FormState> formKey;
  const SignUpStep3({super.key, required this.formKey});
  @override
  State<SignUpStep3> createState() => _SignUpStep3State();
}

class _SignUpStep3State extends State<SignUpStep3> {
  final FocusNode _passwordFocus = FocusNode();
  final FocusNode _confirmPasswordFocus = FocusNode();


  @override
  Widget build(BuildContext context) {
    return Expanded(
      child: SingleChildScrollView(
        physics: const BouncingScrollPhysics(),
        child: GetBuilder<SignUpController>(builder: (signUpController) {

          return Container(
            decoration: BoxDecoration(
              borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
              boxShadow: context.customThemeColors.lightShadow,
              color: Theme.of(context).cardColor,
            ),
            padding: const EdgeInsets.symmetric(horizontal : Dimensions.paddingSizeDefault, vertical: Dimensions.paddingSizeLarge),
            margin: const EdgeInsets.fromLTRB(Dimensions.paddingSizeSmall,Dimensions.paddingSizeDefault,Dimensions.paddingSizeSmall,3),

            child: Form(
              key: widget.formKey,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [

                  Text("account_information".tr,
                    style: robotoBold.copyWith(color: Theme.of(context).textTheme.bodyMedium?.color?.withValues(alpha:0.8), fontSize: Dimensions.fontSizeLarge),
                  ),
                  const SizedBox(height: Dimensions.paddingSizeExtraLarge),


                  CustomTextField(
                    inputType: TextInputType.emailAddress,
                    controller: signUpController.companyEmailController,
                    hintText: "enter_email".tr,
                    title: "Email_address".tr,
                    isEnabled: false,
                  ),
                  const SizedBox(height: Dimensions.paddingSizeLarge),


                  CustomTextField(
                    onCountryChanged: (CountryCode countryCode){
                      signUpController.countryDialCode = countryCode.dialCode!;
                    },
                    countryDialCode: signUpController.countryDialCode,
                    hintText: 'ex : 123456789'.tr,
                    isEnabled: false,
                    controller: signUpController.companyPhoneController,

                  ),
                  const SizedBox(height: Dimensions.paddingSizeExtraMoreLarge),

                  CustomTextField(
                    isShowSuffixIcon: true,
                    inputType: TextInputType.visiblePassword,
                    focusNode: _passwordFocus,
                    nextFocus: _confirmPasswordFocus,
                    inputAction: TextInputAction.next,
                    controller: signUpController.accountPasswordController,
                    hintText: "********",
                    title: "password".tr,
                    isPassword: true,
                    onValidate: (value){
                      return (value ==null || value.isEmpty) ? "field_cannot_be_empty".tr : value.length<8 ? "password_should_be".tr : null;
                    },
                  ),
                  const SizedBox(height: Dimensions.paddingSizeExtraMoreLarge),

                  CustomTextField(
                    isShowSuffixIcon: true,
                    focusNode: _confirmPasswordFocus,
                    inputType: TextInputType.visiblePassword,
                    controller: signUpController.accountConfirmPasswordController,
                    inputAction: TextInputAction.done,
                    hintText: "********",
                    title:"confirm_password".tr,
                    isPassword: true,
                    onValidate: (value){
                      return (value ==null || value.isEmpty) ? "field_cannot_be_empty".tr :
                      value != signUpController.accountPasswordController.text ?
                      "confirm_password_does_not_matched".tr : null;
                    },
                  ),

                  const SizedBox(height: Dimensions.paddingSizeExtraLarge,)
                ],
              ),
            ),
          );
          },
        ),
      ),
    );
  }
}