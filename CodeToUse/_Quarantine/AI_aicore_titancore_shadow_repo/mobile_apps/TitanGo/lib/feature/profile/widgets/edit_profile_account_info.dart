import 'package:get/get.dart';
import 'package:demandium_serviceman/utils/core_export.dart';

class EditProfileAccountInfo extends StatelessWidget {
  const EditProfileAccountInfo({super.key}) ;

  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      physics: const BouncingScrollPhysics(),
      child: Padding(
        padding: const EdgeInsets.all(15.0),
        child: GetBuilder<UserController>(
          builder: (controller){
            return Form(
              key: controller.passUpdateKey,
              child: Column(
                mainAxisAlignment: MainAxisAlignment.start,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  customRichText("phone_number".tr,context),
                  NonEditableTextField(text: controller.userInfo.phone??"",),

                  customRichText("new_password".tr,context),
                  CustomTextFormField(
                    isShowSuffixIcon: true,
                    isPassword: true,
                    controller: controller.passController,
                    hintText: "enter_new_password".tr,
                    isShowBorder: true,
                  ),

                  customRichText("Confirm_New_Password".tr,context),
                  CustomTextFormField(
                    isShowSuffixIcon: true,
                    isPassword: true,
                    controller: controller.confirmPassController,
                    hintText: "enter_confirm_password".tr,
                    isShowBorder: true,
                  ),
                  const SizedBox(height: Dimensions.paddingSizeLarge,),

                  CustomButton(
                      btnTxt: 'save'.tr,
                      isLoading: controller.isLoading,
                    onPressed: () => _updatePassword(controller)
                  ),
                ],
              ),
            );
          },
        )
      ),
    );
  }

  Widget customRichText(String title,BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 8.0),
      child: RichText(
        text: TextSpan(
          children:[
            TextSpan(text: title,
              style: robotoRegular.copyWith(
                color: Theme.of(context).textTheme.bodyLarge!.color!.withValues(alpha:0.8),
              ),
            ),
            TextSpan(text: ' *', style: robotoRegular.copyWith(color: Theme.of(context).colorScheme.error)),
          ],
        ),
      ),
    );
  }

  void _updatePassword(UserController controller) {

    if(controller.passController!.text.isEmpty){
      showCustomSnackBar('enter_new_password'.tr, type: ToasterMessageType.info);
    }else if (controller.passController!.text.length < 8) {
      showCustomSnackBar('password_should_be'.tr);
    } else if (controller.confirmPassController!.text.isEmpty) {
      showCustomSnackBar('enter_confirm_password'.tr, type: ToasterMessageType.info);
    } else if(controller.passController!.text != controller.confirmPassController!.text) {
      showCustomSnackBar('confirm_password_does_not_matched'.tr);
    }else {
      controller.updatePassword();
    }

  }
}
