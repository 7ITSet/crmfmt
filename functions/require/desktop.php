<?
defined ('_DSITE') or die ('Access denied');
global $user,$sql,$terminal;

require('../functions/classes/charts.php');
$chart=new charts;

//$chart_data=$chart->linear(time()-2678400);
$chart_data['2017-04-01']=2500;
$chart_data['2017-05-01']=3500;
$chart_data['2017-06-01']=11500;
?>
<style>
#chart-transactions {background: #3f3f4f;color:#ffffff;	
	width		: 100%;
	height		: 500px;
	font-size	: 11px;
}				
	width	: 100%;
	height	: 500px;
}					
</style>
<script type="text/javascript" src="/js/amcharts/amcharts.js"></script>
<script type="text/javascript" src="/js/amcharts/serial.js"></script>
<script type="text/javascript" src="/js/amcharts/themes/dark.js"></script>
<script type="text/javascript" src="/js/amcharts/lang/ru.js"></script>
<script>
$(document).ready(function(){
var chart = AmCharts.makeChart("chart-transactions", {
        "type": "serial",
        "theme": "dark",
		"language":"ru",
        "pathToImages": "/js/amcharts/images/",
        "dataDateFormat": "YYYY-MM-DD",
        "valueAxes": [{
            "id":"v1",
            "axisAlpha": 0,
            "position": "left"
        }],
        "graphs": [
			{
				"id": "g1",
				"bullet": "round",
				"bulletBorderAlpha": 1,
				"balloonText": "[[title]]: [[value]] р.",
				"bulletColor": "#FFFFFF",
				"bulletSize": 5,
				"hideBulletsCount": 50,
				"lineThickness": 2,
				"title": "Сумма продаж за день",
				"type": "smoothedLine",
				"useLineColorForBulletBorder": true,
				"valueField": "cash"
			}
		],
        "chartScrollbar": {
			"graph": "g1",
			"scrollbarHeight": 30
		},
        "chartCursor": {
            "cursorPosition": "mouse",
            "pan": true,
             "valueLineEnabled":true,
             "valueLineBalloonEnabled":true
        },
        "categoryField": "date",
        "categoryAxis": {
            "parseDates": true,
            "dashLength": 1,
            "minorGridEnabled": true,
            "position": "top"
        },
        exportConfig:{
          menuRight: '20px',
          menuBottom: '50px',
          menuItems: [{
          icon: '/js/amcharts/images/export.png',
          format: 'png'	  
          }]  
        },
        "dataProvider": [
<?
foreach($chart_data as $v=>$k)
	echo '{
		"date":"'.$v.'",
		"cash":'.$k.'},';

?>		
		]
    }
);

chart.addListener("rendered", zoomChart);

zoomChart();
function zoomChart(){
    chart.zoomToIndexes(chart.dataProvider.length - 40, chart.dataProvider.length - 1);
}


});
</script>
<!-- widget grid -->
				<section id="widget-grid" class="">
				
				
				
				
					<!-- row -->
					<div class="row">

<!-- NEW WIDGET START -->
						
					<article class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
						
							<!-- Widget ID (each widget will need unique ID)-->
							<div class="jarviswidget jarviswidget-color-blue" id="wid-id-0" data-widget-editbutton="false" data-widget-colorbutton="false" data-widget-deletebutton="false">
												

				
								<header>
									<span class="widget-icon"> <i class="fa fa-bar-chart-o"></i> </span>
									<h2>График продаж</h2>
				
								</header>
				
								<!-- widget div-->
								<div>				
									<!-- widget content -->
									<div class="widget-body no-padding">
				
										<div id="chart-transactions" style="height:400px;width:100%;"></div>
				
									</div>
									<!-- end widget content -->
				
								</div>
								<!-- end widget div -->
				
							</div>
							<!-- end widget -->
	
						</article>
					
						<!-- WIDGET END -->

					</div>
				
					<!-- end row -->
				
					<!-- end row -->
				
				</section>
				<!-- end widget grid -->