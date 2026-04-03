import 'package:get/get.dart';
import '../../utils/core_export.dart';

class NonEditableTextField extends StatelessWidget {
  final String ? text;
  final String ? text2;
  const NonEditableTextField({super.key,required this.text, this.text2=""}) ;

  @override
  Widget build(BuildContext context) {
    return Container(
      width: Get.width,
      padding: const EdgeInsets.symmetric(horizontal: Dimensions.paddingSizeLarge,vertical: Dimensions.paddingSizeDefault),
      decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(Dimensions.paddingSizeSmall),
          color: Colors.grey.withValues(alpha:0.1),
          border: Border.all(color: Colors.grey.withValues(alpha:0.6),width: 1)),
      child: Row(
        children: [
          Text("$text $text2",style: robotoMedium.copyWith(color: Theme.of(context).textTheme.bodyLarge!.color!.withValues(alpha:0.6),
              fontSize: Dimensions.fontSizeDefault),),
        ],
      )
    );
  }
}
