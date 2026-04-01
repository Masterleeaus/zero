import 'package:firebase_auth/firebase_auth.dart';
import 'package:get/get.dart';
import 'package:demandium_serviceman/utils/core_export.dart';

class AuthController extends GetxController implements GetxService {
  final AuthRepo authRepo;
  AuthController({required this.authRepo});

  bool? _isLoading = false;
  bool? get isLoading => _isLoading;

  bool _isActiveRememberMe = false;
  bool get isActiveRememberMe => _isActiveRememberMe;

  String _verificationCode = '';
  String get verificationCode => _verificationCode;

  bool _isWrongOtpSubmitted = false;
  bool get isWrongOtpSubmitted => _isWrongOtpSubmitted;


  Future<void> login(String phoneWithCountryCode,String countryCode,String phoneWithoutCountryCode, String password) async {

    _isLoading = true;
    update();

    Response? response = await authRepo.login(phone: phoneWithCountryCode, password: password);
    if (response!.statusCode == 200 && response.body['response_code']=="auth_login_200") {
     _saveDeviceTokenAndNavigate(
       response: response,
       phoneWithCountryCode: phoneWithCountryCode,
       password: password,
       countryCode: countryCode,
     );

    } else  if(response.statusCode == 401 && response.body['response_code']=="account_disabled_401"){
      showCustomSnackBar(response.body['message'].toString().capitalizeFirst ?? response.statusText, toasterTitle: "account_blocked_notice", icon: Images.userBlock);
    }
    else {
      showCustomSnackBar(response.body['message'].toString().capitalizeFirst ?? response.statusText );
    }
    _isLoading = false;
    update();
  }

  Future<void> _saveDeviceTokenAndNavigate({required Response response, required String  phoneWithCountryCode, required String  password, required String countryCode }) async {
    if (_isActiveRememberMe) {
      authRepo.saveUserNumberAndPassword(phoneWithCountryCode, password,countryCode);
    } else {
      authRepo.clearUserNumberAndPassword();
    }
    authRepo.saveUserToken(response.body['content']['token']);
    await Get.find<UserController>().getUserInfo();
    authRepo.updateToken();
    Get.find<SplashController>().updateLanguage(true);
    showCustomSnackBar('successfully_logged_in'.tr, type : ToasterMessageType.success);
    Get.offNamed(RouteHelper.getInitialRoute());
  }

  Future<ResponseModel?> sendVerificationCode({required String identity, required String identityType,required  SendOtpType type, bool resendOtp = false}) async {
    ResponseModel? responseModel;
    if(type == SendOtpType.firebase){
      _sendOtpForFirebaseVerification(identity: identity,identityType:  identityType, resendOtp: resendOtp);
    } else{
      responseModel = await _sendOtpForForgetPassword(identity, identityType);
    }
    return responseModel;
  }


  Future<ResponseModel> _sendOtpForForgetPassword(String identity, String identityType) async {
    _isLoading = true;
    update();
    Response response = await authRepo.sendOtpForForgetPassword(identity,identityType);

    if (response.statusCode == 200 && response.body["response_code"]=="default_200") {
      _isLoading = false;
      update();
      return ResponseModel(true, "");
    } else {
      _isLoading = false;
      update();
      return ResponseModel(false, response.body["message"] ?? response.statusText);
    }
  }

  Future<void> _sendOtpForFirebaseVerification({required String identity, required  String identityType, required bool resendOtp }) async {
    _isLoading = true;
    update();

    FirebaseAuth.instance.verifyPhoneNumber(
      phoneNumber: identity,
      verificationCompleted: (PhoneAuthCredential credential) {},
      verificationFailed: (FirebaseAuthException e) {
        _isLoading = false;
        update();

        if(e.code == 'invalid-phone-number') {
          showCustomSnackBar('please_submit_a_valid_phone_number', type: ToasterMessageType.info);

        }else{
          showCustomSnackBar('${e.message}'.replaceAll('_', ' ').capitalizeFirst);
        }

      },
      codeSent: (String vId, int? resendToken) {
        _isLoading = false;
        update();
        if(resendOtp){

        }else{
          Get.toNamed(RouteHelper.getVerificationRoute(identity:identity, identityType : identityType, firebaseSession: vId));
        }

      },
      codeAutoRetrievalTimeout: (String verificationId) {},
    );

  }


  Future<ResponseModel> verifyOtpForForgetPasswordScreen(String identity, String identityType, String otp) async {
    _isLoading = true;
    update();
    Response response = await authRepo.verifyOtpForForgetPassword(identity, identityType, otp);
    ResponseModel responseModel;
    if (response.statusCode==200 &&  response.body['response_code'] == 'default_200') {
      _isLoading = false;
      update();
      responseModel = ResponseModel(true, "successfully_verified");
    }else{
      responseModel = _checkWrongOtp(response);
    }
    _isLoading = false;
    update();
    return responseModel;

  }

  Future<ResponseModel>  verifyOtpForFirebaseOtp({String? session, String? phone, String? code }) async {
    _isLoading = true;
    update();
    Response response = await authRepo.verifyOtpForFirebaseOtpLogin(session: session, phone: phone, code: code);

    ResponseModel responseModel;
    if (response.statusCode == 200) {

      responseModel =  ResponseModel(true, "successfully_verified");
    } else {
      responseModel = _checkWrongOtp(response);
    }
    _isLoading = false;
    update();
    return responseModel;
  }

  Future<void> resetPassword(String identity,String identityType, String otp, String password, String confirmPassword, int isFirebaseOtp) async {
    _isLoading = true;
    update();
    Response? response = await authRepo.resetPassword(identity,identityType, otp, password, confirmPassword, isFirebaseOtp);

    if (response!.statusCode == 200 && response.body['response_code']=="default_password_reset_200") {
      Get.offNamed(RouteHelper.signIn);
      showCustomSnackBar('password_changed_successfully'.tr,type : ToasterMessageType.success);
    } else {
      showCustomSnackBar(response.statusText);
    }
    _isLoading = false;
    update();
  }

  ResponseModel _checkWrongOtp (Response response){
    if (verificationCode.length == 6 && response.statusCode == 403){
      _isWrongOtpSubmitted = true;
    }
    String responseText = "";
    if(response.statusCode == 500){
      responseText = "Internal Server Error";
    }else{
      responseText = response.body["message"] ?? "verification_failed".tr ;
    }
    return ResponseModel(false, responseText);
  }


  void updateWrongVerificationCodeStatus({bool value = false, bool shouldUpdate = false}){
    _isWrongOtpSubmitted = value;
    if(shouldUpdate){
      update();
    }
  }


  void updateVerificationCode(String query) {
    _verificationCode = query;
    _isWrongOtpSubmitted = false;
    update();
  }


  void toggleRememberMe({bool shouldUpdate = true, bool? newValue}) {

    if(newValue!=null){
      _isActiveRememberMe = newValue;
    }else{
      _isActiveRememberMe = !_isActiveRememberMe;
    }
    if(shouldUpdate){
      update();
    }
  }

  void updateToken() async{
    await authRepo.updateToken();
  }

  void unsubscribeToken(){
    authRepo.unsubscribeToken();
  }

  bool isLoggedIn() {
    return authRepo.isLoggedIn();
  }

  bool clearSharedData() {
    return authRepo.clearSharedData();
  }

  String getUserNumber() {
    return authRepo.getUserNumber();
  }

  String getUserCountryCode() {
    return authRepo.getUserCountryCode();
  }

  String getUserPassword() {
    return authRepo.getUserPassword();
  }
}