class ConfigModel {
  String? responseCode;
  String? message;
  ConfigContent? content;


  ConfigModel({String? responseCode, String? message, ConfigContent? content, List<void>? errors}) {
    if (responseCode != null) {
      responseCode = responseCode;
    }
    if (message != null) {
      message = message;
    }
    if (content != null) {
      content = content;
    }

  }

  ConfigModel.fromJson(Map<String, dynamic> json) {
    responseCode = json['response_code'];
    message = json['message'];
    content = json['content'] != null ? ConfigContent.fromJson(json['content']) : null;

  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    data['response_code'] = responseCode;
    data['message'] = message;
    if (content != null) {
      data['content'] = content!.toJson();
    }
    return data;
  }
}

class ConfigContent {
  String? _currencySymbolPosition;
  int? _providerSelfRegistration;
  String? _businessName;
  String? _logo;
  String? _logoFullPath;
  String? _favicon;
  String? _faviconFullPath;
  String? _countryCode;
  String? _businessAddress;
  String? _businessPhone;
  String? _businessEmail;
  String? _baseUrl;
  String? _currencyCode;
  String? _aboutUs;
  String? _privacyPolicy;
  String? _termsAndConditions;
  String? _refundPolicy;
  String? _returnPolicy;
  String? _cancellationPolicy;
  String? _appUrlAndroid;
  String? _appUrlIos;
  int? _smsVerification;
  int? _phoneVerification;
  int? _paginationLimit;
  String? _timeFormat;
  String? _currencyDecimalPoint;
  MinimumVersion? _minimumVersion;
  String? _footerText;
  int? _sendOtpTimer;
  int? _bookingOtpVerification;
  int? _bookingImageVerification;
  String? _additionalChargeLabelName;
  int? _serviceManCanCancelBooking;
  int? _serviceManCanEditBooking;
  int? _providerServicemanCanCancelBooking;
  int? _providerServicemanCanEditBooking;
  String? _currencySymbol;
  List<Language>? _languageList;
  MaintenanceMode? _maintenanceMode;
  int? _firebaseOtpVerification;
  ForgetPasswordVerificationMethod? _forgetPasswordVerificationMethod;
  String? _appEnvironment;
  List<BusinessPage>? _businessPages;
  int? _maxImageUploadSize;



  ConfigContent({String? currencySymbolPosition,
    int? providerSelfRegistration,
    String? businessName,
    String? logo,
    String? logoFullPath,
    String? favicon,
    String? faviconFullPath,
    String? countryCode,
    String? businessAddress,
    String? businessPhone,
    String? businessEmail,
    String? baseUrl,
    String? currencyCode,
    String? aboutUs,
    String? privacyPolicy,
    String? termsAndConditions,
    String? refundPolicy,
    String? returnPolicy,
    String? cancellationPolicy,
    String? appUrlAndroid,
    String? appUrlIos,
    int? smsVerification,
    int? phoneVerification,
    int? paginationLimit,
    String? timeFormat,
    String? currencyDecimalPoint,
    MinimumVersion? minimumVersion,
    String? footerText,
    int? sendOtpTimer,
    int? bookingOtpVerification,
    int? bookingImageVerification,
    String? additionalChargeLabelName,
    int? serviceManCanCancelBooking,
    int? serviceManCanEditBooking,
    int? providerServicemanCanCancelBooking,
    int? providerServicemanCanEditBooking,
    String? currencySymbol,
    List<Language>? languageList,
    MaintenanceMode? maintenanceMode,
    int? firebaseOtpVerification,
    ForgetPasswordVerificationMethod? forgetPasswordVerificationMethod,
    String? appEnvironment,
    List<BusinessPage>? businessPages,
    int? maxImageUploadSize,


  }) {
    if (currencySymbolPosition != null) {
      _currencySymbolPosition = currencySymbolPosition;
    }
    if (providerSelfRegistration != null) {
      _providerSelfRegistration = providerSelfRegistration;
    }
    if (businessName != null) {
      _businessName = businessName;
    }
    if (logo != null) {
      _logo = logo;
    }
    if (logoFullPath != null) {
      _logoFullPath = logoFullPath;
    }
    if (favicon != null) {
      _favicon = favicon;
    }
    if (faviconFullPath != null) {
      _faviconFullPath = faviconFullPath;
    }
    if (countryCode == null) {
      _countryCode = countryCode;
    }
    if (businessAddress != null) {
      _businessAddress = businessAddress;
    }
    if (businessPhone != null) {
      _businessPhone = businessPhone;
    }
    if (businessEmail != null) {
      _businessEmail = businessEmail;
    }
    if (baseUrl != null) {
      _baseUrl = baseUrl;
    }
    if (currencyCode != null) {
      _currencyCode = currencyCode;
    }
    if (aboutUs != null) {
      _aboutUs = aboutUs;
    }
    if (privacyPolicy != null) {
      _privacyPolicy = privacyPolicy;
    }
    if (termsAndConditions != null) {
      _termsAndConditions = termsAndConditions;
    }
    if (refundPolicy != null) {
      _refundPolicy = refundPolicy;
    }
    if (returnPolicy != null) {
      _returnPolicy = returnPolicy;
    }
    if (cancellationPolicy != null) {
      _cancellationPolicy = cancellationPolicy;
    }

    if (appUrlAndroid != null) {
      _appUrlAndroid = appUrlAndroid;
    }
    if (appUrlIos != null) {
      _appUrlIos = appUrlIos;
    }
    if (smsVerification != null) {
      _smsVerification = smsVerification;
    }
    if (phoneVerification != null) {
      _phoneVerification = phoneVerification;
    }
    if (paginationLimit != null) {
      _paginationLimit = paginationLimit;
    }

    if (timeFormat != null){
      _timeFormat = timeFormat;
    }

    if (currencyDecimalPoint != null) {
      _currencyDecimalPoint = currencyDecimalPoint;
    }
    if (minimumVersion != null) {
      _minimumVersion = minimumVersion;
    }
    if (footerText != null) {
      _footerText = footerText;
    }
    if (sendOtpTimer != null) {
      _sendOtpTimer = sendOtpTimer;
    }
    if (forgetPasswordVerificationMethod != null) {
      _forgetPasswordVerificationMethod = forgetPasswordVerificationMethod;
    }
    if (bookingOtpVerification != null) {
      _bookingOtpVerification = bookingOtpVerification;
    }
    if (bookingImageVerification != null) {
      _bookingImageVerification = bookingImageVerification;
    }

    if (additionalChargeLabelName!= null) {
      _additionalChargeLabelName = additionalChargeLabelName;
    }

    if (serviceManCanCancelBooking != null) {
      _serviceManCanCancelBooking = serviceManCanCancelBooking;
    }

    if (serviceManCanEditBooking != null) {
      _serviceManCanEditBooking = serviceManCanEditBooking;
    }

    if (providerServicemanCanCancelBooking != null) {
      _providerServicemanCanCancelBooking = providerServicemanCanCancelBooking;
    }

    if (_providerServicemanCanEditBooking != null) {
      _providerServicemanCanEditBooking = providerServicemanCanEditBooking;
    }

    if (_currencySymbol != null) {
      _currencySymbol = currencySymbol;
    }
    if ( languageList != null) {
      _languageList = languageList;
    }
    if (maintenanceMode != null) {
      _maintenanceMode = maintenanceMode;
    }
    if (firebaseOtpVerification != null) {
      _firebaseOtpVerification = firebaseOtpVerification;
    }
    if (appEnvironment != null) {
      _appEnvironment = appEnvironment;
    }
    if(businessPages != null) {
      _businessPages = businessPages;
    }
    if(maxImageUploadSize != null) {
      _maxImageUploadSize = maxImageUploadSize;
    }
  }

  String? get currencySymbolPosition => _currencySymbolPosition;

  int? get providerSelfRegistration => _providerSelfRegistration;

  String? get businessName => _businessName;

  String? get logo => _logo;
  String? get logoFullPath => _logoFullPath;

  String? get favicon => _favicon;
  String? get faviconFullPath => _faviconFullPath;

  String? get countryCode => _countryCode;

  String? get businessAddress => _businessAddress;

  String? get businessPhone => _businessPhone;

  String? get businessEmail => _businessEmail;

  String? get baseUrl => _baseUrl;

  String? get currencyCode => _currencyCode;

  String? get aboutUs => _aboutUs;

  String? get privacyPolicy => _privacyPolicy;

  String? get termsAndConditions => _termsAndConditions;

  String? get refundPolicy => _refundPolicy;

  String? get returnPolicy => _returnPolicy;

  String? get cancellationPolicy => _cancellationPolicy;

  String? get appUrlAndroid => _appUrlAndroid;

  String? get appUrlIos => _appUrlIos;

  int? get smsVerification => _smsVerification;

  int? get phoneVerification => _phoneVerification;

  int? get paginationLimit => _paginationLimit;

  String? get timeFormat => _timeFormat;

  String? get currencyDecimalPoint => _currencyDecimalPoint;

  MinimumVersion? get minimumVersion => _minimumVersion;

  String? get footerText => _footerText;
  int? get sendOtpTimer => _sendOtpTimer;
  ForgetPasswordVerificationMethod? get forgetPasswordVerificationMethod => _forgetPasswordVerificationMethod;
  int? get bookingOtpVerification => _bookingOtpVerification;
  int? get bookingImageVerification => _bookingImageVerification;
  String ? get additionalChargeLabelName => _additionalChargeLabelName;

  int? get serviceManCanCancelBooking => _serviceManCanCancelBooking;
  int? get serviceManCanEditBooking => _serviceManCanEditBooking ;
  int? get providerServicemanCanCancelBooking => _providerServicemanCanCancelBooking;
  int? get providerServicemanCanEditBooking => _providerServicemanCanEditBooking;
  String? get currencySymbol => _currencySymbol;
  List<Language>? get languageList => _languageList;
  MaintenanceMode? get maintenanceMode => _maintenanceMode;
  int? get firebaseOtpVerification => _firebaseOtpVerification;
  String? get appEnvironment => _appEnvironment;
  List<BusinessPage>? get businessPages => _businessPages;
  int? get maxImageUploadSize => _maxImageUploadSize;


  ConfigContent.fromJson(Map<String, dynamic> json) {
    _currencySymbolPosition = json['currency_symbol_position'];
    _providerSelfRegistration = json['provider_self_registration'];
    _businessName = json['business_name'];
    _logo = json['logo'];
    _logoFullPath = json['logo_full_path'];
    _favicon = json['favicon'];
    _faviconFullPath = json['favicon_full_path'];
    _countryCode = json['country_code'];
    _businessAddress = json['business_address'];
    _businessPhone = json['business_phone'];
    _businessEmail = json['business_email'];
    _baseUrl = json['base_url'];
    _currencyCode = json['currency_code'];
    _aboutUs = json['about_us'];
    _privacyPolicy = json['privacy_policy'];
    _termsAndConditions = json['terms_and_conditions'];
    _refundPolicy = json['refund_policy'];
    _returnPolicy = json['return_policy'];
    _cancellationPolicy = json['cancellation_policy'];
    _appUrlAndroid = json['app_url_android'];
    _appUrlIos = json['app_url_ios'];
    _smsVerification = json['sms_verification'];
    _phoneVerification = json['phone_verification'];

    _paginationLimit = json['pagination_limit'];

    _timeFormat = json['time_format'];

    _currencyDecimalPoint = json['currency_decimal_point'];
    _minimumVersion = json['min_versions'] != null
        ? MinimumVersion.fromJson(json['min_versions'])
        : null;
    _footerText = json['footer_text'];
    _sendOtpTimer = int.tryParse(json['otp_resend_time'].toString());
    _forgetPasswordVerificationMethod = json['forgot_password_verification_method'] != null
        ? ForgetPasswordVerificationMethod.fromJson(json['forgot_password_verification_method'])
        : null;
    _bookingOtpVerification = int.tryParse(json['booking_otp_verification'].toString());
    _bookingImageVerification = int.tryParse(json['service_complete_photo_evidence'].toString());
    _additionalChargeLabelName = json['additional_charge_label_name'];
    _serviceManCanCancelBooking = int.tryParse(json['serviceman_can_cancel_booking'].toString());
    _serviceManCanEditBooking = int.tryParse(json['serviceman_can_edit_booking'].toString());
    _providerServicemanCanCancelBooking= int.tryParse(json['provider_serviceman_can_cancel_booking'].toString());
    _providerServicemanCanEditBooking = int.tryParse(json['provider_serviceman_can_edit_booking'].toString());
    _currencySymbol = json['currency_symbol'];
    if (json['system_language'] != null) {
      _languageList = <Language>[];
      json['system_language'].forEach((v) { _languageList!.add(Language.fromJson(v)); });
    }
    _maintenanceMode = json['maintenance'] != null
        ? MaintenanceMode.fromJson(json['maintenance'])
        : null;

    _firebaseOtpVerification = int.tryParse(json['firebase_otp_verification'].toString());
    _appEnvironment = json['app_environment'];

    _businessPages = json["business_pages"] == null ? [] : List<BusinessPage>.from(json["business_pages"]!.map((x) => BusinessPage.fromJson(x)));

    _maxImageUploadSize = int.tryParse(json['max_image_upload_size'].toString());

  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    data['currency_symbol_position'] = _currencySymbolPosition;
    data['provider_self_registration'] = _providerSelfRegistration;
    data['business_name'] = _businessName;
    data['logo'] = _logo;
    data['logo_full_path'] = _logoFullPath;
    data['favicon'] = _favicon;
    data['favicon_full_path'] = _faviconFullPath;
    data['country_code'] = _countryCode;
    data['business_address'] = _businessAddress;
    data['business_phone'] = _businessPhone;
    data['business_email'] = _businessEmail;
    data['base_url'] = _baseUrl;
    data['currency_code'] = _currencyCode;
    data['about_us'] = _aboutUs;
    data['privacy_policy'] = _privacyPolicy;
    data['terms_and_conditions'] = _termsAndConditions;
    data['refund_policy'] = _refundPolicy;
    data['return_policy'] = _returnPolicy;
    data['cancellation_policy'] = _cancellationPolicy;
    data['app_url_android'] = _appUrlAndroid;
    data['app_url_ios'] = _appUrlIos;
    data['sms_verification'] = _smsVerification;
    data['phone_verification'] = _phoneVerification;
    data['pagination_limit'] = _paginationLimit;
    data['currency_decimal_point'] = _currencyDecimalPoint;
    if (_minimumVersion != null) {
      data['min_versions'] = _minimumVersion!.toJson();
    }
    data['footer_text'] = _footerText;
    data['otp_resend_time'] = _sendOtpTimer;
    data['forget_password_verification_method'] = _forgetPasswordVerificationMethod;
    data['otpVerification'] = _bookingOtpVerification;
    data['currency_symbol'] = _currencySymbol;
    data['business_pages'] = _businessPages?.map((e) => e.toJson()).toList();
    data['max_image_upload_size'] = _maxImageUploadSize;
    return data;
  }
}
class MinimumVersion {
  String? minVersionForAndroid;
  String? minVersionForIos;

  MinimumVersion({String? minVersionForAndroid, String? minVersionForIos}) {
    if (minVersionForAndroid != null) {
      minVersionForAndroid = minVersionForAndroid;
    }
    if (minVersionForIos != null) {
      minVersionForIos = minVersionForIos;
    }
  }

  MinimumVersion.fromJson(Map<String, dynamic> json) {
    minVersionForAndroid = json['min_version_for_android'];
    minVersionForIos = json['min_version_for_ios'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    data['min_version_for_android'] = minVersionForAndroid;
    data['min_version_for_ios'] = minVersionForIos;
    return data;
  }
}
class Language {
  String? languageCode;
  bool? isDefault;

  Language({this.languageCode, this.isDefault});

  Language.fromJson(Map<String, dynamic> json) {
    languageCode = json['code'];
    isDefault = json['default'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    data['code'] = languageCode;
    data['default'] = isDefault;
    return data;
  }
}

class MaintenanceMode {
  int? maintenanceStatus;
  SelectedMaintenanceSystem? selectedMaintenanceSystem;
  MaintenanceMessages? maintenanceMessages;
  MaintenanceTypeAndDuration? maintenanceTypeAndDuration;

  MaintenanceMode(
      {this.maintenanceStatus,
        this.selectedMaintenanceSystem,
        this.maintenanceMessages, this.maintenanceTypeAndDuration});

  MaintenanceMode.fromJson(Map<String, dynamic> json) {
    maintenanceStatus = json['maintenance_status'];
    selectedMaintenanceSystem = json['selected_maintenance_system'] != null
        ? SelectedMaintenanceSystem.fromJson(
        json['selected_maintenance_system'])
        : null;
    maintenanceMessages = json['maintenance_messages'] != null
        ? MaintenanceMessages.fromJson(json['maintenance_messages'])
        : null;

    maintenanceTypeAndDuration = json['maintenance_type_and_duration'] != null
        ? MaintenanceTypeAndDuration.fromJson(
        json['maintenance_type_and_duration'])
        : null;
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    data['maintenance_status'] = maintenanceStatus;
    if (selectedMaintenanceSystem != null) {
      data['selected_maintenance_system'] =
          selectedMaintenanceSystem!.toJson();
    }
    if (maintenanceMessages != null) {
      data['maintenance_messages'] = maintenanceMessages!.toJson();
    }
    if (maintenanceTypeAndDuration != null) {
      data['maintenance_type_and_duration'] =
          maintenanceTypeAndDuration!.toJson();
    }
    return data;
  }
}

class SelectedMaintenanceSystem {
  int? mobileApp;
  int? webApp;
  int? providerApp;
  int? servicemanApp;

  SelectedMaintenanceSystem(
      {this.mobileApp, this.webApp, this.providerApp, this.servicemanApp});

  SelectedMaintenanceSystem.fromJson(Map<String, dynamic> json) {
    mobileApp = json['mobile_app'];
    webApp = json['web_app'];
    providerApp = json['provider_app'];
    servicemanApp = json['serviceman_app'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    data['mobile_app'] = mobileApp;
    data['web_app'] = webApp;
    data['provider_app'] = providerApp;
    data['serviceman_app'] = servicemanApp;
    return data;
  }
}

class MaintenanceMessages {
  int? businessNumber;
  int? businessEmail;
  String? maintenanceMessage;
  String? messageBody;

  MaintenanceMessages(
      {this.businessNumber,
        this.businessEmail,
        this.maintenanceMessage,
        this.messageBody});

  MaintenanceMessages.fromJson(Map<String, dynamic> json) {
    businessNumber = json['business_number'];
    businessEmail = json['business_email'];
    maintenanceMessage = json['maintenance_message'];
    messageBody = json['message_body'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    data['business_number'] = businessNumber;
    data['business_email'] = businessEmail;
    data['maintenance_message'] = maintenanceMessage;
    data['message_body'] = messageBody;
    return data;
  }
}

class MaintenanceTypeAndDuration {
  String? maintenanceDuration;
  String? startDate;
  String? endDate;

  MaintenanceTypeAndDuration(
      {String? maintenanceDuration, String? startDate, String? endDate});

  MaintenanceTypeAndDuration.fromJson(Map<String, dynamic> json) {
    maintenanceDuration = json['maintenance_duration'];
    startDate = json['start_date'];
    endDate = json['end_date'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    data['maintenance_duration'] = maintenanceDuration;
    data['start_date'] = startDate;
    data['end_date'] = endDate;
    return data;
  }
}

class ForgetPasswordVerificationMethod {
  int? phone;
  int? email;

  ForgetPasswordVerificationMethod({this.phone, this.email});

  ForgetPasswordVerificationMethod.fromJson(Map<String, dynamic> json) {
    phone = int.tryParse(json['phone'].toString());
    email = int.tryParse(json['email'].toString());
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    data['phone'] = phone;
    data['email'] = email;
    return data;
  }
}

class BusinessPage {
  String? pageKey;
  String? title;
  int? isDefault;

  BusinessPage({
    this.pageKey,
    this.isDefault,
    this.title,
  });

  factory BusinessPage.fromJson(Map<String, dynamic> json) => BusinessPage(
    pageKey: json["page_key"],
    title: json["title"],
    isDefault: json["is_default"],
  );

  Map<String, dynamic> toJson() => {
    "page_key": pageKey,
    "is_default": isDefault,
    "title": title,
  };
}
