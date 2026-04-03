import 'package:get/get.dart';
import 'package:demandium_serviceman/utils/core_export.dart';

class MenuScreen extends StatelessWidget {
  const MenuScreen({super.key,});

  @override
  Widget build(BuildContext context) {

    ConfigModel? configModel  = Get.find<SplashController>().configModel;
    double ratio = ResponsiveHelper.isDesktop(context) ? 1.1 : ResponsiveHelper.isTab(context) ? 1.1 : 1.2;

    final List<MenuModel> menuList = [
      MenuModel(icon: Images.profile, title: 'profile'.tr, route: RouteHelper.getProfileRoute()),
      MenuModel(icon: Images.languageIcon, title: 'language'.tr, route: RouteHelper.getLanguageRoute()),
      MenuModel(icon: Images.chatImage, title: 'inbox'.tr, route: RouteHelper.getInboxScreenRoute()),

      ...(configModel
          ?.content
          ?.businessPages
          ?? []).map((page) => MenuModel(
        icon: page.pageKey == HtmlType.aboutUs.value
            ? Images.aboutUs
            : page.pageKey == HtmlType.termsAndCondition.value
            ? Images.termsIcon : page.pageKey == HtmlType.privacyPolicy.value
            ? Images.privacyPolicyIcon : page.pageKey == HtmlType.cancellationPolicy.value
            ? Images.cancellationPolicy : page.pageKey == HtmlType.refundPolicy.value ? Images.refundPolicy : Images.othersPageIcon, // Or choose icon based on page
        title: page.pageKey == HtmlType.aboutUs.value
            ? 'about_us'.tr
            : page.pageKey == HtmlType.termsAndCondition.value
            ? 'terms_conditions'.tr : page.pageKey == HtmlType.privacyPolicy.value
            ? 'privacy_policy'.tr : page.pageKey == HtmlType.cancellationPolicy.value
            ? 'cancellation_policy'.tr : page.pageKey == HtmlType.refundPolicy.value ? 'refund_policy'.tr : page.title ?? '',
        route: RouteHelper.getHtmlRoute(page.pageKey!),
      )),


      // if(aboutUs!='')MenuModel(icon: Images.aboutUs, title: 'about_us'.tr, route: RouteHelper.getHtmlRoute('about_us')),
      // if(privacyPolicy!='')MenuModel(icon: Images.privacyPolicy, title: 'privacy_policy'.tr, route: RouteHelper.getHtmlRoute("privacy-policy")),
      // if(termsAndCondition!='') MenuModel(icon: Images.termsConditions, title: 'terms_conditions'.tr, route: RouteHelper.getHtmlRoute('terms-and-condition')),
      // if(refundPolicy!='')MenuModel(icon: Images.refundPolicy, title: 'refund_policy'.tr, route: RouteHelper.getHtmlRoute('refund_policy')),
      // if(cancellationPolicy!='')MenuModel(icon: Images.cancellationPolicy, title: 'cancellation_policy'.tr, route: RouteHelper.getHtmlRoute('cancellation_policy')),
      MenuModel(icon: Images.logout, title: 'logout'.tr , route: RouteHelper.signIn),
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
