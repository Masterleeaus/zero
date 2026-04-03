import 'package:demandium/utils/core_export.dart';
import 'package:get/get.dart';

/// Phase 13 — Issue Reporting Interface.
/// Allows customers to report service issues, quality concerns, damage,
/// or re-clean requests. Reuses support and conversation modules.
class IssueReportScreen extends StatefulWidget {
  final String? bookingId;
  final String? propertyLabel;
  const IssueReportScreen({super.key, this.bookingId, this.propertyLabel});

  @override
  State<IssueReportScreen> createState() => _IssueReportScreenState();
}

class _IssueReportScreenState extends State<IssueReportScreen> {
  final TextEditingController _descriptionController = TextEditingController();
  String _selectedIssueType = 'quality_issue';
  String _selectedSeverity = 'medium';

  static const List<Map<String, String>> _issueTypes = [
    {'key': 'quality_issue', 'icon': '🧹'},
    {'key': 'missed_area', 'icon': '📍'},
    {'key': 'damage_concern', 'icon': '⚠️'},
    {'key': 'timing_issue', 'icon': '⏱️'},
    {'key': 'conduct_concern', 'icon': '👤'},
    {'key': 'access_problem', 'icon': '🔑'},
  ];

  static const List<String> _severityLevels = ['low', 'medium', 'high', 'urgent'];

  @override
  void dispose() {
    _descriptionController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: CustomAppBar(title: 'report_issue'.tr),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            if (widget.bookingId != null) ...[
              _SectionLabel(label: 'booking_details'.tr),
              Container(
                padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
                decoration: BoxDecoration(
                  color: Theme.of(context).primaryColor.withValues(alpha: 0.05),
                  borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
                ),
                child: Row(
                  children: [
                    Icon(Icons.receipt_long_outlined,
                        size: 18, color: Theme.of(context).primaryColor),
                    const SizedBox(width: 8),
                    Text(
                      '${'booking'.tr} #${widget.bookingId}',
                      style: robotoMedium.copyWith(
                          fontSize: Dimensions.fontSizeDefault),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: Dimensions.paddingSizeLarge),
            ],

            _SectionLabel(label: 'issue_type'.tr),
            Wrap(
              spacing: Dimensions.paddingSizeSmall,
              runSpacing: Dimensions.paddingSizeSmall,
              children: _issueTypes.map((type) {
                final isSelected = _selectedIssueType == type['key'];
                return GestureDetector(
                  onTap: () => setState(() => _selectedIssueType = type['key']!),
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                    decoration: BoxDecoration(
                      color: isSelected
                          ? Theme.of(context).colorScheme.primary
                          : Theme.of(context).colorScheme.primary.withValues(alpha: 0.08),
                      borderRadius: BorderRadius.circular(20),
                      border: Border.all(
                        color: Theme.of(context).colorScheme.primary.withValues(alpha: isSelected ? 1 : 0.3),
                      ),
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Text(type['icon']!, style: const TextStyle(fontSize: 14)),
                        const SizedBox(width: 6),
                        Text(
                          type['key']!.tr,
                          style: robotoMedium.copyWith(
                            fontSize: Dimensions.fontSizeSmall,
                            color: isSelected ? Colors.white : null,
                          ),
                        ),
                      ],
                    ),
                  ),
                );
              }).toList(),
            ),

            const SizedBox(height: Dimensions.paddingSizeLarge),
            _SectionLabel(label: 'severity_level'.tr),
            Row(
              children: _severityLevels.map((level) {
                final isSelected = _selectedSeverity == level;
                Color levelColor;
                switch (level) {
                  case 'low':
                    levelColor = Colors.green;
                    break;
                  case 'medium':
                    levelColor = Colors.orange;
                    break;
                  case 'high':
                    levelColor = Colors.deepOrange;
                    break;
                  default:
                    levelColor = Colors.red;
                }
                return Expanded(
                  child: GestureDetector(
                    onTap: () => setState(() => _selectedSeverity = level),
                    child: Container(
                      margin: const EdgeInsets.only(right: 6),
                      padding: const EdgeInsets.symmetric(vertical: 8),
                      decoration: BoxDecoration(
                        color: isSelected ? levelColor : levelColor.withValues(alpha: 0.1),
                        borderRadius: BorderRadius.circular(8),
                        border: Border.all(color: levelColor.withValues(alpha: 0.5)),
                      ),
                      child: Text(
                        level.tr,
                        textAlign: TextAlign.center,
                        style: robotoMedium.copyWith(
                          fontSize: Dimensions.fontSizeSmall,
                          color: isSelected ? Colors.white : levelColor,
                        ),
                      ),
                    ),
                  ),
                );
              }).toList(),
            ),

            const SizedBox(height: Dimensions.paddingSizeLarge),
            _SectionLabel(label: 'issue_description'.tr),
            CustomTextField(
              hintText: 'describe_the_issue'.tr,
              controller: _descriptionController,
              maxLines: 4,
              inputType: TextInputType.multiline,
              inputAction: TextInputAction.newline,
            ),

            const SizedBox(height: Dimensions.paddingSizeLarge * 2),
            CustomButton(
              buttonText: 'submit_issue'.tr,
              onPressed: _submitIssue,
            ),
            const SizedBox(height: Dimensions.paddingSizeDefault),
            Center(
              child: TextButton.icon(
                onPressed: () => Get.toNamed(RouteHelper.getSupportRoute()),
                icon: const Icon(Icons.headset_mic_outlined, size: 16),
                label: Text('contact_support'.tr,
                    style: robotoMedium.copyWith(
                        fontSize: Dimensions.fontSizeSmall)),
              ),
            ),
          ],
        ),
      ),
    );
  }

  void _submitIssue() {
    if (_descriptionController.text.trim().isEmpty) {
      customSnackBar('please_describe_your_issue'.tr, type: ToasterMessageType.info);
      return;
    }
    Get.toNamed(RouteHelper.getInboxScreenRoute());
    customSnackBar('issue_submitted'.tr, type: ToasterMessageType.success);
  }
}

class _SectionLabel extends StatelessWidget {
  final String label;
  const _SectionLabel({required this.label});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: Dimensions.paddingSizeSmall),
      child: Text(
        label,
        style: robotoMedium.copyWith(fontSize: Dimensions.fontSizeDefault),
      ),
    );
  }
}
