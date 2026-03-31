import 'package:demandium/feature/conversation/binding/conversation_binding.dart';
import 'package:demandium/feature/conversation/controller/conversation_controller.dart';
import 'package:demandium/feature/conversation/view/conversation_list_screen.dart';
import 'package:demandium/feature/wallet/binding/wallet_binding.dart';
import 'package:demandium/feature/wallet/wallet_screen.dart';
import 'package:demandium/utils/core_export.dart';
import 'package:get/get.dart';

class BottomNavScreen extends StatefulWidget {
  final AddressModel ? previousAddress;
  final bool showServiceNotAvailableDialog;
  final int pageIndex;
  const  BottomNavScreen({super.key, required this.pageIndex, this.previousAddress, required this.showServiceNotAvailableDialog});

  @override
  State<BottomNavScreen> createState() => _BottomNavScreenState();
}

class _BottomNavScreenState extends State<BottomNavScreen> {
  int _pageIndex = 0;
  bool _canExit = GetPlatform.isWeb ? true : false;

  @override
  void initState() {
    super.initState();
    _pageIndex = widget.pageIndex;

    if(_pageIndex==1){
      Get.find<BottomNavController>().changePage(BnbItem.bookings, shouldUpdate: false);
    }else if(_pageIndex==2){
      Get.find<BottomNavController>().changePage(BnbItem.messages, shouldUpdate: false);
    }
    else if(_pageIndex==3){
      Get.find<BottomNavController>().changePage(BnbItem.payments, shouldUpdate: false);
    }else{
      Get.find<BottomNavController>().changePage(BnbItem.home, shouldUpdate: false);
    }
  }

  @override
  Widget build(BuildContext context) {

    final padding = MediaQuery.of(context).padding;
    bool isUserLoggedIn = Get.find<AuthController>().isLoggedIn();

    return CustomPopScopeWidget(
      canPop: ResponsiveHelper.isWeb() ? true : false,
      onPopInvoked: () {
        if (Get.find<BottomNavController>().currentPage != BnbItem.home) {
          Get.find<BottomNavController>().changePage(BnbItem.home);
        } else {
          if (_canExit) {
            if(!GetPlatform.isWeb) {
              exit(0);
            }
          } else {
            customSnackBar('back_press_again_to_exit'.tr, type : ToasterMessageType.info);
            _canExit = true;
            Timer(const Duration(seconds: 2), () {
              _canExit = false;
            });
          }
        }
      },

      child: Scaffold(
        bottomNavigationBar: ResponsiveHelper.isDesktop(context) ? const SizedBox() : Container(
          padding: EdgeInsets.only(
            top: Dimensions.paddingSizeDefault,
            bottom: padding.bottom > 15 ? 0 : Dimensions.paddingSizeDefault,
          ),
          color:Get.isDarkMode ? Theme.of(context).cardColor.withValues(alpha: .5) : Theme.of(context).primaryColor,
          child: SafeArea(
            child: Padding( padding: const EdgeInsets.symmetric(horizontal: Dimensions.paddingSizeExtraSmall),
              child: Row(children: [

                _bnbItem(
                  icon: Images.home, label: 'home', bnbItem: BnbItem.home, context: context,
                  onTap: () => Get.find<BottomNavController>().changePage(BnbItem.home),
                ),

                _bnbItem(
                  icon: Images.bookings, label: 'bookings', bnbItem: BnbItem.bookings, context: context,
                  onTap: () {
                    if (!isUserLoggedIn && Get.find<SplashController>().configModel.content?.guestCheckout == 1) {
                      Get.toNamed(RouteHelper.getTrackBookingRoute());
                    } else  if(!isUserLoggedIn){
                      Get.toNamed(RouteHelper.getNotLoggedScreen("booking","my_bookings"));
                    } else {
                      Get.find<BottomNavController>().changePage(BnbItem.bookings);
                    }
                  },
                ),

                _bnbItem(
                  icon: Images.chatImage, label: 'messages', bnbItem: BnbItem.messages, context: context,
                  onTap: () {
                    if (!isUserLoggedIn) {
                      Get.toNamed(RouteHelper.getNotLoggedScreen(RouteHelper.chatInbox, "messages"));
                    } else {
                      Get.find<BottomNavController>().changePage(BnbItem.messages);
                    }
                  },
                ),

                _bnbItem(
                  icon: Images.walletMenu, label: 'payments', bnbItem: BnbItem.payments, context: context,
                  onTap: () {
                    if (!isUserLoggedIn) {
                      Get.toNamed(RouteHelper.getNotLoggedScreen(RouteHelper.myWallet, "payments"));
                    } else {
                      Get.find<BottomNavController>().changePage(BnbItem.payments);
                    }
                  },
                ),

                _bnbItem(
                  icon: Images.menu, label: 'more', bnbItem: BnbItem.more, context: context,
                  onTap: () => Get.bottomSheet(const MenuScreen(),
                    backgroundColor: Colors.transparent, isScrollControlled: true,
                  ),
                ),
              ]),
            ),
          ),
        ),

        body: GetBuilder<BottomNavController>(builder: (navController){
          return _bottomNavigationView(widget.previousAddress, widget.showServiceNotAvailableDialog);
        }),

      ),
    );
  }

  Widget _bnbItem({required String icon, required String label, required BnbItem bnbItem, required GestureTapCallback onTap, context}) {
    return GetBuilder<BottomNavController>(builder: (bottomNavController){
      return Expanded(
        child: InkWell(
          onTap: onTap,
          child: Column(mainAxisAlignment: MainAxisAlignment.center, mainAxisSize: MainAxisSize.min, children: [

            Image.asset(icon, width: 18, height: 18,
              color: Get.find<BottomNavController>().currentPage == bnbItem ? Colors.white : Colors.white60,
            ),
            const SizedBox(height: Dimensions.paddingSizeExtraSmall),

            Text(label.tr,
              style: robotoRegular.copyWith( fontSize: Dimensions.fontSizeSmall,
                color: Get.find<BottomNavController>().currentPage == bnbItem ? Colors.white : Colors.white60,
              ),
            ),

          ]),
        ),
      );
    });
  }

  Widget _bottomNavigationView(AddressModel? previousAddress, bool showServiceNotAvailableDialog) {
    PriceConverter.getCurrency();
    switch (Get.find<BottomNavController>().currentPage) {
      case BnbItem.home:
        return HomeScreen(addressModel: previousAddress, showServiceNotAvailableDialog: showServiceNotAvailableDialog,);
      case BnbItem.bookings:
        if (!Get.find<AuthController>().isLoggedIn()) {
          return HomeScreen(addressModel: previousAddress, showServiceNotAvailableDialog: false);
        }
        return const BookingListScreen();
      case BnbItem.messages:
        if (!Get.find<AuthController>().isLoggedIn()) {
          return HomeScreen(addressModel: previousAddress, showServiceNotAvailableDialog: false);
        }
        if (!Get.isRegistered<ConversationController>()) {
          ConversationBinding().dependencies();
        }
        return const ConversationListScreen();
      case BnbItem.payments:
        if (!Get.find<AuthController>().isLoggedIn()) {
          return HomeScreen(addressModel: previousAddress, showServiceNotAvailableDialog: false);
        }
        if (!Get.isRegistered<WalletController>()) {
          WalletBinding().dependencies();
        }
        return const WalletScreen(status: null);
      case BnbItem.more:
        return HomeScreen(addressModel: previousAddress, showServiceNotAvailableDialog: false);
    }
  }
}
