enum HtmlType {
  termsAndCondition('terms-and-conditions'),
  aboutUs('about-us'),
  privacyPolicy('privacy-policy'),
  cancellationPolicy('cancellation-policy'),
  refundPolicy('refund-policy'),
  others('');

  final String value;
  const HtmlType(this.value);

  /// Convert string to enum
  static HtmlType? fromValue(String value) {
    return HtmlType.values.firstWhere(
          (type) => type.value == value,
      orElse: () => others,
    );
  }
}
enum SendOtpType {forgetPassword, firebase, verification}
enum NoDataType { notification, booking, others}
enum BooingListStatus{accepted,ongoing}
enum BookingDetailsTabControllerState {bookingDetails,status}
enum EarningType{monthly, yearly}
enum EditProfileTabControllerState {generalInfo,accountIno}
enum ToasterMessageType {success, error, info}