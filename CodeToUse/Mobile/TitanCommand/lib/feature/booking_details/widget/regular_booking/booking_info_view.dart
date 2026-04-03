import 'package:demandium_provider/helper/extension_helper.dart';
import 'package:get/get.dart';
import 'package:demandium_provider/util/core_export.dart';

class BookingInformationView extends StatelessWidget {
  final BookingDetailsContent bookingDetails;
  final bool isSubBooking;
  const BookingInformationView({super.key, required this.bookingDetails, required this.isSubBooking});

  @override
  Widget build(BuildContext context) {
    return  GetBuilder<BookingDetailsController>(builder: (bookingDetailsController){
      return Container(
        decoration: BoxDecoration(
          color: Theme.of(context).cardColor,
          boxShadow: context.customThemeColors.lightShadow
        ),
        padding: const EdgeInsets.symmetric(vertical: Dimensions.paddingSizeSmall, horizontal: Dimensions.paddingSizeDefault),
        child: Column(crossAxisAlignment: CrossAxisAlignment.start,
          children: [

            Row(mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Expanded(
                  child: Row(children: [
                    Text('${'booking'.tr} # ${bookingDetails.readableId}',
                      overflow: TextOverflow.ellipsis,
                      style: robotoBold.copyWith(fontSize: Dimensions.fontSizeLarge,
                          color: Theme.of(context).textTheme.bodyLarge!.color?.withValues(alpha:0.9), decoration: TextDecoration.none
                      ),
                    ),
                    if(isSubBooking) Container(
                      decoration: const BoxDecoration(shape: BoxShape.circle, color: Colors.green),
                      padding: const EdgeInsets.all(2),
                      margin: const EdgeInsets.symmetric(horizontal: Dimensions.paddingSizeExtraSmall),
                      child: const Icon(Icons.repeat, color: Colors.white,size: 12,),
                    )
                  ]),
                ),

                Container(
                  padding: const EdgeInsets.symmetric(
                      horizontal: Dimensions.paddingSizeDefault,
                      vertical: Dimensions.paddingSizeExtraSmall
                  ),
                  decoration: BoxDecoration(
                    borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
                    color: Get.isDarkMode?
                    Colors.grey.withValues(alpha:0.2):context.customThemeColors.buttonBackgroundColorMap[bookingDetails.bookingStatus],
                  ),
                  child: Center(
                    child: Text( bookingDetails.bookingStatus!.tr,
                      style: robotoMedium.copyWith(
                          fontWeight: FontWeight.w500,
                          fontSize: 12,
                          color:Get.isDarkMode?Theme.of(context).primaryColorLight : context.customThemeColors.buttonTextColorMap[bookingDetails.bookingStatus]
                      ),
                    ),
                  ),
                )
              ],
            ),

            const SizedBox(height:Dimensions.paddingSizeExtraSmall),
            BookingItem(
              img: Images.iconCalendar,
              title: '${'booking_date'.tr} : ',
              subTitle: DateConverter.dateMonthYearTime(
                  DateConverter.isoUtcStringToLocalDate(bookingDetails.createdAt!)),
            ),
            if(bookingDetails.serviceSchedule!=null) const SizedBox(height:Dimensions.paddingSizeExtraSmall),

            if(bookingDetails.serviceSchedule!=null) BookingItem(
              img: Images.iconCalendar,
              title: '${'scheduled_date'.tr} : ',
              subTitle: ' ${DateConverter.dateMonthYearTime(DateTime.tryParse(bookingDetails.serviceSchedule!))}',
            ),
            // const SizedBox(height:Dimensions.paddingSizeExtraSmall),
            // BookingItem(
            //   img: Images.iconLocation,
            //   title: '${'service_address'.tr} : ${bookingDetails.serviceAddress?.address ?? bookingDetails.subBooking?.serviceAddress?.address ?? 'address_not_found'.tr}',
            //   subTitle: '',
            // ),

          ],
        ),
      );
    });
  }
}

