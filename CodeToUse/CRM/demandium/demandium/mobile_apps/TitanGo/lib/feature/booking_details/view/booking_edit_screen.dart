import 'package:demandium_serviceman/utils/core_export.dart';
import 'package:get/get.dart';


class BookingEditScreen extends StatefulWidget {
  final bool isSubBooking;
  const BookingEditScreen({super.key, required this.isSubBooking}) ;
  @override
  State<BookingEditScreen> createState() => _BookingEditScreenState();

}

class _BookingEditScreenState extends State<BookingEditScreen> {

  @override
  void initState() {
    super.initState();
    Get.find<BookingEditController>().initializedControllerValue(Get.find<BookingDetailsController>().bookingDetails?.bookingContent?.bookingDetailsContent);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: CustomAppBar(title: "edit_booking".tr),
      body: GetBuilder<BookingEditController>(builder: (bookingEditController){
        return Column( children: [
          Expanded(
            child: SingleChildScrollView(
              child: Padding( padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
                child: Column(crossAxisAlignment: CrossAxisAlignment.start,children: [

                  const PaymentStatusButton(),
                  const SizedBox(height: Dimensions.paddingSizeDefault,),

                  Container(width: Get.width,
                    padding: const EdgeInsets.symmetric(horizontal: Dimensions.paddingSizeLarge),
                    decoration: BoxDecoration(
                      color: Theme.of(context).cardColor.withValues(alpha:0.1),
                      borderRadius: BorderRadius.circular(Dimensions.paddingSizeSmall),
                      border: Border.all(color: Theme.of(context).primaryColor.withValues(alpha:0.2),width: 1),
                    ),
                    child: DropdownButtonHideUnderline(
                      child: DropdownButton(
                        dropdownColor: Theme.of(context).cardColor,
                        borderRadius: BorderRadius.circular(5),
                        elevation: 2,
                        hint: Text(bookingEditController.selectedBookingStatus ==''?
                        "select_booking_status".tr : "${'booking_status'.tr} :   ${bookingEditController.selectedBookingStatus.tr}",
                          style: robotoRegular.copyWith(
                              color: bookingEditController.selectedBookingStatus ==''?
                              Theme.of(context).textTheme.bodyLarge!.color!.withValues(alpha:0.6):
                              Theme.of(context).textTheme.bodyLarge!.color!.withValues(alpha:0.8)
                          ),
                        ),
                        icon: const Icon(Icons.keyboard_arrow_down),
                        items: bookingEditController.statusTypeList.map((String items) {
                          bool isDisabled = Get.find<BookingDetailsController>().bookingDetails?.bookingContent?.bookingDetailsContent?.bookingStatus == "ongoing" && 
                                          (items.toLowerCase() == "accepted" || items == 'canceled');
                          return DropdownMenuItem(
                            value: items,
                            enabled: !isDisabled,
                            child: Row(
                              children: [
                                Text(items.tr,
                                  style: robotoRegular.copyWith(
                                    color: isDisabled 
                                      ? Theme.of(context).textTheme.bodyLarge!.color!.withValues(alpha:0.4)
                                      : Theme.of(context).textTheme.bodyLarge!.color!.withValues(alpha:0.8),
                                  ),
                                ),
                              ],
                            ),
                          );
                        }).toList(),
                        onChanged: (String? newValue) {
                          if (newValue != null) {
                            bookingEditController.changeBookingStatusDropDownValue(newValue);
                          }
                        },
                      ),
                    ),
                  ),

                  TextFieldTitle(title: "service_schedule".tr, fontSize: Dimensions.fontSizeLarge,),
                  Container(
                    decoration: BoxDecoration(
                        color: Theme.of(context).cardColor.withValues(alpha:0.1),
                        borderRadius: BorderRadius.circular(Dimensions.paddingSizeSmall),
                        border: Border.all(color: Theme.of(context).primaryColor.withValues(alpha:0.2),width: 1)
                    ),
                    margin: const EdgeInsets.only(bottom : Dimensions.paddingSizeDefault),
                    padding: const EdgeInsets.only(left : Dimensions.paddingSizeDefault),
                    child: Row(mainAxisAlignment: MainAxisAlignment.spaceBetween,children: [

                      (bookingEditController.scheduleTime != null)?
                      Text(DateConverter.dateMonthYearTime(DateTime
                          .tryParse(bookingEditController.scheduleTime!)),
                        style: robotoRegular.copyWith(fontSize: Dimensions.fontSizeDefault),
                        textDirection: TextDirection.ltr,
                      ): const SizedBox(),
                      const SizedBox(width: Dimensions.paddingSizeDefault,),

                      IconButton(onPressed: ()=> bookingEditController.selectDate(),
                        icon:  Icon(Icons.calendar_month_outlined, color: Theme.of(context).primaryColor.withValues(alpha:0.5),),
                      )

                    ],),
                  ),

                  Row(mainAxisAlignment : MainAxisAlignment.spaceBetween, children: [
                    Text('service_list'.tr, style: robotoMedium.copyWith(color: Theme.of(context).primaryColor,fontSize: Dimensions.fontSizeLarge),),

                    if(!widget.isSubBooking) TextButton(
                      onPressed: bookingEditController.cartList.length == 1 &&  bookingEditController.cartList[0].variantKey == null ? null : (){
                        showModalBottomSheet(
                            useRootNavigator: true,
                            isScrollControlled: true,
                            backgroundColor: Colors.transparent,
                            context: context, builder: (context) => SubcategoryServiceView (
                          categoryId: "", subCategoryId: '', serviceList: bookingEditController.serviceList??[],)
                        );
                      },
                      style: ElevatedButton.styleFrom(
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
                          side: BorderSide(color: bookingEditController.cartList.length == 1 &&  bookingEditController.cartList[0].variantKey == null?
                          Theme.of(context).hintColor : Theme.of(context).primaryColor), // Customize the border color here
                        ),
                      ),
                      child: Row(mainAxisSize: MainAxisSize.min, children: [
                        Icon(Icons.add, color: bookingEditController.cartList.length == 1 &&  bookingEditController.cartList[0].variantKey == null ?
                        Theme.of(context).hintColor : Theme.of(context).primaryColor,
                          size: Dimensions.fontSizeDefault,
                        ),
                        const SizedBox(width: 8.0), // Adjust the spacing between the icon and text here
                        Text("add_service".tr, style: robotoMedium.copyWith(fontSize: Dimensions.fontSizeDefault,
                          color: bookingEditController.cartList.length == 1 &&  bookingEditController.cartList[0].variantKey == null
                              ? Theme.of(context).hintColor : Theme.of(context).primaryColor,
                        ),),
                      ]),
                    )
                  ],),

                  const SizedBox(height: Dimensions.paddingSizeDefault,),

                  GridView.builder(
                    key: UniqueKey(),
                    gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
                      crossAxisSpacing: Dimensions.paddingSizeLarge,
                      mainAxisSpacing: ResponsiveHelper.isDesktop(context) ?
                      Dimensions.paddingSizeLarge :
                      Dimensions.paddingSizeSmall,
                      childAspectRatio: ResponsiveHelper.isMobile(context) ?  5 : 6 ,
                      crossAxisCount: 1,
                      mainAxisExtent: 110,
                    ),
                    physics: const NeverScrollableScrollPhysics(),
                    shrinkWrap: true,
                    itemCount: bookingEditController.cartList.length,
                    itemBuilder: (context, index) {
                      bool disableQuantityButton  = bookingEditController.cartList.length == 1 &&  bookingEditController.cartList[0].variantKey == null;
                      return  CartServiceWidget(cart: bookingEditController.cartList[index], cartIndex: index, disableQuantityButton: disableQuantityButton, isSubBooking: widget.isSubBooking,);
                    },
                  ),

                  const SizedBox(height: 90,),


                ],),
              ),
            ),
          ),

          GetBuilder<BookingEditController>(builder: (bookingEditController){

            var bookingDetails = Get.find<BookingDetailsController>().bookingDetails?.bookingContent?.bookingDetailsContent;
            return Padding( padding: const EdgeInsets.symmetric(horizontal: Dimensions.paddingSizeDefault, vertical: Dimensions.paddingSizeSmall),
              child:  SafeArea(
                child: CustomButton(
                btnTxt: "update_status".tr,
                isLoading: bookingEditController.statusUpdateLoading,
                onPressed: (){
                  bookingEditController.updateBooking(
                    bookingId : bookingDetails?.subBooking?.id ?? bookingDetails?.id,
                    subBookingId: bookingDetails?.id,
                    zoneId : bookingDetails?.zoneId ?? bookingDetails?.subBooking?.zoneId ?? "",
                    isSubBooking: widget.isSubBooking,
                  );
                },
                            ),
              ),
                        );
          })
        ]);
      }),

    );
  }
}
