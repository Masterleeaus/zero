import 'package:cached_network_image/cached_network_image.dart';
import 'package:demandium_serviceman/utils/images.dart';
import 'package:flutter/material.dart';

class CustomImage extends StatelessWidget {
  final String? image;
  final double? height;
  final double? width;
  final BoxFit? fit;
  final String placeholder;
  const CustomImage({super.key, required this.image, this.height, this.width, this.fit = BoxFit.cover, this.placeholder = Images.placeholder});

  @override
  Widget build(BuildContext context) {
    return CachedNetworkImage(
      imageUrl: image!, height: height, width: width, fit: BoxFit.cover,
      placeholder: (context, url) => Image.asset(placeholder, height: height, width: width, fit: BoxFit.cover),
      errorWidget: (context, url, error) => Image.asset(placeholder, height: height, width: width, fit: BoxFit.cover),
    );
  }
}
