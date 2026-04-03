import 'package:demandium_serviceman/utils/core_export.dart';
import 'package:get/get.dart';

/// Supply Issue reporting screen – cleaners log missing/low supplies.
/// Submits locally for now; hook to a real API endpoint when available.
class SupplyIssueScreen extends StatefulWidget {
  const SupplyIssueScreen({super.key});

  @override
  State<SupplyIssueScreen> createState() => _SupplyIssueScreenState();
}

class _SupplyIssueScreenState extends State<SupplyIssueScreen> {
  String? _selectedType;
  final TextEditingController _notesController = TextEditingController();

  static const List<String> _supplyTypes = [
    'low_chemicals',
    'broken_equipment',
    'missing_tools',
    'restock_required',
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
      appBar: CustomAppBar(title: 'supply_issues'.tr),
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
                children: _supplyTypes
                    .map((type) => RadioListTile<String>(
                          title: Text(type.tr,
                              style: robotoRegular.copyWith(
                                  fontSize: Dimensions.fontSizeDefault)),
                          value: type,
                          groupValue: _selectedType,
                          activeColor: Theme.of(context).primaryColor,
                          onChanged: (val) =>
                              setState(() => _selectedType = val),
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
                onPressed: _selectedType == null
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
