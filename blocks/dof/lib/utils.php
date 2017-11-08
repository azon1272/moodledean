<?PHP
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

/** Проверяет корректность имени плагина
 * Имя должно быть длиной до 20 символов и состоять только из латинских символов
 * Цифры нельзя, потому что так же сделано в плагинах moodle и PARAM_ALPHA их не пропускает
 * @param $str - имя плагина
 * @return bool true, если имя удовлетворяет этим условиям
 * false - если не удовлетворяет  
 */
function dof_checkcode($str)
{
    return mb_strlen($str) > 0 AND mb_strlen($str) <= 20 AND preg_match("/^[a-z]+$/",$str);
}
/** Возвращает строку перевода из файла локализации
 * @param string $langfilepath - путь к файлу локализации
 * @param string $identifier - идентификатор строки, которая хранит перевод 
 * @param object $a - объект, который хранит значения, вставляемые в перевод строки
 * @return string строка перовода
 * @access public
 */
function dof_get_langstring($langfilepath, $identifier, $a=null)
{
    $resultstring = '';
    if ($a)
    {
        $a = clean_getstring_data($a);
    }					
    if (file_exists($langfilepath))
    {
        if ($result = get_string_from_file($identifier, $langfilepath, "\$resultstring"))
        {

            eval($result);
            return $resultstring;
        }
    }
    return '[['.$identifier.']]';
}
/** Получить текущую нагрузку системы
 * @return int загрузка системы (1 - час-пик, 2 - нормальное состояние, 3 - пониженная нагрузка)
 * @access public
 */
function dof_get_loan()
{
    return 3;
}
/** Вывести сообщение при работе в пакетном режиме
 * @param int $mlevel - приоритет текущего сообщения
 * @param string $string - сообщение
 * @param $eol - символ конца строки
 * @param $sleep - задержка после вывода сообщения
 * @param $clevel - уровень детализации сообщений (0 - не выводить,1 - статистика, 2 - индикатор, 3 - детальная диагностика)
 * @return bool
 * @access public
 */
function dof_mtrace($mlevel,$string,$eol="\n",$sleep=0,$clevel=3)
{
    // Выводим только сообщения, соответствующие уровню детализации
    if ($mlevel <= $clevel)
    {
        mtrace($string,$eol,$sleep);
        
    }
    return true;
}

/** Обертка для вывода отладочных сообщений
 * Выводит отладочные сообщения для разроаботчиков, которые показываются только в режиме отображения
 * ошибок "DEVELOPER" (эта настройка включается в Moodle)
 * 
 * @param string $message [optional] - выводимое сообщение
 * @param int    $level [optional] - глубина режива отладки
 *                              DEBUG_ALL - выводить все сообщения отладчика PHP
 *                              DEBUG_NORMAL - выводить ошибки, предупреждения и примечания
 *                              DEBUG_DEVELOPER - выводить дополнительные сообщения отладчика Moodle для разработчиков
 * @param array $backtrace [optional] - использовать собственные методы трассировки
 * 
 * return moodle function result debugging();
 */
function dof_debugging($message = '', $level = DEBUG_NORMAL, $backtrace = null)
{
    return debugging($message, $level, $backtrace);
}

/**
 * Функция для отладки
 * 
 * @param mixed $object
 * @param bool $return - возвратить строку? Иначе - echo.
 * @return mixed|void|string
 */
function dof_print_object($object, $return = true)
{
    if ( !$return )
    {
        print_object($object);
    } else if (CLI_SCRIPT)
    {
        fwrite(STDERR, print_r($object, true));
        fwrite(STDERR, PHP_EOL);
    } else
    {
        return html_writer::tag('pre', s(print_r($object, true)), array('class' => 'notifytiny'));
    }
    return false;
}

/**
 * Увеличивает лимиты памяти и времени исполнения для больших процессов
 *
 * @param int $time
 * @param string $memory
 * @param bool $endflush
 */
function dof_hugeprocess($time=null,$memory=null,$endflush=true)
{
    if (is_null($time))
    {
        $time = 0;
    }
    if (is_null($memory))
    {
        $memory = "512M";
    }
    @set_time_limit($time);
    @raise_memory_limit($memory);
    ignore_user_abort();//не прерывать выполнение скрипта при отсоединении клиента
    if ($endflush)
    {
        while (ob_get_level()>1)
        {//выключаем буферирование вывода до одного уровня
            ob_end_flush();
        }
    }            
} 
/** Записать поля из объекта $obj2 поверх полей из объекта $obj1
 * @return object
 * @access public
 */
function dof_object_merge($obj1,$obj2)
{
    $obj = clone $obj1;
    // Перебираем все вложенные элементы
    foreach ($obj2 as $key => $value)
    {
        if (!isset($obj->$key))
        { // Элемент отсутствует - замещаем
            if (is_scalar($value) or is_resource($value) or is_null($value))
            { // Копируем нерекурсивный элемент
                $obj->$key = $value;
            }else
            { // Клонируем рекурсивный элемент
                $obj->$key = fullclone($value);
            }
        } elseif (is_scalar($value) or is_resource($value) or is_null($value))
        { // Замещаем скалярный элемент
            $obj->$key = $value;
        }else
        { // Сливаем вложенный рекурсивный элемент
            $obj->$key = dof_object_merge($obj->$key,$value);
        }	
    }
    return $obj;
}

/** Проверяет, начинается ли строка с другой строки 
 * @param $haystack - строка, в которой ищем
 * @param $needle - искомая строка
 * @return mixed false, если не начинается с $needle, true - если совпадает и строка остатка - если меньше
 */
function dof_strbeginfrom($haystack,$needle)
{
    if (mb_substr($haystack,0,$nlenght = mb_strlen($needle),'utf-8')===$needle)
    {
        if ($hlenght = mb_strlen($haystack) > $nlenght)
        {
            return mb_substr($haystack,$nlenght,$hlenght,'utf-8');
        }
        return true;
    }
    return false;
}
/** Проверяет, содержит ли переменная положительное целое
 * @param mixed $val
 * @return bool 
 */
function is_int_string($val)
{
    if (is_int($val) or ctype_digit($val))
    {
        return true;
    }
    return false;
}

/** Преобразомать массив html-опций элемента к строке
 * @param array|string - строка со свойствами html-элемента
 * 
 * @return string
 */
function dof_transform_tag_options($options)
{
    if ( is_string($options) )
    {
        return $options;
    }
    if ( ! is_array($options) )
    {
        return '';
    }
    
    $result = '';
    
    foreach ( $options as $name => $value )
    {
        $result .= ' '.$name.'="'.$value.'" ';
    }
    
    return $result;
}

/** Определить, возможна ли установка плагина
 * @todo эта функция временно вынесена в библиотеку utils для того чтобы избежать многокоатных
 *       однотипных правок кода в более чем 80 плагинах.
 *       Она не содержит в себе каких-либо зависимостей.
 *       Эту функцию следует удалить отсюда при рефакторинге
 * @see dof_modlib_base_plugin::is_setup_possible()
 * 
 * @param dof_plugin $pluginobj - объект плагина системы "электронный деканат"
 * @param int $oldversion [optional] - старая версия плагина в базе (если плагин обновляется)
 *                                    или 0 если плагин устанавливается
 * 
 * @return bool 
 *              true - если плагин можно устанавливать
 *              false - если плагин устанавливать нельзя
 * 
 */
function dof_is_plugin_setup_possible($pluginobj, $oldversion=0)
{
    global $DOF;
    $pluginlist = null;
    if ( ! method_exists($pluginobj, 'is_setup_possible_list') )
    {// нет функции со списком плагинов -  этом случае считаем что установка возможна
        return true;
    }
    
    // получаем все плагины, которые необходимо установить или обновить
    $pluginlist = $pluginobj->is_setup_possible_list($oldversion);
    
    if ( empty($pluginlist) )
    {// список необходимых плагинов пуст можем начинать установку
        return true;
    }
    
    if ( ! is_array($pluginlist) )
    {// список плагинов получен, но в неправильном формате
        $DOF->print_error('Wrong plugin list format in is_setup_possible_list(), class '.get_class($pluginobj));
    }
    
    foreach ( $pluginlist as $plugintype => $plugins )
    {
        foreach ( $plugins as $plugincode => $version )
        {
            if ( ! $DOF->plugin_exists($plugintype, $plugincode) )
            {// в системе не установлен нужный плагин
                return false;
            }
            if ( $DOF->$plugintype($plugincode)->version() < $version )
            {// версия требуемого плагина слишком старая
                return false;
            }
        }
    }
    
    // все условия для начала установки плагина выполнены
    return true;
}

/*** Функции работы со временем ***/

/** Эта функция - аналог getdate, но не учитывает переход на летнее/зимнее время (который отменен теперь)
 * Создана для исправления ошибки, возникшей 07 ноября 2011.
 * Код взят с php.net: http://www.php.net/manual/en/function.getdate.php#86395
 * 
 * @param int $timestamp [optional] - unixtime-метка для преобразования
 * 
 * @return array массив, поструктуре аналогичный массиву из php-функции getdate()
 */
function dof_gmgetdate($timestamp=null)
{
    if ( is_null($timestamp) )
    {
        $timestamp = time();
    }

    $dateParts = array(
        'mday'    => 'j',
        'wday'    => 'w',
        'yday'    => 'z',
        'mon'     => 'n',
        'year'    => 'Y',
        'hours'   => 'G',
        'minutes' => 'i',
        'seconds' => 's',
        'weekday' => 'l',
        'month'   => 'F',
        0         => 'U'
    );

    while (list($part, $format) = each($dateParts))
    {
        $GMdateParts[$part] = gmdate($format, $timestamp);
    }

    return $GMdateParts;
}

/** Получить часовой пояс пользователя moodle 
 * 
 * @return string|boolean - часовой пояс в UTC или false
 * @param int $timezone - номер часового пояса
 */
function dof_usertimezone($timezone=99)
{
    return usertimezone($timezone);
}

/** Получить временую зону на сервере. На текущий момент это UTC+4 (Москва)
 * @todo не нашел в Moodle подходящей функции поэтому помещаю здесь
 * 
 * @return float - временная зона (смещение в часах)
 */
function dof_servertimezone()
{
    return idate('Z') / 3600;
}

/** Получить дату и время с учетом часового пояса
 * 
 * @return string|boolean - время с учетом часового пояса или false
 * @param int $date - время в unixtime
 * @param string $format - формат даты с учетом символов используемых в strftime
 * @param int $timezone - номер часового пояса
 * @param boolean $fixday - true стирает нуль перед %d
 *                          false - не стирает
 */
function dof_userdate($date, $format = '', $timezone = 99, $fixday = true)
{
    return userdate($date, $format, $timezone, $fixday);
}

/** Получить дату и время с учетом часового пояса
 * 
 * @return array - время с учетом часового пояса
 * @param int $date - время в unixtime
 * @param int $timezone - номер часового пояса
 */
function dof_usergetdate($date,$timezone=99)
{
    return usergetdate($date,$timezone);
}

/** Получить дату и время с учетом часового пояса
 * 
 * @return int - время с учетом часового пояса в Unixtime
 * @param int $date - время в unixtime
 * @param int $timezone - номер часового пояса
 */
function dof_make_timestamp($year, $month=1, $day=1, $hour=0, $minute=0, $second=0, $timezone=99, $applydst=true)
{
    return make_timestamp($year, $month, $day, $hour, $minute, $second, $timezone, $applydst);
}


/** Получить список временных зон moodle
 *  + [99] - время на сервере
 * 
 * @return Array(
 *                       [-13.0] => UTC-13
 *                       [-12.5] => UTC-12.5
 *                       [-12.0] => UTC-12
 *                       [-11.5] => UTC-11.5
 *                       [-11.0] => UTC-11
 *                       [-10.5] => UTC-10.5
 *                       [-10.0] => UTC-10 ...
 *                        ....
 *                       [12.5] => UTC+12.5
 *                       [13.0] => UTC+13
 */
function dof_get_list_of_timezones()
{
    $timezone = get_list_of_timezones();
    // добавим - зону локальную(сервера)
    $timezone['99'] = get_string('serverlocaltime');
    return $timezone;
}

/** Отсортировать поля внутри объекта
 * @param object $object - объект с полями
 * @return bool|object - false в случае ошибки или объект
 */
function dof_sort_object_fields($object, $sortorder = SORT_REGULAR)
{
    if ( !is_object($object) OR empty($object) )
    {
        return false;
    }
    // Преобразуем объект в массив
    $array = get_object_vars($object);
    $sorted = new stdClass();
    // Отсортируем массив
    ksort($array, $sortorder);
    // Сформируем объект заново и передадим его
    foreach ( $array as $key => $value )
    {
        $sorted->$key = $value;
    }
    return $sorted;
}

/**
 * Сформировать срез многомероного массива
 * 
 * @param array $array - Многомерный массив
 * @param int $limitfrom - Смещение, >= 0
 * @param int $limitnum - Число возвращаемых элементов
 * @param array $options - Массив опций
 * 
 * @return - Срез массива
 */
function dof_array_slice($array, &$limitfrom = 0, &$limitnum = 0, $options = [])
{
    if ( ! is_array($array) )
    {// Значение не является массивом
        return $array;
    }
    
    $result = [];
    foreach ( $array as $key => $value ) 
    {// Обработка каждого элемента
        if ( is_array($value) )
        {
            $slice = dof_array_slice($value, $limitfrom, $limitnum, $options);
            if ( ! empty($slice) )
            {
                $result[$key] = $slice;
            }
        } else 
        {
            if ( $limitfrom == 0 && $limitnum > 0 )
            {
                $result[$key] = $value;
                $limitnum -= 1;
            } else
            {
                $limitfrom -= 1;
            }
        }
    }
    return $result;
}
