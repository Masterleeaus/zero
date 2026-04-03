import 'package:demandium/utils/core_export.dart';
import 'package:get/get.dart';

class WalletScreen extends StatefulWidget {
  final String? status;
  final String? token;
  final String? fromNotification;
  const WalletScreen({super.key, required this.status, this.token, this.fromNotification}) ;
  @override
  State<WalletScreen> createState() => _WalletScreenState();
}

class _WalletScreenState extends State<WalletScreen> {

  final tooltipController = JustTheController();

  @override
  void initState() {
    super.initState();
    Get.find<WalletController>().getWalletTransactionData(1);
    Get.find<WalletController>().getBonusList(false);
    Get.find<WalletController>().insertFilterList();


    Future.delayed(const Duration(seconds: 1)).then((value){
      if(widget.status != null && widget.status!.contains("success") && Get.find<WalletController>().getWalletAccessToken() != widget.token){
        customSnackBar("message", customWidget: Row(children: [
          const SizedBox(width: Dimensions.paddingSizeDefault,),
          const Icon(Icons.check_circle, color: Colors.white70,),
          const SizedBox(width: Dimensions.paddingSizeDefault,),
          Text("fund_added_successfully".tr, style: robotoRegular.copyWith(color: Colors.white70,),)
        ]), borderRadius: Dimensions.radiusExtraMoreLarge
        );
      }
    }).then((value) {
      Get.find<WalletController>().setWalletAccessToken(widget.token ?? "");
    });

  }
  @override
  Widget build(BuildContext context) {
    final ScrollController scrollController = ScrollController();
    return Scaffold(
      endDrawer : ResponsiveHelper.isDesktop(context) ? const MenuDrawer() : null,

      appBar: CustomAppBar(title: 'payments_center'.tr, actionWidget: InkWell(
        onTap: () {
          showGeneralDialog(
            context: context, barrierDismissible: true, transitionDuration: const Duration(milliseconds: 500),
            barrierLabel: MaterialLocalizations.of(context).dialogLabel, barrierColor: Colors.black.withValues(alpha: 0.5),
            pageBuilder: (context, _, __) {
              return const WalletUsesManualDialog();
            },
            transitionBuilder: (context, animation, secondaryAnimation, child) {
              return SlideTransition( position: CurvedAnimation(parent: animation, curve: Curves.easeOut,
                ).drive(Tween<Offset>(begin: const Offset(0, -1.0), end: Offset.zero,)),
                child: child,
              );
            },
          );
        },
        child: Padding( padding: const EdgeInsets.fromLTRB(15,0, 15, 0),
          child: Image.asset(Images.info, width: 20, height: 20, color: Colors.white,),
        ),
      ),
        onBackPressed: (){
          if(widget.fromNotification == "fromNotification"){
            Get.offAllNamed(RouteHelper.getMainRoute("home"));
          }else{
            Get.back();
          }
        },
      ),

      body: RefreshIndicator(
        onRefresh: () async {
          Get.find<WalletController>().insertFilterList();
          Get.find<WalletController>().getWalletTransactionData(1, reload: true);
        },
        child: GetBuilder<WalletController>(
          builder: (walletController){
            return ResponsiveHelper.isDesktop(context) ? WalletScreenWeb (scrollController: scrollController, tooltipController: tooltipController,) :

            SingleChildScrollView(
              controller: scrollController,
              child: Column(children: [
                WalletTopCard(tooltipController: tooltipController,),
                const _BillingCenterQuickActions(),
                const WalletPromotionalBannerView(),
                WalletListView(scrollController: scrollController,),
              ],),
            );
        }),
      ),
    );
  }
}

/// Lifecycle billing centre quick-action row.
/// Promotes Pay Invoice, Payment History, Credits, Outstanding Balance.
class _BillingCenterQuickActions extends StatelessWidget {
  const _BillingCenterQuickActions();

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(
        horizontal: Dimensions.paddingSizeDefault,
        vertical: Dimensions.paddingSizeSmall,
      ),
      child: Row(
        children: [
          _BillingAction(
            icon: Icons.receipt_long_outlined,
            label: 'pay_invoice'.tr,
            onTap: () {
              // Scrolls the user to the active transaction list below
              customSnackBar('pay_invoice'.tr, type: ToasterMessageType.info);
            },
          ),
          const SizedBox(width: Dimensions.paddingSizeSmall),
          _BillingAction(
            icon: Icons.history_rounded,
            label: 'payment_history_title'.tr,
            onTap: () {
              customSnackBar('payment_history_title'.tr, type: ToasterMessageType.info);
            },
          ),
          const SizedBox(width: Dimensions.paddingSizeSmall),
          _BillingAction(
            icon: Icons.card_giftcard_outlined,
            label: 'credits_label'.tr,
            onTap: () {
              customSnackBar('credits_label'.tr, type: ToasterMessageType.info);
            },
          ),
        ],
      ),
    );
  }
}

class _BillingAction extends StatelessWidget {
  final IconData icon;
  final String label;
  final VoidCallback onTap;

  const _BillingAction({
    required this.icon,
    required this.label,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    final primary = Theme.of(context).colorScheme.primary;
    return Expanded(
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
        child: Container(
          padding: const EdgeInsets.symmetric(vertical: 10),
          decoration: BoxDecoration(
            color: primary.withValues(alpha: 0.07),
            borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
            border: Border.all(color: primary.withValues(alpha: 0.2)),
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(icon, size: 20, color: primary),
              const SizedBox(height: 4),
              Text(
                label,
                style: robotoMedium.copyWith(
                  fontSize: Dimensions.fontSizeExtraSmall,
                  color: primary,
                ),
                textAlign: TextAlign.center,
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
              ),
            ],
          ),
        ),
      ),
    );
  }
}
