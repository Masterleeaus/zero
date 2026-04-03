import 'package:get/get.dart';
import 'package:demandium_serviceman/utils/core_export.dart';

class ColumnText extends StatelessWidget {
  final int? amount;
  final String title;
  const ColumnText({super.key,required this.title,this.amount}) ;

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      width: Get.width * .27,
      child: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.start,
          children: [
            Text(amount?.toString() ??"",
              style: robotoBold.copyWith(fontSize: 17),
            ),

            const SizedBox(height: Dimensions.paddingSizeSmall),

            Text(
              title,
              textAlign: TextAlign.center,
              style: robotoRegular.copyWith(
                fontSize: Dimensions.fontSizeDefault,
                color: Theme.of(context).textTheme.bodyLarge!.color?.withValues(alpha:0.5),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
