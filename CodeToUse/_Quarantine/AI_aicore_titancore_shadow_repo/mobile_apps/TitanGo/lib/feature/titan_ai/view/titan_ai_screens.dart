import 'package:demandium_serviceman/utils/core_export.dart';

/// Ask Titan – AI assistant entry point.
/// Placeholder screen; connects to the Titan AI backend when available.
class AskTitanScreen extends StatelessWidget {
  const AskTitanScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Theme.of(context).colorScheme.surface,
      appBar: CustomAppBar(title: 'ask_titan'.tr),
      body: Center(
        child: Padding(
          padding: const EdgeInsets.all(Dimensions.paddingSizeExtraLarge),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(
                Icons.smart_toy_rounded,
                size: 80,
                color: Theme.of(context).primaryColor.withValues(alpha: 0.45),
              ),
              const SizedBox(height: Dimensions.paddingSizeLarge),
              Text(
                'ask_titan'.tr,
                style: robotoBold.copyWith(
                    fontSize: Dimensions.fontSizeOverLarge),
              ),
              const SizedBox(height: Dimensions.paddingSizeDefault),
              Text(
                'titan_ai_coming_soon'.tr,
                style: robotoRegular.copyWith(
                  fontSize: Dimensions.fontSizeDefault,
                  color: Theme.of(context)
                      .textTheme
                      .bodyLarge
                      ?.color
                      ?.withValues(alpha: 0.6),
                ),
                textAlign: TextAlign.center,
              ),
            ],
          ),
        ),
      ),
    );
  }
}

/// Voice Control entry point.
class VoiceControlScreen extends StatelessWidget {
  const VoiceControlScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Theme.of(context).colorScheme.surface,
      appBar: CustomAppBar(title: 'voice_control'.tr),
      body: Center(
        child: Padding(
          padding: const EdgeInsets.all(Dimensions.paddingSizeExtraLarge),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(
                Icons.mic_rounded,
                size: 80,
                color: Theme.of(context).primaryColor.withValues(alpha: 0.45),
              ),
              const SizedBox(height: Dimensions.paddingSizeLarge),
              Text(
                'voice_control'.tr,
                style: robotoBold.copyWith(
                    fontSize: Dimensions.fontSizeOverLarge),
              ),
              const SizedBox(height: Dimensions.paddingSizeDefault),
              Text(
                'voice_coming_soon'.tr,
                style: robotoRegular.copyWith(
                  fontSize: Dimensions.fontSizeDefault,
                  color: Theme.of(context)
                      .textTheme
                      .bodyLarge
                      ?.color
                      ?.withValues(alpha: 0.6),
                ),
                textAlign: TextAlign.center,
              ),
            ],
          ),
        ),
      ),
    );
  }
}

/// Training resources entry point.
class TrainingScreen extends StatelessWidget {
  const TrainingScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Theme.of(context).colorScheme.surface,
      appBar: CustomAppBar(title: 'training'.tr),
      body: Center(
        child: Padding(
          padding: const EdgeInsets.all(Dimensions.paddingSizeExtraLarge),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(
                Icons.school_rounded,
                size: 80,
                color: Theme.of(context).primaryColor.withValues(alpha: 0.45),
              ),
              const SizedBox(height: Dimensions.paddingSizeLarge),
              Text(
                'training'.tr,
                style: robotoBold.copyWith(
                    fontSize: Dimensions.fontSizeOverLarge),
              ),
              const SizedBox(height: Dimensions.paddingSizeDefault),
              Text(
                'training_coming_soon'.tr,
                style: robotoRegular.copyWith(
                  fontSize: Dimensions.fontSizeDefault,
                  color: Theme.of(context)
                      .textTheme
                      .bodyLarge
                      ?.color
                      ?.withValues(alpha: 0.6),
                ),
                textAlign: TextAlign.center,
              ),
            ],
          ),
        ),
      ),
    );
  }
}
