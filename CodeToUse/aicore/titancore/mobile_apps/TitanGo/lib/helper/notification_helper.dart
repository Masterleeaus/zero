import 'dart:convert';
import 'dart:math';
import 'package:demandium_serviceman/common/widgets/demo_reset_dialog_widget.dart';
import 'package:demandium_serviceman/utils/core_export.dart';
import 'package:get/get.dart';
import 'package:http/http.dart' as http;


class NotificationHelper {
  static Future<void> initialize(FlutterLocalNotificationsPlugin flutterLocalNotificationsPlugin) async {
    var androidInitialize = const AndroidInitializationSettings('notification_icon');
    var iOSInitialize = const DarwinInitializationSettings();
    var initializationsSettings = InitializationSettings(android: androidInitialize, iOS: iOSInitialize);
    flutterLocalNotificationsPlugin.initialize(initializationsSettings, onDidReceiveNotificationResponse: (NotificationResponse? notificationResponse) async {
      try{
        PriceConverter.getCurrency(Get.context!);
        if(notificationResponse!.payload != null &&  notificationResponse.payload!=''){
          NotificationBody notificationBody = NotificationBody.fromJson(jsonDecode(notificationResponse.payload!));

          if (kDebugMode) {
            print("onMessageOpenApp: Notification Type => ${notificationBody.notificationType} / Title => ${notificationBody.title} / ${notificationBody.body}");
            print("onMessageOpenApp: Notification Body =>  ${notificationBody.toJson().toString()}");
          }

          if(notificationBody.notificationType=="chatting"){
            if(Get.currentRoute.contains(RouteHelper.chatScreen)){
              Get.back();
              Get.back();
            } else if(Get.currentRoute.contains(RouteHelper.chatInbox)){
              Get.back();
            }
            Get.toNamed(RouteHelper.getChatScreenRoute(
              notificationBody.channelId??"",
              notificationBody.userType == "supper-admin" ? "admin" : notificationBody.userName??"",
              notificationBody.userProfileImage??"",
              notificationBody.userPhone??"",
              notificationBody.userType??"",
              fromNotification: "fromNotification",
            ));
          }
          else if(notificationBody.notificationType=='booking' && notificationBody.bookingId!=null && notificationBody.bookingId!=''){
            Get.toNamed(RouteHelper.getBookingDetailsRoute(bookingId: notificationBody.bookingId!, fromPage: 'fromNotification', isSubBooking: notificationBody.bookingType == "repeat"));
          } else if(notificationBody.notificationType=='privacy_policy' && notificationBody.title!=null && notificationBody.title!=''){
            Get.toNamed(RouteHelper.getHtmlRoute(HtmlType.privacyPolicy.value));
          }else if(notificationBody.notificationType=='terms_and_conditions' && notificationBody.title!=null && notificationBody.title!=''){
            Get.toNamed(RouteHelper.getHtmlRoute(HtmlType.termsAndCondition.value));
          }else{
            Get.toNamed(RouteHelper.getNotificationRoute());
          }
        }
      }catch (e) {
        if (kDebugMode) {
          print("");
        }
      }
      return;
    });

    FirebaseMessaging.onMessage.listen((RemoteMessage message) {
      if (kDebugMode) {
        print("onMessage: Notification Type => ${message.data["type"]}/ Title => ${message.data['title']} ${message.notification?.title}/${message.notification?.body}/${message.notification?.titleLocKey}");
      }

      if(message.data['type']=='booking') {
        Get.find<BookingRequestController>().getBookingList(BooingListStatus.accepted.name.toLowerCase(), 1);
      }

      if(message.data['type']=='chatting'){

        if((message.data['channel_id']!="" && message.data['channel_id']!=null)){
          if( Get.currentRoute.contains(RouteHelper.chatScreen)
              && (message.data['channel_id'] == Get.find<ConversationController>().channelId) ){

            Get.find<ConversationController>().getConversation(message.data['channel_id'], 1);

          } else if(  Get.currentRoute.contains(RouteHelper.chatInbox) || Get.currentRoute.contains(RouteHelper.chatScreen) ){

            if (kDebugMode) {print("User Type : ${message.data['user_type']}");}

            NotificationHelper.showNotification(message, flutterLocalNotificationsPlugin, false);

            if(message.data['user_type'] == 'customer'){
              Get.find<ConversationController>().getChannelList(1);
            }else{
              Get.find<ConversationController>().getChannelList(1, type: "provider");
            }

          } else{
            NotificationHelper.showNotification(message,flutterLocalNotificationsPlugin,false);
          }


        } else{
          NotificationHelper.showNotification(message,flutterLocalNotificationsPlugin,false);
        }

      }
      else if(message.data['type']=='general'){
        NotificationHelper.showNotification(message,flutterLocalNotificationsPlugin,false);
        Get.find<NotificationController>().getNotifications(1);
      }
      else if((message.data['type']=='maintenance')){
        Get.find<SplashController>().getConfigData();
      }
      else if(message.data['type'] == 'demo_reset') {
        if(Get.find<SplashController>().configModel?.content?.appEnvironment == "demo"){
          Get.dialog(const DemoResetDialogWidget(), barrierDismissible: false);
        }
      }
      else{
        NotificationHelper.showNotification(message,flutterLocalNotificationsPlugin,false);
      }

    });
    FirebaseMessaging.onMessageOpenedApp.listen((RemoteMessage? message) {
      try{
        PriceConverter.getCurrency(Get.context!);
        if(message!=null && message.data.isNotEmpty) {
          NotificationBody notificationBody = convertNotification(message.data);
          if(notificationBody.notificationType=="chatting" && notificationBody.channelId!=""){

            if(Get.currentRoute.contains(RouteHelper.chatScreen)){
              Get.back();
              Get.back();
            } else if(Get.currentRoute.contains(RouteHelper.chatInbox)){
              Get.back();
            }

            Get.toNamed(RouteHelper.getChatScreenRoute(
              notificationBody.channelId??"",
              notificationBody.userType == "supper-admin" ? "admin" : notificationBody.userName??"",
              notificationBody.userProfileImage??"",
              notificationBody.userPhone??"",
              notificationBody.userType??"",
              fromNotification: "fromNotification",
            ));
          }

          else if(notificationBody.notificationType=='booking' && notificationBody.bookingId!=null && notificationBody.bookingId!=''){
            Get.toNamed(RouteHelper.getBookingDetailsRoute( bookingId: notificationBody.bookingId!,fromPage: 'fromNotification', isSubBooking: notificationBody.bookingType == "repeat"));
          }
          else if(notificationBody.notificationType=='privacy_policy' && notificationBody.title!=null && notificationBody.title!=''){
            Get.toNamed(RouteHelper.getHtmlRoute(HtmlType.privacyPolicy.value));
          }
          else if(notificationBody.notificationType=='terms_and_conditions' && notificationBody.title!=null && notificationBody.title!=''){
            Get.toNamed(RouteHelper.getHtmlRoute(HtmlType.termsAndCondition.value));
          }else{
            Get.toNamed(RouteHelper.getNotificationRoute());
          }
        }
      }catch (e) {
        if (kDebugMode) {
          print("");
        }
      }
    });
  }

  static Future<void> showNotification(RemoteMessage message, FlutterLocalNotificationsPlugin fln, bool data) async {
    if(!GetPlatform.isIOS) {
      String? title;
      String? body;
      String? image;
      String playLoad = jsonEncode(message.data);

      title = message.data['title']?.replaceAll('_', ' ').toString().capitalize;
      body = message.data['body'];
      image = (message.data['image'] != null && message.data['image'].isNotEmpty)
          ? message.data['image'].startsWith('http') ? message.data['image']
          : '${AppConstants.baseUrl}/storage/app/public/notification/${message.data['image']}' : null;

      if(image != null && image.isNotEmpty) {
        try{
          await showBigPictureNotificationHiddenLargeIcon(title!, body!, playLoad, image, fln);
        }catch(e) {
          await showBigTextNotification(title :title!, body: '',payload: playLoad, fln : fln);
        }
      }else {
        await showBigTextNotification(title : title ??"", body: '',payload: playLoad, fln : fln);
      }
    }
  }

  static Future<void> showBigTextNotification({required String title, required String body, required String payload, required FlutterLocalNotificationsPlugin fln}) async {
    BigTextStyleInformation bigTextStyleInformation = BigTextStyleInformation(
      body, htmlFormatBigText: true,
      contentTitle: title, htmlFormatContentTitle: true,
    );
    AndroidNotificationDetails androidPlatformChannelSpecifics = AndroidNotificationDetails(
      "demandium", AppConstants.appName, importance: Importance.max,
      styleInformation: bigTextStyleInformation, priority: Priority.max, playSound: true,
      sound: const RawResourceAndroidNotificationSound('notification'),
    );
    int randomNumber = Random().nextInt(100);
    NotificationDetails platformChannelSpecifics = NotificationDetails(android: androidPlatformChannelSpecifics);
    await fln.show(randomNumber, title, body, platformChannelSpecifics, payload: payload);
  }

  static Future<void> showBigPictureNotificationHiddenLargeIcon(String title, String body, String playLoad, String image, FlutterLocalNotificationsPlugin fln) async {
    final String largeIconPath = await _downloadAndSaveFile(image, 'largeIcon');
    final String bigPicturePath = await _downloadAndSaveFile(image, 'bigPicture');
    final BigPictureStyleInformation bigPictureStyleInformation = BigPictureStyleInformation(
      FilePathAndroidBitmap(bigPicturePath), hideExpandedLargeIcon: true,
      contentTitle: title, htmlFormatContentTitle: true,
      summaryText: body, htmlFormatSummaryText: true,
    );
    final AndroidNotificationDetails androidPlatformChannelSpecifics = AndroidNotificationDetails(
      "demandium", AppConstants.appName,
      largeIcon: FilePathAndroidBitmap(largeIconPath), priority: Priority.max, playSound: true,
      styleInformation: bigPictureStyleInformation, importance: Importance.max,
      sound: const RawResourceAndroidNotificationSound('notification'),
    );
    int randomNumber = Random().nextInt(100);
    final NotificationDetails platformChannelSpecifics = NotificationDetails(android: androidPlatformChannelSpecifics);
    await fln.show(randomNumber, title, body, platformChannelSpecifics, payload: playLoad);
  }

  static Future<String> _downloadAndSaveFile(String url, String fileName) async {
    final Directory directory = await getApplicationDocumentsDirectory();
    final String filePath = '${directory.path}/$fileName';
    final http.Response response = await http.get(Uri.parse(url));
    final File file = File(filePath);
    await file.writeAsBytes(response.bodyBytes);
    return filePath;
  }

  static NotificationBody convertNotification(Map<String, dynamic> data){
    return NotificationBody.fromJson(data);
  }


}

@pragma('vm:entry-point')
Future<dynamic> myBackgroundMessageHandler(RemoteMessage message) async {
  if (kDebugMode) {
    print("onBackground: ${message.notification?.title}/${message.notification?.body}/${message.notification?.titleLocKey}");
  }
}