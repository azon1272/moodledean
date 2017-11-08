<?php
////////////////////////////////////////////////////////////////////////////
//                                                                        //
// NOTICE OF COPYRIGHT                                                    //
//                                                                        //
// Dean`s Office for Moodle                                               //
// Электронный деканат                                                    //
// <http://www.deansoffice.ru/>                                           //
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
 * Базовый класс для объявления класса тега
 */
abstract class dof_storage_tags_tags
{
    /**
     * @var dof_control - Ссылка на объект $DOF
     */
    protected $dof;
    
    /**
     * Экземпляр класса
     */
    protected static $_instance;
    
    
    /**
     * Закрываем доступ к функции вне класса.
     */
    private function __clone()
    {
    }
    
    /** 
     * Конструктор
     * 
     * @param dof_control $dof - объект с методами ядра деканата
     * @param object $options - дополнительные опции
     */
    protected function __construct(dof_control $dof, $options)
    {
        // Сохраняем ссылку на DOF, чтоб вызывать его через $this->dof
        $this->dof = $dof;
    }
    
    /**
     * Возвращаем экземпляр класса
     * 
     * @param dof_control $dof
     * @param object $options - дополнительные опции
     * 
     * @return dof_storage_tags_tags_select
     */
    static function getInstance(dof_control $dof, $options)
    {
        // проверяем актуальность экземпляра
        if (null === self::$_instance) {
            // создаем новый экземпляр
            self::$_instance = new self($dof, $options);
        }
        // возвращаем кземпляр
        return self::$_instance;
    }
    
    /**
     * Возвращает имя класса
     * 
     * @return string
     */
    static function get_tag_class()
    {
        return 'tags';
    }
    
    /**
     * Поддержка классом ручной отлинковки объектов
     * 
     * @return boolean - true/false - Да/Нет
     */
    static function is_manual_unlink()
    {
        return false;
    }
    
    /**
     * Возвращает массив параметров тега, 
     * которые заполняются пользователем при создании тега класса
     * 
     * Массив представляет собой набор параметров для формирования полей в форме
     *  Формат:
     *      array('имя опции' => array('параметры поля'))
     *  Поддерживаемые параметры:
     *      'type' - тип поля в форме
     *      'name' - имя поля в форме
     *      'label' - название поля.  
     *      'setDefault' - значение по-умолчанию 
     *      'setType' - тип поля ( PARAM_TEXT, ... )
     *      'addRule' - правило ( required )
     *      'addRuleValidation' - валидация ( client, server )
     *       
     * @return array - объект с параметрами класса 
     */
    public function get_tagoptions()
    {
        return NULL;
    }
    
    /**
     * Проверяет опции тега на корректность и возвращает 
     * готовый объект для добавления в БД 
     * 
     * @param string $code - код тега
     * @param string $department - подразделение тега
     * @param string $alias - алиас тега
     * @param string $about - описание тега
     * @param int $parentid - ID родительского тега
     * @param int $ownerid - ID владельца тега
     * @param int $cron - требуется ли запуск крона и когда
     * @param int $cronrepeate - период устаревания тега
     * @param object $options - опции тега
     * 
     * @return Object - объект для добавления в БД
     */
    public function check_tag($code, $department, $alias='', $about='', $parentid = 0, $ownerid = 0, $cron = -1, $cronrepeate = 0, $options = NULL)
    {
        // Готовим объект для вставки
        $result = new stdClass();
     
        // Готовим поле об ошибке
        $result->errorstatus = false;
        
        // Класс тега
        $result->class = self::get_tag_class();
        
        // Добавляем parentid
        $result->parentid = $parentid;
        
        // Добавляем ownerid
        $result->ownerid = $ownerid;
        
        // Добавляем опции тега. абстрактный класс не может иметь опций
        $result->options = NULL;
        
        // Добавляем поддержку крона
        $result->cron = $cron;
        
        // Добавляем опции крона
        $result->cronrepeatе = $cronrepeatе;
        
        // Добавляем описание
        $result->about = $about;
        
        // Добавляем код тега
        $result->code = $code;
        
        // Добавляем алиас
        $result->alias = $alias;  
        
        return $result;
    }
    
   /**
    * Метод маппинга объекта к тегу
    * 
    * Проверяет, подходит ли объект по параметрам
    * Если да - возвращает объект, готовый для добавления в БД линковок
    * Если нет - возвращает NULL
    *
    * @param object $tagobject - объект тега из БД, к которому будет линковаться объект
    * @param string $plugintype - тип плагина, объект которого линкуется к тегу
    * @param string $plugincode - код плагина, объект которого линкуется к тегу
    * @param int $objectid - ID объекта, который линкуется к тегу
    * @param int $department - ID подразделения 
    * @param object $object
    * @param array $manualoptions - Вручную заданные опции линковки 
    * 
    * @return NULL|object - Объект линковки
    */
    public function map($tagobject, $plugintype, $plugincode, $objectid, $department, $object=NULL, $manualoptions=NULL)
    {
        return NULL;
    }
    
   /**
    * Сформировать массив объектов для перелинковки
    * 
    * @param object $tagobject - объект тега из БД
    * @param int $department - код подразделения
    * @param int $limit - лимит формирования
    * @param object $continue - объект для продолжения формирования списка
    * 
    * @return NULL|array - список объектов справочника в формате
    *         $result->list[$plugintype][$plugincode][$objectid] = (object)
    */
    public function get_rescanobjects_list($tagobject, $department = 0, $limit = 0, $continue = NULL)
    {
        return NULL;
    }
    
    /**
     * Отображение нестандартной информации о теге
     * 
     * @param object $tagobject - объект тега из БД
     * 
     * @return string
     */
    public function show_tag($tagobject, $addvars = NULL) 
    {
        return '';
    }
    
    /**
     * Показать информацию о линке
     * 
     * @param object $tagobject - объект тега из БД
     * @param object $taglinkobject - объект линковки из БД
     * 
     * @return string
     */
    public function show_taglink($tagobject, $taglinkobject, $addvars = NULL)
    {
        return '';
    }
    
    /**
     * Показать список линков
     * 
     * @param object $tagobject - объект тега из БД
     * @param object $list - массив линковок из БД
     * @param array $addvars - массив GET параметров
     * 
     * @return string
     */
    public function show_taglinks_list($tagobject, $list, $addvars = NULL)
    {
        return '';
    }    
    
    /**
     * Рекурсивный метод формирования html-кода параметров объекта
     *
     * Предназначен для вывода html кода параметров тега
     * в удобночитаемом виде
     *
     * @param object $options - параметры тега
     * 
     * @return $html - список критериев
     */
    protected function options_to_html($options = NULL)
    {
        // Если опции пусты
        if ( empty($options) )
        {
            return '';
        }
        
        // Начинаем формировать опции этой ноды
        $html = html_writer::start_tag('ul');
        
        foreach ($options as $key => $value)
        {
            if ( is_array($value) )
            {// Элемент является массивом
                $html .= html_writer::tag('li', $key.': '.$this->options_to_html($value));
            } else
            {// Элемент - не массив
                $html .= html_writer::tag('li', $key.': '.$value);
            }
        }
        // Заканчиваем формировать опции этой ноды
        $html .= html_writer::end_tag('ul');
    
        return $html;
    }
}
