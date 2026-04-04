import 'package:get/get.dart';
import 'package:demandium_serviceman/utils/core_export.dart';

class ProfileScreen extends StatelessWidget {
  const ProfileScreen({super.key}) ;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: CustomAppBar(title: "my_profile".tr),
      body: GetBuilder<UserController>(
        builder: (userController) {
          return userController.isLoading ? const ProfileShimmer() :
          SingleChildScrollView(
            physics: const BouncingScrollPhysics(),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                profileHeaderSection(context,userController),
                ProfileCardItem(
                  title: "dark_mode".tr,
                  leadingIcon: Images.changeModeIcon,
                  isDarkItem: true,
                ),
                const SizedBox(height: Dimensions.paddingSizeSmall),

                InkWell(
                  onTap: () =>Get.toNamed(RouteHelper.profileInformation),
                  child: ProfileCardItem(
                    title: "edit_profile".tr,
                    leadingIcon: Images.profileIcon,
                  ),
                ),
                const SizedBox(height: Dimensions.paddingSizeSmall),

                InkWell(
                  onTap: () => Get.toNamed(RouteHelper.getNotificationRoute()),
                  child: ProfileCardItem(
                    title: "notification".tr,
                    leadingIcon: Images.profileNotificationIcon,
                  ),
                ),
                const SizedBox(height: Dimensions.paddingSizeSmall),

                InkWell(
                  onTap: ()=> Get.toNamed(RouteHelper.getHtmlRoute("terms-and-conditions")),
                  child: ProfileCardItem(
                    title: "terms_conditions".tr,
                    leadingIcon: Images.aboutUs,
                  ),
                ),
                const SizedBox(height: Dimensions.paddingSizeSmall),

                InkWell(
                  onTap:() =>Get.toNamed( RouteHelper.getHtmlRoute('privacy-policy')),
                  child: ProfileCardItem(
                    title: "privacy_policy".tr,
                    leadingIcon: Images.privacyPolicy,
                  ),
                ),
                const SizedBox(height: Dimensions.paddingSizeSmall),

                InkWell(
                  onTap:() =>Get.dialog(
                      ConfirmationDialog(
                          icon: Images.logout,
                        title: 'are_you_sure_to_logout'.tr,
                        onNoPressed: () {
                          Get.back();
                        },
                        onYesPressed: () {
                          Get.find<AuthController>().clearSharedData();
                          Get.offAllNamed(RouteHelper.getSignInRoute(RouteHelper.splash));
                        }, description: ''), useSafeArea: false),
                  child: ProfileCardItem(
                    title: "logout".tr,
                    leadingIcon: Images.logout,
                  ),
                ),
                const SizedBox(height: Dimensions.paddingSizeLarge),
              ],
            ),
          );
        },
      ),
    );
  }

  Widget profileHeaderSection(BuildContext context,UserController controller){




    return SizedBox(
      height: 260,
      width: MediaQuery.of(context).size.width,
      child: Column(
        mainAxisAlignment: MainAxisAlignment.spaceEvenly,
        children: [
          
          ClipRRect(
            borderRadius: BorderRadius.circular(50),
            child: CustomImage(
              image: controller.userInfo.profileImageFullPath ?? "", height: 100, width: 100,
              placeholder: Images.userPlaceHolder,
            ),
          ),
          
          Text(
            "${controller.userInfo.firstName??""} ${controller.userInfo.lastName??""}",
            style: robotoBold.copyWith(fontSize: 16, color: Theme.of(context).primaryColorLight.withValues(alpha:.8)),
          ),

          Container(
            padding: const EdgeInsets.symmetric(horizontal: 20),
            width: Get.width,
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisAlignment: MainAxisAlignment.spaceEvenly,
              children: [
                ColumnText(
                  amount: controller.totalDays,
                  title: "since_joined".tr,
                ),
                ColumnText(
                  amount: controller.contents?.completedBookingsCount ?? 0,
                  title: "booking_completed".tr,
                ),
                ColumnText(
                  amount: controller.contents?.bookingsCount ?? 0,
                  title: "total_assigned_booking".tr,
                ),
              ],
            ),
          )
        ],
      ),
    );
  }
}
