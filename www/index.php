<?
define('_DSITE', 1);
ini_set('display_errors', 1);
ini_set('memory_limit', '1024M');
require_once('../functions/main.php');
ob_start();
?>
<!DOCTYPE html>
<html lang="en-us">

<head>
	<meta charset="utf-8">
	<!--<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">-->

	<meta name="description" content="<?= isset($current['description']) ? $current['description'] : '' ?>" />
	<meta name="keywords" content="<?= isset($current['keywords']) ? $current['keywords'] : '' ?>" />
	<title><? echo $current['m_content_title']; ?> — Formetoo ERP</title>

	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
	<link href='https://fonts.googleapis.com/css?family=Yanone+Kaffeesatz:700,400' rel='stylesheet' type='text/css'>
	<!-- Basic Styles -->
	<link rel="stylesheet" type="text/css" media="screen" href="/css/bootstrap.css">
	<link rel="stylesheet" type="text/css" media="screen" href="/css/font-awesome.css">

	<!-- SmartAdmin Styles : Please note (smartadmin-production.css) was created using LESS variables -->
	<link rel="stylesheet" type="text/css" media="screen" href="/css/smartadmin-production.css">
	<link rel="stylesheet" type="text/css" media="screen" href="/css/smartadmin-skins.min.css">

	<!-- SmartAdmin RTL Support is under construction
			 This RTL CSS will be released in version 1.5
		<link rel="stylesheet" type="text/css" media="screen" href="/css/smartadmin-rtl.min.css"> -->

	<!-- We recommend you use "your_style.css" to override SmartAdmin
		     specific styles this will also ensure you retrain your customization with each SmartAdmin update.
		<link rel="stylesheet" type="text/css" media="screen" href="/css/your_style.css"> -->

	<!-- Demo purpose only: goes with demo.js, you can delete this css when designing your own WebApp -->
	<link rel="stylesheet" type="text/css" media="screen" href="/css/demo.min.css">

	<!-- FAVICONS -->
	<link rel="shortcut icon" href="/img/favicon.ico" type="image/x-icon">
	<link rel="icon" href="/img/favicon.ico" type="image/x-icon">

	<!-- Link to Google CDN's jQuery + jQueryUI; fall back to local -->
	<script src="/js/jquery-2.1.1.min.js"></script>
</head>

<body class="">
	<!-- possible classes: minified, fixed-ribbon, fixed-header, fixed-width-->

	<!-- HEADER -->
	<header id="header">
		<div id="logo-group">

			<!-- PLACE YOUR LOGO HERE -->
			<span id="logo"><img src="/img/logo.png" alt="Formetoo ERP"> </span>
			<!-- END LOGO PLACEHOLDER -->

			<!-- Note: The activity badge color changes when clicked and resets the number to 0
				Suggestion: You may want to set a flag when this happens to tick off all checked messages / notifications -->
			<span id="activity" class="activity-dropdown" style="display:none"> <i class="fa fa-user"></i> <b class="badge"> 21 </b> </span>

			<!-- AJAX-DROPDOWN : control this dropdown height, look and feel from the LESS variable file -->
			<div class="ajax-dropdown">

				<!-- the ID links are fetched via AJAX to the ajax container "ajax-notifications" -->
				<div class="btn-group btn-group-justified" data-toggle="buttons">
					<label class="btn btn-default">
						<input type="radio" name="activity" id="/ajax/notify/mail.html">
						Msgs (14) </label>
					<label class="btn btn-default">
						<input type="radio" name="activity" id="/ajax/notify/notifications.html">
						notify (3) </label>
					<label class="btn btn-default">
						<input type="radio" name="activity" id="/ajax/notify/tasks.html">
						Tasks (4) </label>
				</div>

			</div>
			<!-- END AJAX-DROPDOWN -->
		</div>

		<!-- projects dropdown -->
		<div class="project-context hidden-xs">

			<span class="label" style="font-family:'Arial'">История:</span>
			<span class="project-selector dropdown-toggle" data-toggle="dropdown">Последние транзакции <i class="fa fa-angle-down"></i></span>

			<!-- Suggestion: populate this list with fetch and push technique -->
			<ul class="dropdown-menu">
				<?
				/* if($t_history=$terminal->history('',10))
						foreach($t_history as $t_h)
							echo '<li><a href="javascript:void(0);">',
								date('G:i:s',$t_h['m_transactions_date']).', '.transform::date_f($t_h['m_transactions_date']).' — ',
								($t_h['m_transactions_type']!='return')?('покупка на сумму <b>'.transform::price_o($t_h['m_transactions_sum_buy']).'</b> р., '.(($t_h['m_transactions_type']=='cash')?'начислено бонусов: ':'оплачено бонусами: ').'<b>'.$t_h['m_transactions_sum_bonus'].'</b> р.'):('возврат товара на сумму <b>'.transform::price_o($t_h['m_transactions_sum_buy']).'</b> р., возврат бонусов <b>'.$t_h['m_transactions_sum_bonus'].'</b> р.');
								'</a></li>'; */
				?>
			</ul>
			<!-- end dropdown-menu-->

		</div>
		<!-- end projects dropdown -->
		<div id="userinfo">
			<div id="userinfo-foto"></div>
			<a href="javascript:void(0);" id="userinfo-name" class="btn btn-default" rel="popover" data-placement="bottom" data-content="
						<form action='' style='min-width:300px;'>
							<div id='userinfo-detail-info'>
								<img id='userinfo-detail-foto' src=''/>
								<div id='userinfo-detail-name'><b><?= $user->getInfo('m_users_name') ?></b></div>
								<div id='userinfo-detail-email'><?= $user->getInfo('m_users_email') ?></div>
								<div id='userinfo-detail-settings-block'>
									
								</div>
							</div>
							<div class='form-actions'>
								<div class='row'>
									<div class='col-md-6' style='float:left'>
										<a id='userinfo-detail-settings' href='javascript:void(0);' class='btn btn-sm bg-color-blueLight txt-color-white'>
											<i class='fa fa-gear'></i>
											 Настройки аккаунта
										</a>
									</div>
									<div class='col-md-4' style='float:right'>
										<button class='btn btn-sm btn-primary logout' id='logout' type='button'>
											<i class='fa fa-sign-out'></i>
											Выйти
										</button>
									</div>
								</div>
							</div>
						</form>" data-html="true">
				<?= transform::fio($user->getInfo('m_users_name')) ?>
			</a>
		</div>
	</header>
	<!-- END HEADER -->

	<!-- Left panel : Navigation area -->
	<!-- Note: This width of the aside area can be adjusted through LESS variables -->
	<aside id="left-panel">
		<!-- User info -->
		<div class="login-info">
			<span>
				<!-- User image size is adjusted inside CSS, it should stay as it -->
				<a href="/" title="Рабочий стол" style="margin-top:10px;margin-left:-2px;font-weight:600;color:">
					<i class="fa fa-lg fa-fw fa-home" style="font-size:23px;margin-right:3px;margin-top:2px;"></i>
					<span class="menu-item-parent" style="font-size:14px;">Рабочий стол</span>
				</a>

			</span>
		</div>
		<!-- end user info -->

		<!-- NAVIGATION : This navigation is also responsive

			To make this navigation dynamic please make sure to link the node
			(the reference to the nav > ul) after page load. Or the navigation
			will not initialize.
			-->
		<nav>
			<!-- NOTE: Notice the gaps after each icon usage <i></i>..
				Please note that these links work a bit different than
				traditional href="" links. See documentation for details.
				-->
			<? $menu->display('main') ?>
			<a href="/importExcel.php" target="_blank" title="Импорт excel" style="margin-left: 15px"><span class="menu-item-parent">Импорт excel</span></a>
			<br />
			<a href="/exportExcel.php" title="Экспорт excel" style="margin-left: 15px"><span class="menu-item-parent">Экспорт excel</span></a>
			<br />
			<a href="/importExcelSEO.php" target="_blank" title="Импорт excel" style="margin-left: 15px"><span class="menu-item-parent">Импорт excelSEO</span></a>
			<br />
			<a href="/exportExcelSEO.php" target="_blank" title="Импорт excel" style="margin-left: 15px"><span class="menu-item-parent">Экспорт excelSEO</span></a>
		</nav>
		<span class="minifyme" data-action="minifyMenu">
			<i class="fa fa-arrow-circle-left hit"></i>
		</span>

		<div id="faster-nav-top-bottom">
			<div id="faster-nav-top"><i class="fa fa-arrow-circle-up"></i>Наверх</div>
			<div id="faster-nav-bottom"><i class="fa fa-arrow-circle-down"></i>Вниз</div>
		</div>

	</aside>
	<!-- END NAVIGATION -->

	<!-- MAIN PANEL -->
	<div id="main" role="main">

		<!-- RIBBON -->
		<div id="ribbon">

			<!-- breadcrumb -->
			<ol class="breadcrumb">
				<? $menu->breadcrumbs() ?>
			</ol>
			<!-- end breadcrumb -->

		</div>
		<!-- END RIBBON -->

		<!-- MAIN CONTENT -->
		<div id="content">

			<div class="row">
				<div class="col-xs-12 col-sm-7 col-md-7 col-lg-4">
					<h1 class="page-title txt-color-blueDark">
						<?= $current['m_content_h1'] ?>
					</h1>
				</div>
			</div>

			<?= $current['m_content_content'] ?>

		</div>
		<!-- END MAIN CONTENT -->

	</div>
	<!-- END MAIN PANEL -->

	<!-- PAGE FOOTER -->
	<div class="page-footer">
		<div class="row">
			<div class="col-xs-12 col-sm-6">
				<span class="txt-color-white">Formetoo.ERP © 2014</span>
			</div>
		</div>
	</div>
	<!-- END PAGE FOOTER -->

	<!--================================================== -->

	<script src="/js/libs/jquery-ui-1.10.3.min.js"></script>

	<!-- BOOTSTRAP JS -->
	<script src="/js/bootstrap/bootstrap.min.js"></script>

	<!-- CUSTOM NOTIFICATION -->
	<script src="/js/notification/SmartNotification.min.js"></script>

	<!-- JARVIS WIDGETS -->
	<script src="/js/smartwidgets/jarvis.widget.min.js"></script>

	<!-- JQUERY VALIDATE -->
	<script src="/js/plugin/jquery-validate/jquery.validate.js"></script>

	<!-- JQUERY MASKED INPUT -->
	<script src="/js/plugin/masked-input/jquery.maskedinput.min.js"></script>

	<!-- JQUERY SELECT2 INPUT -->
	<script src="/js/plugin/select2/select2.min.js"></script>

	<!-- browser msie issue fix -->
	<script src="/js/plugin/msie-fix/jquery.mb.browser.min.js"></script>

	<!--[if IE 8]>

		<h1>Your browser is out of date, please update your browser by going to www.microsoft.com/download</h1>

		<![endif]-->

	<!-- MAIN APP JS FILE -->
	<script src="/js/app.min.js"></script>

	<script src="/js/add_functions.js"></script>

	<!-- PAGE RELATED PLUGIN(S) -->
	<script src="/js/plugin/datatables/jquery.dataTables.min.js"></script>
	<script src="/js/plugin/datatables/dataTables.colVis.min.js"></script>
	<script src="/js/plugin/datatables/dataTables.tableTools.min.js"></script>
	<script src="/js/plugin/datatables/dataTables.bootstrap.min.js"></script>
	<script src="/js/plugin/x-editable/x-editable.min.js"></script>

	<script type="text/javascript">
		$(document).ready(function() {

			function datepicker() {
				$("input.datepicker").each(function(index, el) {
					$(el).datepicker("option", "altFormat", "yy-mm-dd <?= dtu('', 'H:i:s') ?>");
					$(el).datepicker("option", "changeMonth", true);
					$(el).datepicker("option", "changeYear", true);
					$(el).datepicker("option", "dateFormat", "dd.mm.yy");
					$(el).datepicker("option", "dayNamesMin", ["Вс", "Пн", "Вт", "Ср", "Чт", "Пт", "Сб"]);
					$(el).datepicker("option", "monthNames", ["Январь", "Февраль", "Март", "Апрель", "Май", "Июнь", "Июль", "Август", "Сентябрь", "Октябрь", "Ноябрь", "Декабрь"]);
					$(el).datepicker("option", "monthNamesShort", ["января", "февраля", "марта", "апреля", "мая", "июня", "июля", "августа", "сентября", "октября", "ноября", "декабря"]);
					$(el).datepicker("option", "firstDay", 1);
					$(el).each(function(index, el) {
						$(el).datepicker("option", "altField", $(el).next());
					});
				});

			}

			{requireJS}

			datepicker();

			pageSetUp();
			$(document).on('click', '#logout', function() {
				window.location = '/?logout';
				return false;
			});
			var userinfodetail = 0;
			$('#userinfo-name').on('click', function() {
				if (!userinfodetail)
					userinfodetail = $('#userinfo-foto').offset();
				$('#userinfo-detail-info').parents('.popover').css({
					'left': userinfodetail.left - 144,
					'top': '+=5'
				});
			});

			$("div.toolbar").html('<div class="text-right"><img src="img/logo.png" alt="SmartAdmin" style="width: 111px; margin-top: 3px; margin-right: 10px;"></div>');

			$('select.autoselect').select2();

			$('.datatable').each(function(index, el) {
				var order = [],
					no_order = [];
				if ($(this).find('th.order').length)
					order = [$(this).find('th.order').index(), $(this).find('th.order').attr('order')];

				$(this).find('th.no-order').each(function(index, el) {
					no_order[no_order.length] = index;
				});

				var lengthMenu = [
					[20, 50, 100, -1],
					[20, 50, 100, "Все"]
				];
				if ($(this).attr('data-paging') == 'false')
					lengthMenu = [
						[-1],
						["Все"]
					];

				if ($(this).find('th.order').length)
					var otable = $(this).DataTable({
						"searching": $(this).hasClass("not-search") ? false : true,
						"order": [order],
						"lengthMenu": lengthMenu,
						"columnDefs": [{
							"targets": no_order,
							"orderable": false
						}],
						"fnDrawCallback": function(oSettings) {}
					});
				else
					var otable = $(this).DataTable({
						"searching": $(this).hasClass("not-search") ? false : true,
						"lengthMenu": lengthMenu,
						"columnDefs": [{
							"targets": no_order,
							"orderable": false
						}],
						"fnDrawCallback": function(oSettings) {}
					});
				$(".th-filter").on("change", function() {
					var search = '';
					if ($(this).find('option:selected').val() != 0)
						search = $.trim($(this).find('option:selected').text());
					otable
						.column($(this).parent().index() + ':visible')
						.search(search)
						.draw();
				});

				$(this).on('length.dt', function(e, settings, len) {
					if (len === null || len === undefined || !len)
						localStorage.setItem("row_count", 20);
					else {
						localStorage.setItem("row_count", len);
					}
				});

				if (isNaN(localStorage.getItem("row_count")) || localStorage.getItem("row_count") === null) {} else {
					$(this).DataTable().page.len(localStorage.getItem("row_count") * 1).draw();
				}
			});

			if (localStorage.getItem("select_order"))
				$("#select_order").select2("val", localStorage.getItem("select_order") + "").triggerHandler("change");

			$("span.minifyme").on('mousedown', function() {
				if (localStorage.getItem("minify_menu") && localStorage.getItem("minify_menu") == "1")
					localStorage.setItem("minify_menu", "0");
				else {
					localStorage.setItem("minify_menu", "1");
				}
			});
			if (localStorage.getItem("minify_menu") && localStorage.getItem("minify_menu") == "1")
				$("span.minifyme").trigger('click');


			$(window).resize(function() {
				$('body').height($(document).height());
			});

			$('body').height($(document).height());

			$('input').attr('autocomplete', 'off');

			$('#faster-nav-top').on('click', function() {
				$('html, body').animate({
					scrollTop: 0
				}, 'slow');
			});
			$('#faster-nav-bottom').on('click', function() {
				$('html, body').animate({
					scrollTop: $(document).height()
				}, 'slow');
			});


			$('form').on('keydown', function(event) {
				if (event.keyCode == 13 && event.target.get(0).tagName != 'textarea') {
					event.preventDefault();
					return false;
				}
			});

			/* function rowspan(){
				$("tr").each(function(index,el){
					$(el).find("td.unionrows").each(function(index1,el1){
						if($(el1).text()==$(el).next().find("td.unionrows:eq("+index1+")").text())
							$(el).next().find("td.unionrows:eq("+index1+")").addClass("remove");
					});
				});
				$("tr").each(function(index,el){
					$(el).find("td.unionrows.remove").each(function(index1,el1){
						var tdrowspan;
						$(el).prevAll().each(function(ind,e){
							if($(e).find("td.unionrows:not(\'.remove\'):eq("+index1+")").length){
								tdrowspan=$(e).find("td.unionrows:not(\'.remove\'):eq("+index1+")");
								return false;
							}
						});
						var rowspan=tdrowspan.attr("rowspan")?tdrowspan.attr("rowspan"):1;
						tdrowspan.attr("rowspan",rowspan*1+1);
						$(el1).remove();
					});
				});
			}
			rowspan(); */

		});
	</script>


</body>

</html>
<?
transform::optimize(ob_get_clean());
?>