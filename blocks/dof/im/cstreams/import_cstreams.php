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
 * Отображает форму добавления и редактирования. 
 * Если передан параметр id, 
 * то отображается редактирование, 
 * если не передан - добавление. 
 */

// Подключаем библиотеки
require_once('lib.php');
require_once('form.php');
require_once('process_import_cstreams.php');
//проверяем доступ
$DOF->im('cstreams')->require_access('import');
$DOF->modlib('nvg')->add_level($DOF->get_string('title', 'cstreams'), 
                     $DOF->url_im('cstreams','/list.php'),$addvars);
$DOF->modlib('nvg')->add_level($DOF->get_string('import_cstreams', 'cstreams'),
                     $DOF->url_im('cstreams','/import_cstreams.php'),$addvars);
// подключаем форму
$form = new dof_im_cstreams_import_form();
$error = '';
if ( $form->is_submitted() AND confirm_sesskey() AND $formdata = $form->get_data() )
{
    //обработчик загруженного файла
    //print_object($formdata);//die;
    //print_object($_POST);
    // теперь все проверки фыйлов делает форма
    //$um = $formdata->_upload_manager('userfile',false,false,null,false,0);
    //if ($um->preprocess_files())
    
    {//если файл без вирусов и др дряни
        $filename = $form->save_temp_file('userfile');//сохранили имя файла
        /*if ( $um->files['userfile']['type'] != 'text/csv' )
        {// послан не csv файл
            $error .= $DOF->get_string('error_type_files','cstreams').'<br>';
        }else*/
        {// делаем импорт данных
            $process = new dof_im_cstreams_import_process($DOF, $formdata->ageid);
            // сначало делаем проверку
            $check = $process->import_cstreams($filename);
            $offcheck = $process->import_cstreams($filename);
            unset($offcheck[0]);
            if ( empty($offcheck) AND isset($formdata->button['begin']) )
            {// если все хорошо, делаем сам импорт если он нужен
                $data = $process->import_cstreams($filename,true);
            }
        }
        
    }
}

//вывод на экран

//печать шапки страницы
//$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

if ( $error != '' )
{// если возникли ошибки с файлом
    $DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
    // печать формы
    $form->display();
    print '<div align="center" style=" color:red; "><b>'.$error.'</b></div>';
    $DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
}elseif ( isset($formdata) AND isset($formdata->button['check']) )
{// данные импорта проходили проверку - выведем сообщения на экран
    $DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
    // печать формы
    $form->display();
    unset($check[0]);
    if ( ! empty($check) )
    {// были обнаружены ошибки
        $process->print_error_check($check);
    }elseif ( isset($process) )
    {// ошибок нет
        print '<div align="center" style=" color:green; "><b>'.
          $DOF->get_string('check_success','cstreams').'</b></div>';
    }
    $DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
}elseif ( isset($formdata) AND isset($formdata->button['begin']) )
{// данные импорта загружались в БД - выплюнем файл пользователю
    if ( isset($data) )
    {// ошибок нет - файл с созданными потоками
        $process->get_file_csv($data);
    }elseif ( ! empty($check) )
    {// если возникли ошибки - пришлем файл с ошибками
        $process->get_file_csv($check);
    }
}else
{// ничего нет - отображаем страницу
    $DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
    // печать формы
    $form->display();
    print $DOF->get_string('file_formating_csv','cstreams').'.<br>';
    print $DOF->get_string('file_reg_format','cstreams').':<br>';
    print 'id '.$DOF->get_string('sm_item','cstreams').
          ' &rarr; id '.$DOF->get_string('sm_appointment','cstreams').
          ' &rarr; id '.$DOF->get_string('sm_department','cstreams').
          ' &rarr; '.$DOF->get_string('eduweeks','cstreams').
          ' &rarr; '.$DOF->get_string('hours','cstreams').
          ' &rarr; '.$DOF->get_string('hoursweek','cstreams').
          ' &rarr; '.$DOF->get_string('hoursweekdistance','cstreams').
          ' &rarr; id '.$DOF->get_string('sm_programmsbcs','cstreams').' &crarr;';
    print '<ul>'.
	"<li>&nbsp;&rarr;&nbsp;-&nbsp;".$DOF->get_string('separator_sumbol','cstreams').'</li>'.
	"<li>&nbsp;&crarr;&nbsp;-&nbsp".$DOF->get_string('new_str_simbol','cstreams').
	"</li></p></ul>";
    $DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
}


//печать подвала


?>