<?PHP
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

// Подключаем библиотеки
require_once('lib.php');
require_once('form.php');
$contractid = required_param('contractid', PARAM_INT);

$contract = $DOF->storage('contracts')->get($contractid);
if ( ! $contract )
{// Договор не найден
    $DOF->print_error('notfound', '', $contractid, 'im', 'sel');
}

// Проверка прав доступа
$DOF->im('sel')->require_access('editcontract', $contractid);

// Добавление уровня навигации
$DOF->modlib('nvg')->add_level(
    $DOF->get_string('contractlist', 'sel'), 
    $DOF->url_im('sel','/contracts/list.php', $addvars)
);


// Сформировать дополнительные данные для формы
$somevars = $addvars;
$somevars['id'] = $contractid;
$customdata = new stdClass();
$customdata->dof = $DOF;
$customdata->contractid = $contractid;
$customdata->addvars = $addvars;
$customdata->persons = ['studentid' => $contract->studentid];
$customdata->returnurl = $DOF->url_im('sel', '/contracts/view.php', $somevars);
$customdata->freezepersons = [];
$customdata->personlabels = [
    'studentid' => $DOF->get_string('student', 'sel'),
    'clientid' => $DOF->get_string('specimen', 'sel')
];

// Формирование URL текущей страницы
$addvars['contractid'] = $contractid;
$pagelink = $DOF->url_im('sel','/contracts/edit_second.php', $addvars);
// Добавление уровня навигации
$DOF->modlib('nvg')->add_level(
    $DOF->get_string('editcontract', 'sel'), 
    $pagelink
);

// Добавление в форму Законного представителя
if ( ( $contract->studentid !== $contract->clientid && $contract->clientid !== null ) || 
     (string)$contract->clientid === "0" )
{// Добавление в форму законного представителя
    $customdata->persons['clientid'] = $contract->clientid;
}

// Блокировка форм персон
if ( ( ! empty($contract->clientid) ) && ! is_null($contract->clientid) )
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

// Формирование значений по-умолчанию для блока создания подписки на программу
$default = [];
// установим значения по умолчанию для подписки
if ( $DOF->storage('programmsbcs')->count_list(array('contractid'=>$contractid)) > 1 )
{// подписок много - просто выведем их списком
    $customdata->countsbc = true;
}else
{// подписка одна или вообще нет - будем создавать/редактировать
   $customdata->countsbc = false;
   if ( $programmsbc = $DOF->storage('programmsbcs')->get_record(array('contractid'=>$contractid)) )
   {// Подписка в единственном экземпляре
       $default['programmsbcid'] = $programmsbc->id;
       $default['programmsbc'] = 1;
       $default['prog_and_agroup'] = array($programmsbc->programmid, 
                                           $programmsbc->agenum, 
                                           $programmsbc->agroupid);
       $default['eduform'] = $programmsbc->eduform;
       $default['freeattendance'] = $programmsbc->freeattendance;
       if ( $history = $DOF->storage('learninghistory')->get_first_learning_data($programmsbc->id) )
       {// периода есть - учтем его
           $default['agestart'] = $history->ageid;
       }
       if ( isset($programmsbc->datestart) )
       {// дата есть - учтем ее
           $default['datestart'] = $programmsbc->datestart;
       }
   }
}

// Создание формы с редактированием персон по договору
$form = new sel_contract_form_two_page(
    $pagelink, 
    $customdata,
    'post',
    '',
    ['class' => 'sel_contract_form_two_page']
);
// Заполнение данных по-умолчанию для подписки на программу
$form->set_data($default);

$error = $form->process();
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

echo $error;
// Отображаем форму
$form->display();
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
?>