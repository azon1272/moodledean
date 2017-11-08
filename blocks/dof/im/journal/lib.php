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

// Загрузка библиотек верхнего уровня
require_once(dirname(realpath(__FILE__))."/../lib.php");

// Добавление таблицы стилей
$DOF->modlib('nvg')->add_css('im', 'journal', '/styles.css');

$DOF->modlib('nvg')->add_level($DOF->get_string('title', 'journal'), $DOF->url_im('journal', '/index.php'), $addvars);

class dof_im_journal_view_person_info
{

    /**
     * @var dof_control
     */
    protected $dof;
    private $personid;

    public function __construct($dof, $personid)
    {
        $this->dof = $dof;
        $this->personid = $personid;
    }

    /**
     * Получить информацию о периодов ученика
     * 
     * @return array - возвращает запись периодов
     */
    public function get_info_student_age()
    {// выберем все id контрактов
        if ( !$contracts = $this->get_student_contracts() )
        {
            return array();
        }
        //опишем массивы
        $mascontr = array();
        $masprogr = array();
        $masageid = array();
        $masage = array();

        foreach ( $contracts as $val )
        {// массив id контрактов
            $mascontr[] = $val->id;
        }

        foreach ( $mascontr as $id )
        {// для каждого id контракта найдем подписку
            $masprogr = $this->dof->storage('programmsbcs')->get_programmsbcs_by_contractid_ids($id);
            foreach ( $masprogr as $val )
            {// для каждой подписки ещем периоды  
                $programmsbcid = $val;
                if ( $masage = $this->dof->storage('learninghistory')->get_subscribe_ages($programmsbcid) )
                {// создаём запись периодов
                    foreach ( $masage as $val )
                    {// и записываем в массив
                        $masageid[] = $val;
                    }
                }
            }
        }

        return $masageid;
    }

    /**
     * Получить информацию об обучении
     * 
     * @return string - html-код списка
     */
    public function get_learning_info()
    {
        $result = '';
        if ( !$contracts = $this->get_student_contracts() )
        {// нет контрактов - нет информации для вывода
            return '';
        }

        if ( !$learninghistory = $this->get_info_student_age($this->personid) )
        {// ученик пока еще нигде не учился
            return '';
        }

        $result .= '<ul>';
        foreach ( $learninghistory as $record )
        {// выводим каждую итоговую ведомость как ссылку
            $result .= $this->get_finalgrades_link($record);
        }
        $result .= '</ul>';
        // выделим  в блок, если есть ЧТО выделять
        if ( $result )
        {
            $result = $this->dof->modlib('widgets')->print_box_start() .
                    $this->dof->get_string('finalgrades', 'journal') .
                    $result .
                    $this->dof->modlib('widgets')->print_box_end(true);
        }
        return $result;
    }

    /**
     * Получить список всех контрактов ученика
     * 
     * @return array|false 
     */
    protected function get_student_contracts()
    {
        return $this->dof->storage('contracts')->get_list_by_student_age($this->personid);
    }

    /** Получить ссылку на просмотр информации об обучении за указанный период
     * 
     * @return string
     * @param object $learninghistory объект из таблицы leraninghistory
     */
    protected function get_finalgrades_link($learninghistory)
    {
        $depid = optional_param('departmentid', 0, PARAM_INT);
        $addvars = array();
        $addvars['departmentid'] = $depid;
        $programmname = '';
        if ( $programmsbc = $this->dof->storage('programmsbcs')->get($learninghistory->programmsbcid) )
        {
            $programmname = ' (' . $this->dof->storage('programms')->
                            get_field($programmsbc->programmid, 'name') . ')';
        }
        return '<li><a href="' . $this->dof->url_im('recordbook', '/finalgrades.php?programmsbcid=' .
                  $learninghistory->programmsbcid . '&ageid=' . $learninghistory->ageid, $addvars) . '">'
                . $this->dof->storage('ages')->get_field($learninghistory->ageid, 'name') . $programmname . '</a></li>';
    }
}

function dof_im_journal_get_date($time)
{
    $date = dof_usergetdate($time);
    return mktime(12, 0, 0, $date['mon'], $date['mday'], $date['year']);
}
?>