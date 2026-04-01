import 'package:get/get.dart';
import 'package:demandium_serviceman/utils/core_export.dart';


class CategoryTabItem extends GetView<BookingRequestController> {
  const CategoryTabItem({super.key, required this.title,this.index}) ;
  final String title;
  final int? index;


  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.symmetric(horizontal: Dimensions.paddingSizeExtraSmall),
      padding: const EdgeInsets.symmetric(horizontal: 10,vertical: 5),
      decoration: BoxDecoration(
          color: controller.bookingHistorySelectedIndex !=index && Get.isDarkMode?
          Colors.grey.withValues(alpha:0.2):controller.bookingHistorySelectedIndex ==index? Theme.of(context).primaryColor:
          Theme.of(context).primaryColor.withValues(alpha:0.1),
          borderRadius: const BorderRadius.all(Radius.circular(20))
      ),
      child: Row(
        children: [
          SizedBox(
            height: 16,
            width: 16,
            child: Image.asset(
              index == 0 ? Images.allIcon :index == 1 ?
              Images.completedIcon:Images.canceledIcon
            )
          ),
          const SizedBox(width: 5),
          Text(
            title,
            textAlign: TextAlign.center,
            style:robotoMedium.copyWith(
                fontSize: Dimensions.fontSizeSmall,color: controller.bookingHistorySelectedIndex == index ?
            Colors.white: Theme.of(context).textTheme.bodyLarge!.color),
          )
        ],
      ),
    );
  }
}