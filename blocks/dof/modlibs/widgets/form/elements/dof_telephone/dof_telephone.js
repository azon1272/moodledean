
function dof_telephone_init(element) 
{
	element.oninput = function() {
		var value = element.value;
		var caretplace = element.selectionStart;
		if ( value != "" ) {
			// Фильтрация символов
			value = value.replace(/[^ +\-0-9]/gim,'');
			if ( value.length > 1 ) {
				value = value[0] + value.substring(1).replace(/[^ \-0-9]/gim,'');
			}
			// Удаление множественных пробелов
			value = value.replace(/[^\S\r\n]{2,}/gim,' ');
			// Удаление множественных - 
			value = value.replace(/\-{2,}/gim,'-');
			
			if ( value[0] === '8') {
				value = '+7 ' + value.substring(1);
			}
			if ( value[0] != '+') {
				value = '+' + value;
			}
			if ( value[1] === ' ') {
				value = '+7' + value.substring(1);
			}
		}
		element.value = value;
		//element.setSelectionRange(caretplace, caretplace);
	}
}