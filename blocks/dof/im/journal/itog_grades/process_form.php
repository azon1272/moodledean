<?php
////////////////////////////////////////////////////////////////////////////
//                                                                        //
// NOTICE OF COPYRIGHT                                                    //
//                                                                        //
// Dean`s Office for Moodle                                               //
// Электронный деканат                                                    //
// <http://sourceforge.net/projects/freedeansoffice/>                     //
//                                                                        //
// Copyright (C) 2008-2999                                                //
// Ilia Smirnov (Илья Смирнов)                                            //
// Evgenij Tsygantsov (Евгений Цыганцов)                                  //
// Alex Djachenko (Алексей Дьяченко)  alex-pub@my-site.ru                 //
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

// Подключаем библиотеки
require_once('lib.php');

// создаем путь на возврат
$path = $DOF->url_im('journal','/group_journal/index.php?csid='.$cstreamid,$addvars);
if ( $form->is_cancelled() )
{//ввод данных отменен - возвращаем на страницу просмотра класса
    redirect($path);
}elseif ( $form->is_submitted() AND confirm_sesskey() AND $formdata = $form->get_data() )
{//даные переданы в текущей сессии - получаем
    //print_object($formdata);//die;    
    // создаем объект для сохранения в БД
    $report = false;
    $cstream_complite = false;
    $itog_grades = new dof_im_journal_order_itog_grades ($DOF, $formdata);
    
    if ( isset($formdata->auto) )
    {// @todo обработчик для автоматического сохранения ведомости- переработать
        $syncorderid = $DOF->sync('courseenrolment')->sync_cstream($cstreamid, false, false);
        if ( $syncorderid === true )
        {// вернулось true - значит по какой-то причине не можем закрыть итоговую ведомость
            $error .= '<br><b style=" color:red; ">'.$DOF->get_string('auto_itog_grades_error','journal').'</b><br>';;
        }elseif ( $syncorderid === false )
        {// вернулось false - ошибка при заполнении итоговой ведомости
            $error .= '<br><b style=" color:red; ">'.$DOF->get_string('auto_itog_grades_failure','journal').'</b><br>';
        }else
        {// вернулся id приказа
            $report = true;
            $orderid = $syncorderid;
        }
    }elseif ( isset($formdata->grade) )
    {// есть оценки, надо сохранить
    	
    	if ( ! $orderobj = $itog_grades->order_set_itog_grade() )
    	{// если при сохранении возникли ошибки - сообщим об этом
    		$error .=  '<br><b style=" color:red; ">'.$DOF->get_string('error_get_itog_grades','journal').'</b><br>';
    	}else
    	{// все удачно сохранилось
    		if ( ! empty($orderobj->data->itoggrades) ) 
    		{// и оценки менялись
		        if ( ! isset($formdata->confirm_save_grades) )
			    {// сохранение оценок неподтверждено
			    	notice($DOF->get_string('not_found_confirm','journal'));
			    }else
			    {// созраним приказ и покажем ведомость
		    		$report = true;
		    		$orderid = $itog_grades->save_order_itog_grade($orderobj);  
			    }  
    		}
    		// если оценки не менялись, то и фиг с ним - покажем снова форму  
    	}
        if ( isset($formdata->complete_cstream) )
        {// нам указали, что обучение надо завершить
        	if ( empty($orderobj->data->itoggrades) )
        	{// но оценки не ставили - просто завершим поток без гемороя
        		redirect($DOF->url_im('journal','/itog_grades/edit.php?id='.$orderobj->data->cstreamid.'&complete=1'.'&departmentid='.$addvars['departmentid']));
        	}
        	// запомним, что нам надо завершить поток
        	$cstream_complite = true;
        	
        }
    }else
    {// оценок нет
        if ( isset($formdata->complete_cstream) )
        {// но обучение надо завершить
           redirect($DOF->url_im('journal','/itog_grades/edit.php?id='.$cstreamid.'&complete=1'.'&departmentid='.$addvars['departmentid']));
        	
        }
    }
      
}

?>