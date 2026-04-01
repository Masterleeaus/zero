import 'package:demandium_serviceman/utils/core_export.dart';
import 'package:get/get.dart';

/// Dedicated checklist execution surface (bottom nav tab 3).
/// Shows active/accepted jobs with a "Start Checklist" launcher.
/// When a real checklist API is available this screen should load
/// checklist data directly; for now it surfaces checklists from job details.
class ChecklistsScreen extends StatefulWidget {
  const ChecklistsScreen({super.key});

  @override
  State<ChecklistsScreen> createState() => _ChecklistsScreenState();
}

class _ChecklistsScreenState extends State<ChecklistsScreen> {

  @override
  void initState() {
    super.initState();
    // Ensure booking list is loaded
    Get.find<BookingRequestController>()
        .getBookingList('accepted', 1);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Theme.of(context).colorScheme.surface,
      appBar: MainAppBar(
        title: 'checklists'.tr,
        color: Theme.of(context).primaryColor,
      ),
      body: GetBuilder<BookingRequestController>(
        builder: (controller) {
          if (controller.isFirst) {
            return const Center(child: CircularProgressIndicator());
          }

          // Collect all jobs that a cleaner can run checklists for
          List<BookingRequestModel> activeJobs = controller.bookingList
              .where((b) =>
                  b.bookingStatus == 'accepted' ||
                  b.bookingStatus == 'ongoing')
              .toList();

          if (activeJobs.isEmpty) {
            return _buildEmptyState(context);
          }

          return RefreshIndicator(
            onRefresh: () async =>
                controller.getBookingList('accepted', 1),
            backgroundColor: Theme.of(context).colorScheme.surface,
            color: Theme.of(context).primaryColor,
            child: ListView.builder(
              padding: const EdgeInsets.all(Dimensions.paddingSizeSmall),
              itemCount: activeJobs.length,
              itemBuilder: (context, index) =>
                  _ChecklistJobCard(booking: activeJobs[index]),
            ),
          );
        },
      ),
    );
  }

  Widget _buildEmptyState(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(Dimensions.paddingSizeExtraLarge),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              Icons.checklist_rounded,
              size: 64,
              color: Theme.of(context).primaryColor.withValues(alpha: 0.35),
            ),
            const SizedBox(height: Dimensions.paddingSizeDefault),
            Text(
              'no_checklist_available'.tr,
              style: robotoMedium.copyWith(
                fontSize: Dimensions.fontSizeLarge,
                color: Theme.of(context)
                    .textTheme
                    .bodyLarge
                    ?.color
                    ?.withValues(alpha: 0.7),
              ),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: Dimensions.paddingSizeSmall),
            Text(
              'checklist_job_hint'.tr,
              style: robotoRegular.copyWith(
                fontSize: Dimensions.fontSizeDefault,
                color: Theme.of(context)
                    .textTheme
                    .bodyLarge
                    ?.color
                    ?.withValues(alpha: 0.5),
              ),
              textAlign: TextAlign.center,
            ),
          ],
        ),
      ),
    );
  }
}

class _ChecklistJobCard extends StatelessWidget {
  final BookingRequestModel booking;
  const _ChecklistJobCard({required this.booking});

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.symmetric(
          vertical: Dimensions.paddingSizeExtraSmall),
      padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
      decoration: BoxDecoration(
        color: Theme.of(context).cardColor,
        borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
        boxShadow: Get.find<ThemeController>().darkTheme ? null : lightShadow,
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(Icons.checklist_rounded,
                  color: Theme.of(context).primaryColor, size: 20),
              const SizedBox(width: Dimensions.paddingSizeExtraSmall),
              Expanded(
                child: Text(
                  '${'booking'.tr} # ${booking.readableId ?? ""}',
                  style: robotoBold.copyWith(
                      fontSize: Dimensions.fontSizeDefault),
                ),
              ),
              if (booking.isRepeatBooking == 1)
                Container(
                  decoration: const BoxDecoration(
                      shape: BoxShape.circle, color: Colors.green),
                  padding: const EdgeInsets.all(2),
                  margin: const EdgeInsets.only(
                      right: Dimensions.paddingSizeExtraSmall),
                  child: const Icon(Icons.repeat,
                      color: Colors.white, size: 10),
                ),
              Container(
                padding: const EdgeInsets.symmetric(
                  vertical: Dimensions.paddingSizeExtraSmall,
                  horizontal: Dimensions.paddingSizeSmall,
                ),
                decoration: BoxDecoration(
                  color:
                      Theme.of(context).primaryColor.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(50),
                ),
                child: Text(
                  (booking.bookingStatus ?? '').tr,
                  style: robotoMedium.copyWith(
                    fontSize: Dimensions.fontSizeSmall,
                    color: Theme.of(context).primaryColor,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: Dimensions.paddingSizeSmall),
          if (booking.subCategory?.name != null)
            Text(
              booking.subCategory!.name!,
              style: robotoRegular.copyWith(
                fontSize: Dimensions.fontSizeSmall,
                color: Theme.of(context)
                    .textTheme
                    .bodyLarge
                    ?.color
                    ?.withValues(alpha: 0.6),
              ),
            ),
          if (booking.serviceSchedule != null) ...[
            const SizedBox(height: Dimensions.paddingSizeExtraSmall),
            Row(
              children: [
                Icon(Icons.calendar_today_rounded,
                    size: 14,
                    color: Theme.of(context)
                        .textTheme
                        .bodyLarge
                        ?.color
                        ?.withValues(alpha: 0.5)),
                const SizedBox(width: Dimensions.paddingSizeExtraSmall),
                Text(
                  DateConverter.dateMonthYearTime(
                      DateTime.tryParse(booking.serviceSchedule!)),
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
          ],
          const SizedBox(height: Dimensions.paddingSizeSmall),
          Row(
            children: [
              Expanded(
                child: OutlinedButton.icon(
                  onPressed: () => Get.toNamed(
                    RouteHelper.getBookingDetailsRoute(
                      bookingId: booking.id ?? '',
                      isSubBooking: false,
                    ),
                  ),
                  icon: const Icon(Icons.play_arrow_rounded, size: 18),
                  label: Text('start_checklist'.tr),
                  style: OutlinedButton.styleFrom(
                    foregroundColor: Theme.of(context).primaryColor,
                    side:
                        BorderSide(color: Theme.of(context).primaryColor),
                    shape: RoundedRectangleBorder(
                      borderRadius:
                          BorderRadius.circular(Dimensions.radiusDefault),
                    ),
                  ),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}
