import 'package:demandium_serviceman/helper/extension_helper.dart';
import 'package:get/get.dart';
import 'package:demandium_serviceman/utils/core_export.dart';


class BusinessSummerySection extends StatelessWidget {
  const  BusinessSummerySection({super.key,}) ;
  @override
  Widget build(BuildContext context) {
    return  GetBuilder<DashboardController>(builder: (controller){
      return Container(
        decoration: BoxDecoration(
          color: Theme.of(context).cardColor,
          boxShadow: context.customThemeColors.cardShadow,
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            SizedBox(height: Dimensions.paddingSizeDefault),

            Padding(
              padding: const EdgeInsets.symmetric(
                horizontal: Dimensions.paddingSizeDefault,
              ),
              child: Text(
                "bookings_summary".tr,
                style: robotoMedium.copyWith(
                  fontSize: Dimensions.fontSizeDefault,
                  fontWeight: FontWeight.w600,
                  color: Theme.of(context).textTheme.bodyLarge!.color!.withValues(alpha:0.8),
                ),
              ),
            ),
            Container(
              padding:  const EdgeInsets.symmetric(
                horizontal:Dimensions.paddingSizeDefault,
                vertical: Dimensions.paddingSizeSmall,
              ),
              width: MediaQuery.of(context).size.width,
              child: Column(
                children: [
                  Row(
                    spacing: Dimensions.paddingSizeSmall,
                    children:   [
                      BusinessSummaryItem(
                        height: 100,
                        cardColor: context.customThemeColors.assignedBusinessSummaryCardColor,
                        curveColor: context.customThemeColors.assignedBusinessSummaryCurveColor,
                        amount: controller.cards.pendingBookings ?? 0,
                        title: "total_assigned_booking".tr,
                        iconData: Images.earning,
                      ),

                      BusinessSummaryItem(
                        height: 100,
                        curveColor: context.customThemeColors.ongoingBusinessSummaryCurveColor,
                        cardColor: context.customThemeColors.ongoingBusinessSummaryCardColor,
                        amount: controller.cards.ongoingBookings ?? 0,
                        title: "ongoing_booking".tr,
                        iconData: Images.service,
                      ),
                    ],
                  ),
                  const SizedBox(height: Dimensions.paddingSizeSmall,),

                  Row(
                    spacing: Dimensions.paddingSizeSmall,
                    children:   [
                      BusinessSummaryItem(
                        height: 100,
                        curveColor: Color(0xff3BB104),
                        cardColor: Color(0xff36A900),
                        amount: controller.cards.completedBookings ?? 0,
                        title: "total_completed_booking".tr,
                        iconData: Images.serviceMan,
                      ),

                      BusinessSummaryItem(
                        height: 100,
                        curveColor: context.customThemeColors.canceledBusinessSummaryCurveColor,
                        cardColor: context.customThemeColors.canceledBusinessSummaryCardColor,
                        amount: controller.cards.canceledBookings ?? 0,
                        title: "total_canceled_booking".tr,
                        iconData: Images.booking,
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ],
        ),
      );
      }
    );
  }
}
