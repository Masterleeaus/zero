import 'package:demandium_serviceman/utils/core_export.dart';
import 'package:get/get.dart';
import 'helper/get_di.dart' as di;


final FlutterLocalNotificationsPlugin flutterLocalNotificationsPlugin = FlutterLocalNotificationsPlugin();

Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();

  if(GetPlatform.isAndroid) {
    try {
      await Firebase.initializeApp(
        ///todo you need to configure that firebase Option with your own firebase to run your app
        ///Go to android/app/google-services.json and find those key and added in below
        options: const FirebaseOptions(
          apiKey: "AIzaSyBYMyaGbvQhVf6YIfH1TEVT56Zs83QASxg", ///current_key here
          appId: "1:889759666168:android:f49fc09445e6d8f884d00d", ///mobilesdk_app_id here
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

  await FlutterDownloader.initialize(debug: true, ignoreSsl: true);


  Map<String, Map<String, String>> languages = await di.init();
  NotificationBody? body;

  try {
    await NotificationHelper.initialize(flutterLocalNotificationsPlugin);
    FirebaseMessaging.onBackgroundMessage(myBackgroundMessageHandler);

    if (GetPlatform.isMobile) {
      final RemoteMessage? remoteMessage = await FirebaseMessaging.instance.getInitialMessage();
      if (remoteMessage != null) {
        body = NotificationHelper.convertNotification(remoteMessage.data);
      }
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
          title: AppConstants.appName,
          debugShowCheckedModeBanner: false,
          navigatorKey: Get.key,
          theme: themeController.darkTheme ? dark : light,
          locale: localizeController.locale,
          translations: Messages(languages: languages),
          fallbackLocale: Locale(AppConstants.languages[0].languageCode!, AppConstants.languages[0].countryCode),
          initialRoute: RouteHelper.getSplashRoute(body : body),
          getPages: RouteHelper.routes,
          defaultTransition: Transition.topLevel,
          transitionDuration: const Duration(milliseconds: 500),
          builder: (context, widget) => MediaQuery(
            data: MediaQuery.of(context).copyWith(textScaler: TextScaler.linear(MediaQuery.sizeOf(context).width < 380 ?  0.9 : 1)),
            child: Material(
              child: SafeArea(
                top: false,
                bottom: GetPlatform.isAndroid,
                child: Stack(children: [
                  widget!,
                ]),
              ),
            ),
          ),
        );
      });
    });
  }
}
