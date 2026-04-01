import 'package:demandium_serviceman/utils/core_export.dart';

/// Checklist launcher widget displayed inside Job Details.
/// Provides "Start Checklist" and "Resume Checklist" actions.
/// Replace the snackbar calls with real checklist navigation
/// once the checklist execution backend is available.
class ChecklistLauncherWidget extends StatelessWidget {
  const ChecklistLauncherWidget({super.key});

  @override
  Widget build(BuildContext context) {
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
                Icon(Icons.checklist_rounded,
                    color: Theme.of(context).primaryColor, size: 18),
                const SizedBox(width: Dimensions.paddingSizeExtraSmall),
                Text(
                  'checklists'.tr,
                  style: robotoBold.copyWith(
                    fontSize: Dimensions.fontSizeDefault,
                    color: Theme.of(context).primaryColor,
                  ),
                ),
              ],
            ),
          ),
          Divider(
              height: 1,
              color:
                  Theme.of(context).primaryColor.withValues(alpha: 0.15)),
          Padding(
            padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
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
                const SizedBox(height: Dimensions.paddingSizeSmall),
                Row(
                  children: [
                    Expanded(
                      child: OutlinedButton.icon(
                        onPressed: () => showCustomSnackBar(
                          'checklist_coming_soon'.tr,
                          type: ToasterMessageType.info,
                        ),
                        icon: const Icon(Icons.play_arrow_rounded, size: 18),
                        label: Text('start_checklist'.tr),
                        style: OutlinedButton.styleFrom(
                          foregroundColor: Theme.of(context).primaryColor,
                          side: BorderSide(
                              color: Theme.of(context).primaryColor),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(
                                Dimensions.radiusDefault),
                          ),
                        ),
                      ),
                    ),
                    const SizedBox(width: Dimensions.paddingSizeSmall),
                    Expanded(
                      child: OutlinedButton.icon(
                        onPressed: () => showCustomSnackBar(
                          'checklist_coming_soon'.tr,
                          type: ToasterMessageType.info,
                        ),
                        icon: const Icon(Icons.restart_alt_rounded, size: 18),
                        label: Text('resume_checklist'.tr),
                        style: OutlinedButton.styleFrom(
                          foregroundColor: Theme.of(context)
                              .textTheme
                              .bodyLarge
                              ?.color
                              ?.withValues(alpha: 0.7),
                          side: BorderSide(
                            color: Theme.of(context)
                                .textTheme
                                .bodyLarge!
                                .color!
                                .withValues(alpha: 0.3),
                          ),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(
                                Dimensions.radiusDefault),
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
