<?php
/**
 * Класс, созданный для экспорта в csv
 * 
 * Алгоритм обработки:
 * 1. Находим первый массив объектов и возвращаем его имя.
 * 2. Возвращаем сам массив.
 * 3. Возвращаем объект с именами полей первой записи этого массива.
 * 4. Превращаем все записи массива объектов в массивы.
 * 5. Формируем из них текст.
 */
class dof_im_partners_templater_students extends dof_modlib_templater_package{
    /** Экземпляр объекта $DOF
     * @var object
     */
    var $dof;
    /**
     * @var object
     */
    var $plugintype;
    /**
     * @var object
     */
    var $pluginname;
    /**
     * @var object
     */
    var $templatename;
    /** Неформатированные данные для экспорта
     * @var object
     */
    var $data;
    /** Объекты для получения файла определенного типа
     * Формат объекта:
     * $formats->$type = dof_templater_format_$type
     */
    var $formats;
    /**
     * Конструктор класса
     * @param object $dof - объект $DOF, чтобы сделать его глобальным
     * @param object $templater - объект от класса dof_modlib_templater
     * @param string $plugintype - тип плагина
     * @param string $pluginname - имя плагина
     * @param object $obj - Данные для экспорта
     * @param string $templatename - имя шаблона
     */
    public function __construct($dof, $plugintype, $pluginname, $obj, $templatename=null)
    {
        $this->dof          = $dof;
        $this->plugintype   = $plugintype;
        $this->pluginname   = $pluginname;
        $this->templatename = $templatename;
        $this->set_data($obj);
        $this->formats = new stdClass;
        // подключаем необходимые классы
        require_once($this->dof->plugin_path('modlib','templater','/format.php'));
    
    }
    /**
     * Возвращает экземпляр класса для преобразования
     * данных в файл определенного типа
     * @param string $type - тип файла, в который надо превратить данные
     * @param object $options - дополнительные параметры
     * @return mixed - dof_modlib_templater_format -
     * объект dof_modlib_templater_format_$type
     * или false
     */
    public function create_format($type, $options = null)
    {
        if ( !$type OR ! is_string($type) )
        {// неизвестно, в какой формат экспортировать данные';
            return false;
        }
        $formats = $this->get_formats();
        if ( !in_array($type, $formats) )
        {// в списке поддерживаемых форматов запрашиваемый не значится';
            return false;
        }
        if ( isset($this->formats->$type) )
        {//уже создали объект для экспорта в такой тип файлов';
            return $this->formats->$type;//вернем его
        }
        // определим путь к подключаемому файлу
        $path = $this->format_path($type);
        
        if ( file_exists($path) )
        {// файл есть - подключаем';
            require_once($path);
        }else
        {// файла нет - сообщаем об этом';
            return false;
        }
        // определяем имя класса, занимающегося форматированием
        $classname = 'dof_im_partners_format_'.$type;
        if ( class_exists($classname) )
        {// класс с нужным названием есть в папке';
            //создаем его экземпляр и сохраняем его
            $this->formats->$type = new $classname($this->dof,
                    $this->plugintype, $this->pluginname, $this->templatename);
            return $this->formats->$type;
        }else
        {// в файле нет класса с нужным названием';
            return false;
        }
    }
    /**
     * Загрузить необработанные данные в объект.
     * @param object $obj - набор данных для вывода в файл
     * @return bool
     */
    private function set_data($obj)
    {
        $this->data = $obj;
        return true;
    }
    /**
     * Получить необработанный объект с данными.
     * @return object
     */
    private function get_data()
    {
        return $this->data;
    }
    /**
     * получить отформатированные данные,
     * пригодные для обработки функцией file_put_contents().
     * @param string $type — в какой формат экспортировать,
     * @param stdClass Object $options — дополнительные параметры.
     * @return string
     */
    public function get_file($type, $options=null)
    {
        if ( ! $exportclass = $this->create_format($type, $options) )
        {//нет класса для превращения данных в файл';
            return false;
        }
        // запихиваем неформатированные данные в обьект
        $exportclass->set_data($this->data);
        // вызываем функцию форматирования
        $contents = $exportclass->get_file($options);
        // возвращаем готовые данные
        return $contents;
    }
    /**
     * инициализировать передачу файла клиенту через браузер
     * @param $type — в какой формат экспортировать,
     * @param $options — дополнительные параметры.
     * @return void
     */
    public function send_file($type, $options=null)
    {
         
        $error = false;//якобы ошибок нет
        if ( ! $format = $this->create_format($type, $options) )
        {//нет объекта для экспорта данных в файл
            $error = $this->dof->get_string('error_export_format',
                    'templater', $type,'modlib');
        }
        if ( ! $file = $this->get_file($type, $options) )
        {//данные не получены
            //формируем сообщение об ошибке и отправляем его обратно
            $error = $this->dof->get_string('error_export_data',
                    'templater', null,'modlib');
        }
        if ( $error )
        {//есть ошибки - сообщим об этом';
            //и покажем ссылку 'назад'
            //получаем путь без параметров в нем
            $cleanurl = explode  ('?', $_SERVER['HTTP_REFERER']);
            //формируем ссылку
            $backward = '<a href="'.$cleanurl[0].'">'
                    .$this->dof->get_string('backward', 'templater', null,'modlib')
                    .'</a>';
            //формируем страницу с шапкой и подвалом
            $this->dof->modlib('nvg')->print_header(NVG_MODE_PORTAL);
            print '<p align="center">'.$error.'<br /><br />'.$backward.'</p>';
            $this->dof->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
        }else
        {//все нормально, отправляем файл';
             
            $format->send_headers($options);
            print $file;
        }
    }
    /**
     * список доступных форматов для этого документа
     * сканируем папку modlibs/templater/formats и получаем список форматов,
     * в которые мы можем экспортировать
     * сканируем папку внешнего плагина (или свою?) на предмет наличия шаблонов файла для экспорта
     * типы, для которых шаблон не найден - исключаем
     * возвращаем массив с типами файлов, в которые возможен экспорт
     *
     * @return array $formats - список доступных форматов для экспорта
     */
    protected function get_formats()
    {
        $formats     = array();
        $formatspath = $this->template_path('formats', false);
        if ( is_dir($formatspath) )
        {// ищем папку с форматами
            $folderstypes = scandir($formatspath);
        }else
        {// ошибка - указанная директория не найдена
            return false;
        }
        foreach ($folderstypes as $folder)
        {// перебираем папки, отчищая их от расширения и служебных папок, чтобы получить список форматов файлов
            if ( ! preg_match('/\./', $folder) )
            {// если это не служебные ссылки на верхний каталог, не файлы и не удаленные файлы
                if ( is_dir($formatspath.'/'.$folder) AND
                        is_file($formatspath.'/'.$folder.'/init.php'))
                {// еще раз проверим, что файл является директорией,
                    // и в ней есть соответствующий файл,
                // и после этого запихнем его в список форматов
                $formats[] = $folder;
                }
    
            }
        }
        return $formats;
    }
    /**
     * Задает путь к шаблону
     * (работает через класс dof_modlib_templater)
     * @return string
     */
    protected function template_path($adds=null, $fromplugin=null)
    {
        return $this->dof->modlib('templater')->
        template_path($this->plugintype, $this->pluginname, $this->templatename, $adds, $fromplugin);
    }
    /**
     * Возвращает путь к файлу, в котором лежит контейнер
     * во внешнем плагине или внутри templater
     * @param mixed string - путь к файлу init.php
     * или bool false, если файла нет в templater
     */
    private function format_path($type)
    {
        //формируем путь к package внешнего плагина
        $extpath = $this->template_path($type.'/init.php',true);

        if ( $extpath )
        {//файл есть - возвращаем
            return $extpath;
        }
        //формируем путь к собственному package
        return $this->template_path('formats/'.$type.'/init.php',false);
    }
}?>