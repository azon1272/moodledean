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
 * Импорт данных в Электронный Деканат. Класс плагина.
 *
 * @package    sync
 * @subpackage import
 * @author     Polikarpov Alexander <polikarpovst@gmail.com>
 * @copyright  2016
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class dof_sync_import implements dof_sync
{
    /**
     * Объект деканата для доступа к общим методам
     * 
     * @var dof_control
     */
    protected $dof;
    
    /**
     * Доступные расширения файлов для импорта
     * 
     * @var array
     */
    protected $available_extensions = ['csv'];
    
    // **********************************************
    // Методы, предусмотренные интерфейсом plugin
    // **********************************************
    
    /** 
     * Метод, реализующий инсталяцию плагина в систему
     * 
     * Создает или модифицирует существующие таблицы в БД
     * и заполняет их начальными значениями
     * 
     * @return boolean
     */
    public function install()
    {
        return $this->dof->storage('acl')->save_roles($this->type(),$this->code(),$this->acldefault());
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
        // Обновление прав доступа
        return $this->dof->storage('acl')->save_roles($this->type(),$this->code(),$this->acldefault());  
    }
    
    /**
     * Возвращает версию установленного плагина
     * 
     * @return int - Версия плагина
     */
    public function version()
    {
		return 2016070400;
    }
    
    /** 
     * Возвращает версии интерфейса Деканата, с которыми этот плагин может работать
     * 
     * @return string
     */
    public function compat_dof()
    {
        return 'aquarium_bcd';
    }

    /**
     * Возвращает версии стандарта плагина этого типа, которым этот плагин соответствует
     * 
     * @return string
     */
    public function compat()
    {
        return 'ancistrus';
    }
    
    /** 
     * Возвращает тип плагина
     * 
     * @return string 
     */
    public function type()
    {
        return 'sync';
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
        return 'import';
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
                                'ig'           => 2016060900,
                                'nvg'          => 2016050400,
                                'widgets'      => 2016050500
                ],
                'storage' => [
                                'config'          => 2012042500,
                                'acl'             => 2012042500
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
                                'ig'           => 2016060900,
                                'nvg'          => 2016050400,
                                'widgets'      => 2016050500
                ],
                'storage' => [
                                'config'          => 2012042500,
                                'acl'             => 2012042500
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
        return [
        ];
    }
    
    /** 
     * Требуется ли запуск cron в плагине
     * 
     * @return bool
     */
    public function is_cron()
    {
       // Запуск каждый день
       return 86400;
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
    public function is_access($do, $objid = null, $userid = null)
    {
        if ( $this->dof->is_access('datamanage') OR 
             $this->dof->is_access('admin') OR 
             $this->dof->is_access('manage') 
           )
        {// Открыть доступ для менеджеров
            return true;
        } 
              
        // Получаем ID персоны, с которой связан данный пользователь 
        $personid = $this->dof->storage('persons')->get_by_moodleid_id($userid);
        // Формируем параметры для проверки прав
        $acldata = $this->get_access_parametrs($do, $objid, $personid);

        switch ( $do )
        {// Определяем дополнительные параметры в зависимости от запрашиваемого права
            default:
                break;
        }
        // Производим проверку
        if ( $this->acl_check_access_paramenrs($acldata) )
        {// Право есть
            return true;
        } 
        return false;
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
    public function require_access($do, $objid = null, $userid = null)
    {
        if ( ! $this->is_access($do, $objid, $userid) )
        {
            $notice = "{$this->code()}/{$do} (block/dof/{$this->type()}/{$this->code()}: {$do})";
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
    public function catch_event($gentype, $gencode, $eventcode, $intvar, $mixedvar)
    {
        return true;
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
        $result = true;
        if ( $loan > 0 )
        {// Любой уровень исполнения
            // Очистка директории с устаревшими файлами импорта( > 1 месяца )
            $timeinterval = 2592000;
            $this->clean_importfiles_directory($timeinterval);
        }
        return $result;
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
    }
    
    /**
     * Получить настройки для плагина
     * 
     * @param unknown $code
     * 
     * @return array - Массив настроек плагина
     */
    public function config_default($code = null)
    {
        // Плагин включен и используется
        $config = [];
        
        return $config;
    }
    
    // **********************************************
    //       Методы для работы с полномочиями
    // **********************************************
    
    /**
     * Получить список параметров для фунции has_hight()
     *
     * @return object - список параметров для фунции has_hight()
     * @param string $action - совершаемое действие
     * @param int $objectid - id объекта над которым совершается действие
     * @param int $personid
     */
    protected function get_access_parametrs($action, $objectid, $personid, $depid = null)
    {
        $result = new stdClass();
        $result->plugintype   = $this->type();
        $result->plugincode   = $this->code();
        $result->code         = $action;
        $result->personid     = $personid;
        $result->departmentid = $depid;
        $result->objectid     = $objectid;
    
        if ( is_null($depid) )
        {// Подразделение не задано - ищем в GET/POST
            $result->departmentid = optional_param('departmentid', 0, PARAM_INT);
        }
        if ( ! $objectid )
        {// Если objectid не указан - установим туда 0 чтобы не было проблем с sql-запросами
            $result->objectid = 0;
        }
    
        return $result;
    }
    
    /**
     * Проверить права через плагин acl.
     *
     * Функция вынесена сюда, чтобы постоянно не писать
     * длинный вызов и не перечислять все аргументы
     *
     * @param object $acldata - объект с данными для функции storage/acl->has_right()
     *
     * @return bool
     */
    protected function acl_check_access_paramenrs($acldata)
    {
        return $this->dof->storage('acl')->
            has_right(
                $acldata->plugintype,
                $acldata->plugincode,
                $acldata->code,
                $acldata->personid,
                $acldata->departmentid,
                $acldata->objectid
            );
    }
    
    /**
     * Сформировать права доступа для интерфейса
     *
     * @return array - Массив с данными по правам доступа
     */
    public function acldefault()
    {
        $a = [];
    
        return $a;
    }
    
    // **********************************************
    // Собственные методы
    // **********************************************
    
    /**
     * Получить путь до файла импорта 
     * 
     * @param string $filename - Имя файла импорта. Если не указан, вернет путь до папки
     * 
     * @return string - Путь до файла импорта
     */
    public function get_importfile_path($filename = '')
    {
        // Вернуть путь в moodledata
        return $this->dof->plugin_path($this->type(), $this->code(), '/tmp/'.$filename);
    }
    
    /**
     * Сохранить файл импорта
     *
     * @param string $filename - Контент файла
     * @param string $filename - Имя файла импорта. Если не указан, имя будет сгенерировано
     * @param bool $override - Перезаписывать существующий файл
     *
     * @return string - Имя файла или false, если сохранение не удалось
     */
    public function save_importfile($filecontent, $filename = '')
    {
        // Получение пути для файла
        if ( $filename )
        {// Имя файла указано
            $filepath = $this->get_importfile_path($filename);
        } else 
        {// Требуется сгенерировать имя файла
            $filepath = $this->dof->plugin_path($this->type(), $this->code(), '/tmp/tempnam');
            
            // Получение имени файла
            $tmppath = $this->get_importfile_path();
            $filename = str_replace ($tmppath, '', $filepath);
        }
    
        // Сохранение файла
        $file = fopen($filepath, 'w');
        fwrite($file, (string)$filecontent);
        
        return $filename;
    }
    
    /**
     * Процесс очистки устаревших файлов в папке импорта
     * 
     * @param int $timeinterval - Минимальное время жизни файла для установки его как устаревшего и удаления
     * 
     * return bool - Результат работы 
     */
    protected function clean_importfiles_directory($timeinterval)
    {
        // Получение папки с файлами импорта
        $tmppath = $this->get_importfile_path();
        
        $result = true;
        
        if ( is_dir($tmppath) ) 
        {
            // Открытие каталога
            if ( $dir = opendir($tmppath) ) 
            {
                // Проверка каждого файла на устаревание
                while ( (($file = readdir($dir)) !== false) )
                {
                    if ( is_file($tmppath.$file) )
                    {
                        // Получение времени изменения файла
                        $filetime = filemtime($tmppath.$file);
                        
                        if ( (time() - $filetime) > $timeinterval )
                        {// Файл считается устаревшим
                        
                        // Удаление файла
                        $result = ( unlink($tmppath.$file) & $result);
                        }
                    }
                }
                closedir($dir);
            }
        }
        return $result;
    }
    
    /**
     * Импорт данных в электронный деканат из файла
     * 
     * Производит автоматическое определение формата и анализ полей в файле
     * По результатам анализа передает управление индивидуальному методу обработки
     * При критических ошибках выбрасывает исключение и возвращает falsе
     * 
     * @param string $type - Тип импорта(ожидаемые данные)
     * @param string $importfilepath - Путь до файла импорта
     * @param string $logfilepath - Путь до файла лога
     * @param array $options - Массив дополнительных параметров обработки
     * 
     * @return array|bool - Массив с результатами работы, или false в случае критической ошибки в работе
     * 
     * @throws dof_exception_file - При ошибках в чтении файла импорта
     * @throws dof_exception - При прочих критических ошибках
     */
    public function import($type, $importfilepath, $logfilepath = null, $options = [])
    {
        // Проверка на наличие файла
        if ( ! file_exists($importfilepath) )
        {// Файл не найден
            throw new dof_exception_file('importfileerror', 'file_not_found');
            return false;
        }
        
        // Получение расширения файла
        $exploded = (array)explode('.', $importfilepath);
        $extension = end($exploded);
        if ( empty($extension) )
        {// Расширение не получено
            throw new dof_exception_file('importfileerror', 'file_extension_not_found');
            return false;
        }
        
        if ( array_search($extension, $this->available_extensions) === false )
        {// Расширение файла не поддерживается системой
            throw new dof_exception_file('importfileerror', 'file_extension_not_support');
            return false;
        }
        
        // Передача работы методу импорта данного типа файла
        switch ( $extension )
        {
            // Импорт csv
            case 'csv' :
                return $this->import_csv($type, $importfilepath, $logfilepath, $options);
                break;
            // Импортер не найден
            default :
                throw new dof_exception('importerror', 'file_extension_not_support');
                return false;
        }
        
    }
    
    /**
     * Импорт данных в электронный деканат из csv-файла
     *
     * @param string $type - Тип импорта(ожидаемые данные)
     * @param string $importfilepath - Путь до файла импорта
     * @param string $logfilepath - Путь до файла лога
     * @param array $options - Массив дополнительных параметров обработки
     *              bool 'simulation' - Производить симуляцию импорта. В этом режиме данные не будут добавлены в систему
     *              string 'delimiter' - Разделитель полей. По-умолчанию ;
     *              
     * @return array|bool - Массив с результатами работы, или false в случае критической ошибки в работе
     *
     * @throws dof_exception - При критических ошибках импорта
     */
    protected function import_csv($type, $importfilepath, $logfilepath = null, $options = [])
    {
        // Установка разделителя
        if ( ! isset($options['delimiter']) )
        {// Разделитель не указан
            // Установка разделителя по-умолчанию
            $delimiter = ';';
        } else
        {// Разделитель указан
            $delimiter = $options['delimiter'];
        }
        
        // Установка симуляции
        $simulation = false;
        if ( isset($options['simulation']) )
        {
            $simulation = (bool)$options['simulation'];
        }
        
        // Подключение класса импорта из csv
        require_once $this->dof->plugin_path('sync', 'import','/classes/import_csv.php');
        
        // Попытка импорта
        try {
            
            // Получение импортера
            $importer = new dof_sync_import_csv($this->dof, $type, $importfilepath, $delimiter);

            // Симуляция процесса
            if ( $simulation )
            {// Включение режима симуляции
                $importer->init_simulation();
            } else 
            {// Симуляция отключена
                // Установка логирования процесса
                $importer->init_loging($logfilepath);
            }
            
            // Импорт
            $importer->import($options);
            
            // Получение результатов импорта
            $importresult = $importer->get_import_status();
            
        } catch ( dof_exception $e )
        {// Критическая ошибка импорта
            $importresult = false;
        }
        
        // Вернуть результат импорта
        return $importresult;
    }
}
?>