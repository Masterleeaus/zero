import 'package:demandium_provider/helper/extension_helper.dart';
import 'package:demandium_provider/util/core_export.dart';
import 'package:get/get.dart';

/// Quick Actions section for Mission Control dashboard.
///
/// Provides one-tap shortcuts to core supervisor workflows:
/// schedule, issues, team status, quality review, messaging, and AI assistant.
class QuickActionsSection extends StatelessWidget {
  const QuickActionsSection({super.key});

  @override
  Widget build(BuildContext context) {
    final List<_QuickAction> actions = [
      _QuickAction(
        icon: Icons.calendar_today_rounded,
        label: 'view_schedule'.tr,
        onTap: () => Get.offAllNamed(RouteHelper.getInitialRoute(pageIndex: 1)),
      ),
      _QuickAction(
        icon: Icons.warning_amber_rounded,
        label: 'open_issues'.tr,
        onTap: () => Get.toNamed(RouteHelper.getReportingPageRoute('menu')),
      ),
      _QuickAction(
        icon: Icons.people_rounded,
        label: 'team_status'.tr,
        onTap: () => Get.offAllNamed(RouteHelper.getInitialRoute(pageIndex: 2)),
      ),
      _QuickAction(
        icon: Icons.star_rounded,
        label: 'quality_review'.tr,
        onTap: () => Get.toNamed(RouteHelper.getReportingPageRoute('quality')),
      ),
      _QuickAction(
        icon: Icons.chat_bubble_rounded,
        label: 'message_team'.tr,
        onTap: () => Get.toNamed(RouteHelper.getInboxScreenRoute()),
      ),
      _QuickAction(
        icon: Icons.psychology_rounded,
        label: 'ask_titan'.tr,
        onTap: () => Get.toNamed(RouteHelper.getHelpAndSupportScreen()),
      ),
    ];

    return Container(
      margin: const EdgeInsets.symmetric(
        horizontal: Dimensions.paddingSizeDefault,
        vertical: Dimensions.paddingSizeSmall,
      ),
      padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
      decoration: BoxDecoration(
        color: Theme.of(context).cardColor,
        borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
        boxShadow: context.customThemeColors.cardShadow,
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'quick_actions'.tr,
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
          const SizedBox(height: Dimensions.paddingSizeDefault),
          GridView.builder(
            physics: const NeverScrollableScrollPhysics(),
            shrinkWrap: true,
            gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
              crossAxisCount: ResponsiveHelper.isDesktop(context) ? 6 : 3,
              mainAxisExtent: 85,
              crossAxisSpacing: Dimensions.paddingSizeSmall,
              mainAxisSpacing: Dimensions.paddingSizeSmall,
            ),
            itemCount: actions.length,
            itemBuilder: (context, index) {
              return _QuickActionButton(action: actions[index]);
            },
          ),
        ],
      ),
    );
  }
}

class _QuickAction {
  final IconData icon;
  final String label;
  final VoidCallback onTap;

  const _QuickAction({
    required this.icon,
    required this.label,
    required this.onTap,
  });
}

class _QuickActionButton extends StatelessWidget {
  final _QuickAction action;

  const _QuickActionButton({required this.action});

  @override
  Widget build(BuildContext context) {
    return InkWell(
      borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
      onTap: action.onTap,
      child: Container(
        decoration: BoxDecoration(
          color: Theme.of(context).primaryColor.withValues(alpha: 0.06),
          borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
          border: Border.all(
            color: Theme.of(context).primaryColor.withValues(alpha: 0.12),
          ),
        ),
        padding: const EdgeInsets.symmetric(
          horizontal: Dimensions.paddingSizeExtraSmall,
          vertical: Dimensions.paddingSizeSmall,
        ),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              action.icon,
              color: Theme.of(context).primaryColor,
              size: 26,
            ),
            const SizedBox(height: Dimensions.paddingSizeExtraSmall),
            Text(
              action.label,
              style: robotoMedium.copyWith(
                fontSize: Dimensions.fontSizeExtraSmall,
                color: Theme.of(context)
                    .textTheme
                    .bodyLarge!
                    .color!
                    .withValues(alpha: 0.75),
              ),
              textAlign: TextAlign.center,
              maxLines: 2,
              overflow: TextOverflow.ellipsis,
            ),
          ],
        ),
      ),
    );
  }
}
