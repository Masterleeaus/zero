import 'package:demandium_serviceman/feature/booking_details/model/booking_details_model.dart';

class ProfileDataModel {
  ProfileDataModel({
    this.responseCode,
    this.message,
    this.content,
  });

  String?  responseCode;
  String?  message;
  ProfileContent?  content;

  factory ProfileDataModel.fromJson(Map<String, dynamic> json) => ProfileDataModel(
    responseCode: json["response_code"],
    message: json["message"],
    content: ProfileContent.fromJson(json["content"]),
  );

  Map<String, dynamic> toJson() => {
    "response_code": responseCode,
    "message": message,
    "content": content!.toJson(),
  };
}

class ProfileContent {
  String? id;
  String? providerId;
  String? userId;
  String? createdAt;
  String? updatedAt;
  String? deletedAt;
  int? bookingsCount;
  int? completedBookingsCount;
  Provider ? provider;
  ProfileContent(
      {this.id,
        this.providerId,
        this.userId,
        this.createdAt,
        this.updatedAt,
        this.deletedAt,
        this.bookingsCount,
        this.completedBookingsCount,
        this.provider,
      });

  ProfileContent.fromJson(Map<String, dynamic> json) {
    id = json['id'];
    providerId = json['provider_id'];
    userId = json['user_id'];
    createdAt = json['created_at'];
    updatedAt = json['updated_at'];
    deletedAt = json['deleted_at'];
    bookingsCount = int.parse(json['bookings_count'].toString());
    completedBookingsCount = int.parse(json['completed_bookings_count'].toString());
    provider = json['provider'] !=null ? Provider.fromJson(json['provider'])  : null;
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    data['id'] = id;
    data['provider_id'] = providerId;
    data['user_id'] = userId;
    data['created_at'] = createdAt;
    data['updated_at'] = updatedAt;
    data['deleted_at'] = deletedAt;
    data['bookings_count'] = bookingsCount;
    data['completed_bookings_count'] = completedBookingsCount;
    return data;
  }
}




