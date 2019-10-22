<?
header("HTTP/1.0 404 Not Found");
?>
<!DOCTYPE html>
<html id="html404">
<head>
<meta http-equiv="content-type" content="text/html;charset=utf-8"/>
<link rel="stylesheet" type="text/css" href="/css/style.css" />
<link rel="shortcut icon" href="/favicon.ico" />
<title>404 — Страница не найдена</title>
</head>
<body id="body404">
<div id="main404">
	<div id="digits404"></div>
	<div id="content404">
		<h1>Нет такой страницы.</h1>
		<p>Страницы с адресом <? echo $_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']; ?> сейчас нет на нашем сайте, но, возможно, она появится чуть позже<span style="font-family:'arial',sans-serif">☺</span></p>
		<p>Если вы уверены, что попали на эту страницу по нашей вине, пожалуйста, сообщите нам об этом через <a href="/contacts/#feedback">форму обратной связи<span class="underline"></span></a>.</p>
		<p>Воспользуйтесь ссылками ниже, чтобы попасть в существующие разделы нашего сайта:</p>
		<p id="links404">
			<a href="/" title="Главная">Главная<span class="underline"></span></a>
			<a href="/uslugi/" title="Услуги">Услуги<span class="underline"></span></a>
			<a href="/price/" title="Документация">Прайс-лист<span class="underline"></span></a>
			<a href="/contacts/" title="Контакты">Контакты<span class="underline"></span></a>
		</p>
	</div>
</div>
<div id="logo404"><a href="/"></a></div>
			<!-- Yandex.Metrika counter -->
			<script type="text/javascript">
			(function (d, w, c) {
				(w[c] = w[c] || []).push(function() {
					try {
						w.yaCounter21137620 = new Ya.Metrika({id:21137620,
								webvisor:true,
								clickmap:true,
								trackLinks:true,
								accurateTrackBounce:true});
					} catch(e) { }
				});

				var n = d.getElementsByTagName("script")[0],
					s = d.createElement("script"),
					f = function () { n.parentNode.insertBefore(s, n); };
				s.type = "text/javascript";
				s.async = true;
				s.src = (d.location.protocol == "https:" ? "https:" : "http:") + "//mc.yandex.ru/metrika/watch.js";

				if (w.opera == "[object Opera]") {
					d.addEventListener("DOMContentLoaded", f, false);
				} else { f(); }
			})(document, window, "yandex_metrika_callbacks");
			</script>
			<noscript><div><img src="//mc.yandex.ru/watch/21137620" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
			<!-- /Yandex.Metrika counter -->
</body>
</html>