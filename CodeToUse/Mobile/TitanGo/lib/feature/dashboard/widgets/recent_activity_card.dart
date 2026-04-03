import 'package:demandium_serviceman/helper/extension_helper.dart';
import 'package:get/get.dart';
import 'package:demandium_serviceman/utils/core_export.dart';


class RecentActivityItem extends StatelessWidget {
  final DashboardBooking activityData;
  final RepeatBooking? repeatBooking;
  const RecentActivityItem({
    super.key, required this.activityData, this.repeatBooking,
  }) ;

  @override
  Widget build(BuildContext context) {
    return GetBuilder<SplashController>(builder: (splashController) {
      return InkWell(
        onTap: (){
          Get.toNamed(RouteHelper.getBookingDetailsRoute(bookingId : activityData.id!));
        },
        child: Container(
          decoration: BoxDecoration(
            border: Border(
              bottom: BorderSide(
                color: Theme.of(context).dividerColor.withValues(alpha: 0.05),
                width: 2,
              ),
            ),
          ),
          child: Stack( children: [
            Row(crossAxisAlignment: CrossAxisAlignment.center, children: [
              Padding(
                padding: const EdgeInsets.all(Dimensions.paddingSizeSmall),
                child: ClipRRect(
                  borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
                  child: CustomImage(
                    height: 50, width: 50,
                    image: Get.find<SplashController>().configModel?.content != null && activityData.detail![0].service != null ?
                    activityData.detail![0].service!.thumbnailFullPath ?? "" : "",
                  ),
                ),
              ),
              const SizedBox(width: Dimensions.paddingSizeExtraSmall),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Row(children: [

                      Flexible(
                        child: RichText(
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          text: TextSpan(
                            children: [
                              TextSpan(
                                text: "${"booking".tr} ",
                                style: robotoMedium.copyWith(
                                  fontSize: Dimensions.fontSizeDefault,
                                  color: Theme.of(context).textTheme.titleLarge?.color,
                                  fontWeight: FontWeight.w400,
                                ),
                              ),
                              TextSpan(
                                text: "# ${repeatBooking?.readableId ?? activityData.readableId}",
                                style: robotoMedium.copyWith(
                                  fontSize: Dimensions.fontSizeDefault,
                                  color: Theme.of(context).textTheme.bodyLarge?.color,
                                  fontWeight: FontWeight.w600,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),

                      // Text("booking".tr + "# ${ repeatBooking?.readableId ?? activityData.readableId}".tr, maxLines: 1, style: robotoBold.copyWith(fontWeight: FontWeight.w700, color: const Color(0xff758590))),
                      if(repeatBooking?.readableId != null) Container(
                        decoration: const BoxDecoration(shape: BoxShape.circle, color: Colors.green),
                        padding: const EdgeInsets.all(2),
                        margin: const EdgeInsets.symmetric(horizontal: Dimensions.paddingSizeExtraSmall),
                        child: const Icon(Icons.repeat, color: Colors.white,size: 9,),
                      )
                    ]),
                    const SizedBox(height: Dimensions.paddingSizeExtraSmall),

                    Text(textDirection: TextDirection.ltr,
                      DateConverter.dateMonthYearTime(DateConverter.isoUtcStringToLocalDate( repeatBooking?.createdAt ?? activityData.createdAt!)),
                      style: robotoRegular.copyWith(fontSize: Dimensions.fontSizeSmall, color: const Color(0xff758590)),
                    )
                  ],
                ),
              ),
              Container(
                padding: EdgeInsets.symmetric(horizontal: Dimensions.fontSizeSmall,vertical: Dimensions.paddingSizeExtraSmall),
                decoration: BoxDecoration(
                  borderRadius: BorderRadius.circular(5),
                  color:Get.isDarkMode?Colors.grey.withValues(alpha:0.2):
                  context.customThemeColors.buttonBackgroundColorMap[repeatBooking?.bookingStatus ?? activityData.bookingStatus],
                ),
                child: Text(
                  repeatBooking?.bookingStatus?.tr ?? activityData.bookingStatus!.tr,
                  style:robotoMedium.copyWith(fontWeight: FontWeight.w500, fontSize: Dimensions.fontSizeSmall,
                      color: context.customThemeColors.buttonTextColorMap[repeatBooking?.bookingStatus ?? activityData.bookingStatus]
                  ),
                ),
              ),
            ]),
            Positioned.fill(child: CustomInkWell(
              onTap: (){
                Get.toNamed(RouteHelper.getBookingDetailsRoute(bookingId : repeatBooking?.id ?? activityData.id!, isSubBooking: repeatBooking != null));
              },
            ))
          ]),
        ),
      );
    });
  }}