var area=subarea=city='';
jQuery.fn.sug = function(o) {
	// параметры по умолчанию
	var o = $.extend({    
		// URL для поиска слов
		url:'/ajax/suggest_addr.php',
		// функция, которая срабатывает при закрытии окна с подсказками
		onClose:function(suggest) {  
			setTimeout(function(){
				// плавно закрывает окно
				suggest.hide(); 
			}, 0); 
		},
		// функция, возвращающая данные для отправки на сервер
		dataSend:function(input) {  
			return 'type='+type+'&w='+word+'&area='+area+'&subarea='+subarea+'&city='+city;
		},
		// функция, которая срабатывает при добавлении слова в input
		wordClick:function(input,link) { 
			input.val(link.attr('href')).focus().trigger('change');
			if (input.attr('suggest')=='area') area=link.attr('rel');
			if (input.attr('suggest')=='subarea') subarea=link.attr('rel');
			if (input.attr('suggest')=='city') city=link.attr('rel');
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
		input.after('<div class="suggest"></div>'); 
		// присваиваем его переменной
		var suggest = input.next();
		// выставляем для него ширину
		suggest.width(suggest.prev().width()+20); 
		// когда input не в фокусе
		input.blur(function(){ 
			// если подсказки не скрыты
			if (suggest.is(':visible'))  {  
				// скрываем подсказки
				onClose(suggest); 
			}
		})
		// при нажатии клавиши
		.keydown(function(e) {
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
					if (input.attr('suggest')=='area') area=suggest.children('a.suggest-selected').attr('rel');
					if (input.attr('suggest')=='subarea') subarea=suggest.children('a.suggest-selected').attr('rel');
					if (input.attr('suggest')=='city') city=suggest.children('a.suggest-selected').attr('rel');
					return false;
				}
				 // если нажата клавиша Enter или Esc
				if (e.keyCode == 13 || e.keyCode == 27) {
					// закрываем окно
					o.wordClick(input,suggest.children('a.suggest-selected'));
					onClose(suggest); 
					return false;
				}
			}
		})
		.keyup(function(e) {
	       	// если нажата одна из клавиш, выходим
			if (e.keyCode == 38 || e.keyCode == 40 || e.keyCode == 13 || e.keyCode == 27) return false; 
			if (input.attr('suggest')=='area'&&input.val()=='') area='';
			if (input.attr('suggest')=='subarea'&&input.val()=='') subarea='';
			if (input.attr('suggest')=='city'&&input.val()=='') city='';
			 // добавляем переменную со значением поля ввода
			word = input.val();
			type = input.attr('suggest');		
			// если переменная не пуста
			if (word) { 
				$.get(o.url,
					o.dataSend(input),
					// функция при завершении запроса
					function(data){
						// если есть список подходящих слов
						if (data.length > 0) { 
						// функция, срабатывающая при нажатии на слово
						suggest.html(data).show().children('a').on('mousedown click',function(){
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