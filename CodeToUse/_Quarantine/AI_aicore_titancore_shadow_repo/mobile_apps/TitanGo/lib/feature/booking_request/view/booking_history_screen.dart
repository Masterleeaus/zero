import 'package:demandium_serviceman/common/widgets/no_data_screen.dart';
import 'package:get/get.dart';
import 'package:demandium_serviceman/utils/core_export.dart';


class BookingHistoryScreen extends StatefulWidget {
  const BookingHistoryScreen({super.key}) ;
  @override
  State<BookingHistoryScreen> createState() => _BookingHistoryScreenState();
}

class _BookingHistoryScreenState extends State<BookingHistoryScreen> {

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Theme.of(context).colorScheme.surface,
      appBar:  MainAppBar(title: 'booking_history'.tr,color: Theme.of(context).primaryColor),
      body: GetBuilder<BookingRequestController>(
        builder: (bookingRequestController){

          List<BookingRequestModel> bookingList = bookingRequestController.bookingHistoryList;
          String selectedTab = bookingRequestController.bookingHistoryStatus[bookingRequestController.bookingHistorySelectedIndex].toLowerCase();

          bool isEmpty = bookingList.isEmpty;
          int subBookingCount = 0;

          if(bookingList.isNotEmpty && selectedTab != "all"){
            for(var booking in bookingList){
              if(booking.repeatBookingList !=null && booking.repeatBookingList!.isNotEmpty ){
                for(var subBooking in booking.repeatBookingList!){
                  if( selectedTab == subBooking.bookingStatus){
                    subBookingCount ++;
                    break;
                  }
                }
              }{
                subBookingCount ++;
              }
            }
          }
          if(!isEmpty  && selectedTab != "all"){
            if(subBookingCount == 0){
              isEmpty = true;
            }
          }

          return RefreshIndicator(
            backgroundColor: Theme.of(context).colorScheme.surface,
            color: Theme.of(context).textTheme.bodyLarge!.color!.withValues(alpha:0.6),
            onRefresh: () async{
              bookingRequestController.getBookingHistory(
                  bookingRequestController.bookingHistoryStatus[bookingRequestController.bookingHistorySelectedIndex],1
              );
            },
            child: CustomScrollView(
              controller: bookingRequestController.bookingHistoryScrollController,
              physics: const AlwaysScrollableScrollPhysics(
                parent: ClampingScrollPhysics()
              ),
              slivers: [
                SliverPersistentHeader(delegate: BookingHistorySectionMenu(),pinned: true,floating: false,),
                bookingRequestController.isFirst ?
                const SliverToBoxAdapter(child: BookingRequestItemShimmer()) :
                isEmpty ?
                SliverToBoxAdapter(
                  child: SizedBox(
                    height: Get.height * 0.75,
                    child: NoDataScreen(
                      type: NoDataType.booking,
                      text: '${'no'.tr} ${bookingRequestController.bookingHistoryStatus[bookingRequestController.bookingHistorySelectedIndex]=='All'?'booking'.tr.toLowerCase():
                      bookingRequestController.bookingHistoryStatus[bookingRequestController.bookingHistorySelectedIndex].toLowerCase().tr.toLowerCase()} ${"request_right_now".tr}',
                    ),
                  ),
                ) :
                SliverToBoxAdapter(
                  child: Column( children: [
                     ListView.builder(
                       shrinkWrap: true,
                       itemCount: bookingList.length,
                       physics: const NeverScrollableScrollPhysics(),
                       itemBuilder: (con, index){


                         return bookingList[index].repeatBookingList !=null && bookingList[index].repeatBookingList!.isNotEmpty ?
                         ListView.builder(
                           shrinkWrap: true, physics: const NeverScrollableScrollPhysics(),
                           itemCount: bookingList[index].repeatBookingList!.length,
                           itemBuilder: (context,secondIndex){

                             String? bookingStatus = bookingList[index].repeatBookingList![secondIndex].bookingStatus;

                             return (selectedTab ==  bookingStatus) || selectedTab == "all" ? BookingRequestItem(
                               bookingRequestModel: bookingList[index],
                               repeatBooking : bookingList[index].repeatBookingList![secondIndex],
                             ) : const SizedBox();
                           },
                         ):
                         BookingRequestItem(bookingRequestModel: bookingList[index]);
                       },
                     ),
                      bookingRequestController.isLoading ? CircularProgressIndicator(color: Theme.of(context).primaryColor,):const SizedBox()
                  ]),
                ),
              ],
            ),  ///////
          );
        },
      ),
    );
  }
}


