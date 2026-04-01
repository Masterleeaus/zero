import 'package:demandium_serviceman/utils/core_export.dart';
import 'package:get/get.dart';

class MainAppBar extends StatelessWidget implements PreferredSizeWidget {
  final String? title;
  final Color color;
  final double? titleFontSize;
  const MainAppBar({
    super.key,
    this.title,
    this.titleFontSize,
    required this.color,
  }) ;

  @override
  Widget build(BuildContext context) {

    return GetBuilder<NotificationController>(builder: (notificationController){
      return AppBar(
        elevation: 7,
        titleSpacing: -5,
        backgroundColor: Theme.of(context).colorScheme.surface,
        surfaceTintColor: Theme.of(context).cardColor,
        shadowColor: Get.isDarkMode?Theme.of(context).primaryColor.withValues(alpha:0.5):Theme.of(context).primaryColor.withValues(alpha:0.1),
        leading: Padding(
          padding: const EdgeInsets.symmetric(horizontal: Dimensions.paddingSizeSmall + 3,
              vertical: Dimensions.paddingSizeExtraSmall),
          child: Image.asset(Images.appbarLogo),
        ),
        title: title!=null?
        Text(title!.tr,
          style: robotoBold.copyWith(
            color: Theme.of(context).primaryColor,
            fontSize: titleFontSize ?? Dimensions.fontSizeExtraLarge,
          ),
        ):
        Image.asset(Images.logo, width: 110,),
        actions: [
          Padding(
            padding: const EdgeInsets.only(top: 12.0),
            child: InkWell(
              onTap: ()=> Get.toNamed(RouteHelper.getInboxScreenRoute()),
              child: Image.asset(Images.appbarMessage, height: 20,),
            ),
          ),

          Padding(
            padding: const EdgeInsets.only(top: 10.0),
            child: Stack(
              children: [
                IconButton(
                  onPressed: () {
                    Get.to(const NotificationScreen());
                    notificationController.resetNotificationCount();
                  },
                  icon: Image.asset(Images.appbarNotification, height: 22,),
                ),
                if(notificationController.unseenNotificationCount > 0 )
                Positioned(
                  right: 2,
                  top: 0,
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 2,vertical: 3),
                    height: 20,
                    width: 20,
                    decoration: BoxDecoration(
                        shape: BoxShape.circle,
                        color: Theme.of(context).primaryColor
                    ),
                    child: FittedBox(
                        child: Text(
                          notificationController.unseenNotificationCount.toString(),
                          style: robotoRegular.copyWith(color: light.cardColor
                          ),
                        )
                    ),
                  ),
                )
              ],
            ),
          ),
          const SizedBox(
            width: Dimensions.paddingSizeExtraSmall,
          )
        ],
      );
    });
  }

  @override
  Size get preferredSize => const Size(double.maxFinite, 55);
}
