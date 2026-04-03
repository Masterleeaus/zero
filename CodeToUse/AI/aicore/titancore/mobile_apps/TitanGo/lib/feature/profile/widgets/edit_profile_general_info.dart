import 'package:get/get.dart';
import 'package:demandium_serviceman/utils/core_export.dart';

class EditProfileGeneralInfo extends StatelessWidget {
  const EditProfileGeneralInfo({super.key}) ;
  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      physics: const BouncingScrollPhysics(),
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 15.0),
        child: GetBuilder<UserController>(
          builder: (userController){
            return userController.contents == null ? const ProfileInfoShimmer()
            : Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const SizedBox(height: Dimensions.paddingSizeLarge),
                Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [

                    Stack(alignment: AlignmentDirectional.center,
                      children: [
                        userController.pickedFile==null ? ClipRRect(
                          borderRadius: BorderRadius.circular(50),
                          child: CustomImage(
                            image: userController.userInfo.profileImageFullPath??"", height: 100, width: 100,
                            placeholder: Images.userPlaceHolder,
                          ),
                        ) : CircleAvatar(radius: 50, backgroundImage:FileImage(File(userController.pickedFile!.path))),

                        IconButton( onPressed: ()=>userController.pickImage(),
                          icon: Icon(Icons.camera_enhance_rounded, color: light.cardColor),
                        ),
                      ],
                    ),
                  ],
                ),

                customRichText("full_name".tr,true,context),
                NonEditableTextField(text: userController.userInfo.firstName??"",text2: userController.userInfo.lastName??""),

                customRichText("email".tr,true,context),
                CustomTextFormField(
                  isShowSuffixIcon: true,
                  controller: userController.emailController,
                  hintText: "enter_email_address".tr,
                  isShowBorder: true,
                ),
                customRichText("select_identity_type".tr,true,context),
                NonEditableTextField(text: userController.userInfo.identificationType!=null?userController.userInfo.identificationType.toString().tr:'',),

                customRichText("identity_number".tr,true,context),
                NonEditableTextField(text: userController.userInfo.identificationNumber??"",),

                const SizedBox(height: Dimensions.paddingSizeLarge),

                if(userController.userInfo.identificationImageFullPath != null && userController.userInfo.identificationImageFullPath!.isNotEmpty)
                  GridView.builder(
                    gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
                        crossAxisCount: ResponsiveHelper.isTab(context) ? 2 : 1,
                        crossAxisSpacing: Dimensions.paddingSizeSmall,
                        mainAxisSpacing: Dimensions.paddingSizeSmall,
                        mainAxisExtent: 200
                    ),
                    itemBuilder: (context,index){
                      return  InkWell(
                        onTap: (){
                          Get.to(ImageDetailScreen(
                            imageList: userController.userInfo.identificationImageFullPath ?? [],
                            index: index,
                            appbarTitle: 'identification_image'.tr,
                          ),
                          );
                        },
                        child: ClipRRect(borderRadius: BorderRadius.circular(10),
                          child: CustomImage(
                            fit: BoxFit.fill,
                            image: userController.userInfo.identificationImageFullPath![index],
                          ),
                        ),
                      );
                    },
                    physics: const NeverScrollableScrollPhysics(),
                    shrinkWrap: true,
                    itemCount: userController.userInfo.identificationImageFullPath!.length,
                  ),

                const SizedBox(height: Dimensions.paddingSizeLarge),


               CustomButton(
                 btnTxt: "save".tr,
                 isLoading: userController.isLoading,
                 onPressed: ()=> _updateProfile(context,userController),
                ),
                const SizedBox(height: Dimensions.paddingSizeLarge),
            ]);
          },
        )
      ),
    );
  }

  void _updateProfile(BuildContext context, UserController profileController) {
    if(profileController.emailController!.text.isEmpty){
      showCustomSnackBar("enter_email_address".tr, type: ToasterMessageType.info);
    }
    else{
      profileController.updateProfile();
    }
  }

  Widget customRichText(String title,bool isRequired,BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 8.0),
      child: RichText(
          text: TextSpan(children: <TextSpan>[
        TextSpan(
            text: title,
            style: robotoMedium.copyWith(color: Theme.of(context).textTheme.bodyLarge!.color!.withValues(alpha:0.8))),
        TextSpan(text: isRequired?' *':"", style: robotoRegular.copyWith(color: Theme.of(context).colorScheme.error)),
      ])),
    );
  }

}
