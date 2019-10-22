<?
ob_start();
?>
<!DOCTYPE html>
<html lang="en-us" id="extr-page">
	<head>
		<meta charset="utf-8">
		<title>Вход в Formetoo CRM</title>
		<meta name="description" content="">
		<meta name="author" content="">
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
		
		<!-- #CSS Links -->
		<!-- Basic Styles -->
		<link rel="stylesheet" type="text/css" media="screen" href="/css/bootstrap.min.css">
		<link rel="stylesheet" type="text/css" media="screen" href="/css/font-awesome.min.css">

		<!-- SmartAdmin Styles : Please note (smartadmin-production.css) was created using LESS variables -->
		<link rel="stylesheet" type="text/css" media="screen" href="/css/smartadmin-production.css">
		<link rel="stylesheet" type="text/css" media="screen" href="/css/smartadmin-skins.min.css">
		
		<!-- Demo purpose only: goes with demo.js, you can delete this css when designing your own WebApp -->
		<link rel="stylesheet" type="text/css" media="screen" href="/css/demo.min.css">

		<!-- #FAVICONS -->
		<link rel="shortcut icon" href="/img/favicon.ico" type="image/x-icon">
		<link rel="icon" href="/img/favicon.ico" type="image/x-icon">

	</head>
	
	<body class="login" style="background-image:url('/img/bg<?=mt_rand(1,6)?>.jpg')">

		<div id="main" role="main">
		<div id="logo-login"></div>
			<!-- MAIN CONTENT -->
			<div id="content" class="container">

				<div class="row">
					
					<div class="col-xs-12 col-sm-12 col-md-5 col-lg-4" style="margin:0 auto;float:none;">
						<div class="well no-padding">
							<form action="/" method="post" id="login-form" class="smart-form client-form">
								<header>
									Вход в <span style="font-family:Yanone Kaffeesatz;font-weight:700;font-size:21px;"><span style="color:#f90">F</span><span style="color:#545454">ormetoo CRM</span></span>
								</header>

								<fieldset>
									<?
										if(isset($_GET['login-error']))
											echo '<section><center><b style="color:red">Логин или пароль неверны!</b></center></section>'
									?>
									<section>
										<label class="label">E-mail</label>
										<label class="input"> <i class="icon-append fa fa-user"></i>
											<input type="email" name="email">
											<b class="tooltip tooltip-top-right"><i class="fa fa-user txt-color-teal"></i>Укажите e-mail для входа в систему</b></label>
									</section>

									<section>
										<label class="label">Пароль</label>
										<label class="input"> <i class="icon-append fa fa-lock"></i>
											<input type="password" name="password">
									</section>
									<section>
										<label class="checkbox">
											<input type="checkbox" name="rm" checked="">
											<i></i>Оставаться в системе</label>
									</section>
								</fieldset>
								<footer>
									<div class="note" style="float:left;margin-top:15px;">
										<a href="?forgot">Напомнить пароль</a>
									</div>
									<button type="submit" class="btn btn-primary">
										Войти
									</button>
								</footer>
								<input type="hidden" name="action" value="user_login"/>
							</form>

						</div>						
					</div>
				</div>
			</div>

		</div>

		<!--================================================== -->	

	    <!-- Link to Google CDN's jQuery + jQueryUI; fall back to local -->
	    <script src="js/libs/jquery-2.0.2.min.js"></script>

	    <script src="js/libs/jquery-ui-1.10.3.min.js"></script>

		<!-- JS TOUCH : include this plugin for mobile drag / drop touch events 		
		<script src="js/plugin/jquery-touch/jquery.ui.touch-punch.min.js"></script> -->

		<!-- BOOTSTRAP JS -->		
		<script src="/js/bootstrap/bootstrap.min.js"></script>

		<!-- JQUERY VALIDATE -->
		<script src="/js/plugin/jquery-validate/jquery.validate.min.js"></script>
		
		<!-- JQUERY MASKED INPUT -->
		<script src="/js/plugin/masked-input/jquery.maskedinput.min.js"></script>
		
		<!--[if IE 8]>
			
			<h1>Your browser is out of date, please update your browser by going to www.microsoft.com/download</h1>
			
		<![endif]-->

		<!-- MAIN APP JS FILE -->
		<script src="/js/app.min.js"></script>

		<script type="text/javascript">
			$(document).ready(function(){
				var html=$('html').height();
				$('body').css('height',html);
			});
			runAllForms();

			$(function() {
				
				$("#login-form").validate({
					
					rules : {
						email : {
							required : true,
							email : true
						},
						password : {
							required : true,
							minlength : 6,
							maxlength : 24
						}
					},

					
					messages : {
						email : {
							required : 'Поле является обязательным к заполнению',
							email : 'Укажите правильный e-mail'
						},
						password : {
							required : 'Поле является обязательным к заполнению',
							minlength: 'Пароль должен состоять минимум из 6-ти символов',
							maxlength: 'Пароль должен состоять максимум из 20-ти символов'
						}
					},

				
					errorPlacement : function(error, element) {
						error.insertAfter(element.parent());
					}
				});
			});
		</script>

	</body>
</html>
<?
	transform::optimize(ob_get_clean());
?>