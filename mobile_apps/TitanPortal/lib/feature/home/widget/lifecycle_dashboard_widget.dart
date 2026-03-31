import 'package:demandium/utils/core_export.dart';
import 'package:get/get.dart';

/// Phase 3 — Lifecycle mission control dashboard widget
/// Displayed at the top of the Home screen for logged-in customers.
/// Shows upcoming service summary and quick action buttons.
class LifecycleDashboardWidget extends StatelessWidget {
  const LifecycleDashboardWidget({super.key});

  @override
  Widget build(BuildContext context) {
    bool isLoggedIn = Get.find<AuthController>().isLoggedIn();
    if (!isLoggedIn) return const SliverToBoxAdapter(child: SizedBox.shrink());

    return SliverToBoxAdapter(
      child: Center(
        child: SizedBox(
          width: Dimensions.webMaxWidth,
          child: Padding(
            padding: const EdgeInsets.symmetric(
              horizontal: Dimensions.paddingSizeDefault,
              vertical: Dimensions.paddingSizeSmall,
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                _QuickActionsRow(),
                const SizedBox(height: Dimensions.paddingSizeSmall),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

class _QuickActionsRow extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          'quick_actions'.tr,
          style: robotoMedium.copyWith(
            fontSize: Dimensions.fontSizeDefault,
            color: Theme.of(context).textTheme.titleMedium?.color,
          ),
        ),
        const SizedBox(height: Dimensions.paddingSizeSmall),
        SingleChildScrollView(
          scrollDirection: Axis.horizontal,
          child: Row(
            children: [
              _QuickActionChip(
                icon: Icons.cleaning_services_rounded,
                label: 'request_service'.tr,
                onTap: () => Get.toNamed(RouteHelper.getSearchResultRoute()),
              ),
              const SizedBox(width: Dimensions.paddingSizeSmall),
              _QuickActionChip(
                icon: Icons.calendar_today_rounded,
                label: 'my_bookings'.tr,
                onTap: () {
                  if (Get.find<AuthController>().isLoggedIn()) {
                    Get.toNamed(RouteHelper.getBookingScreenRoute(true));
                  }
                },
              ),
              const SizedBox(width: Dimensions.paddingSizeSmall),
              _QuickActionChip(
                icon: Icons.payment_rounded,
                label: 'pay_invoice'.tr,
                onTap: () => Get.toNamed(RouteHelper.getMyWalletScreen()),
              ),
              const SizedBox(width: Dimensions.paddingSizeSmall),
              _QuickActionChip(
                icon: Icons.chat_bubble_outline_rounded,
                label: 'message_support'.tr,
                onTap: () => Get.toNamed(RouteHelper.getInboxScreenRoute()),
              ),
              const SizedBox(width: Dimensions.paddingSizeSmall),
              _QuickActionChip(
                icon: Icons.flag_outlined,
                label: 'report_issue'.tr,
                onTap: () => Get.toNamed(RouteHelper.getSupportRoute()),
              ),
              const SizedBox(width: Dimensions.paddingSizeSmall),
              _QuickActionChip(
                icon: Icons.auto_awesome_rounded,
                label: 'ask_titan'.tr,
                onTap: () => Get.toNamed(RouteHelper.getSupportRoute()),
                isPrimary: true,
              ),
            ],
          ),
        ),
      ],
    );
  }
}

class _QuickActionChip extends StatelessWidget {
  final IconData icon;
  final String label;
  final VoidCallback onTap;
  final bool isPrimary;

  const _QuickActionChip({
    required this.icon,
    required this.label,
    required this.onTap,
    this.isPrimary = false,
  });

  @override
  Widget build(BuildContext context) {
    final color = isPrimary
        ? Theme.of(context).colorScheme.primary
        : Theme.of(context).colorScheme.secondary;

    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(20),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
        decoration: BoxDecoration(
          color: color.withValues(alpha: 0.12),
          borderRadius: BorderRadius.circular(20),
          border: Border.all(color: color.withValues(alpha: 0.4), width: 1),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(icon, size: 16, color: color),
            const SizedBox(width: 6),
            Text(
              label,
              style: robotoMedium.copyWith(
                fontSize: Dimensions.fontSizeSmall,
                color: color,
              ),
            ),
          ],
        ),
      ),
    );
  }
}
