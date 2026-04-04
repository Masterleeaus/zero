import 'package:get/get.dart';
import 'package:demandium_serviceman/utils/core_export.dart';



class NoDataScreen extends StatelessWidget {
  final NoDataType? type;
  final String? text;
  const NoDataScreen({super.key, required this.text, this.type});

  @override
  Widget build(BuildContext context) {
    return  Padding(
        padding: const EdgeInsets.all(Dimensions.paddingSizeLarge),
        child: Column(mainAxisAlignment: MainAxisAlignment.center, crossAxisAlignment: CrossAxisAlignment.center, children: [
          const Row(),
          Image.asset( type == NoDataType.booking ? Images.noData :
            type == NoDataType.notification ? Images.emptyNotification : Images.noData,
            width: MediaQuery.of(context).size.height * 0.08,
            height: MediaQuery.of(context).size.height * 0.08,
            color: Get.isDarkMode?light.cardColor:null,
          ),
          SizedBox(height: MediaQuery.of(context).size.height * 0.04),
          Text( text !=null ? text! :
             type == NoDataType.booking ? 'sorry_your_order_history_is_Empty'.tr :
             type == NoDataType.notification ? 'empty_notifications'.tr : "",
            style: robotoMedium.copyWith(
                fontSize: Dimensions.fontSizeDefault,
                color: Theme.of(context).primaryColor),
            textAlign: TextAlign.center,
          ),
          SizedBox(height: MediaQuery.of(context).size.height * 0.08),

        ]),
    );
  }
}