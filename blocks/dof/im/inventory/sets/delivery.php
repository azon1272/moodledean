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

require_once('lib.php');
require_once($DOF->plugin_path('im','inventory','/invorders/form.php'));

// подтверждение выдачи комплекта
$confirm    = optional_param('confirm', 0, PARAM_INT);

//добавление уровня навигации
$DOF->modlib('nvg')->add_level($DOF->get_string('delivery_set', 'inventory'), 
    $DOF->url_im('inventory','/sets/delivery.php',$addvars));

// данные для формы
$customdata = new stdClass();
$customdata->dof = $DOF;
$customdata->departmentid = $addvars['departmentid'];
$customdata->categoryid = $addvars['invcategoryid'];

// Форма выдачи оборудования
$path = $DOF->url_im('inventory','/sets/delivery.php?confirm='.$confirm,$addvars);
$form = new dof_im_inventory_order_set_any_delivery($path, $customdata);

//вывод на экран
//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);


// обработка формы выдачи
if ( ! $confirm AND $formdata = $form->get_data() )
{// данные отправлены в форму, но подтверждение еще не прошло
    confirm_sesskey();
    // нарисуем таблицу с подтверждением выдачи оборудования
    $DOF->im('inventory')->display_delivery_set_confirmation($formdata, $addvars['departmentid']);
    // дополнительные параметры для перехода по ссылкам
    $yesopts = array(
            'id'       => $formdata->categoryid,
            'confirm'  => 1,
            'setid'    => $formdata->setid,
            'userid'   => $formdata->search['id_autocomplete'],
            'notes'    => $formdata->notes,
            'sesskey'  => sesskey()
        );
    $noopts = array(
            'id'      => $formdata->categoryid,
            'confirm' => 0
        );
    
    $linkyes = $DOF->url_im('inventory','/sets/delivery.php', $addvars + $yesopts);
    $linkno  = $DOF->url_im('inventory','/sets/delivery.php', $addvars + $noopts);
    $message = '<div style="text-align:center;">'.$DOF->get_string('delivery_question', 'inventory').'</div>';
    // Спрашиваем пользователя, действительно ли он хочет выдать оборудование
    $DOF->modlib('widgets')->notice_yesno($message, $linkyes, $linkno);
}elseif ( $confirm )
{// Выдача комплекта подтверждена
    confirm_sesskey();
    // получаем все нужные данные из формы подтверждения
    $setid  = required_param('setid', PARAM_INT);
    $userid = required_param('userid', PARAM_INT);
    $notes  = optional_param('notes', '', PARAM_TEXT);
    
    
    if ( $DOF->storage('invsets')->set_delivery($setid, $userid, $notes))
    {// Составляем сообщение о том, что комплект выдан
        $message = $DOF->modlib('widgets')->success_message($DOF->get_string('give_set', 'inventory'));
    }else
    {// Не удалось выдать комплект
        $message = $DOF->modlib('widgets')->error_message($DOF->get_string('delivery_error', 'inventory'));
    }
    // @todo добавить возможность выбирать: выдать еще один комплект или перейти на просмотр
    // Переходим на страницу просмотра комплекта
    redirect($DOF->url_im('inventory','/sets/view.php', $addvars + array('id' => $setid)), $message, 0.5);
}else
{// данные не пришли - просто отображаем форму
    echo $DOF->im('inventory')->additional_nvg('/sets/delivery.php', $addvars);

    $form->display();
}

//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
?>