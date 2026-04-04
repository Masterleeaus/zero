import 'package:demandium_serviceman/utils/core_export.dart';
import 'package:get/get.dart';

/// Site Notes / Property Memory block displayed near the top of Job Details.
/// Shows address, contact info, and other on-site instructions pulled from
/// the booking's service address. Extend to a dedicated property_notes API
/// endpoint once the backend exposes one.
class SiteNotesWidget extends StatelessWidget {
  const SiteNotesWidget({super.key});

  @override
  Widget build(BuildContext context) {
    return GetBuilder<BookingDetailsController>(
      builder: (controller) {
        final bookingDetails =
            controller.bookingDetails?.bookingContent?.bookingDetailsContent;
        if (bookingDetails == null) return const SizedBox.shrink();

        final address = bookingDetails.serviceAddress;
        final hasAddress = address != null &&
            (address.address?.isNotEmpty == true ||
                address.street?.isNotEmpty == true);
        final hasContact = address?.contactPersonName?.isNotEmpty == true ||
            address?.contactPersonNumber?.isNotEmpty == true;

        if (!hasAddress && !hasContact) {
          return _buildNoNotes(context);
        }

        return Container(
          margin: const EdgeInsets.symmetric(
            horizontal: Dimensions.paddingSizeDefault,
            vertical: Dimensions.paddingSizeSmall,
          ),
          decoration: BoxDecoration(
            color: Theme.of(context).primaryColor.withValues(alpha: 0.04),
            borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
            border: Border.all(
              color: Theme.of(context).primaryColor.withValues(alpha: 0.2),
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
                    Icon(Icons.home_work_rounded,
                        color: Theme.of(context).primaryColor, size: 18),
                    const SizedBox(width: Dimensions.paddingSizeExtraSmall),
                    Text(
                      'site_notes'.tr,
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
                  color: Theme.of(context)
                      .primaryColor
                      .withValues(alpha: 0.15)),
              Padding(
                padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    if (hasAddress) ...[
                      _SiteNoteRow(
                        icon: Icons.location_on_outlined,
                        label: 'service_address'.tr,
                        value: _buildAddress(address),
                      ),
                    ],
                    if (address?.addressLabel?.isNotEmpty == true) ...[
                      const SizedBox(
                          height: Dimensions.paddingSizeExtraSmall),
                      _SiteNoteRow(
                        icon: Icons.label_outline_rounded,
                        label: 'entry_instructions'.tr,
                        value: address!.addressLabel!,
                      ),
                    ],
                    if (hasContact) ...[
                      const SizedBox(
                          height: Dimensions.paddingSizeExtraSmall),
                      _SiteNoteRow(
                        icon: Icons.person_outline_rounded,
                        label: 'contact_person'.tr,
                        value: _formatContactPerson(address),
                      ),
                    ],
                  ],
                ),
              ),
            ],
          ),
        );
      },
    );
  }

  String _buildAddress(ServiceAddress address) {
    final parts = <String>[
      if (address.address?.isNotEmpty == true) address.address!,
      if (address.street?.isNotEmpty == true) address.street!,
      if (address.city?.isNotEmpty == true) address.city!,
      if (address.country?.isNotEmpty == true) address.country!,
    ];
    return parts.join(', ');
  }

  String _formatContactPerson(ServiceAddress? address) {
    final name = address?.contactPersonName ?? '';
    final phone = address?.contactPersonNumber ?? '';
    if (name.isNotEmpty && phone.isNotEmpty) return '$name · $phone';
    if (name.isNotEmpty) return name;
    return phone;
  }

  Widget _buildNoNotes(BuildContext context) {
    return Container(
      margin: const EdgeInsets.symmetric(
        horizontal: Dimensions.paddingSizeDefault,
        vertical: Dimensions.paddingSizeSmall,
      ),
      padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
      decoration: BoxDecoration(
        color: Theme.of(context).primaryColor.withValues(alpha: 0.04),
        borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
        border: Border.all(
          color: Theme.of(context).primaryColor.withValues(alpha: 0.15),
        ),
      ),
      child: Row(
        children: [
          Icon(Icons.home_work_rounded,
              color: Theme.of(context).primaryColor.withValues(alpha: 0.5),
              size: 18),
          const SizedBox(width: Dimensions.paddingSizeExtraSmall),
          Text(
            'no_site_notes'.tr,
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
    );
  }
}

class _SiteNoteRow extends StatelessWidget {
  final IconData icon;
  final String label;
  final String value;

  const _SiteNoteRow(
      {required this.icon, required this.label, required this.value});

  @override
  Widget build(BuildContext context) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Icon(icon,
            size: 15,
            color: Theme.of(context)
                .textTheme
                .bodyLarge
                ?.color
                ?.withValues(alpha: 0.45)),
        const SizedBox(width: Dimensions.paddingSizeExtraSmall),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                label,
                style: robotoMedium.copyWith(
                  fontSize: Dimensions.fontSizeSmall,
                  color: Theme.of(context)
                      .textTheme
                      .bodyLarge
                      ?.color
                      ?.withValues(alpha: 0.55),
                ),
              ),
              Text(
                value,
                style: robotoRegular.copyWith(
                  fontSize: Dimensions.fontSizeSmall,
                  color: Theme.of(context)
                      .textTheme
                      .bodyLarge
                      ?.color
                      ?.withValues(alpha: 0.8),
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }
}
