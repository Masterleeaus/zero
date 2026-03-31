import 'package:demandium_serviceman/feature/booking_details/model/checklist_execution_state.dart';
import 'package:demandium_serviceman/utils/core_export.dart';
import 'package:get/get.dart';

/// State-aware checklist launcher displayed inside Job Details.
/// Manages local execution state (pending → active → paused → completed → verified).
/// Buttons shown are contextual to the current state.
/// TODO: Persist state to local store and sync to backend when API is ready.
class ChecklistLauncherWidget extends StatefulWidget {
  final String jobId;
  const ChecklistLauncherWidget({super.key, required this.jobId});

  @override
  State<ChecklistLauncherWidget> createState() =>
      _ChecklistLauncherWidgetState();
}

class _ChecklistLauncherWidgetState extends State<ChecklistLauncherWidget> {
  late ChecklistExecutionModel _execution;

  @override
  void initState() {
    super.initState();
    _execution = ChecklistExecutionModel(jobId: widget.jobId);
  }

  void _start() => setState(() => _execution.start());
  void _pause() => setState(() => _execution.pause());
  void _complete() {
    setState(() => _execution.complete());
    showCustomSnackBar(
      'checklist_completed'.tr,
      type: ToasterMessageType.success,
    );
  }

  @override
  Widget build(BuildContext context) {
    final state = _execution.state;

    return Container(
      margin: const EdgeInsets.symmetric(
        horizontal: Dimensions.paddingSizeDefault,
        vertical: Dimensions.paddingSizeSmall,
      ),
      decoration: BoxDecoration(
        color: Theme.of(context).cardColor,
        borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
        border: Border.all(
          color: Theme.of(context).primaryColor.withValues(alpha: 0.25),
        ),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Header row with state badge
          Padding(
            padding: const EdgeInsets.fromLTRB(
              Dimensions.paddingSizeDefault,
              Dimensions.paddingSizeDefault,
              Dimensions.paddingSizeDefault,
              Dimensions.paddingSizeExtraSmall,
            ),
            child: Row(
              children: [
                Icon(Icons.checklist_rounded,
                    color: Theme.of(context).primaryColor, size: 18),
                const SizedBox(width: Dimensions.paddingSizeExtraSmall),
                Expanded(
                  child: Text(
                    'checklists'.tr,
                    style: robotoBold.copyWith(
                      fontSize: Dimensions.fontSizeDefault,
                      color: Theme.of(context).primaryColor,
                    ),
                  ),
                ),
                _StateBadge(state: state),
              ],
            ),
          ),
          Divider(
              height: 1,
              color: Theme.of(context).primaryColor.withValues(alpha: 0.15)),
          Padding(
            padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                if (state == ChecklistState.pending)
                  Text(
                    'no_checklist_available'.tr,
                    style: robotoRegular.copyWith(
                      fontSize: Dimensions.fontSizeSmall,
                      color: Theme.of(context)
                          .textTheme
                          .bodyLarge
                          ?.color
                          ?.withValues(alpha: 0.55),
                    ),
                  ),
                if (state == ChecklistState.active) ...[
                  Row(
                    children: [
                      Icon(Icons.timelapse_rounded,
                          size: 14, color: Colors.orange.shade600),
                      const SizedBox(width: Dimensions.paddingSizeExtraSmall),
                      Text(
                        'checklist_in_progress'.tr,
                        style: robotoMedium.copyWith(
                          fontSize: Dimensions.fontSizeSmall,
                          color: Colors.orange.shade600,
                        ),
                      ),
                    ],
                  ),
                ],
                if (state == ChecklistState.paused) ...[
                  Row(
                    children: [
                      Icon(Icons.pause_circle_outline_rounded,
                          size: 14, color: Theme.of(context).hintColor),
                      const SizedBox(width: Dimensions.paddingSizeExtraSmall),
                      Text(
                        'checklist_paused'.tr,
                        style: robotoRegular.copyWith(
                          fontSize: Dimensions.fontSizeSmall,
                          color: Theme.of(context).hintColor,
                        ),
                      ),
                    ],
                  ),
                ],
                if (state.isFinished) ...[
                  Row(
                    children: [
                      Icon(Icons.verified_rounded,
                          size: 14, color: Colors.green.shade600),
                      const SizedBox(width: Dimensions.paddingSizeExtraSmall),
                      Text(
                        state == ChecklistState.verified
                            ? 'checklist_verified'.tr
                            : 'checklist_state_completed'.tr,
                        style: robotoMedium.copyWith(
                          fontSize: Dimensions.fontSizeSmall,
                          color: Colors.green.shade600,
                        ),
                      ),
                    ],
                  ),
                ],
                const SizedBox(height: Dimensions.paddingSizeSmall),
                _buildActionRow(context, state),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildActionRow(BuildContext context, ChecklistState state) {
    if (state.isFinished) return const SizedBox.shrink();

    final List<Widget> actions = [];

    if (state == ChecklistState.pending) {
      actions.add(Expanded(
        child: _ActionButton(
          icon: Icons.play_arrow_rounded,
          label: 'start_checklist'.tr,
          onPressed: _start,
          primary: true,
        ),
      ));
    }

    if (state == ChecklistState.paused) {
      actions.add(Expanded(
        child: _ActionButton(
          icon: Icons.restart_alt_rounded,
          label: 'resume_checklist'.tr,
          onPressed: _start,
          primary: true,
        ),
      ));
    }

    if (state == ChecklistState.active) {
      actions.add(Expanded(
        child: _ActionButton(
          icon: Icons.pause_rounded,
          label: 'pause_checklist'.tr,
          onPressed: _pause,
          primary: false,
        ),
      ));
      actions.add(const SizedBox(width: Dimensions.paddingSizeSmall));
      actions.add(Expanded(
        child: _ActionButton(
          icon: Icons.check_circle_outline_rounded,
          label: 'complete_checklist'.tr,
          onPressed: _complete,
          primary: true,
        ),
      ));
    }

    return Row(children: actions);
  }
}

class _StateBadge extends StatelessWidget {
  final ChecklistState state;
  const _StateBadge({required this.state});

  Color _color(BuildContext context) {
    switch (state) {
      case ChecklistState.pending:
        return Theme.of(context).hintColor.withValues(alpha: 0.7);
      case ChecklistState.active:
        return Colors.orange.shade600;
      case ChecklistState.paused:
        return Colors.amber.shade700;
      case ChecklistState.completed:
        return Colors.green.shade600;
      case ChecklistState.verified:
        return Colors.blue.shade600;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(
        horizontal: Dimensions.paddingSizeSmall,
        vertical: 3,
      ),
      decoration: BoxDecoration(
        color: _color(context).withValues(alpha: 0.12),
        borderRadius: BorderRadius.circular(50),
        border: Border.all(color: _color(context).withValues(alpha: 0.4)),
      ),
      child: Text(
        state.label.tr,
        style: robotoMedium.copyWith(
          fontSize: Dimensions.fontSizeSmall,
          color: _color(context),
        ),
      ),
    );
  }
}

class _ActionButton extends StatelessWidget {
  final IconData icon;
  final String label;
  final VoidCallback onPressed;
  final bool primary;

  const _ActionButton({
    required this.icon,
    required this.label,
    required this.onPressed,
    required this.primary,
  });

  @override
  Widget build(BuildContext context) {
    return OutlinedButton.icon(
      onPressed: onPressed,
      icon: Icon(icon, size: 18),
      label: Text(label),
      style: OutlinedButton.styleFrom(
        foregroundColor: primary
            ? Theme.of(context).primaryColor
            : Theme.of(context)
                .textTheme
                .bodyLarge
                ?.color
                ?.withValues(alpha: 0.7),
        side: BorderSide(
          color: primary
              ? Theme.of(context).primaryColor
              : Theme.of(context)
                  .textTheme
                  .bodyLarge!
                  .color!
                  .withValues(alpha: 0.3),
        ),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
        ),
      ),
    );
  }
}
