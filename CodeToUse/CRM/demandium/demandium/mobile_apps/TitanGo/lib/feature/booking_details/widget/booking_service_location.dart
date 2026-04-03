import 'package:demandium_serviceman/utils/core_export.dart';
import 'package:geolocator/geolocator.dart';
import 'package:get/get.dart';

class BookingServiceLocation extends StatelessWidget {
  final BookingDetailsContent bookingDetails;
  final bool isSubBooking;
  const BookingServiceLocation({super.key, required this.bookingDetails, required this.isSubBooking});

  @override
  Widget build(BuildContext context) {
    return  GetBuilder<BookingDetailsController>(builder: (bookingDetailsController){
      return Column(crossAxisAlignment: CrossAxisAlignment.start,
        children: [


          SizedBox(height: Dimensions.paddingSizeSmall),

          Container(
            decoration: BoxDecoration(
              borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
              color: Theme.of(context).primaryColor.withValues(alpha: 0.05),
            ),
            padding: EdgeInsets.all(Dimensions.paddingSizeSmall),
            child: Column(crossAxisAlignment: CrossAxisAlignment.start, spacing: Dimensions.paddingSizeSmall, children: [


              Row(children: [
                Expanded(
                  child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                    Text("${'service_location'.tr} : ",
                      overflow: TextOverflow.ellipsis,
                      style: robotoMedium.copyWith(fontSize: Dimensions.fontSizeLarge,
                        color: Theme.of(context).textTheme.bodyLarge!.color?.withValues(alpha:0.8),
                      ),
                    ),
                  
                    Text(
                      bookingDetails.serviceLocation == "customer"
                          ? bookingDetails.serviceAddress?.address ?? bookingDetails.subBooking?.serviceAddress?.address ?? 'address_not_found'.tr
                          : bookingDetails.provider?.companyAddress ?? bookingDetails.subBooking?.provider?.companyAddress ?? 'address_not_found'.tr,
                      maxLines: 1, overflow: TextOverflow.ellipsis,
                      style: robotoRegular.copyWith(color: Theme.of(context).textTheme.bodySmall?.color?.withValues(alpha: 0.65)),
                    ),
                  ]),
                ),
                InkWell(
                  onTap: (){
                    _checkPermission(() async {
                      if(bookingDetails.serviceAddress != null || bookingDetails.provider != null){
                        Get.dialog(const CustomLoader(),barrierDismissible: false);
                        await Geolocator.getCurrentPosition().then((position) {

                          double lat = bookingDetails.serviceLocation == "customer"
                              ? double.tryParse(bookingDetails.serviceAddress?.lat ?? "0") ?? 23.8103
                              : bookingDetails.provider?.coordinates?.lat ?? 23.00 ;

                          double lon = bookingDetails.serviceLocation == "customer"
                              ? double.tryParse(bookingDetails.serviceAddress?.lon ?? "0") ?? 90.4125
                              : bookingDetails.provider?.coordinates?.lng ?? 90.4125;

                          MapUtils.openMap( lat, lon,
                            position.latitude , position.longitude,
                          );
                        });
                        Get.back();
                      }else{
                        showCustomSnackBar("service_address_not_found".tr, type: ToasterMessageType.info);
                      }
                    });
                  },
                  child: Container(
                    decoration: BoxDecoration(
                      borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
                      border: Border.all(color: Theme.of(context).primaryColor, width: 0.6),
                      color: Theme.of(context).cardColor,
                    ),
                    padding: EdgeInsets.all(Dimensions.paddingSizeExtraSmall + 2),
                    child: Icon(Icons.location_on_rounded, color: Theme.of(context).primaryColor, size: 20,),
                  ),
                ),
              ]),


              Container(
                decoration: BoxDecoration(
                  borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
                  color: Theme.of(context).colorScheme.onTertiary,
                ),
                padding: EdgeInsets.symmetric(horizontal: Dimensions.paddingSizeDefault, vertical: Dimensions.paddingSizeSmall),
                child: RichText(
                  text: TextSpan(
                    text:  "you_need_to_go_to_the".tr,
                    style: robotoRegular.copyWith(fontSize: Dimensions.fontSizeDefault,  color: Theme.of(context).textTheme.bodySmall!.color),
                    children: <TextSpan>[
                      TextSpan(
                        text: " ${ (bookingDetails.serviceLocation == "provider" || bookingDetails.subBooking?.serviceLocation == "provider") ? "provider_location".tr : 'customer_location'.tr} ",
                        style: robotoBold.copyWith(fontSize: Dimensions.fontSizeDefault,  color: Theme.of(context).textTheme.bodyLarge!.color),
                      ),
                      TextSpan(
                        text: " ${'to_provide_the_service'.tr} ",
                        style: robotoRegular.copyWith(fontSize: Dimensions.fontSizeDefault,  color: Theme.of(context).textTheme.bodySmall!.color),
                      ),
                    ],
                  ),
                ),
              ),

            ]),
          )
        ],
      );
    });
  }

  void _checkPermission(Function onTap) async {
    LocationPermission permission = await Geolocator.checkPermission();
    if(permission == LocationPermission.denied) {
      permission = await Geolocator.requestPermission();
    }
    if(permission == LocationPermission.denied) {
      showCustomSnackBar('you_have_to_allow'.tr, type : ToasterMessageType.info);
    }else if(permission == LocationPermission.deniedForever) {
      Get.dialog( const PermissionDialog(), barrierDismissible: true);
    }else {
      onTap();
    }
  }

}

class MapUtils {
  MapUtils._();

  static Future<void> openMap(double destinationLatitude, double destinationLongitude, double userLatitude, double userLongitude) async {
    String googleUrl = 'https://www.google.com/maps/dir/?api=1&origin=$userLatitude,$userLongitude'
        '&destination=$destinationLatitude,$destinationLongitude&mode=d';
    if (await canLaunchUrl(Uri.parse(googleUrl))) {
      await launchUrl(Uri.parse(googleUrl), mode: LaunchMode.externalApplication);
    } else {
      throw 'Could not open the map.';
    }
  }
}

