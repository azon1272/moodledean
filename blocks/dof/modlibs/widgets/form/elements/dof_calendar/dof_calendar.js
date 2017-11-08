/**
 *  Метод, который отрисовывает календарь(левый с, правый до)
 *  @param string name - имя элемента(календаря)
 *  @param string text_from - дата, с какого числа
 *  @param string text_to - дата, до какого числа
 * @author baranov
 */
function show_calendar(name,text_from,text_to,text_today)
{

    // начинает работу наш датапикер - календарь-левая сторона
	$( "#"+name+"_from" ).datepicker({
		showOtherMonths: true,
		selectOtherMonths: true,
		defaultDate: text_from , 
		beforeShowDay: function(d) {
	           var dat = $.datepicker.formatDate("d.mm.yy", d);
			   if ($.inArray(dat, dates)!=-1) return [true, "cls2"];
			   else return [true];
	           },
		onSelect: function( selectedDate ) {
		    // получаем объект
			var obj = $( this ).data( "datepicker" );
			var option = "minDate";
			var time_unix = new Date(obj.currentYear, obj.currentMonth, obj.currentDay,0,0,0);
			// устанавливаем дату в hidden поле
			$("#id_"+name+"_from").attr("value",Date.parse(time_unix)/1000);
		
			instance = $( this ).data( "datepicker" ),
			date = $.datepicker.parseDate(
					instance.settings.dateFormat ||
					$.datepicker._defaults.dateFormat,
					selectedDate, instance.settings );
			$( "#"+name+"_to" ).datepicker( "option", option, date );
			// меняем дату под календарем
			$("#"+name+"_data_from").html($(this).val() );
		}
	});
	
 	// начинает работу наш датапикер - календарь-правая сторона
	$( "#"+name+"_to" ).datepicker({
		showOtherMonths: true,
		selectOtherMonths: true,
		defaultDate: text_to,
		beforeShowDay: function(d) {
	           var dat = $.datepicker.formatDate("d.mm.yy", d);
			   if ($.inArray(dat, dates)!=-1) return [true, "cls2"];
			   else return [true];
	           },
		onSelect: function( selectedDate ) {
		    // получаем объект
			var obj = $( this ).data( "datepicker" );
			var option = "maxDate";
			var time_unix = new Date(obj.currentYear, obj.currentMonth, obj.currentDay,0,0,0);
			// устанавливаем дату в hidden поле
			$("#id_"+name+"_to").attr("value",Date.parse(time_unix)/1000);
			
			instance = $( this ).data( "datepicker" ),
			date = $.datepicker.parseDate(
					instance.settings.dateFormat ||
					$.datepicker._defaults.dateFormat,
					selectedDate, instance.settings );
			$( "#"+name+"_from" ).datepicker( "option", option, date );
			
			// меняем дату под календарем
			$("#"+name+"_data_to").html($(this).val() );
		}
	});
	
}        		 