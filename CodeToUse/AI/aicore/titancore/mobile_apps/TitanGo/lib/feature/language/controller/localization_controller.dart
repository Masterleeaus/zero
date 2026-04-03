import 'package:demandium_serviceman/utils/core_export.dart';
import 'package:get/get.dart';


class LocalizationController extends GetxController implements GetxService {
  late SharedPreferences sharedPreferences;
  late ApiClient apiClient;

  LocalizationController({required this.sharedPreferences, required this.apiClient}) {
    loadCurrentLanguage();
  }

  Locale _locale = Locale(AppConstants.languages[0].languageCode!, AppConstants.languages[0].countryCode);
  bool _isLtr = true;
  List<LanguageModel> _localLanguages = [];

  Locale get locale => _locale;
  bool get isLtr => _isLtr;
  List<LanguageModel> get localLanguages => _localLanguages;

  void setLanguage(Locale locale, {bool isInitial = false}) {
    Get.updateLocale(locale);
    _locale = locale;
    if(_locale.languageCode == 'ar') {
      _isLtr = false;
    }else {
      _isLtr = true;
    }


    ///pick zone id to update header

    saveLanguage(_locale);
    apiClient.updateHeader(sharedPreferences.getString(AppConstants.token), [], locale.languageCode);
    Get.find<SplashController>().updateLanguage(isInitial);
    update();
  }

  void loadCurrentLanguage() async {
    _localLanguages = [];
    _localLanguages.addAll(AppConstants.languages);
    filterLanguage(isInitial: true);
    update();
  }

  void saveLanguage(Locale locale) async {
    sharedPreferences.setString(AppConstants.languageCode, locale.languageCode);
    sharedPreferences.setString(AppConstants.countryCode, locale.countryCode!);
  }

  int _selectedIndex = 0;

  int get selectedIndex => _selectedIndex;

  void setSelectIndex(int index) {
    _selectedIndex = index;
    update();
  }


  void filterLanguage({bool shouldUpdate = true, bool isChooseLanguage = false, bool isInitial = false,}) {

    List<Language> adminLanguageList = isInitial ? [] : Get.find<SplashController>().configModel?.content?.languageList ?? [];

    bool showAllLocalLanguage = AppConstants.languageCode.length == 1 || adminLanguageList.isEmpty;

    String? defaultLanguageCode;

    List<String> localLanguageCode = AppConstants.languages.map((e) => e.languageCode!).toList();

    for (var defaultLanguage in adminLanguageList) {
      if (!localLanguageCode.contains(defaultLanguage.languageCode)) {
        showAllLocalLanguage = true;
        break;
      }
      if (defaultLanguage.isDefault == true) {
        defaultLanguageCode = defaultLanguage.languageCode;
      }
    }

    if (!showAllLocalLanguage) {
      _localLanguages = [];
      _selectedIndex = 0;

      for (var element in adminLanguageList) {
        int index = AppConstants.languages.indexWhere((language) => language.languageCode == element.languageCode);
        if (index > -1) {
          _localLanguages.add(AppConstants.languages[index]);
        }
      }
      if (isChooseLanguage) {
        _selectedIndex = _localLanguages.indexWhere((e) => e.languageCode == defaultLanguageCode,) != -1
            ? _localLanguages.indexWhere((e) => e.languageCode == defaultLanguageCode) : 0;
      } else {
        for (int index = 0; index < _localLanguages.length; index++) {
          if (_localLanguages[index].languageCode ==
              sharedPreferences.getString(AppConstants.languageCode)) {
            _selectedIndex = index;
            break;
          }
        }
      }
    } else {
      if (defaultLanguageCode != null && isChooseLanguage) {
        _selectedIndex = _localLanguages.indexWhere((e) => e.languageCode == defaultLanguageCode,) != -1
          ? _localLanguages.indexWhere((e) => e.languageCode == defaultLanguageCode) : 0;
      } else if (isChooseLanguage) {
        _selectedIndex = 0;
      } else {
        for (int index = 0; index < _localLanguages.length; index++) {
          if (_localLanguages[index].languageCode ==
              sharedPreferences.getString(AppConstants.languageCode)) {
            _selectedIndex = index;
            break;
          }
        }
      }
    }

    _locale = Locale(
      _localLanguages[_selectedIndex].languageCode!,
      _localLanguages[_selectedIndex].countryCode,
    );
    _isLtr = _locale.languageCode != 'ar';

      Future.delayed(const Duration(milliseconds: 700), (){
        setLanguage(_locale, isInitial: true);
      });
    }

}