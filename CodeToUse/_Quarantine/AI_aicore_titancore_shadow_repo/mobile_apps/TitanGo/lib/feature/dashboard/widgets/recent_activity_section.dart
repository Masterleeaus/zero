import 'package:demandium_serviceman/helper/extension_helper.dart';
import 'package:get/get.dart';
import 'package:demandium_serviceman/utils/core_export.dart';

class RecentActivitySection extends StatelessWidget {
  const RecentActivitySection({super.key}) ;
  @override
  Widget build(BuildContext context) {
    return GetBuilder<DashboardController>(builder: (dashboardController){

      List<DashboardBooking> bookingList = dashboardController.bookings;
      int itemCount = 0;

      return Container(
        decoration: BoxDecoration(
          color: Theme.of(context).cardColor,
          boxShadow: context.customThemeColors.cardShadow,
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            _RecentActivityHeaderWidget(bookingList: bookingList),

            bookingList.isEmpty?
            Container(
              padding: const EdgeInsets.symmetric(vertical:Dimensions.paddingSizeSmall),
        
              child: Center(
                child: Text(
                  'your_recent_booking_will_appear_here'.tr,
                  style: robotoRegular.copyWith(
                    fontSize: Dimensions.fontSizeSmall,
        
                  ),
                ),
              ),
            ) :
            Padding(
              padding: const EdgeInsets.symmetric(horizontal:Dimensions.paddingSizeExtraSmall),
              child: ListView.builder(
                itemBuilder: (context, index) {
        
                  bool isRepeatBooking = bookingList[index].repeatBookingList !=null && bookingList[index].repeatBookingList!.isNotEmpty;
        
                  if(!isRepeatBooking){
                    itemCount ++;
                  }
                  return isRepeatBooking && itemCount < 6  ?
                  ListView.builder(
                    padding: EdgeInsets.zero,
                    shrinkWrap: true,
                    physics: const NeverScrollableScrollPhysics(),
                    itemCount: bookingList[index].repeatBookingList!.length,
                    itemBuilder: (context,secondIndex){
                      itemCount ++;
                      return itemCount < 6 ? RecentActivityItem(
                        activityData: bookingList[index],
                        repeatBooking : bookingList[index].repeatBookingList![secondIndex],
                      ) : const SizedBox();
                    },
                  ): itemCount < 6 ? Column(
                    children: [
                      RecentActivityItem(activityData: bookingList[index]),
                    ],
                  ) : const SizedBox() ;
                },
                physics: const NeverScrollableScrollPhysics(),
                shrinkWrap: true,
                itemCount: bookingList.length,
              ),
            ),
          ],
        ),
      );
    });
  }
}

class _RecentActivityHeaderWidget extends StatelessWidget {
  const _RecentActivityHeaderWidget({
    required this.bookingList,
  });

  final List<DashboardBooking> bookingList;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(
          horizontal: Dimensions.paddingSizeDefault,
          vertical: Dimensions.paddingSizeSmall),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Expanded(
            child: Text(
              "my_recent_activities".tr,
              style: robotoMedium.copyWith(
                color: Theme.of(context).textTheme.bodyLarge!.color!.withValues(alpha:0.8),
                fontWeight: FontWeight.w600,
                fontSize: Dimensions.fontSizeDefault,
              ),
            ),
          ),

         if(bookingList.isNotEmpty) GestureDetector(
            onTap: () {
              BottomNavScreen.onChangesIndex(2);
              },
            child: Text(
              "view_all".tr,
              style: robotoMedium.copyWith(
                color: Theme.of(context).primaryColor,
                decoration: TextDecoration.underline,
              ),
            ),
          ),
        ],

      ),
    );
  }
}
