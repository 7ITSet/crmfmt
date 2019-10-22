<?
defined ('_DSITE') or die ('Access denied');
global $user,$sql,$terminal;
?>
<script>
$(document).ready(function(){
    var otable = $('#datatable_sales').DataTable({
		 "order": [[5,"desc"]],
		 "pageLength": 5
    });
    $("#datatable_sales thead th input[type=text]").on( 'keyup change', function () {
        otable
            .column( $(this).parent().index()+':visible' )
            .search( this.value )
            .draw();       
    });
});
</script>
<!-- widget grid -->
				<section id="widget-grid" class="">
				
					<!-- row -->
					<div class="row">
				<div class="col-sm-12">
						<div class="alert alert-warning alert-block">
							  <a class="close" data-dismiss="alert" href="#">×</a>
							  <h4 class="alert-heading">Функционал находится в разработке!</h4>
							  При помощи рассылок можно будет отправлять пользователям (картхолдерам) ДС бизON сообщения рекламного характера: новости, акции, скидки, розыгрыши и т. д, ранжируя их по нескольким признакам (пол, возраст, район проживания, социальный статус, сфера деятельности, увлечения, и т. д.). Функционал раздела позволит отправлять сообщения ползователям, проживающим в выделенной области на карте города.
							</div>
				</div>
					</div>
				
					<!-- end row -->
				
					<!-- end row -->
				
				</section>
				<!-- end widget grid -->