import 'package:demandium_serviceman/utils/core_export.dart';

class AppConstants {
  static const String appName = 'Demandium';
  static const String appUser = 'Serviceman App';
  static const String appVersion = '3.6'; /// Flutter SDK: 3.38.5
  static const String baseUrl = 'YOUR_BASE_URL_HERE';
  static const bool avoidMaintenanceMode = false;
  static const String loginUrl = '/api/v1/serviceman/auth/login';
  static const String configUrl = '/api/v1/serviceman/config';
  static const String bookingRequestUrl = '/api/v1/serviceman/booking/list';
  static const String dashboardUrl = '/api/v1/serviceman/dashboard';
  static const String profileInfoUrl = '/api/v1/serviceman/info';
  static const String updatePasswordUrl = '/api/v1/serviceman/profile/change-password';
  static const String updateProfileUrl = '/api/v1/serviceman/update/profile';
  static const String notificationUrl = '/api/v1/serviceman/push-notifications';
  static const String bookingDetailsUrl = '/api/v1/serviceman/booking/detail/';
  static const String subBookingDetailsUrl = '/api/v1/serviceman/booking/single/detail/';
  static const String bookingStatusUpdateUrl = '/api/v1/serviceman/booking/status-update';
  static const String subBookingStatusUpdateUrl = '/api/v1/serviceman/booking/single-repeat-status-update';
  static const String bookingOTPNotificationUri = '/api/v1/serviceman/booking/opt/notification-send';
  static const String forgetPasswordUrl = '/api/v1/serviceman/forgot-password';
  static const String otpVerificationUrl = '/api/v1/serviceman/otp-verification';
  static const String resetPasswordUrl = '/api/v1/serviceman/reset-password';
  static const String createChannel = '/api/v1/serviceman/chat/create-channel';
  static const String getChannelListUrl = '/api/v1/serviceman/chat/channel-list';
  static const String searchChannelListUrl = '/api/v1/serviceman/chat/channel-list-search';
  static const String getConversationUrl = '/api/v1/serviceman/chat/conversation';
  static const String sendMessageUrl = '/api/v1/serviceman/chat/send-message';
  static const String paymentStatusUpdate = '/api/v1/serviceman/booking/payment-status-update';
  static const String verifyTokenUri = '/api/v1/auth/verify-token';
  static const String tokenUri = '/api/v1/serviceman/update/fcm-token';

  static const String pagesDetailsApi = '/api/v1/serviceman/config/page-details';
  static const String serviceListBasedOnSubCategory = '/api/v1/serviceman/service/data/sub-category-wise';
  static const String getBookingPriceList = '/api/v1/serviceman/booking/service/info';
  static const String removeCartServiceFromServer = '/api/v1/serviceman/booking/service/edit/remove-service';
  static const String updateRegularBooking = '/api/v1/serviceman/booking/service/edit/update-booking';
  static const String updateSubBooking = '/api/v1/serviceman/booking/repeat/service/edit/update-booking';
  static const String firebaseOtpVerify = '/api/v1/user/verification/firebase-auth-verify';
  static const String regularBookingInvoiceUrl = '/admin/booking/serviceman-invoice/';
  static const String singleRepeatBookingInvoiceUrl = "/admin/booking/serviceman-fullbooking-single-invoice/";

  static const String sendOtpForForgetPassword = '/api/v1/user/forget-password/send-otp';
  static const String verifyOtpForForgetPasswordScreen = '/api/v1/user/forget-password/verify-otp';
  static const String resetPasswordUri = '/api/v1/user/forget-password/reset';
  static const String changeLanguage = '/api/v1/serviceman/change-language';
  static const String bookingStatisticDataUrl = '/api/v1/serviceman/dashboard/booking-statistics';


  // Shared Key
  static const String theme = 'demand_theme';
  static const String token = 'demand_token';
  static const String countryCode = 'demand_country_code';
  static const String languageCode = 'demand_language_code';
  static const String userPassword = 'demand_user_password';
  static const String userNumber = 'demand_user_number';
  static const String userCountryCode = 'demand_user_country_code';
  static const String notificationCount = 'demand_notification_count';
  static const String topic = 'provider-serviceman';
  static const String localizationKey = 'X-localization';
  static const String intro = 'intro';

  static List<LanguageModel> languages = [
    LanguageModel(imageUrl: Images.usa, languageName: 'English', countryCode: 'US', languageCode: 'en'),
    LanguageModel(imageUrl: Images.arabic, languageName: 'عربى', countryCode: 'SA', languageCode: 'ar'),
    LanguageModel(imageUrl: Images.bn, languageName: 'বাংলা', countryCode: 'BD', languageCode: 'bn'),
    LanguageModel(imageUrl: Images.india, languageName: 'Hindi', countryCode: 'IN', languageCode: 'hi'),
  ];

  static const double maxLimitOfTotalFileSent = 5;

  /// Allowed image file extensions for upload
  static const List<String> allowedImageExtensions = [
    'png',
    'jpg',
    'jpeg',
    'gif',
    'webp',
  ];

  /// Allowed document file extensions for upload
  static const List<String> _allowedDocumentExtensions = [
    'pdf',
    'csv',
    'txt',
    'xls',
    'xlsx',
    'doc',
    'docx',
  ];

  /// All allowed file extensions (images + documents)
  static const List<String> allowedFileExtensions = [
    ...allowedImageExtensions,
    ..._allowedDocumentExtensions,
  ];

  /// Default image quality for image picker (0-100)
  static const int defaultImageQuality = 80;

}
