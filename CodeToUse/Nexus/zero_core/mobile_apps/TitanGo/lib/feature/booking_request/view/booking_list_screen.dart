import 'package:demandium_serviceman/common/widgets/no_data_screen.dart';
import 'package:get/get.dart';
import 'package:demandium_serviceman/utils/core_export.dart';


class BookingListScreen extends StatefulWidget {
  const BookingListScreen({super.key}) ;

  @override
  State<BookingListScreen> createState() => _BookingListScreenState();
}

class _BookingListScreenState extends State<BookingListScreen> {
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Theme.of(context).colorScheme.surface,
        appBar:  MainAppBar(title: 'booking_requests'.tr,color: Theme.of(context).primaryColor),
      body: GetBuilder<BookingRequestController>(
        builder: (bookingRequestController){

          bool isEmpty = bookingRequestController.bookingList.isEmpty;
          int subBookingCount = 0;

          if(bookingRequestController.bookingList.isNotEmpty){
            for(var booking in bookingRequestController.bookingList){
              if(booking.repeatBookingList !=null && booking.repeatBookingList!.isNotEmpty){
                for(var subBooking in booking.repeatBookingList!){
                  if(bookingRequestController.bookingStatusState.name.toLowerCase() == subBooking.bookingStatus){
                    subBookingCount ++;
                    break;
                  }
                }
              }{
                subBookingCount ++;
              }
            }
          }

          if(!isEmpty){
            if(subBookingCount == 0){
              isEmpty = true;
            }
          }


          List<BookingRequestModel> bookingList = bookingRequestController.bookingList;
          return RefreshIndicator(
            backgroundColor: Theme.of(context).colorScheme.surface,
            color: Theme.of(context).textTheme.bodyLarge!.color!.withValues(alpha:0.6),
            onRefresh: () async{
              Get.find<BookingRequestController>().getBookingList(bookingRequestController.bookingStatusState.name.toLowerCase(),1);
            },
            child: CustomScrollView(
              controller:bookingRequestController.scrollController,
              physics: const ClampingScrollPhysics(
                parent: AlwaysScrollableScrollPhysics()
              ),
              slivers: [
                SliverPersistentHeader(delegate: BookingListMenu(),pinned: true,floating: false,),

                !bookingRequestController.isFirst? isEmpty ?
                SliverToBoxAdapter(child: SizedBox(
                  height: Get.height * 0.75,
                  child: NoDataScreen(
                    type: NoDataType.booking,
                    text: '${'no'.tr} ${bookingRequestController.bookingStatusState.name.toLowerCase()=='all'?'':
                    bookingRequestController.bookingStatusState.name.toLowerCase().tr.toLowerCase()} ${"request_right_now".tr}',

                  ),
                )) : SliverToBoxAdapter(
                  child: Column( children: [
                    bookingList.length > 1 ? ListView.builder(
                      shrinkWrap: true,
                      itemCount: bookingList.length,
                      physics: const NeverScrollableScrollPhysics(),
                      itemBuilder: (con, index){
                        return bookingList[index].repeatBookingList !=null && bookingList[index].repeatBookingList!.isNotEmpty  ?
                        ListView.builder(
                          shrinkWrap: true, physics: const NeverScrollableScrollPhysics(),
                          itemCount: bookingList[index].repeatBookingList!.length,
                          itemBuilder: (context,secondIndex){
                            return bookingRequestController.bookingStatusState.name.toLowerCase() ==  bookingList[index].repeatBookingList![secondIndex].bookingStatus ? BookingRequestItem(
                              bookingRequestModel: bookingList[index],
                              repeatBooking : bookingList[index].repeatBookingList![secondIndex],
                            ) : const SizedBox();
                          },
                        ):
                        BookingRequestItem(bookingRequestModel: bookingList[index]);
                      },
                    ) : SizedBox(
                      height: Get.height * 0.7,
                      child: Center(
                        child: NoDataScreen(text: 'no_booking_found'.tr),
                      ),
                    ),
                    bookingRequestController.isLoading ? CircularProgressIndicator(color: Theme.of(context).primaryColor,):const SizedBox()
                  ]),
                ) :const SliverToBoxAdapter(child: BookingRequestItemShimmer()),
              ],
            ),
          );
        },
      )
    );
  }
}




