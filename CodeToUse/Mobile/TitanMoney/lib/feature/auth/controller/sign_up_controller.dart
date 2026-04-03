import 'package:get/get.dart';
import 'package:demandium_provider/util/core_export.dart';


class SignUpController extends GetxController {
  final AuthRepo authRepo;
  SignUpController({required this.authRepo});


  bool? _isLoading = false;
  bool? get isLoading => _isLoading;


  final double _identityImageSize=0;
  double get identityImageSize=> _identityImageSize;

  bool _isLogoValid = true;
  bool get isLogoValid =>_isLogoValid;

  bool _isCoverImageValidity = true;
  bool get isCoverImageValidity => _isCoverImageValidity;

  bool _isZoneValid = true;
  bool get isZoneValid => _isZoneValid;

  bool _isIdentityTypeValid = true;
  bool get isIdentityTypeValid => _isIdentityTypeValid;

  bool _isIdentityImageValid = true;
  bool get isIdentityImageValid => _isIdentityImageValid;

  int _selectedDigitalPaymentMethodIndex = -1;
  int get selectedDigitalPaymentMethodIndex => _selectedDigitalPaymentMethodIndex;

  SignUpPageStep currentStep = SignUpPageStep.step1;

  BusinessPlanType? selectedBusinessPlan;
  SubscriptionPaymentType ? selectedSubscriptionPaymentType;

  SubscriptionPackage? selectedSubscriptionPackage;

  List<XFile?>? _pickedIdentityImageList = [];
  List<XFile?>? get pickedIdentityImage => _pickedIdentityImageList;

  final List<MultipartBody> _selectedIdentityImageList = [];
  List<MultipartBody> get selectedIdentityImageList => _selectedIdentityImageList;

  bool _keepPersonalInfoAsCompanyInfo = false;
  bool get keepPersonalInfoAsCompanyInfo => _keepPersonalInfoAsCompanyInfo;


  var companyNameController = TextEditingController();
  var companyPhoneController = TextEditingController();
  var companyAddressController = TextEditingController();
  var companyEmailController = TextEditingController();
  
  var contactPersonNameController = TextEditingController();
  var contactPersonPhoneController = TextEditingController();
  var contactPersonEmailController = TextEditingController();
  
  var  identityTypeController = TextEditingController();
  var identityNumberController = TextEditingController();
  
  var accountFirstNameController = TextEditingController();
  var accountLastNameController = TextEditingController();
  var accountEmailController = TextEditingController();
  var accountPhoneController = TextEditingController();
  var accountPasswordController = TextEditingController();
  var accountConfirmPasswordController = TextEditingController();

  String selectedIdentityType= "";
  String selectedZoneId = '';
  String selectedZoneName ="";

  List<ZoneData> zoneList=[];

  var countryDialCode = "+880";

  XFile? _coverImageFile ;
  XFile? get coverImageFile => _coverImageFile;
  
  @override
  void onInit() {
    super.onInit();
    getZoneList();

    companyNameController.clear();
    companyPhoneController.clear();
    companyEmailController.clear();
    contactPersonNameController.clear();
    contactPersonPhoneController.clear();
    contactPersonEmailController.clear();
    identityTypeController.clear();
    identityNumberController.clear();
    accountFirstNameController.clear();
    accountLastNameController.clear();
    accountPhoneController.clear();
    accountEmailController.clear();
    accountPasswordController.clear();
    accountConfirmPasswordController.clear();
    countryDialCode = CountryCode.fromCountryCode(Get.find<SplashController>().configModel.content!.countryCode!).dialCode!;

  }

  Future<void> registration(SignUpBody signUpBody) async {
    _isLoading = true;
    update();

    Response? response = await authRepo.registration(
      signUpBody: signUpBody,
      identityImage: _selectedIdentityImageList,
      profileImage: _profileImageFile!,
      coverImage: _coverImageFile!,
    );

    if (response!.statusCode == 200 && response.body['response_code'] == "provider_store_200") {

      var config = Get.find<SplashController>().configModel.content;
      if(response.body['content'] != null){
        resetAllValue();
        Get.to(PaymentScreen(url: response.body['content'], fromPage: "signUp"));
      }
      else if(config?.emailVerification == 1 || config?.phoneVerification == 1){

        String identity = config?.phoneVerification == 1 ? signUpBody.accountPhone!.trim() :  signUpBody.accountEmail!.trim();
        String identityType = config?.phoneVerification == 1 ? "phone" : "email";
        SendOtpType type = (config?.phoneVerification == 1 && config?.firebaseOtpVerification == 1) ? SendOtpType.firebase : SendOtpType.verification;

        await Get.find<AuthController>().sendVerificationCode(identity:  identity , identityType: identityType, type: type, fromPage: "verification").then((status){
          if(status !=null){
            if(status.isSuccess!){
              Get.toNamed(RouteHelper.getVerificationRoute(
                identity: identity,identityType: identityType,
                fromPage: "verification",
                firebaseSession: type == SendOtpType.firebase ? status.message : null,
                showSignUpDialog: true,
              ));
            }else{
              Get.offNamed(RouteHelper.signIn);
              showCustomSnackBar(status.message.toString().capitalizeFirst);
            }
            resetAllValue();
            _isLoading = false;
            update();
          }

        });


      }else{
        resetAllValue();
        Get.offNamed(RouteHelper.signIn);
        showCustomBottomSheet(child: const WelcomeBottomSheet(fromSignup: true,));
        _isLoading = false;
        update();
      }
    }
    else if(response.statusCode == 400 && response.body['response_code'] == "default_400"){
      showCustomSnackBar(response.body["errors"][0]['message']);
      _isLoading = false;
      update();

    } else{
      ApiChecker.checkApi(response);

      _isLoading = false;
      update();
    }
  }

  Future<void> getZoneList() async {
    Response? response = await authRepo.getZonesDataList();
    if (response!.statusCode == 200)
    {
      zoneList=[];
      response.body['content']['data'].forEach((element){
        zoneList.add(ZoneData.fromJson(element));
      });
    }
    else {
    }
    update();
  }
  XFile? _profileImageFile;
  XFile? get profileImageFile => _profileImageFile;

  void pickProfileImage(bool isRemove) async {
    if(isRemove){
      _profileImageFile =null;
    }
    else{
      _profileImageFile = await FileValidationHelper.validateAndPickImage(source: ImageSource.gallery);
      if(_profileImageFile != null) {
        checkOthersFieldValidity(shouldUpdate: false);
      }
    }
    update();
  }
  XFile? pickedFile ;
  void pickImage() async {
    pickedFile = (await ImagePicker().pickImage(source: ImageSource.gallery))!;
    update();
  }
    
  void pickIdentityImage(bool isRemove,{int? index}) async {
    if(isRemove) {
      if(index != null){
        _selectedIdentityImageList.removeAt(index);
        _pickedIdentityImageList =[];
      }
    }else{
      XFile? pickedImage = await FileValidationHelper.validateAndPickImage(source: ImageSource.gallery);
      
      if(pickedImage != null) {
        _selectedIdentityImageList.add(MultipartBody('identity_images[]', pickedImage));
        checkOthersFieldValidity();
        update();
      }
    }
    update();
  }

  void resetAllValue(){
    companyNameController.clear();
    companyPhoneController.clear();
    companyEmailController.clear();
    contactPersonNameController.clear();
    contactPersonPhoneController.clear();
    contactPersonEmailController.clear();
    identityTypeController.clear();
    identityNumberController.clear();
    accountFirstNameController.clear();
    accountLastNameController.clear();
    accountPasswordController.clear();
    accountConfirmPasswordController.clear();
    accountEmailController.clear();
    accountPhoneController.clear();
    companyAddressController.clear();
    currentStep = SignUpPageStep.step1;
    _profileImageFile=null;
    _profileImageFile =null;
    _coverImageFile = null;
    _pickedIdentityImageList= [];
    _keepPersonalInfoAsCompanyInfo =false;
  }

  void togglePersonalInfoAsCompanyInfo(){
    _keepPersonalInfoAsCompanyInfo =! _keepPersonalInfoAsCompanyInfo;

    if(keepPersonalInfoAsCompanyInfo){
      contactPersonNameController.text = companyNameController.text;
      contactPersonPhoneController.text = companyPhoneController.text;
      contactPersonEmailController.text = companyEmailController.text;
    }
    else{
      contactPersonNameController.text ="";
      contactPersonPhoneController.text ="";
      contactPersonEmailController.text ="";
    }
    update();
  }

  void autoSaveContactPersonInfoAsGeneralInfo(){

    if(keepPersonalInfoAsCompanyInfo){
      contactPersonNameController.text = companyNameController.text;
      contactPersonPhoneController.text = companyPhoneController.text;
      contactPersonEmailController.text = companyEmailController.text;
    }

    Future.delayed(const Duration(milliseconds: 50), (){
      update();
    });
  }

  void updateDigitalPaymentMethodIndex (int index, {bool isUpdate = true}) {
    _selectedDigitalPaymentMethodIndex = index;
    if(isUpdate){
      update();
    }
  }

  void updateBusinessPlanType(BusinessPlanType type, {bool shouldUpdate = true }){
    selectedBusinessPlan = type;
    if(shouldUpdate){
      update();
    }
  }

  void updateSubscriptionPaymentType(SubscriptionPaymentType type, {bool shouldUpdate = true }){
    selectedSubscriptionPaymentType = type;
    if(shouldUpdate){
      update();
    }
  }

  void updateSelectedSubscription(SubscriptionPackage? sub, {bool shouldUpdate = true }){
    selectedSubscriptionPackage = sub;
    if(shouldUpdate){
      update();
    }
  }

  void setZoneData(String newZoneName,newZoneId){
    selectedZoneName =newZoneName;
    selectedZoneId = newZoneId;
    update();
  }

  void setIdentityType(String newIdentityType){
    selectedIdentityType =newIdentityType;
    update();
  }

  void updateRegistrationStep(SignUpPageStep currentPage){
    currentStep=currentPage;
    update();
  }

  void setAddressControllerText(String addressText){
    companyAddressController.text = addressText;
    update();
  }

  void checkOthersFieldValidity({bool shouldUpdate = true, bool isInitial = false, bool step1 = false, bool step2 = false}){

    if(step1){
      if(profileImageFile!=null || isInitial){
        _isLogoValid = true;
      }else{
        _isLogoValid = false;

      }
      if(coverImageFile !=null || isInitial){
        _isCoverImageValidity = true;
      }else{
        _isCoverImageValidity = false;
      }

    }else if(step2){

      if(selectedZoneName !="" || isInitial){
        _isZoneValid = true;
      }else{
        _isZoneValid = false;
      }

      if(selectedIdentityType !="" || isInitial){
        _isIdentityTypeValid = true;
      }else{
        _isIdentityTypeValid = false;
      }

      if(_selectedIdentityImageList.isNotEmpty || isInitial){
        _isIdentityImageValid = true;
      }else{
        _isIdentityImageValid = false;
      }
    }else{
      _isLogoValid = true;
      _isCoverImageValidity = true;
      _isZoneValid = true;
      _isIdentityTypeValid = true;
      _isIdentityImageValid = true;
    }

    if(shouldUpdate){
      update();
    }
  }

  void pickCoverImage() async {
    _coverImageFile = await FileValidationHelper.validateAndPickImage(source: ImageSource.gallery);
    update();
  }

}