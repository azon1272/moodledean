<?php
////////////////////////////////////////////////////////////////////////////
//                                                                        //
// NOTICE OF COPYRIGHT                                                    //
//                                                                        //
// Dean`s Office for Moodle                                               //
// Электронный деканат                                                    //
// <http://deansoffice.ru/>                                               //
//                                                                        //
// Copyright (C) 2008-2999  Alex Djachenko (Алексей Дьяченко)             //
// alex-pub@my-site.ru                                                    //
// Copyright (C) 2008-2999  Evgenij Cigancov (Евгений Цыганцов)           //
// Copyright (C) 2008-2999  Ilia Smirnov (Илья Смирнов)                   //
// Copyright (C) 2008-2999  Mariya Rojayskaya (Мария Рожайская)           //
//                                                                        //
// This program is free software: you can redistribute it and/or modify   //
// it under the terms of the GNU General Public License as published by   //
// the Free Software Foundation, either version 3 of the Licensen.        //
//                                                                        //
// This program is distributed in the hope that it will be useful,        //
// but WITHOUT ANY WARRANTY; without even the implied warranty of         //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the          //
// GNU General Public License for more details.                           //
//                                                                        //
// You should have received a copy of the GNU General Public License      //
// along with this program.  If not, see <http://www.gnu.org/licenses/>.  //
//                                                                        //
////////////////////////////////////////////////////////////////////////////

/**
 * Интерфейс календаря. Страница обработки действий над событиями дня
 * 
 * @package    im
 * @subpackage schdays
 * @author     Polikarpov Alexander <polikarpovst@gmail.com>
 * @copyright  2016
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Подключаем библиотеки
require_once('lib.php');

// ПОЛУЧЕНИЕ GET-параметров и валидация
// ID дня
$dayid = optional_param('id', NULL, PARAM_INT);
if ( empty($dayid) )
{// ID дня не указан
    // Добавить сообщение
    $DOF->messages->add($DOF->get_string('process_events_error_dayid_not_set', 'schdays'), 'error');
} else
{// ID получен
	// Получение дня
	if ( ! $day = $DOF->storage('schdays')->get($dayid) )
	{// День не найден
		// Добавить сообщение
		$DOF->messages->add($DOF->get_string('process_events_error_dayid_not_found', 'schdays'), 'error');
	}
}
// Тип действия
$type =  optional_param('type', NULL, PARAM_TEXT);
if ( empty($type) )
{// Действие не указано
	// Добавить сообщение
	$DOF->messages->add($DOF->get_string('process_events_error_action_not_set', 'schdays'), 'error');
}
// Подтверждение действия
$confirm = optional_param('confirm', FALSE, PARAM_BOOL);



// Запрос доступа
$access = $DOF->im('schedule')->is_access('create_schedule');
if ( empty($type) )
{// Доступ закрыт
	// Добавить сообщение
	$DOF->messages->add($DOF->get_string('process_events_error_assess_denied', 'schdays'), 'error');
}



// Добавление уровня навигации
$usertimezone = $DOF->storage('persons')->get_usertimezone_as_number();
$addvars['id'] = $dayid;
$DOF->modlib('nvg')->add_level(
		dof_userdate($day->date, '%d.%m.%Y', $usertimezone), 
		$DOF->url_im('schdays','/view.php', $addvars)
);

$html = '';

if ( ! $DOF->messages->errors_exists() )
{// Ошибок нет
	
	if ( $confirm )
	{// Подтверждение действия
		// Создание обработчика действий
		$manager = new dof_im_schdays_schedule_manager($DOF,$addvars);
		
		// Выполнение действий
		switch ( $type )
		{
			// Создание расписания
			case 'create' :
				if ( $day->status != 'plan' )
				{// Статус дня не валиден
					// Добавить сообщение
					$DOF->messages->add($DOF->get_string('process_events_error_create_unvalid_status', 'schdays'), 'error');
					
					// Кнопка назад
					$html .= $DOF->modlib('widgets')->button(
							$DOF->get_string('process_events_return_to_dayview', 'schdays'),
							$DOF->url_im('schdays','/view.php', $addvars)
					);
				} else
				{// Создание расписания на день
					$html .= $manager->create_day($day);
				}
				break;
			// Удаление расписания
			case 'delete' :
				if ( $day->status != 'active' AND $day->status != 'completed' AND $day->status != 'draft' )
				{// Статус дня не валиден
					// Добавить сообщение
					$DOF->messages->add($DOF->get_string('process_events_error_delete_unvalid_status', 'schdays'), 'error');
					
					// Кнопка назад
					$html .= $DOF->modlib('widgets')->button(
							$DOF->get_string('process_events_return_to_dayview', 'schdays'),
							$DOF->url_im('schdays','/view.php', $addvars)
					);
				} else
				{// Удаление расписания на день
					$html .= $manager->delete_day($day);
				}
				break;
			// Обновление расписания
			case 'update' :
				if ( $day->status != 'active' AND $day->status != 'completed' AND $day->status != 'draft' )
				{// Статус дня не валиден
					// Добавить сообщение
					$DOF->messages->add($DOF->get_string('process_events_error_update_unvalid_status', 'schdays'), 'error');
					
					// Кнопка назад
					$html .= $DOF->modlib('widgets')->button(
							$DOF->get_string('process_events_return_to_dayview', 'schdays'),
							$DOF->url_im('schdays','/view.php', $addvars)
					);
				} else
				{// Обновление расписания
					$html .= $manager->update_day($day);
				}
				break;
			// Восстановление расписания
			case 'restore' : 
			    if ( $day->status != 'plan' )
			    {// Статус дня не валиден
    			    // Добавить сообщение
    			    $DOF->messages->add($DOF->get_string('process_events_error_restore_unvalid_status', 'schdays'), 'error');
    			    
    			    // Кнопка назад
    			    $html .= $DOF->modlib('widgets')->button(
    			        $DOF->get_string('process_events_return_to_dayview', 'schdays'),
    			        $DOF->url_im('schdays','/view.php', $addvars)
    			    );
			    } else
			    {// Восстановление расписания
			        $html .= $manager->restore_events_day($day);
			    }
			    break;
			// Задача не определена
			default :
				// Добавить сообщение
				$DOF->messages->add($DOF->get_string('process_events_confirmation_error_action_not_set', 'schdays'), 'error');
				break;
		}
	} else 
	{// Отображение подтверждения действий
		// Ссылка на возврат
		$linkno = $DOF->url_im('schdays','/view.php', $addvars);
		$addvars['confirm'] = 1;
		$addvars['type'] = $type;
		// Ссылка на подтверждение
		$linkyes = $DOF->url_im('schdays', '/process_events.php?', $addvars);
		switch ( $type )
		{
			// Создание расписания
			case 'create' :
				$submitstring = $DOF->get_string('process_events_confirmation_create_events', 'schdays');
				$html .= $DOF->modlib('widgets')->notice_yesno($submitstring, $linkyes, $linkno, NULL, NULL, 'get', 'get', TRUE);
				break;
			// Удаление расписания
			case 'delete' :
				$submitstring = $DOF->get_string('process_events_confirmation_delete_events', 'schdays');
				$html .= $DOF->modlib('widgets')->notice_yesno($submitstring, $linkyes, $linkno, NULL, NULL, 'get', 'get', TRUE);
				break;
			// Обновление расписания
			case 'update' :
				$submitstring = $DOF->get_string('process_events_confirmation_update_events', 'schdays');
				$html .= $DOF->modlib('widgets')->notice_yesno($submitstring, $linkyes, $linkno, NULL, NULL, 'get', 'get', TRUE);
				break;
			case 'restore' :
			    $submitstring = $DOF->get_string('process_events_confirmation_restore_events', 'schdays');
			    $html .= $DOF->modlib('widgets')->notice_yesno($submitstring, $linkyes, $linkno, NULL, NULL, 'get', 'get', TRUE);
			    break;
			// Задача не определена
			default : 
				// Добавить сообщение
				$DOF->messages->add($DOF->get_string('process_events_confirmation_error_action_not_set', 'schdays'), 'error');
				break;
		}
	}
}

// Шапка страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
print($html);
// Подвал страницы
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>