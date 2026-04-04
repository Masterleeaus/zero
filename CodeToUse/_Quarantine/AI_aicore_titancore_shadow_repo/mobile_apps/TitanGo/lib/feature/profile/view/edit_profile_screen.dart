import 'package:get/get.dart';
import 'package:demandium_serviceman/utils/core_export.dart';

class EditProfileScreen extends StatefulWidget {
  const EditProfileScreen({super.key}) ;
  @override
  State<EditProfileScreen> createState() => _EditProfileScreenState();
}
class _EditProfileScreenState extends State<EditProfileScreen> with SingleTickerProviderStateMixin {
  TabController? tabController;


  @override
  void initState() {
    super.initState();
    tabController = TabController(vsync: this, length: 2);
    Get.find<UserController>().pickImage(removePickedProfileImage: true, shouldUpdate: false);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Theme.of(context).colorScheme.surface,
      appBar: CustomAppBar(title: "edit_profile".tr),
      body:  GetBuilder<UserController>(
        builder: (userController){
          return Column(
            children: [
              const SizedBox(height: Dimensions.paddingSizeDefault,),
              Container(
                height: 45,
                width: Get.width,
                margin: const EdgeInsets.symmetric(horizontal: Dimensions.paddingSizeDefault),
                decoration: BoxDecoration(
                  border:  Border(
                    bottom: BorderSide(color: Theme.of(context).primaryColor.withValues(alpha:0.7), width: 1),
                  ),
                ),
                child: TabBar(
                    unselectedLabelColor:Theme.of(context).textTheme.bodyLarge?.color?.withValues(alpha:0.5),
                    indicatorColor: Theme.of(context).primaryColor,
                    controller: tabController,
                    labelColor: Theme.of(context).primaryColorLight,
                    labelStyle:  robotoMedium.copyWith(fontSize: Dimensions.fontSizeLarge),
                    labelPadding: EdgeInsets.zero,
                  tabs: [
                    SizedBox(height: 40,width: MediaQuery.of(context).size.width*.5,
                        child:  Center(child: Text("general_info".tr))
                    ),
                    SizedBox(height: 40,width: MediaQuery.of(context).size.width*.5,
                        child:  Center(child: Text("account_info".tr))
                    ),
                  ],

                  onTap: (int index) {
                    switch (index) {
                      case 0:
                        userController.updatePageCurrentState(EditProfileTabControllerState.generalInfo);
                        break;
                      case 1:
                        userController.updatePageCurrentState(EditProfileTabControllerState.accountIno);
                        break;
                    }
                  }),
              ),

               Expanded(
                child: TabBarView(controller: tabController  ,children: const [
                  EditProfileGeneralInfo(),
                  EditProfileAccountInfo()
                ]),
              )
            ],
          );
          },
      ),
    );
  }
}
