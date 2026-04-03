
import 'package:get/get.dart';
import 'package:demandium_serviceman/utils/core_export.dart';

class MonthlyDashBoardChart extends StatefulWidget {
  const MonthlyDashBoardChart({super.key}) ;

  @override
  MonthlyDashBoardChartState createState() => MonthlyDashBoardChartState();
}

class MonthlyDashBoardChartState extends State<MonthlyDashBoardChart> {
  List<Color> gradientColors = [
    const Color(0xfffc8e91),
    const Color(0xff4560ad),
  ];
  @override
  Widget build(BuildContext context) {

    return GetBuilder<DashboardController>(builder:(dashboardController){
      return Stack(
        children: <Widget>[
          AspectRatio(
            aspectRatio: ResponsiveHelper.isTab(context)? 5 :1.8,
            child: Padding(
              padding: const EdgeInsets.only(
                  right: 8.0,  bottom: 8),
              child: LineChart(
                mainData(dashboardController),
              ),
            ),
          ),
        ],
      );
    });
  }



  Widget bottomTitleWidgets(double value, TitleMeta meta) {
    var style = TextStyle(
      color: Theme.of(context).textTheme.bodyLarge!.color!.withValues(alpha:0.7),
      fontWeight: FontWeight.bold,
      fontSize: 10,
    );
    Widget text;
    switch (value.toInt()) {

      case 1:
        text =  Text('1', style: style);
        break;
      case 2:
        text =  Text('2', style: style);
        break;
      case 3:
        text =  Text('3', style: style);
        break;
      case 4:
        text =  Text('4', style: style);
        break;
      case 5:
        text =  Text('5', style: style);
        break;
      case 6:
        text =  Text('5', style: style);
        break;
      case 7:
        text =  Text('7', style: style);
        break;
      case 8:
        text =  Text('8', style: style);
        break;
      case 9:
        text =  Text('9', style: style);
        break;
      case 10:
        text =  Text('10', style: style);
        break;
      case 11:
        text =  Text('11', style: style);
        break;
      case 12:
        text =  Text('12', style: style);
        break;
      case 13:
        text =  Text('13', style: style);
        break;
      case 14:
        text =  Text('14', style: style);
        break;
      case 15:
        text =  Text('15', style: style);
        break;
      case 16:
        text =  Text('16', style: style);
        break;
      case 17:
        text =  Text('17', style: style);
        break;
      case 18:
        text =  Text('18', style: style);
        break;
      case 19:
        text =  Text('19', style: style);
        break;
      case 20:
        text =  Text('20', style: style);
        break;
      case 21:
        text =  Text('21', style: style);
        break;
      case 22:
        text =  Text('22', style: style);
        break;
      case 23:
        text =  Text('23', style: style);
        break;
      case 24:
        text =  Text('24', style: style);
        break;
      case 25:
        text =  Text('25', style: style);
        break;
      case 26:
        text =  Text('26', style: style);
        break;
      case 27:
        text =  Text('27', style: style);
        break;
      case 28:
        text =  Text('28', style: style);
        break;
      case 29:
        text =  Text('29', style: style);
        break;
      case 30:
        text =  Text('30', style: style);
        break;
      case 31:
        text =  Text('31', style: style);
        break;

      default:
        text =  Text('', style: style);
        break;
    }

    return SideTitleWidget(
      meta: meta,
      space: 10.0,
      child: text,
    );
  }



  LineChartData mainData(DashboardController controller) {
    return LineChartData(
      lineTouchData: const LineTouchData(
        handleBuiltInTouches: true,
        touchTooltipData: LineTouchTooltipData(
          fitInsideHorizontally: true
        ),
      ),
      gridData: const FlGridData(show: true),
      titlesData: FlTitlesData(
          bottomTitles: AxisTitles(
            sideTitles: SideTitles(
              showTitles: true,
              reservedSize: 32,
              interval: controller.monthlyChartList.length==31? 3:
              controller.monthlyChartList.length==32?4:controller.monthlyChartList.length==30?3:4,
              getTitlesWidget: bottomTitleWidgets,
            ),
          ),
          rightTitles: const AxisTitles(
            sideTitles: SideTitles(showTitles: false),
          ),
          topTitles: const AxisTitles(
            sideTitles: SideTitles(showTitles: false),
          ),

          leftTitles: AxisTitles(
              axisNameSize: 20,
              sideTitles: SideTitles(

                showTitles: true,
                reservedSize: controller.mmM<100.00?40:controller.mmM<10000?45:controller.mmM<100000?50:55,
              )

          )

      )
      ,
      borderData: FlBorderData(
        show: true,
        border: const Border(
          bottom: BorderSide(color: Color(0xff4e4965), width: 1),
          left: BorderSide(color: Colors.transparent),
          right: BorderSide(color: Colors.transparent),
          top: BorderSide(color: Colors.transparent),
        ),
      ),
      minX: 0,
      maxX: controller.monthlyChartList.length.toDouble()-1,
      minY: 0,
      maxY: controller.mmM,
      lineBarsData: [
        LineChartBarData(

          spots:  controller.monthlyChartList,
          isCurved: false,
          curveSmoothness: 0,
          gradient: LinearGradient(
            colors: gradientColors,
            begin: Alignment.centerLeft,
            end: Alignment.centerRight,
          ),
          barWidth: 2,
          isStrokeCapRound: true,
          dotData: const FlDotData(
            show: false,
          ),
          belowBarData: BarAreaData(
            show: true,
            gradient: LinearGradient(
              colors: gradientColors
                  .map((color) => color.withValues(alpha:0.4))
                  .toList(),
              begin: Alignment.centerLeft,
              end: Alignment.centerRight,
            ),
          ),
        ),
      ],
    );
  }
}
