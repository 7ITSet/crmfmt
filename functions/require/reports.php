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
							  В этом разделе есть возможность получать отчетную информацию по сделанным продажам с картами ДС бизON, ранжируя её по периодам, точкам продаж и другим признакам.
							</div>
				</div>
					</div>
				
					<!-- end row -->
				
					<!-- end row -->
				
				</section>
				<!-- end widget grid -->