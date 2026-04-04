

import 'package:demandium_serviceman/helper/extension_helper.dart';
import 'package:demandium_serviceman/utils/core_export.dart';
import 'package:get/get.dart';
import 'package:skeletonizer/skeletonizer.dart';

class BookingStatisticsWidget extends StatelessWidget {
  const BookingStatisticsWidget({super.key});


  @override
  Widget build(BuildContext context) {
    return GetBuilder<DashboardController>(
      builder: (dashboardController) {
        final earningData = dashboardController.bookingStatisticsModel;

        return Container(
          decoration: BoxDecoration(
            color: Theme.of(context).cardColor,
            boxShadow: context.customThemeColors.cardShadow,
          ),
          child: Skeletonizer(
            enabled: earningData == null,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Padding(
                  padding: const EdgeInsets.symmetric(
                    horizontal: Dimensions.paddingSizeDefault,
                    vertical: Dimensions.paddingSizeDefault,
                  ),
                  child: Row(
                    children: [
                      Text(
                        'booking_statistics'.tr,
                        style: robotoMedium.copyWith(
                          fontSize: Dimensions.fontSizeDefault,
                          fontWeight: FontWeight.w600,
                          color: Theme.of(context).textTheme.bodyLarge!.color!.withValues(alpha:0.8),
                        ),
                      ),

                    ],
                  ),
                ),

                Padding(
                  padding: const EdgeInsets.symmetric(
                    horizontal: Dimensions.paddingSizeDefault,
                  ),
                  child: SingleChildScrollView(
                    scrollDirection: Axis.horizontal,
                    child: Row(
                      children: [
                        _BookingCart(
                          period: 'this_week'.tr,
                          total: earningData?.thisWeek?.total ?? 0,
                          change: earningData?.thisWeek?.change ?? 0.0,
                          periodLabel: 'from_last_week'.tr,
                        ),
                        const SizedBox(width: Dimensions.paddingSizeDefault),

                        _BookingCart(
                          period: 'this_month'.tr,
                          total: earningData?.thisMonth?.total ?? 0,
                          change: earningData?.thisMonth?.change ?? 0.0,
                          periodLabel: 'from_last_month'.tr,
                        ),
                        const SizedBox(width: Dimensions.paddingSizeDefault),

                        _BookingCart(
                          period: 'this_year'.tr,
                          total: earningData?.thisYear?.total ?? 0,
                          change: earningData?.thisYear?.change ?? 0.0,
                          periodLabel: 'from_last_year'.tr,
                        ),
                      ],
                    ),
                  ),
                ),
                const SizedBox(height: Dimensions.paddingSizeDefault),
              ],
            ),
          ),
        );
      },
    );
  }
}

class _BookingCart extends StatelessWidget {
  final String period;
  final int total;
  final double change;
  final String periodLabel;

  const _BookingCart({
    required this.period,
    required this.total,
    required this.change,
    required this.periodLabel,
  });

  @override
  Widget build(BuildContext context) {
    final isPositive = change >= 0;
    final changeColor = isPositive ? Colors.green : Colors.red;
    final changeIcon = isPositive ? Icons.arrow_upward : Icons.arrow_downward;

    return Container(
      padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
      decoration: BoxDecoration(
        color: Theme.of(context).cardColor,
        borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
        border: Border.all(color: context.customThemeColors.orderStatisticBorderColor!),
        boxShadow: context.customThemeColors.cardShadow,
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            period,
            style: robotoRegular.copyWith(
              fontSize: Dimensions.fontSizeSmall,
              color: Theme.of(context).textTheme.bodyLarge!.color!.withValues(alpha: 0.6),
            ),
          ),
          const SizedBox(height: Dimensions.paddingSizeSmall),

          Text(
            '$total',
            style: robotoBold.copyWith(
              fontSize: Dimensions.fontSizeLarge,
              color: Theme.of(context).primaryColor,
            ),
          ),
          const SizedBox(height: Dimensions.paddingSizeSmall),

          Row(children: [
            Container(
              padding: const EdgeInsets.all(Dimensions.paddingSizeExtraSmall),
              decoration: BoxDecoration(
                color: changeColor.withValues(alpha: 0.1),
                shape: BoxShape.circle,
              ),
              child: Icon(
                changeIcon,
                size: Dimensions.paddingSizeSmall,
                color: changeColor,
              ),
            ),
            const SizedBox(width: Dimensions.paddingSizeExtraSmall),

            Text(
              '${isPositive ? '+' : ''}$change% $periodLabel',
              style: robotoRegular.copyWith(
                fontSize: Dimensions.fontSizeSmall,
                color: Theme.of(context)
                    .textTheme
                    .bodyLarge!
                    .color!
                    .withValues(alpha: 0.6),
              ),
            ),
          ]),
        ],
      ),
    );
  }
}