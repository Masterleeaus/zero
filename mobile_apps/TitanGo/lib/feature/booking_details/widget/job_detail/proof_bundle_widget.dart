import 'package:demandium_serviceman/utils/core_export.dart';
import 'package:get/get.dart';

/// Proof bundle widget that groups job photos into operational sections:
/// Before / After / Issue / Extra Work.
/// Replaces the flat proof list in BookingDetailsWidget.
/// TODO: Wire each section to distinct upload endpoints when backend supports
/// photo categories (before/after/issue/extra).
class ProofBundleWidget extends StatelessWidget {
  final List<String> photoEvidenceFullPath;
  final bool showUploadButton;
  final String bookingId;
  final bool isSubBooking;

  const ProofBundleWidget({
    super.key,
    required this.photoEvidenceFullPath,
    required this.showUploadButton,
    required this.bookingId,
    required this.isSubBooking,
  });

  @override
  Widget build(BuildContext context) {
    // Until per-category photo endpoints exist, display all existing photos
    // in the "After Photos" section and show empty placeholders for others.
    // TODO: Split into before/after/issue/extra when backend categorises photos.
    return Padding(
      padding: const EdgeInsets.symmetric(
          horizontal: Dimensions.paddingSizeDefault),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const SizedBox(height: Dimensions.paddingSizeDefault),
          Row(
            children: [
              Icon(Icons.photo_library_outlined,
                  color: Theme.of(context).primaryColor, size: 18),
              const SizedBox(width: Dimensions.paddingSizeExtraSmall),
              Text(
                'job_proof_bundle'.tr,
                style: robotoBold.copyWith(
                  fontSize: Dimensions.fontSizeDefault,
                  color: Theme.of(context).primaryColor,
                ),
              ),
            ],
          ),
          const SizedBox(height: Dimensions.paddingSizeSmall),
          // Before Photos (placeholder – no backend source yet)
          _ProofSection(
            label: 'before_photos'.tr,
            icon: Icons.photo_camera_back_outlined,
            photos: const [],
            showUploadButton: showUploadButton,
            bookingId: bookingId,
            isSubBooking: isSubBooking,
            onAddPressed: null,
          ),
          const SizedBox(height: Dimensions.paddingSizeSmall),
          // After Photos – all current photo evidence goes here
          _ProofSection(
            label: 'after_photos'.tr,
            icon: Icons.photo_camera_outlined,
            photos: photoEvidenceFullPath,
            showUploadButton: showUploadButton,
            bookingId: bookingId,
            isSubBooking: isSubBooking,
            onAddPressed: showUploadButton
                ? () => Get.bottomSheet(
                      CameraButtonSheet(
                        bookingId: bookingId,
                        isSubBooking: isSubBooking,
                      ),
                    )
                : null,
          ),
          const SizedBox(height: Dimensions.paddingSizeSmall),
          // Issue Photos (placeholder)
          _ProofSection(
            label: 'issue_photos'.tr,
            icon: Icons.report_problem_outlined,
            photos: const [],
            showUploadButton: false,
            bookingId: bookingId,
            isSubBooking: isSubBooking,
            onAddPressed: null,
          ),
          const SizedBox(height: Dimensions.paddingSizeSmall),
          // Extra Work Photos (placeholder)
          _ProofSection(
            label: 'extra_work_photos'.tr,
            icon: Icons.add_a_photo_outlined,
            photos: const [],
            showUploadButton: false,
            bookingId: bookingId,
            isSubBooking: isSubBooking,
            onAddPressed: null,
          ),
        ],
      ),
    );
  }
}

class _ProofSection extends StatelessWidget {
  final String label;
  final IconData icon;
  final List<String> photos;
  final bool showUploadButton;
  final String bookingId;
  final bool isSubBooking;
  final VoidCallback? onAddPressed;

  const _ProofSection({
    required this.label,
    required this.icon,
    required this.photos,
    required this.showUploadButton,
    required this.bookingId,
    required this.isSubBooking,
    required this.onAddPressed,
  });

  @override
  Widget build(BuildContext context) {
    final bool hasPhotos = photos.isNotEmpty;

    return Container(
      padding: const EdgeInsets.all(Dimensions.paddingSizeSmall),
      decoration: BoxDecoration(
        color: Theme.of(context).primaryColor.withValues(alpha: 0.04),
        borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
        border: Border.all(
          color: Theme.of(context).primaryColor.withValues(alpha: 0.12),
        ),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(icon,
                  size: 14,
                  color: Theme.of(context)
                      .textTheme
                      .bodyLarge
                      ?.color
                      ?.withValues(alpha: 0.6)),
              const SizedBox(width: Dimensions.paddingSizeExtraSmall),
              Expanded(
                child: Text(
                  label,
                  style: robotoMedium.copyWith(
                    fontSize: Dimensions.fontSizeSmall,
                    color: Theme.of(context)
                        .textTheme
                        .bodyLarge
                        ?.color
                        ?.withValues(alpha: 0.75),
                  ),
                ),
              ),
              if (onAddPressed != null)
                InkWell(
                  onTap: onAddPressed,
                  borderRadius: BorderRadius.circular(50),
                  child: Padding(
                    padding: const EdgeInsets.all(4),
                    child: Icon(Icons.add_circle_outline_rounded,
                        size: 18,
                        color: Theme.of(context).primaryColor),
                  ),
                ),
            ],
          ),
          if (hasPhotos) ...[
            const SizedBox(height: Dimensions.paddingSizeExtraSmall),
            SizedBox(
              height: 70,
              child: ListView.builder(
                scrollDirection: Axis.horizontal,
                physics: const BouncingScrollPhysics(),
                itemCount: photos.length,
                itemBuilder: (context, index) {
                  return GestureDetector(
                    onTap: () => Get.to(ImageDetailScreen(
                      imageList: photos,
                      index: index,
                      appbarTitle: label,
                    )),
                    child: Container(
                      margin: const EdgeInsets.only(
                          right: Dimensions.paddingSizeExtraSmall),
                      child: ClipRRect(
                        borderRadius:
                            BorderRadius.circular(Dimensions.radiusSmall),
                        child: CustomImage(
                          image: photos[index],
                          height: 70,
                          width: 100,
                        ),
                      ),
                    ),
                  );
                },
              ),
            ),
          ],
          if (!hasPhotos && onAddPressed == null) ...[
            const SizedBox(height: Dimensions.paddingSizeExtraSmall),
            Text(
              'no_photos_yet'.tr,
              style: robotoRegular.copyWith(
                fontSize: Dimensions.fontSizeSmall,
                color: Theme.of(context)
                    .textTheme
                    .bodyLarge
                    ?.color
                    ?.withValues(alpha: 0.35),
              ),
            ),
          ],
        ],
      ),
    );
  }
}
