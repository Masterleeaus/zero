import 'package:demandium_serviceman/feature/checklist/view/checklists_screen.dart';
import 'package:demandium_serviceman/feature/reports/view/report_issue_screen.dart';
import 'package:demandium_serviceman/feature/reports/view/supply_issue_screen.dart';
import 'package:demandium_serviceman/feature/titan_ai/view/titan_ai_screens.dart';
import 'package:get/get.dart';
import 'package:demandium_serviceman/utils/core_export.dart';

class MenuScreen extends StatelessWidget {
  const MenuScreen({super.key,});

  @override
  Widget build(BuildContext context) {

    ConfigModel? configModel  = Get.find<SplashController>().configModel;
    double ratio = ResponsiveHelper.isDesktop(context) ? 1.1 : ResponsiveHelper.isTab(context) ? 1.1 : 1.2;

    final List<MenuModel> menuList = [
      // Primary worker items
      MenuModel(icon: Images.profile, title: 'profile'.tr, route: RouteHelper.getProfileRoute()),
      MenuModel(icon: Images.chatImage, title: 'messages'.tr, route: RouteHelper.getInboxScreenRoute()),
      MenuModel(icon: Images.appbarNotification, title: 'notifications'.tr, route: RouteHelper.getNotificationRoute()),
      MenuModel(icon: Images.languageIcon, title: 'language'.tr, route: RouteHelper.getLanguageRoute()),

      // Operational tools
      MenuModel(icon: Images.history, title: 'checklists'.tr, route: '_checklists'),
      MenuModel(icon: Images.booking, title: 'supply_issues'.tr, route: '_supply_issues'),
      MenuModel(icon: Images.cancelIcon, title: 'report_issue'.tr, route: '_report_issue'),

      // AI / voice / training entry points
      MenuModel(icon: Images.dashboardProfile, title: 'ask_titan'.tr, route: '_ask_titan'),
      MenuModel(icon: Images.appbarMessage, title: 'voice_control'.tr, route: '_voice_control'),
      MenuModel(icon: Images.service, title: 'training'.tr, route: '_training'),

      // Support
      MenuModel(icon: Images.aboutUs, title: 'help_support'.tr, route: '_help_support'),
      MenuModel(icon: Images.history, title: 'booking_history'.tr, route: RouteHelper.getBookingHistoryRoute()),

      // Footer / legal
      ...(configModel
          ?.content
          ?.businessPages
          ?? []).where((page) =>
            page.pageKey == HtmlType.privacyPolicy.value ||
            page.pageKey == HtmlType.termsAndCondition.value ||
            page.pageKey == HtmlType.cancellationPolicy.value
          ).map((page) => MenuModel(
        icon: page.pageKey == HtmlType.termsAndCondition.value
            ? Images.termsIcon
            : page.pageKey == HtmlType.privacyPolicy.value
            ? Images.privacyPolicyIcon
            : Images.cancellationPolicy,
        title: page.pageKey == HtmlType.termsAndCondition.value
            ? 'terms_conditions'.tr
            : page.pageKey == HtmlType.privacyPolicy.value
            ? 'privacy_policy'.tr
            : 'cancellation_policy'.tr,
        route: RouteHelper.getHtmlRoute(page.pageKey!),
      )),

      MenuModel(icon: Images.logout, title: 'logout'.tr, route: RouteHelper.signIn),
    ];

    return Container(
      width: Dimensions.webMaxWidth,
      padding: const EdgeInsets.symmetric(horizontal: Dimensions.paddingSizeSmall),
      decoration: BoxDecoration(
        borderRadius: const BorderRadius.vertical(top: Radius.circular(Dimensions.radiusExtraLarge)),
        color: Theme.of(context).cardColor,
      ),
      child: Column(mainAxisSize: MainAxisSize.min, children: [
        InkWell(
          onTap: () => Get.back(),
          child: const Icon(Icons.keyboard_arrow_down_rounded, size: 30),
        ),
        const SizedBox(height: Dimensions.paddingSizeExtraSmall),

        GridView.builder(
          physics: const NeverScrollableScrollPhysics(),
          shrinkWrap: true,
          gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
            crossAxisCount: ResponsiveHelper.isDesktop(context) ? 8 : ResponsiveHelper.isTab(context) ? 6 : 4,
            childAspectRatio: (1/ratio),
            crossAxisSpacing: Dimensions.paddingSizeExtraSmall, mainAxisSpacing: Dimensions.paddingSizeExtraSmall,
          ),
          itemCount: menuList.length,
          itemBuilder: (context, index) {
            return MenuButton(menu: menuList[index], isLogout: index == menuList.length-1);
          },
        ),
        SizedBox(height: ResponsiveHelper.isMobile(context) ? Dimensions.paddingSizeSmall : 0),
        SafeArea(
          child: RichText(
            text: TextSpan(
                text: "app_version".tr,
                style: robotoRegular.copyWith(fontSize: Dimensions.fontSizeDefault,color: Theme.of(context).primaryColorLight),
                children: <TextSpan>[
                  TextSpan(
                    text: " ${AppConstants.appVersion} ",
                    style: robotoBold.copyWith(fontSize: Dimensions.fontSizeDefault),
                  )
                ]
            ),
          ),
        ),
        const SizedBox(height: 10)

      ]),
    );
  }
}
