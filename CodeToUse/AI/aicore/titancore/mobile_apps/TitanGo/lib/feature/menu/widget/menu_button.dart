import 'package:demandium_serviceman/feature/checklist/view/checklists_screen.dart';
import 'package:demandium_serviceman/feature/reports/view/report_issue_screen.dart';
import 'package:demandium_serviceman/feature/reports/view/supply_issue_screen.dart';
import 'package:demandium_serviceman/feature/titan_ai/view/titan_ai_screens.dart';
import 'package:get/get.dart';
import 'package:demandium_serviceman/utils/core_export.dart';


class MenuButton extends StatelessWidget {
  final MenuModel? menu;
  final bool? isLogout;
  const MenuButton({super.key, required this.menu, required this.isLogout});

  @override
  Widget build(BuildContext context) {
    int count = ResponsiveHelper.isDesktop(context) ? 8 : ResponsiveHelper.isTab(context) ? 6 : 4;
    double size = ((context.width > Dimensions.webMaxWidth ? Dimensions.webMaxWidth : context.width)/count)-Dimensions.paddingSizeDefault;

    return InkWell(
      onTap: () async {
        if(isLogout!) {
          Get.back();
          if(Get.find<AuthController>().isLoggedIn()) {
            Get.dialog(
              ConfirmationDialog(
                icon: Images.logout,
                title: 'are_you_sure_to_logout'.tr,
                onNoPressed: () {
                  Get.back();
                },
                onYesPressed: () {
                  Get.find<AuthController>().clearSharedData();
                  Get.offAllNamed(RouteHelper.getSignInRoute(RouteHelper.splash));
                }, description: '',),
              useSafeArea: false,
            );
          }else {
          }
        }
        else if(menu!.route!.contains('profile')) {
          Get.offNamed(RouteHelper.getProfileRoute());
        }else if(menu!.route!.contains('language')) {
          Get.back();
          Get.bottomSheet(const ChooseLanguageBottomSheet(), backgroundColor: Colors.transparent, isScrollControlled: true);
        }else if(_isSpecialRoute(menu!.route!)) {
          Get.back();
          _navigateSpecial(menu!.route!);
        }else {
          Get.offNamed(menu!.route!);
        }
      },

      child: Column(children: [
        Container(
          decoration: BoxDecoration(
            borderRadius: const BorderRadius.all(Radius.circular(Dimensions.paddingSizeExtraSmall)),
            color: Get.isDarkMode?Colors.grey.withValues(alpha:0.2):Theme.of(context).primaryColor.withValues(alpha:0.05),
          ),
          height: size-(size*0.2),
          padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
          margin: const EdgeInsets.symmetric(horizontal: Dimensions.paddingSizeSmall),
          alignment: Alignment.center,
          child: Image.asset(menu!.icon!, width: size, height: size),
        ),
        const SizedBox(height: Dimensions.paddingSizeExtraSmall),
        Text(menu!.title!, style: robotoMedium.copyWith(fontSize: Dimensions.fontSizeSmall), textAlign: TextAlign.center),
      ]),
    );
  }

  static bool _isSpecialRoute(String route) => route.startsWith('_');

  static void _navigateSpecial(String route) {
    switch (route) {
      case '_checklists':
        Get.to(() => const ChecklistsScreen());
        break;
      case '_supply_issues':
        Get.to(() => const SupplyIssueScreen());
        break;
      case '_report_issue':
        Get.to(() => const ReportIssueScreen());
        break;
      case '_ask_titan':
        Get.to(() => const AskTitanScreen());
        break;
      case '_voice_control':
        Get.to(() => const VoiceControlScreen());
        break;
      case '_training':
        Get.to(() => const TrainingScreen());
        break;
      case '_help_support':
        Get.toNamed(RouteHelper.getInboxScreenRoute());
        break;
    }
  }
}
