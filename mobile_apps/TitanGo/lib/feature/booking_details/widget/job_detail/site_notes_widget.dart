import 'package:demandium_serviceman/utils/core_export.dart';
import 'package:get/get.dart';

/// Site Notes / Property Memory block displayed near the top of Job Details.
/// Shows address, contact info, and on-site operational instructions pulled
/// from the booking's service address.
/// TODO: Extend to a dedicated property_notes API endpoint once the backend
/// exposes per-site memory fields (alarm hints, parking, pet warnings, etc.).
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
        final hasLabel = address?.addressLabel?.isNotEmpty == true;
        final hasCity = address?.city?.isNotEmpty == true;
        final hasCountry = address?.country?.isNotEmpty == true;

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
                        value: _buildAddress(address!),
                      ),
                    ],
                    if (hasCity || hasCountry) ...[
                      const SizedBox(
                          height: Dimensions.paddingSizeExtraSmall),
                      _SiteNoteRow(
                        icon: Icons.map_outlined,
                        label: 'site_area'.tr,
                        value: [
                          if (hasCity) address!.city!,
                          if (hasCountry) address!.country!,
                        ].join(', '),
                      ),
                    ],
                    if (hasLabel) ...[
                      const SizedBox(
                          height: Dimensions.paddingSizeExtraSmall),
                      _SiteNoteRow(
                        icon: Icons.vpn_key_outlined,
                        label: 'entry_instructions'.tr,
                        value: address!.addressLabel!,
                        highlight: true,
                      ),
                    ],
                    if (hasContact) ...[
                      const SizedBox(
                          height: Dimensions.paddingSizeExtraSmall),
                      _SiteNoteRow(
                        icon: Icons.person_outline_rounded,
                        label: 'site_contact'.tr,
                        value: _formatContactPerson(address),
                      ),
                    ],
                    // Placeholder rows for future backend-sourced fields.
                    // These render only when the backend provides the data.
                    // TODO: bind to PropertyMemory API fields when available.
                    _buildPlaceholderRows(context),
                  ],
                ),
              ),
            ],
          ),
        );
      },
    );
  }

  /// Placeholder rows for upcoming backend fields.
  /// Shown as subtle reminders; gracefully hidden until data exists.
  Widget _buildPlaceholderRows(BuildContext context) {
    return const SizedBox.shrink();
    // TODO: uncomment and bind to real data when PropertyMemory API is ready:
    // _SiteNoteRow(icon: Icons.alarm_rounded, label: 'alarm_hints'.tr, value: ...),
    // _SiteNoteRow(icon: Icons.local_parking_rounded, label: 'parking_notes'.tr, value: ...),
    // _SiteNoteRow(icon: Icons.pets_rounded, label: 'pet_warning'.tr, value: ...),
    // _SiteNoteRow(icon: Icons.sticky_note_2_outlined, label: 'special_site_notes'.tr, value: ...),
    // _SiteNoteRow(icon: Icons.warning_amber_rounded, label: 'access_warnings'.tr, value: ...),
  }

  String _buildAddress(ServiceAddress address) {
    final parts = <String>[
      if (address.address?.isNotEmpty == true) address.address!,
      if (address.street?.isNotEmpty == true) address.street!,
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
  final bool highlight;

  const _SiteNoteRow({
    required this.icon,
    required this.label,
    required this.value,
    this.highlight = false,
  });

  @override
  Widget build(BuildContext context) {
    final valueColor = highlight
        ? Theme.of(context).primaryColor
        : Theme.of(context).textTheme.bodyLarge?.color?.withValues(alpha: 0.8);
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Icon(icon,
            size: 15,
            color: highlight
                ? Theme.of(context).primaryColor.withValues(alpha: 0.8)
                : Theme.of(context)
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
                style: (highlight ? robotoBold : robotoRegular).copyWith(
                  fontSize: Dimensions.fontSizeSmall,
                  color: valueColor,
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }
}
