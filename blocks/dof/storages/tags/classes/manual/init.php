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
 * manual - подкласс тега для ручной прилинковки объекта
 * 
 * Опции класса
 *   Нет
 *                         
 */
class dof_storage_tags_tags_manual extends dof_storage_tags_tags 
{
    /**
     * Возвращает имя класса
     * 
     * @return string - имя класса тега
     */
    static function get_tag_class()
    {
        return 'manual';
    }
    
    /**
     * Поддержка классом ручной отлинковки объектов
     *
     * @return boolean - true/false - Да/Нет
     */
    static function is_manual_unlink()
    {
        return true;
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
        // Формируем опции класса
        $options = array();
        
        // Возвращаем опции класса
        return $options;
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
     * @return object - объект для добавления в БД
     */
    public function check_tag($code, $department, $alias='', $about='', $parentid = 0, $ownerid = 0, $cron = -1, $cronrepeate = 0, $options = NULL)
    {
        // Готовим объект для вставки
        $result = new stdClass();
         
        $result->errorstatus = false;
    
        // Класс тега
        $result->class = self::get_tag_class();
    
        // Добавляем parentid
        $result->parentid = $parentid;
    
        // Добавляем departmentid
        $result->departmentid = $department;
        
        // Добавляем ownerid
        $result->ownerid = $ownerid;
        
        // Добавляем опции тега
        $result->options = NULL;
    
        // Добавляем поддержку крона
        $result->cron = $cron;
    
        // Добавляем опции крона
        $result->cronrepeate = $cronrepeate;
    
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
    * @param int $department - ID подразделения для переопределения значения
    * @param object $object - объект для переопределения линкующегося объекта
    * @param array $manualoptions - Вручную заданные опции линковки 
    * 
    * @return NULL|object - Объект линковки
    */
    public function map($tagobject, $plugintype, $plugincode, $objectid, $department, $object = NULL, $manualoptions = NULL)
    {   
        /* Переопределения значений */
        
        // Получаем объект
        if ( empty($object) )
        {// Объект не переопределен, значит можно брать оригинал из БД
            $object = $this->dof->storage($plugincode)->get($objectid);
        }
        
        if ( empty($object) )
        {// Если не получили объект
            return NULL;
        }
        
        if ( empty($department) )
        {// Подразделение не переопределено, значит пробуем взять из объекта
            
            if ( isset($object->departmentid) )
            {// Подразделение указано в объекте
                $department = $object->departmentid;
            }
        }
        
        /* Формируем линковку */
        
        // Формируем возвращаемый объект линковки
        $taglinkobject = new stdClass();
        $taglinkobject->tagid = $tagobject->id;
        $taglinkobject->plugintype = $plugintype;
        $taglinkobject->plugincode = $plugincode;
        $taglinkobject->objectid = $objectid;
        $taglinkobject->sortname = $plugincode.$objectid;
        $taglinkobject->departmentid = $department;
        $taglinkobject->date = date('U');
        $taglinkobject->infotext = 'Объект хранилища '.$plugincode.' c ID:'.$objectid;
        
        /* Получаем информацию об объекте */
        
        // Формируем данные для широковещательного запроса 
        $mixedvars = array(
            'plugintype' => $plugintype,
            'plugincode' => $plugincode                       
        );
        
        // Кидаем широковещательный запрос для получения информации об объекте
        $result = $this->dof->send_event('storage', 'tags', 'getinfo', $objectid, $mixedvars);
        
        // Формируем объект с информацией
        $infoser = new stdClass();
        // Если запрос вернул результат
        if ( is_array($result) )
        {    
            // Формируем объект с информацией
            foreach ( $result as $key => $item )
            {// Добавляем в объект
                $infoser->$key = $item;  
            }
        }
        // Добавляем опции прилинкованного объекта 
        $taglinkobject->infoserial = serialize($infoser);
        
        // Возвращаем объект линка
        return $taglinkobject; 
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
        
        // Формируем вывод объектов для текущего класса
        $result = new stdClass();
        
        $result->list = array();
        
        // Возвращаем массив
        return $result;
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
        // Получаем параметры тега
        $tagoptions = unserialize($tagobject->options);
        
        // Получаем форматированный список параметров
        $htmloptions = $this->options_to_html($tagoptions);
        
        //  Определяем таблицу
        $table = new stdClass();
        
        // Свойства таблицы
        $table->tablealign = 'left';
        $table->align = array('left');
        
        $table->width = '75%';
        
        $labels = array();
        // Ссылка на редактирование тега
        $labels['label'] = '<b>'.$this->dof->get_string("class_select_tag_options", 'tags', NULL, 'storage').'</b>';
        
        $table->head = $labels;
        
        // Заполняем таблицу
        $table->data = array();
        // Ссылка на дочерний тег
       
        $table->data[] = array($htmloptions);
        
        return $table;
    }

    /**
     * Показать информацию о линке
     * 
     * @param object $tagobject - объект тега из БД
     * @param object $taglinkobject - объект линковки из БД
     * 
     * @return string
     */
    public function show_taglink($tagobject, $taglinkobject, $addvars = NULL )
    {
        //  Определяем таблицу
        $table = new stdClass();

        // Свойства таблицы
        $table->tablealign = 'left';
        $table->align = array('left','left');
        $table->width = '75%';
        $table->size = array('30%','70%');
         
        // Заполняем таблицу
        $table->data = array();
        
        if ( isset($taglinkobject->infoserial->link) )
        {// Если среди опций есть ссылка на объект - покажем ее
            
            $table->data[] = dof_html_writer::link(
                    $taglinkobject->infoserial->link, 
                    $this->dof->get_string('class_select_table_taglink_link', 'tags', NULL, 'storage')
            );
        }
        
        return $table;
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
    public function show_taglinks_list($tagobject, $list, $addvars = NULL )
    {
        //  Определяем таблицу
        $table = new stdClass();
        
        // Свойства таблицы
        $table->tablealign = 'left';
        $table->align = array('left');
        
        $table->width = '75%';
        
        $labels = array();
        
        // Ссылка на редактирование тега
        $labels['label'] = '<b>'.$this->dof->get_string("class_select_tag_taglinks_list", 'tags', NULL, 'storage').'</b>';
        
        $table->head = $labels;
        
        // Заполняем таблицу
        $table->data = array();
        $table->rowclasses = array();
        $depid = optional_param('departmentid', 0, PARAM_INT);
        // Проверяем доступность плагина workflow
        $workflowavailable = $this->dof->plugin_exists('workflow', 'taglinks'); 
        $r = 0;
        foreach ($list as $item)
        {
            // Формируем строку
            $row = array();
            
            // Проверяем доступ к линковке
            if ( $this->dof->storage('taglinks')->is_access('view', $item->id, null, $depid) )
            {
                $addvars['taglinkid'] = $item->id;
                // Ссылка
                $row[] = '<a 
                    href="'.$this->dof->url_im('sycrm','/tags/taglink.php',$addvars).'" 
                    title="'.$this->dof->get_string('go_to_taglink', 'sycrm').'">'.
                            $item->id.
                    '</a>';
            } else 
            {
                $table->rowclasses[$r] = 'disable_taglinkrow';
                $addvars['taglinkid'] = $item->id;
            }
              
            // Ссылка
            $row[] = $item->id;
            // Тип плагина
            $row[] = $item->plugintype;
            // Имя плагина
            $row[] = $this->dof->get_string("title", $item->plugincode, null, $item->plugintype);
            // ID объекта
            $row[] = $item->objectid;
            // Краткая информация
            $row[] = $item->infotext;
            // Статус
            if ( $workflowavailable )
            {
                $row[] = $this->dof->workflow('taglinks')->get_name($item->status);
            } else
            {
                $row[] = $item->status;
            }
            
            // Добавляем строку в таблицу
            $table->data[] = $row;
            $r++;
        }
        return $table;
    }
}