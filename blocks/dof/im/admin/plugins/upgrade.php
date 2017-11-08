<?php
require_once('lib.php');
//проверка прав доступа сделана в lib.php

$DOF->modlib('nvg')->print_header(NVG_MODE_PAGE);
//проверяем наличие необходимых параметров
$type = required_param('type', PARAM_ALPHA);
$code = required_param('code', PARAM_ALPHA);

if ( data_submitted() AND confirm_sesskey() )
{//кнопки нажаты - обрабатываем их нажатие
	if( isset($_POST['yes']) )
	{//нажата кнопка да
        $rez = $DOF->plugin_upgrade($type, $code);
        //$rez = false;
        if($rez)
        {//плагин обновлен - сообщаем об этом
            print $DOF->get_string('plugin_upgrade_true', 'admin', $code);
        }else
        {//плагин не обновлен - сообщаем об этом
            print $DOF->get_string('plugin_upgrade_false', 'admin', $code);
        }
		//покажем ссылку "назад"
		print '<p align="center"><a href="'.$DOF->modlib('nvg')->get_url()."?type={$type}\">"
			  .$DOF->get_string('backwards', 'nvg', null, 'modlib').'</a></p>';
		
	}else
	{//нажата кнопка нет - надо вернуться на предыдущую страницу
		redirect($DOF->modlib('nvg')->get_url()."?type={$type}", '', 0);
	}
}else
{//рисуем форму с запросом на выполнение действий
	//запомним идентификатор сессии для проверки целостности сеанса
	$sesskey = !empty($USER->id) ? $USER->sesskey : '';
	print '<form method="post">';//открыли форму
	//спросили что делать
	$plugin = $DOF->plugin_getrec($type, $code);
	print $DOF->get_string('upgrade', 'admin').'&nbsp;'.$DOF->get_string($type, 'admin').'&nbsp;'
	.$code.'&nbsp;('.$plugin->version.')&nbsp;'
	.$DOF->get_string('to_version', 'admin', $DOF->plugin($type, $code)->version()).'?&nbsp;&nbsp;';
	//выводим кнопки для ответа
	print '<input type="submit" name="yes" value="'.$DOF->get_string('yes', 'admin').'">&nbsp;&nbsp;';
	print '<input type="submit" name="no" value="'.$DOF->get_string('no', 'admin').'">';
	//запоминаем данные для работы 
	print '<input type="hidden" name="sesskey" value="'.$sesskey.'">';
	print '<input type="hidden" name="type" value="'.$type.'">';
	print '<input type="hidden" name="code" value="'.$code.'">';
	print '</form>';//закрыли форму
}
$DOF->modlib('nvg')->print_footer(NVG_MODE_PAGE);
?>