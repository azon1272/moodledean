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
// Copyright (C) 2011-2999  Evgeniy Gorelov (Евгений Горелов)             //
// Copyright (C) 2011-2999  Evgeniy Yaroslavtsev (Евгений Ярославцев)     //
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

/** Класс стандартных функций интерфейса
 * 
 */
class dof_storage_pridepends extends dof_storage
{
    /**
     * @var object dof_control - объект с методами ядра деканата
     */
    protected $dof;
    // массив критериев записи на дисциплину, берется из конфига
    protected $criteriescfg;
    // **********************************************
    // Методы, предусмотренные интерфейсом plugin
    // **********************************************

    /** 
     * Метод, реализующий обновление плагина в системе
     * Создает или модифицирует существующие таблицы в БД
     * @param string $old_version - версия установленного в системе плагина
     * @return boolean
     * @access public
     */
    public function upgrade($oldversion)
    {
        //подключаем конфиг мудла
        global $CFG;
        //методы для установки таблиц из xml
        require_once($CFG->libdir.'/ddllib.php');
        
        return true;// уже установлена самая свежая версия
    }
    /** 
     * Возвращает версию установленного плагина
     * @return string
     * @access public
     */
    public function version()
    {
        // Версия плагина (используется при определении обновления)
        return 2012042500;
    }
    /**
     * Возвращает версии интерфейса Деканата, 
     * с которыми этот плагин может работать
     * @return string
     * @access public
     */
    public function compat_dof()
    {
        return 'aquarium';
    }
    /** 
     * Возвращает версии стандарта плагина этого типа, 
     * которым этот плагин соответствует
     * @return string
     * @access public
     */
    public function compat()
    {
        return 'paradusefish';
    }
    /** 
     * Возвращает тип плагина
     * @return string 
     * @access public
     */
    public function type()
    {
        return 'storage';
    }
    /** 
     * Возвращает короткое имя плагина
     * Оно должно быть уникально среди плагинов этого типа
     * @return string
     * @access public
     */
    public function code()
    {
        return 'pridepends';
    }
    /** 
     * Возвращает список плагинов, 
     * без которых этот плагин работать не может
     * @return array
     * @access public
     */
    public function need_plugins()
    {
        return array('storage'=>array('programmitems'=>2011020800));
    }
    /** 
     * Список обрабатываемых плагином событий 
     * @return array - array(array('plugintype'=>..,'plugincode'=>..,'eventcode'=>..),...)
     * @access public
     */
    public function list_catch_events()
    {
        // Пока событий не обрабатываем
        return array();
    }
    /** 
     * Требуется ли запуск cron в плагине
     * @return bool
     * @access public
     */
    public function is_cron()
    {
        // Просим запускать крон не чаще раза в 15 минут
        return false;
    }
    
    /** 
     * Проверяет полномочия на совершение действий
     * @param string $do - идентификатор действия, которое должно быть совершено
     * @param int $objid - идентификатор экземпляра объекта, 
     * по отношению к которому это действие должно быть применено
     * @param int $userid - идентификатор пользователя, полномочия которого проверяются
     * @return bool true - можно выполнить указанное действие по отношению к выбранному объекту
     * false - доступ запрещен
     * @access public
     */
    public function is_access($do, $objid = NULL, $userid = NULL)
    {
        // Используем функционал из $DOFFICE
        return $this->dof->is_access($do, NULL, $userid);
    }
    /** 
     * Обработать событие
     * @param string $gentype - тип модуля, сгенерировавшего событие
     * @param string $gencode - код модуля, сгенерировавшего событие
     * @param string $eventcode - код задания
     * @param int $intvar - дополнительный параметр 
     * @param mixed $mixedvar - дополнительные параметры
     * @return bool - true в случае выполнения без ошибок
     * @access public
     */
    public function catch_event($gentype,$gencode,$eventcode,$intvar,$mixedvar)
    {
        // Ничего не делаем, но отчитаемся об "успехе"
        return true;
    }
    /** 
     * Запустить обработку периодических процессов
     * @param int $loan - нагрузка (1 - только срочные, 2 - нормальный режим, 3 - ресурсоемкие операции)
     * @param int $messages - количество отображаемых сообщений (0 - не выводить,1 - статистика,
     *  2 - индикатор, 3 - детальная диагностика)
     * @return bool - true в случае выполнения без ошибок
     * @access public
     */
    public function cron($loan,$messages)
    {
        // Определяем текущую нагрузку системы
        if ($loan >= 3)
        {
            // Выполняем задание, только если система свободна
            // Создаем тестовую запись
            $obj = new stdClass();
            $obj->code = 'a'.date('mdhis');
            $obj->name = 'Автоматически созданный объект '.date('ymdhis');
            $id = $this->insert($obj);
            // Выводим детальное (код 3) диагностическое сообщение о ходе обработки
            // Будет отображено только если $messages >= 3 
            dof_mtrace(3,"\nДобавлена запись [{$id}] {$obj->code}","",0,$messages);
            // Удаляем записи, старше недели и выводим сообщения, в зависимости от $messages
            $this->purge(time()-3600*24*7,$messages);
        }
        return true;
    }
    /** 
     * Обработать задание, отложенное ранее в связи с его длительностью
     * @param string $code - код задания
     * @param int $intvar - дополнительный параметр 
     * @param mixed $mixedvar - дополнительные параметры
     * @return bool - true в случае выполнения без ошибок
     * @access public
     */
    public function todo($code,$intvar,$mixedvar)
    {
        if ($code === 'purge')
        {// Нас попросили провести "очистку"
            return $this->purge($intvar,3);
        }
        return true;
    }
    /** 
     * Конструктор
     * @param dof_control $dof - объект с методами ядра деканата
     * @access public
     */
    public function __construct($dof)
    {
        // Сохраняем ссылку на DOF, чтоб вызывать его через $this->dof
        $this->dof = $dof;
    }

    /** 
     * Возвращает название таблицы без префикса (mdl_)
     * с которой работает examplest
     * @return text
     * @access public
     */
    public function tablename()
    {
        // Имя таблицы, с которой работаем
        return 'block_dof_s_pridepends';
    }

    // TODO если не понадобится, то удалить
    /** 
     * Удалить все записи, старше указанного времени 
     * @param int $old - время устаревания объектов
     * @param int $messages - количество отображаемых сообщений (0 - не выводить,1 - статистика,
     *  2 - индикатор, 3 - детальная диагностика) 
     * @return mixed bool false если операция не удалась или id вставленной записи
     * @access public
     */
    public function purge($old,$messages)
    {
        $old = intval($old);

        // Удаляем устаревшие записи старше недели
        $count = 0;
        // Записи к удалению
        $list = $this->get_records_select("adddate< {$old}");
        if (!$list){$list = array();}
        // Выводим диагностику
        dof_mtrace(1,"\nК удалению ".count($list)." записей старше {$old}","\n",0,$messages);
        foreach ($list as $rec)
        {
            // Удаляем лишнюю запись
            if ($this->delete($rec->id))
            {//удаление прошло успешно - сообщаем
                dof_mtrace(2,".","",0,$messages);
                dof_mtrace(3,"Удаляем запись [{$rec->id}] от {$rec->adddate} - {$rec->code}","\n",0,$messages);
            }else
            {//сообщаем о неудаче удаления
                dof_mtrace(1,"Ошибка удаления записи  [{$rec->id}]");
            }
        }
        return true;
    }
    
    // **********************************************
    //              Собственные методы
    // **********************************************
    
    /**
     * Вернуть массив с настройками или одну переменную
     * 
     * @param string $key - название искомого параметра
     * @return mixed
     * @author Evgeniy Gorelov
     */
    public function get_cfg($key=null)
    {
        if (! isset($this->criteriescfg) || empty($this->criteriescfg))
        {
            if ( file_exists($cfgfile = $this->dof->plugin_path($this->type(),$this->code(),'/cfg/criteries.php')) )
            {
                include ($cfgfile);
                $this->criteriescfg = $criteriescfg;
            }else
            {
                return null;
            }
        }
        
        if (empty($key))
        {
            return $this->criteriescfg;
        }else
        {
            return (@$this->criteriescfg[$key]);
        }
    }
    
    
    /**
     * Метод возвращает дисциплины, которые нужно пройти (т.е. еще не пройденные)
     * для записи на переданную параметром дисциплину.
     * 
     * @param int $targetpritemid id дисциплины
     * @param int $psbcid id подписки на программу сотрудника
     * @return array Массив объектов с полями: тип зависимости (поле из таблицы), 
     * текст ошибки, параметр невыполненного предусловия.
     * @author Evgeniy Gorelov
     */
    public function check_pridepends( $psbcid, $targetpritemid )
    {
        /**
         * +взять список по $targetpritemid
         * +для каждой записи вызвать метод, код которого указан в поле type в таблице зависимостей
         * +этот метод смотрит пройдена ли дисц, если да, то вернет true, иначе false
         * +Возвращаемые значения
         * * Массив объектов с полями: тип зависимости (поле из таблицы), текст ошибки, id невыполненной дисциплины-предусловия.
         */
        $res = array();
        
        // взять список по $targetpritemid
        $list = $this->get_records(array('programmitemid'=>$targetpritemid));
        if ( !$list )
        {
            return array();
        }
        
        // для каждой записи вызвать метод, код которого указан в поле type в таблице зависимостей
        foreach($list as $depend)
        {
            if (! method_exists($this, $depend->type) )
            {
                $this->dof->print_error('not_exist_method','',$depend->type,$this->type(),$this->code());
                continue;
            }
            // название функции, которая будет проверять зависисмости
            $methodname = $this->dof->storage('pridepends')->get_cfg($depend->type);
            if (! $this->$methodname($targetpritemid, $psbcid, $depend->value) )
            {
                $errparam = '';
                // Сформируем параметр для сообщения о невыполнении предусловия
                if ( 'requirepritem' == $depend->type)
                {
                    // получим название дисциплины, по которой не хватает зачета
                    if (! $programmitem = $this->dof->storage('programmitems')->get($depend->value) )
                    {
                        $this->dof->print_error('not_found_dpit','',$depend->value,$this->type(),$this->code());
                    }
                    $errparam = $programmitem->name;
                }
                else
                {
                    $errparam = $depend->value;
                }
                
                // возвращаемое сообщение об ошибке будет соответствовать типу невыполненного предусловия
                $errcode = $depend->type . '_err';
                
                // Массив объектов с полями: тип зависимости (поле из таблицы), 
                // текст ошибки, id невыполненной дисциплины-предусловия
                $ertext = $this->dof->get_string($errcode, $this->code(), $errparam, $this->type());
                $err = new stdClass();
                $err->type = $depend->type;
                $err->err = $ertext;
                $err->dependdisc = $depend->value;
                $res[] = $err;
            }
        }
        
        return $res;
    }
    
    /**
     * Метод возвращает массив зависимостей для дисциплины (массив из id дисциплин)
     * 
     * @param int $targetpritemid id дисциплины
     * @return array Массив объектов с полями: тип зависимости (поле из таблицы), 
     * текст ошибки, параметр предусловия.
     * @author Evgeniy Gorelov
     */
    public function get_list_by_id( $id )
    {
        $list = $this->get_records(array('programmitemid'=>$id),'value');
        if ( !$list )
        {
            return array();
        }
        
        return $list;
    }
    
    /**
     * Составляет массив с данных о дисциплинах, которые можно выбрать в качестве зависимостей
     * для дисцивлины с $id
     * 
     * array('id дисциплины-зависимости'=>'название дисциплины зависимости')
     * 
     * @param int $id дисциплины, для которой выводим список
     * @return array array('id дисциплины-зависимости'=>'название дисциплины зависимости')
     * @author Evgeniy Gorelov 
     */
    public function get_list_depends_select( $id )
    {
        /**
         * +взять список зависимостей
         * +взять список дисциплин той же программы
         * +удалить оттуда целевую
         * +удалить зависимости
         * +вернуть остатки
         */
        $arres = array();
        
        if (! $targetdisc = $this->dof->storage('programmitems')->get($id))
        {
            $this->dof->print_error('not_found_pit','',$id,$this->type(),$this->code());
        }
        
        // взять список зависимостей
        $fields = 'value';
        $listdepobjs = $this->get_records(array('programmitemid'=>$id), 'id', $fields);
        // строим новый массив из конкретного свойства объектов из массива
        $listdepstrs = $this->make_as_by_ao($listdepobjs, $fields);
        unset($listdepobjs);
        
        // взять список дисциплин той же программы
        $fields = 'id, name';
        if (! $listdiscs = $this->dof->storage('programmitems')->get_records(array('programmid'=>$targetdisc->programmid),
                'id', $fields) )
        {
            $this->dof->print_error('not_found_pits','',$id,$this->type(),$this->code());
        }
        
        foreach($listdiscs as $key=>$d)
        {
            // удалить зависимости
            if ( in_array($d->id, $listdepstrs) )
            {
                unset($listdiscs[$key]);
                continue;
            }
            // удалить оттуда целевую
            if ( $d->id == $id )
            {
                unset($listdiscs[$key]);
                continue;
            }
            
            // Если условия не сработали, то данные - нужные
            // сохраняем остатки в виде array(id=>name)
            $arres[$d->id] = $d->name;
        }
        
        // вернуть остатки
        return $arres;
    }
    
    /**
     * Сделать массив строк из массива объектов, но переводить не целиком обЪект, а только его 
     * конкретное свойство.
     * 
     * Из массива объектов, берет свойство $fn каждого объекта и кладет в новый массив
     *  
     * @param array $aos array objects
     * @param string $fn field name
     * @return string $str массив строк = свойствам $fn всех объектов из массива $aos 
     * @author Evgeniy Gorelov
     */
    public function make_as_by_ao( $aos, $fn )
    {
        $arres = array();
        if ( !is_array($aos) OR '' == $fn )
        {
            return $arres;
        }
        
        foreach($aos as $ao)
        {
            // косвенная ссылка,т.е. в переменной лежит название переменной
            $arres[] = $ao->$fn;
        }
        
        return $arres;
    }
    
    /**
     * Функция предусловия. Базовая. Проверяет, получен ли студентом зачет по данной дисциплине
     * 
     * @param int $targetpritid id целевой дисциплины (для универсальности интерфейса)
     * @param int $psbcid id подписки на программу
     * @param int $dependitid id дисциплины, необходимой для подписания на целевую дисциплину
     * @return bool true(успех)|false(неудача)
     * @author Evgeniy Gorelov
     */
    public function requirepritem( $targetpritid, $psbcid, $dependpritid )
    {
        // 'успешно завершен', 'перезачет'
        $statuses = array('completed', 'reoffset');
        // ищем успешно завершенную(или презечтенную) подписку на дисциплину
        $pitemids = $this->dof->storage('cpassed')->get_records(array('programmsbcid'=>
                $psbcid,'programmitemid'=>$dependpritid,'status'=>$statuses),'id', 'id');
        // если не нашли, значит нет зачета
        if ( !$pitemids )
        {
            return false;
        }
        // если нашли, значит есть зачет
        else
        {
            return true;
        }        
    }
    
}
?>