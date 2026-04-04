import 'package:demandium_serviceman/feature/dashboard/widgets/booking_statistics_widget.dart';
import 'package:get/get.dart';
import 'package:demandium_serviceman/utils/core_export.dart';


class DashBoardScreen extends StatefulWidget {
  const DashBoardScreen({super.key}) ;
  @override
  State<DashBoardScreen> createState() => _DashBoardScreenState();
}
class _DashBoardScreenState extends State<DashBoardScreen> {

  void _loadData(){
    Get.find<DashboardController>().getDashboardData(reload: false);
    Get.find<DashboardController>().getBookingStatisticData(isReload: true);
    Get.find<UserController>().getUserInfo();
    Get.find<DashboardController>().changeToYearlyEarnStatisticsChart(EarningType.monthly);
    Get.find<DashboardController>().getMonthlyBookingsDataForChart(
      DateConverter.stringYear(DateTime.now()),DateTime.now().month.toString(),
      isRefresh: true
    );
    Get.find<DashboardController>().getYearlyBookingsDataForChart(
      DateConverter.stringYear(DateTime.now()),
      isRefresh: true
    );
    Get.find<NotificationController>().getNotifications(1,saveNotificationCount: false);
  }

  @override
  void initState() {
    super.initState();
    Get.find<UserController>().getUserInfo();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar:  MainAppBar(
        color: Theme.of(context).primaryColor,
        title: AppConstants.appName,
        titleFontSize: Dimensions.fontSizeOverLarge,
      ),
      body: RefreshIndicator(
        backgroundColor: Theme.of(context).colorScheme.surface,
        color: Theme.of(context).textTheme.bodyLarge!.color!.withValues(alpha:0.6),
        onRefresh: () async {
          _loadData();
        },
        child: SingleChildScrollView(
          physics: const ClampingScrollPhysics(
            parent: AlwaysScrollableScrollPhysics()
          ),
          child: GetBuilder<DashboardController>(
            builder: (dashboardController){
              return dashboardController.isLoading ?
              const DashboardTopCardShimmer() :

              const Column(
                children:[
                  SizedBox(height: Dimensions.paddingSizeSmall),

                  BusinessSummerySection(),
                  SizedBox(height: Dimensions.paddingSizeSmall),

                  BookingStatisticsWidget(),
                  SizedBox(height: Dimensions.paddingSizeSmall),

                  RecentActivitySection(),
                  SizedBox(height: Dimensions.paddingSizeDefault,)
                ],
              );
            },
          ),
        ),
      )
    );
  }
}
