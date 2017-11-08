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

/**
 * Библиотека управления загружаемыми файлами
 *
 * @package    im
 * @subpackage filestorage
 * @author     Polikarpov Alexander <polikarpovst@gmail.com>
 * @copyright  2015
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class dof_modlib_filestorage implements dof_plugin_modlib
{
    /**
     * Объект деканата для доступа к общим методам
     * @var dof_control
     */
    protected $dof;
    
    // **********************************************
    // Методы, предусмотренные интерфейсом plugin
    // **********************************************
    
    /** 
     * Метод, реализующий инсталяцию плагина в систему
     * Создает или модифицирует существующие таблицы в БД
     * и заполняет их начальными значениями
     * 
     * @return boolean
     */
    public function install()
    {
        return true;
    }
    
    /** 
     * Метод, реализующий обновление плагина в системе.
     * Создает или модифицирует существующие таблицы в БД
     * 
     * @param string $old_version - Версия установленного в системе плагина
     * 
     * @return boolean
     */
    public function upgrade($oldversion)
    {
        return true;
    }
    
    /**
     * Возвращает версию установленного плагина
     * 
     * @return int - Версия плагина
     */
    public function version()
    {
		return 2016041800;
    }
    
    /** 
     * Возвращает версии интерфейса Деканата, с которыми этот плагин может работать
     * 
     * @return string
     */
    public function compat_dof()
    {
        return 'aquarium';
    }

    /**
     * Возвращает версии стандарта плагина этого типа, которым этот плагин соответствует
     * 
     * @return string
     */
    public function compat()
    {
        return 'neon';
    }
    
    /** 
     * Возвращает тип плагина
     * 
     * @return string 
     */
    public function type()
    {
        return 'modlib';
    }
    
    /** 
     * Возвращает короткое имя плагина
     * 
     * Оно должно быть уникально среди плагинов этого типа
     * 
     * @return string
     */
    public function code()
    {
        return 'filestorage';
    }
    
    /** 
     * Возвращает список плагинов, без которых этот плагин работать не может
     * 
     * @return array
     */
    public function need_plugins()
    {
        return [
                'modlib' => [
                        'widgets'         => 2009050800
                ],
                'storage' => [
                        'persons'         => 2015012000,
                        'config'          => 2011080900,
                        'acl'             => 2011040504
                ]
        ];
    }
    
    /** 
     * Определить, возможна ли установка плагина в текущий момент
     * Эта функция одинакова абсолютно для всех плагинов и не содержит в себе каких-либо зависимостей
     * @TODO УДАЛИТЬ эту функцию при рефакторинге. Вместо нее использовать наследование
     * от класса dof_modlib_base_plugin 
     * 
     * @see dof_modlib_base_plugin::is_setup_possible()
     * @param int $oldversion[optional] - старая версия плагина в базе (если плагин обновляется)
     *                                    или 0 если плагин устанавливается
     * 
     * @return bool 
     *              true - если плагин можно устанавливать
     *              false - если плагин устанавливать нельзя
     */
    public function is_setup_possible($oldversion = 0)
    {
        return dof_is_plugin_setup_possible($this, $oldversion);
    }

    /**
     * Получить список плагинов, которые уже должны быть установлены в системе,
     * и без которых начать установку или обновление невозможно
     *
     * @param int $oldversion[optional] - старая версия плагина в базе (если плагин обновляется)
     *                                    или 0 если плагин устанавливается
     *
     * @return array массив плагинов, необходимых для установки
     *      Формат: array('plugintype'=>array('plugincode' => YYYYMMDD00));
     */
    public function is_setup_possible_list($oldversion = 0)
    {
        return [
                'modlib' => [
                        'widgets'         => 2009050800
                ],
                'storage' => [
                        'persons'         => 2015012000,
                        'config'          => 2011080900,
                        'acl'             => 2011040504
                ]
        ];
    }
    
    /** 
     * Список обрабатываемых плагином событий 
     * 
     * @return array - array(array('plugintype'=>..,'plugincode'=>..,'eventcode'=>..),...)
     */
    public function list_catch_events()
    {
       return [];
    }
    
    /** 
     * Требуется ли запуск cron в плагине
     * 
     * @return bool
     */
    public function is_cron()
    {
       // Запуск не требуется
       return false;
    }
    
    /** 
     * Проверяет полномочия на совершение действий
     * 
     * @param string $do - идентификатор действия, которое должно быть совершено
     * @param int $objid - идентификатор экземпляра объекта, 
     *                     по отношению к которому это действие должно быть применено
     * @param int $userid - идентификатор пользователя Moodle, полномочия которого проверяются
     * 
     * @return bool 
     *              true - можно выполнить указанное действие по 
     *                     отношению к выбранному объекту
     *              false - доступ запрещен
     */
    public function is_access($do, $objid = NULL, $userid = NULL)
    {
        // Используем функционал из $DOFFICE
        return $this->dof->is_access($do, NULL, $userid);
    }
    
    /** 
	 * Требует наличия полномочия на совершение действий
	 * 
     * @param string $do - идентификатор действия, которое должно быть совершено
     * @param int $objid - идентификатор экземпляра объекта, 
     *                     по отношению к которому это действие должно быть применено
     * @param int $userid - идентификатор пользователя Moodle, полномочия которого проверяются
     * 
     * @return bool 
     *              true - можно выполнить указанное действие по 
     *                     отношению к выбранному объекту
     *              false - доступ запрещен
     */
    public function require_access($do, $objid = NULL, $userid = NULL)
    {
        if ( ! $this->is_access($do, $objid, $userid) )
        {
            $code = $this->code();
            $type = $this->type();
            $notice = $code.'/'.$do.' (block/dof/'.$type.'/'.$code.': '.$do.')';
            if ($objid){$notice.=" id={$objid}";}
            $this->dof->print_error('nopermissions','',$notice);
        }
    }
    
    /** 
     * Обработать событие
     * 
     * @param string $gentype - тип модуля, сгенерировавшего событие
     * @param string $gencode - код модуля, сгенерировавшего событие
     * @param string $eventcode - код задания
     * @param int $intvar - дополнительный параметр 
     * @param mixed $mixedvar - дополнительные параметры
     * 
     * @return bool - true в случае выполнения без ошибок
     */
    public function catch_event($gentype,$gencode,$eventcode,$intvar,$mixedvar)
    {
        return false;
    }
    
    /**
     * Запустить обработку периодических процессов
     * 
     * @param int $loan - нагрузка (
     *              1 - только срочные, 
     *              2 - нормальный режим, 
     *              3 - ресурсоемкие операции
     *        )
     * @param int $messages - количество отображаемых сообщений (
     *              0 - не выводить,
     *              1 - статистика,
     *              2 - индикатор, 
     *              3 - детальная диагностика
     *        )
     *        
     * @return bool - true в случае выполнения без ошибок
     */
    public function cron($loan,$messages)
    {
        return true;
    }
    
    /**
     * Обработать задание, отложенное ранее в связи с его длительностью
     * 
     * @param string $code - код задания
     * @param int $intvar - дополнительный параметр 
     * @param mixed $mixedvar - дополнительные параметры
     * 
     * @return bool - true в случае выполнения без ошибок
     */
    public function todo($code,$intvar,$mixedvar)
    {
        return true;
    }
    
    /** 
     * Конструктор
     * 
     * @param dof_control $dof - объект с методами ядра деканата
     */
    public function __construct($dof)
    {
        // Сохраняем ссылку на DOF, чтоб вызывать его через $this->dof
        $this->dof = $dof;
        $this->dofcontext = context_block::instance($dof->instance->id);
    }
    
    // **********************************************
    //              Собственные методы
    // **********************************************
    
    /**
     * Получить новый Itemid для хранения файлов
     * 
     * @param string $area - Файловая зона
     * 
     * @return int|bool - ID свободного экземпляра файловой зоны или false в случае ошибки
     */
    public function get_new_itemid( $area = 'public' )
    {
        global $CFG;
        
        $context = context_block::instance($this->dof->instance->id);
        require_once($CFG->libdir.'/filelib.php');
        $fs = get_file_storage();
        
        // Получение случайного ID зоны
        $itemid = mt_rand(1, 999999999);
        // Счетчик попыток
        $counter = 500;
        // Флаг завершения поиска
        $complete = false;
        do {
            // Проверка наличия файлов
            $files = $fs->get_area_files($context->id, 'block_dof', $area, $itemid);
            if ( empty($files) )
            {// Файлов нет, зону можно использовать
                $complete = true;
            } else  
            {// Зона не пуста - ищем новую
                $itemid = mt_rand(1, 999999999);
                $counter--;
            }
        } while ( empty($complete) && $counter > 0 );

        if ( $counter < 1 )
        {// ID не найден
            return false;
        }
        return $itemid;
    }
    
    /**
     * Сформировать ссылки на файлы
     * 
     * @param int $itemid - ID зоны
     * @param array $options - Дополнительные параметры 
     * 
     * @return string - HTML-код ссылок на файлы
     */
    public function link_files($itemid, $options = [])
    {
        $html = '';
        
        // Инициализируем генератор HTML
        $this->dof->modlib('widgets')->html_writer();
        
        // Контекст деканата
        $dofcontext = context_block::instance($this->dof->instance->id);
        // Менеждер
        $fs = get_file_storage();
        
        // Получение файлов
        $files = $fs->get_area_files($dofcontext->id, 'block_dof', 'public', $itemid);
        foreach ( $files as $file )
        {
            if ( $file->is_directory() )
            {// Пропуск директорий
                continue;
            }
            
            // Формирование ссылки на файл
            $filename = $file->get_filename();
            $url = moodle_url::make_pluginfile_url(
                    $file->get_contextid(),
                    $file->get_component(),
                    $file->get_filearea(),
                    $file->get_itemid(),
                    $file->get_filepath(),
                    $file->get_filename()
            );
            $html .= dof_html_writer::link($url, $filename);
        }
        
        return $html;
    }
    
    /** 
     * Подготовить файловую зону
     * 
     * Данный метод следует вызывать перед опредеделнием поля.
     * 
     * @param $name - Имя поля
     * @param $itemid - ID зоны файлов
     * 
     * @return int - ID пользовательской зоны
     */
    public function definion_filemanager($name, $itemid = NULL)
    {
        // Имя поля без _filemanager
        $tname = str_replace('_filemanager', '', $name);
        
        $data = new stdClass();
        // Подготовка файлменеджера
        file_prepare_standard_filemanager(
                $data,
                $tname,
                ['maxfiles' => 1, 'subdirs' => false],
                $this->dofcontext,
                'block_dof',
                'public',
                $itemid
        );
        
        if ( isset($data->$name) )
        {// Установлена зона
            return $data->$name;
        } else
        {
            return NULL;
        }
    }
    
    /**
     * Сохранить файлы из менеджера
     *
     * @param $name - Имя поля
     *
     * @return int - ID пользовательской зоны
     */
    public function process_filemanager($name, $draftitemid, $itemid = NULL)
    {
        // Имя поля без _filemanager
        $tname = str_replace('_filemanager', '', $name);
    
        if ( empty($itemid) )
        {// Зона асохранения не объявлена 
            $itemid = $this->get_new_itemid();
        }
        
        $data = new stdClass();
        $data->$name = $draftitemid;
        file_postupdate_standard_filemanager(
            $data,
            $tname,
            ['maxfiles' => 1, 'subdirs' => false],
            $this->dofcontext,
            'block_dof',
            'public',
            $itemid
        );
        
        // Плучение менеджера
        $fs = get_file_storage(); 
        $is_empty = $fs->is_area_empty($this->dofcontext->id, 'block_dof', 'public', $itemid);
        if ( ! empty($is_empty) )
        {// В зоне нет файлов - удаление папок и очистка зоны
            $fs->delete_area_files($this->dofcontext->id, 'block_dof', 'public', $itemid);
            return NULL;
        }
        return $itemid;
    }
    
    /**
     * Получить хэши путей файлов в указанной зоне
     *
     * @param int $itemid - ID зоны, в которую загружены файлы
     * @param array $options - Дополнительные параметры
     *
     * @return array - Массив хэшей путей файлов
     */
    public function get_pathnamehashes($itemid, $options = [])
    {
        // Контекст деканата
        $dofcontext = context_block::instance($this->dof->instance->id);
        // Менеждер
        $fs = get_file_storage();
    
        // Получение файлов
        $files = $fs->get_area_files($dofcontext->id, 'block_dof', 'public', $itemid);
        $pathnamehashes = [];
        foreach ( $files as $file )
        {
            if ( $file->is_directory() )
            {// Пропуск директорий
                continue;
            }
    
            // Формирование ссылки на файл
            $pathnamehash = $file->get_pathnamehash();
            $pathnamehashes[$pathnamehash] = $pathnamehash;
        }
    
        return $pathnamehashes;
    }
}

?>