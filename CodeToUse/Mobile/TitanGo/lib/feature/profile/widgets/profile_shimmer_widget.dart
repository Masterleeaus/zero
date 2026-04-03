import 'package:demandium_serviceman/utils/core_export.dart';

class ProfileShimmer extends StatelessWidget {
  const ProfileShimmer ({super.key}) ;

  @override
  Widget build(BuildContext context) {
    return Shimmer(
      duration: const Duration(seconds: 3),
      interval: const Duration(seconds: 5), //Default value: Duration(seconds: 0)
      color: Colors.white, //Default value
      colorOpacity: 0, //Default value
      enabled: true, //Default value
      direction: const ShimmerDirection.fromLTRB(),
      child: SingleChildScrollView(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.center,
          children: [
            const Row(),
            const SizedBox(height: Dimensions.paddingSizeLarge,),
            Container(
              height: 100,
              width: 100,
              decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(100),
                color: Theme.of(context).shadowColor
              ),
            ),
            const SizedBox(height: Dimensions.paddingSizeDefault,),
            Container(
              height: 20,
              width: 150,
              decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(Dimensions.paddingSizeExtraSmall),
                color: Theme.of(context).shadowColor
              ),
            ),
            const SizedBox(height: Dimensions.paddingSizeDefault,),

            Row(
              mainAxisAlignment: MainAxisAlignment.spaceEvenly,
              children: [
                Column(
                  crossAxisAlignment: CrossAxisAlignment.center,
                  children: [
                    Container(
                      height: 20,
                      width: 10,
                        color: Theme.of(context).shadowColor
                    ),
                    const SizedBox(height: Dimensions.paddingSizeSmall,),
                    Container(
                      height: 15,
                      width:90,
                      color:Theme.of(context).shadowColor
                    ),
                    const SizedBox(height: Dimensions.paddingSizeExtraSmall,),
                    Container(
                      height: 15,
                      width:50,
                      color:Theme.of(context).shadowColor
                    ),
                  ],
                ),

                Column(
                  crossAxisAlignment: CrossAxisAlignment.center,
                  children: [
                    Container(
                      height: 20,
                      width: 10,
                        color:Theme.of(context).shadowColor
                    ),
                    const SizedBox(height: Dimensions.paddingSizeSmall,),
                    Container(
                      height: 15,
                      width:90,
                      color:Theme.of(context).shadowColor
                    ),
                    const SizedBox(height: Dimensions.paddingSizeExtraSmall,),
                    Container(
                      height: 15,
                      width:50,
                      color:Theme.of(context).shadowColor,
                    ),
                  ],
                ),

                Column(
                  crossAxisAlignment: CrossAxisAlignment.center,
                  children: [
                    Container(
                      height: 20,
                      width: 10,
                      color: Theme.of(context).shadowColor,
                    ),
                    const SizedBox(height: Dimensions.paddingSizeSmall,),
                    Container(
                      height: 15,
                      width:90,
                      color:Theme.of(context).shadowColor,
                    ),
                    const SizedBox(height: Dimensions.paddingSizeExtraSmall,),
                    Container(
                      height: 15,
                      width:50,
                      color:Theme.of(context).shadowColor,
                    ),
                  ],
                ),
              ],
            ),

            const SizedBox(height: Dimensions.paddingSizeLarge,),

            ListView.builder(
            shrinkWrap: true,
            itemCount: 5,
              itemBuilder: (context,index){
              return Padding(
                padding: const EdgeInsets.symmetric(horizontal: Dimensions.paddingSizeDefault,vertical: Dimensions.paddingSizeSmall),
                child: Container(
                  height: 55,
                  decoration: BoxDecoration(
                      color:Theme.of(context).shadowColor,
                      borderRadius: BorderRadius.circular(Dimensions.paddingSizeExtraSmall)
                  ),
                  child: const Row(),
                ),
              );
            })
          ],
        ),
      ),
    );
  }
}
