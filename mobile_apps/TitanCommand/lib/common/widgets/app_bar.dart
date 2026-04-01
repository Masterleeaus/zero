import 'package:get/get.dart';
import 'package:demandium_provider/util/core_export.dart';


class CustomAppBar extends StatelessWidget implements PreferredSizeWidget{
  final String title;
  final String? subtitle;
  final bool centerTitle;
  final Function()? onBackPressed;
  final Widget? actionWidget;
  final double? elevation;
  const CustomAppBar({super.key,required this.title,this.centerTitle= false,this.onBackPressed, this.actionWidget, this.subtitle, this.elevation});

  @override
  Widget build(BuildContext context) {
    return AppBar(
      elevation: elevation ?? 5,
      titleSpacing: 0,
      surfaceTintColor: Theme.of(context).cardColor,
      backgroundColor: Theme.of(context).cardColor,
      shadowColor: Get.isDarkMode?Theme.of(context).primaryColor.withValues(alpha:0.5):Theme.of(context).primaryColor.withValues(alpha:0.1),
      centerTitle: centerTitle,
      title: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(title,style: robotoBold.copyWith(fontSize: Dimensions.fontSizeLarge,color: Theme.of(context).primaryColorLight),),
          if(subtitle!=null) Text(subtitle!,style: robotoRegular.copyWith(fontSize: Dimensions.fontSizeSmall,color: Theme.of(context).primaryColorLight)),
        ],
      ),
      leading: IconButton(
        onPressed: onBackPressed ?? (){
          Get.back();
        },
          icon: Icon(Icons.arrow_back_ios,color:Theme.of(context).primaryColorLight, size: 20,),
      ),
      actions: actionWidget!=null?[actionWidget!]:null,
    );
  }
  @override
  Size get preferredSize => const Size(double.maxFinite, 55);
}

