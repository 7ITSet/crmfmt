<?
defined ('_DSITE') or die ('Access denied');

if(isset($_GET['thank_you'])){
?>
<h2 style="text-align:center;">Спасибо за Ваше сообщение, мы обязательно рассмотрим его!</h2>
<?
}
else{
?>
<a name="feedback"></a>
<form action="" method="POST" id="feedback-form">
	<table class="params">
		<tr><td>Ваше имя</td><td><input name="feedback_name" id="feedback_name" type="text" maxlength="100"/><p class="disc"></p></tr>
		<tr><td>Телефон для связи</td><td><input name="feedback_tel" id="feedback_tel" type="text" maxlength="100"/><p class="disc"></p></tr>
		<tr><td>Электронная почта</td><td><input name="feedback_mail" id="feedback_mail" type="text" maxlength="64"/><p class="disc"></p></tr>
		<tr><td>Сообщение</td><td><textarea id="feedback_message" name="feedback_message" style="width:99%;height:150px;" maxlength="5000"></textarea><p class="disc"></p></td></tr>
	</table>
	<input name="mail" value="ok" type="hidden"/>
	<p style="color:#666;font-size:14px;">* Все поля обязательны для заполнения</p>
	<div class="center">
		<button type="button" class="sbutton" id="feedback_send">
			<div class="sbl"></div>
			<div class="sbc">Отправить</div>
			<div class="sbr"></div>
		</button>
	</div>
</form>
<?
}
?>