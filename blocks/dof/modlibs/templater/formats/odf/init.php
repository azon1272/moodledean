<?php
/** класс для парсера в odf
 */
class dof_modlib_templater_format_odf extends dof_modlib_templater_format
{
	/**
	 * @var object - объект данных для вставки
	 */
	var $data;
    /** 
     * Выводит odf-файл в формате, пригодном для вставки в функцию file_put_contents
     * @param object $options - дополнительные параметры (если понадобятся) 
     * @see blocks/dof/modlibs/templater/dof_modlib_templater_format#get_file()
     */
    public function get_file($options=null)
    {//print_object($options);die;
        $zip = new dof_modlib_templater_format_odf_ziper();
        $pref = new stdClass();
        $pref->backup_name = $this->get_filename($options);
        $pref->backup_unique_code = time();
        if ( isset($options->backup_files_from) )
        {//путь к исходным файлам указан
            $pref->backup_files_from = $options->backup_files_from;
        }else
        {//путь не указан - берем путь по умолчанию';
            $pref->backup_files_from = $this->template_path('content');
        }
        // создаем директорию с временными файлами
        $tmppath = $zip->create_tmp_folder($pref);
        $filepath = $tmppath.'/content.xml';
        // получаем содержание нового content.xml
        $newcontent = $this->insert_content($filepath);
        //return $newcontent;
        if ($newcontent)
        {// если новое содержимое получено - то запихнем в архив его
            //если же нет - то у нас просто останется старый xml
            $rez = file_put_contents($tmppath.'/content.xml', $newcontent);
        }
        // помещаем файлы в архив, читаем его, 
        $contents = $zip->create_zip($pref);
        //  удаляем временную директорию,
        $zip->delete_backup($tmppath);
        // возвращаем поток байтов при помощи file_get_contents()
        return $contents;
    }
    /** 
     * Устанавливает имя файла по умолчанию для всех документов формата odf
     * @param object $options - объект с дополнительными параметрами.
     * Поле filename содержит предлагаемое имя файла.
     */
    public function get_filename($options=null)
    {
        // устанавливаем имя файла и расширение по умолчанию
        $filename  = 'export';
        $extention = 'odt';
        // определяем новое имя файла, если оно есть
        if (isset($options->filename) AND is_string($options->filename))
        {
            $options->filename = trim($options->filename);
            if ($options->filename)
            {// если имя файла задано принудительно - используем его
                $filename = $options->filename;
            }
        }
        // определяем расширение файла
        if ( $mimetype = $this->get_mimetype($options) )
        {// если есть файл с mime-типом то узнаем, что это за документ
            switch ($mimetype) 
            {// @todo добавить тип с презентацией
                // это электронная таблица - присвоим расширение ods
            	case 'application/vnd.oasis.opendocument.spreadsheet':
            	$extention = 'ods';
            	break;
            	// это текстовый документ - присвоим расширение odt
            	case 'application/vnd.oasis.opendocument.text':
            	$extention = 'odt';
            	break;
                // неизвестный mime-тип - вернем текстовый файл с ошибкой
            	default:
                $filename  = 'error';
            	$extention = 'odt';
            	break;
            }
        }else
        {// не указан mime-тип, неизвестно, какого формата файл отправлять, назовем файл error
            $filename = 'error';
        }
        // возвращаем имя файла
        return $filename.'.'.$extention;
    }
    
    /**
     * возвращает тип файла
     * @see dof_modlib_templater_format::get_mimetype()
     */
    public function get_mimetype($options=null)
    {
        $file = $this->template_path('content/mimetype');
        if (is_file($file))
        {// если есть файл с mime-типом то узнаем, что это за документ
            $mimetype = trim(file_get_contents($file));
            return $mimetype;
        }else
        {// файл c mime-типом отсутствует
            return false;
        }
    }
    
    /**
     * Возвращает путь к корню плагина или к чему-то внутри него
     * @see blocks/dof/modlibs/templater/dof_modlib_templater_format#template_path()
     */
    public function template_path($adds=null, $fromplugin=null)
    {
        //получаем путь во внешнем плагине
        $external = parent::template_path('odf/'.$adds, true);
        //получаем путь во внутреннем плагине
        $internal = parent::template_path('formats/odf/'.$adds, false);
        if ($fromplugin === true)
        {//надо вернуть путь во внешнем плагине 
            return $external;
        }elseif ($fromplugin === false)
        {//надо вернуть путь в плагине templater
            return $internal;
        }else
        {//надо проверить путь в обоих плагинах
            if ( $external )
            {//во внешнем плагине путь есть - возвращаем его
                return $external;
            }
            //возвращаем путь во внутреннем плагине
            return $internal;
        }
    }
    
    /*******************************/
    /** Собственные методы класса **/
    /*******************************/
    /** 
     * Эта функция вставляет переданные данные в файл
     * и возвращает его как строку
     * @param string $filepath - путь к шаблону
     * @return mixed string или false
     */
    private function insert_content($filepath)
    {
        //подключаем обработчик данных';
        $path = $this->dof->plugin_path('modlib','templater','/lib.php');
        require_once($path);
        $placeholder = new dof_modlib_templater_placeholder($this->data);
        //проверяем наличие файла шаблона';
		if ( ! $rez = $placeholder->get_content($filepath) )
		{//файл шаблона не существует';
            //возвращаем свой файл с сообщением об ошибке
		    $filepath = $this->template_path('content/content.xml', false);
			$rez =  file_get_contents($filepath);
		}
		//возвращаем файл';
		return $rez;
    }
}

/**
 * Класс для упаковки файлов и папок
 * Использует библиотеки moodle
 *
 */
class dof_modlib_templater_format_odf_ziper
{
    /**
     * подключает необходимые библиотеки moodle
     * 
     */
    public function __construct()
    {
        global $CFG;
        //подключаем библиотеки для работы с архивами
        require_once($CFG->dirroot."/backup/util/includes/backup_includes.php");
    }
    /** 
     * Создает временную папку для zip-архива
     * @param object $pref - параметры, 
     * необходимые для создания архива, а именно:
     * $pref->backup_unique_code - уникальный код архива (имя временной папки)
     * $pref->backup_files_from - путь к файлам, которые надо запаковать
     * @return string - путь к созданной временной папке
     */
    public function create_tmp_folder($pref)
    {
        global $CFG;
        //создали папку для файлов архива
        $rez = backup_helper::check_and_create_backup_dir($pref->backup_unique_code);
        $to = $CFG->dataroot."/temp/backup/".$pref->backup_unique_code;
        //очищаем папку
        $rez = backup_helper::clear_backup_dir($to) && $rez;
        //перемещаем файлы которые будем архивировать
        $rez = $this->backup_copy_file($pref->backup_files_from, $to) && $rez;
        return $to;
    }
    /** 
     * Возвращает ссылку на файл архива
     * @param object $pref - объект со всеми необходимыми 
     * для создания архива данными:
     * $pref->backup_unique_code - уникальный код архива (имя временной папки)
     * $pref->backup_files_from - путь к файлам, которые надо запаковать
     * $pref->backup_name - имя файла архива
     * @return string - строка с содержимым odt-файла
     */
    public function create_zip($pref)
    {
        global $CFG;
        //создаем архив');
        //$rez = backup_zip($pref);
        // востановленный depricated-метод moodle backup_zip
        $status = true;

        //Base dir where everything happens
        $basedir = cleardoubleslashes($CFG->dataroot."/temp/backup/".$pref->backup_unique_code);
        //Backup zip file name
        $name = $pref->backup_name;
        //List of files and directories
        $filelist = $this->list_directories_and_files($basedir);

        //Convert them to full paths
        $files = array();
        foreach ($filelist as $file) 
        {
           $files[] = "$basedir/$file";
        }
        $status = zip_files($files, "$basedir/$name");
        if ($status)
        {// если все успешно заархивировали - то получаем содержимое odt-файла
            $filecontents = file_get_contents($basedir.'/'.$name);
        }
        // возвращаем содержимое файла
        return $filecontents;
    }
    /** 
     * Удаляет файл архива
     * и папку временных файлов
     * @param string $fullpath - полный путь к удаляемой папке
     */
    public function delete_backup($fullpath)
    {
        return backup_helper::delete_dir_contents($fullpath) AND remove_dir($fullpath);
    }
    
    /**
     *
     * @param unknown $rootdir
     * @return Ambigous <string, unknown>
     */
    public function list_directories_and_files($rootdir)
    {
        $results = array();
        
        $dir = opendir($rootdir);
        while (false !== ($file=readdir($dir))) {
            if ($file=="." || $file=="..") {
                continue;
            }
            $results[$file] = $file;
        }
        closedir($dir);
        return $results;
    }
    
    public function backup_copy_file($from_file,$to_file,$log_clam=false) {
        global $CFG;
        if (is_file($from_file)) {
            //echo "<br />Copying ".$from_file." to ".$to_file;              //Debug
            //$perms=fileperms($from_file);
            //return copy($from_file,$to_file) && chmod($to_file,$perms);
            umask(0000);
            if (copy($from_file,$to_file)) {
                chmod($to_file,$CFG->directorypermissions);
                if (!empty($log_clam)) {
                    //clam_log_upload($to_file,null,true);
                }
                return true;
            }
            return false;
        }
        else if (is_dir($from_file)) {
            return $this->backup_copy_dir($from_file,$to_file);
        }
        else{
            //echo "<br />Error: not file or dir ".$from_file;               //Debug
            return false;
        }
    }

    public function backup_copy_dir($from_file,$to_file) {
        global $CFG;

        $status = true; // Initialize this, next code will change its value if needed

        if (!is_dir($to_file)) {
            //echo "<br />Creating ".$to_file;                                //Debug
            umask(0000);
            $status = mkdir($to_file,$CFG->directorypermissions);
        }
        $dir = opendir($from_file);
        while (false !== ($file=readdir($dir))) {
            if ($file=="." || $file=="..") {
                continue;
            }
            $status = $this->backup_copy_file ("$from_file/$file","$to_file/$file");
        }
        closedir($dir);
        return $status;
    }
}
?>