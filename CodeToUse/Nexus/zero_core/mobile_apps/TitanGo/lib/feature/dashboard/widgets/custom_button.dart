import 'package:demandium_serviceman/utils/core_export.dart';

class DashboardCustomButton extends StatelessWidget {
  final String buttonText;
  final bool isSelectedButton;
  const DashboardCustomButton({super.key,required this.buttonText,required this.isSelectedButton}) ;

  @override
  Widget build(BuildContext context) {
    return Container(
      height: 30,
      width: 70,
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(5),
        boxShadow: isSelectedButton ? [
          BoxShadow(
            offset: const Offset(0, 1),
            blurRadius: 5,
            color: Colors.black.withValues(alpha:0.2),
          )
        ]:null,
        color: isSelectedButton ? Theme.of(context).cardColor:Colors.grey.withValues(alpha:0.2),
      ),
      child:  Center(
        child: Text(
          buttonText,
          style:  TextStyle(
              fontSize: 12,color: isSelectedButton?Theme.of(context).primaryColor:Colors.grey,
              fontWeight: FontWeight.w500),
        ),
      ),

    );
  }
}
