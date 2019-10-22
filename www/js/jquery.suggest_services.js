var replace='';
jQuery.fn.sug = function(o) {
	// параметры по умолчанию
	var o = $.extend({  
		// URL для поиска слов
		url:'/ajax/suggest_services.php',
		//поле для подстановки значения
		idValue:'m_orders_smeta_services_id[]',
		unitValue:'m_orders_smeta_services_unit[]',
		categoryValue:'m_orders_smeta_services_category[]',
		priceValue:'m_orders_smeta_services_price[]',
		// функция, которая срабатывает при закрытии окна с подсказками
		onClose:function(suggest) { 
			suggest.hide();
		},
		// функция, возвращающая данные для отправки на сервер
		dataSend:function(input) {  
			return 'w='+word;
		},
		// функция, которая срабатывает при добавлении слова в input
		wordClick:function(input,link){
			input.val(link.attr('href')).focus();
			input.attr("title",link.attr('href'));
			id=link.attr('data-id');
			unit=link.attr('data-unit');
			category=link.attr('data-category');
			price=link.attr('data-price');
			input.parents('div.row:first').find("input[name='"+o.idValue+"']").val(id);
			input.parents('div.row:first').find("input[name='"+o.categoryValue+"']").val(category);
			input.parents('div.row:first').find("input[name='"+o.unitValue+"']").val(unit);
			input.parents('div.row:first').find("input[name='"+o.unitValue+"']").attr("title",unit);
			input.parents('div.row:first').find("input[name='"+o.priceValue+"']").val(price);
			/* УБИРАЕМ БЛОКИРОВКУ НА АВТОИЗМЕНЕНИЕ ОБЪЁМА ПРИ ИЗМЕНЕНИИ ТИПА РАБОТ */
			input.parents('.multirow:first').find("input[name=\'m_orders_smeta_services_count[]\']").removeClass("manual-changed");
			input.parents('.multirow:first').find("input[name=\'m_orders_smeta_services_manual_changed[]\']").val(0);	
			input.trigger("change");
			var suggest = input.next();
			suggest.hide();
		}
	}, o);
	// каждое поле для ввода
	return $(this).each(function(){ 
		var onClose = o.onClose;
		// присваиваем переменной input
		var input = $(this); 
		// после него вставляем блок для подсказок
		if(!input.next('div.suggest').length)
			input.after('<div class="suggest"></div>');
		else
			input.next().html('');
		// присваиваем его переменной
		var suggest = input.next();
		// выставляем для него ширину
		suggest.width(suggest.prev().width()+47);
		// когда input не в фокусе
		input.blur(function(){ 
			// если подсказки не скрыты
			if (suggest.is(':visible')){ 
				// скрываем подсказки
				onClose(suggest); 
			}
		})
		// при нажатии клавиши
		.keydown(function(e) {
			//ширина выпадающего списка для динамических полей
			suggest.width(suggest.prev().width()+47);
			//если поле подсказок показано
			if ($('.suggest:visible').length){
				 // если эта клавиша вверх или вниз
				if (e.keyCode == 38 || e.keyCode == 40) {
					// находим выделенный пункт
					var tag = suggest.children('a.suggest-selected'),
					 // и первый в списке
					new_tag = suggest.children('a:first');
					// если выделение существует
					if (tag.length){
						// нажата клавиша вверх
						if (e.keyCode == 38){ 
							// и не выделен первый пункт
							if (suggest.children('a:first').attr('class')!='suggest-selected') 
								// выделяем предыдущий
								new_tag = tag.prev('a');  
							// если выделен первый пункт выделяем последний
							else
								new_tag = suggest.children('a:last');
						//если нажата стрелка вниз
						} else
							//если пункт не последний  выделяем следующий
							if (suggest.children('a:last').attr('class')!='suggest-selected') 
								new_tag = tag.next('a');
							else
								// выделяем первый
								new_tag = suggest.children('a:first');
						// снимаем выделение со старого пункта
						tag.removeClass('suggest-selected');
					}
					// добавляем класс выделения
					new_tag.addClass('suggest-selected');
					// заменяем слово в поле ввода
					input.val(new_tag.attr('href'));
					id=suggest.children('a.suggest-selected').attr('data-id');
					unit=suggest.children('a.suggest-selected').attr('data-unit');
					category=suggest.children('a.suggest-selected').attr('data-category');
					price=suggest.children('a.suggest-selected').attr('data-price');
					input.parents('div.row:first').find("input[name="+o.idValue+"]").val(id);
					input.parents('div.row:first').find("input[name="+o.categoryValue+"]").val(category);
					input.parents('div.row:first').find("input[name="+o.unitValue+"]").val(unit);
					input.parents('div.row:first').find("input[name="+o.priceValue+"]").val(price);
					return false;
				}
				 // если нажата клавиша Enter или Esc
				if (e.keyCode == 13 || e.keyCode == 27) {
					// закрываем окно
					onClose(suggest);
					return false;
				}
			}
		})
		.keyup(function(e) {
	       	// если нажата одна из клавиш, выходим
			if (e.keyCode == 38 || e.keyCode == 40 || e.keyCode == 13 || e.keyCode == 27) return false; 
			if (input.val()=='') replace='';
			 // добавляем переменную со значением поля ввода
			word = input.val();		
			// если переменная не пуста
			if (word) {
				$.get(o.url,
					o.dataSend(input),
					// функция при завершении запроса
					function(data){
						// если есть список подходящих слов
						if (data.length > 0) {
							// функция, срабатывающая при нажатии на слово
							suggest.html(data).show().css('display','block!important').children('a').on('mousedown click',function(k){
								// пользовательская функция, объявленная выше
								o.wordClick(input,$(this));
								return false;
							});
					} else {  
						onClose(suggest);
					}
				});
			// если переменная пуста закрываем окно
			} else { 
	    		onClose(suggest); 
			}		
		});
	});
}