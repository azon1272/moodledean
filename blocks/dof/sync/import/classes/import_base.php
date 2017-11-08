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
 * Импорт данных в Электронный Деканат. Класс импорта из csv.
 *
 * @package    sync
 * @subpackage import
 * @author     Polikarpov Alexander <polikarpovst@gmail.com>
 * @copyright  2016
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class dof_sync_import_base
{
    /**
     * Объект деканата для доступа к общим методам
     *
     * @var dof_control
     */
    protected $dof;
    
    /**
     * Адрес файла импорта
     *
     * @var string
     */
    protected $importfilepath = null;
    
    /**
     * Адрес файла лога
     *
     * @var string
     */
    protected $logfilepath = null;
    
    /**
     * Режим симуляции импорта
     * 
     * @var bool
     */
    protected $simulation = false;
    
    /**
     * Результат процесса импорта
     *
     * @var array
     */
    protected $importresult = null;
    
    /**
     * Тип импорта
     * 
     * В зависимости от типа будут исполняться различные действия по импорту данных
     * Доступные типы: 
     *   programmsbcs - Импорт подписок на программы
     *
     * @var string
     */
    protected $type = '';
    
    /**
     * Конструктор
     *
     * @param dof_control $dof -  Объект деканата для доступа к общим методам
     * @param string $importfilepath -  Объект деканата для доступа к общим методам
     * @param string $type - Тип импорта(ожидаемые данные)
     * @throws dof_exception - При ошибке во время инициализации импортера
     */
    public function __construct($dof, $type, $importfilepath)
    {
        $this->dof = $dof;
        
        // Проверка на наличие файла импорта
        if ( ! file_exists($importfilepath) )
        {// Файл не найден
            throw new dof_exception('importfileerror', 'file_not_found');
        } else 
        {// Установка файла импорта
            $this->importfilepath = $importfilepath;
        }
        $this->type = $type;
    }
    
    /**
     * Установка логирования
     * 
     * При отсутствии файл лога создается. 
     * В противном случае процесс логироуется в конец файла
     * 
     * @param string $logfilepath - Адрес файла лога
     * 
     * @throws dof_exception - При ошибке во время инициализации логирования процесса
     * 
     * @return void
     */
    public function init_loging($logfilepath)
    {
        if ( is_dir($logfilepath) )
        {// Путь является директорией
            throw new dof_exception('logfileerror', 'path_is_directory');
        }
        
        // Проверка на наличие файла импорта
        if ( ! file_exists($logfilepath) )
        {// Файл не найден
            
            // Проверка наличия папки по адресу
            $dir = dirname($logfilepath);
            if ( ! is_dir($dir) )
            {// Папка файла не найдена, файл невозможно будет создать
                throw new dof_exception('logfileerror', 'invalid_path');
            }
        }
        $this->logfilepath = $logfilepath;
        return true;
    }
    
    /**
     * Инициализация режима симуляции
     * 
     * @param bool $enabled - Состояние режима симуляции(True - включен/False - отключен)
     * 
     * @return void
     */
    public function init_simulation($enabled = true)
    {
        $this->simulation = (bool)$enabled;
    }
    
    /**
     * Процесс импорта
     * 
     * @param array $options - Дополнительные опции обработки
     * 
     * @throws dof_exception - При критических ошибках во время импорта файла
     * 
     * @return void
     */
    public function import($options)
    {
        dof_hugeprocess();
        // Процесс импорта
        $this->importresult = [];
    }
    
    /**
     * Получить результат процесса импорта
     *
     * @return array|null - Отчет об импорте или null, если импорт не был запущен
     */
    public function get_import_status()
    {
        return $this->importresult;
    }
    
    /**
     * Добавить в лог
     *
     * Производит запись данных в файл лога, если логирование доступно
     *
     * @param string - Текст, который будет добавлен в лог
     *
     * @return void
     */
    public function add_log($text)
    {
        if ( $this->logfilepath )
        {
            file_put_contents($this->logfilepath, $text.PHP_EOL, FILE_APPEND);
        }
    }
}
