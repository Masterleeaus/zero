import 'package:demandium_serviceman/common/widgets/zoom_image.dart';
import 'package:demandium_serviceman/utils/core_export.dart';
import 'package:get/get.dart';
import 'package:scroll_to_index/scroll_to_index.dart';



class ImageDetailScreen extends StatefulWidget {
  final List<String> imageList;
  final int index;
  final String appbarTitle;
  final String? createdAt;
  const ImageDetailScreen({
    super.key,required this.imageList,
    required this.index,
    this.appbarTitle = "image_list", this.createdAt
  });

  @override
  State<ImageDetailScreen> createState() => _ImageDetailScreenState();
}

class _ImageDetailScreenState extends State<ImageDetailScreen> {
  AutoScrollController? scrollController;

  @override
  void initState() {

    if(widget.imageList.length ==1){
     Future.delayed(const Duration(milliseconds: 5), (){
       Get.off(ZoomImage(
         imagePath: widget.imageList[0],
         createdAt: widget.createdAt,
         appbarTitle: widget.appbarTitle,
       ));
     });
    }else{
      scrollController = AutoScrollController(
        viewportBoundaryGetter: () => Rect.fromLTRB(0, 0, 0, MediaQuery.of(context).padding.bottom),
        axis: Axis.horizontal,
      );
      scrollController!.scrollToIndex(widget.index, preferPosition: AutoScrollPosition.middle);
      scrollController!.highlight(widget.index);
      super.initState();
    }


  }

  @override
  Widget build(BuildContext context) {
    return widget.imageList.length ==1? const SizedBox() : Scaffold(
      appBar: CustomAppBar(title: widget.appbarTitle,
        subtitle: "${widget.imageList.length} ${'images'.tr} ${widget.createdAt != null ?  " • ${widget.createdAt}" : "" }",
      ),
      body: ListView.builder(
        controller: scrollController,
        padding: const EdgeInsets.symmetric(vertical: Dimensions.paddingSizeSmall),
        itemCount: widget.imageList.length,
        itemBuilder: (BuildContext context, index){
          String imageUrl =  widget.imageList[index];

          return InkWell(
            onDoubleTap: (){
              Get.to(ZoomImage(
                imagePath: imageUrl,
                createdAt: widget.createdAt,
                appbarTitle: widget.appbarTitle,
              ));
            },
            child: Container(
              decoration: BoxDecoration(
                color: Theme.of(context).primaryColor.withValues(alpha:0.2),

              ),
              padding: const EdgeInsets.all(Dimensions.paddingSizeSmall),
              margin: const EdgeInsets.symmetric(vertical: Dimensions.paddingSizeSmall),
              child: AutoScrollTag(
                controller: scrollController!,
                key: ValueKey(index),
                index: index,
                child: Hero(
                  tag: widget.imageList[index],
                  child: CustomImage(
                    image: imageUrl,
                    fit: BoxFit.fitWidth,
                  ),
                ),
              ),
            ),
          ) ;
        },
      ),
    );
  }
}