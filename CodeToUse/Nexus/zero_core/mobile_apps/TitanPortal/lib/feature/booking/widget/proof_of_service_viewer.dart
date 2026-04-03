import 'package:demandium/common/widgets/image_dialog.dart';
import 'package:demandium/feature/booking/model/booking_details_model.dart';
import 'package:demandium/utils/core_export.dart';
import 'package:get/get.dart';

/// Phase 8 — Proof-of-Service viewer widget.
/// Displays service evidence photos with lifecycle framing.
/// Wraps and extends the existing photo evidence section.
class ProofOfServiceViewer extends StatelessWidget {
  final BookingDetailsContent bookingDetailsContent;
  const ProofOfServiceViewer({super.key, required this.bookingDetailsContent});

  @override
  Widget build(BuildContext context) {
    final List<String> photos = bookingDetailsContent.photoEvidenceFullPath ?? [];
    if (photos.isEmpty) return const SizedBox.shrink();

    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: Dimensions.paddingSizeDefault),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const SizedBox(height: Dimensions.paddingSizeDefault),
          Row(
            children: [
              Icon(Icons.verified_outlined, size: 18, color: Theme.of(context).primaryColor),
              const SizedBox(width: 6),
              Text(
                'proof_of_service'.tr,
                style: robotoMedium.copyWith(
                  fontSize: Dimensions.fontSizeDefault,
                  color: Theme.of(context).primaryColor,
                ),
              ),
            ],
          ),
          const SizedBox(height: Dimensions.paddingSizeSmall),
          _PhotoStrip(
            label: 'after_photos'.tr,
            photos: photos,
            context: context,
          ),
        ],
      ),
    );
  }
}

class _PhotoStrip extends StatelessWidget {
  final String label;
  final List<String> photos;
  final BuildContext context;

  const _PhotoStrip({
    required this.label,
    required this.photos,
    required this.context,
  });

  @override
  Widget build(BuildContext ctx) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          label,
          style: robotoRegular.copyWith(
            fontSize: Dimensions.fontSizeSmall,
            color: Theme.of(ctx).hintColor,
          ),
        ),
        const SizedBox(height: 6),
        Container(
          height: 90,
          padding: const EdgeInsets.all(Dimensions.paddingSizeSmall),
          decoration: BoxDecoration(
            color: Theme.of(ctx).primaryColor.withValues(alpha: 0.05),
            borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
          ),
          child: ListView.builder(
            scrollDirection: Axis.horizontal,
            physics: const BouncingScrollPhysics(),
            itemCount: photos.length,
            itemBuilder: (context, index) {
              return Padding(
                padding: const EdgeInsets.symmetric(
                    horizontal: Dimensions.paddingSizeExtraSmall),
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
                      height: 70,
                      width: 120,
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
