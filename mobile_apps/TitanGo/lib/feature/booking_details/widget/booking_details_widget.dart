import 'package:demandium_serviceman/feature/booking_details/widget/job_detail/checklist_launcher_widget.dart';
import 'package:demandium_serviceman/feature/booking_details/widget/job_detail/escalation_widget.dart';
import 'package:demandium_serviceman/feature/booking_details/widget/job_detail/job_arrival_widget.dart';
import 'package:demandium_serviceman/feature/booking_details/widget/job_detail/proof_bundle_widget.dart';
import 'package:demandium_serviceman/feature/booking_details/widget/job_detail/site_notes_widget.dart';
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

                  // Arrival / Access preflight – shown for active jobs
                  if (bookingStatus == "accepted" || bookingStatus == "ongoing")
                    const JobArrivalWidget(),

                  // Site Notes / Property Memory block
                  const SiteNotesWidget(),

                  // Checklist launcher with execution state
                  ChecklistLauncherWidget(jobId: bookingDetails.id ?? ''),

                  // Supervisor escalation quick-actions – shown for active jobs
                  if (bookingStatus == "accepted" || bookingStatus == "ongoing")
                    const EscalationWidget(),

                  BookingSummeryView(bookingDetails: bookingDetails),

                  BookingDetailsProviderInfo(bookingDetails: bookingDetails),

                  BookingDetailsCustomerInfo(bookingDetails: bookingDetails),

                  // Proof bundle (before/after/issue/extra) replaces flat proof list
                  if (bookingDetails.photoEvidenceFullPath != null &&
                      bookingDetails.photoEvidenceFullPath!.isNotEmpty)
                    ProofBundleWidget(
                      photoEvidenceFullPath:
                          bookingDetails.photoEvidenceFullPath!,
                      showUploadButton: false,
                      bookingId: bookingDetails.id ?? '',
                      isSubBooking: widget.isSubBooking,
                    ),

                  if (Get.find<SplashController>()
                              .configModel
                              ?.content
                              ?.bookingImageVerification ==
                          1 &&
                      showDeliveryConfirmImage &&
                      bookingDetails.bookingStatus != 'completed')
                    ProofBundleWidget(
                      photoEvidenceFullPath: bookingDetailsController
                          .pickedPhotoEvidence
                          .map((x) => x.path)
                          .toList(),
                      showUploadButton: true,
                      bookingId: bookingDetails.id ?? '',
                      isSubBooking: widget.isSubBooking,
                    ),

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









