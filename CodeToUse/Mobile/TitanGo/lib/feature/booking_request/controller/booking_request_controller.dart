import 'package:get/get.dart';
import 'package:demandium_serviceman/utils/core_export.dart';


class BookingRequestController extends GetxController  implements GetxService{
  final BookingRequestRepo bookingRequestRepo;
  BookingRequestController({required this.bookingRequestRepo});

  final ScrollController scrollController = ScrollController();
  final ScrollController bookingHistoryScrollController = ScrollController();

  List<String> bookingHistoryStatus = [ "All" , "Completed" , "Canceled"];
  List<String> bookingRequestList = [ "accepted" , "ongoing" ];

  bool _isLoading= false;
  bool get isLoading => _isLoading;

  bool _isFirst= true;
  bool  get isFirst=> _isFirst;

  bool _isPaginationLoading= false;
  bool get isPaginationLoading => _isPaginationLoading;

  int _bookingListPageSize = 1;
  int _offset = 1;
  int get offset => _offset;

  List<BookingRequestModel> _bookingList =[];
  List<BookingRequestModel> get bookingList => _bookingList;

  List<BookingRequestModel> _bookingHistoryList =[];
  List<BookingRequestModel> get bookingHistoryList => _bookingHistoryList;

  int _bookingHistorySelectedIndex = 0;
  int get bookingHistorySelectedIndex =>_bookingHistorySelectedIndex;

  String ? requestType;
  BooingListStatus get bookingStatusState => _bookingStatusState;

  var _bookingStatusState = BooingListStatus.accepted;

  void updateBookingStatusState(BooingListStatus booingListStatus){
      _bookingStatusState=booingListStatus;
      update();
    getBookingList(_bookingStatusState.name.toLowerCase(),1);

  }
  @override
  void onInit(){
    _bookingStatusState = BooingListStatus.accepted;
    super.onInit();
    scrollController.addListener(() {
      if (scrollController.position.pixels == scrollController.position.maxScrollExtent) {
        if (_offset <= _bookingListPageSize) {
          _isPaginationLoading=true;
          bottomLoader();
          update();
          getBookingList( _bookingStatusState.name.toLowerCase(),_offset+1,isFromPagination: true);
        }else{
        }
      }
    });

    bookingHistoryScrollController.addListener(() {
      if (bookingHistoryScrollController.position.pixels == bookingHistoryScrollController.position.maxScrollExtent) {
        if (_offset <= _bookingListPageSize) {
          _isPaginationLoading=true;
          update();
          getBookingHistory(bookingHistoryStatus[_bookingHistorySelectedIndex],_offset+1,isFromPagination: true);
        }else{
        }
      }
    });

  }


  Future<void> getBookingList(String requestType, int offset, {bool isFromPagination = false, bool shouldUpdate = true})async{
     _offset = offset;
    if(!isFromPagination){
      _bookingList=[];
      _isFirst = true;
    }
     update();
    Response response = await bookingRequestRepo.getBookingList(requestType.toLowerCase(), offset);
    if(response.statusCode == 200){
      _isLoading = false;
      _isFirst = false;
      response.body['content']['data'].forEach((item)=> _bookingList.add(BookingRequestModel.fromJson(item)));
      _bookingListPageSize = response.body['content']['last_page'];
    }else{
      _isFirst = false;
      _isLoading = false;
      ApiChecker.checkApi(response);
    }
    update();
  }


  Future<void> getBookingHistory(String requestType, int offset, {bool isFromPagination = false})async{
    _isLoading = true;
    _isFirst = false;
    _offset = offset;
    if(!isFromPagination){
      _isFirst = true;
      _bookingHistoryList=[];
    }
    update();

    Response response = await bookingRequestRepo.getBookingHistoryList(requestType.toLowerCase(),offset);
    if(response.statusCode == 200){
      if(!isFromPagination){
        _bookingHistoryList=[];
      }
      List<dynamic> bookingList = response.body['content']['data'];
      _bookingListPageSize = response.body['content']['last_page'];
      for (var serviceman in bookingList) {
        _bookingHistoryList.add(BookingRequestModel.fromJson(serviceman));
      }
      _isLoading = false;
      _isFirst = false;
      update();
    } else{
      ApiChecker.checkApi(response);
      _isLoading = false;
      _isFirst = false;
      update();
    }
    update();
  }

  void updateBookingHistorySelectedIndex(int index){
    _bookingHistorySelectedIndex = index;
    update();
  }

  void bottomLoader(){
    _isFirst = false;
    _isLoading = true;
    update();
  }

}