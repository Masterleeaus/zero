import 'package:demandium_provider/helper/extension_helper.dart';
import 'package:get/get.dart';
import 'package:demandium_provider/util/core_export.dart';


class SignUpStep2 extends StatefulWidget {
  final GlobalKey<FormState> formKey;
  const SignUpStep2({super.key, required this.formKey});

  @override
  State<SignUpStep2> createState() => _SignUpStep2State();
}

class _SignUpStep2State extends State<SignUpStep2> {

  final FocusNode _identityFocus = FocusNode();

  @override
  Widget build(BuildContext context) {
    return Expanded(
      child: SingleChildScrollView(
        physics: const BouncingScrollPhysics(),
        child: GetBuilder<SignUpController>(builder: (signUpController) {
          return Container(
            decoration: BoxDecoration(
              borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
              boxShadow: context.customThemeColors.lightShadow,
              color: Theme.of(context).cardColor,
            ),
            padding: const EdgeInsets.symmetric(horizontal : Dimensions.paddingSizeDefault, vertical: Dimensions.paddingSizeLarge),
            margin: const EdgeInsets.fromLTRB(Dimensions.paddingSizeSmall,Dimensions.paddingSizeDefault,Dimensions.paddingSizeSmall,3),


            child: Form(key: widget.formKey,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [


                  Text("business_information".tr,
                    style: robotoBold.copyWith(color: Theme.of(context).textTheme.bodyMedium?.color?.withValues(alpha:0.8), fontSize: Dimensions.fontSizeLarge),
                  ),
                  const SizedBox(height: Dimensions.paddingSizeDefault,),


                  TextFieldTitle(title:"select_zone".tr,requiredMark: true, isPadding: false, fontSize: Dimensions.fontSizeSmall,),
                  Column(crossAxisAlignment: CrossAxisAlignment.start, children: [



                    Container(width: Get.width, height: 40,
                        decoration: BoxDecoration(
                          border: Border(bottom: BorderSide(color: signUpController.isZoneValid ? Theme.of(context).hintColor : Theme.of(context).colorScheme.error)),
                        ),

                        child: DropdownButtonHideUnderline(
                          child: DropdownButton(
                            menuMaxHeight: Get.height*.40,
                            dropdownColor: Theme.of(context).cardColor,
                            borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
                            elevation: 8,
                            hint: Text(signUpController.selectedZoneName==""?"select_your_zone".tr:signUpController.selectedZoneName,
                              style: robotoRegular.copyWith(
                                color: signUpController.selectedZoneName==''?
                                Theme.of(context).textTheme.bodyLarge!.color!.withValues(alpha:0.6):
                                Theme.of(context).textTheme.bodyLarge!.color!.withValues(alpha:0.8),
                                  fontSize: signUpController.selectedIdentityType==''? Dimensions.fontSizeSmall : Dimensions.fontSizeDefault
                              ),
                            ),
                            icon: const Icon(Icons.keyboard_arrow_down),
                            items: signUpController.zoneList.map((ZoneData zoneData) {
                              return DropdownMenuItem(
                                value: zoneData,
                                child: Text(zoneData.name!,
                                  style: robotoRegular.copyWith(
                                    color: Theme.of(context).textTheme.bodyLarge!.color!.withValues(alpha:0.7),
                                  ),
                                ),
                              );
                            }).toList(),
                            onChanged: (ZoneData? zoneData) {
                              signUpController.setZoneData(zoneData!.name!,zoneData.id!);
                              signUpController.checkOthersFieldValidity(step2: true);
                            },
                          ),
                        ),
                      ),
                      if(!signUpController.isZoneValid)
                        Padding(padding: const EdgeInsets.only(top : 5),
                          child: Text("fill_required_field".tr,
                            style: robotoRegular.copyWith(color: Theme.of(context).colorScheme.error, fontSize: Dimensions.fontSizeSmall),
                          ),
                        )
                    ],
                  ),

                  const SizedBox(height: Dimensions.paddingSizeDefault,),


                  TextFieldTitle(title: "identity_type".tr,requiredMark: true, isPadding: false, fontSize: Dimensions.fontSizeSmall,),
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Container(width: Get.width, height: 40,
                        decoration: BoxDecoration(
                          border: Border(bottom: BorderSide(color: signUpController.isIdentityTypeValid ? Theme.of(context).hintColor : Theme.of(context).colorScheme.error)),
                        ),
                        child: DropdownButtonHideUnderline(
                          child: DropdownButton(
                            padding: EdgeInsets.zero,
                            dropdownColor: Theme.of(context).cardColor,
                            borderRadius: BorderRadius.circular(5),
                            elevation: 2,
                            hint: Text(signUpController.selectedIdentityType==''?
                              "select_identity_type".tr:signUpController.selectedIdentityType.tr,
                              style: robotoRegular.copyWith(
                                  color: signUpController.selectedIdentityType==''?
                                  Theme.of(context).textTheme.bodyLarge!.color!.withValues(alpha:0.6):
                                  Theme.of(context).textTheme.bodyLarge!.color!.withValues(alpha:0.8),
                                fontSize: signUpController.selectedIdentityType==''? Dimensions.fontSizeSmall : Dimensions.fontSizeDefault,
                              ),
                            ),
                            icon: const Icon(Icons.keyboard_arrow_down),
                            items: AppConstants.identityTypeList.map((String items) {
                            return DropdownMenuItem(
                              value: items,
                              child: Row(
                                children: [
                                  Text(items.tr,
                                    style: robotoRegular.copyWith(
                                      color: Theme.of(context).textTheme.bodyLarge!.color!.withValues(alpha:0.7),
                                    ),
                                  ),
                                ],
                              ),
                            );
                            }).toList(),
                            onChanged: (String? newValue) {
                              signUpController.setIdentityType(newValue!);
                              signUpController.checkOthersFieldValidity(step2: true);
                            }
                          ),
                        ),
                      ),
                      if(!signUpController.isIdentityTypeValid)
                        Padding(padding: const EdgeInsets.only(top : 5),
                          child: Text('fill_required_field'.tr,
                            style: robotoRegular.copyWith(color: Theme.of(context).colorScheme.error, fontSize: Dimensions.fontSizeSmall),
                          ),
                        )
                    ],
                  ),

                  const SizedBox(height: Dimensions.paddingSizeExtraMoreLarge),


                  CustomTextField(
                    inputType: TextInputType.text,
                    title: "identity_number".tr,
                    controller: signUpController.identityNumberController,
                    hintText: "enter_identity_number".tr,
                    maxLines: 1,
                    focusNode: _identityFocus,
                    onValidate: (value){
                      return (value ==null || value.isEmpty) ? 'enter_identity_number'.tr : null;
                    },
                  ),

                  const SizedBox(height: Dimensions.paddingSizeDefault),
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [

                      TextFieldTitle(title:"identity_image".tr, requiredMark: true),

                      if(signUpController.selectedIdentityImageList.isNotEmpty) ListView.builder(
                        itemBuilder: (context,index){
                          return  Padding(
                            padding: const EdgeInsets.symmetric(vertical: Dimensions.paddingSizeExtraSmall +2),
                            child: Stack(children: [
                              ClipRRect(
                                borderRadius: BorderRadius.circular(10),
                                child: Image.file(
                                  File(signUpController.selectedIdentityImageList[index].file.path),
                                  fit: BoxFit.cover,
                                  height: 110,
                                  width: MediaQuery.of(context).size.width,
                                ),
                              ),

                              Positioned(
                                top: -10,
                                right: -10,
                                child: IconButton(
                                  onPressed: ()=> signUpController.pickIdentityImage(true,index: index),
                                  icon: const Icon(Icons.highlight_remove_rounded,color: Colors.red,size: 25),
                                ),
                              ),
                            ]),
                          );
                        },
                        padding: EdgeInsets.zero,
                        shrinkWrap: true,
                        physics: const NeverScrollableScrollPhysics(),
                        itemCount: signUpController.selectedIdentityImageList.length,
                      ),

                      const SizedBox(height: Dimensions.paddingSizeExtraSmall,),
                      signUpController.selectedIdentityImageList.length<AppConstants.limitOfPickedIdentityImageNumber?
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          DottedBorderBox(
                            height: 110,
                            width: MediaQuery.of(context).size.width,
                            showErrorBorder: !signUpController.isIdentityImageValid,
                            onTap: ()=> signUpController.pickIdentityImage(false),
                          ),

                          if(!signUpController.isIdentityImageValid)
                            Padding(padding: const EdgeInsets.only(top : 5),
                              child: Text("provide_identity_image".tr,
                                style: robotoRegular.copyWith(color: Theme.of(context).colorScheme.error, fontSize: Dimensions.fontSizeSmall),
                              ),
                            )
                        ],
                      ):const SizedBox.shrink(),

                      const SizedBox(height: Dimensions.paddingSizeSmall,),

                      if(signUpController.selectedIdentityImageList.isEmpty)
                        ImageValidationTextWidget(showRatioValidation: false),
                    ],
                  ),
                ],
              ),
            ),
          );},
        ),
      ),
    );
  }
}
