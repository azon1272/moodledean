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

// Определяем режимы отображения шапки и подвала
// Без шапки и подвала (upload/download)
define('NVG_MODE_FILE',0);
// Версия для печати
define('NVG_MODE_PRINT',1);
// Всплывающее окошко
define('NVG_MODE_POPUP',2);
// Страница - полноценные шапка и подвал без боковых колонок
define('NVG_MODE_PAGE',3);
// Трехколоночная страница
define('NVG_MODE_PORTAL',4);

/**
 * Класс для навигации, отображения заголовков и других служебных элементов страницы
 */
class dof_modlib_nvg implements dof_plugin_modlib
{
    /**
     * @var dof_control
     */
    protected $dof;
    /** Распечатан или еще не распечатан заголовок страницы
     * @var bool
     */
    protected $headerprinted = false;
    // **********************************************
    // Методы, предусмотренные интерфейсом plugin
    // **********************************************
    /** Метод, реализующий инсталяцию плагина в систему
     * Создает или модифицирует существующие таблицы в БД
     * и заполняет их начальными значениями
     * @return boolean
     * Может надо возвращать массив с названиями таблиц и результатами их создания?
     * чтобы потом можно было распечатать сообщения о результатах обновления
     * @access public
     */
    public function install()
    {
        return true;
    }
    /** Метод, реализующий обновление плагина в системе
     * Создает или модифицирует существующие таблицы в БД
     * @param string $old_version - версия установленного в системе плагина
     * @return boolean
     * Может надо возвращать массив с названиями таблиц и результатами их создания/изменения?
     * чтобы потом можно было распечатать сообщения о результатах обновления
     * @access public
     */
    public function upgrade($oldversion)
    {
        return true;
    }
    /** Возвращает версию установленного плагина
     * @return string
     * @access public
     */
    public function version()
    {
        return 2016050400;
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
        return 'neon_a';
    }
    
    /** Возвращает тип плагина
     * @return string 
     * @access public
     */
    public function type()
    {
        return 'modlib';
    }
    /** Возвращает короткое имя плагина
     * Оно должно быть уникально среди плагинов этого типа
     * @return string
     * @access public
     */
    public function code()
    {
        return 'nvg';
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
    // **********************************************
    // Собственные методы
    // **********************************************
    /**
     * @var array массив, содержащий уровни навигации
     */
    protected $levels;
    /** Конструктор
     * @param dof_control $dof
     * 
     */
    public function __construct($dof)
    {
        $this->dof = $dof;
        $this->levels = array();
    }
    /** Добавить уровень к строке навигации
     * @param string $name - название уровня
     * @param string $url - строка пути, по которому надо перейти
     * @param array $addvars[optional] - массив доп параметров(ключ - значение)
     * @return string  - html-код названия секции
     */
    public function add_level($name, $url, $addvars=NULL)
    {
        global $PAGE;
        if ( is_array($addvars) )
        {// если переданы дополнительные get-параметры для ссылки - то их нужно добавить к адресу
            $url = new moodle_url($url, $addvars);
        }
        $this->levels[] = array('name'=>$name,'url'=>$url);
        return true;
    }
    /** Подключить javascript-файл в раздел head
     * 
     * @param string $plugintype - тип плагина, из которого подключается файл
     * @param string $plugincode - код плагина, из которого подключается файл
     * @param string $addpath    - путь к файлу внутри плагина
     * @param bool $inhead[optional] - где подключать скрипт
     *                                 true - в начале страницы секции head
     *                                 false - внизу страницы (для более быстрого отображения html)
     * @return bool
     */
    public function add_js($plugintype, $plugincode, $addpath, $inhead=true)
    {
        global $PAGE;
        // получаем путь к файлу скрипта
        $urlfunc = "url_$plugintype";
        $url = new moodle_url($this->dof->$urlfunc($plugincode, $addpath));
        
        // Устанавливаем зависимости
        $PAGE->requires->js($url, $inhead);
        
        return true;
    }
    
    /** Подключить внешнюю таблицу стилей
     * 
     * @param string $plugintype - тип плагина, из которого подключается файл
     * @param string $plugincode - код плагина, из которого подключается файл
     * @param string $addpath    - путь к файлу внутри плагина
     * 
     * @return bool
     */
    public function add_css($plugintype, $plugincode, $addpath)
    {
        global $PAGE;
        // получаем путь к файлу стилей
        $urlfunc = "url_$plugintype";
        $url = new moodle_url($this->dof->$urlfunc($plugincode, $addpath));
        
        // Подключаем стили в список зависимостей
        $PAGE->requires->css($url);
        
        return true;
    }
    
    /** 
     * Сформировать блок хлебных крошек
     * 
     * @return string - html-код строки навигации
     */
    public function get_breadcrumbs_bar()
    {
        global $PAGE;

        if ( ! empty($this->levels) )
        {// Элементы хлебных крошек определены
            foreach ($this->levels as $this_level=>$info)
            {// Добавление каждого уровня в навигацию Moodle
                $PAGE->navbar->add($info['name'],$info['url']);
            }
            // Установка текущего url страницы
            $lastlevel = end($this->levels);
            $url = new moodle_url($lastlevel['url']);
            $PAGE->set_url($url);
        }
        return true;
   }
    /** Получить название элемента
     * 
     * @param int $level - уровень навигации
     * @return string  - название элемента
     */
    public function get_name($level = NULL)  
    {
        if ( is_null($level) )
        {//если уровень навигации не задан - вернем последний
            end($this->levels); //перевели указатель на последний элемент массива
            $info = current($this->levels);//получили последний элемент
            reset($this->levels);//вернули указатель на первый элемент массива
        }else
        {//уровень навигации указан
            $info = $this->levels[$level];//получаем информацию о нем
        }
        return $info['name'];//вернули его имя
    }
    /** Получить URL элемента
     * 
     * @param int $level - уровень навигации
     * @return string  - url элемента
     */
    public function get_url($level = NULL)
    {
        if ( is_null($level) )
        {//уровень навигации не указан
            end($this->levels); //перевели указатель на последний элемент массива
            $info = current($this->levels);//получили его
            reset($this->levels);//вернули указатель на первый элемент массива
        }
        else
        {//уровень навигации указан
           $info = $this->levels[$level];//получаем информацию о нем
        }
        
        return $info['url'];//возвращаем его url
    }
    /** Возвращает html-код блока
     * @param string $code - код плагина
     * @param string $blocktitle - название блока
     * @param string $contentname - название блока
     * @param int $id - id реакции блока
     * @return bool - true - блок есть, false - блока нет
     */
    public function print_block($code, $contentname, $id = 1, $blocktitle=null)
    {
        GLOBAL $OUTPUT;
        $content = $this->dof->im($code)->get_block($contentname, $id);//получаем содержание блока
        if (!is_string($content))
        {
            return false;
        }
        echo "\n<!-- start block {$contentname} -->\n";
        
        $bc = new block_contents();
        $bc->content = $content;
        $bc->title = $blocktitle;       
        // POS LEFT may be wrong, but no way to get a better guess here.
        echo $OUTPUT->block($bc, BLOCK_POS_LEFT);
        echo "\n<!-- end block {$contentname} -->\n";
        return true;
    }
    /**
     * Возвращает массив с параметрами блоков для колонки
     * @param mixed $side - настройки блоков, путь к файлу с настройками или код колонки
     * @return array - список блоков или пустой массив
     */
    protected function get_blocks_cfg($side)
    {
         if (is_array($side))
         {
             // Передан массив
             return $side;
         }elseif ( $side == 'right' )
         {//надо вернуть правые блоки
             $side = $this->dof->plugin_path('modlib', 'nvg','/cfg/right.php'); //подключаем правые блоки
         } elseif ( $side == 'left' )
         {//надо вернуть левые блоки
             $side = $this->dof->plugin_path('modlib', 'nvg','/cfg/left.php');  //подключаем левые блоки
         }elseif (is_file($side))
         {
             // Передан путь - ничего делать не надо
             // все сделаем в конце
         }else
         {//передано непонятно что
             return array();
         }
         include ($side);
         return $blocks;    
    }
    /** Выводит на экран блоки, которые должны отображаться
     * по левому ($side = 'left') либо правому ($side = 'right') краю страницы
     * @param mixed $side - указывает, блоки какой стороны надо собирать 
     * @return bool
     */
     public function print_blocks($side = 'left')
    {
        GLOBAL $OUTPUT;

        $OUTPUT->container_start();
        $blocks = $this->get_blocks_cfg($side);
        foreach ($blocks as $block )
        {//перебираем и печатаем блоки
            if ( $this->dof->plugin_exists('im', $block['im']) OR $block['im'] == 'admin'  )
            {
                $this->print_block($block['im'], $block['name'], $block['id'], $block['title']);
            }
        }
        $OUTPUT->container_end();
    }
    /** Отобразить заголовок страницы
     * 
     * @param int $mode - режим отображения
     * @param string $opt - путь к файлу с левыми блоками 
     * @return bool
     */
    public function print_header($mode = NVG_MODE_PAGE, $opt = NULL)
    {
        global $PAGE, $OUTPUT;
        
        // Установка заголовка страницы
        $PAGE->set_title($this->get_name());
        
        // Добавление хлебных крошек Деканата в навигацию Moodle
        $this->get_breadcrumbs_bar();
        
        // Отображение в зависимости от запрошенного формата
        switch ($mode)
        {
            // Режим "без окна" - шапка не печатается
            case NVG_MODE_FILE :
                break;
            // Версия для печати
            case NVG_MODE_PRINT :
                @header('Content-Type: text/html; charset=utf-8');
                echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">'
                        ."\n".'<head>'
                        ."\n".'<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />'
                        ."\n".'</head>'
                        ."\n".'<body class="user course-3 dir-ltr lang-ru_utf8" id="user-view">';
                // Вывод заголовка страницы
                echo $OUTPUT->heading($this->get_name());
                break;
            // Версия для всплывающего окна
            case NVG_MODE_POPUP :
                // Очистка всех заголовков
                $PAGE->set_heading('');
                $PAGE->set_focuscontrol('');
                $PAGE->set_cacheable(false);
                // Шапка страницы
                echo $OUTPUT->header();
                break;
            // Стандартная версия страницы
            case NVG_MODE_PORTAL :
                // Установка заголовка страницы
                $PAGE->set_heading($this->get_name());
                
                // Шапка страницы
                echo $OUTPUT->header();
                
                // Контент деканата
                echo '<div id="block_dof_content" class="block_dof block_dof_content block_dof_content_mode_portal">';
                
                // Начало трехколоночной таблицы
                echo '<table><tr>';
                
                if ( is_null($opt) ) 
                {// Позиция блоков не указана
                    $opt = 'left';
                }
                // Блоки
                $side = $this->get_blocks_cfg($opt);
                
                if( ! empty($side) )
                {// Блоки найдены
                    echo '<td class="block_dof_maintable_left">';
                    $this->print_blocks($side);
                    echo '</td>';
                }
                // Контент
                echo '<td class="block_dof_maintable_center">';
                // Контент страницы
                echo '<div class="block_dof_maintable_content_wrapper">';
                echo '<div class="block_dof_maintable_content">';
                break;
            // Стандартная версия страницы
            case NVG_MODE_PAGE :
            default:
                // Установка заголовка страницы
                $PAGE->set_heading($this->get_name());
                // Шапка страницы
                echo $OUTPUT->header();
                break;
       }
       // Заголовок уже распечатан 
       $this->headerprinted = true;
       
       return $this->headerprinted;
    }
    /** Получить код иконки сайта (появляется рядом с адресной строкой)
     * вставляется на всех страницах
     * 
     * @return string html-тег иконки для вставки в head
     */
    protected function get_favicon()
    {
        
        return "\n".'<link rel="shortcut icon" href="'.
                $this->dof->url_modlib('nvg', '/icons/favicon.gif').'" type="image/gif">'."\n";
    }
    /** Отобразить подвал страницы
    * @param int $mode - режим отображения
    * @return bool
    */
    public function print_footer($mode = NVG_MODE_PAGE, $opt = NULL)
    {
        global $OUTPUT;
        switch ($mode)
        {
            // Режим "без окна" - подвал не печатается
            case NVG_MODE_FILE :
                break;
            // Версия для печати
            case NVG_MODE_PRINT :
                echo '</body></html>';
                break;
            // Версия для всплывающего окна
            case NVG_MODE_POPUP :
                // Печать подвала
                echo $OUTPUT->footer('empty');
                break;
            // Стандартная версия страницы
            case NVG_MODE_PORTAL :
                
                // Контент страницы
                echo '</div>';
                
                // Шапка страницы с обязательными блоками
                echo '<div class="block_dof_maintable_top">';
                // Печать обязательных блоков шапки страницы
                $path = $this->dof->plugin_path('modlib', 'nvg', '/cfg/top.php');
                $tophtml = '';
                $tophtml .= $this->print_sections($path, ['returnhtml' => true]);
                $tophtml = $this->dof->messages->display(['returnhtml' => true]).$tophtml;
                print($tophtml);
                echo '</div></div>';
                echo '</td>';
                
                if ( is_null($opt) ) 
                {// Позиция блоков не указана
                    $opt = 'right';
                }
                // Блоки
                $side = $this->get_blocks_cfg($opt);
                
                if( ! empty($side) )
                {// Блоки найдены
                    echo '<td class="block_dof_maintable_right">';
                    $this->print_blocks($side);
                    echo '</td>';
                }
                
                // Конец трехколоночной таблицы
                echo '</tr></table>';
                
                // Вывод копирайта
                $this->print_copyright('small');
                
                echo '</div>';
                
                // Печать подвала
                echo $OUTPUT->footer();
            break;
            // Стандартная версия страницы
            case NVG_MODE_PAGE :
            default:
                // Вывод копирайта
                $this->print_copyright('small');
                // Печать подвала
                echo $OUTPUT->footer();
            break;
        }
        return true;
    }
    /** 
     * Отобразить секции
     * 
     * @param mixed $cfg - описание выводимых блоков (array), путь к конфигу с описанием или null по-умолчанию
     * @param $options - Опции отображения
     *          ['returnhtml'] => true - Вернуть html код вместо печати  
     * @return mixed
     */
    public function print_sections($cfg = null, $options = [])
    {
        $sections = [];
        // Получаем настройки отображаемых секций
        if ( is_null($cfg) )
        {
            $cfg = $this->dof->plugin_path('modlib', 'nvg', '/cfg/center.php');
        }
        if ( is_array($cfg) )
        {// Передали массив секций
            $sections = $cfg;
        } elseif ( is_string($cfg) )
        {// Путь до массива секций в конфиге
            if( ! file_exists($cfg) )
            {// Файл конфига не найден
                $this->dof->messages->add(
                    $this->dof->get_string('error_config_file_not_found', 'nvg', NULL, 'modlib'),
                    'error'
                );
                return '';   
            } else 
            {// ПОдключить файл конфигурации
                include $cfg;
            }
        }
        // Отображаем секции
        $html = '';
        if ( ! empty($sections) )
        {
            $html .= dof_html_writer::start_div('block_dof_sections'); 
        
            foreach ($sections as $section)
            {       
                if ( isset($section['name']) )
                {// Блок именной секции
                    $html .= dof_html_writer::start_div('block_dof_section block_dof_section_'.$section['name']);
                } else
                {// Блок неименованной секции
                    $html .= dof_html_writer::start_div('block_dof_section block_dof_section_unnamed');
                }
                if ( isset($section['title']) )
                {// Имеется заголовок 
                    $title = dof_html_writer::tag('strong', $section['title']);
                    $html .= dof_html_writer::div($title, 'block_dof_section_title');
                }
                // Получить контент секции
                $html .= $this->dof->im($section['im'])->get_section($section['name'],$section['id']);
                
                $html .= dof_html_writer::end_div();
                $html .= dof_html_writer::div('', 'block_dof_section_separator');
            }
        }
        $html .= dof_html_writer::end_div();
        
        if ( isset($opt['returnhtml']) && $opt['returnhtml'] === true )
        {// Возврат блока
            return $html;
        } else
        {// Печать блока
            print ( $html );
        }
    }
    /** Вывод инфо об ОТ, копирайтов и т.д.
     * @return string html-код выводящий всю эту информацию 
     */
    public function print_copyright($size='small')
    {
        global $CFG, $OUTPUT;
        $rez = '';
        if ($size != 'small')
        {//подробный вариант
            $rez .= '<br />'.$this->dof->get_string('project_site')
                        .'&nbsp;<a href="http://www.infoco.ru/course/view.php?id=19">
                        Free Dean\'s Office&nbsp;</a>';
            $rez .= '<br /><a href="'.$CFG->wwwroot.'/blocks/dof/credits.php">Dean\'s Office&nbsp;</a>';
            $rez .= '<br />'.$this->dof->get_string('version').':&nbsp;'.$this->dof->version_text();
            $rez .= '&nbsp;<a href="http://sourceforge.net/projects/freedeansoffice">
                    (build&nbsp;'.$this->dof->version().')</a>';
            $rez .= '<br />'.$this->dof->get_string('license').':&nbsp;<a href="'.
                        $CFG->wwwroot.'/blocks/dof/gpl.txt">GPL</a>';
            $OUTPUT->container_start();
            $OUTPUT->box_start('generalbox sitetopic');
            print '<strong>'.$this->dof->get_string('project_info').'</strong>';
            print $rez;
            $OUTPUT->box_end();
            $OUTPUT->container_end();
        }else
        {//короткий вариант 
            $rez .= '<a  href="'.$CFG->wwwroot.'/blocks/dof/credits.php">'
                .$this->dof->get_string('projectname').'</a>';
            print '<div style="font-size:xx-small;text-align:right;padding-bottom:0px;padding-top:3px;">'.$rez.'</div>';
        }
        return true;
    }
    
    /** Метод возвращает true если функция print_header уже отработала
     *  и возвращает false если этого еще не произошло
     * 
     * @return bool
     */
    public function is_header_printed()
    {
        return $this->headerprinted;
    }
    
    /** Установить url, по которому находится просматриваемая страница
     * Согласно стандарту Moodle 2 этот метод должен вызываться с каждой страницы
     * 
     * @param string $plugintype - тип плагина fdo 
     * @param string $plugincode - код плагина fdo 
     * @param string $adds - дополнительный путь внутри плагина
     * @param array $params[optional] - дополнительные get-параметры для ссылки 
     */
    public function set_url($plugintype, $plugincode, $adds='', $params=array())
    {
        global $PAGE;
        
        $callback = "url_$plugintype";
        $url = $this->dof->$callback($plugincode, $adds, $params);
        $url = new moodle_url($url, $params);
        return $PAGE->set_url($url);
    }
    /** Установить url, по которому находится просматриваемая страница
     * Функция-обертка чтобы указывать меньше параметров
     * 
     * @param string $plugincode - код im-плагина 
     * @param string $adds - дополнительный путь внутри плагина
     * @param array $params - дополнительные get-параметры для ссылки  
     * 
     */
    public function set_url_im($plugincode, $adds='', $params=array())
    {
        return $this->set_url('im', $adds, $params);
    }
    /*************************************************************/
    /******             Устаревшие функции                   *****/
    /****** Сохранены для совместимости со старыми плагинами *****/
    /*************************************************************/
    /** Получить строку с дополнительными мета-тегами (а также стилями и скриптами), 
     * которые нужно вставить в заголовок
     * @deprecated несовместимо с Moodle 2.2
     * 
     * @return string
     */
    protected function get_meta()
    {
        $this->dof->debugging('call to deprecated function modlib/nvg::get_meta()');
        // Добавляем к общему количеству meta-тегов стили moodle
        $styles = $this->get_styles();
        // Добавляем иконку сайта
        $styles .= $this->get_favicon();
        
        // собираем все подключенные ранее библиотеки в одну строку перед подключением
        foreach ( $this->meta as $plugintype => $plugincode )
        {
            foreach ( $plugincode as $plugincode => $code )
            {
                foreach ( $code as $code => $tag )
                {// Объединяем теги символом конца строки, чтобы исходник страницы было легче читать
                    $styles .= "\n\t".$tag;
                }
            }
        }
        
        return $styles;
    }
    /** Получить строку дополнительных свойств для тега body
     * @deprecated несовместимо с Moodle 2.2
     * 
     * @return string
     */
    protected function get_bodytags()
    {
        return $this->bodytags;
    }
    /** Строка свойств для атрибута body (полезно для добавления onload() и т. д.)
     * 
     * @deprecated несовместимо с Moodle 2.2
     * @return bool
     * @param string $tags - строка, которая будет добавлена внутрь тега body
     */
    protected function add_bodytags($tags)
    {
        $this->dof->debugging('call to deprecated function modlib/nvg::add_bodytags()');
        
        if ( ! is_string($tags) )
        {
            return false;
        }
        
        if ( $this->is_header_printed() )
        {// если заголовок уже распечатан - не пытаемся подключить никание стили, а сразу пишем об ошибке
            $errortags = htmlspecialchars(implode(', ', $tags));
            $this->dof->print_error('error:cannot_modify_bodytags', '', $$errortags, 'modlib', 'nvg');
        }
        
        $this->bodytags .= $tags;
        return true;
    }
    /** Получить строку со списком css-файлов, отвечающих за стили moodle
     * @deprecated несовместимо с Moodle 2.2
     * 
     * @return string 
     */
    protected function get_styles()
    {
        global $CFG;
        $styles = '';
        // создаем ссылку на файл стилей fdo
        $link = $CFG->wwwroot.'/blocks/dof/styles.php';
        // делаем ссылку тегом
        $styles .= '<link rel="stylesheet" type="text/css" href="'.$link.'" />';
        
        return $styles;
    }
    /** Добавить мета-теги к разделу head, оставив только уникальные
     * @deprecated несовместимо с Moodle 2.2
     * 
     * @return bool
     * 
     * @param string $plugintype - тип плагина, из которого подключается meta
     * @param string $plugincode - код плагина, из которого подключается meta 
     * @param string $meta       - тег, который нужно добавить
     * @param string $code [optional] - собственный код библиотеки в плагине, 
     *                                 или путь к библиотеке
     *                                 md5 от тега (если просто добавляется мета-тег)
     *                                 Требуется для того чтобы сохранить уникальность тега
     */
    public function add_meta($plugintype, $plugincode, $meta, $code=null)
    {
        $this->dof->debugging('call to deprecated function modlib/nvg::add_meta()');
        if ( ! $code )
        {// если код подключаемой библиотеки или мета-тега не задан - то возьмем его как md5 от самого тега
            $code = md5($meta);
        }
        
        if ( $this->is_header_printed() )
        {// если заголовок уже распечатан 
            if ( ! isset($this->meta[$plugintype][$plugincode][$code]) )
            {// и если библиотека не подключена - то сообщим об ошибке
                $metatext = htmlspecialchars($meta);
                $this->dof->print_error('error:cannot_include_scripts', '', $metatext, 'modlib', 'nvg');
            }else
            {// если подключена - то ничего не делаем, это значит что заголовок выведен 
                // со всеми нужными библиотеками, и все ОК
                return true;
            }
        }else
        // заголовок еще не выведен - добавляем библиотеку в список подключаемых
        $this->meta[$plugintype][$plugincode][$code] = $meta;
        
        return true;
    }
    
    /** Получить код для вставки в &lt;head&gt; js-библиотеки
     * @deprecated несовместимо с Moodle 2.2
     * 
     * @return string 
     * @param string $path - путь к js файлу
     */
    protected function create_js_tag($path)
    {
        return '<script type="text/javascript" src="'.$path.'"></script>';
    }
    
    /** Получить код для вставки в &lt;head&gt; css-библиотеки
     * @deprecated несовместимо с Moodle 2.2
     * 
     * @return string 
     * @param string $path - путь к css файлу
     */
    protected function create_css_tag($path)
    {
        return '<link rel="stylesheet" type="text/css" href="'.$path.'" />';
    }
    
    /**
     * Генерирует HTML-код, который необходимо добавить в тэг <head> на странице.
     *
     * Обычно, этот метод вызывается автоматически кодом, который печатает тэг <head>
     * и его не нужно вызывать вручную
     *
     * @param moodle_page $page
     * @param core_renderer $renderer
     * @return string HTML-код для тэга <head>
     */
    public function get_head_code($page = null, $renderer = null)
    {
        global $PAGE, $OUTPUT;
        if ( $page == null )
        {
            $page = $PAGE;
        }
        if ( $renderer == null )
        {
            $renderer = $OUTPUT;
        }
        return $PAGE->requires->get_head_code($page, $renderer);
    }
    
    /**
     * Генерирует HTML-код, который необходимо добавить в конец страницы.
     *
     * Обычно, этот метод вызывается автоматически кодом, который печатает подвал
     * и его не нужно вызывать вручную
     *
     * @return string HTML-код для подвала
     */
    public function get_end_code()
    {
        global $PAGE;
        return $PAGE->requires->get_end_code();
    }
    
    
    /**
     * Подключает строки перевода для JS, которые будут загружены во время использования кнопок
     *
     * @param array|object $identifiers - идентификаторы строк
     * @param string $component - компонент, где искать строки ('moodle')
     * @param mixed $a - дополнительные параметры, для подстановки в строках
     */
    public function strings_for_js($identifiers, $component, $a = null)
    {
        global $PAGE;
        $PAGE->requires->strings_for_js($identifiers, $component, $a);
    }
    
}
?>