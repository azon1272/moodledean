<?php
////////////////////////////////////////////////////////////////////////////
//                                                                        //
// NOTICE OF COPYRIGHT                                                    //
//                                                                        //
// Dean`s Office for Moodle                                               //
// Электронный деканат                                                    //
// <http://sourceforge.net/projects/freedeansoffice/>                     //
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


/** Справочник тематических рахделов
 * 
 */
class dof_storage_planinh extends dof_storage
{
    /**
     * @var dof_control
     */
    protected $dof;
    // **********************************************
    // Методы, предусмотренные интерфейсом plugin
    // **********************************************

    /** Метод, реализующий обновление плагина в системе
     * Создает или модифицирует существующие таблицы в БД
     * @param string $old_version - версия установленного в системе плагина
     * @return boolean
     * @access public
     */
    public function upgrade($oldversion)
    {
        global $CFG;
        $result = true;
        //методы для установки таблиц из xml
        require_once($CFG->libdir.'/ddllib.php');

        return $result;
    }
    /** Возвращает версию установленного плагина
     * @return string
     * @access public
     */
    public function version()
    {
        // Версия плагина (используется при определении обновления)
		return 2012042500;
    }
    /** Возвращает версии интерфейса Деканата, 
     * с которыми этот плагин может работать
     * @return string
     * @access public
     */
    public function compat_dof()
    {
        return 'aquarium';
    }

    /** Возвращает версии стандарта плагина этого типа, 
     * которым этот плагин соответствует
     * @return string
     * @access public
     */
    public function compat()
    {
        return 'paradusefish';
    }
    
    /** Возвращает тип плагина
     * @return string 
     * @access public
     */
    public function type()
    {
        return 'storage';
    }
    /** Возвращает короткое имя плагина
     * Оно должно быть уникально среди плагинов этого типа
     * @return string
     * @access public
     */
    public function code()
    {
        return 'planinh';
    }
    /** Возвращает список плагинов, 
     * без которых этот плагин работать не может
     * @return array
     * @access public
     */
    public function need_plugins()
    {
		return array();
    }
    /** Список обрабатываемых плагином событий 
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
        // Просим запускать крон не чаще раза в 15 минут
        return false;
    }
    
    /** Проверяет полномочия на совершение действий
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
    /** Обработать событие
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
    /** Запустить обработку периодических процессов
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
    /** Обработать задание, отложенное ранее в связи с его длительностью
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
    /** Конструктор
     * @param dof_control $dof - объект с методами ядра деканата
     * @access public
     */
    public function __construct($dof)
    {
        // Сохраняем ссылку на DOF, чтоб вызывать его через $this->dof
        $this->dof = $dof;
    }

    /** Возвращает название таблицы без префикса (mdl_)
     * @return text
     * @access public
     */
    public function tablename()
    {
        // Имя таблицы, с которой работаем
        return 'block_dof_s_planinh';
    }

    // **********************************************
    //              Собственные методы
    // **********************************************
    

    
    /** Возвращает массив id родитедьсих/дочерних связей
     * @param int $id - id, по которому производиться поиск
     * @param string $edit - поле, по которому производиться поиск
     * @return array массив id
     * @access public
     */
    public function get_list_id($edit, $id)
    {// определим массив резудбтата
        $masrez = array();
        // массив полей таблицы planinh
        $mas = array('planid', 'inhplanid');
        // проверка на правильность заполнения флага
        if ( ! in_array($edit, $mas) )
        {
            return array();
        }
        // создаем запись результата
        if ( ! $rec = $this->get_records(array($edit=>$id)) )
        {
            return array();
        }
        // определяем поле, которое будем выводить
        if ( $edit == 'planid')
        {
           $edit = 'inhplanid'; 
        }elseif ( $edit == 'inhplanid')
        {
            $edit = 'planid';
        }
        foreach ( $rec as $val)
        {
            $masrez[] = $val->$edit;
        }
        return $masrez;
    }
    
    /** Обновить данные о наследовании контрольной точки
     * @todo сделать проверку: удалять и создавать заново записи только в том случае, если данные не изменились
     * 
     * @return bool
     * @param int $pointid - id контрольной точки в таблице plans
     * @param array $parentids - массив id контрольных отчек от которых наследуется переданная контрольная точка
     */
    public function upgrade_point_links($pointid, $parentids)
    {
        $result = true;
        if ( ! is_array($parentids) OR ! $pointid )
        {// неправильный тип данных - это ошибка
            // @todo использовать exception для таких случаев
            return false;
        }
        
        if ( $links = $this->get_records(array('inhplanid'=>$pointid)) )
        {// получаем все связи это контрольной точки с другими точками
            foreach ( $links as $link )
            {// свзязи есть - удаляем их все
                $result = $result & (bool)$this->delete($link->id);
            }
        }
        if ( empty($parentids) )
        {// если передан пустой массив родительских контрольных точек -
            // то это значит, что новые связи создавать не надо, надо просто удалить старые
            return $result;
        }
        
        // если перечислены id родительских контрольных точек - то удалим только старые и запишем новые
        foreach ( $parentids as $parentid )
        {// после удаления всех старых связей - создаем новые
            if ( $this->is_exists(array('planid'=>$parentid, 'inhplanid'=>$pointid)) )
            {// такая точка уже существует - не будем создавать новую
                continue;
            }
            $obj = new stdClass();
            $obj->planid    = $parentid;
            $obj->inhplanid = $pointid;
            
            $result = $result & (bool)$this->insert($obj);
        }
        return $result;
    }
    
    /** Создать записи о наследовании для контрольной точки
     * 
     * @return bool
     * @param int $pointid - id контрольной точки в таблице plans
     * @param array $parentids - массив id контрольных отчек от которых 
     *                               наследуется переданная контрольная точка
     */
    public function create_point_links($pointid, $parentids)
    {
        $result = true;
        if ( ! is_array($parentids) OR ! $pointid )
        {// неправильный тип данных - это ошибка
            // @todo использовать exception для таких случаев
            return false;
        }
        
        if ( empty($parentids) )
        {// не переданы id контрольных точек - ничего не нужно создавать, нормальная ситуация
            return true;
        }
        foreach ( $parentids as $parentid )
        {// для каждой темы устанавливаем связь
            if ( $this->is_exists(array('planid'=>$parentid, 'inhplanid'=>$pointid)) )
            {// такая точка уже существует - не будем создавать новую
                continue;
            }
            $obj = new stdClass();
            $obj->planid    = $parentid;
            $obj->inhplanid = $pointid;
            $result = $result & (bool)$this->insert($obj);
        }
        return $result;
    }
}
?>