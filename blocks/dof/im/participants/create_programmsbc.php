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
 * Интерфейс управления участниками учебного процесса. Панель управления слушателями.
 *
 * @package    im
 * @subpackage participants
 * @author     Polikarpov Alexander <polikarpovst@gmail.com>
 * @copyright  2016
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Подключение библиотек
require_once('lib.php');
require_once('form.php');

// HTML-код старинцы
$html = '';

// Отображение сообщений на основе GET-параметров
$DOF->im('participants')->messages();

// Получение GET-параметров
$baseurl = $DOF->url_im('participants', '/students.php', $addvars);
$returnurl = optional_param('returnurl', $baseurl, PARAM_URL);

// Добавление уровня навигации плагина
$DOF->modlib('nvg')->add_level(
    $DOF->get_string('page_students_name', 'participants'),
    $returnurl
);

// Получение параметров, определяющих текущий этап создания подписки
$personid = optional_param('personid', null, PARAM_INT);
$contractid = optional_param('contractid', null, PARAM_INT);

// URL начала процесса создания подписки
$addvars['returnurl'] = $returnurl;
$starturl = $DOF->url_im('participants', '/create_programmsbc.php', $addvars);

// Добавление уровня навигации плагина
$DOF->modlib('nvg')->add_level(
    $DOF->get_string('page_students_create_programmsbc_name', 'participants'),
    $starturl
);

// Формирование текущего URL
if ( ! ( $personid === null ) )
{
    $addvars['personid'] = $personid;
}
if ( ! ( $contractid === null ) )
{
    $addvars['contractid'] = $contractid;
}
$currenturl = $DOF->url_im('participants', '/create_programmsbc.php', $addvars);

if ( (int)$contractid > 0 )
{// Шаг 4. Создание подписки
    
    $contract = $DOF->storage('contracts')->get($contractid);
    if ( empty($contract) )
    {// Договор не найден
        $DOF->messages->add(
            $DOF->get_string('page_students_create_programmsbc_error_contract_not_found', 'participants'),
            'error'
        );
    } else 
    {// Договор найден
        if ( ! $DOF->storage('contracts')->is_access('use', $contractid, null, $addvars['departmentid']) )
        {// Доступ к использованию данного договора запрещен
            $DOF->messages->add(
                $DOF->get_string('students_create_select_contract_error_contractfind_access_use', 'participants'),
                'error'
            );
        }
    }
    
    if ( ! $DOF->messages->errors_exists() )
    {
        // Проверка валидности договора на обучение
        if ( empty($contract->studentid) || empty($contract->clientid) )
        {// Необходимо дозаполнить договор
            
            // Добавление уровня навигации плагина
            $DOF->modlib('nvg')->add_level(
                $DOF->get_string('page_students_create_programmsbc_contractpersons_name', 'participants'),
                $currenturl
            );
            
            // Проверка прав на редактирование договора
            if ( ! $DOF->storage('contracts')->is_access('edit', $contractid, null, $addvars['departmentid']) )
            {// Доступ к редактированию данного договора запрещен
                $DOF->messages->add(
                    $DOF->get_string('students_create_select_contract_error_contractfind_access_edit', 'participants'),
                    'error'
                );
            } else 
            {// Отображение формы редактирования персон по договору
                
                // Сформировать дополнительные данные для формы
                $customdata = new stdClass();
                $customdata->dof = $DOF;
                $customdata->contractid = $contractid;
                $customdata->addvars = $addvars;
                $customdata->persons = ['studentid' => $contract->studentid];
                $customdata->returnurl = $currenturl;
                $customdata->freezepersons = [];
                $customdata->personlabels = [
                    'studentid' => $DOF->get_string('students_create_programmsbc_contractpersons_student', 'participants'),
                    'clientid' => $DOF->get_string('students_create_programmsbc_contractpersons_client', 'participants')
                ];
                
                // Добавление в форму Законного представителя
                if ( ( $contract->studentid !== $contract->clientid && $contract->clientid !== null ) ||
                     (string)$contract->clientid === "0" )
                {
                    $customdata->persons['clientid'] = $contract->clientid;
                }
                
                // Блокировка полей формы
                if ( ! empty($contract->clientid) )
                {// Представитель указан в договоре
                    if ( $DOF->storage('contracts')->is_personel($contract->clientid, $contractid, 'fdo') )
                    {// Персона имет административные права или указана в других договорах
                        // Запрет редактирования персоны
                        $customdata->freezepersons['clientid'] = [];
                    }
                }
                if ( ! empty($contract->studentid) )
                {// Указана персона по договору
                    if ( $DOF->storage('contracts')->is_personel($contract->studentid, $contractid, 'fdo') )
                    {// Персона имет административные права или указана в других договорах
                        // Запрет редактирования персоны
                        $customdata->freezepersons['studentid'] = [];
                    }
                }
                // Создание формы с редактированием персон по договору
                $form = new dof_im_participants_students_create_contract_persons_contract(
                    $currenturl,
                    $customdata,
                    'post',
                    '',
                    ['class' => 'dof_im_participants_students_create_contract_persons_contract']
                );
                
                // Обработчик формы
                $form->process();
                // Рендер формы
                $html .= $form->render();
            }
        } else 
        {// Договор валиден
            
            // Добавление уровня навигации плагина
            $DOF->modlib('nvg')->add_level(
                $DOF->get_string('page_students_create_programmsbc_create_programmsbc_name', 'participants'),
                $currenturl
            );
            
            // Сохранение подписки
            $customdata = new stdClass();
            $customdata->dof = $DOF;
            $customdata->addvars = $addvars;
            $customdata->returnurl = $returnurl;
            $customdata->contractid = $contractid;
            
            $form = new dof_im_participants_students_create(
                $currenturl,
                $customdata,
                'post',
                '',
                ['class' => 'dof_im_participants_students_create']
            );
            // Обработчик формы
            $form->process();
            // Рендер формы
            $html .= $form->render();
        }
    }
    
} elseif ( $contractid === 0 && (int)$personid > 0 )
{// Шаг 3. Создание договора
    if ( ! $DOF->plugin_exists('im', 'sel') )
    {// Интерфейс договоров недоступен
        redirect($starturl);
    }
    
    // Добавление уровня навигации плагина
    $DOF->modlib('nvg')->add_level(
        $DOF->get_string('page_students_create_programmsbc_create_contract_name', 'participants'),
        $currenturl
    );
    
    require_once($DOF->plugin_path('im', 'sel', '/contracts/form.php'));
    require_once($DOF->plugin_path('im', 'sel', '/cfg/contractcfg.php'));
    
    // Сформировать дополнительные данные для формы
    $customdata = new stdClass;
    $customdata->dof          = $DOF;
    $customdata->contractid   = $contractid;
    $customdata->addvars      = $addvars;
    $customdata->departmentid = $addvars['departmentid'];
    $customdata->returnurl    = $currenturl;
    $customdata->cancelurl    = $starturl;
    $customdata->idresultparam = 'contractid';
    if ( isset($im_contracts['createnumber']) AND $im_contracts['createnumber'] )
    {// Настройка ручного указания номера договора
        // @todo переписать с использованием плагина config
        $customdata->createnumber = true;
    }
    
    // Сформировать значения по-умолчанию для формы
    $default = [];
    $default['student'] = 'personid';
    $default['client'] = 'student';
    $default['nocurator'] = true;
    $default['department'] = $addvars['departmentid'];
    $default['st_person_id[id]'] = (int)$personid;
    $default['st_person_id[id_autocomplete]'] = (int)$personid;
    $default['st_person_id[st_person_id]'] = $DOF->storage('persons')->get_fullname($personid);
    
    // Форма выбора договора для создания подписки на программу
    $form = new im_sel_contract_save_form(
        $currenturl,
        $customdata,
        'post',
        '',
        ['class' => 'im_sel_contract_save_form']
    );
    
    // Добавление данных в форму
    $form->set_data($default);
    
    // Обработчик формы
    $form->process();
    // Рендер формы
    $html .= $form->render();
    
} elseif ( $contractid === 0 )
{// Шаг 2. Создание персоны
    require_once($DOF->plugin_path('im', 'persons', '/form.php'));
    
    // Добавление уровня навигации плагина
    $DOF->modlib('nvg')->add_level(
        $DOF->get_string('page_students_create_programmsbc_create_person_name', 'participants'),
        $currenturl
    );
    
    // Сформировать дополнительные данные для формы
    $customdata = new stdClass();
    $customdata->dof = $DOF;
    $customdata->addvars = $addvars;
    $customdata->returnurl = $currenturl;
    $customdata->persons = ['personid' => 0];
    $customdata->personlabels = [
        'personid' => $DOF->get_string(
            'page_students_create_programmsbc_create_person_header', 
            'participants')
    ];
    
    // Форма выбора договора для создания подписки на программу
    $form = new dof_im_persons_edit_form(
        $currenturl,
        $customdata,
        'post',
        '',
        ['class' => 'dof_im_persons_edit_form']
    );
    
    // Обработчик формы
    $form->process();
    // Рендер формы
    $html .= $form->render();
    
} else 
{// Шаг 1. Выбор договора для подписки
    
    // Добавление уровня навигации плагина
    $DOF->modlib('nvg')->add_level(
        $DOF->get_string('page_students_create_programmsbc_select_contract_name', 'participants'),
        $currenturl
    );

    // Сформировать дополнительные данные для формы
    $customdata = new stdClass;
    $customdata->dof = $DOF;
    $customdata->addvars = $addvars;
    $customdata->returnurl = $currenturl;
    $customdata->cidparam = 'contractid';
    $customdata->pidparam = 'personid';
    // Форма выбора договора для создания подписки на программу
    $form = new dof_im_participants_students_create_select_contract(
        $currenturl, 
        $customdata, 
        'post', 
        '', 
        ['class' => 'dof_im_participants_students_create_select_contract']
    );
    // Обработчик формы
    $form->process();
    // Рендер формы
    $html .= $form->render();
    
}

// Печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

print($html);

// Печать подвала страницы
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
?>