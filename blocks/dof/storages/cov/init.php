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

/**
 * COV - custom options values.
 * Справочник, позволяющий хранить дополнительные опции для всех справочников.
 * @author Ilya Fastenko 2014. Opentechnology Ltd.
 */
class dof_storage_cov extends dof_storage
{
    /**
     * @var object dof_control - объект с методами ядра деканата
     */
    protected $dof;
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
        return 2014032000;
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
        return 'cov';
    }
    /**
     * Возвращает список плагинов,
     * без которых этот плагин работать не может
     * @return array
     * @access public
     */
    public function need_plugins()
    {
        return array();
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
    /** Требуется ли запуск cron в плагине
     * @return bool
     * @access public
     */
    public function is_cron()
    {
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
        return 'block_dof_s_cov';
    }
    
    // **********************************************
    //              Собственные методы
    // **********************************************
    
    /**
     * Метод сохраняет настройку в справочник.
     * Создает запись, если с такими параметрами записи еще нет.
     * Обновляет запись, если она есть с такими параметрами, и переданный $value !== NULL
     * Если запись с переданными параметрами есть, а в качестве $value передали NULL, то запись будет удалена
     * 
     * @param string $plugintype - тип плагина, к объекту которого привязана дополнительная опция
     * (почти всегда - storage)
     * @param string $plugincode - код плагина, к объекту которого привязана дополнительная опция
     * @param numeric $objectid - id объекта, к которому привязывается дополнительная опция
     * @param string $code - код дополнительной переменной (имя поля)
     * @param string $value - значение дополнительной опции
     * @param string $substorage - код подхранилища: не актуально для storage, поэтому почти всегда пустая строка,
     * но в перспективе, может понадобится (аналогично похожему полю в реестре синхронизаций)
     * @return bool
     */
    public function save_option($plugintype, $plugincode, $objectid, $code, $value=null, $substorage=null)
    {
        $conds = array(
            'plugintype' => $plugintype,
            'plugincode' => $plugincode,
            'substorage' => $substorage,
            'objectid' => $objectid,
            'code' => $code,
        );
        //проверяем есть ли уже такая настройка в справочнике
        $cov = $this->dof->storage($this->code())->get_record($conds, 'id');
        
        $dataobject = new stdClass();
        $dataobject->plugintype = $plugintype;
        $dataobject->plugincode = $plugincode;
        $dataobject->substorage = $substorage;
        $dataobject->objectid = $objectid;
        $dataobject->code = $code;
        $dataobject->value = $value;
        
        $success =  true;
        
        //если такая настройка есть
        if ($cov !== false)
        {
            if (!is_null($value))
            {
                $success = $this->dof->storage($this->code())->update($dataobject, $cov->id, true);
            }
            else
            {
                //если передали в качестве значения NULL, то удаляем запись
                $success = $this->dof->storage($this->code())->delete($cov->id, true);
            }
        }
        else
        {
            if (!is_null($value))
            {
                $success = $this->dof->storage($this->code())->insert($dataobject, true);
            }
        }
        unset($dataobject);
        return $success;
    }
    
    /**
     * Метод возвращает одно значение из таблицы настроек по переданным параметрам.
     * 
     * @param string $plugintype - тип плагина, к объекту которого привязана дополнительная опция
     *        (почти всегда - storage)
     * @param string $plugincode - код плагина, к объекту которого привязана дополнительная опция
     * @param int $objectid - id объекта, к которому привязывается дополнительная опция
     * @param string $code - код дополнительной переменной (имя поля)
     * @param string $substorage - код подхранилища: не актуально для storage, поэтому почти всегда пустая строка,
     *        но в перспективе, может понадобится (аналогично похожему полю в реестре синхронизаций)
     * @param array $opt - дополнительные опции
     *        ['emptyreturn'] - значение, которое возвращается при ошибке (mixed NULL по-умолчанию)
     *              
     * @return NULL - если значение не найдено|string значение - если найдено
     */
    public function get_option($plugintype, $plugincode, $objectid, $code, $substorage=null, $opt = array())
    {
        $conds = array(
            'plugintype' => $plugintype,
            'plugincode' => $plugincode,
            'substorage' => $substorage,
            'objectid' => $objectid,
            'code' => $code,
        );
        // Получим значение
        $value = $this->dof->storage($this->code())->get_field($conds, 'value');
        
        // Вернем значение
        if ($value === false)
        {// Значение не найдено
            if ( isset($opt['emptyreturn']) )
            {
                return $opt['emptyreturn'];
            } else
            {
                return NULL;
            }
        } else
        {// Значение найдено
            return $value;
        }
    }
    
    /**
     * Метод получает из справочника опции по переданным параметрам и добавляет их
     * в качестве свойств к переданному объекту $customobj. Если не передали объект $customobj,
     * полученные значения будут присоединены к новому stdClass(). Метод возвращает
     * получившийся $customobj.
     * @param string $plugintype - тип плагина, к объекту которого привязана дополнительная опция
     * (почти всегда - storage)
     * @param string $plugincode - код плагина, к объекту которого привязана дополнительная опция
     * @param numeric $objectid - id объекта, к которому привязывается дополнительная опция
     * @param stdClass $customobj - объект, к которому будут присоединены полученные из справочника опции.
     * @param string $substorage - код подхранилища: не актуально для storage, поэтому почти всегда пустая строка,
     * но в перспективе, может понадобится (аналогично похожему полю в реестре синхронизаций)
     * @return stdClass - переданные в метод объект, дополненный дополнительными опциями из справочника
     */
    public function add_options_to_custom_object($plugintype, $plugincode, $objectid, stdClass $customobj=null, $substorage=null)
    {
        if (is_null($customobj))
        {
            $customobj = new stdClass();
        }
        
        $conds = array(
            'plugintype' => $plugintype,
            'plugincode' => $plugincode,
            'substorage' => $substorage,
            'objectid' => $objectid,
        );
        $options = $this->dof->storage($this->code())->get_records($conds,'','id,code,value');
        
        foreach ($options as $option)
        {
            $customobj->{$option->code} = $option->value;
        }
        
        return $customobj;
    }
}
?>