import 'package:demandium/utils/core_export.dart';
import 'package:get/get.dart';

/// Pass 2 — Lifecycle mission control dashboard widget.
/// Displayed at the top of the Home screen for logged-in customers.
/// Shows lifecycle status cards (upcoming service, payment, proof, support)
/// and extended quick action chips.
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
                const _LifecycleStatusRow(),
                const SizedBox(height: Dimensions.paddingSizeDefault),
                const _QuickActionsRow(),
                const SizedBox(height: Dimensions.paddingSizeSmall),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

// ---------------------------------------------------------------------------
// Lifecycle status summary cards row
// ---------------------------------------------------------------------------

class _LifecycleStatusRow extends StatelessWidget {
  const _LifecycleStatusRow();

  @override
  Widget build(BuildContext context) {
    return GetBuilder<ServiceBookingController>(builder: (ctrl) {
      final BookingModel? nextService = _resolveNextService(ctrl);
      return SingleChildScrollView(
        scrollDirection: Axis.horizontal,
        child: Row(
          children: [
            _LifecycleCard(
              icon: Icons.cleaning_services_rounded,
              title: 'upcoming_service_card'.tr,
              subtitle: nextService != null
                  ? nextService.readableId ?? nextService.id ?? ''
                  : 'no_upcoming_service'.tr,
              isEmpty: nextService == null,
              onTap: () => Get.toNamed(RouteHelper.getBookingScreenRoute(true)),
            ),
            const SizedBox(width: Dimensions.paddingSizeSmall),
            _LifecycleCard(
              icon: Icons.receipt_long_outlined,
              title: 'outstanding_payment_label'.tr,
              subtitle: 'no_outstanding_payment'.tr,
              isEmpty: true,
              onTap: () => Get.toNamed(RouteHelper.getMyWalletScreen()),
            ),
            const SizedBox(width: Dimensions.paddingSizeSmall),
            _LifecycleCard(
              icon: Icons.verified_outlined,
              title: 'recent_proof_label'.tr,
              subtitle: 'no_recent_proof'.tr,
              isEmpty: true,
              onTap: () => Get.toNamed(RouteHelper.getBookingScreenRoute(true)),
            ),
            const SizedBox(width: Dimensions.paddingSizeSmall),
            _LifecycleCard(
              icon: Icons.support_agent_outlined,
              title: 'active_support_label'.tr,
              subtitle: 'no_active_support'.tr,
              isEmpty: true,
              onTap: () => Get.toNamed(RouteHelper.getInboxScreenRoute()),
            ),
          ],
        ),
      );
    });
  }

  BookingModel? _resolveNextService(ServiceBookingController ctrl) {
    if (ctrl.bookingList == null || ctrl.bookingList!.isEmpty) return null;
    try {
      return ctrl.bookingList!.firstWhere(
        (b) => b.bookingStatus == 'accepted' || b.bookingStatus == 'ongoing',
        orElse: () => ctrl.bookingList!.first,
      );
    } catch (_) {
      return null;
    }
  }
}

class _LifecycleCard extends StatelessWidget {
  final IconData icon;
  final String title;
  final String subtitle;
  final bool isEmpty;
  final VoidCallback onTap;

  const _LifecycleCard({
    required this.icon,
    required this.title,
    required this.subtitle,
    required this.isEmpty,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    final primary = Theme.of(context).colorScheme.primary;
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
      child: Container(
        width: 160,
        padding: const EdgeInsets.all(Dimensions.paddingSizeSmall),
        decoration: BoxDecoration(
          color: isEmpty
              ? Theme.of(context).cardColor
              : primary.withValues(alpha: 0.07),
          borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
          border: Border.all(
            color: isEmpty
                ? Theme.of(context).dividerColor
                : primary.withValues(alpha: 0.3),
            width: 1,
          ),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(icon, size: 18,
              color: isEmpty ? Theme.of(context).hintColor : primary,
            ),
            const SizedBox(height: 6),
            Text(
              title,
              style: robotoMedium.copyWith(
                fontSize: Dimensions.fontSizeExtraSmall,
                color: isEmpty ? Theme.of(context).hintColor : primary,
              ),
            ),
            const SizedBox(height: 4),
            Text(
              subtitle,
              style: robotoRegular.copyWith(
                fontSize: Dimensions.fontSizeExtraSmall,
                color: Theme.of(context).textTheme.bodySmall?.color,
              ),
              maxLines: 2,
              overflow: TextOverflow.ellipsis,
            ),
          ],
        ),
      ),
    );
  }
}

// ---------------------------------------------------------------------------
// Quick actions chips row
// ---------------------------------------------------------------------------

class _QuickActionsRow extends StatelessWidget {
  const _QuickActionsRow();

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
                label: 'view_services'.tr,
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
                icon: Icons.home_work_outlined,
                label: 'manage_properties'.tr,
                onTap: () => Get.toNamed(RouteHelper.getAddressRoute('menu')),
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
