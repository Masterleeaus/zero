import 'package:demandium_serviceman/utils/core_export.dart';
import 'package:get/get.dart';

/// Job Arrival / Access preflight gate shown above checklist execution.
/// Cleaners must confirm arrival + access before starting checklists.
/// All state is local-only for now.
/// TODO: Persist confirmation to backend via /api/v1/provider/job/arrival when available.

enum _ArrivalStep { arrival, access, alarm, complete }

class JobArrivalWidget extends StatefulWidget {
  const JobArrivalWidget({super.key});

  @override
  State<JobArrivalWidget> createState() => _JobArrivalWidgetState();
}

class _JobArrivalWidgetState extends State<JobArrivalWidget> {
  bool _arrivedConfirmed = false;
  bool _accessConfirmed = false;
  bool _alarmConfirmed = false;
  bool _unableToAccess = false;
  String? _selectedUnableReason;
  final TextEditingController _unableNotesController = TextEditingController();

  static const List<String> _unableReasons = [
    'unable_access_no_one_home',
    'unable_access_locked_out',
    'unable_access_alarm_active',
    'unable_access_safety_concern',
    'unable_access_wrong_address',
    'unable_access_other',
  ];

  bool get _isGateComplete =>
      _arrivedConfirmed && _accessConfirmed && _alarmConfirmed;

  @override
  void dispose() {
    _unableNotesController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    if (_isGateComplete && !_unableToAccess) {
      return _buildCompletedBanner(context);
    }

    return Container(
      margin: const EdgeInsets.symmetric(
        horizontal: Dimensions.paddingSizeDefault,
        vertical: Dimensions.paddingSizeSmall,
      ),
      decoration: BoxDecoration(
        color: Theme.of(context).cardColor,
        borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
        border: Border.all(
          color: Colors.blue.shade300.withValues(alpha: 0.6),
        ),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Header
          Padding(
            padding: const EdgeInsets.fromLTRB(
              Dimensions.paddingSizeDefault,
              Dimensions.paddingSizeDefault,
              Dimensions.paddingSizeDefault,
              Dimensions.paddingSizeExtraSmall,
            ),
            child: Row(
              children: [
                Icon(Icons.location_on_rounded,
                    color: Colors.blue.shade600, size: 18),
                const SizedBox(width: Dimensions.paddingSizeExtraSmall),
                Expanded(
                  child: Text(
                    'job_arrival_access'.tr,
                    style: robotoBold.copyWith(
                      fontSize: Dimensions.fontSizeDefault,
                      color: Colors.blue.shade600,
                    ),
                  ),
                ),
              ],
            ),
          ),
          Divider(height: 1, color: Colors.blue.shade100),
          Padding(
            padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
            child: _unableToAccess
                ? _buildUnableToAccessForm(context)
                : _buildArrivalChecks(context),
          ),
        ],
      ),
    );
  }

  Widget _buildArrivalChecks(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        _ArrivalCheckTile(
          label: 'confirm_arrival'.tr,
          hint: 'confirm_arrival_hint'.tr,
          icon: Icons.place_rounded,
          checked: _arrivedConfirmed,
          onChanged: _arrivedConfirmed
              ? null
              : (v) => setState(() => _arrivedConfirmed = v ?? false),
        ),
        if (_arrivedConfirmed) ...[
          const SizedBox(height: Dimensions.paddingSizeExtraSmall),
          _ArrivalCheckTile(
            label: 'confirm_access'.tr,
            hint: 'confirm_access_hint'.tr,
            icon: Icons.key_rounded,
            checked: _accessConfirmed,
            onChanged: _accessConfirmed
                ? null
                : (v) => setState(() => _accessConfirmed = v ?? false),
          ),
        ],
        if (_accessConfirmed) ...[
          const SizedBox(height: Dimensions.paddingSizeExtraSmall),
          _ArrivalCheckTile(
            label: 'confirm_alarm_entry'.tr,
            hint: 'confirm_alarm_entry_hint'.tr,
            icon: Icons.security_rounded,
            checked: _alarmConfirmed,
            onChanged: _alarmConfirmed
                ? null
                : (v) => setState(() => _alarmConfirmed = v ?? false),
          ),
        ],
        const SizedBox(height: Dimensions.paddingSizeSmall),
        TextButton.icon(
          onPressed: () => setState(() => _unableToAccess = true),
          icon: Icon(Icons.block_rounded,
              size: 16, color: Theme.of(context).colorScheme.error),
          label: Text(
            'unable_to_access'.tr,
            style: robotoMedium.copyWith(
              fontSize: Dimensions.fontSizeSmall,
              color: Theme.of(context).colorScheme.error,
            ),
          ),
          style: TextButton.styleFrom(
            padding: EdgeInsets.zero,
            tapTargetSize: MaterialTapTargetSize.shrinkWrap,
          ),
        ),
      ],
    );
  }

  Widget _buildUnableToAccessForm(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            Icon(Icons.warning_amber_rounded,
                color: Theme.of(context).colorScheme.error, size: 18),
            const SizedBox(width: Dimensions.paddingSizeExtraSmall),
            Text(
              'unable_to_access'.tr,
              style: robotoBold.copyWith(
                fontSize: Dimensions.fontSizeDefault,
                color: Theme.of(context).colorScheme.error,
              ),
            ),
          ],
        ),
        const SizedBox(height: Dimensions.paddingSizeSmall),
        Text(
          'unable_access_reason_label'.tr,
          style: robotoMedium.copyWith(fontSize: Dimensions.fontSizeSmall),
        ),
        const SizedBox(height: Dimensions.paddingSizeExtraSmall),
        Container(
          decoration: BoxDecoration(
            color: Theme.of(context).colorScheme.surface,
            borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
          ),
          child: Column(
            children: _unableReasons
                .map((reason) => RadioListTile<String>(
                      dense: true,
                      title: Text(reason.tr,
                          style: robotoRegular.copyWith(
                              fontSize: Dimensions.fontSizeSmall)),
                      value: reason,
                      groupValue: _selectedUnableReason,
                      activeColor: Theme.of(context).colorScheme.error,
                      onChanged: (v) =>
                          setState(() => _selectedUnableReason = v),
                    ))
                .toList(),
          ),
        ),
        const SizedBox(height: Dimensions.paddingSizeSmall),
        TextFormField(
          controller: _unableNotesController,
          maxLines: 3,
          style:
              robotoRegular.copyWith(fontSize: Dimensions.fontSizeDefault),
          decoration: InputDecoration(
            hintText: 'add_notes'.tr,
            hintStyle: robotoRegular.copyWith(
                color: Theme.of(context)
                    .textTheme
                    .bodyLarge
                    ?.color
                    ?.withValues(alpha: 0.4)),
            border: OutlineInputBorder(
              borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
            ),
            filled: true,
            fillColor: Theme.of(context).cardColor,
            contentPadding: const EdgeInsets.all(Dimensions.paddingSizeSmall),
          ),
        ),
        const SizedBox(height: Dimensions.paddingSizeSmall),
        Row(
          children: [
            Expanded(
              child: OutlinedButton(
                onPressed: () => setState(() => _unableToAccess = false),
                child: Text('go_back'.tr),
              ),
            ),
            const SizedBox(width: Dimensions.paddingSizeSmall),
            Expanded(
              child: ElevatedButton.icon(
                onPressed: _selectedUnableReason == null
                    ? null
                    : () {
                        showCustomSnackBar(
                          'unable_access_escalated'.tr,
                          type: ToasterMessageType.warning,
                        );
                        Get.toNamed(RouteHelper.reportIssue);
                      },
                icon: const Icon(Icons.send_rounded, size: 16),
                label: Text('escalate_issue'.tr),
                style: ElevatedButton.styleFrom(
                  backgroundColor: Theme.of(context).colorScheme.error,
                  foregroundColor: Colors.white,
                ),
              ),
            ),
          ],
        ),
      ],
    );
  }

  Widget _buildCompletedBanner(BuildContext context) {
    return Container(
      margin: const EdgeInsets.symmetric(
        horizontal: Dimensions.paddingSizeDefault,
        vertical: Dimensions.paddingSizeSmall,
      ),
      padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
      decoration: BoxDecoration(
        color: Colors.green.shade50,
        borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
        border: Border.all(color: Colors.green.shade200),
      ),
      child: Row(
        children: [
          Icon(Icons.verified_rounded, color: Colors.green.shade600, size: 18),
          const SizedBox(width: Dimensions.paddingSizeExtraSmall),
          Expanded(
            child: Text(
              'arrival_access_confirmed'.tr,
              style: robotoMedium.copyWith(
                fontSize: Dimensions.fontSizeSmall,
                color: Colors.green.shade700,
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _ArrivalCheckTile extends StatelessWidget {
  final String label;
  final String hint;
  final IconData icon;
  final bool checked;
  final ValueChanged<bool?>? onChanged;

  const _ArrivalCheckTile({
    required this.label,
    required this.hint,
    required this.icon,
    required this.checked,
    required this.onChanged,
  });

  @override
  Widget build(BuildContext context) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Checkbox(
          value: checked,
          onChanged: onChanged,
          activeColor: Colors.blue.shade600,
          materialTapTargetSize: MaterialTapTargetSize.shrinkWrap,
          visualDensity: VisualDensity.compact,
        ),
        const SizedBox(width: Dimensions.paddingSizeExtraSmall),
        Expanded(
          child: GestureDetector(
            onTap: () => onChanged?.call(!checked),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  label,
                  style: robotoMedium.copyWith(
                    fontSize: Dimensions.fontSizeSmall,
                    color: checked
                        ? Colors.blue.shade600
                        : Theme.of(context)
                            .textTheme
                            .bodyLarge
                            ?.color,
                    decoration: checked ? TextDecoration.lineThrough : null,
                  ),
                ),
                Text(
                  hint,
                  style: robotoRegular.copyWith(
                    fontSize: Dimensions.fontSizeSmall,
                    color: Theme.of(context)
                        .textTheme
                        .bodyLarge
                        ?.color
                        ?.withValues(alpha: 0.5),
                  ),
                ),
              ],
            ),
          ),
        ),
      ],
    );
  }
}
