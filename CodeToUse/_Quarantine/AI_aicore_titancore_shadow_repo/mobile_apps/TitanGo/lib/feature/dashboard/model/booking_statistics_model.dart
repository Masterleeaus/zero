
import 'dart:convert';

BookingStatisticsModel earningDataModelFromJson(String str) => BookingStatisticsModel.fromJson(json.decode(str));

String earningDataModelToJson(BookingStatisticsModel data) => json.encode(data.toJson());

class BookingStatisticsModel {
  BookingData? thisWeek;
  BookingData? thisMonth;
  BookingData? thisYear;

  BookingStatisticsModel({
    this.thisWeek,
    this.thisMonth,
    this.thisYear,
  });

  factory BookingStatisticsModel.fromJson(Map<String, dynamic> json) => BookingStatisticsModel(
    thisWeek: json["this_week"] == null ? null : BookingData.fromJson(json["this_week"]),
    thisMonth: json["this_month"] == null ? null : BookingData.fromJson(json["this_month"]),
    thisYear: json["this_year"] == null ? null : BookingData.fromJson(json["this_year"]),
  );

  Map<String, dynamic> toJson() => {
    "this_week": thisWeek?.toJson(),
    "this_month": thisMonth?.toJson(),
    "this_year": thisYear?.toJson(),
  };
}

class BookingData {
  int total;
  double change;

  BookingData({
    required this.total,
    required this.change,
  });

  factory BookingData.fromJson(Map<String, dynamic> json) => BookingData(
    total: int.tryParse('${json["total_bookings"]}') ?? 0,
    change: double.tryParse('${json["change"]}') ?? 0,
  );

  Map<String, dynamic> toJson() => {
    "total_bookings": total,
    "change": change,
  };
}
