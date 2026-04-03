import 'package:demandium_serviceman/utils/core_export.dart';
import 'package:get/get.dart';

class BottomNavScreen extends StatefulWidget {
  final int pageIndex;
  const BottomNavScreen({super.key, required this.pageIndex});

  static final GlobalKey<BottomNavScreenState> bottomNavKey = GlobalKey<BottomNavScreenState>();

  static void onChangesIndex(int index) => bottomNavKey.currentState?._setPage(index);



  @override
  BottomNavScreenState createState() => BottomNavScreenState();
}

class BottomNavScreenState extends State<BottomNavScreen> {

  void _loadData() async {
    await Get.find<DashboardController>().getDashboardData();
    Get.find<DashboardController>().getBookingStatisticData();
    Get.find<DashboardController>().getMonthlyBookingsDataForChart(DateConverter.stringYear(DateTime.now()),DateTime.now().month.toString());
    Get.find<DashboardController>().getYearlyBookingsDataForChart(DateConverter.stringYear(DateTime.now()));
    Get.find<BookingRequestController>().updateBookingStatusState(BooingListStatus.accepted);
    Get.find<BookingRequestController>().getBookingHistory('all',1);
    Get.find<BookingRequestController>().updateBookingHistorySelectedIndex(0);
    Get.find<LocalizationController>().filterLanguage(shouldUpdate: false);
    Get.find<ConversationController>().getChannelList(1, type: "customer");
    Get.find<ConversationController>().getChannelList(1, type: "provider");
    Get.find<AuthController>().updateToken();
  }

  PageController? _pageController;
  int _pageIndex = 0;
  List<Widget>? _screens;

  bool _canExit = GetPlatform.isWeb ? true : false;

  @override
  void initState() {
    super.initState();

    Future.delayed(const Duration(milliseconds: 200), (){
      _loadData();
    });

    _pageIndex = widget.pageIndex;
    _pageController = PageController(initialPage: widget.pageIndex);

    _screens = [
      const DashBoardScreen(),
      const BookingListScreen(),
      const BookingHistoryScreen(),
      const Text("More"),
    ];

    Future.delayed(const Duration(seconds: 1), () {
      setState(() {});
    });


  }

  @override
  Widget build(BuildContext context) {

    final padding = MediaQuery.of(context).padding;

    return CustomPopScopeWidget(
      onPopInvoked: (){
        if (_pageIndex != 0) {
          _setPage(0);
        } else {
          if(_canExit) {
            SystemNavigator.pop();
          }else {
            showCustomSnackBar('back_press_again_to_exit'.tr, type : ToasterMessageType.info);
            _canExit = true;
            Timer(const Duration(seconds: 2), () {
              _canExit = false;
            });
          }
        }
      },
      child: Scaffold(
        bottomNavigationBar: Container(
          padding: EdgeInsets.only(
            top: Dimensions.paddingSizeDefault,
            bottom: padding.bottom > 15 ? 0 : Dimensions.paddingSizeDefault,
          ),
          decoration: BoxDecoration(
              color: Get.isDarkMode?Theme.of(context).colorScheme.surface:Theme.of(context).primaryColor,
              boxShadow:[
                BoxShadow(
                  offset: const Offset(0, 1),
                  blurRadius: 5,
                  color: Theme.of(context).primaryColor.withValues(alpha:0.5),
                )]
          ),
          child: SafeArea(
            child: Padding(padding: const EdgeInsets.symmetric(horizontal: Dimensions.paddingSizeExtraSmall),
              child: Row(children: [
                _getBottomNavItem(0, Images.dashboard, 'dashboard'.tr),
                _getBottomNavItem(1, Images.requests, 'requests'.tr),
                _getBottomNavItem(2, Images.history, 'history'.tr),
                _getBottomNavItem(3, Images.moree, 'more'.tr),
              ]),
            ),
          ),
        ),
        body: PageView.builder(
          controller: _pageController,
          itemCount: _screens!.length,
          physics: const NeverScrollableScrollPhysics(),
          itemBuilder: (context, index) {
            return _screens![index];
          },
        ),
      ),
    );
  }

  void _setPage(int pageIndex) {
    if(pageIndex == 3) {
      Get.bottomSheet(const MenuScreen(), backgroundColor: Colors.transparent, isScrollControlled: true);
    }else {
      setState(() {
        _pageController!.jumpToPage(pageIndex);
        _pageIndex = pageIndex;
      });
    }
  }

  Widget _getBottomNavItem(int index, String icon, String title) {
    return Expanded(child: InkWell(
      onTap: () => _setPage(index),
      child: Column(mainAxisSize: MainAxisSize.min, children: [

        icon.isEmpty ? const SizedBox(width: 20, height: 20) : Image.asset(
            icon, width: 17, height: 17,
            color: _pageIndex == index ? Get.isDarkMode ? Theme.of(context).primaryColor : Colors.white : Colors.grey.shade400
        ),
        const SizedBox(height: Dimensions.paddingSizeExtraSmall),

        Text(title, style: robotoRegular.copyWith(
            fontSize: Dimensions.fontSizeSmall,
            color: _pageIndex == index ? Get.isDarkMode ? Theme.of(context).primaryColor : Colors.white : Colors.grey.shade400
        )),

      ]),
    ));
  }

}
