import 'package:get/get.dart';
import 'package:demandium_serviceman/utils/core_export.dart';



class BookingDetailsWidget extends StatefulWidget{
  final String? bookingId;
  final bool isSubBooking;
  const BookingDetailsWidget({super.key, this.bookingId, required this.isSubBooking}) ;

  @override
  State<BookingDetailsWidget> createState() => _BookingDetailsWidgetState();
}

class _BookingDetailsWidgetState extends State<BookingDetailsWidget> {

  @override
  Widget build(BuildContext context) {
    return GetBuilder<BookingDetailsController>(
      builder: (bookingDetailsController){

        final bookingDetailsModel = bookingDetailsController.bookingDetails;
        final bookingDetails = bookingDetailsController.bookingDetails?.bookingContent?.bookingDetailsContent;

        bool showDeliveryConfirmImage = bookingDetailsController.showPhotoEvidenceField;
        ConfigModel? configModel = Get.find<SplashController>().configModel;

        int isGuest = bookingDetails?.isGuest ?? 0;
        bool isPartial =  (bookingDetails !=null && bookingDetails.partialPayments !=null && bookingDetails.partialPayments!.isNotEmpty) ? true : false ;
        String bookingStatus = bookingDetails?.bookingStatus ?? "";
        bool subBookingPaid = widget.isSubBooking && bookingDetails?.isPaid == 1;

        bool isEditBooking = (configModel?.content?.serviceManCanEditBooking == 1
            && bookingDetailsController.bookingDetails?.bookingContent?.providerServicemanCanEditBooking == 1)
            && (!subBookingPaid && !isPartial && (bookingStatus == "accepted" || bookingStatus == "ongoing")
                && ((isGuest == 1 && bookingDetails?.paymentMethod != "cash_after_service") ? false : true));

        return bookingDetailsModel == null && bookingDetailsModel?.bookingContent == null ? const BookingDetailsShimmer() :
        bookingDetailsModel != null && bookingDetailsModel.bookingContent == null ? SizedBox(height: Get.height * 0.7, child:  BookingEmptyScreen (bookingId: widget.bookingId ?? "",)): Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Expanded(child: SingleChildScrollView(
              controller: bookingDetailsController.scrollController,
              child: Column(
                children: [
                  const SizedBox(height:Dimensions.paddingSizeSmall),


                  Row( mainAxisAlignment: MainAxisAlignment.center, children: [

                    const SizedBox(width:Dimensions.paddingSizeDefault),
                    Expanded(
                      child: CustomButton(
                        btnTxt: "edit_booking".tr, icon: Icons.edit,
                        onPressed: isEditBooking ? (){
                          Get.to(()=>  BookingEditScreen(isSubBooking: widget.isSubBooking,));
                        }: null,
                      ),),
                    const SizedBox(width:Dimensions.paddingSizeSmall),

                    CustomButton(
                      width: 120, btnTxt: "invoice".tr,  icon: Icons.file_present,
                      color: Colors.blue,
                      onPressed: () async {
                        Get.dialog(const CustomLoader(), barrierDismissible: false);
                        String languageCode = Get.find<LocalizationController>().locale.languageCode;
                        String uri = "${AppConstants.baseUrl}${widget.isSubBooking ? AppConstants.singleRepeatBookingInvoiceUrl : AppConstants.regularBookingInvoiceUrl}${bookingDetails?.id}/$languageCode";
                        if (kDebugMode) {
                          print("Uri : $uri");
                        }
                        await _launchUrl(Uri.parse(uri));
                        Get.back();
                      },
                    ),
                    const SizedBox(width:Dimensions.paddingSizeDefault),
                  ]),

                  const SizedBox(height:Dimensions.paddingSizeExtraSmall),

                  BookingInformationView(bookingDetails: bookingDetails!, isSubBooking: widget.isSubBooking,),

                  BookingSummeryView(bookingDetails: bookingDetails),

                  BookingDetailsProviderInfo(bookingDetails: bookingDetails),

                  BookingDetailsCustomerInfo(bookingDetails: bookingDetails),


                  bookingDetails.photoEvidenceFullPath != null &&  bookingDetails.photoEvidenceFullPath!.isNotEmpty ?
                  Padding( padding: const EdgeInsets.symmetric(horizontal: Dimensions.paddingSizeDefault),
                    child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [

                      const SizedBox(height: Dimensions.paddingSizeDefault,),
                      Text('completed_service_picture'.tr,  style: robotoMedium.copyWith(fontSize: Dimensions.fontSizeDefault, color: Theme.of(context).primaryColor),),
                      const SizedBox(height: Dimensions.paddingSizeSmall),

                      Container(
                        height: 90,
                        decoration: BoxDecoration(
                          color: Theme.of(context).primaryColor.withValues(alpha:0.05),
                          borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
                        ),
                        padding: const EdgeInsets.all(Dimensions.paddingSizeSmall),
                        child: ListView.builder(
                          controller: bookingDetailsController.completedServiceImagesScrollController,
                          scrollDirection: Axis.horizontal,
                          physics: const BouncingScrollPhysics(),
                          itemCount:  bookingDetails.photoEvidenceFullPath?.length,
                          itemBuilder: (context, index) {
                            return Hero(
                              tag: bookingDetails.photoEvidenceFullPath?[index] ?? "",
                              child: Padding(
                                padding: const EdgeInsets.symmetric(horizontal: Dimensions.paddingSizeExtraSmall),
                                child: ClipRRect(
                                  borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
                                  child: GestureDetector(
                                    onTap: (){
                                      Get.to(ImageDetailScreen(
                                        imageList: bookingDetails.photoEvidenceFullPath ?? [],
                                        index: index,
                                        appbarTitle: 'completed_service_picture'.tr,

                                      ),
                                      );
                                    },
                                    child: CustomImage(
                                      image: bookingDetails.photoEvidenceFullPath?[index]??"",
                                      height: 70, width: 120,
                                    ),
                                  ),
                                ),
                              ),
                            );
                          },
                        ),
                      ),
                    ]),
                  ):
                  const SizedBox(),

                  Get.find<SplashController>().configModel?.content?.bookingImageVerification == 1 && showDeliveryConfirmImage && bookingDetails.bookingStatus != 'completed' ? Padding(
                    padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
                    child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                      Text('completed_service_picture'.tr,  style: robotoMedium.copyWith(fontSize: Dimensions.fontSizeDefault, color: Theme.of(context).primaryColor),),
                      const SizedBox(height: Dimensions.paddingSizeSmall),

                      Container(
                        height: 90,
                        decoration: BoxDecoration(
                          color: Theme.of(context).primaryColor.withValues(alpha:0.05),
                          borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
                        ),
                        padding: const EdgeInsets.all(Dimensions.paddingSizeSmall),
                        child: ListView.builder(
                          scrollDirection: Axis.horizontal,
                          physics: const BouncingScrollPhysics(),
                          itemCount: bookingDetailsController.pickedPhotoEvidence.length+1,
                          itemBuilder: (context, index) {
                            XFile? file = index == bookingDetailsController.pickedPhotoEvidence.length ? null : bookingDetailsController.pickedPhotoEvidence[index];
                            if(index < 5 && index == bookingDetailsController.pickedPhotoEvidence.length) {
                              return InkWell(
                                onTap: () {
                                  Get.bottomSheet(CameraButtonSheet(bookingId: bookingDetails.id ?? "", isSubBooking: widget.isSubBooking,));
                                },
                                child: Container(
                                  height: 60, width: 70, alignment: Alignment.center, decoration: BoxDecoration(
                                  borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
                                  color: Theme.of(context).primaryColor.withValues(alpha:0.1),
                                ),
                                  child:  Icon(Icons.camera_alt_sharp, color: Theme.of(context).primaryColor, size: 32),
                                ),
                              );
                            }
                            return file != null ? Container(
                              margin: const EdgeInsets.only(right: Dimensions.paddingSizeSmall),
                              decoration: BoxDecoration(
                                borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
                              ),
                              child: Stack(children: [
                                ClipRRect(
                                  borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
                                  child: GetPlatform.isWeb ? Image.network(
                                    file.path, width: 120, height: 70, fit: BoxFit.cover,
                                  ) : Image.file(
                                    File(file.path), width: 120, height: 70, fit: BoxFit.cover,
                                  ),
                                ),
                              ]),
                            ) : const SizedBox();
                          },
                        ),
                      ),
                    ]),
                  ) : const SizedBox(),

                  const SizedBox(height:Dimensions.paddingSizeExtraLarge),
                ],
              ),
            ),),
            bookingDetails.bookingStatus == "accepted" ||  bookingDetails.bookingStatus == "ongoing" ?
            SafeArea(child: StatusChangeDropdownButton(bookingId: bookingDetails.id??"",bookingDetails: bookingDetails ,isSubBooking: widget.isSubBooking,)): const SizedBox(),
          ],
        );
      },
    );
  }


  Future<void> _launchUrl(Uri url) async {
    if (!await launchUrl(url)) {
      throw 'Could not launch $url';
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

class BookingEmptyScreen extends StatelessWidget {
  final String? bookingId;
  const BookingEmptyScreen({super.key, required this.bookingId});

  @override
  Widget build(BuildContext context) {
    return Center(child: Column(mainAxisAlignment: MainAxisAlignment.center,children: [
      Image.asset(Images.noResults, height: Get.height * 0.1, color: Theme.of(context).primaryColor,),
      const SizedBox(height: Dimensions.paddingSizeLarge,),
      Text("information_not_found".tr, style: robotoRegular,),
      const SizedBox(height: Dimensions.paddingSizeLarge,),

      CustomButton(
        height: 35, width: 120, radius: Dimensions.radiusExtraLarge,
        btnTxt: "go_back".tr, onPressed: () {
        //Get.find<BookingRequestController>().removeBookingItemFromList(bookingId ?? "", shouldUpdate: true , bookingStatus: "");
        Get.back();
      },)

    ],),);
  }
}









