import 'package:demandium_serviceman/api/api_client.dart';
import 'package:demandium_serviceman/utils/app_constants.dart';
import 'package:get/get_connect/http/src/response/response.dart';

class HtmlRepository{
  final ApiClient apiClient;
  HtmlRepository({required this.apiClient});

  Future<Response> getPagesContent(String pageKey) async {
    return await apiClient.getData('${AppConstants.pagesDetailsApi}/$pageKey');
  }

}