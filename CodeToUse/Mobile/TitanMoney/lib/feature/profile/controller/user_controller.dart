import 'package:demandium_provider/feature/settings/business/controller/identity_controller.dart';
import 'package:get/get.dart';
import 'package:demandium_provider/util/core_export.dart';
import 'package:demandium_provider/feature/profile/model/provider_model.dart';



class UserProfileController extends GetxController implements GetxService{
  final UserRepo userRepo;
  UserProfileController({required this.userRepo});

  final GlobalKey<FormState> profileInformationFormKey = GlobalKey<FormState>();

  TextEditingController? companyNameController,companyPhoneController,companyEmailController,
      personalNameController,personalPhoneController,personalEmailController,
      emailController, passwordController,confirmPasswordController;

  bool keepPersonalInfoAsCompanyInfo = false;

  bool _showOverflowDialog = false;
  bool get showOverflowDialog => _showOverflowDialog;

  bool _trialWidgetNotShow = false;
  bool get trialWidgetNotShow => _trialWidgetNotShow;

  String _providerId = '';
  String get providerId =>_providerId;

  var countryDialCode = "+880";

  String _selectedZoneID ='';
  String get selectedZoneID => _selectedZoneID;

  String _selectedZoneName ="";
  String get selectedZoneName => _selectedZoneName;

  String myZone='';
  String? myZoneId;
  double latitude = 0;
  double longitude= 0;

  List<ZoneData> zoneList=[];

  bool _isZoneValid = true;
  bool get isZoneValid => _isZoneValid;


  int _totalCompleteRequest= 0;
  int _totalCanceledRequest= 0;
  int _totalOngoingRequest= 0;
  int _totalAcceptedRequest= 0;

  int get totalCompletedRequest=> _totalCompleteRequest;
  int get totalCanceledRequest=> _totalCanceledRequest;
  int get totalOngoingRequest=> _totalOngoingRequest;
  int get totalAcceptedRequest=> _totalAcceptedRequest;






  @override
  void onInit() {
    super.onInit();
    //getProviderInfo();
    companyNameController = TextEditingController();
    companyPhoneController = TextEditingController();
    companyEmailController = TextEditingController();

    personalNameController = TextEditingController();
    personalPhoneController = TextEditingController();
    personalEmailController = TextEditingController();

    emailController = TextEditingController();
    passwordController = TextEditingController();
    confirmPasswordController = TextEditingController();

    countryDialCode = CountryCode.fromCountryCode(Get.find<SplashController>().configModel.content?.countryCode??"BD").dialCode!;
  }

  @override
  void onClose() {
    companyNameController!.dispose();
    companyPhoneController!.dispose();
    companyEmailController!.dispose();
    personalNameController!.dispose();
    personalPhoneController!.dispose();
    personalEmailController!.dispose();

    emailController!.dispose();
    passwordController!.dispose();
    confirmPasswordController!.dispose();
  }


  void togglePersonalInfoAsCompanyInfo(){
    keepPersonalInfoAsCompanyInfo =! keepPersonalInfoAsCompanyInfo;

    if(keepPersonalInfoAsCompanyInfo){
      personalNameController!.text = companyNameController!.text;
      personalPhoneController!.text = companyPhoneController!.text;
      personalEmailController!.text = companyEmailController!.text;
    }
    else{

        personalNameController!.text = _providerModel?.content?.providerInfo?.contactPersonName??"";
        personalPhoneController!.text = ValidationHelper.getValidPhone(_providerModel?.content?.providerInfo?.contactPersonPhone ?? "");
        personalEmailController!.text= _providerModel?.content?.providerInfo?.contactPersonEmail??"";
    }
    update();
  }

   ProviderModel? _providerModel;
   XFile? _pickedFile ;
   XFile? _coverImageFile ;
   bool _isLoading = false;

  ProviderModel? get providerModel => _providerModel;
  XFile? get pickedFile => _pickedFile;
  XFile? get coverImageFile => _coverImageFile;
  bool get isLoading => _isLoading;





  Future<bool> getProviderInfo({bool reload = false}) async {
    _isLoading = true;

    Get.find<LocationController>().setPickedLocation();


    if(_providerModel==null || reload){
      Response response = await userRepo.getProviderInfo();
      if (response.statusCode == 200) {
         getZoneList();
        _providerModel = ProviderModel.fromJson(response.body);

         double payablePercentage = getOverflowPercent(
           double.tryParse(_providerModel?.content?.providerInfo?.owner?.account?.accountPayable??"0")??0,
           double.tryParse(_providerModel?.content?.providerInfo?.owner?.account?.accountReceivable??"0")??0,
             Get.find<SplashController>().configModel.content?.maxCashInHandLimit ?? 0,
         );

         hideOverflowDialog(payablePercentage: payablePercentage, hideDialog: false);

        companyNameController!.text = _providerModel?.content?.providerInfo?.companyName??'';

        countryDialCode = ValidationHelper.getValidCountryCode(_providerModel?.content?.providerInfo?.companyPhone ?? "" ) != ""
            ? ValidationHelper.getValidCountryCode(_providerModel?.content?.providerInfo?.companyPhone ?? "")
            : CountryCode.fromCountryCode(Get.find<SplashController>().configModel.content!.countryCode!).dialCode ?? "+880";

        companyPhoneController!.text = ValidationHelper.getValidPhone(_providerModel?.content?.providerInfo?.companyPhone ?? "") != "" ? ValidationHelper.getValidPhone(_providerModel?.content?.providerInfo?.companyPhone??"" ) : _providerModel?.content?.providerInfo?.companyPhone ?? "";

        companyEmailController!.text = _providerModel?.content?.providerInfo?.companyEmail??"";
        personalNameController!.text = _providerModel?.content?.providerInfo?.contactPersonName??"";
        personalPhoneController!.text = ValidationHelper.getValidPhone(_providerModel?.content?.providerInfo?.contactPersonPhone ?? "") != "" ? ValidationHelper.getValidPhone(_providerModel?.content?.providerInfo?.contactPersonPhone ?? "" ) : PhoneNumber.parse(_providerModel?.content?.providerInfo?.contactPersonPhone ?? '').toString();
        personalEmailController!.text = _providerModel?.content?.providerInfo?.contactPersonEmail??"";
        emailController!.text = _providerModel?.content?.providerInfo?.owner?.email??"";
        latitude = _providerModel?.content?.providerInfo?.coordinates?.latitude?? 0;
        longitude = _providerModel?.content?.providerInfo?.coordinates?.longitude?? 0;
        _totalCompleteRequest= 0;
        _totalCanceledRequest= 0;
        _totalOngoingRequest= 0;
        _totalAcceptedRequest= 0;

        _providerId = _providerModel!.content!.providerInfo!.id!;
        myZoneId =_providerModel!.content!.providerInfo!.zoneId!;
        _selectedZoneID = myZoneId!;
        _selectedZoneName ='';

         getZoneList();

        if(companyNameController!.text==personalNameController!.text
            && companyPhoneController!.text==personalPhoneController!.text
            &&companyEmailController!.text==personalEmailController!.text){
          keepPersonalInfoAsCompanyInfo = true;
        }else{
          keepPersonalInfoAsCompanyInfo = false;
        }

        if(_providerModel!.content!.bookingOverview!=[] && _providerModel!.content!.bookingOverview!=null){
          for (var element in _providerModel!.content!.bookingOverview!) {
            if(element.bookingStatus=='accepted'){
              _totalAcceptedRequest = element.total!;
            }else if(element.bookingStatus=="canceled"){
              _totalCanceledRequest = element.total!;
            }else if(element.bookingStatus=="completed"){
              _totalCompleteRequest = element.total!;
            }else if(element.bookingStatus=="ongoing"){
              _totalOngoingRequest = element.total!;
            }
          }
        }else{
          _totalCompleteRequest= 0;
          _totalCanceledRequest= 0;
          _totalOngoingRequest= 0;
          _totalAcceptedRequest= 0;
        }
        _isLoading= false;
        update();
      } else {
        ApiChecker.checkApi(response);
      }
    }
    _isLoading = false;
    update();

    return _providerModel != null;

  }

  Future<ResponseModel> updateProfile({required String address, required String identityNumber}) async {
    _isLoading = true;
    update();

    if(Get.find<LocationController>().pickAddress.address != ""){
      latitude = Get.find<LocationController>().pickPosition.latitude;
      longitude = Get.find<LocationController>().pickPosition.longitude;
    }

    Response response = await userRepo.updateProfile(
      companyName: companyNameController!.text.toString(),
      companyPhone: "$countryDialCode${companyPhoneController!.text.toString()}",
      companyAddress:  address,
      lat: latitude,
      lon: longitude,
      companyEmail: companyEmailController!.text.toString(),
      contactPersonName: personalNameController!.text.toString(),
      contactPersonPhone: "$countryDialCode${ personalPhoneController!.text.toString()}",
      contactPersonEmail: personalEmailController!.text.toString(),
      zoneId: _selectedZoneID,
      profileImage: _pickedFile,
      deletedIdentityImages: Get.find<IdentityController>().getDeletedImageUrls(),
      identityImages: Get.find<IdentityController>().getUploadedImageFiles(),
      coverImages: coverImageFile,
      identityNumber: identityNumber,
      identityType: Get.find<IdentityController>().selectedIdentityType,
    );

    if(response.statusCode == 200){
      await getProviderInfo(reload: true);


      if(companyNameController!.text == personalNameController!.text
          && companyPhoneController!.text == personalPhoneController!.text
          && companyEmailController!.text == personalEmailController!.text){
        keepPersonalInfoAsCompanyInfo = true;
      }else{
        keepPersonalInfoAsCompanyInfo = false;
      }
      _isLoading=false;
      update();

      return ResponseModel(true, response.body['message']);
    }
    else{
      _isLoading = false;
      update();
      try{
        return  ResponseModel(false, response.body['errors'][0]['message']);

      }catch(e){
        return ResponseModel(false, response.statusText ?? "Something went wrong");

      }
    }
  }

  Future<void> updatePassword() async {
    _isLoading = true;
    update();

    Response response = await userRepo.updatePasswordApi(password: passwordController!.text, confirmPassword: confirmPasswordController!.text);

    if(response.statusCode == 200){
      showCustomSnackBar(response.body['message'], type: ToasterMessageType.success);

    } else{
      showCustomSnackBar(response.body['errors'][0]['message']);
    }
    _isLoading = false;
    update();
  }


  Future<void> getZoneList() async {
    _selectedZoneName ='';

    if(zoneList.isEmpty){
      Response? response = await userRepo.getZonesDataList();
      if (response!.statusCode == 200)
      {
        zoneList=[];

        List<dynamic>? list = response.body['content']['data'];

        if(zoneList.isEmpty){
          for (var element in list!) {
            zoneList.add(ZoneData.fromJson(element));
          }
        }

        if(zoneList.isNotEmpty && _providerModel!=null){

          for (var element in zoneList) {
            if(element.id==_providerModel!.content!.providerInfo!.zoneId!){
              myZone = element.name!;
            }
          }
        }
      }
      else {
      }
    }else{
      if(_providerModel!=null){
        for (var element in zoneList) {
          if(element.id==_providerModel!.content!.providerInfo!.zoneId!){
            myZone = element.name!;
          }
        }
      }
    }

      update();
  }

  void setNewZoneValue(String zoneName,zoneId){
    _selectedZoneName =zoneName;
    _selectedZoneID = zoneId;
    update();
  }

  void pickImage() async {
  _pickedFile = await FileValidationHelper.validateAndPickImage(source: ImageSource.gallery);
  update();
}

  void pickCoverImage() async {
  _coverImageFile = await FileValidationHelper.validateAndPickImage(source: ImageSource.gallery);
  update();
}

  void resetImage() async {
    _pickedFile = null;
    _coverImageFile = null;
  }


  double getOverflowPercent(double payable, double receivable, double maxAmount) {
     double amount = getTransactionAmountAmount(payable, receivable);

     double percentage = (amount / maxAmount) * 100;
     return percentage;
   }

  double getTransactionAmountAmount(double payable, double receivable) {
    double amount = 0;
    if(payable > receivable){
      amount = payable - receivable;
    }else{
      amount = receivable - payable;
    }
    return amount;
  }

  TransactionType getTransactionType (double payable, double receivable){
    TransactionType type =  TransactionType.none;

    if(payable == receivable){
      if(payable == 0 || receivable == 0){
        type  = TransactionType.none;
      }else{
        type = TransactionType.adjust;
      }
    } else if(payable > receivable ){
      if(receivable > 0.0){
        type = TransactionType.adjustAndPayable;
      }else{
        type = TransactionType.payable;
      }
    }else if(receivable > payable){
      if( payable> 0.0){
        type = TransactionType.adjustWithdrawAble;
      }else{
        type = TransactionType.withdrawAble;
      }
    } else{
      type  = TransactionType.none;
    }

    return type;
  }

  int numberOfShowDialog = 0;

  void hideOverflowDialog({double? payablePercentage, bool hideDialog = true}){

    if(!hideDialog ){

      if(payablePercentage != null){
        if( !_showOverflowDialog && payablePercentage >= 80 && payablePercentage < 100 && numberOfShowDialog < 1){
          numberOfShowDialog ++;
          _showOverflowDialog = true;

        } else if(payablePercentage >= 100){
          numberOfShowDialog = 0;
          _showOverflowDialog = true;
        } else{
          // //numberOfShowDialog = 0;
          // _showOverflowDialog = false;
        }
      }

    }else{
      _showOverflowDialog = false;
      update();
    }
  }

  void updateNumberOfTimeShowingDialog(){
    numberOfShowDialog = 0;
    _showOverflowDialog = false;
  }

  bool haveAnyAcceptedAndOngoingBooking(){
    return  (_totalAcceptedRequest + _totalOngoingRequest) > 0;
  }


  void onProfileChangeValidationCheck({bool shouldUpdate = true}){
    if(selectedZoneName == ""){
      _isZoneValid = false;
    }
    if(shouldUpdate){
      update();
    }
  }

  void clearUserProfileData(){
    _providerModel  = null;
    update();
  }


  Future<bool> trialWidgetShow({required String route}) async {
    const Set<String> routesToHideWidget = {
      '/business-plan', 'show-dialog', '/success', '/payment',
    };
    _trialWidgetNotShow = routesToHideWidget.contains(route);

    Future.delayed(const Duration(milliseconds: 500), () {
      update();
    });
    return _trialWidgetNotShow;
  }


  bool checkAvailableFeatureInSubscriptionPlan({required String featureType}){

    bool status = _providerModel?.content?.subscriptionInfo?.status == "subscription_base"
        && !_providerModel!.content!.subscriptionInfo!.subscribedPackageDetails!.featureList!.contains(featureType) ? false : true;

    if(!status){
      showCustomSnackBar('this_feature_is_not_included_in_your_current_subscription_plan'.tr);
    }
    return status;
  }



}