
/**
 * При нажатии на ссылку/кнопу скрывает/показыват фрагмент текста
 *
 * @param divname - id блока, который будет раскрываться/скрываться
 * @param bitn - класс, в котором меняется значение +/-
 * 
 */
function dof_modlib_widgets_js_hide_show(divname,btn)
{
   $('.'+divname).slideToggle("fast");
   $('.'+btn).toggleClass('show');
   return false;
}

