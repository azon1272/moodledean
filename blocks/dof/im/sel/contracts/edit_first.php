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

// Подключение библиотек
require_once(dirname(realpath(__FILE__)).'/lib.php');
require_once(dirname(realpath(__FILE__)).'/../cfg/contractcfg.php');
require_once(dirname(realpath(__FILE__)).'/form.php');

// HTML-код старинцы
$html = '';

// Получение GET-параметров
$contractid = optional_param('contractid', 0, PARAM_INT);

// Добавление уровня навигации плагина
$DOF->modlib('nvg')->add_level(
    $DOF->get_string('contractlist', 'sel'),
    $DOF->url_im('sel', '/contracts/list.php', $addvars)
);

// Формирование текущего URL
if ( $contractid )
{
    $addvars['contractid'] = $contractid;
}
$currenturl = $DOF->url_im('sel', '/contracts/edit_first.php', $addvars);

// Получение договора
$contract = $DOF->storage('contracts')->get($contractid);
if ( $contractid && empty($contract) )
{// Договор не найден в системе
   $DOF->messages->add(
       $DOF->get_string('notfound','sel', $contractid), 
       'error'
   );
} else 
{
    // Проверка прав доступа
    if( $contractid )
    {// Редактирование договора
        
        // Добавление уровня навигации плагина
        $DOF->modlib('nvg')->add_level(
            $DOF->get_string('editcontract', 'sel'),
            $currenturl
        );
        
        $DOF->im('sel')->require_access('editcontract', $contractid);
    }else
    {// Создание договора
        
        // Добавление уровня навигации плагина
        $DOF->modlib('nvg')->add_level(
            $DOF->get_string('newcontract', 'sel'),
            $currenturl
        );
        
        $DOF->im('sel')->require_access('openaccount');
    }
    
    // Установка пользователя, создающего контракт
    if ( ! $seller = $DOF->storage('persons')->get_bu(null, true) )
    {// Персона не найдена
        $DOF->messages->add(
            $DOF->get_string('notfoundperson', 'sel'),
            'error'
        );
    }
}

if ( ! $DOF->messages->errors_exists() )
{// Ошибок не найдено

    // Сформировать дополнительные данные для формы
    $customdata = new stdClass();
    $customdata->dof          = $DOF;
    $customdata->contractid   = $contractid;
    $customdata->addvars      = $addvars;
    $customdata->returnurl = $DOF->url_im('sel', '/contracts/edit_second.php', $addvars);
    $customdata->cancelurl = $DOF->url_im('sel', '/contracts/list.php', $addvars + ['byseller' => 1]);
    if ( isset($im_contracts['createnumber']) AND $im_contracts['createnumber'] )
    {// Настройка ручного указания номера договора
        // @todo переписать с использованием плагина config
        $customdata->createnumber = true;
    }
    
    // Сформировать значения по-умолчанию для формы
    $default = new stdClass();
    $default->student = 'new';
    $default->client = 'student';
    $default->nocurator = true;
    $default->department = $addvars['departmentid'];
    
    // Переопределение значений поле формы
    if ( $contract )
    {// Заполнение полей из договора
        if ( $contract->studentid )
        {// Установка студента
            $default->student = 'personid';
            $default->studentid = (int)$contract->studentid;
        }
        if ( $contract->clientid )
        {// Установка клиента
            $default->client = 'personid';
            $default->clientid  = (int)$contract->clientid;
        }
        if ( $contract->studentid == $contract->clientid )
        {
            $default->client = 'student';
        }
        if ( $contract->curatorid )
        {// Установка куратора
            $default->curatorid = $contract->curatorid;
            $default->nocurator    = false;
        }
        $default->num = $contract->num;
        $default->date = $contract->date;
        $default->department = $contract->departmentid;
        $default->notes = $contract->notes;
        $default->metacontract = $contract->metacontractid;
    }
    
    // Форма сохранения договора
    $form = new im_sel_contract_save_form($currenturl, $customdata);

    // Добавление данных в форму
    $form->set_data($default);
    
    // Обработчик формы
    $form->process();
    
    // Отображение формы
    $html .= $form->render();
}

// Печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

print($html);

// Печать подвала страницы
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
?>