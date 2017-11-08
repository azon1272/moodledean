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



//загрузка библиотек верхнего уровня
require_once(dirname(realpath(__FILE__))."/../lib.php");


/** Класс по отображению тематического планирования
 */
class dof_im_plans_themeplan_view
{
    /**
     * @var dof_control
     */
    protected $dof;
    private $linktype;
    private $linkid;
    /** 
     * @var dof_modlib_widgets_ifield объект виджета для inline-редактирования названия темплана
     */
    protected $ifield;
        
    /** Конструктор класса
     * @constructor
     * @param dof_control $dof -  cодержит методы ядра деканата
     * @param $linktipe - тип ссылки
     * @param $linkid - id ссылки
     */
    function __construct(dof_control $dof, $linktype, $linkid)
    {
        $this->dof = $dof;
        $this->linktype = $linktype;
        $this->linkid = $linkid;
        // создаем объект виджета для inline-радактирования темы темплана
        $this->ifield = $this->dof->modlib('widgets')->ifield('storage', 'plans', 'name', 0, 'textarea', '');
    }

    /** Получить одну строку темплана
     * 
     * @return array
     * @param $plan object - объект из темплана
     * @param $i int - № строки
     * @param $edit bool - флаг редактирования
     * 0 - дата
     * 1 - счетчик($i)
     * 2 - имя темплана
     * 3 - раздел темплана
     * 4 - примечание
     * 5 - родит.запсись
     * 6 - флаг
     */
    public function get_one_themeplan_string($plan, $i, $conds, $edit=false)
    { 
        $mas = array();
        $masrec = array();
        if ( ! isset($plan) )
        {
            return false;
        }
        $themeplan = array();
        if ( $this->linktype != 'programmitems' )
        {// для непредметов выведем 
            if ( $this->linktype == 'plan' )
            {// для plan - предметокласс
                $linktypesd = 'cstreams';
            }else
            {// остальные такие же
                $linktypesd = $this->linktype;
            }
            if ( $dateobject = $this->dof->storage($linktypesd)->get($this->linkid) )
            {// высчитаем дату
                $themeplan[] = dof_userdate($dateobject->begindate + $plan->reldate,'%d-%m-%Y');
            }else
            {// пустая
                $themeplan[] = "";
            }
        }else
        {// пустая
            $themeplan[] = "";
        }
        $themeplan[] = $i;
        
        if ( $edit AND ($this->dof->im('plans')->is_access('editthemeplan:'.$this->linktype, $plan->linkid)
            OR $this->dof->im('plans')->is_access('editthemeplan:'.$this->linktype.'/my', $plan->linkid))
            AND ( ! $this->dof->storage('schevents')->get_records(array('planid'=>$plan->id,'status'=>array('replaced','completed'))) 
            AND $plan->status != 'fixed')
                    OR $this->dof->is_access('datamanage') OR $this->dof->im('plans')->get_cfg('can_edit_lesson') )
        {// тему планирования можно редактировать через ajax. Используем виджет ifield
            $themeplan[] = $this->ifield->get_html($plan->id, $plan->name);
        }else
        {
            $themeplan[] = $plan->name;
        }
        
        
        if ( ! $recthemeplan = $this->dof->storage('plansections')->get($plan->plansectionsid) )
        {// пустое
            $themeplan[] = '';
        }else 
        {
            $themeplan[] = $recthemeplan->name;
        }
        $themeplan[] = $plan->note;
        // результат - массив с id
        $mas = $this->dof->storage('planinh')->get_list_id('inhplanid', $plan->id);  
        // создеём запись plans-ов     
        $rez = '';
        if ( ! empty($mas) )
        {// перебираем массив с id из plans
            foreach ($mas as $vol)
            {
                if ( $rec = $this->dof->storage('plans')->get($vol) )
                {
                    if ( ! $rec->name)
                    {// проверка на имя, если нет, то показывать всё равно надо
                        $rec->name = '***';    
                    }
                    // создаем запись
                    $rez .= "<a href='".$this->dof->url_im('plans',"/themeplan/viewthemeplan.php?linktype={$rec->linktype}&linkid={$rec->linkid}",$conds)."'>"
                        .$rec->name."</a><br>";
                }
            }    
        }
        $themeplan[] = $rez;
        if ( $plan->linktype != $this->linktype )
        {// КТ не того типа - покажем ссылку на просмотр ее типа
            $link = '<a href='.$this->dof->url_im('plans',"/themeplan/viewthemeplan.php?linktype={$plan->linktype}&linkid={$plan->linkid}",$conds).'>
            <img src="'.$this->dof->url_im('plans', 
            '/icons/viewtoplevel.png').'" alt="'.$this->dof->modlib('ig')->igs('view').'
            " title="'.$this->dof->modlib('ig')->igs('view').'"></a>&nbsp;';
        }elseif ( $edit AND ($this->dof->im('plans')->is_access('editthemeplan:'.$this->linktype, $plan->linkid, null, $plan->linktype)
            OR $this->dof->im('plans')->is_access('editthemeplan:'.$this->linktype.'/my', $plan->linkid)) )
        {//покажем ссылку на страницу редактирования';
            $link = '';
            if ( ( ! $this->dof->storage('schevents')->get_records(array('planid'=>$plan->id,'status'=>array('replaced','completed'))) AND $plan->status != 'fixed')
                    OR $this->dof->is_access('datamanage') OR $this->dof->im('plans')->get_cfg('can_edit_lesson') )
            {// нельзя редактировать КТ если на нее есть отмеченные события
                $link .= '<a id="edit_plan_'.$plan->id.'" href='.$this->dof->url_im('plans','/edit.php?pointid='.$plan->id,$conds).'>
                <img src="'.$this->dof->url_im('plans', 
                '/icons/edit.png').'" alt="'.$this->dof->modlib('ig')->igs('edit').'
                " title="'.$this->dof->modlib('ig')->igs('edit').'"></a>&nbsp;';
            }
            if ( in_array($plan->status, array('draft','fixed','excluded')) AND $this->dof->workflow('plans')->is_access('changestatus') )
            {// подтвердить тему
                $link .= '<a id="activate_plan_'.$plan->id.'" href='.$this->dof->url_im('plans','/active.php?planid='.$plan->id,$conds).'>
                <img src="'.$this->dof->url_im('plans', 
                '/icons/state.png').'" alt="'.$this->dof->modlib('ig')->igs('confirm').'/'.$this->dof->modlib('ig')->igs('active').'
                " title="'.$this->dof->modlib('ig')->igs('confirm').'/'.$this->dof->modlib('ig')->igs('active').'"></a>&nbsp;';
            }
            if ( $plan->status == 'active' AND $this->dof->workflow('plans')->is_access('changestatus') )
            {// зафиксировать тему
                $link .= '<a id="fix_plan_'.$plan->id.'" href='.$this->dof->url_im('plans','/fix.php?planid='.$plan->id,$conds).'>
                <img src="'.$this->dof->url_im('plans', 
                '/icons/fix.png').'" alt="'.$this->dof->modlib('ig')->igs('fix').'
                " title="'.$this->dof->modlib('ig')->igs('fix').'"></a>&nbsp;';
            }
            // @todo исключение оценок пока убрано
            //if ( in_array($plan->status, array('active','fixed')) AND $plan->linktype == 'cstreams' 
            //     AND ! $this->dof->storage('schevents')->get_list_filter('planid',$plan->id,'status',array('plan','replaced','completed')) )
            //{// если на темплан нет событий - покажем ссылку на удаление
            //    $link .= '<a href='.$this->dof->url_im('plans','/exclude.php?planid='.$plan->id).'>
            //    <img src="'.$this->dof->url_im('plans', 
            //    '/icons/exclude.png').'" alt="'.$this->dof->modlib('ig')->igs('exclude').'
            //    " title="'.$this->dof->modlib('ig')->igs('exclude').'"></a>&nbsp;';
            //}
            if ( ! $this->dof->storage('schevents')->get_records(array('planid'=>$plan->id,'status'=>array('plan','replaced','completed','postponed'))) )
            {// если на темплан нет событий - покажем ссылку на удаление
                $link .= '<a id="delete_plan_'.$plan->id.'" href='.$this->dof->url_im('plans','/delete.php?planid='.$plan->id,$conds).'>
                <img src="'.$this->dof->url_im('plans', 
                '/icons/delete.png').'" alt="'.$this->dof->modlib('ig')->igs('delete').'
                " title="'.$this->dof->modlib('ig')->igs('delete').'"></a>&nbsp;';
            }
        }else
        {
            $link = '';
        }
        if ( isset($plan->status) AND ! empty($plan->status) )
        {
            $themeplan[] = $this->dof->workflow('plans')->get_name($plan->status);    
        }else 
        {
            $themeplan[] = '';            
        }
        $lesson_types = $this->dof->modlib('refbook')->get_lesson_types();
        if ( isset($lesson_types[$plan->type]) )
        {// есть запись - выведем
            $themeplan[] = $lesson_types[$plan->type];
        }else
        {
            $themeplan[] = '';
        }
        $themeplan[] = $link;
        return $themeplan;
    }

    /** Рисует таблицу отображения имен темплана
     * 
     * @return string - html-код страницы
     */
    public function get_table_plansections($conds,$edit=false)
    {// рисуем таблицу
        $table = new stdClass();
        $table->tablealign = "center";
        $table->align = array("left");
        // заголовок
        $table->head = array($this->dof->get_string('namethemeplan','plans'),
                             $this->dof->modlib('ig')->igs('actions'));
        // найдем элементы темразделов
        if ( $mas = $this->dof->storage('plansections')->get_theme_plan
                ($this->linktype, $this->linkid, array('active')) )
        {// если они есть
            foreach ($mas as $val)
            {// выведем имя каждого
                if ( $edit AND ($this->dof->im('plans')->is_access('editthemeplan:'.$this->linktype, $val->linkid, null, $val->linktype)
            OR $this->dof->im('plans')->is_access('editthemeplan:'.$this->linktype.'/my', $val->linkid)) )
                {//покажем ссылку на страницу редактирования';
                    $link = '<a href='.$this->dof->url_im('plans','/editsection.php?id='.$val->id,$conds).'>
                    <img src="'.$this->dof->url_im('plans', 
                    '/icons/edit.png').'" alt="'.$this->dof->modlib('ig')->igs('edit').'
                    " title="'.$this->dof->modlib('ig')->igs('edit').'"></a>&nbsp;';
                    if ( ! $this->dof->storage('plans')->get_records(array('plansectionsid'=>$val->id,'status'=>array('active', 'fixed', 'excluded', 'draft'))) )
                    {// если на темраздел нет активных темпланов - покажем ссылку на удаление
                        $link .= '<a href='.$this->dof->url_im('plans','/deletesection.php?sectionid='.$val->id,$conds).'>
                        <img src="'.$this->dof->url_im('plans', 
                        '/icons/delete.png').'" alt="'.$this->dof->modlib('ig')->igs('delete').'
                        " title="'.$this->dof->modlib('ig')->igs('delete').'"></a>&nbsp;';
                    }
                }else
                {
                    $link = '';
                }
                $table->data[] = array($val->name,$link);
            }
        }else 
        {// записей нет
            $table->data[] = array($this->dof->get_string('no_points_found','plans'),'');
        }
        //возвращаем таблицу
        return $this->dof->modlib('widgets')->print_table($table,true); 

    }
    
    /** Рисует таблицу отображения темплана
     * 
     * @return string - html-код страницы
     */
    public function get_table_themeplan($conds,$edit=false)
    {// находим записи
        $plans = $this->dof->storage('plans')->get_theme_plan
        ($this->linktype, $this->linkid,array('draft','active','fixed','excluded','checked','completed'));
        if ( ! $plans )
        {// если их нет, то и выводить нечего
            return '';
        }else 
        {   // счётчик
            $i = 0;
            // рисуем таблицу
            $table = new stdClass();
            $table->tablealign = "center";
            $table->cellpadding = 2;
            $table->cellspacing = 2;
            $table->align = array("center","center","center","center","center","center","center","center");
            $table->size  = array(null, null, '100%');
            $table->width = '100%';
            $table->wrap = array(true);
            // заголовок
            $table->head = array($this->dof->modlib('ig')->igs('date'),
                                 $this->dof->get_string('number','plans'),
                                 $this->dof->get_string('name_plan','plans'),
                                 $this->dof->get_string('name_themeplan','plans', '<br/>'),                             
                                 $this->dof->get_string('note','plans'),
                                 $this->dof->get_string('parenttheme','plans', '<br/>'),
                                 $this->dof->get_string('status','plans'),
                                 $this->dof->get_string('theme_type','plans'),
                                 $this->dof->get_string('actions','plans') );
            $table->data = array();
            foreach ( $plans as $plan)
            {// заносим даные в таблицу
                ++$i;
                // формируем строку для каждого
                $table->data[] = $this->get_one_themeplan_string($plan, $i,$conds, $edit);
            }
        }      
        //возвращаем таблицу
        return $this->dof->modlib('widgets')->print_table($table,true); 
    }
    
    /** Выводит пояснительную записку
     * 
     * @return string - html-код страницы
     */
    public function get_table_note($conds,$edit=false)
    {// запись из БД по linktype и linkid 
        if ( $this->linktype == 'plan' )
        {// переписываем linktype
            $linktype = 'cstreams';
        }else
        {// оставляем прежним
            $linktype = $this->linktype;
        }
        if ( $rec = $this->dof->storage($linktype)->get($this->linkid) )
        {
            if ( $this->linktype != 'ages' )
            {// заполняем таблицу
                $table = new stdClass();
                $table->align = array('left');
                //$table->data[] = array($this->dof->get_string('planatory','plans').$link);
                $table->data[] = array($rec->explanatory);
            }else
            {// если пусто, ничего не возвращаем
                return false;
            }   
        }       
        //возвращаем таблицу
        return $this->dof->modlib('widgets')->print_table($table,true); 
    }
    
    
    /** Выводит строку для производства действий над всеми элементами темплана
     * 
     * @return string - html-код страницы
     */
    public function get_table_all_actions($conds)
    {// запись из БД по linktype и linkid 
        $table = new stdClass();
        $table->align = array('left');
        $table->width = "100%";
        $links = $this->dof->get_string('actionsallthemeplan','plans').': ';
        if ( ($this->dof->im('plans')->is_access('editthemeplan:'.$this->linktype, $this->linkid, null, $this->linktype)
            OR $this->dof->im('plans')->is_access('editthemeplan:'.$this->linktype.'/my', $this->linkid)) )
        {//покажем ссылку на страницу редактирования';
            // если на темплан нет событий - покажем ссылку на удаление
                $links .= '<a href='.$this->dof->url_im('plans','/activeall.php?linktype='.$this->linktype.'&linkid='.$this->linkid,$conds).'>
                <img src="'.$this->dof->url_im('plans', 
                '/icons/state.png').'" alt="'.$this->dof->modlib('ig')->igs('confirm').'/'.$this->dof->modlib('ig')->igs('active').'
                " title="'.$this->dof->modlib('ig')->igs('confirm').'/'.$this->dof->modlib('ig')->igs('active').'"></a>&nbsp;';

                $links .= '<a href='.$this->dof->url_im('plans','/fixall.php?linktype='.$this->linktype.'&linkid='.$this->linkid,$conds).'>
                <img src="'.$this->dof->url_im('plans', 
                '/icons/fix.png').'" alt="'.$this->dof->modlib('ig')->igs('fix').'
                " title="'.$this->dof->modlib('ig')->igs('fix').'"></a>&nbsp;';

        }
        $table->data[] = array($links);   
        //возвращаем таблицу
        return $this->dof->modlib('widgets')->print_table($table,true); 
    }
    
}


?>