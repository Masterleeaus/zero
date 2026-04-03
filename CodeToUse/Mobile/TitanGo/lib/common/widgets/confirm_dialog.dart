import 'package:demandium_serviceman/utils/core_export.dart';
import 'package:get/get.dart';

class ConfirmationDialog extends StatelessWidget {
  final String? icon;
  final String? title;
  final String? description;
  final Color? yesButtonColor;
  final Function()? onYesPressed;
  final bool? isLogOut;
  final Color? imageBackgroundColor;
  final Function? onNoPressed;
  final bool isLoading;
  const ConfirmationDialog({super.key, required this.icon, this.title, required this.description, required this.onYesPressed,
    this.isLogOut = false, this.onNoPressed, this.yesButtonColor=const Color(0xFFF24646), this.imageBackgroundColor, this.isLoading=false});

  @override
  Widget build(BuildContext context) {
    return Dialog(
      elevation: 0,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(Dimensions.radiusDefault)),
      insetPadding: const EdgeInsets.all(30),
      backgroundColor: Theme.of(context).cardColor,
      clipBehavior: Clip.antiAliasWithSaveLayer,
      child: SizedBox(width: 320, child: Padding(
        padding: const EdgeInsets.all(Dimensions.paddingSizeLarge),
        child: Column(mainAxisSize: MainAxisSize.min, children: [

          Padding(
            padding: const EdgeInsets.all(Dimensions.paddingSizeLarge),
            child: Image.asset(
              icon!,
              width: 50,
              height: 50,
              color: imageBackgroundColor,
            ),
          ),

          title != null ? Padding(
            padding: const EdgeInsets.symmetric(horizontal: Dimensions.paddingSizeLarge),
            child: Text(
              title!, textAlign: TextAlign.center,
              style: robotoMedium.copyWith(
                  fontSize: Dimensions.fontSizeDefault,
                  color: Theme.of(context).textTheme.bodyLarge!.color!.withValues(alpha:0.8)
              ),
            ),
          ) : const SizedBox(),

          Padding(
            padding: const EdgeInsets.all(Dimensions.paddingSizeSmall),
            child: Text(description!,
                style: robotoRegular.copyWith(
                    fontSize: Dimensions.fontSizeSmall,color: Theme.of(context).hintColor),
                textAlign: TextAlign.center
            ),
          ),
          const SizedBox(height: Dimensions.paddingSizeLarge),

          Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const SizedBox(width: Dimensions.paddingSizeLarge),

                Expanded(child: TextButton(
                  onPressed: () => isLogOut! ? onYesPressed!() : onNoPressed != null ? onNoPressed!() : Get.back(),
                  style: TextButton.styleFrom(
                    backgroundColor: Theme.of(context).hintColor.withValues(alpha:0.3),
                    minimumSize: const Size(Dimensions.webMaxWidth, 40),
                    padding: EdgeInsets.zero,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(Dimensions.radiusSmall)),
                  ),
                  child: Text(
                    isLogOut! ? 'yes'.tr : 'no'.tr, textAlign: TextAlign.center,
                    style: robotoBold.copyWith(color: Theme.of(context).textTheme.bodyLarge!.color),
                  ),
                )),
                const SizedBox(width: Dimensions.paddingSizeLarge),


                Expanded(
                    child: isLoading?
                    SizedBox(
                      height: 30,
                      width: 30,
                      child: CircularProgressIndicator(
                        color: Theme.of(context).hoverColor,
                      ),
                    ): CustomButton(
                      color: yesButtonColor,
                      btnTxt: isLogOut! ? 'no'.tr : 'yes'.tr,
                      onPressed: () => isLogOut! ? Get.back() : onYesPressed!(),
                      radius: Dimensions.radiusSmall, height: 40,
                    )
                ),
                const SizedBox(width: Dimensions.paddingSizeLarge),

              ]),

        ]),
      )),
    );
  }
}