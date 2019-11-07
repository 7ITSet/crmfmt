<?
defined('_DSITE') or die('Access denied');
global $sql, $settings;
?>

<section id="widget-grid" class="">
  <?
  if (isset($_GET['success']))
    echo '<div class="row"><article class="col-lg-12">
			<div class="alert alert-success alert-block">
				<a class="close" data-dismiss="alert" href="#">x</a>
				<h4 class="alert-heading">Выполнено!</h4>
				Информация о клиенте успешно обновлена!
			</div>
		</article></div>';
  if (isset($_GET['error']))
    echo '<div class="row"><article class="col-lg-12">
			<div class="alert alert-danger alert-block">
				<a class="close" data-dismiss="alert" href="#">x</a>
				<h4 class="alert-heading">Произошла ошибка!</h4>
				Произошла ошибка при сохранении данных.
			</div>
		</article></div>';
  ?>
  <div class="row">
    <article class="col-lg-6">
      <div class="jarviswidget" id="wid-id-1" data-widget-colorbutton="false" data-widget-editbutton="false" data-widget-fullscreenbutton="false" data-widget-custombutton="false" data-widget-sortable="false" style="" role="widget">

        <header>
          <span class="widget-icon"> <i class="fa fa-edit"></i> </span>
          <h2>Страница настроек</h2>
          <span class="obligatory">* помечены поля, обязательные для заполнения.</span>
        </header>

        <div>
          <div class="widget-body">
            <form class="smart-form" method="post">
              <header>
                Настройки корзины
              </header>
              <fieldset>
                <div class="row">
                  <section class="col col-6">
                    <label class="label">Минимальная сумма корзины <span class="obligatory_elem">*</span></label>
                    <label class="input">
                      <input type="number" name="min_total_sum" value="<?= $settings->min_total_sum_cart ?>">
                    </label>
                  </section>
                </div>
              </fieldset>
              <footer>
                <button type="submit" class="btn btn-primary">
                  <i class="fa fa-save"></i>
                  Сохранить
                </button>
              </footer>
              <input type="hidden" name="action" value="settings_page_change" />
            </form>
          </div>
        </div>
      </div>
    </article>
  </div>
</section>