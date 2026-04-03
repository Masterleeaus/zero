import 'package:get/get.dart';
import 'package:demandium_provider/util/core_export.dart';


class AuthRepo {
  final ApiClient apiClient;
  final SharedPreferences sharedPreferences;
  AuthRepo({required this.apiClient, required this.sharedPreferences});

  Future<Response?> registration({
    required SignUpBody signUpBody,
    required List<MultipartBody> identityImage,
    required XFile profileImage,
    required XFile coverImage,
  }) async {

    final List<MultipartBody> imageList = identityImage.toList();
    imageList.add(MultipartBody('cover_image', coverImage));

    return await apiClient.postMultipartData(AppConstants.registerUri, signUpBody.toJson(), imageList, MultipartBody('logo', profileImage),);
  }

  Future<Response?> login({required String emailOrPassword, required String password, required String type}) async {
    return await apiClient.postData(AppConstants.loginUri, {"email_or_phone": emailOrPassword, "password": password, "type": type});
  }

  Future<Response> deleteUser() async {
    return await apiClient.deleteData(AppConstants.providerRemove);
  }


  Future<Response> sendOtpForVerificationScreen(String identity, String type) async {
    return await apiClient.postData(AppConstants.sendOtpForVerification, {
      "identity": identity,
      "identity_type": type,
      "check_user": "1"
    });
  }

  Future<Response> verifyOtpForFirebaseOtpLogin({String? session, String? phone, String? code }) async {
    return await apiClient.postData(AppConstants.firebaseOtpVerify,
      {
        "sessionInfo": session,
        'phoneNumber': phone,
        'code': code,
        "user_type" : "provider-admin"
      },
    );
  }

  Future<Response> sendOtpForForgetPassword(String identity, String identityType) async {
    return await apiClient.postData(AppConstants.sendOtpForForgetPassword,
      {
        "identity": identity,
        "identity_type": identityType
      },

    );
  }

  Future<Response?> verifyOtpForVerificationScreen(String? identity,String identityType, String otp) async {
    return await apiClient.postData(AppConstants.verifyOtpForVerificationScreen,
        {
          "identity": identity,
          'otp':otp,
          "identity_type": identityType,
        },
    );
  }

  Future<Response> verifyOtpForForgetPassword(String identity, String identityType, String otp) async {
    return await apiClient.postData(AppConstants.verifyOtpForForgetPasswordScreen,
      {
        "identity": identity,
        'otp':otp,
        "identity_type": identityType
      },
    );
  }

  Future<Response?> resetPassword(String identity, String identityType, String otp, String password, String confirmPassword,  int isFirebaseOtp) async {
    return await apiClient.putData(
      AppConstants.resetPasswordUri,
      {
        "_method": "put",
        "identity": identity,
        "identity_type": identityType,
        "otp": otp,
        "password": password,
        "confirm_password": confirmPassword,
        "is_firebase_otp": isFirebaseOtp
      },
    );
  }

  Future<Response?> updateToken() async {

    String? deviceToken;
    if (GetPlatform.isIOS) {
      FirebaseMessaging.instance.setForegroundNotificationPresentationOptions(alert: true, badge: true, sound: true);
      NotificationSettings settings = await FirebaseMessaging.instance.requestPermission(
        alert: true, announcement: false, badge: true, carPlay: false,
        criticalAlert: false, provisional: false, sound: true,
      );
      if(settings.authorizationStatus == AuthorizationStatus.authorized) {
        deviceToken = await _saveDeviceToken();
      }
    }else {
      deviceToken = await _saveDeviceToken();
    }

    FirebaseMessaging.instance.subscribeToTopic(AppConstants.topic);
    FirebaseMessaging.instance.subscribeToTopic('${AppConstants.topic}-${Get.find<UserProfileController>().myZoneId}');
    return await apiClient.postData(AppConstants.tokenUrl, {"_method": "put", "fcm_token": deviceToken});
  }

  Future<String?> _saveDeviceToken() async {
    String? deviceToken = '@';
    if(!GetPlatform.isWeb) {
      try {
        deviceToken = await FirebaseMessaging.instance.getToken();
      }catch(e) {
        if (kDebugMode) {
          print('token error : $e');
        }
      }
    }
    if (deviceToken != null) {
      if (kDebugMode) {
        print('--------Device Token---------- $deviceToken');
      }
    }
    return deviceToken;
  }

  Future<void> unsubscribeToken() async {
    if(GetPlatform.isAndroid) {
      FirebaseMessaging.instance.unsubscribeFromTopic('${AppConstants.topic}-${Get.find<UserProfileController>().myZoneId}');
      apiClient.postData(AppConstants.tokenUrl, {"_method": "put", "fcm_token": "@"});
    }
  }


  Future<bool?> saveUserToken(String token) async {
    apiClient.token = token;
    apiClient.updateHeader(token, sharedPreferences.getString(AppConstants.languageCode));
    return await sharedPreferences.setString(AppConstants.token, token);
  }

  String getUserToken() {
    return sharedPreferences.getString(AppConstants.token) ?? "";
  }

  bool isLoggedIn() {
    return sharedPreferences.containsKey(AppConstants.token);
  }

  bool clearSharedData() {
    if(GetPlatform.isAndroid) {
      FirebaseMessaging.instance.unsubscribeFromTopic(AppConstants.topic);
      FirebaseMessaging.instance.unsubscribeFromTopic('${AppConstants.topic}-${Get.find<UserProfileController>().myZoneId}');
      apiClient.postData(AppConstants.tokenUrl, {"_method": "put", "fcm_token": "@"});
    }
    sharedPreferences.remove(AppConstants.token);
    sharedPreferences.remove(AppConstants.userAddress);
    apiClient.token = null;
    apiClient.updateHeader(null, null);
    return true;
  }
  Future<void> saveUserNumberAndPassword(String number, String password) async {
    try {
      await sharedPreferences.setString(AppConstants.userPassword, password);
      await sharedPreferences.setString(AppConstants.userNumber, number);
    } catch (e) {
      rethrow;
    }
  }

  void toggleNotificationSound(bool isNotification){
    sharedPreferences.setBool(AppConstants.notification, isNotification);
  }

  bool isNotificationActive() {
    return sharedPreferences.getBool(AppConstants.notification) ?? true;
  }

  Future<bool> clearUserNumberAndPassword() async {
    await sharedPreferences.remove(AppConstants.userPassword);
    return await sharedPreferences.remove(AppConstants.userNumber);
  }

  Future<Response?> getZonesDataList() async {
    return await apiClient.getData('${AppConstants.zoneUrl}?limit=200&offset=1');
  }

  String getUserNumber() {
    return sharedPreferences.getString(AppConstants.userNumber) ?? "";
  }

  String getUserPassword() {
    return sharedPreferences.getString(AppConstants.userPassword) ?? "";
  }

  void setRememberMeValue(bool rememberMeValue) {
    sharedPreferences.setBool(AppConstants.isRememberActive, rememberMeValue);
  }
  bool? getRememberMeValue() {
    return sharedPreferences.getBool(AppConstants.isRememberActive);
  }
}
