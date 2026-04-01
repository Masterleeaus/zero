import 'package:demandium/common/widgets/image_dialog.dart';
import 'package:demandium/feature/booking/model/booking_details_model.dart';
import 'package:demandium/utils/core_export.dart';
import 'package:get/get.dart';

/// Pass 2 — Proof-of-Service trust module.
/// Displays service evidence photos with lifecycle framing:
///   - Completion status badge
///   - Cleaner attribution (if available)
///   - Service timestamp (if available)
///   - Photo evidence strip
///   - Issue escalation shortcut
class ProofOfServiceViewer extends StatelessWidget {
  final BookingDetailsContent bookingDetailsContent;
  const ProofOfServiceViewer({super.key, required this.bookingDetailsContent});

  @override
  Widget build(BuildContext context) {
    final List<String> photos = bookingDetailsContent.photoEvidenceFullPath ?? [];
    if (photos.isEmpty) return const SizedBox.shrink();

    final String? cleanerName = _cleanerName(bookingDetailsContent);
    final String? completedAt = bookingDetailsContent.serviceSchedule;
    final String? bookingId = bookingDetailsContent.readableId ?? bookingDetailsContent.id;

    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: Dimensions.paddingSizeDefault),
      child: Container(
        decoration: BoxDecoration(
          color: Theme.of(context).primaryColor.withValues(alpha: 0.04),
          borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
          border: Border.all(
            color: Theme.of(context).primaryColor.withValues(alpha: 0.15),
          ),
        ),
        padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Header: Proof of Service label + completion badge
            Row(
              children: [
                Icon(Icons.verified_outlined, size: 18,
                    color: Theme.of(context).primaryColor),
                const SizedBox(width: 6),
                Expanded(
                  child: Text(
                    'proof_of_service'.tr,
                    style: robotoMedium.copyWith(
                      fontSize: Dimensions.fontSizeDefault,
                      color: Theme.of(context).primaryColor,
                    ),
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  decoration: BoxDecoration(
                    color: Colors.green.withValues(alpha: 0.12),
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: Colors.green.withValues(alpha: 0.4)),
                  ),
                  child: Text(
                    'service_completed_label'.tr,
                    style: robotoMedium.copyWith(
                      fontSize: Dimensions.fontSizeExtraSmall,
                      color: Colors.green.shade700,
                    ),
                  ),
                ),
              ],
            ),

            // Cleaner + timestamp metadata
            if (cleanerName != null || completedAt != null) ...[
              const SizedBox(height: Dimensions.paddingSizeSmall),
              Row(
                children: [
                  if (cleanerName != null) ...[
                    Icon(Icons.person_outlined, size: 14,
                        color: Theme.of(context).hintColor),
                    const SizedBox(width: 4),
                    Text(
                      cleanerName,
                      style: robotoRegular.copyWith(
                        fontSize: Dimensions.fontSizeExtraSmall,
                        color: Theme.of(context).hintColor,
                      ),
                    ),
                    const SizedBox(width: 12),
                  ],
                  if (completedAt != null) ...[
                    Icon(Icons.schedule_outlined, size: 14,
                        color: Theme.of(context).hintColor),
                    const SizedBox(width: 4),
                    Expanded(
                      child: Text(
                        completedAt,
                        style: robotoRegular.copyWith(
                          fontSize: Dimensions.fontSizeExtraSmall,
                          color: Theme.of(context).hintColor,
                        ),
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                  ],
                ],
              ),
            ],

            const SizedBox(height: Dimensions.paddingSizeSmall),

            // Photo strip
            _PhotoStrip(label: 'after_photos'.tr, photos: photos),

            // Issue escalation shortcut
            const SizedBox(height: Dimensions.paddingSizeSmall),
            TextButton.icon(
              style: TextButton.styleFrom(
                padding: EdgeInsets.zero,
                minimumSize: Size.zero,
                tapTargetSize: MaterialTapTargetSize.shrinkWrap,
              ),
              onPressed: () => Get.toNamed(
                RouteHelper.getReportIssueRoute(bookingId: bookingId),
              ),
              icon: Icon(Icons.flag_outlined, size: 14,
                  color: Theme.of(context).colorScheme.error),
              label: Text(
                'report_issue_about_service'.tr,
                style: robotoRegular.copyWith(
                  fontSize: Dimensions.fontSizeExtraSmall,
                  color: Theme.of(context).colorScheme.error,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  String? _cleanerName(BookingDetailsContent details) {
    if (details.serviceman?.user != null) {
      final u = details.serviceman!.user!;
      final name = '${u.firstName ?? ''} ${u.lastName ?? ''}'.trim();
      return name.isNotEmpty ? name : null;
    }
    if (details.provider?.companyName != null &&
        details.provider!.companyName!.isNotEmpty) {
      return details.provider!.companyName;
    }
    return null;
  }
}

class _PhotoStrip extends StatelessWidget {
  final String label;
  final List<String> photos;

  const _PhotoStrip({required this.label, required this.photos});

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          label,
          style: robotoRegular.copyWith(
            fontSize: Dimensions.fontSizeSmall,
            color: Theme.of(context).hintColor,
          ),
        ),
        const SizedBox(height: 6),
        SizedBox(
          height: 90,
          child: ListView.builder(
            scrollDirection: Axis.horizontal,
            physics: const BouncingScrollPhysics(),
            itemCount: photos.length,
            itemBuilder: (context, index) {
              return Padding(
                padding: const EdgeInsets.only(right: Dimensions.paddingSizeSmall),
                child: ClipRRect(
                  borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
                  child: GestureDetector(
                    onTap: () => showDialog(
                      context: context,
                      builder: (ctx) => ImageDialog(
                        imageUrl: photos[index],
                        title: 'proof_of_service'.tr,
                        subTitle: '',
                      ),
                    ),
                    child: CustomImage(
                      image: photos[index],
                      height: 90,
                      width: 130,
                    ),
                  ),
                ),
              );
            },
          ),
        ),
      ],
    );
  }
}
