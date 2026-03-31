
import 'package:demandium_provider/feature/tutorial/controller/tutorial_controller.dart';
import 'package:demandium_provider/feature/tutorial/widgets/tutorial_button_widget.dart';
import 'package:demandium_provider/util/core_export.dart';
import 'package:flutter/rendering.dart';
import 'package:get/get.dart';
import 'feature/nav/widgets/cash_overflow_dialog.dart';
import 'helper/get_di.dart';


FlutterLocalNotificationsPlugin flutterLocalNotificationsPlugin = FlutterLocalNotificationsPlugin();

AndroidNotificationChannel? channel1;
AndroidNotificationChannel? channel2;

Future<void> main() async {

  WidgetsFlutterBinding.ensureInitialized();

  try{
     if(Platform.isAndroid) {
      try {
        await Firebase.initializeApp(
          ///todo you need to configure that firebase Option with your own firebase to run your app
          ///Go to android/app/google-services.json and find those key and added in below
          options: const FirebaseOptions(
            apiKey: "AIzaSyBYMyaGbvQhVf6YIfH1TEVT56Zs83QASxg", ///current_key here
            appId: "1:889759666168:android:e0fa950568fe69a784d00d", ///mobilesdk_app_id here
            messagingSenderId: "889759666168", ///project_number here
            projectId: "demancms", ///project_id her
          ),
        );
      }catch(e) {
        await Firebase.initializeApp();

      }
    } else {
      await Firebase.initializeApp();
    }
  }catch(e) {
    if (kDebugMode) {
      print('Error initializing Flutter bindings: ${e.toString()}');
    }
  }

  await FlutterDownloader.initialize(debug: true, ignoreSsl: true);



  Map<String, Map<String, String>> languages = await init();
  NotificationBody? body;

  try {
    if (GetPlatform.isMobile) {
      final RemoteMessage? remoteMessage = await FirebaseMessaging.instance.getInitialMessage();
      if (remoteMessage != null) {
        body = NotificationHelper.convertNotification(remoteMessage.data);
      }
      await NotificationHelper.initialize(flutterLocalNotificationsPlugin);
      FirebaseMessaging.onBackgroundMessage(myBackgroundMessageHandler);
    }
  }catch(e) {
    if (kDebugMode) {
      print("");
    }
  }
  runApp(MyApp(languages: languages, body: body));
}

class MyApp extends StatelessWidget {
  final Map<String, Map<String, String>>? languages;
  final NotificationBody? body;
  const MyApp({super.key, required this.languages, required this.body});
  @override
  Widget build(BuildContext context) {
    return GetBuilder<ThemeController>(builder: (themeController) {
      return GetBuilder<LocalizationController>(builder: (localizeController) {
        return GetMaterialApp(
          routingCallback: (route){
            Get.find<TutorialController>().onChangeBottomSheetStatus((route?.isBottomSheet ?? false) || (route?.isDialog ?? false));
          },

          title: AppConstants.appName,
          debugShowCheckedModeBanner: false,
          navigatorKey: Get.key,
          theme: themeController.darkTheme ? dark : light,
          locale: localizeController.locale,
          translations: Messages(languages: languages),
          initialRoute: RouteHelper.getSplashRoute(body: body,),
          getPages: RouteHelper.routes,
          defaultTransition: Transition.fadeIn,
          transitionDuration: const Duration(milliseconds: 500),
          builder: (context, widget) => MediaQuery(
            data: MediaQuery.of(context).copyWith(textScaler: TextScaler.linear(MediaQuery.sizeOf(context).width < 380 ?  0.9 : 1)),
            child: _GlobalScrollListener(
              child: Material(
                child: SafeArea(
                  top: false,
                  bottom: GetPlatform.isAndroid,
                  child: Stack(children: [

                    widget!,

                    GetBuilder<UserProfileController>(builder: (userProfileController){

                      double receivableAmount = double.tryParse(userProfileController.providerModel?.content?.providerInfo?.owner?.account?.accountReceivable ?? "0" ) ?? 0;
                      double payableAmount = double.tryParse(userProfileController.providerModel?.content?.providerInfo?.owner?.account?.accountPayable ?? "0") ?? 0 ;

                      TransactionType transactionType =  userProfileController.getTransactionType(payableAmount, receivableAmount);
                      double transactionAmount =  userProfileController.getTransactionAmountAmount(payableAmount, receivableAmount);

                      double payablePercent =  userProfileController.providerModel != null ?
                      userProfileController.getOverflowPercent(payableAmount, receivableAmount, Get.find<SplashController>().configModel.content?.maxCashInHandLimit?? 0) : 0;

                      bool overFlowDialogStatus = userProfileController.showOverflowDialog && userProfileController.providerModel != null &&
                          Get.find<SplashController>().configModel.content?.suspendOnCashInHandLimit == 1 &&  Get.find<SplashController>().configModel.content?.digitalPayment == 1;

                      return  SafeArea(
                        child: Align(alignment: Alignment.bottomRight,
                          child: Padding(padding: const EdgeInsets.only(bottom: 90),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.end,
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                (transactionType == TransactionType.payable || transactionType == TransactionType.adjustAndPayable ||  transactionType == TransactionType.adjust)
                                    && ( payablePercent >= 80  && overFlowDialogStatus) && !userProfileController.trialWidgetNotShow
                                    ?  CashOverflowDialog(payablePercent: payablePercent,amount: transactionAmount,) : const SizedBox()
                              ],
                            ),
                          ),
                        ),
                      );
                    }),


                    TutorialButtonWidget(),



                  ]),
                ),
              ),
            ),
          ),
        );
      },
      );
    },
    );
  }
}




class _GlobalScrollListener extends StatelessWidget {
  final Widget child;

  const _GlobalScrollListener({required this.child});

  @override
  Widget build(BuildContext context) {
    bool isUserScrolling = false;

    return NotificationListener<ScrollNotification>(
      onNotification: (notification) {
        final tutorialController = Get.find<TutorialController>();

        if (notification is ScrollStartNotification) {
          tutorialController.setVisibility(false);

        }else if (notification is ScrollEndNotification) {
          tutorialController.setVisibility(true);
        }

        if (notification is UserScrollNotification) {
          isUserScrolling = notification.direction != ScrollDirection.idle;
        }

        if(notification.metrics.pixels >= notification.metrics.maxScrollExtent && isUserScrolling) {
          tutorialController.setVisibility(false);
        }

        return false;
      },
      child: child,
    );
  }
}