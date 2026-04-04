import 'package:demandium_serviceman/helper/extension_helper.dart';
import 'package:get/get.dart';
import 'package:demandium_serviceman/utils/core_export.dart';

class BookingRequestItem extends StatelessWidget {
  final BookingRequestModel? bookingRequestModel;
  final RepeatBooking? repeatBooking;
  const BookingRequestItem({
    super.key,  this.bookingRequestModel, this.repeatBooking,});

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color: Theme.of(context).cardColor,
        borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
        boxShadow: Get.find<ThemeController>().darkTheme ? null : lightShadow,
      ),
      margin: const EdgeInsets.symmetric(horizontal: Dimensions.paddingSizeSmall, vertical: Dimensions.paddingSizeExtraSmall),
      child: Stack( children: [
        Column(crossAxisAlignment: CrossAxisAlignment.start , children: [

          Padding(
            padding: const EdgeInsets.fromLTRB(Dimensions.paddingSizeDefault,
              Dimensions.paddingSizeDefault, Dimensions.paddingSizeDefault, Dimensions.paddingSizeExtraSmall+3,
            ),
            child: Row(children: [ Expanded(
              child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                Row(
                  children: [
                    Text("booking".tr,
                      style: robotoMedium.copyWith(
                        fontSize: Dimensions.fontSizeLarge,
                        color: Theme.of(context).textTheme.bodySmall!.color!.withValues(alpha:0.7),
                      ),
                    ),
                    Text("${'booking'.tr} # ${repeatBooking?.readableId ?? bookingRequestModel?.readableId ?? ""}",
                      style: robotoBold.copyWith(
                        color: Theme.of(context).textTheme.bodyLarge!.color!.withValues(alpha:0.8),
                        fontSize: Dimensions.fontSizeLarge,
                      ),
                      overflow: TextOverflow.ellipsis,
                    ),

                    if(bookingRequestModel?.isRepeatBooking == 1) Container(
                      decoration: const BoxDecoration(shape: BoxShape.circle, color: Colors.green),
                      padding: const EdgeInsets.all(2),
                      margin: const EdgeInsets.symmetric(horizontal: Dimensions.paddingSizeExtraSmall),
                      child: const Icon(Icons.repeat, color: Colors.white,size: 10,),
                    )
                  ],
                ),
                Text(bookingRequestModel?.subCategory?.name ?? " ",
                  style: robotoRegular.copyWith(fontSize: Dimensions.fontSizeSmall + 2,  color: Theme.of(context).textTheme.bodyLarge?.color?.withValues(alpha:0.6)),
                  maxLines: 1, overflow: TextOverflow.ellipsis,
                ),
              ])),

              const SizedBox(width: Dimensions.paddingSizeDefault,),

              Container(
                padding: const EdgeInsets.symmetric(vertical: Dimensions.paddingSizeExtraSmall, horizontal: Dimensions.paddingSizeDefault),
                decoration: BoxDecoration(
                  borderRadius: BorderRadius.circular(50),
                  color: Get.isDarkMode?Colors.grey.withValues(alpha:0.2):
                  context.customThemeColors.buttonBackgroundColorMap[ repeatBooking?.bookingStatus  ?? bookingRequestModel?.bookingStatus],
                ),
                child: Center(
                  child: Text('${repeatBooking?.bookingStatus  ?? bookingRequestModel?.bookingStatus}'.tr,
                    style:robotoMedium.copyWith(fontWeight: FontWeight.w500, fontSize: 12,
                      color: context.customThemeColors.buttonTextColorMap[repeatBooking?.bookingStatus  ?? bookingRequestModel?.bookingStatus],
                    ),
                  ),
                ),
              ),
            ]),
          ),

          Container(height: 2,width: double.infinity,
            decoration: BoxDecoration(
              color: Theme.of(context).primaryColor.withValues(alpha:0.1),
            ),
          ),
          Padding(
            padding: const EdgeInsets.fromLTRB(Dimensions.paddingSizeDefault,
              Dimensions.paddingSizeExtraSmall, Dimensions.paddingSizeDefault, Dimensions.paddingSizeDefault,
            ),
            child: Row(children: [
              Expanded(
                child: Column(children: [
                  Row(
                    children: [
                      Text("${'booking_date'.tr}: ",
                          style: robotoRegular.copyWith(fontSize: Dimensions.fontSizeSmall,   color: Theme.of(context).textTheme.bodyLarge?.color?.withValues(alpha:0.6))
                      ),
                      Expanded(
                        child: Text(" ${DateConverter.dateMonthYearTime(DateConverter
                            .isoUtcStringToLocalDate( repeatBooking?.createdAt ?? bookingRequestModel!.createdAt!))}",
                          style: robotoRegular.copyWith(fontSize: Dimensions.fontSizeSmall,   color: Theme.of(context).textTheme.bodyLarge?.color?.withValues(alpha:0.6)),
                          textDirection: TextDirection.ltr,
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      )
                    ],
                  ),

                  const SizedBox(height: Dimensions.paddingSizeExtraSmall,),
                  Row( children: [
                    Text("${'scheduled_date'.tr}: ",
                      style: robotoRegular.copyWith(fontSize: Dimensions.fontSizeSmall ,  color: Theme.of(context).textTheme.bodyLarge?.color?.withValues(alpha:0.6)),
                    ),
                    if(repeatBooking?.serviceSchedule != null || bookingRequestModel?.serviceSchedule !=null)Expanded(
                      child: Text(DateConverter.dateMonthYearTime(DateTime.tryParse(repeatBooking?.serviceSchedule ?? bookingRequestModel!.serviceSchedule!)),
                        style: robotoRegular.copyWith(fontSize: Dimensions.fontSizeSmall ,  color: Theme.of(context).textTheme.bodyLarge?.color?.withValues(alpha:0.6)),
                        textDirection: TextDirection.ltr,
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                  ]),
                ]),
              ),

              Column(crossAxisAlignment: CrossAxisAlignment.end, children: [
                Text('total_amount'.tr,
                  style: robotoRegular.copyWith(fontSize: Dimensions.fontSizeDefault,  color: Theme.of(context).secondaryHeaderColor),
                ),
                Text(PriceConverter.convertPrice( repeatBooking?.totalBookingAmount ?? bookingRequestModel?.totalBookingAmount ?? 0),
                  style: robotoMedium.copyWith(fontSize: Dimensions.fontSizeLarge, color: Theme.of(context).primaryColorLight),
                ),
              ]),
            ]),
          ),

          Container(
            decoration: BoxDecoration(
              borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
              color: Theme.of(context).primaryColor.withValues(alpha: 0.1),
            ),
            margin: EdgeInsets.fromLTRB( Dimensions.paddingSizeDefault, 0, Dimensions.paddingSizeDefault, Dimensions.paddingSizeSmall),
            padding: EdgeInsets.symmetric(horizontal: Dimensions.paddingSizeDefault, vertical: Dimensions.paddingSizeExtraSmall),
            child: Row(spacing: Dimensions.paddingSizeExtraSmall, children: [
              Icon(Icons.location_on_rounded, size: 17),
              Text(
                (repeatBooking?.serviceLocation == "provider" || bookingRequestModel?.serviceLocation == "provider") ?  "provider_location".tr : "service_location".tr ,
                style: robotoMedium.copyWith(
                    color: Theme.of(context).textTheme.bodyLarge?.color?.withValues(alpha: 0.8)
                ),
              )
            ]),
          )
        ],),
        Positioned.fill(child: CustomInkWell(
          onTap: ()=>Get.toNamed(
            RouteHelper.getBookingDetailsRoute( bookingId: repeatBooking?.id ?? bookingRequestModel!.id!, isSubBooking: repeatBooking != null),
          ),
        ))
      ]),
    );
  }
}

Widget bookingDate(String dateType,String date){
  return Builder(
    builder: (context) {
      return Row(
        children: [
          Text(dateType, style: robotoRegularLow.copyWith(color: Theme.of(context).secondaryHeaderColor)),
          Text(
            textDirection: TextDirection.ltr,
            DateConverter.dateMonthYearTime(DateConverter.isoUtcStringToLocalDate(date)),
            style: robotoRegularLow.copyWith(color: Theme.of(context).secondaryHeaderColor)
          ),
        ],
      );
    }
  );
}






