import 'package:demandium_serviceman/utils/core_export.dart';
import 'package:get/get.dart';


class CustomAppBar extends StatelessWidget implements PreferredSizeWidget{
  final String title;
  final bool centerTitle;
  final String? subtitle;
  final Function()? onBackPressed;
  const CustomAppBar({super.key,required this.title,this.centerTitle= false, this.onBackPressed, this.subtitle}) ;

  @override
  Widget build(BuildContext context) {
    return AppBar(
      elevation: 5,
      titleSpacing: 0,
      backgroundColor: Theme.of(context).cardColor,
      surfaceTintColor: Theme.of(context).cardColor,
      shadowColor: Get.isDarkMode?Theme.of(context).primaryColor.withValues(alpha:0.5):Theme.of(context).primaryColor.withValues(alpha:0.1),
      centerTitle: centerTitle,
      title: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(title,style: robotoBold.copyWith(fontSize: Dimensions.fontSizeLarge,color: Theme.of(context).primaryColorLight),),
          if(subtitle!=null) Text(subtitle!,style: robotoRegular.copyWith(fontSize: Dimensions.fontSizeSmall,color: Theme.of(context).textTheme.bodySmall?.color),),
        ],
      ),
      leading: IconButton(
        onPressed: onBackPressed ?? (){
          if(Navigator.canPop(context)){
            Get.back();
          }else{
            Get.offAllNamed(RouteHelper.getInitialRoute());
          }
        },
        icon: Icon(Icons.arrow_back_ios,color:Theme.of(context).primaryColorLight,size: 20,),
      ),
    );
  }
  @override
  Size get preferredSize => const Size(double.maxFinite, 55);
}

