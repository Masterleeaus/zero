import 'package:demandium/utils/core_export.dart';
import 'package:get/get.dart';

/// Pass 2 — Issue Reporting Interface.
/// Supports 8 issue types aligned to the lifecycle.
/// Shows lifecycle feedback card after submission and
/// routes customer to Messages (conversation inbox).
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
  bool _submitted = false;

  // 8 issue types per spec
  static const List<Map<String, String>> _issueTypes = [
    {'key': 'quality_issue',          'icon': '🧹'},
    {'key': 'missed_area',            'icon': '📍'},
    {'key': 'damage_concern',         'icon': '⚠️'},
    {'key': 'access_concern',         'icon': '🔑'},
    {'key': 'billing_question',       'icon': '💳'},
    {'key': 'cleaner_conduct_concern','icon': '👤'},
    {'key': 're_clean_request',       'icon': '🔄'},
    {'key': 'others',                 'icon': '💬'},
  ];

  @override
  void dispose() {
    _descriptionController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: CustomAppBar(title: 'report_issue'.tr),
      body: _submitted ? _SubmittedFeedback(bookingId: widget.bookingId) : _Form(
        bookingId: widget.bookingId,
        propertyLabel: widget.propertyLabel,
        selectedIssueType: _selectedIssueType,
        descriptionController: _descriptionController,
        issueTypes: _issueTypes,
        onSelectIssueType: (key) => setState(() => _selectedIssueType = key),
        onSubmit: _submitIssue,
      ),
    );
  }

  void _submitIssue() {
    if (_descriptionController.text.trim().isEmpty) {
      customSnackBar('please_describe_your_issue'.tr, type: ToasterMessageType.info);
      return;
    }
    setState(() => _submitted = true);
  }
}

// ---------------------------------------------------------------------------
// Issue form
// ---------------------------------------------------------------------------

class _Form extends StatelessWidget {
  final String? bookingId;
  final String? propertyLabel;
  final String selectedIssueType;
  final TextEditingController descriptionController;
  final List<Map<String, String>> issueTypes;
  final ValueChanged<String> onSelectIssueType;
  final VoidCallback onSubmit;

  const _Form({
    required this.bookingId,
    required this.propertyLabel,
    required this.selectedIssueType,
    required this.descriptionController,
    required this.issueTypes,
    required this.onSelectIssueType,
    required this.onSubmit,
  });

  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Service context card
          if (bookingId != null) ...[
            _SectionLabel(label: 'service_details'.tr),
            Container(
              padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
              decoration: BoxDecoration(
                color: Theme.of(context).primaryColor.withValues(alpha: 0.05),
                borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
              ),
              child: Row(
                children: [
                  Icon(Icons.receipt_long_outlined, size: 18,
                      color: Theme.of(context).primaryColor),
                  const SizedBox(width: 8),
                  Text(
                    '${'service_no'.tr} $bookingId',
                    style: robotoMedium.copyWith(
                        fontSize: Dimensions.fontSizeDefault),
                  ),
                  if (propertyLabel != null) ...[
                    const SizedBox(width: 8),
                    const Text('·'),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        propertyLabel!,
                        style: robotoRegular.copyWith(
                          fontSize: Dimensions.fontSizeSmall,
                          color: Theme.of(context).hintColor,
                        ),
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                  ],
                ],
              ),
            ),
            const SizedBox(height: Dimensions.paddingSizeLarge),
          ],

          // Issue type selector
          _SectionLabel(label: 'issue_type'.tr),
          Wrap(
            spacing: Dimensions.paddingSizeSmall,
            runSpacing: Dimensions.paddingSizeSmall,
            children: issueTypes.map((type) {
              final isSelected = selectedIssueType == type['key'];
              final primary = Theme.of(context).colorScheme.primary;
              return GestureDetector(
                onTap: () => onSelectIssueType(type['key']!),
                child: AnimatedContainer(
                  duration: const Duration(milliseconds: 150),
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                  decoration: BoxDecoration(
                    color: isSelected
                        ? primary
                        : primary.withValues(alpha: 0.08),
                    borderRadius: BorderRadius.circular(20),
                    border: Border.all(
                      color: primary.withValues(alpha: isSelected ? 1 : 0.3),
                    ),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Text(type['icon']!,
                          style: const TextStyle(fontSize: 14)),
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

          // Description
          _SectionLabel(label: 'issue_description'.tr),
          CustomTextField(
            hintText: 'describe_the_issue'.tr,
            controller: descriptionController,
            maxLines: 4,
            inputType: TextInputType.multiline,
            inputAction: TextInputAction.newline,
          ),

          const SizedBox(height: Dimensions.paddingSizeLarge * 2),
          CustomButton(
            buttonText: 'submit_issue'.tr,
            onPressed: onSubmit,
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
    );
  }
}

// ---------------------------------------------------------------------------
// Post-submission lifecycle feedback card
// ---------------------------------------------------------------------------

class _SubmittedFeedback extends StatelessWidget {
  final String? bookingId;
  const _SubmittedFeedback({this.bookingId});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.all(Dimensions.paddingSizeLarge),
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Container(
            width: double.infinity,
            padding: const EdgeInsets.all(Dimensions.paddingSizeLarge),
            decoration: BoxDecoration(
              color: Colors.green.withValues(alpha: 0.07),
              borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
              border: Border.all(color: Colors.green.withValues(alpha: 0.3)),
            ),
            child: Column(
              children: [
                const Icon(Icons.check_circle_outline_rounded,
                    color: Colors.green, size: 48),
                const SizedBox(height: Dimensions.paddingSizeDefault),
                Text(
                  'issue_submitted_title'.tr,
                  style: robotoBold.copyWith(
                    fontSize: Dimensions.fontSizeExtraLarge,
                    color: Colors.green.shade700,
                  ),
                  textAlign: TextAlign.center,
                ),
                const SizedBox(height: Dimensions.paddingSizeSmall),
                Text(
                  'your_issue_sent'.tr,
                  style: robotoMedium.copyWith(
                    fontSize: Dimensions.fontSizeDefault,
                  ),
                  textAlign: TextAlign.center,
                ),
                const SizedBox(height: Dimensions.paddingSizeExtraSmall),
                Text(
                  'you_will_receive_reply_in_messages'.tr,
                  style: robotoRegular.copyWith(
                    fontSize: Dimensions.fontSizeSmall,
                    color: Theme.of(context).hintColor,
                  ),
                  textAlign: TextAlign.center,
                ),
              ],
            ),
          ),

          const SizedBox(height: Dimensions.paddingSizeLarge),

          // Route to Messages
          CustomButton(
            buttonText: 'view_in_messages'.tr,
            onPressed: () {
              if (Get.find<AuthController>().isLoggedIn()) {
                Get.toNamed(RouteHelper.getInboxScreenRoute());
              } else {
                Get.toNamed(RouteHelper.getSupportRoute());
              }
            },
          ),

          const SizedBox(height: Dimensions.paddingSizeDefault),

          TextButton(
            onPressed: () {
              if (Navigator.canPop(context)) {
                Get.back();
              } else {
                Get.offAllNamed(RouteHelper.getMainRoute('home'));
              }
            },
            child: Text('go_home'.tr,
                style: robotoMedium.copyWith(
                    fontSize: Dimensions.fontSizeDefault)),
          ),
        ],
      ),
    );
  }
}

// ---------------------------------------------------------------------------
// Section label helper
// ---------------------------------------------------------------------------

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
