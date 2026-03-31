import 'package:demandium_serviceman/feature/booking_details/widget/booking_details_widget.dart';
import 'package:get/get.dart';
import 'package:demandium_serviceman/utils/core_export.dart';


class BookingDetailsScreen extends StatefulWidget{
  final String bookingId;
  final String? fromPage;
  final bool isSubBooking ;
  const BookingDetailsScreen({super.key,required this.bookingId, this.fromPage, required this.isSubBooking,}) ;

  @override
  State<BookingDetailsScreen> createState() => _BookingDetailsScreenState();
}
class _BookingDetailsScreenState extends State<BookingDetailsScreen> with SingleTickerProviderStateMixin {

  TabController? controller;

  @override
  void initState() {
    super.initState();
    var bookingDetailsController = Get.find<BookingDetailsController>();
    controller = TabController(vsync: this, length: 2);
    bookingDetailsController.resetBookingDetailsValue();
    bookingDetailsController.getBookingDetails(bookingID :widget.bookingId, isSubBooking: widget.isSubBooking);
  }

  @override
  Widget build(BuildContext context) {
    return GetBuilder<BookingDetailsController>(
      builder: (bookingDetailsController){
        return CustomPopScopeWidget(
          child: Scaffold(
            backgroundColor: Theme.of(context).colorScheme.surface,
            appBar: CustomAppBar(
              title: 'booking_details'.tr,
              onBackPressed: (){
                if(widget.fromPage == 'fromNotification'){
                  Get.offAllNamed(RouteHelper.getInitialRoute());
                }else{
                  Get.back();
                }
              },
            ),
            body: Column(children: [
              Container(
                height: 45, width: Get.width,
                color: Theme.of(context).colorScheme.surface,
                padding: const EdgeInsets.symmetric(horizontal: Dimensions.paddingSizeDefault),
                child: TabBar(
                  unselectedLabelColor:Theme.of(context).textTheme.bodyLarge?.color?.withValues(alpha:0.5),
                  indicatorColor: Theme.of(context).primaryColor,
                  controller: controller,
                  labelColor: Theme.of(context).primaryColorLight,
                  labelStyle:  robotoMedium.copyWith(fontSize: Dimensions.fontSizeLarge),
                  labelPadding: EdgeInsets.zero,
                  onTap: (int? index) {
                    switch (index) {
                      case 0:
                        bookingDetailsController.updateServicePageCurrentState(
                            BookingDetailsTabControllerState.bookingDetails
                        );
                        break;
                      case 1:
                        bookingDetailsController.updateServicePageCurrentState(
                            BookingDetailsTabControllerState.status
                        );
                        break;
                    }
                  },
                  tabs: [
                    SizedBox(width: MediaQuery.of(context).size.width * 0.5,child: Tab(text: 'booking_details'.tr)),
                    SizedBox(width: MediaQuery.of(context).size.width * 0.5, child: Tab(text: 'status'.tr)),
                  ],
                ),
              ),
              Expanded(
                child: TabBarView(controller: controller, children:  [
                  BookingDetailsWidget(isSubBooking: widget.isSubBooking,),
                  const BookingStatus(),
                ]),
              ),
            ]),

            floatingActionButton:  bookingDetailsController.isShowChattingButton(bookingDetailsController.bookingDetails?.bookingContent?.bookingDetailsContent) ? Container(
              height: 50, width: 50, margin: const EdgeInsets.only(bottom: 50),
              child: FloatingActionButton(
                elevation: 0.0, backgroundColor: Theme.of(context).primaryColor,
                onPressed: ()=> Get.bottomSheet( const CreateChannelDialog()),
                child: Icon(Icons.message_rounded,color: light.cardColor,),
              ),
            ) : null,

          ),
        );
      },
    );
  }
}

