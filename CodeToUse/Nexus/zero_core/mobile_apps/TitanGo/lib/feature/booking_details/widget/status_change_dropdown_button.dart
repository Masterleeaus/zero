import 'package:get/get.dart';
import 'package:demandium_serviceman/utils/core_export.dart';



class StatusChangeDropdownButton extends StatelessWidget {
  final String bookingId;
  final BookingDetailsContent bookingDetails;
  final bool isSubBooking;
  const StatusChangeDropdownButton({super.key, required this.bookingId, required this.bookingDetails, required this.isSubBooking}) ;

  @override
  Widget build(BuildContext context) {
    return GetBuilder<BookingDetailsController>(builder: (bookingDetailsController){

      List<String> statusList = [];
      for (var element in bookingDetailsController.statusTypeList) {
        statusList.add(element);
      }
      if(((isSubBooking && bookingDetails.isPaid == 1)
          || bookingDetailsController.bookingDetails?.bookingContent?.bookingDetailsContent?.bookingStatus == 'ongoing')
          && statusList.contains("canceled")){
        statusList.remove("canceled");
      }

      return Container(
        padding: const EdgeInsets.symmetric(horizontal: Dimensions.paddingSizeSmall, vertical: Dimensions.paddingSizeSmall),
        height: 70,
        child: bookingDetailsController.dropDownValue == "completed" && bookingDetailsController.showPhotoEvidenceField && Get.find<SplashController>().configModel?.content?.bookingOtpVerification == 1?
        CustomButton(btnTxt: "request_for_otp".tr, onPressed: () {
          bookingDetailsController.sendBookingOTPNotification(bookingId, shouldUpdate: false);
          Get.bottomSheet(OtpVerificationBottomSheet(bookingId: bookingId, isSubBooking: isSubBooking,));
        },) :
        Row(children: [
          Expanded(
            child: Container(padding: const EdgeInsets.symmetric(horizontal: Dimensions.paddingSizeLarge), height: 45,
              decoration: BoxDecoration(
                  borderRadius: const BorderRadius.all(Radius.circular(5)),
                  border: Border.all(color: Get.isDarkMode? light.cardColor.withValues(alpha:0.3):
                  Theme.of(context).primaryColor.withValues(alpha:0.30)
                  )
              ),
              child: DropdownButtonHideUnderline(
                child: DropdownButton(
                  hint: bookingDetailsController.dropDownValue == ''?
                  Text(bookingDetails.bookingStatus!.tr) :
                  Text(bookingDetailsController.dropDownValue.tr),
                  dropdownColor: Theme.of(context).cardColor,
                  borderRadius: BorderRadius.circular(5),
                  elevation: 2,
                  icon: const Icon(Icons.keyboard_arrow_down),
                  items: statusList.map((String items) {
                    return DropdownMenuItem(value: items, child: Row(children: [Text(items.tr)]));
                  }).toList(),
                  onChanged:(String? newValue) {
                    bookingDetailsController.setSelectedValue(newValue!);

                    if(newValue=="completed"){

                      if(Get.find<SplashController>().configModel?.content?.bookingImageVerification == 1 && bookingDetailsController.pickedPhotoEvidence.isNotEmpty){
                        bookingDetailsController.changePhotoEvidenceStatus(status: true);
                      }else if(Get.find<SplashController>().configModel?.content?.bookingOtpVerification == 1) {
                        bookingDetailsController.changePhotoEvidenceStatus(status: true);
                      }else{
                        bookingDetailsController.changePhotoEvidenceStatus(status: false);
                      }

                      if(Get.find<SplashController>().configModel?.content?.bookingImageVerification == 0 && Get.find<SplashController>().configModel?.content?.bookingOtpVerification == 0){
                        Get.bottomSheet(PaymentReceiveDialog(bookingDetails.id!,bookingDetails.totalBookingAmount.toString(), isSubBooking: isSubBooking,));
                      }else{
                        if(Get.find<SplashController>().configModel?.content?.bookingImageVerification == 1  && bookingDetailsController.pickedPhotoEvidence.isEmpty){
                          showCustomBottomSheet(child: CameraButtonSheet(bookingId: bookingId, isSubBooking: isSubBooking,),);
                        }
                      }
                    }
                  },
                ),
              ),
            ),
          ),
          const SizedBox(width:Dimensions.paddingSizeDefault),
          bookingDetailsController.isUpdate ?
          SizedBox(height: 45,width: 112,
              child:Center(child: CircularProgressIndicator(color: Theme.of(context).hoverColor))
          ) : CustomButton(
            height: 45,width: 112,
            btnTxt:"change".tr,
            onPressed: bookingDetails.bookingStatus == "canceled"
                || bookingDetails.bookingStatus == "completed"
                || bookingDetails.bookingStatus == bookingDetailsController.dropDownValue? null
                : (){
              bookingDetailsController.changeBookingStatus(
                bookingId:bookingDetails.id.toString(),
                bookingStatus:bookingDetails.bookingStatus!,
                isSubBooking: isSubBooking
              );},
          )
        ]),
      );
    });
  }
}
