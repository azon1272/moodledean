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
// Copyright (C) 2008-2999  Evgeniy Gorelov (Евгений Горелов)             //
// Copyright (C) 2008-2999  Ilya Fastenko (Илья Фастенко)                 //
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

// Подключение библиотеки
global $DOF;
require_once($DOF->plugin_path('storage','orders','/baseorder.php'));

/**
 * Базовый класс для подлписывания студентов на дисциплины
 *
 * @params obj $data объект с данными приказа
 * @params int depid id подразделения, из которого брать персон
 * @params int $stage номер этапа тестирования
 * @params bool $commoncstream общий ли для всех студентов приказа cstream
 * @author 2011 Evgeniy Gorelov
 */
class dof_modlib_cur_order_base extends dof_storage_orders_baseorder
{
    public $data;
    public $departmentid;
    protected $code;
    
    /** Конструктор
     * @param dof_control $dof - объект с методами ядра деканата
     * @param int $departmentid[optional] id подразделения приказа, 
     *      - при создании экземпляра для последующей загрузки приказа 
     *      из БД данное поле можно не указывать, т.к. оно заполнено в БД
     *      - при создании экземпляра для последующего сохранения в БД 
     *      данное поле следует обязательно указать, т.к. будут 
     *      проблемы при исполнении приказа
     *      при загрузке старого
     * @access public
     */
    public function __construct($dof, $departmentid=null)
    {
        parent::__construct($dof);
        // устанавливаем подразделение приказа
        $this->departmentid = $departmentid;
        // устанавливаем дополнительные свойства
        $this->cur_init();
    }
    
    public function plugintype()
    {
        return 'modlib';
    }
    
    public function plugincode()
    {
        return 'cur';
    }
    
    public function code()
    {
        return 'base';
    }
    
    // **********************************************
    //                  API
    // **********************************************

    /**
     * Инициализирует объект
     */
    protected function cur_init()
    {
        $this->data->cur_students = array();
        return true;
    }
    
    /**
     * Устанавливает фильтры в опции приказа
     * @param string $code - код приказа(аттестация продавцов/аттестация кассиров)
     * @param array $options - массив опций
     * @author Ilya Fastenko
     */
    public function cur_set_filters($code, $options=array())
    {
        if($code !== 'avattysell' AND $code !== 'avattycash' AND $code !== 'reportorder' )
        {
            return false;
        }

        if(!empty($options))
        {
            foreach($options as $key => $value)
            {
                $property = 'f_'.$code.'_'.$key;
                $this->data->$property = $value;
            }
        }
    }
    
    /**
     * Получить значения фильтра в виде объекта
     * 
     * вид свойств фильтра 
     * f_{$code}_name
     * 
     * @param string $code коде фильтра
     * @return object если найдутся заполненные свойства, т.е. != null, 
     *      то объект со свойствами, иначе - пустой объект
     * @author 2011 Evgeniy Gorelov
     */
    public function cur_get_filters($code)
    {
        $objfilters = new stdClass();

        // в любом случае получим массив
        foreach($this->data as $key=>$value)
        {
            $splitkey = split('_', $key, 3);
            // найдем свойства объкта, которые являются фильтром 
            // и имеют значения
            if ( isset($splitkey[0]) AND isset($splitkey[1])
                    AND 'f' == $splitkey[0] AND $code == $splitkey[1] 
                    AND null != $value )
            {
                $objfilters->$splitkey[2] = $value;
            }
        }
        
        return $objfilters;
    }
    
    /**
     * Устанавливает опции приказа
     * @param int $programmitemid - id дисциплины
     * @param array $options - массив опций
     * @author Ilya Fastenko
     */
    protected function cur_set_programmitems($programmitemid,$options=array())
    {
        return true;
    }
    
    /**
     * Устанавливает глобальные опции приказа
     * @param array $options - массив опций
     * @author Ilya Fastenko
     */
    public function cur_set_options($options=array())
    {
        if(!empty($options))
        {
            foreach($options as $key => $value)
            {
                $this->data->$key = $value;
            }
        }
    }
    
    //TODO get_options($key=null) - возвращает объект опций или одну опцию по ключу. Если опции нет, возвращает NULL
    
    /**
     * Метод для заполнения объкта приказа данными о персонах по-умолчанию
     */
    protected function cur_regenerate()
    {
        return true;
    }
    
    /**
     * Сохраняет в сессии текущий экземпляр класса 
     * 
     * @params int $idtmp[optional] временный id для хранения объекта в сессии
     *      Если его не передать, то сгенерируется новое случайное число и 
     *      объект сохранится с новым временным id в сессии.
     * @author Evgeniy Gorelov
     */
    public function cur_save_session($idtmp = null)
    {
        // Наличие сессии не проверяем, т.к. этим занимается сам moodle 
        // Проверим входные параметры
        if ( ! is_null($idtmp) AND ! is_int_string($idtmp) )
        {
            return false;
        }
        
        $obj = new stdClass();
        // сохраним в объекте все свойства, кроме dof
        foreach($this as $optname => $optvalue)
        {
            if ('dof' != $optname)
            {
                $obj->$optname = $optvalue;
            }
        }
        // Если передали ненулевое число
        if ( $idtmp )
        {
            // Если в сессии есть объект с таким идентификатором
            if (isset($_SESSION['dof'][$this->plugintype()][$this->plugincode()][$idtmp]))
            {
                // Удалим старый объект
                unset($_SESSION['dof'][$this->plugintype()][$this->plugincode()][$idtmp]);
                // Сохраним новый объект
                $_SESSION['dof'][$this->plugintype()][$this->plugincode()][$idtmp] = $obj;
            }
        }
        // Если id не передали, или передали 0
        // 0 не считаем за идентификатор
        else
        {
            // Будем генерировать новый идентификатор пока не найдем такой, 
            // с которым нет объектов в нашей папке сессии
            do
            {
                $idtmp = mt_rand(1,1000000000);
            } while(isset($_SESSION['dof'][$this->plugintype()][$this->plugincode()][$idtmp]));
            // Сохраним новый объект
            $_SESSION['dof'][$this->plugintype()][$this->plugincode()][$idtmp] = $obj;
        }
//        var_dump($idtmp);
//        var_dump($_SESSION);

        return $idtmp;
    }
    
    /**
     * Загружает из сессии текущий экземпляр класса 
     * 
     * @params int $idtmp[optional] временный id для хранения объекта в сессии
     * @return object объект
     * @author Evgeniy Gorelov
     */
    public function cur_load_session($idtmp)
    {
        // Наличие сессии не проверяем, т.к. этим занимается сам moodle 
        // Проверим входные параметры
        if ( ! is_null($idtmp) AND ! is_int_string($idtmp) )
        {
            return false;
        }
        
        if ( ! isset($_SESSION['dof'][$this->plugintype()][$this->plugincode()][$idtmp]) )
        {
            return false;
        }
        
        $obj = $_SESSION['dof'][$this->plugintype()][$this->plugincode()][$idtmp];
        // восстановим в объекте все свойства, кроме dof
        foreach($obj as $optname => $optvalue)
        {
            // на всякий случай проверим, но этого свойства тут не должно быть
            if ('dof' != $optname)
            {
                $this->$optname = $optvalue;
            }
        }
//        var_dump($idtmp);
//        var_dump($this);
        return true;
    }
    
    /**
     * Получает из объекта приказа данные студента по переданному id подписки
     * @param int $programmsbcid - id подписки
     * @return object - объект с данными студента
     * @author Ilya Fastenko
     */
    public function cur_get_student($programmsbcid)
    {
        if (!is_int_string($programmsbcid))
        {
            return false;
        }
        
        return $this->data->cur_students[$programmsbcid];
    }
    
    /**
     * Добавляет студента-подписчика в приказ (если есть - обновляются или 
     *      пересоздаются опции).
     * 
     * @param int $programmsbcid id подписки на программу студента, 
     *      которого мы хотим добавить в объект
     * @param object $options объект с опциями
     * @param bool $force переписать или обновить опции студента, 
     *      ести такой уже есть
     * @return bool
     * @author Evgeniy Gorelov
     */
    public function cur_save_student($programmsbcid,$options, $force=false)
    {
        // проверка входных параметров
        if ( ! is_int_string($programmsbcid) OR ! is_object($options) )
        {
            return false;
        }
        
        // Если такой студент уже есть
        if ( isset($this->data->cur_students[$programmsbcid]) )
        {
            // Если надо пересоздать опции студента
            if ($force)
            {
                unset($this->data->cur_students[$programmsbcid]);
                $this->data->cur_students[$programmsbcid] = $options;
            }
            else
            {
                // преобразуем оба объекта с опциями в массив, сливаем оба массива,
                // причем новые опции при совпадении будут замещать старые
                $sbcopts = array_merge((array)$this->data->cur_students[$programmsbcid],
                        (array)$options);
                // обновим опции студента
                $this->data->cur_students[$programmsbcid] = (object) $sbcopts;
            }
        }
        else
        {
            // если данного студента еще нет, то просто добавим его
            $this->data->cur_students[$programmsbcid] = $options;
        }
        
        return true;
    } 
    
    //TODO delete_student($programmsbcid) - удаляет подписчика из приказа
    
    //TODO list_students() - возвращает массив подписчиков, отсортированный по sortname. Ключ - programmsbcid. Значения sortname, fullname, firstname, middlename, lastname, programmitems (массив с подписками и их опциями), options - дополнительные опции подписки
    //TODO get_itemsbc($programmsbcid,$programmitemid) - получить данные одной подписки
    
/**
     * Сохранить дисциплину с опциями для студента.
     * Если дисципллина уже есть, то опции обновляются.
     * При совпадении опций происходит замещение более новыми.
     * 
     * $cstreamid и $appointmentid = 0 - не указывать, =-1 - выбрать 
     * автоматически, -2 - создать (только для cstreamid). Если 
     * подписка у данного ученика есть - обновляем параметры.
     * 
     * @param int $programmsbcid id подписки на программу
     * @param int $programmitemid id дисциплины, на которую хотим 
     *      подписать данного студента
     * @param int $ageid id учебного периода, в котором будет создан cstream
     * @param int $departmentid id подразделения, в котором будет создан cpassed
     * @param int $cstreamid[optional] id учебного процесса для данной дисциплины
     * @param int $appointmentid[optional] id преподавателя
     * @param object $options[optional]
     * @return bool
     * @author Evgeniy Gorelov
     */
    public function cur_save_itemsbc($programmsbcid, $programmitemid, $ageid, 
            $departmentid, $cstreamid=null, $appointmentid=0, $options=null)
    {
        // проверка входных параметров
        if ( ! is_int_string($programmsbcid)   OR  ! is_int_string($programmitemid)
                OR ! is_int_string($ageid)     OR  ! is_int_string($departmentid) 
                OR ! is_int_string($cstreamid) AND ! is_null($cstreamid) 
                OR ! is_int_string($appointmentid) 
                OR ! is_null($options)         AND ! is_array($options) )
        {
            return false;
        }
        
        $newobjitem = new stdClass();
        $newobjitem->cstreamid = $cstreamid;
        $newobjitem->appointmentid = $appointmentid;
        $newobjitem->ageid = $ageid;
        $newobjitem->departmentid = $departmentid;
        $newobjitem->options = $options;
        
        // Данного студента нет в приказе 
        if ( ! isset($this->data->cur_students) 
                OR ! isset($this->data->cur_students[$programmsbcid]) )
        {
            return false;
        }

        // Если дисциплина существует, то обновим ее опции, 
        // при совпадении замещая новыми
        if ( isset($this->data->cur_students[$programmsbcid]->itemsbcs[$programmitemid]) )
        {
            $itemopts = array_merge( (array)
                    $this->data->cur_students[$programmsbcid]->itemsbcs[$programmitemid],
                    (array) $newobjitem);
            $itemopts = (object)$itemopts;
            $this->data->cur_students[$programmsbcid]->itemsbcs[$programmitemid] = $itemopts;
        }
        // Дисциплина не существует, добавим ее
        else
        {
            $this->data->cur_students[$programmsbcid]->itemsbcs[$programmitemid] = $newobjitem;
        }
        
        return true;
    }
    
    /**
     * Исполнить действия, сопутствующие исполнению приказа 
     * 
     * @param object $order - объект приказа
     * @return bool true/false
     * @author Ilya Fastenko AND Evgeniy Gorelov
     */
    protected function execute_actions($order)
    {
        //проверка входных параметров
        if ( !is_object($order) )
        {
            return false;
        }

        //обработка всех студентов из приказа
        if ( !$this->execute_students($order) )
        {// произошла ошибка во время подписания студентов на дисциплины
            return false;
        }
        // все успешно
        return true;
    }
    
    /**
     * Обработка всех студентов из приказа
     * 
     * @param object $order - объект приказа
     * @return bool true/false
     * @author Ilya Fastenko
     */
    protected function execute_students($order)
    {
        //проверка входных параметров
        if ( !is_object($order) )
        {
            return false;
        }
        
        $this->data->commoncstream = $order->data->commoncstream;
        $this->data->filtercode = $order->data->filtercode;
        
        //получаем массив студентов
        $students = $order->data->cur_students;
        
        $success = true;
        //перебираем студентов
        foreach($students as $psbcid=>$student)
        {
            //выполняем обработку одного студента
            $success = ( $this->execute_student_one($psbcid,$student) AND $success );
        }
        
        return $success;
    }
    
    /**
     * Обработка одного студента
     * 
     * @param int $psbcid - id подписки на программу
     * @param object $student - объект студента
     * @return bool true/false
     * @author Ilya fastenko AND Evgeniy Gorelov
     */
    protected function execute_student_one($psbcid,$student)
    {
        //проверка входных параметров
        if ( !is_object($student) OR !is_int_string($psbcid) )
        {
            return false;
        }
        
        $success = true;
        //перебираем дисциплины на подтверждение
        foreach($student->itemsbcs as $pritemid=>$pritem)
        {
            // сохраним id дисциплины в объект, чтобы передачать меньше параметров
            $pritem->id = $pritemid;
            //создадим подписку на дисциплину
            $success = ( $this->execute_student_itemsbc($psbcid, $pritem)
                    AND $success );
        }
        
        return $success;
    }
    
    /**
     * Подписывает студента на дисциплину
     * 
     * @param int $psbcid id подписки на программу
     * @param int $pritem объект с информацией о дисциплине из массива студентов
     * @return bool true/false
     * @author Ilya Fastenko AND Evgeniy Gorelov
     */
    protected function execute_student_itemsbc($psbcid, $pritem)
    {
        //проверка входных параметров
        if ( ! is_int_string($psbcid) OR ! is_object($pritem) ) 
        {
            return false;
        }
        
        // обработаем возможные значения для преподавателя
        switch($pritem->appointmentid)
        {
            case '-1':
                // выбрать преподавателя автоматически
                //TODO
                echo'Пока не реализовано -1 <br>';
                break;
            case '0':
                // не указывать преподавателя
                $pritem->appointmentid = '';
                break;
        }
        
        //если общий cstream
        if ( $this->data->commoncstream )
        {
            // обработаем возможные значения для учебного процесса
            switch($pritem->cstreamid)
            {
                case '-2':
                    //создаем cstream
                    $pritem->cstreamid = $this->cur_create_cstream_for_programmitem($pritem);
                    break;
                case '-1':
                    // выбрать автоматически
                    //TODO
                    echo'Пока не реализовано -1 <br>';
                    break;
                case '0':
                    // не указывать
                    //TODO
                    echo'Пока не реализовано 0 <br>';
                    break;
            }
        }
        else
        {
            //TODO
            echo'Пока не реализовано false <br>';
        }
        
        //подписываем студента на учебный процесс
        if ( !$cpassedid = $this->dof->storage('cpassed')->sign_student_on_cstream(
                    $pritem->cstreamid, $psbcid, $this->id) )
        {
            return false;
        }

        return true;
    }
    
    /**
     * Создает cstream для дисциплины и возвращает его id. 
     * Если в рамках этой заявки для этой дисциплины уже создан cstream,
     * то просто вернет его id.
     * 
     * TODO возможно этому методу здесь не место. Возможно его необходимо поместить
     * в один из справочников
     * 
     * @param int $pritem объект с информацией о дисциплине из массива студентов
     * @return int $cstreamid id учебного процесса
     * @author Ilya Fastenko AND Evgeniy Gorelov
     */
    protected function cur_create_cstream_for_programmitem($pritem)
    {
        //проверка входных параметров
        if (!is_object($pritem))
        {
            return false;
        }
        
        //ищем в массиве id дисциплин - id дисциплины
        if ($cstreamid = array_search($pritem->id,$this->itemsbcids))
        {
            //нашли - возвращаем ее ключ(cstreamid)
            return $cstreamid;
        }
        //если в массиве id дисциплин для которых необходимо создать cstream еще нет дисциплины с таким id
        else
        {//создаем cstream, возвращаем cstreamid
            $filters = $this->cur_get_filters($this->data->filtercode);
            
            $cstream = new stdClass();
            $cstream->ageid = $pritem->ageid;
            $cstream->programmitemid = $pritem->id;
            $cstream->teacherid = $pritem->appointmentid;
            $cstream->departmentid = $pritem->departmentid;
            $cstream->mdlgroup = null;
            $cstream->eduweeks = $this->dof->storage('ages')->get_field($pritem->ageid,
                    'eduweeks');
            $cstream->begindate = time();
            $maxduration = $this->dof->storage('programmitems')->get_field(
                    $pritem->id,'maxduration');
            $cstream->enddate = $cstream->begindate + $maxduration;
            
            $cstreamid = $this->dof->storage('cstreams')->insert($cstream);
            //меняем статус потока
            $this->dof->workflow('cstreams')->change($cstreamid,'active');
            
            //помещаем id дисциплины в массив, в качестве ключа будем использовать cstreamid
            $this->itemsbcids[$cstreamid] = $pritem->id;
            
            //возвращаем cstreamid
            return $cstreamid;
        }
    }
    
    //TODO delete_itemsbc($programmsbcid,$programmitemid) - удалить подписку
    
    protected function cur_list_itemsbcs($programmsbcid)
    {
        
    }
    
    // TODO set_global_sbc() - Алексей хотел добавить этот метод
    // TODO get_global_sbc() - Алексей хотел добавить этот метод

    // **********************************************
    //       ---=== Собственные методы ===---
    // **********************************************
    
    // ---=== Публичные методы ===---
    
    /**
     * Удалить из приказа дисциплины переданного студента
     * 
     * @param int $sbcid id подписки на программу того студента, 
     *      дисциплины которого следует удалить
     * @return bool true - успех
     * @author Evgeniy Gorelov
     */
    public function cur_remove_prits_sbc($sbcid)
    {
        if ( ! is_int_string($sbcid) )
        {
            return false;
        }
        
        $this->data->cur_students[$sbcid]->itemsbcs = array();
        
        return true;
    }
    
    /**
     * Сохранить приказ в БД
     * @return bool true/false
     * @author Ilya Fastenko
     */
    public function cur_save()
    {
        global $addvars;
        
        $orderobj = new stdClass();
        
        if ( ! $personid = $this->dof->storage('persons')->get_by_moodleid_id() )
        {// если id персоны не найден 
    		return false;
    	}
        //сохраняем автора приказа
        $orderobj->ownerid = $personid;
        
        if ( isset($addvars['departmentid']) AND $addvars['departmentid'] )
        {// установим выбранное на странице id подразделения 
            $orderobj->departmentid = $addvars['departmentid'];
        }
        else if ( !empty($this->departmentid) )
        {//установим из этого объекта
            $orderobj->departmentid = $this->departmentid;
        }
        else
        {// установим id подразделения из сведений о том кто формирует приказ
            $orderobj->departmentid = $this->dof->storage('persons')->get_field($personid,'departmentid');
        }
        
        //id приказа
        $orderobj->id = $this->id;
        // подразделение приказа
        $orderobj->departmentid = $this->departmentid;
        //данные приказа
        $orderobj->data = $this->data;
        //дата создания приказа
        $orderobj->date = time();
        
        //если удалось сохранить
        if ( $this->save($orderobj) )
        {
            //если не пуст id сессии
            if (!empty($this->idtmp))
            {//сохраняем в сессию
                $this->cur_save_session($this->idtmp);
                //приказ отчет сохранять в сессию не нужно, он формируется по крону
            }
            return true;
        }
        
        return false;
    }
    
    /**
     * Загрузить все данные приказа из БД
     * 
     * @param $id id приказа, данные которого хотим загрузить
     * @return object - объект приказа | bool - false
     * @author Evgeniy Gorelov
     */
    public function cur_load($id)
    {
        if (  !($orderdata = $this->load($id)) )
        {
            return false;
        }
        
        // сохраняем данные приказа в объект
        // не используемые пока поля закомментированы
        $this->id            = $orderdata->id;
        $this->data          = $orderdata->data;
        $this->departmentid  = $orderdata->departmentid;
        
        //эти данные записывает в объект $this->load
        //они нам вроде не нужны. Можно вместо unset переопределить load здесь
        unset($this->data->_departmentid);
        unset($this->data->_ownerid);
        unset($this->data->_signerid);
        unset($this->data->_date);
        unset($this->data->_signdate);
        unset($this->data->_exdate);
        unset($this->data->_changedate);
        
        return true;
    }
    
    // ---=== Защищенные методы ===---
    

}


?>