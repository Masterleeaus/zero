import 'package:demandium_serviceman/utils/core_export.dart';
import 'package:photo_view/photo_view.dart';

class ZoomImage extends StatefulWidget {
  final String imagePath;
  final String? createdAt;
  final String appbarTitle;
  const ZoomImage({super.key, required this.imagePath, this.createdAt, required this.appbarTitle})
      ;

  @override
  State<ZoomImage> createState() => _ZoomImageState();
}

class _ZoomImageState extends State<ZoomImage> {
  bool isZoomed = false;

  @override
  Widget build(BuildContext context) {

    return Scaffold(
      appBar: isZoomed ? null : CustomAppBar(
        title: widget.appbarTitle,
        subtitle: widget.createdAt,
      ),
      body: Stack(children: [

          PhotoView(

            scaleStateChangedCallback: (value) {
              if (value != PhotoViewScaleState.initial) {
                isZoomed = true;
              } else {
                isZoomed = false;
              }
              setState(() {});
            },
            minScale: PhotoViewComputedScale.contained,
            imageProvider: NetworkImage(widget.imagePath),
          ),

        ],
      ),
    );
  }
}