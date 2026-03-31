import 'package:demandium_serviceman/utils/core_export.dart';
import 'package:get/get.dart';

/// Report Issue screen – allows cleaners to log on-site problems.
/// Submits locally for now; hook to a real API endpoint when available.
class ReportIssueScreen extends StatefulWidget {
  const ReportIssueScreen({super.key});

  @override
  State<ReportIssueScreen> createState() => _ReportIssueScreenState();
}

class _ReportIssueScreenState extends State<ReportIssueScreen> {
  String? _selectedIssueType;
  final TextEditingController _notesController = TextEditingController();

  static const List<String> _issueTypes = [
    'access_problem',
    'customer_unavailable',
    'damage_found',
    'hazard_detected',
    'incomplete_previous_clean',
    'extra_work_requested',
    'safety_risk',
    'animal_on_site',
    'biohazard_material',
    'incorrect_supplies_provided',
    'property_access_code_incorrect',
    'service_scope_unclear',
  ];

  @override
  void dispose() {
    _notesController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Theme.of(context).colorScheme.surface,
      appBar: CustomAppBar(title: 'report_issue'.tr),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'issue_type'.tr,
              style: robotoMedium.copyWith(
                  fontSize: Dimensions.fontSizeDefault),
            ),
            const SizedBox(height: Dimensions.paddingSizeSmall),
            Container(
              decoration: BoxDecoration(
                color: Theme.of(context).cardColor,
                borderRadius:
                    BorderRadius.circular(Dimensions.radiusDefault),
              ),
              child: Column(
                children: _issueTypes
                    .map((type) => RadioListTile<String>(
                          title: Text(type.tr,
                              style: robotoRegular.copyWith(
                                  fontSize: Dimensions.fontSizeDefault)),
                          value: type,
                          groupValue: _selectedIssueType,
                          activeColor: Theme.of(context).primaryColor,
                          onChanged: (val) =>
                              setState(() => _selectedIssueType = val),
                        ))
                    .toList(),
              ),
            ),
            const SizedBox(height: Dimensions.paddingSizeDefault),
            Text(
              'add_notes'.tr,
              style: robotoMedium.copyWith(
                  fontSize: Dimensions.fontSizeDefault),
            ),
            const SizedBox(height: Dimensions.paddingSizeSmall),
            TextFormField(
              controller: _notesController,
              maxLines: 4,
              style: robotoRegular.copyWith(
                  fontSize: Dimensions.fontSizeDefault),
              decoration: InputDecoration(
                hintText: 'write_something'.tr,
                hintStyle: robotoRegular.copyWith(
                    color: Theme.of(context)
                        .textTheme
                        .bodyLarge
                        ?.color
                        ?.withValues(alpha: 0.4)),
                border: OutlineInputBorder(
                  borderRadius:
                      BorderRadius.circular(Dimensions.radiusDefault),
                ),
                filled: true,
                fillColor: Theme.of(context).cardColor,
              ),
            ),
            const SizedBox(height: Dimensions.paddingSizeLarge),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: _selectedIssueType == null
                    ? null
                    : () {
                        showCustomSnackBar('report_submitted'.tr,
                            type: ToasterMessageType.success);
                        Get.back();
                      },
                style: ElevatedButton.styleFrom(
                  backgroundColor: Theme.of(context).primaryColor,
                  disabledBackgroundColor:
                      Theme.of(context).primaryColor.withValues(alpha: 0.4),
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(
                      vertical: Dimensions.paddingSizeDefault),
                  shape: RoundedRectangleBorder(
                    borderRadius:
                        BorderRadius.circular(Dimensions.radiusDefault),
                  ),
                ),
                child: Text('submit'.tr, style: robotoBold),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
