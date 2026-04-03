import 'package:demandium_serviceman/utils/core_export.dart';
import 'package:get/get.dart';

/// Supervisor escalation quick-action bar shown inside Job Details.
/// Provides one-tap escalation actions for common execution friction points.
/// Actions submit locally for now; hook to a notifications/escalation backend when available.
/// TODO: Wire to /api/v1/provider/escalation or push notification when backend ready.
class EscalationWidget extends StatelessWidget {
  const EscalationWidget({super.key});

  static const List<_EscalationAction> _actions = [
    _EscalationAction(
      icon: Icons.help_outline_rounded,
      labelKey: 'escalate_need_help',
      color: Colors.blue,
    ),
    _EscalationAction(
      icon: Icons.schedule_rounded,
      labelKey: 'escalate_running_late',
      color: Colors.orange,
    ),
    _EscalationAction(
      icon: Icons.phone_in_talk_rounded,
      labelKey: 'escalate_supervisor_call',
      color: Colors.purple,
    ),
    _EscalationAction(
      icon: Icons.lock_person_outlined,
      labelKey: 'escalate_access_problem',
      color: Colors.red,
    ),
  ];

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.symmetric(
        horizontal: Dimensions.paddingSizeDefault,
        vertical: Dimensions.paddingSizeSmall,
      ),
      padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
      decoration: BoxDecoration(
        color: Theme.of(context).cardColor,
        borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
        border: Border.all(
          color: Theme.of(context)
              .textTheme
              .bodyLarge!
              .color!
              .withValues(alpha: 0.1),
        ),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(Icons.support_agent_rounded,
                  color: Theme.of(context).primaryColor, size: 18),
              const SizedBox(width: Dimensions.paddingSizeExtraSmall),
              Text(
                'need_support'.tr,
                style: robotoBold.copyWith(
                  fontSize: Dimensions.fontSizeDefault,
                  color: Theme.of(context).primaryColor,
                ),
              ),
            ],
          ),
          const SizedBox(height: Dimensions.paddingSizeSmall),
          GridView.builder(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
              crossAxisCount: 2,
              crossAxisSpacing: Dimensions.paddingSizeSmall,
              mainAxisSpacing: Dimensions.paddingSizeSmall,
              childAspectRatio: 3.2,
            ),
            itemCount: _actions.length,
            itemBuilder: (context, index) =>
                _EscalationTile(action: _actions[index]),
          ),
        ],
      ),
    );
  }
}

class _EscalationAction {
  final IconData icon;
  final String labelKey;
  final Color color;

  const _EscalationAction({
    required this.icon,
    required this.labelKey,
    required this.color,
  });
}

class _EscalationTile extends StatelessWidget {
  final _EscalationAction action;
  const _EscalationTile({required this.action});

  void _onTap(BuildContext context) {
    if (action.labelKey == 'escalate_access_problem') {
      Get.toNamed(RouteHelper.reportIssue);
    } else {
      showCustomSnackBar(
        '${'escalation_sent'.tr} — ${action.labelKey.tr}',
        type: ToasterMessageType.info,
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: () => _onTap(context),
      borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
      child: Container(
        padding: const EdgeInsets.symmetric(
          horizontal: Dimensions.paddingSizeSmall,
          vertical: Dimensions.paddingSizeExtraSmall,
        ),
        decoration: BoxDecoration(
          color: action.color.withValues(alpha: 0.08),
          borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
          border: Border.all(color: action.color.withValues(alpha: 0.25)),
        ),
        child: Row(
          children: [
            Icon(action.icon, size: 16, color: action.color),
            const SizedBox(width: Dimensions.paddingSizeExtraSmall),
            Expanded(
              child: Text(
                action.labelKey.tr,
                style: robotoMedium.copyWith(
                  fontSize: Dimensions.fontSizeSmall,
                  color: action.color,
                ),
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
              ),
            ),
          ],
        ),
      ),
    );
  }
}
