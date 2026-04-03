import 'package:demandium_serviceman/feature/booking_details/widget/booking_service_location.dart';
import 'package:demandium_serviceman/helper/extension_helper.dart';
import 'package:get/get.dart';
import 'package:demandium_serviceman/utils/core_export.dart';

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
            boxShadow: Get.find<ThemeController>().darkTheme ? null : lightShadow
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
                    borderRadius: BorderRadius.circular(5),
                    color: Get.isDarkMode?
                    Colors.grey.withValues(alpha:0.2): context.customThemeColors.buttonBackgroundColorMap[bookingDetails.bookingStatus],
                  ),
                  child: Center(
                    child: Text(bookingDetails.bookingStatus?.tr ?? "",
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
            const SizedBox(height:Dimensions.paddingSizeDefault),
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

            const SizedBox(height:Dimensions.paddingSizeExtraSmall),
            BookingServiceLocation(bookingDetails: bookingDetails, isSubBooking: isSubBooking,),

            Padding(
              padding: const EdgeInsets.symmetric(horizontal: Dimensions.paddingSizeExtraSmall),
              child: Column( crossAxisAlignment: CrossAxisAlignment.start, children: [
                const SizedBox(height:Dimensions.paddingSizeSmall),
                Text("payment_method".tr,
                  style: robotoBold.copyWith(
                      fontSize: Dimensions.fontSizeDefault,
                      color: Theme.of(context).textTheme.bodyLarge!.color),
                ),
                const SizedBox(height:Dimensions.paddingSizeExtraSmall),

                Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, crossAxisAlignment: CrossAxisAlignment.start, children: [
                  Flexible(
                    child: Text("${bookingDetails.paymentMethod!.tr} ${ bookingDetails.partialPayments !=null  && bookingDetails.partialPayments!.isNotEmpty ? "&_wallet_balance".tr: ""}",
                        style: robotoRegular.copyWith(fontSize: Dimensions.fontSizeSmall, color: Theme.of(context).textTheme.bodyLarge!.color!.withValues(alpha:.5))),
                  ),
                  SizedBox(width: Dimensions.paddingSizeSmall),

                  RichText(
                    text: TextSpan(text: "${'payment_status'.tr} ",
                      style: robotoMedium.copyWith(fontSize: Dimensions.fontSizeDefault, color: Theme.of(context).hintColor),
                      children: <TextSpan>[
                        TextSpan(
                          text: bookingDetails.partialPayments != null && bookingDetails.partialPayments!.isNotEmpty && bookingDetails.isPaid == 0 ? "partially_paid".tr :  bookingDetails.isPaid == 0 ? "unpaid".tr : "paid".tr,
                          style: robotoBold.copyWith(
                            fontSize: Dimensions.fontSizeDefault,
                            color: bookingDetails.partialPayments != null && bookingDetails.partialPayments!.isNotEmpty && bookingDetails.isPaid == 0 ? Theme.of(context).primaryColor : bookingDetails.isPaid == 0 ?
                            Theme.of(context).colorScheme.error: Colors.green,
                          ),
                        )
                      ],
                    ),
                  ),
                ]),
                const SizedBox(height:Dimensions.paddingSizeExtraSmall),

                (bookingDetails.paymentMethod !="cash_after_service" && bookingDetails.paymentMethod !="offline_payment") ?
                Padding(padding: const EdgeInsets.only(bottom : Dimensions.paddingSizeExtraSmall),
                  child: Text("${'transaction_id'.tr} : ${bookingDetails.transactionId}",
                    style: robotoRegular.copyWith(
                      fontSize: Dimensions.fontSizeSmall,
                      color: Theme.of(context).hintColor,
                    ),
                  ),
                ):const SizedBox.shrink(),


                Row(
                  children: [
                    Text("${'amount'.tr} : ",
                      style: robotoBold.copyWith(
                        fontSize: Dimensions.fontSizeSmall,
                        color: Theme.of(context).textTheme.bodyLarge!.color!.withValues(alpha:0.7),
                      ),
                    ),

                    Text(PriceConverter.convertPrice(bookingDetails.totalBookingAmount ?? 0,isShowLongPrice:true),
                      style: robotoBold.copyWith(
                        fontSize: Dimensions.fontSizeSmall,
                        color: Theme.of(context).textTheme.bodyLarge!.color!.withValues(alpha:0.7),
                      ),
                    ),
                  ],
                )

              ]),
            )

          ],
        ),
      );
    });
  }
}

