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
 * Главная библиотека плагина
 */ 

// Загрузка библиотек верхнего уровня
require_once(dirname(realpath(__FILE__))."/../lib.php");

// Добавим в массив GET параметров период
$addvars['ageid'] = required_param('ageid', PARAM_INT);
// Навигация
$agename = $DOF->storage('ages')->get_field($addvars['ageid'],'name');
$DOF->modlib('nvg')->add_level($DOF->get_string('title','schdays',$agename), $DOF->url_im('schdays','/calendar.php', $addvars));

/** Класс для вывода информации о периодах
 */
class dof_im_schdays_display
{
    protected $dof;
    private $data;
    private $addvars;
    private $departmentid; // подразделение
    
    function __construct($dof, $addvars)
    {
        $this->dof = $dof;
        $this->addvars = $addvars;
        $this->departmentid = $addvars['departmentid'];
    }
    
    /** Возвращает код im'а, в котором хранятся отслеживаемые объекты
     * @return string
     * @access private
     */
    private function get_im()
    {
        return 'schdays';
    }

    /** Распечатать вертикальную таблицу для удобного отображения информации по элементу
     * @todo не использует print_table, т.к выводит вертикальную таблицу
     * @param int $id - id периода из таблицы ages
     * @return string
     */
    public function get_table_one($id)
    {
        $table = new stdClass();
        if ( ! $schdays = $this->dof->storage('schdays')->get($id))
        {// не нашли шаблон - плохо
            return '';
        }
        
        // @todo переписать это извращение
        // получаем заголовки таблицы
        $descriptions = $this->get_fields_description('view');
        $data = $this->get_string_full($schdays);
        foreach ( $data as $elm )
        {
            $table->data[] = array('<b>'.current(each($descriptions)).'</b>', $elm);
        }
        return $this->dof->modlib('widgets')->print_table($table, true);
    }
    
    /** 
     * Получает строку таблицы для отображения списка периодов
     * 
     * @param object $obj - объект периода из таблицы ages
     * @return array
     */
    private function get_string_full($obj)
    {
        $add = array();
        $add['departmentid'] = $this->departmentid;
        $add['ageid'] = $this->addvars['ageid'];
        $outadd = array();
        $outadd['departmentid'] = $this->departmentid;
        $string = array();
        $string[] = dof_userdate($obj->date,"%d-%m-%Y");
        $string[] = $this->dof->storage('ages')->get_field($obj->ageid, 'name');
        $string[] = $this->dof->storage('departments')->get_field($obj->departmentid, 'name').' <br>['.
                    $this->dof->storage('departments')->get_field($obj->departmentid, 'code').']';
        $string[] = $obj->daynum;
        $string[] = $obj->dayvar;
        $string[] = $this->dof->get_string($obj->type, $this->get_im());
        $string[] = $this->dof->workflow('schdays')->get_name($obj->status);
        // добавляем ссылку
        $link = '';
        if ( $this->dof->storage('schdays')->is_access('edit', $obj->id) )
        {//покажем ссылку на страницу редактирования
            $link .= $this->dof->modlib('ig')->icon(
                'edit',
                $this->dof->url_im($this->get_im(), '/edit.php?id='.$obj->id, $add),
                ['width'=>'20px']
            );
        }
        if ( $obj->status == 'plan' )
        {
            $link .= $this->dof->modlib('ig')->icon_plugin('create_events','im',$this->get_im(),
                    $this->dof->url_im($this->get_im(),'/process_events.php?id='.$obj->id.'&type=create',$add),
                    array('title'=>$this->dof->get_string('create_events', $this->get_im()),
                          'width'=>'20px'));
        }
        if ( $obj->status == 'active' OR $obj->status == 'completed' OR $obj->status == 'draft' )
        {
            $link .= $this->dof->modlib('ig')->icon_plugin('update_events','im',$this->get_im(),
                    $this->dof->url_im($this->get_im(),'/process_events.php?id='.$obj->id.'&type=update',$add),
                    array('title'=>$this->dof->get_string('update_events', $this->get_im()),
                          'width'=>'20px'));
            $link .= $this->dof->modlib('ig')->icon_plugin('delete_events','im',$this->get_im(),
                    $this->dof->url_im($this->get_im(),'/process_events.php?id='.$obj->id.'&type=delete',$add),
                    array('title'=>$this->dof->get_string('delete_events', $this->get_im()),
                          'width'=>'20px'));
        }
        // Проверка наличия событий для восстановления
        $estatuses = $this->dof->workflow('schevents')->get_meta_list('junk');
        $isexists = $this->dof->storage('schevents')->get_records(['dayid' => $obj->id, 'status' => array_keys($estatuses)]);
        if ( $obj->status == 'plan' && ! empty($isexists) )
        {
            $link .= $this->dof->modlib('ig')->icon_plugin(
                'restore_events', 
                'im', 
                $this->get_im(),
                $this->dof->url_im($this->get_im(), '/process_events.php?id='.$obj->id.'&type=restore', $add),
                ['title' => $this->dof->get_string('restore_events', $this->get_im()), 'width'=>'20px']
            );
        }
        array_unshift($string, $link);
        return $string;
    }
    
    /** Распечатать таблицу для отображения периодов
     * @param int $conds - параметры поиска
     * @param int $limitfrom  - $limitfrom - начиная с какой записи просматривается фрагмент списка записей
     * @param int $limitnum - сколько записей нужно извлечь из базы
     * @return string
     */
    public function get_table_all($conds,$limitfrom,$limitnum)
    {
        $sort = array();
        $sort[$this->addvars['sort']] = $this->addvars['sort'];
        $sort['dir'] = $this->addvars['dir'];
        if ( !$schdays = $this->dof->storage('schdays')->get_listing($conds,$limitfrom,$limitnum,$sort) )
        {// не нашли скажем об этом
            return array('<div align="center">(<i>'.$this->dof->get_string('no_schdays_found', $this->get_im).'</i>)</div>',0);
        }
        // формируем данные
        $this->data = array();
        foreach ( $schdays as $schday )
        {//для каждого периода формируем строку
            $this->data[] = $this->get_string_all($schday);
        }
        return array($this->print_table(),count($schdays));
    }
    
    /** Получает строку таблицы для отображения списка периодов
     * @param object $$obj - объект периода из таблицы ages
     * @return array
     */
    private function get_string_all($obj)
    {
        $add = array();
        $add['departmentid'] = $this->departmentid;
        $add['ageid'] = $this->addvars['ageid'];
        $outadd = array();
        $outadd['departmentid'] = $this->departmentid;
        $string = array();
        $string[] = dof_userdate($obj->date,"%d-%m-%Y");
        $string[] = $this->dof->storage('ages')->get_field($obj->departmentid,'name');
        $string[] = $this->dof->storage('departments')->get_field($obj->departmentid,'name').' <br>['.
                    $this->dof->storage('departments')->get_field($obj->departmentid,'code').']';
        $string[] = $obj->daynum;
        $string[] = $obj->dayvar;
        $string[] = $this->dof->workflow('schdays')->get_name($obj->status);
        // добавляем ссылку
        $link = '';
        if ( $this->dof->storage('schdays')->is_access('edit', $obj->id) )
        {//покажем ссылку на страницу редактирования
            $link .= $this->dof->modlib('ig')->icon('edit',
                    $this->dof->url_im($this->get_im(),'/edit.php?id='.$obj->id,$add));
        }
        /*
        if ( $this->dof->storage('ages')->is_access('view', $obj->id) )
        {//покажем ссылку на страницу просмотра
            $link .= $this->dof->modlib('ig')->icon('view',
                    $this->dof->url_im($this->get_im(),'/view.php?ageid='.$obj->id,$add));
        }
        if ( $this->dof->im('plans')->is_access('viewthemeplan',$obj->id,null,'ages') )
        {// если есть право на просмотр планирования
            $link .= $this->dof->modlib('ig')->icon_plugin('plan','im',$this->get_im(),
                    $this->dof->url_im('plans','/themeplan/viewthemeplan.php?linktype=ages&linkid='.$obj->id,$outadd),
                    array('title'=>$this->dof->get_string('view_plancstream', $this->get_im())));
        }*/
        array_unshift($string, $link);
        return $string;
    }
    
    /** Возвращает html-код таблицы
     * @param string $type - тип таблицы
     * @return string - html-код или пустая строка
     */
    private function print_table($type='all')
    {
        // рисуем таблицу
        $table = new stdClass();
        $table->tablealign = "center";
        $table->cellpadding = 5;
        $table->cellspacing = 5;
        switch ( $type )
        {
            // для одного периода
            case 'view':
                $table->size = array ('100px','150px');
                $table->align = array ("center","center");
                break;
            // полная версия
            case 'full':
            // для списка периодов 
            case 'all':
                $table->size = array ('100px','150px','150px','200px','150px','100px');
                $table->align = array ("center","center","center","center","center","center","center","center","center");
                break;
        }
        
        // шапка таблицы
        $table->head =  $this->get_fields_description($type);
        // заносим данные в таблицу     
        $table->data = $this->data;
        return $this->dof->modlib('widgets')->print_table($table,true);
    }
    
    /** Получить заголовоки таблицы
     * @param string $type - тип таблицы
     * @return array
     */
    private function get_fields_description($type)
    {
        $head = array();
        switch ( $type )
        {
            // для одного периода
            case 'view':
            // полная версия
            case 'full':
                $head[] = $this->dof->modlib('ig')->igs('actions');
                $head[] = $this->dof->get_string('date',       $this->get_im());
                $head[] = $this->dof->get_string('age',        $this->get_im());
                $head[] = $this->dof->get_string('department', $this->get_im());
                $head[] = $this->dof->get_string('daynum',     $this->get_im());
                $head[] = $this->dof->get_string('dayvar',     $this->get_im());
                $head[] = $this->dof->get_string('type',       $this->get_im());
                $head[] = $this->dof->modlib('ig')->igs('status');
                break;
            // для списка периодов 
            case 'all':
                $head[] = $this->dof->modlib('ig')->igs('actions');
                list($url,$icon) = $this->get_link_sort('date');
                $head[] = '<a href=\''.$url.'\'>'.
                    $this->dof->get_string('date', $this->get_im()).'</a>'.$icon;
                list($url,$icon) = $this->get_link_sort('age');
                $head[] = '<a href=\''.$url.'\'>'.
                    $this->dof->get_string('ageid', $this->get_im()).'</a>'.$icon;
                $head[] = $this->dof->get_string('department',  $this->get_im());
                list($url,$icon) = $this->get_link_sort('daynum');
                $head[] = '<a href=\''.$url.'\'>'.
                    $this->dof->get_string('dayvar', $this->get_im()).'</a>'.$icon;
                list($url,$icon) = $this->get_link_sort('status');
                $head[] = '<a href=\''.$url.'\'>'.
                    $this->dof->modlib('ig')->igs('status').'</a>'.$icon;
                break;
        }
        return $head;

    }
    
    /** Получить ссылку с иконкой сортировки
     * @param sting type - имя поля для которого добавляется иконка сортировки
     * @return array
     */
    private function get_link_sort($type)
    {   
        $add = $this->addvars;
        list($dir,$icon) = $this->dof->modlib('ig')->get_icon_sort($type,$add['sort'],$add['dir']);
        unset($add['sort']);
        unset($add['dir']);
        return array($this->dof->url_im('ages','/list.php?sort='.$type.'&dir='.$dir,$add),$icon);
    }
    
}


class dof_im_schdays_schedule_manager
{
    protected $dof;
    private $addvars;
    private $departmentid; // подразделение
    private $ageid; // подразделение
    
    function __construct($dof, $addvars)
    {
        $this->dof = $dof;
        $this->addvars = $addvars;
        $this->departmentid = $addvars['departmentid'];
        $this->ageid = $addvars['ageid'];
    }
    
    /** Возвращает код плагина для языковых строк и ссылок
     * @return string
     * @access private
     */
    private function im_code()
    {
        return 'schdays';
    }

    public function auto_create_days($day)
    {

    }

    
    
    
    public function create_days($ageid, $departmentid, $datestart, $dateend)
    {
        // Создадим расписание и получим ошибки, если они есть
        $errors = $this->dof->im('schdays')->auto_create_events($ageid,$departmentid, $datestart, $dateend, true);
        
        if ( $errors === true )
        {
            return $this->dof->get_string('error:no_days_week', $this->im_code());
        }
        if ( ! empty($errors) )
        {// при сохранении шаблонов возникли ошибки
            $errordate = dof_userdate($datestart,"%d-%m-%Y") . ' - ' .
                         dof_userdate($dateend,"%d-%m-%Y") . ': <br/>';
            $message = $this->style_result_message($errordate, false);
            
            $errordays[] = $message.$this->get_templates_errors($errors);
            
            $message = implode(' ', $errordays);
        } else
        {// ошибок нет, отображаем, что всё хорошо
            $message = $this->dof->get_string('schedule_created', $this->im_code());
            $message = $this->style_result_message($message, true);
        }
        return $message;
    }
    
    /** Содать расписание на день
     * @param object $day - Объект дня из БД
     * 
     * @return string - сообщение о результате формирования расписания на день
     */
    public function create_day($day)
    {
        // Получим ID шаблонов, расписание для которых не удалось создать 
        $errors = $this->dof->storage('schevents')->create_from_templates($day->id);
        $message = '';
        if ( ! empty($errors) )
        {// При формировании расписания из шаблонов возникли ошибки
            // Сменим статус дня на Неполное расписание
            $this->dof->workflow('schdays')->change($day->id, 'draft');
            // Сформируем вывод информации об результатах создания расписания
            $errordays[] = $this->get_templates_errors(array($day->id => $errors));
            if ( ! empty($errordays) )
            {// При создании расписания на некоторые дни возникли ошибки - отобразим их
                $message = implode(' ', $errordays);
                return $message;
            }
        } else
        {// Расписание успешно создано
            // Сменим статус дня на Создано расписание
            $this->dof->workflow('schdays')->change($day->id, 'active');
            
            // Сформируем вывод информации об результатах создания расписания
            $message = $this->dof->get_string('schedule_created', $this->im_code());
            $message = $this->style_result_message($message, true);
            $link = $this->dof->url_im('schdays','/view.php?id='.$day->id,$this->addvars);
            return $this->dof->modlib('widgets')->button($message,$link);
        }
    }

    public function update_day($day)
    {
        
        $delevents = $this->delete_events_day($day->id);
        $message = '';  
        // если все прошло успешно - удалить сам день
        if ( ! $delevents )
        {// все события удалены
            $message = $this->dof->get_string('schedule_delete_failed', $this->im_code());
            $message = $this->style_result_message($message, true);
            $link = $this->dof->url_im('schdays','/view.php?id='.$day->id,$this->addvars);
            return $this->dof->modlib('widgets')->button($message,$link);  
        }
        $this->dof->workflow('schdays')->change($day->id, 'plan');

        $errors = $this->dof->storage('schevents')->create_from_templates($day->id);

        if ( ! empty($errors) )
        {// при сохранении шаблонов возникли ошибки
            $this->dof->workflow('schdays')->change($day->id, 'draft');
            $errordays[] = $message.$this->get_templates_errors(array($day->id => $errors));
            if ( ! empty($errordays) )
            {// при создании расписания на некоторые дни возникли ошибки - отобразим их
                $message = implode(' ', $errordays);
                return $message;
            }
        } else
        {
            $this->dof->workflow('schdays')->change($day->id, 'active');
            $message = $this->dof->get_string('schedule_update', $this->im_code());
            $message = $this->style_result_message($message, true);
            $link = $this->dof->url_im('schdays','/view.php?id='.$day->id,$this->addvars);
            return $this->dof->modlib('widgets')->button($message,$link);
        }
    }

    /** Удалить день вместе с событиями
     * @param array $dayid - данные, пришедшие из формы
     * 
     * @return bool
     */
    public function delete_day($dayid)
    {
        if ( is_object($dayid) )
        {
            if ( isset($dayid->id) )
            {
                $dayid = $dayid->id;
            } else
            {
                return false;
            }
        }
        if ( !is_int_string($dayid) )
        {
            return false;
        }
        $delevents = $this->delete_events_day($dayid);  
        // если все прошло успешно - удалить сам день
        if ( $delevents )
        {// все события удалены
            $this->dof->workflow('schdays')->change($dayid, 'plan');
            $message = $this->dof->get_string('schedule_delete', $this->im_code());
            $message = $this->style_result_message($message, true);
            $link = $this->dof->url_im('schdays','/view.php?id='.$dayid,$this->addvars);
            return $this->dof->modlib('widgets')->button($message,$link);
        } else 
        {// не смогли удалить ВСЕ дня с этой датой
            $this->dof->workflow('schdays')->change($day->id, 'draft');
            $message = $this->dof->get_string('schedule_delete_failed', $this->im_code());
            $message = $this->style_result_message($message, true);
            $link = $this->dof->url_im('schdays','/view.php?id='.$dayid,$this->addvars);
            return $this->dof->modlib('widgets')->button($message,$link);  
        }    
    }
    
    /** 
     * Восстановить события на указанный день
     * 
     * @param int $day - День, события которого требуется восстановить
     * 
     * @return string - Результат восстановления в виде таблицы
     */
    public function restore_events_day($day)
    {
        // Базовые параметры
        $usertimezone = $this->dof->storage('persons')->get_usertimezone_as_number();
        
        // Получение удаленных событий дня
        $junkstatuses = $this->dof->workflow('schevents')->get_meta_list('junk');
        $junkstatusessearch = array_keys($junkstatuses);
        $junkevents = $this->dof->storage('schevents')->get_records(['dayid' => $day->id, 'status' => $junkstatusessearch]);
        
        // Получение текущих событий дня
        $realstatuses = $this->dof->workflow('schevents')->get_meta_list('real');
        $realstatusessearch = array_keys($realstatuses);
        $realevents = $this->dof->storage('schevents')->get_records(['dayid' => $day->id, 'status' => $realstatusessearch]);
        
        // Таблица для отображения результата
        $table = new stdClass();
        $table->align = ["left", 'center'];
        $table->head = [
            'event' => $this->dof->get_string('process_events_restore_title_deleted_event', 'schdays'),
            'status' => $this->dof->get_string('process_events_restore_title_process_status', 'schdays')
        ];
        $table->data = [];
        
        // Массив с данными для учета дублей событий
        $restored_check = [];
        // Массив для учета смены статусов у контрольных точек
        $returned_plans = [];
        
        if ( is_array($junkevents) )
        {// Возврат каждого дня
            foreach ( $junkevents as $junkevent )
            {
                // Ключ для проверки дублирующих событий
                $checkkey = 'e'.$junkevent->templateid.'_'.$junkevent->planid;
                // Проверка дублирования события
                if ( isset($restored_check[$checkkey]) )
                {// Событие полностью дублирует уже возвращенное
                    continue;
                }
                
                // Данные о событии для таблицы
                $eventstr = dof_userdate($junkevent->date, '%d.%m.%Y %H:%M', $usertimezone);
                $eventstatusstr = '';
                
                // Имя статуса
                $event_newstatus = $this->dof->workflow('schevents')->restore_status($junkevent->id, 'real', ['return' => 'newstatus']);
                if ( $event_newstatus )
                {// Успешная смена статуса
                    
                    // Добавление данных события для защиты от дублирования
                    if ( ! empty($junkevent->planid) || ! empty($junkevent->templateid) )
                    {// Добавление события для проверок на уникальность
                        $restored_check[$checkkey] = $junkevent->id;
                    }
                    
                    // Добавление сообщения
                    $statusname = $this->dof->workflow('schevents')->get_name($event_newstatus);
                    $eventstatusstr .= $this->dof->modlib('widgets')->success_message(
                        $this->dof->get_string('process_events_restore_change_success', 'schdays', $statusname)
                    );
                    
                    // Работа с тематическим планом события
                    if ( ! empty($junkevent->planid) )
                    {// Событие связано с тематическим планом
                        $plan = $this->dof->storage('plans')->get($junkevent->planid);
                    
                        if ( ! empty($plan) )
                        {// План указан
                            
                            if ( isset($returned_plans[$junkevent->planid]) )
                            {// Контрольная точка уже возвращала статус в рамках другого события
                                $plan_newstatus = $returned_plans[$junkevent->planid];
                            } else 
                            {// Вернуть план в предыдущее состояние
                                $plan_newstatus = $this->dof->workflow('plans')->
                                    restore_status($junkevent->planid, 'real', ['return' => 'newstatus']);
                            }
                            if ( $plan_newstatus )
                            {// Успешная смена статуса тематического плана для события
                                $returned_plans[$plan->id] = $plan_newstatus;
                                // Добавление сообщения
                                $statusname = $this->dof->workflow('plans')->get_name($plan_newstatus);
                                $eventstatusstr .= $this->dof->modlib('widgets')->success_message(
                                    $this->dof->get_string('process_events_restore_change_plan_success', 'schdays', $statusname)
                                );
                            } else
                            {// Смена статуса тематического плана не удалась
                                // Добавление сообщения
                                $eventstatusstr .= $this->dof->modlib('widgets')->error_message(
                                    $this->dof->get_string('process_events_restore_change_plan_error', 'schdays')
                                );
                            }
                        } else
                        {// Тематический план не найден
                            // Добавление сообщения
                            $eventstatusstr .= $this->dof->modlib('widgets')->error_message(
                                $this->dof->get_string('process_events_restore_error_plan_not_found', 'schdays')
                            );
                        }
                    }
                } else
                {// Неудачная смена статуса
                    // Добавление сообщения
                    $eventstatusstr .= $this->dof->modlib('widgets')->error_message(
                        $this->dof->get_string('process_events_restore_change_error', 'schdays')
                    );
                }
                    
                // Формирование строки таблицы
                $table->data[$junkevent->id] = [
                    'event' => $eventstr,
                    'status' => $eventstatusstr
                ];
            }
        }
        
        // Вернуть состояние дня
        $this->dof->workflow('schdays')->restore_status($day->id, 'real');
        
        $html = $this->dof->modlib('widgets')->print_table($table, true);
        $html .= $this->dof->modlib('widgets')->button(
            $this->dof->get_string('schedule_restored', $this->im_code()), 
            $this->dof->url_im('schdays', '/view.php?id='.$day->id, $this->addvars)
        );
        
        return $html;
    }
    
    /** Удаляет расписание на указанный день
     * @param int $dayid - id созданного дня
     * @return bool - результат удаления расписания
     */
    public function delete_events_day($dayid)
    {
        $res = true;
        $conds = new stdClass();
        $conds->dayid = $dayid;
        $sql = $this->dof->storage('schevents')->get_select_listing($conds);
        if ( $events = $this->dof->storage('schevents')->get_records_select($sql) )
        {
            foreach($events as $event)
            {// для каждой КТ удалим ее вместе с событием
                if ( ! $this->dof->storage('schevents')->cancel_event($event->id, true) ) 
                {// не удалось удалить событие
                    // нельзя будет удалить и ДЕНЬ
                    $res = false;
                }
            }
        }
        return $res;
    }

    /** Создает расписание на день
     * 
     * @deprecated - К удалению
     * 
     * @param int $ageid - id периода
     * @param int $daynum -день недели
     * @param int $dayvar - вариант недели
     * @param int $dayid - id созданного дня
     * @param int $depid - id подразделения
     * @param int $implied - является ли урок мнимым
     * @return array - массив шаблонов, на которые не создались события
     */
    /*public function create_events_day($daynum,$dayvar,$dayid)
    {
        // найдем все интересующие нас шаблоны 
        $conds = new stdClass();
        $conds->departmentid = $this->departmentid;
        $conds->daynum = $daynum;
        $conds->dayvar = $dayvar;
        $conds->ageid  = $this->ageid;
        $conds->status = array('active');
        if ( ! $templates = $this->dof->storage('schtemplates')->get_objects_list($conds))
        {// не нашли шаблоны - не надо создавать события';
            return array();
        }
        $templateids = array();
        foreach ( $templates as $template )
        {// для каждого шаблона создадим событие
            if ( ! $cstream = $this->dof->storage('cstreams')->get($template->cstreamid) )
            {// поток не найден
                $template->error = 'error:cstream_not_found';
                $template->errortype = 'schtemplate';
                $templateids[$template->id] = $template;
                continue;
            }
            if ( $cstream->status != 'active' )
            {// поток не активный - создать урок нельзя
                $template->error = 'error:cstream_is_not_active';
                $template->errortype = 'cstream';
                $templateids[$template->id] = $template;
                continue;
            }
            // расчитываем дату
            $date = $this->dof->storage('schdays')->get_field($dayid,'date');
            // отматываем дату дня на начало дня и добавляем время урока по шаблону
            $date_begin = $date - (DAYSECS / 2) + $template->begin;
            if ( ($cstream->begindate > $date_begin) OR ($cstream->enddate < $date_begin) )
            {// если дата урока не входит в промежуток времени потока
                // событие создавать нельзя
                $template->error = 'error:begindate_and_cstream_not_compatible';
                $template->errortype = 'cstream';
                $templateids[$template->id] = $template;
                continue;
            }
            $event = new stdClass();
            $event->templateid     = $template->id; // id шаблона
            $event->dayid          = $dayid;// id дня
            $event->type           = $template->type; //тип урока
            $event->cstreamid      = $template->cstreamid; // id потока
            $event->teacherid      = $cstream->teacherid; //id учителя @todo может и не надо
            $event->appointmentid  = $cstream->appointmentid; // id должности учителя, который ведет урок

            if ( isset($cstream->appointmentid) AND $cstream->appointmentid )
            {// проверим статус табельного номера
                $status = $this->dof->storage('appointments')->get_field($cstream->appointmentid, 'status');
                if ( $status == 'patient' OR $status == 'vacation' )
                {// учитель на больничном или в отпуске не может быть назначен событию
                    $event->teacherid      = 0;
                    $event->appointmentid  = 0;
                }
            }

            $event->date           = $date_begin; // дата урока
            $event->duration       = $template->duration; // длительность
            $event->place          = $template->place; // аудитория
            $event->form           = $template->form; // форма занятия        
            $event->ahours         = 1; // предполагаемое кол-во академических часов

            if ( ! $scheventid = $this->dof->storage('schevents')->insert($event) )
            {// не удалось сохранить событие
                $template->error = 'error:schevent_not_saved';
                $template->errortype = 'schevent';
                $templateids[$template->id] = $template;
                continue;
            }
            $schday = $this->dof->storage('schdays')->get($dayid);
            if ( $schday->type != 'working' )
            {// установим мнимый статус
                $this->dof->workflow('schevents')->change($scheventid, 'implied');
            }
        }
        // вернем шаблоны, где возникли ошибки
        return $templateids;
    }*/
    
    public function style_result_message($message, $success)
    {
       $color = 'red';
       if ( $success )
       {
           $color = 'green';
       }
       return '<p align="center" style="color:'.$color.';"><b>'.$message.'</b></p>'; 
    }
    
    /** Обработать массив не созданных уроков, и вывести сообщение
     * @todo документировать остальные параметры
     * 
     * @param array - массив записей из таблицы schtemplates с дополнительным полем error,
     *                которое содержит всебе идентификатор строки перевода из языкового файла
     * 
     * @return string - сообщение о том какие шаблоны не удалось создать и почему
     */
    protected function get_templates_errors($errors)
    {
        $result = '';
        if ( empty($errors) )
        {// ошибок нет - выводить нечего
            return $result;
        }
        
        $message = $this->dof->get_string('error:schedule_not_created', $this->im_code());
        $message = $this->style_result_message($message, false);
        $result .= $message;
        foreach ( $errors as $dayid => $dayerrors )
        {// Формируем список ошибок для каждого дня
            // Получим день
            $day = $this->dof->storage('schdays')->get($dayid);
            
            // Сформируем заголовок
            if ( ! empty($day) )
            {// День найден
                $timezone = $this->dof->storage('departments')->get_timezone($day->departmentid);
                $name = dof_userdate($day->date, "%d-%m-%Y", $timezone, false);
            } else 
            {// День не найден в системе - выведем ID
                $name = 'ID: '.$dayid;
            }
            $result .= html_writer::div($name, '', array('align' => 'center', 'style' => 'color: red'));
            $result .= html_writer::div(
                    $this->dof->get_string($dayerrors['code'], $this->im_code())
                    , '', array('align' => 'center', 'style' => 'color: red'));
            
            if ( isset($dayerrors['templates']) )
            {// Есть ошибки в шаблонах
                $table = new stdClass();
                $table->align = array("center","center","center","center","center");
                $table->head = array(
                        $this->dof->get_string('cstream_name', 'schdays'),
                        $this->dof->get_string('event_time', 'schdays'),
                        $this->dof->modlib('ig')->igs('error'),
                        $this->dof->modlib('ig')->igs('actions')
                );
                foreach( $dayerrors['templates'] as $templateid => $errorcode )
                {
                    // Получим битый шаблон
                    $template = $this->dof->storage('schtemplates')->get($templateid);
                    if ( empty($template) )
                    {// Шаблон не нашли
                        $table->data[] = array(
                                '',
                                '',
                                $this->dof->get_string('error:template_not_found', 'schdays', $templateid),
                                ''
                        );
                    } else 
                    {// Шаблон есть в системе
                        // Получим учебный процесс
                        $cstream = $this->dof->storage('cstreams')->get($template->cstreamid);
                        if ( empty($cstream) )
                        {// Учебного процесса нет
                            $link = '';
                            $ageid = 0;
                        } else 
                        {// Сформируем ссылку на учебный процесс
                            $addvars = array('departmentid' => $cstream->departmentid,
                                             'cstreamid'    => $cstream->id);
                            $url = $this->dof->url_im('cstreams','/view.php',$addvars);
                            $link = html_writer::link($url, $cstream->name);
                            $ageid = $cstream->ageid;
                        }
                        
                        // Получим часовой пояс подразделения , которому принадежит день
                        $timezone = $this->dof->storage('departments')->get_timezone($dayid);
                        // Получим время начала дня
                        $daydate = dof_usergetdate($day->date, $timezone);
                        $daystart = make_timestamp(
                            $daydate['year'], 
                            $daydate['mon'], 
                            $daydate['mday'], 
                            0, 
                            0, 
                            0, 
                            $timezone
                        );
                        // Получим время начала и конца урока
                        $event_begin = $daystart + $template->begin;
                        $event_end = $event_begin + $template->duration;
                        // Сформируем для пользователя
                        $usereventstart = $this->dof->storage('persons')->
                            get_userdate($event_begin, "%H:%M");
                        $usereventend = $this->dof->storage('persons')->
                            get_userdate($event_end, "%H:%M");
                        $time = $usereventstart.' - '.$usereventend;
                        
                        // Действия над шаблоном
                        $actions = '';
                        $addvars = array(
                                'departmentid' => $template->departmentid,
                                'ageid' => $ageid,
                                'id' => $template->id
                        );
                        if ( $this->dof->storage('schtemplates')->is_access('view',$template->id) )
                        {// пользователь может просматривать шаблон
                            $actions .= ' <a href='.$this->dof->url_im('schedule','/view.php', $addvars).' target="_blank" >'.
                                '<img src="'.$this->dof->url_im('schedule', '/icons/view.png').
                                '"alt="'.$this->dof->get_string('view_template', $this->im_code()).
                                '" title="'.$this->dof->get_string('view_template', $this->im_code()).'">'.'</a>';
                        }
                        if ( $this->dof->storage('schtemplates')->is_access('edit',$template->id) )
                        {// пользователь может редактировать шаблон
                            $actions .= ' <a href='.$this->dof->url_im('schedule','/edit.php',$addvars).' target="_blank" >'.
                                '<img src="'.$this->dof->url_im('schedule', '/icons/edit.png').
                                '"alt="'.$this->dof->get_string('edit_template', $this->im_code()).
                                '" title="'.$this->dof->get_string('edit_template', $this->im_code()).'">'.'</a>';
                        }
                            
                        // Сфомируем строку
                        $table->data[] = array(
                                $link,
                                $time,
                                $this->dof->get_string($errorcode, 'schdays', $templateid),
                                $actions
                        );
                    }
                }
                $table = $this->dof->modlib('widgets')->print_table($table, true);
                $result .= $table;
            }
        }
        return $result;
    }
    
    /** Получить строку с данными для таблицы  с ошибками
     * @todo дописать варианты для ошибок шаблона и события
     * 
     * @return array
     */
    protected function get_error_table_row($dayerrors)
    {
        $row = array();
        switch ( $dayerrors->code )
        {
            // Ошибки шаблонов
            case 'templates_errors': 
                $row[] = $dayerrors->name;
                $row[] = '';
                $row[] = '';
                $row[] = $dayerrors->code;
                $row[] = '';
                return $this->get_error_table_row_schtemplate($template);
            break;
            // День не найден
            case 'day_not_found': 
                $row[] = $dayerrors->name;
                $row[] = '';
                $row[] = '';
                $row[] = $dayerrors->code;
                $row[] = '';
                return $this->get_error_table_row_schevent($template);
            break;
            // Доступ запрещен
            case 'access_denied':
                $row[] = $dayerrors->name;
                $row[] = '';
                $row[] = '';
                $row[] = $dayerrors->code;
                $row[] = '';
                return $this->get_error_table_row_cstream($template);
            break;
        }
    }
    
    /** Получить строку таблицы для отображения информации об ошибке потока
     * (предполагается, что при передачи данных в эту функцию поток существует)
     * 
     */
    protected function get_error_table_row_cstream($template)
    {
        $row = array();
        $emptyrow = array('','','',$this->dof->get_string($template->error, $this->im_code()),'');
        
        if ( ! $cstream = $this->dof->storage('cstreams')->get($template->cstreamid) )
        {
            return $emptyrow;
        }
        if ( $this->dof->storage('cstreams')->is_access('view', $cstream->id) )
        {// у пользователя есть право на просмотр предмето-класса - покажем ссылку
            $cstreamname = '<a href='.$this->dof->url_im('cstreams','/view.php?cstreamid='.$cstream->id,
                           array('departmentid' => $this->departmentid)).' target="_blank" >'.
                           $cstream->name.'</a>';
        }else
        {// у пользователя нет права на просмотр предмето-класса - покажем только название
            $cstreamname = $cstream->name;
        }
        if ( ! $pitem   = $this->dof->storage('programmitems')->get($cstream->programmitemid) )
        {
            return $emptyrow;
        }
        if ( ! $appointment = $this->dof->storage('appointments')->get($cstream->appointmentid) )
        {
            return $emptyrow;
        }
        if ( ! $eagreement  = $this->dof->storage('eagreements')->get($appointment->eagreementid) )
        {
            return $emptyrow;
        }
        if ( ! $teachername = $this->dof->storage('persons')->get_fullname($eagreement->personid) )
        {
            return $emptyrow;
        }
        
        $row[] = $cstreamname;
        $row[] = $pitem->name; 
        $row[] = $teachername;
        $row[] = $this->dof->get_string($template->error, $this->im_code());
        // ссылки на просмотр и редактирование шаблона
        $link  = $this->get_link('view_template', $template);
        $link .= $this->get_link('edit_template', $template);
        $row[] = $link;
        
        return $row;
    }
    
    /** Получить строку таблицы для отображения информации об ошибке шаблона
     * @todo изменить порядок элементов в массиве, когда будут отображаться 3 разные таблицы
     * 
     */
    protected function get_error_table_row_schtemplate($template)
    {
        $row = array();
        switch ($template->error)
        {
            // предмето-класс не найден
            case 'error:cstream_not_found': 
                // нет потока
                $row[] = $this->dof->modlib('ig')->igs('no');
                // нет учителя
                $row[] = $this->dof->modlib('ig')->igs('no');
                // нет предмета
                $row[] = $this->dof->modlib('ig')->igs('no');
                // описание ошибки
                $row[] = $this->dof->get_string($template->error, $this->im_code());
                // ссылки на просмотр и редактирование шаблона
                $link  = $this->get_link('view_template', $template);
                $link .= $this->get_link('edit_template', $template);
                $row[] = $link;
            break;
        }
        
        return $row;
    }
    
    /** Получить строку таблицы для отображения информации об ошибке события
     * (предполагается, что при передачи данных в эту функцию поток существует)
     * @todo изменить порядок элементов в массиве, когда будут отображаться 3 разные таблицы
     * 
     */
    protected function get_error_table_row_schevent($template)
    {
        $row = array();
        
        switch ($template->error)
        {
            
        }
        
        return $row;
    }
    
    /** Получить ссылку с иконкой, для выполнения действия, с проверкой прав
     * @param string $action - совершаемое действие
     * @param int $id - id объекта, на который генерируется ссылка
     * 
     */
    protected function get_link($action, $template)
    {
        $link = '';
        // дополнительные параметры ссылки
        $add = array('departmentid' => $this->departmentid,
                     'ageid'        => $this->ageid);
        switch ( $action )
        {
            case 'view_template': 
                $id = $template->id;
                if ( $this->dof->storage('schtemplates')->is_access('view',$id) )
                {// пользователь может просматривать шаблон
                    $link .= ' <a href='.$this->dof->url_im('schedule','/view.php?id='.$id,$add).' target="_blank" >'.
                            '<img src="'.$this->dof->url_im('schedule', '/icons/view.png').
                            '"alt="'.$this->dof->get_string('view_template', $this->im_code()).
                            '" title="'.$this->dof->get_string('view_template', $this->im_code()).'">'.'</a>';
                }
            break;
            case 'edit_template':
                $id = $template->id;
                if ( $this->dof->storage('schtemplates')->is_access('edit',$id) )
                {// пользователь может редактировать шаблон
                    $link .= ' <a href='.$this->dof->url_im('schedule','/edit.php?id='.$id,$add).' target="_blank" >'.
                            '<img src="'.$this->dof->url_im('schedule', '/icons/edit.png').
                            '"alt="'.$this->dof->get_string('edit_template', $this->im_code()).
                            '" title="'.$this->dof->get_string('edit_template', $this->im_code()).'">'.'</a>';
                }
            break;
        }
        
        return $link;
    }
    
    /** Получить массив строк, которые будут являться заголовками для таблицы ошибок,
     * возникших при создании расписания
     * @todo дописать варианты для шаблона и события
     * 
     * @param string $type - тип таблицы с ошибками. Возможные варианты:
     *                       schtemplate - ошибка в шаблоне
     *                       cstream - ошибка в потоке
     *                       event - ошибка в событии
     * 
     * @return array
     */
    protected function get_error_table_header($type)
    {
        switch ( $type )
        {
            // таблица с ошибками шаблона
            case 'schtemplate': 
            // таблица с ошибками события
            case 'event': 
            // таблица с ошибками потока
            case 'cstream':
            return array($this->dof->get_string('cstream_name', $this->im_code()),
                         $this->dof->get_string('item', $this->im_code()),
                         $this->dof->get_string('teacher', $this->im_code()),
                         $this->dof->modlib('ig')->igs('error'),
                         $this->dof->modlib('ig')->igs('actions'));
            break;
        }
        return array();
    }
}


?>