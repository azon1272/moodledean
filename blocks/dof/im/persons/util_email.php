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

// Подключаем библиотеки
require_once('lib.php');
require_once('form.php');

// Доступно только менеджерам по продажам или кому можно видеть все
$DOF->require_access('datamanage');

$DOF->modlib('nvg')->add_level($DOF->get_string('listpersons', 'persons'),
      $DOF->url_im('persons','/list.php'),$addvars);
$DOF->modlib('nvg')->add_level($DOF->get_string('createpersonemails', 'persons'), 
      $DOF->url_im('persons','/util_email.php'),$addvars);
$form = new dof_im_persons_email_edit_form();

$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
$form->display();
if ($formdata = $form->get_data() AND ! empty($formdata->emails) )
{
    $emails = explode(',',$formdata->emails);
    // Рисуем таблицу
	$table = new stdClass();
	$table->data = array();
    foreach ( $emails as $email )
    {
        $email = trim($email);
        $contractsid = '';
        $eagreementsid = '';
        if (!$person = $DOF->storage('persons')->get_record(array('email' => $email)) )
        {
            
        	// Пробуем найти пользователя Moodle
        	if ($objmdluser = $DOF->modlib('ama')->user(false)->get_list(array('email'=>$email)))
        	{
        	    if ( count($objmdluser) == 1 )
        	    {
            	    $objmdluser = current($objmdluser);
            		// Регистрируем пользователя, как персону
            		if (!$personid = $DOF->storage('persons')->reg_moodleuser($objmdluser))
            		{
            			$personid = "Registred user isn't founded";
            		}
        	    }else
        	    {
        	        $personid = 'not unic';
        	    }
        	}else
        	{
        		$personid = "Account is not registered";
        	}
        }else
        {
            
            $personid = $person->id;
            
            if ( $contracts = $DOF->storage('contracts')->get_records(array
               ('studentid'=>$personid,'status'=>array('new','clientsign','wesign','studentreg','work','frozen'))) )
            {
                $contracts = array_keys($contracts);
                $contractsid = implode(',',$contracts);
            }
            if ( $eagreements = $DOF->storage('eagreements')->get_records(array('personid'=>$personid,'status'=>'active')) )
            {
                $eagreements = array_keys($eagreements);
                $eagreementsid = implode(',',$eagreements);
            }
            if ( $person->status == 'deleted' )
            {
                $personid .= ' Deleted';
            }
        }
        
		$table->data[] = array($email,$personid,$contractsid,$eagreementsid);
    }
    $table->head = array('email','personid','contractid','eagreementid');
	$table->tablealign = "center";
	$table->align = array ("left","left");
	$table->wrap = array ("","");
	$table->cellpadding = 5;
	$table->cellspacing = 0;
	$table->width = '600';
	$table->size = array('200px','400px');
	// $table->head = array('', '');
	$DOF->modlib('widgets')->print_table($table);
}

$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
?>