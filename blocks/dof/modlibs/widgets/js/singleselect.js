
/**
 * Поддержка единичного выпадающего списка Деканата
 */

var forms = document.getElementsByClassName('dof_single_select');
for ( var f = 0; f < forms.length; f++ )
{
    var selects = forms[f].getElementsByTagName("select");
    for (s = 0; s < selects.length; s++ )
    {
    	var form = forms[f];
    	selects[s].onchange = function(form) 
    	{
    		return function() { form.submit();}
    	}(form);
    }
}