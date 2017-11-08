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

/** Класс-контроллер (компоновщик)
 */
class dof_control
{
    /** Конфигурационный файл moodle
    * @var string
    */
    protected $moodlecfg;
    
    /** Базовый путь в папку деканата
    * @var string
    */
    protected $pathbase;
    
    /** Базовый URL в папку деканата
    * @var string
    */
    protected $urlbase;
    
    /** Кеш созданный объектов
     * @var array
     */
    protected $pluginsobj;
    
    /**
     * Обработчик уведомлений системы
     * 
     * @var dof_messages - Обработчик уведомлений
     */
    public $messages;
    
    public function version()
    {
        return 2016052500;
    }
    /** Возвращает версию установленного плагина
     * @return string
     * @access public
     */
    public function version_text()
    {
        return '2.7.4';
    }
    /** Возвращает версию интерфейса класса dof_control
     * @return string
     * @access public
     */
    public function compat()
    {
        // Совместимость снизу-вверх обеспечивается частичным сравнением строк
        // плагин "требует" версию ядра не ниже той, которая в нем указана.
        // Например: плагин aquarium_a, совместим с ядром aquarium_ab
        // но не совместим с ядром aquarium_b
        return 'aquarium_bcd';
    }
    /** Возвращает ожидаемую деканатом версию интерфейса плагина
     * @return string
     * @access public
     */
    public function plugin_compat($type)
    {
        // Совместимость снизу-вверх обеспечивается частичным сравнением строк
        // система "требует" версию интерфейса плагина не ниже той, которая здесь указана
        // Например: плагин angelfish_a совместим с ядром angelfish или angelfish_a,
        // но не совместим с ядром angelfish_b
        switch ($type)
        {
            case 'im':
                return 'angelfish';
            break;
            case 'sync':
                return 'ancistrus';
            break;
            case 'storage':
                return 'paradusefish';
            break;
            case 'workflow':
                return 'guppy_a';
            break;
            case 'modlib':
                return 'neon';
            break;
            default:
                return 'errortype';
            break;
        }
    }
    /** Mетод для получения объекта типа storage и использования его методов
    * @param string $code задает имя плагина, который надо использовать
    * @return dof_storage объект - экземпляр указанного класса 
    * @access public
    */
    public function storage($code)
    {
        return $this->plugin('storage',$code);
    }
    /** Mетод для вызова объекта типа im и использования его методов
    * @param string $code задает имя плагина, который надо использовать
    * @access public
    * @return dof_plugin_im объект - экземпляр указанного класса 
    */
    public function im($code)
    {
        return $this->plugin('im',$code);
    }
    /** Mетод для вызова объекта типа modlib и использования его методов
    * @param string $code задает имя плагина, который надо использовать
    * @access public
    * @return dof_plugin_modlib объект - экземпляр указанного класса 
    */
    public function modlib($code)
    {
        return $this->plugin('modlib',$code);
    }    
    /** Mетод для вызова объекта типа workflow и использования его методов
    * @param string $code задает имя плагина, который надо использовать
    * @access public
    * @return dof_workflow объект - экземпляр указанного класса 
    */
    public function workflow($code)
    {
        return $this->plugin('workflow',$code);
    }
    /** Mетод для вызова класса типа sync и использования его методов
    * @param string $code задает имя плагина, который надо использовать 
    * @access public
    * @return dof_sync объект - экземпляр указанного класса
    */
    public function sync($code)
    {
        return $this->plugin('sync',$code);
    }
    /** Mетод для вызова класса плагина
    * @param string $type задает тип плагина, который надо использовать
    * @param string $code задает имя плагина, который надо использовать
    * @return dof_plugin - экземпляр указанного класса 
    * @access public
    */
    public function plugin($type,$code)
    {
        if ( ! isset($this->pluginsobj[$type][$code]) )
        {
            $this->pluginsobj[$type][$code] = $this->plugin_init($type,$code);
        }
        return $this->pluginsobj[$type][$code];
    }
    /** Метод возвращает список зарегистрированных плагинов указанного типа
    * @param string $type - тип плагинов, которые надо вернуть 
    * @return array массив записей из таблицы регистрации плагинов
    * @access public
    */
    public function plugin_list($type)
    {
        GLOBAL $DB;
        $rez = array();
        //получим все плагины этого типа
        $conditions = array('type' => $type);
        $ar = $DB->get_records('block_dof_plugins', $conditions);
        if ( ! $ar )
        {// нет плагинов этого типа
            $ar = array();//вернем пустой массив
        }
        foreach ( $ar as $obj )
        {//формируем массив нужной структуры
            $rez[$obj->code] = array('type'    => $obj->type, 
                                     'code'    => $obj->code,
                                     'version' => $obj->version,
                                     'cron'    => $obj->cron);
        }
        return $rez;
    }
    /** Возварщает список всех установленных в 
     * систему плагинов
     * @retuen array
     */
    function plugin_list_all()
    {
        return array(//получили папки всех плагинов
                     'im'       => $this->plugin_list('im'), 
                     'storage'  => $this->plugin_list('storage'),
                     'workflow' => $this->plugin_list('workflow'),
                     'sync'     => $this->plugin_list('sync'),
                     'modlib'   => $this->plugin_list('modlib'));

    }
    /** Метод возвращает список доступных плагинов указанного типа
     * Т.е. возвращает список папок внутри папки указанного типа 
    * @param string $type - тип плагинов, которые надо вернуть 
    * @return array массив записей из таблицы регистрации плагинов
    * @access public
    */
    public function plugin_list_dir($type)
    {
        $rez = array();
        // сформировали путь к папке плагинов
        $dir = $this->plugin_path($type);
        $content = scandir($dir);//получили ее содержимое
        foreach( $content as $key => $var )
        {//просматриваем и удаляем все что не нужно 
            if( ! is_dir($dir.'/'.$var)// это не каталог
                OR '.' === $var[0]// имя начинается с точки
                OR strcasecmp('cvs', $var) == 0 )//это служебная папка 
            {//удаляем из массива
                unset($content[$key]);
            }
        }
        foreach ( $content as $val )
        {
            $rez[$val] = array('code'    => $val, 
                               'type'    => $type,
                               'version' => $this->plugin($type, $val)->version());
        }
        return $rez;
    }
    /** Возвращает список всех папок всех плагинов 
     * return array
     */
    public function plugin_dir_all()
    {
        return array(//получили папки всех плагинов
                     'im'       => $this->plugin_list_dir('im'), 
                     'storage'  => $this->plugin_list_dir('storage'),
                     'workflow' => $this->plugin_list_dir('workflow'),
                     'sync'     => $this->plugin_list_dir('sync'),
                     'modlib'   => $this->plugin_list_dir('modlib'));
    }
    /** Метод установки и обновления всех плагинов.
     * Учитывает записимости плагинов друг от друга.
     * return bool
     */
    public function plugin_setup()
    {
        // Снимаем лимит на ресурсы
        dof_hugeprocess();
        // Получаем список всех доступных плагинов
        $dirlist = $this->plugin_dir_all();
        // Счетчик, прекращающий итерации (защита от зависания)
        $counter = 100;
        // var_dump($dirlist);
        // var_dump($installlist);
        // В цикле определяем, сколько плагинов мы можем установить в этот проход, ставим, пока есть что ставить
        do
        {
            // Получаем список всех установленных плагинов
            $installlist = $this->plugin_list_all();
            // Готовим пустой массив с плагинами для установки и обновления
            $toinstalllist = array();
            $toupgradelist = array();
            // Перебираем типы
            foreach ($dirlist as $typelist)
            {
                // Перебираем плагины
                foreach ($typelist as $plugin)
                {
                    // Требуется ли установка
                    if ( isset($installlist[($plugin['type'])][($plugin['code'])])
                            AND $installlist[($plugin['type'])][($plugin['code'])]['version'] < $plugin['version'] )
                    {
                        // Плагин установлен, но требуется обновление
                        if ( method_exists($this->plugin($plugin['type'],$plugin['code']),'is_setup_possible')
                                AND !$this->plugin($plugin['type'],$plugin['code'])->
                                    is_setup_possible($installlist[($plugin['type'])][($plugin['code'])]['version']) )
                        {
                            // Обновление невозможно - продолжаем перебор
                            continue;    
                        }
                        $toupgradelist[] = $plugin;
                    }elseif (!isset($installlist[($plugin['type'])][($plugin['code'])]))
                    {
                        // Плагин не установлен
                        if ( method_exists($this->plugin($plugin['type'],$plugin['code']),'is_setup_possible')
                                AND ! $this->plugin($plugin['type'],$plugin['code'])->is_setup_possible(NULL) )
                        {
                            // Установка невозможна - продолжаем перебор
                            continue;    
                        }
                        // Плагин не установлен
                        $toinstalllist[] = $plugin;
                    }
                }
            }
            // Выполняем обновление до первой ошибки
            foreach ($toupgradelist as $plugin)
            {
                echo "\n<br />Upgrade {$plugin['type']}/{$plugin['code']}";
                $this->mtrace(2,"Upgrade {$plugin['type']}/{$plugin['code']}");
                if (!$this->plugin_upgrade($plugin['type'], $plugin['code']))
                {
                    return false;
                }
                
            }
            // Выполняем установку до первой ошибки
            foreach ($toinstalllist as $plugin)
            {
                $this->mtrace(2,"Install {$plugin['type']}/{$plugin['code']}");
                if (!$this->plugin_install($plugin['type'], $plugin['code']))
                {
                    return false;
                }
            }
            // Уменьшаем количество оставшихся итераций 
            --$counter;
            // Продолжим, если за этот ход хоть чего-нибудь установили или обновили 
        } while ( ! (empty($toinstalllist) AND empty($toupgradelist))  AND $counter>0 );
        return true;
        
    }
    /** Метод устанавливает плагин в системе
     * получаем список установленных плагинов, получаем список папок плагинов
     * еще не установленные - устанавливаем, те что надо обновить - обновляем
     * те что надо удалить - удаляем 
     * @param string $type - тип плагина (один из заданных) 
     * @param string $code - название плагина. Должно быть уникально для данного типа плагинов 
     * @return (bool|int) - id записи в таблице dof_pluguns если плагин был установлен успешно или false
     *                      в случае ошибки
     * @access public
     */
    public function plugin_install($type, $code)
    {
        GLOBAL $DB;
        $conditions = array('type' => $type, 'code' => $code);
        if ( $DB->record_exists('block_dof_plugins', $conditions) )
        {//плагин с таким типом и именем уже используется
            print_error("plugin already installed! type:{$type} code:{$code}");
        }
        //проверим совместимость плагина с системой
        $this->plugin_check($type, $code);
        // проверим, не мешает ли нам что-то в установке плагина
        if ( method_exists($this->plugin($type,$code),'is_setup_possible')
                AND !$this->plugin($type,$code)->is_setup_possible(NULL) )
        {// Установка невозможна
            return false;   
        }
        //вызываем родной установщик плагина
        if ( ! $this->plugin($type, $code)->install() )
        {//установка не прошла
            return false;
        }
        // собираем информацию для события установки плагина
        $plugin = $this->create_plugindata_object($code, $type, $this->plugin($type, $code)->version());
        $mixedvar = array('new' => $plugin);
        // после успешной регистрации отсылаем событие об установке плагина
        if ( ! $this->send_event('core', 'core', 'plugin_install', null, $mixedvar) )
        {
            return false;
        }
        //регистрируем в базе плагинов; 
        return $this->plugin_insertrec($type, $code);
   }
    /** Метод обновляет плагин в системе
    * @param string $type - тип плагина (один из заданных)
    * @param string $code - название плагина. Должно быть уникально для данного типа плагинов 
    * @return bool - true в случае успешного обновления плагина и false если плагин обновить не удалось
    * @access public
    */
    public function plugin_upgrade($type, $code)
    {
        GLOBAL $DB;
        //проверим совместимость плагина с системой
        $this->plugin_check($type, $code);
        $conditions = array('type' => $type, 'code' => $code);
        $oldver = $DB->get_field('block_dof_plugins', 'version', $conditions);
        // проверим, не мешает ли нам что-то в обновлении плагина
        if ( method_exists($this->plugin($type,$code),'is_setup_possible')
                AND !$this->plugin($type,$code)->is_setup_possible($oldver) )
        {// Обновление невозможно
            return false;    
        }
        if ( ! $this->plugin($type, $code)->upgrade($oldver) )
        {
            print_error($type.':'.$code.' Во время обновления плагина произошла ошибка');
        }else
        {
            // собираем информацию для события установки плагина
            $oldplugin = $this->create_plugindata_object($code, $type, $oldver);
            $newplugin = $this->create_plugindata_object($code, $type, $this->plugin($type, $code)->version());
            $mixedvar  = array('old' => $oldplugin, 'new' => $newplugin);
            // обновляем информацию в таблице плагинов
            if ( ! $this->send_event('core', 'core', 'plugin_upgrade', null, $mixedvar) )
            {
                print_error('Не удалось выполнить все события при обновлении плагина');
            }
            // Плагин успешно обновлен - посылаем событие
            return $this->plugin_updaterec($type, $code);
        }
    }
    /** Метод удаляет плагин из системы
    * @param string $type - тип плагина (один из заданных)
    * @param string $code - название плагина. Должно быть уникально для данного типа плагинов 
    * @return bool - true в случае успешного удаления плагина и false если плагин удалить не удалось
    * @access public
    */
    public function plugin_uninstall($type, $code)
    {
        /** проверяем наличие плагинов, зависящих от удаляемого плагина
         *  нашли - удалять нельзя; 
         *  не нашли - удаляем все таблицы плагина
         */
        //получаем все установленные в системе плагины
        $listplug = $this->plugin_list_all();
        // собираем информацию для события удаления плагина
        $plugin = $this->create_plugindata_object($code, $type, $this->plugin($type, $code)->version());
        $mixedvar = array('old' => $plugin);
        //перебираем их
        //ищем среди нужных им плагинов наш плагин
        foreach($listplug as $ptype=>$v)
        {//перебор типов плагинов
            foreach($v as $pcode=>$plug)
            {//перебор плагинов одного типа
                //получаем список плагинов, от которых зависит очередной плагин
                $depend = $this->plugin($ptype, $pcode)->need_plugins();
                if ( ! array_key_exists($type, $depend) )
                {//среди плагинов, нужных для работы плагина name->code, удаляемого плагина нет
                    continue;//переходим к следующему плагину
                }
                if ( array_key_exists($code, $depend[$type]) )
                {//он зависит от удаляемого плагина 
                    //плагин удалять нельзя
                    print_error('Uninstall impossible!<br />This plugin need for 
                                another plugins<br />
                                type: '.$ptype.' code: '.$pcode); 
                }
            }
        }
        //наш плагин никому не нужен :(
        //удаляем все сведения из таблицы dof_events
        if ( ! $this->delete_recipient($type, $code) )
        {//не удалось удалить все записи о прослушиваемых им событиях
            print_error('Can\'t to delete records about events for plugin!');
        }
        // Вызываем собственный метод удаления плагина
        if ( method_exists($this->plugin($type, $code),'uninstall') )
        {
            if ( ! $this->plugin($type, $code)->uninstall() )
            {//удаление плагина не удалось
                return false;
            }
        }
        // посылаем событие о том что плагин удален
        if ( ! $this->send_event('core', 'core', 'plugin_uninstall', null, $mixedvar) )
        {
            return false;
        }
        //удаляем сведения о регистрации плагина
        return $this->plugin_removerec($type, $code);
    }
    /** Экземпляр класса указанного плагина
    * @param string $type - тип плагина
    * @param string $code - название плагина
    * @return dof_plugin - экземпляр класса плагина
    * @access public
    */
    protected function plugin_init($type,$code)
    {
        $DOF = $this;
        // Подключаем файл инициализации
        $path = $this->plugin_path($type,$code).'/init.php';
        if ( ! is_file($path) )
        {
            print_error("File doesn`t exists! ({$path})");
        }
        include_once($path);
        // Создаем экземпляр класса
        $classname = "dof_{$type}_{$code}";
        if ( ! class_exists($classname) )
        {
           // Класс не существует
            print_error("Class doesn`t exists! ({$classname})");
        }
        // Создаем экземпляр класса, передаем ему ссылку на себя и возвращаем объект
        return new $classname($this);
    }
    /** Проверяет совместимость плагина с системой
     * перед установкой или обновлением
     * @param string $type - тип плагина
     * @param string - $code - уникальное имя плагина
     * @return bool true или прерывает исполнение после вывода сообщения об ошбке
     * 
     */
    private function plugin_check($type, $code)
    {
        // Ядро должно быть той версии, которую запрашивает плагин, или выше
        if ( false === strpos($this->compat(),$this->plugin($type, $code)->compat_dof()) )
        {// этот плагин несовместим с текущей версией деканата
            print_error('plugin version uncompatible with deansoffice version');
        }
        // Плагин должен быть той версии, которую запрашивает ядро, или выше
        if ( false === strpos($this->plugin($type, $code)->compat(),$this->plugin_compat($type)) )
        {// неправильная версия плагина
            print_error('deansoffice version uncompatible with plugin version');
        }
        $needplugins = $this->plugin($type, $code)->need_plugins();//какие плагины ему нужны
        $dirpluglist = $this->plugin_dir_all();//какие плагины есть вообще
        //проверяем наличие нужных, для нашего плагина, плагинов
        foreach ( $needplugins as $key => $names )
        {//перебираем типы необходимых плагинов
            if ( ! array_key_exists($key, $dirpluglist) )
            {// невозможный тип плагина
              print_error("uncompatible plugin type: {$key}");
            }
            foreach ( $names as $name => $needver )
            {//перебираем имена плагинов
                $plugver = $this->plugin($key, $name)->version();//версия, которую имеет нужный плагин
                if ( ! array_key_exists($name, $dirpluglist[$key]) OR $plugver < $needver)
                {//нужный плагин не найден
                    print_error("needed plugins not found: {$name}");
                }
            }
        }
        return true;
    }
    /** Путь к плагину
    * @param string $type - тип плагина
    * @param string $code [optional] - название плагина
    * @param string $addpath [optional] - путь внутри папки плагина
    * @return string - путь к папке с плагином 
    * @access public
    */
    public function plugin_path($type, $code=null, $addpath=null)
    {
        // Проверяем формат имени плагина
        // Цифры нельзя, потому что так же сделано в плагинах moodle
        // и PARAM_ALPHA их не пропускает
        if (
                ( is_null($code) AND $addpath )
                OR  
                ( ! is_null($code) AND !dof_checkcode($code) )
            )
        {
            // Ошибка: неправильное имя плагина,
            // или не задан код в плагине, но задан путь внутри плагина
            print_error("Wrong module name ({$type}/{$code}+{$addpath}) string!");
        }

        // Получаем базовый путь
        $path = $this->pathbase;
        // Проверяем тип плагина
        switch ($type)
        {
            case 'im':
                $path .= '/im';
            break;
            case 'modlib':
                $path .= '/modlibs';
            break;
            case 'sync':
                $path .= '/sync';
            break;
            case 'storage':
                $path .= '/storages';
            break;
            case 'workflow':
                $path .= '/workflows';
            break;
            default:
                error('Wrong module type: '.$type);
            break;
        }
        if ( !is_null($code) )
        {
            $path .= '/'.$code;
        }
        if ( ! is_dir($path) )
        {
            print_error("Module doesn`t exists! ({$type}:{$code})");
        }
        if ( ! is_null($addpath) )
        {
            if( dof_strbeginfrom($addpath,'/cfg/' )
                    AND file_exists($altpath = $this->moodlecfg->dataroot."/dof/cfg/{$type}/{$code}/".mb_substr($addpath,5)) )
            {
                // Существует альтернативный конфигурационный файл - возвращаем путь к нему
                $path = $altpath;
            }elseif ( dof_strbeginfrom($addpath,'/dat/') )
            {
                // Возвращаем путь к папке модуля внутри moodledata (если папка модуля отсутствовала - создаем, все что после имени плагина - автоматически не создается)
                $path = $this->moodlecfg->dataroot."/dof/dat/{$type}/{$code}/".mb_substr($addpath,5);
                // Создаем папку в соответствии с путем, за исключением имени файла, если путь не заканчивается на слеш
                if ( '/'===mb_substr($addpath,-1) )
                {
                    // Добавляем что-нибудь к имени файла - все-равно dirname это откусит
                    // Создаем недостающие папки
                    check_dir_exists(dirname($path.'111'),true,true);
                }else
                {
                    // Ничего не добавляем - в конце имя файла, которое нужно откусить
                    // Создаем недостающие папки
                    check_dir_exists(dirname($path),true,true);
                }
            }elseif ( dof_strbeginfrom($addpath,'/tmp/') )
            {
                // Возвращаем путь к папке модуля в moodledata/temp (если папка модуля отсутствовала - создаем, все что после имени плагина - автоматически не создается)
                $path = $this->moodlecfg->dataroot."/temp/dof/{$type}/{$code}/".mb_substr($addpath,5);
                // Создаем папку в соответствии с путем, за исключением имени файла, если путь не заканчивается на слеш
                if ( '/'===mb_substr($addpath,-1) )
                {
                    // Добавляем что-нибудь к имени файла - все-равно dirname это откусит при создании папки
                    // Создаем недостающие папки
                    check_dir_exists(dirname($path.'111'),true,true);
                }else
                {
                    // Ничего не добавляем - в конце имя файла, которое нужно откусить при создании папки
                    // Создаем недостающие папки
                    check_dir_exists(dirname($path),true,true);
                }
                // Если требуется создать уникальный временный файл и вернуть путь к нему
                if ( '/tmp/tempnam'===$addpath )
                {
                    // dirname откусывает tempnam в конце
                    $path = tempnam(dirname($path),'tmp_');
                }
                // echo "<br />После вычеслений: {$path}  {$addpath}";
            }else
            {
                // Не используются специальные пути
                $path .= $addpath;
            }
        }
        // Возвращаем результат
        // clean_filename() не используем, так как она предназначена только для имен файлов, а не путей   
        return $path;
    }
    /** URL плагина im
     * @param string $code - название плагина
     * @param string $adds [optional] - фрагмент пути внутри папки плагина
     * начинается с /. Например '/index.php'
     * @param array $vars [optional] - параметры, передаваемые вместе с url
     * @return string - путь к папке с плагином 
     * @access public
     */
    public function url_im($code, $adds='', $vars=array())
    {
        return $this->url('im', $code, $adds, $vars);
    }
    /** URL плагина modlib (для загрузки JavaScript библиотек и т.п.)
     * @param string $code - название плагина
     * @param string $adds [optional] - фрагмент пути внутри папки плагина
     * начинается с /. Например '/index.php'
     * @param array $vars [optional] - параметры, передаваемые вместе с url
     * @return string - путь к папке с плагином 
     * @access public
     */
    public function url_modlib($code, $adds='', $vars=array())
    {
        return $this->url('modlibs', $code, $adds, $vars);
    }
    /** URL плагина sync (для обращения через SOAP-запросы и т. п.)
     * @param string $code - название плагина
     * @param string $adds [optional] - фрагмент пути внутри папки плагина
     * начинается с /. Например '/index.php'
     * @param array $vars [optional] - параметры, передаваемые вместе с url
     * @return string - путь к папке с плагином 
     * @access public
     */
    public function url_sync($code, $adds='', $vars=array())
    {
        return $this->url('sync', $code, $adds, $vars);
    }
    
    /** Базовый метод получения url для плагина любого типа
     * @param string $code - название плагина
     * @param string $adds[optional] - фрагмент пути внутри папки плагина
     * начинается с /. Например '/index.php'
     * @param array $vars[optional] - параметры, передаваемые вместе с url
     * @return string - путь к папке с плагином 
     * @access protected
     */
    protected function url($type, $code, $adds='', $vars=array())
    {
        // url можно получать только для плагинов sync, modlib, и конечно im
        if ( ! in_array($type, array('im', 'modlibs', 'sync')) )
        {
            return false;
        }
        if ( is_object($vars) )
        {// на случай, если передали объект
            $vars = (array)$vars;
        }
        // Moodle 2.x fix: убираем из переданных данных все массивы
        // В Moodle 1.9 переданые значения типа "массив" игнорировались
        // а начиная со второй версии вызывают критическую ошибку, поэтому фильтруем их здесь
        $filteredvars = array();
        foreach ( $vars as $key => $value )
        {
            if ( is_scalar($value) )
            {
                $filteredvars[$key] = $value;
            }
        }
        $url = $this->urlbase."/".$type."/{$code}{$adds}";
        // используем moodle url 
        $uri = new moodle_url($url, $filteredvars);
        // возврат url
        return $uri->out(false);
    }
    /** Возвращает строку перевода из файла локализации
    * Если $plugin_name и $plugin_type не указаны, то перевод ищется в локализации Деканата.
    * Если указаны, то в файле локализации плагина.
    *
    * @param string $identifier - идентификатор строки, которая хранит перевод 
    * @param string $pluginname [optional] - название плагина
    * @param object $a [optional] - объект, который хранит значения, вставляемые в перевод строки
    * @param string $plugintype [optional] - тип плагина
    * @return string строка перовода
    * @access public
    */
    public function get_string($identifier, $pluginname = NULL, $a = NULL, $plugintype = 'im')
    {
        // Получаем имя текущего языка
        $lang = current_language();
        $resultstring = '';
        // Задаем имя файла с переводом
        if ( is_null($pluginname) OR $pluginname==='core' OR $plugintype==='core' )
        {    // Ищем в переводе блока
            $langfile = 'block_dof';
        }else
        {
            // Перевод для плагина лежит в отдельном файле
            $langfile = 'block_dof_'."{$plugintype}_{$pluginname}";
            // Использование параметра $extralocations прямо запрещено в Moodle 2.x
            // Перемещать все языковые строки в один файл мы не будем, поэтому 
            // извлекаем строки сами
            $path = $this->plugin_path($plugintype, $pluginname, "/lang/");
            
            if ( $result = $this->get_string_from_file($identifier, $path.'ru/'.$langfile.'.php', "\$resultstring") )
            {
                eval($result);
                return $resultstring;
            }else
            {
                return '[['.$identifier.']]';
            }
            
        }
        // Возвращаем строку из языкового файла
        return get_string($identifier, $langfile, $a);
    }
    /** Получить строку из языкового файла
     * Эта функция нужна для того чтобы избежать ошибок "OBJECT COUD NOT BE CONVERTED TO STRING" 
     * при подключении языковых файлов
     * This function is only used from {@link get_string()}.
     *
     * @internal Only used from get_string, not meant to be public API
     * @param string $identifier ?
     * @param string $langfile ?
     * @param string $destination ?
     * @return string|false ?
     * @staticvar array $strings Localized strings
     * @access private
     * @todo Finish documenting this function.
     */
    protected function get_string_from_file($identifier, $langfile, $destination)
    {
        @include($langfile);
        if ( ! isset($string[$identifier]))
        {
            return false;
        }
    
        return $destination .'= sprintf("'. $string[$identifier] .'");';
    }
    
    /** Проверяет полномочия на совершение действий
     * @param string $do - идентификатор действия, которое должно быть совершено
     * @param int $idobj [optional] - идентификатор экземпляра объекта, 
     * по отношению к которому это действие должно быть применено
     * @param int $userid [optional] - идентификатор пользователя, полномочия которого проверяются
     * @return bool true - можно выполнить указанное действие по отношению к выбранному объекту
     * false - доступ запрещен
     */
    public function is_access($do, $idobj = NULL, $userid = NULL)
    {
        // Идентификатор объекта на главной странице игнорируем, полномочия проверяем
        // по главной странице в системе ролей moodle.
        if ( class_exists('context_course') )
        {// начиная с moodle 2.6
            global $SITE;
            return has_capability('block/dof:'.$do, context_course::instance($SITE->id),$userid);
        }else
        {// оставим совместимость с moodle 2.5 и менее
            return has_capability('block/dof:'.$do, get_context_instance(CONTEXT_COURSE, SITEID),$userid);
        }
    }
    /** Требует наличия полномочия на совершение действий
     * @param string $do - идентификатор действия, которое должно быть совершено
     * @param int $idobj [optional] - идентификатор экземпляра объекта, 
     * по отношению к которому это действие должно быть применено
     * @param int $userid [optional] - идентификатор пользователя, полномочия которого проверяются
     * @return bool true - можно выполнить указанное действие по отношению к выбранному объекту
     * false - доступ запрещен
     */
    public function require_access($do, $idobj = NULL, $userid = NULL)
    {
        // проверим, не включен ли режим обслуживания
        if ( $this->is_maintenance() )
        {// используем прямое обращение к функции moodle чтобы вывести сообщение
            print_maintenance_message();
            exit;
        }
        // Идентификатор объекта на главной странице игнорируем, полномочия проверяем
        // по главной странице в системе ролей moodle.
        // return require_capability('block/dof:'.$do, get_context_instance(CONTEXT_COURSE, SITEID),$userid);
        if ( ! $this->is_access($do, $idobj, $userid) )
        {
            $notice = "core/{$do} (block/dof:{$do})";
            if ($idobj){$notice.="#{$idobj}";}
            $this->print_error('nopermissions','',$notice);
            
        }
    }
    /** Вывести сообщени об отказе в доступе и завершить исполнение программы
     *
     * @param string $errorcode [optional] - код строки в языковом файле плагина
     * @param string $link [optional] - ссылка на возврат
     * @param object $a [optional] - объект с дополнительными данными для подстановки в языковыю строку
     * @param string $plugintype [optional] - тип плагина в котором находится языковая строка с сообщением об ошибке
     *                             или core если языковые строки берутся из ядра
     * @param string $plugincode [optional] - код плагина в котором аходится языковая строка с сообщением об ошибке
     * 
     * @return null
     */
    public function print_error($errorcode='',$link='',$a=NULL,$plugintype='core',$plugincode=NULL)
    {
        global $CFG, $SESSION, $THEME, $OUTPUT;

        $message = $this->get_string($errorcode, $plugincode, $a, $plugintype);

        if ( empty($link) and !defined('ADMIN_EXT_HEADER_PRINTED') )
        {
            if ( ! empty($SESSION->fromurl) )
            {
                $link = $SESSION->fromurl;
                unset($SESSION->fromurl);
            }else
            {
                $link = $CFG->wwwroot .'/';
            }
        }
        
        // Добавляем сообщение в логи
        $this->add_to_log($plugintype,$plugincode,'error_'.$errorcode, $link, $message);

        $errordocroot = 'http://docs.deansoffice.ru';

        if ( defined('FULLME') && FULLME == 'cron' )
        {
            // Errors in cron should be mtrace'd.
            $this->mtrace(3, $message);
            die;
        }

        if ( $plugintype AND $plugincode )
        {
            $errorlink = "{$errordocroot}/ru/error/{$plugintype}/{$plugincode}/{$errorcode}";
        }elseif ( ! $plugintype or $plugintype==='core' )
        {
            $errorlink = "{$errordocroot}/ru/error/core/{$errorcode}";
        }

        $message = clean_text('<p class="errormessage">'.$message.'</p>'.
               '<p class="errorcode">'.
               "<a href=\"{$errorlink}\">".
        get_string('moreinformation').'</a></p>');

        if ( ! headers_sent() )
        {
            GLOBAL $PAGE;
            //header not yet printed
            @header('HTTP/1.0 404 Not Found');
            $PAGE->set_title(get_string('error'));
            $PAGE->set_url($link);
            echo $OUTPUT->header();
        }else
        {// Закрываем все незакрытые теги перед выводом ошибки
            $OUTPUT->container_end_all();
        }
        
        echo '<br />';

        echo $OUTPUT->box($message, 'errorbox');

        $this->debugging('Stack trace:', DEBUG_DEVELOPER);

        // in case we are logging upgrade in admin/index.php stop it
        // TODO нет такого методо в 2,0 - удалить со временем!!!
        /*
        if (function_exists('upgrade_log_finish'))
        {
            upgrade_log_finish();
        }
        */
        if ( ! empty($link) )
        {
            echo $OUTPUT->continue_button($link);
        }

        echo $OUTPUT->footer();

        for ( $i=0; $i<512; $i++ )
        {  // Padding to help IE work with 404
            echo ' ';
        }
        die;
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
    public function debugging($message = '', $level = DEBUG_NORMAL, $backtrace = null)
    {
        return dof_debugging($message, $level, $backtrace);
    }
    
    /** Вывести сообщение при работе в пакетном режиме
      * 
      * @param int $mlevel - приоритет текущего сообщения
      * @param string $string - сообщение
      * @param string $eol [optional] - символ конца строки
      * @return bool
      * @access public
      */
    public function mtrace($mlevel,$string,$eol="\n",$file=false,$filedata=null)
    {
        // Выводим с параметрами, полученными из настроек текущего режима
        // @todo Сделать получение параметров режима из настроек
        dof_mtrace($mlevel,$string,$eol,0,3);
        if ( $file == true AND !is_null($filedata) )
        {
            $path = $this->plugin_path($filedata->plugintype, $filedata->plugincode, '/dat/'.$filedata->filename.'.txt');
            $resultfile = fopen($path, 'a');
            // формируем данные для вставки в файл
            fputs($resultfile, $string.$eol);
            // завершаем работу с файлом
            fclose($resultfile);
        }
    }

    /** Запротоколировать событие в таблице логов Moodle
     * 
     * @param string $plugintype - тип модуля, сгенерировавшего событие
     * @param string $plugincode - код модуля, сгенерировавшего событие
     * @param string $code - код события
     * @param string $url - ссылка на отчет о событии
     * @param string $text - текстовое описание
     */
    public function add_to_log($plugintype, $plugincode, $code, $url, $text)
    {
        global $SITE;
        
        // Формируем дополнительную информацию
        $otherdata = array();
        $otherdata['plugintype'] = $plugintype;
        $otherdata['plugincode'] = $plugincode;
        $otherdata['eventcode'] = $code;
        $otherdata['url'] = $url;
        $otherdata['text'] = $text;
        
        // Формируем данные для лога
        $eventdata = array(
                'context' => context_course::instance($SITE->id),
                'other' => $otherdata
        );
        $event = \block_dof\event\dof_events::create($eventdata);
        $event->trigger();
        
        return true; 
    }  
    
    /** Сгенерировать событие
     * @param string $plugintype - тип модуля, сгенерировавшего событие
     * @param string $plugincode - код модуля, сгенерировавшего событие
     * @param string $eventcode - код задания
     * @param int $intvar [optional] - дополнительный параметр 
     * @param mixed $mixedvar [optional] - дополнительные параметры
     * @return bool - true в случае выполнения без ошибок
     * @access public 
     */
    public function send_event($plugintype,$plugincode,$eventcode,$intvar=null,$mixedvar=null)
    {
        GLOBAL $DB;
        $result = new dof_broadcast_result($this);
        // Выбираем плагины, слушающие эти события
        $conditions = array('plugintype'=>$plugintype, 'plugincode'=>$plugincode, 'eventcode'=>$eventcode);
        $plugins = $DB->get_records('block_dof_events', $conditions);
        
        if ( ! $plugins )
        {// Некому рассылать
            return true;
        }
        // Рассылаем сообщение
        foreach ($plugins as $plugin)
        {
            // Вызываем плагин-получатель события
            if ( $answer = $this->plugin($plugin->rplugintype,$plugin->rplugincode)->catch_event($plugintype,
                                                    $plugincode,$eventcode,$intvar,$mixedvar) )
            {
                $result->add($plugin->rplugintype, $plugin->rplugincode, $answer);
            }
        }
        return $result;
    }
    /** Добавить задание
     * 
     * @param string $plugintype - тип плагина, который будет выполнять задание
     * @param string $plugincode - код плагина, который будет выполнять задание
     * @param string $todocode - код задания
     * @param int $intvar [optional] - дополнительный параметр для выполнения задания (число)
     * @param mixed $mixedvar [optional] - дополнительные параметры для выполнения задания (обьект)
     * @param int $loan [optional] - загрузка, при которой будет исполнена операция
     *                      1 - срочно,
     *                      2 - в нормальном режиме,
     *                      3 - в часы минимальной загрузки системы
     * @param int $time [optional] - время, после которого должно быть запущено задание 
     * 
     * @return bool - true если задание удалось добавить в очередь
     * @access public
     */
    public function add_todo($plugintype, $plugincode, $todocode,$intvar=null,$mixedvar=null,$loan=2,$time=0)
    {
        GLOBAL $DB, $USER;
        $todo = new  object();
        $todo->plugintype = $plugintype;
        $todo->plugincode = $plugincode;
        $todo->todocode = $todocode;
        $todo->intvar = $intvar;
        $todo->mixedvar = serialize($mixedvar);
        $todo->loan = $loan;
        $todo->tododate = $time;
        $todo->exdate = 0;
        $todo->personid = $this->storage('persons')->get_by_moodleid_id($USER->id);
        return $DB->insert_record('block_dof_todo',$todo);
    }
    
    /** Получить список заданий или одно задание с заданым id
     * 
     * @param int $id - id задания
     * @param string $sort [optional] - сортировка (при выводе списка), по умолчанию - без сортировки
     * @param int $limitnum [optional] - максимальное количество записей, которое будет выбрано
     * @param int $limitfrom [optional] - начиная с какой записи начинать выборку 
     * @return mixed - array, массив объектов из таблицы block_dof_todo при id = 0
     *                 object,  при id > 0
     * 
     * @todo добавить возможность извлекть todo по нескольким параметрам, разрешив передавать
     *       не только id но и массив условий в формате ключ => значение
     */
    public function get_todo($id, $sort='', $limitnum=0, $limitfrom=0)
    {
        GLOBAL $DB;
        if ( ! $id )
        {
            return $DB->get_records('block_dof_todo',null,$sort,'*',$limitfrom,$limitnum);
        }
        $conditions = array('id' => $id);
        return $DB->get_record('block_dof_todo',$conditions);
    }
    
    /** Удаляет по id запись todo
     * @param integer $id - запись, которую удаляем 
     *  
     * @return bool 
     */
    public function delete_todo($id)
    {
        GLOBAL $DB;
        $conditions = array('id'=>$id);
        return $DB->delete_records('block_dof_todo',$conditions);
    }    
    
    
    /******* работа с таблицей dof_events *******/

    /** Удаляет из таблицы событий все события и широковещательные запросы, 
     * которые ожидает указанный плагин
     * 
     * @param string $rplugintype - тип плагина
     * @param string $rplugincode - код плагина
     * @return bool
     */
    protected function delete_recipient($rplugintype, $rplugincode)
    {
        GLOBAL $DB;
        $conditions = array('rplugintype'=>$rplugintype, 'rplugincode'=>$rplugincode);
        $DB->delete_records('block_dof_events', $conditions);
        return ! $DB->record_exists('block_dof_events', $conditions);
    }
    /** Вставляет в таблицу событий все события, 
     * которые ожидает заданный плагин
     * 
     * @param string - $type - тип плагина
     * @param string - $code - уникальное имя плагина
     * @return bool
     */
    protected function plugin_insertevents($type, $code)
    {
        GLOBAL $DB;
        //Удаляем занесенную ранее информацию о прослушиваемых событиях
        if ( ! $this->delete_recipient($type, $code) )
        {//удаление не удалось
            return false;
        }
        //получаем события, которые должен отслеживать плагин
        $allevents = $this->plugin($type, $code)->list_catch_events();
        foreach ( $allevents as $event )
        {//заносим их в базу
            //$event - массив, который содержит код события и имя плагина,
            //который его порождает
            $eventobj = (object)$event;//преобразовали массив в объект
            $eventobj->rplugintype = $type;//добавили сведения о плагине,
            $eventobj->rplugincode = $code;//который ждет это событие
            if ( ! $DB->insert_record('block_dof_events', $eventobj) )
            {//не удалось занести событие в бд - беда
                return false;
            }
        }
        return true;
    }
    /** Создать объект для события установки, удаления или обновления плагина
     * 
     * @return object
     * @param string $code - код плагина
     * @param string $type - тип плагина
     * @param string $version - версия плагина
     */
    protected function create_plugindata_object($code, $type, $version)
    {
        $plugin = new stdClass();
        $plugin->code    = $code;
        $plugin->type    = $type;
        $plugin->version = $version;
        
        return $plugin;
    }
    /******** работа с таблицей dof_plugins ********/
    /** Вставляет запись о плагине в таблицу
     * 
     * @param string - $type - тип добавляемого плагина
     * @param string - $code - уникальное имя добавляемого плагина
     * 
     * @return mixed int - id вставленной записи в таблице block_dof_plugins или false, если вставить не удалось
     */
    protected function plugin_insertrec($type, $code)
    {
        GLOBAL $DB;
        if ( $this->plugin_exists($type, $code) )
        {//плагин уже зарегистрирован в системе
            return true;
        }
        $obj = new stdClass(); //готовим запись для занесения в бд
        $obj->type = $type;
        $obj->code = $code;
        $obj->version = $this->plugin($type, $code)->version();
        if ( $this->plugin($type, $code)->is_cron() )
        {//плагин требует запуска крона
            $obj->cron = $this->plugin($type, $code)->is_cron();
        }
        // регистрация отслеживаемых плагином событий
        if ( ! $this->plugin_insertevents($type, $code) )
        {//регистрация событий не удалась
            return false;
        }
        //регистрация плагина в системе      
        return $DB->insert_record('block_dof_plugins', $obj); 
    }
    /** Обновляет запись о плагине в системе
     * 
     * @param string $type - тип обновляемгого плагина
     * @param string $code - уникальное имя обновляемгого плагина
     * @return bool
     */
    protected function plugin_updaterec($type, $code)
    {
        GLOBAL $DB;
        //получаем запись о плагине в системе
        $obj = $this->plugin_getrec($type, $code);
        //получаем номер новой версии плагина
        $obj->version = $this->plugin($type, $code)->version();
        if ( $this->plugin($type, $code)->is_cron() )
        {//запомним периодичность запуска крона
            $obj->cron = $this->plugin($type, $code)->is_cron(); 
        }else
        {//крон не нужен сбросим период
            $obj->cron = null;
        }
        // регистрация отслеживаемых плагином событий
        if ( ! $this->plugin_insertevents($type, $code) )
        {//регистрация событий не удалась
            return false;
        }
        return $DB->update_record('block_dof_plugins', $obj);
    }
    /** Удаляет плагин из системы
     * 
     * @param string - $type - тип плагина
     * @param string - $code - уникальное имя плагина
     * @return bool true если удаление прошло успешно или false в иных случаях 
     */
    protected function plugin_removerec($type, $code)
    {
        GLOBAL $DB;
        $conditions = array('type'=>$type, 'code'=>$code);
        $DB->delete_records('block_dof_plugins', $conditions);
        return ! $DB->record_exists('block_dof_plugins', $conditions);
    }
    /** Возвращает запись плагина из таблицы регистрации плагинов
     * 
     * @param string $type - тип плагина
     * @param string $code - код плагина
     * @return mixed object - запись из таблицы плагинов если она найдена
     *             или bool false в иных случаях
     */
    public function plugin_getrec($type, $code)
    {
        GLOBAL $DB;
        $conditions = array('type'=>$type, 'code'=>$code);
        return $DB->get_record('block_dof_plugins', $conditions);
    }
    /** Проверяет регистрацию плагина в бд
     * @param string $type - тип плагина
     * @param string $code - код плагина
     * @return bool - true если плагин в бд уже зарегистрирован или false 
     */
    public function plugin_exists($type, $code)
    {
        GLOBAL $DB;
        $conditions = array('type'=>$type, 'code'=>$code);
        return $DB->record_exists('block_dof_plugins', $conditions);
    }
    /** Проверить, существует ли плагин на диске.
     * (Неважно, установлен он или нет)
     * Проверяет только наличие нужной папки и файла init.php в ней
     * Не использует plugin_path, потому что plugin_path возвращает ошибки и прерывает работу скрипта
     * 
     * @param string $type - тип плагина
     * @param string $code - код плагина
     * @return bool
     */
    public function plugin_files_exists($type, $code)
    {
        $path .= '/'.$type.'/'.$code;
        $path = $this->pathbase."/{$type}/{$code}/";
        if ( is_dir($path) )
        {// Папка с плагином есть
            if ( is_file($path.'init.php') )
            {// в плагине есть init
                return true;
            }
        }
        return false;
    }
    
    /** Конструктор класса
    * @param object $CFG - параметры Moodle
    * @access public
    */
    public function __construct($CFG) 
    {
        $this->moodlecfg = $CFG;
        $this->pathbase = $CFG->dirroot.'/blocks/dof';
        $this->urlbase = $CFG->wwwroot.'/blocks/dof';
        $this->messages = new dof_messages($this);
    }
    
    /** Включен ли на сайте режим технического обслуживания?
     * 
     * @return bool true  - режим включен, и пользователь не имеет прав администратора
     *              false - режим выключен, или пользователь имеет права администратора
     */
    public function is_maintenance()
    {
        if ( class_exists('context_system') )
        {// начиная с moodle 2.6
            $context = context_system::instance();
        }else
        {// оставим совместимость с moodle 2.5 и менее
            $context = get_context_instance(CONTEXT_SYSTEM);
        }
        if ( has_capability('moodle/site:config', $context) )
        {// пользователь - админ, можно все
            return false;
        }
        global $SITE;
        if ( file_exists($this->moodlecfg->dataroot.'/'.$SITE->id.'/maintenance.html') )
        {// пользователь не админ и режим включен
            return true;
        }
        
        return false;
    }
    
    /** Проверить, возможна ли установка плагина.
     * Установка плагина возможна только в том случае, 
     * если все зависимые плагины присутствуют на диске
     * 
     * @param string $plugintype - тип плагина
     * @param string $plugincode - код плагина
     * @return bool
     */
    public function is_setup_possible($plugintype, $plugincode)
    {
        if ( ! method_exists($this->dof, $plugintype) )
        {// неизвестный тип плагина
            return false;
        }
        $pluginlist = $this->dof->$plugintype($plugincode)->need_plugins();
        if ( empty($pluginlist) )
        {// нет зависимых плагинов - установка возможна
            return true;
        }
        
        foreach ( $pluginlist as $type => $plugins )
        {
            foreach ( $plugins as $code => $version )
            {
                if ( ! $this->plugin_files_exists($type, $code) )
                {
                    return false;
                }
            }
        }
        
        return true;
    }
    
    /**
     * Получить текущую версию Moodle
     * 
     * @return float номер версии в формате YYYYMMDDRR.XX
     */
    public function moodle_version()
    {
        global $CFG;
        return $CFG->version;
    }
}

?>
