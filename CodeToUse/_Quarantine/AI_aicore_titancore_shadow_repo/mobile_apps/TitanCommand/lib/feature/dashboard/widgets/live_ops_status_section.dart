import 'package:demandium_provider/helper/extension_helper.dart';
import 'package:demandium_provider/util/core_export.dart';
import 'package:get/get.dart';

/// Live Field Operations Status section for Mission Control dashboard.
///
/// Displays real-time operational visibility for:
/// - Active on-site cleaners
/// - Late check-ins
/// - Missed starts
/// - Offline team members
/// - Escalations requiring review
///
/// Currently shows structured placeholder states where live backend
/// data is not yet available. Hooks are in place for future live wiring.
class LiveOpsStatusSection extends StatelessWidget {
  const LiveOpsStatusSection({super.key});

  @override
  Widget build(BuildContext context) {
    return GetBuilder<DashboardController>(
      builder: (dashboardController) {
        return Container(
          margin: const EdgeInsets.symmetric(
            horizontal: Dimensions.paddingSizeDefault,
            vertical: Dimensions.paddingSizeSmall,
          ),
          decoration: BoxDecoration(
            color: Theme.of(context).cardColor,
            borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
            boxShadow: context.customThemeColors.cardShadow,
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Padding(
                padding: const EdgeInsets.fromLTRB(
                  Dimensions.paddingSizeDefault,
                  Dimensions.paddingSizeDefault,
                  Dimensions.paddingSizeDefault,
                  Dimensions.paddingSizeSmall,
                ),
                child: Row(
                  children: [
                    Container(
                      width: 8,
                      height: 8,
                      decoration: const BoxDecoration(
                        color: Colors.green,
                        shape: BoxShape.circle,
                      ),
                    ),
                    const SizedBox(width: Dimensions.paddingSizeExtraSmall),
                    Text(
                      'field_status'.tr,
                      style: robotoMedium.copyWith(
                        fontSize: Dimensions.fontSizeDefault,
                        fontWeight: FontWeight.w600,
                        color: Theme.of(context)
                            .textTheme
                            .bodyLarge!
                            .color!
                            .withValues(alpha: 0.8),
                      ),
                    ),
                    const Spacer(),
                    Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: Dimensions.paddingSizeSmall,
                        vertical: 3,
                      ),
                      decoration: BoxDecoration(
                        color: Theme.of(context).primaryColor.withValues(alpha: 0.08),
                        borderRadius: BorderRadius.circular(20),
                      ),
                      child: Text(
                        'live'.tr,
                        style: robotoMedium.copyWith(
                          fontSize: Dimensions.fontSizeExtraSmall,
                          color: Theme.of(context).primaryColor,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
              const Divider(height: 1),
              _LiveOpsRow(
                icon: Icons.location_on_rounded,
                iconColor: Colors.green,
                label: 'active_on_site'.tr,
                value: dashboardController.dashboardTopCards != null
                    ? (dashboardController.dashboardTopCards!.totalServiceMan ?? 0).toString()
                    : '—',
                onTap: () =>
                    Get.offAllNamed(RouteHelper.getInitialRoute(pageIndex: 2)),
              ),
              _LiveOpsRow(
                icon: Icons.schedule_rounded,
                iconColor: Colors.orange,
                label: 'late_check_ins'.tr,
                value: '—',
                isPlaceholder: true,
                onTap: () =>
                    Get.offAllNamed(RouteHelper.getInitialRoute(pageIndex: 2)),
              ),
              _LiveOpsRow(
                icon: Icons.alarm_off_rounded,
                iconColor: Colors.red,
                label: 'missed_starts'.tr,
                value: '—',
                isPlaceholder: true,
                onTap: () =>
                    Get.toNamed(RouteHelper.getReportingPageRoute('menu')),
              ),
              _LiveOpsRow(
                icon: Icons.wifi_off_rounded,
                iconColor: Colors.grey,
                label: 'offline_team_members'.tr,
                value: '—',
                isPlaceholder: true,
                onTap: () =>
                    Get.offAllNamed(RouteHelper.getInitialRoute(pageIndex: 2)),
              ),
              _LiveOpsRow(
                icon: Icons.flag_rounded,
                iconColor: Colors.deepOrange,
                label: 'escalations_pending'.tr,
                value: '—',
                isPlaceholder: true,
                isLast: true,
                onTap: () =>
                    Get.toNamed(RouteHelper.getReportingPageRoute('menu')),
              ),
            ],
          ),
        );
      },
    );
  }
}

class _LiveOpsRow extends StatelessWidget {
  final IconData icon;
  final Color iconColor;
  final String label;
  final String value;
  final bool isPlaceholder;
  final bool isLast;
  final VoidCallback onTap;

  const _LiveOpsRow({
    required this.icon,
    required this.iconColor,
    required this.label,
    required this.value,
    this.isPlaceholder = false,
    this.isLast = false,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(
          horizontal: Dimensions.paddingSizeDefault,
          vertical: Dimensions.paddingSizeSmall + 2,
        ),
        decoration: BoxDecoration(
          border: isLast
              ? null
              : Border(
                  bottom: BorderSide(
                    color: Theme.of(context).hintColor.withValues(alpha: 0.08),
                  ),
                ),
        ),
        child: Row(
          children: [
            Container(
              width: 34,
              height: 34,
              decoration: BoxDecoration(
                color: iconColor.withValues(alpha: 0.1),
                borderRadius: BorderRadius.circular(8),
              ),
              child: Icon(icon, color: iconColor, size: 18),
            ),
            const SizedBox(width: Dimensions.paddingSizeSmall),
            Expanded(
              child: Text(
                label,
                style: robotoRegular.copyWith(
                  fontSize: Dimensions.fontSizeDefault,
                  color: Theme.of(context)
                      .textTheme
                      .bodyLarge!
                      .color!
                      .withValues(alpha: 0.75),
                ),
              ),
            ),
            isPlaceholder
                ? Text(
                    'coming_soon'.tr,
                    style: robotoRegular.copyWith(
                      fontSize: Dimensions.fontSizeExtraSmall,
                      color: Theme.of(context).hintColor,
                      fontStyle: FontStyle.italic,
                    ),
                  )
                : Text(
                    value,
                    style: robotoBold.copyWith(
                      fontSize: Dimensions.fontSizeLarge,
                      color: Theme.of(context).primaryColorLight,
                    ),
                  ),
            const SizedBox(width: Dimensions.paddingSizeExtraSmall),
            Icon(
              Icons.chevron_right_rounded,
              size: 18,
              color: Theme.of(context).hintColor.withValues(alpha: 0.5),
            ),
          ],
        ),
      ),
    );
  }
}
