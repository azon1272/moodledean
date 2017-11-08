<?php
/*
 * Класс, созданный для экспорта в csv
 */
class dof_modlib_templater_format_html extends dof_modlib_templater_format{

    
    /**
     * @see dof_modlib_templater_format::get_file()
     */
    public function get_file($options=null)
    {
        // Формируем путь
        $filepath = $this->template_path('html/template.html');
        return $newcontent = $this->insert_content($filepath);
    }
    
    /**
     * @see dof_modlib_templater_format::get_filename()
     */
    public function get_filename($options=null)
    {
        // устанавливаем имя файла и расширение по умолчанию
        $filename  = $this->templatename;
        $extention = 'html';
        if (!empty($options->filename) AND is_string($options->filename))
        {// определяем новое имя файла, если оно есть';
            $options->filename = trim($options->filename);
            if ($options->filename)
            {// если имя файла задано принудительно - используем его
                $filename = $options->filename;
            }
        }
        return $filename.'.'.$extention;
    }
    
    /**
     * @see dof_modlib_templater_format::get_mimetype()
     */
    public function get_mimetype($options=null)
    {
        return 'text/html';
    }
	/** 
	 * Возвращает содержание всех заголовков,
	 * необходимых для отправки файла
	 * @return array массив строк заголовков
	 */
	public function get_headers($options = null)
	 {
	 	$rez = array();
		$rez[] = 'Content-Type:'.$this->get_mimetype($options).'; charset=utf8';
		// Показываем на экране
        // $rez[] = 'Content-Disposition: attachment; filename="'.$this->get_filename().'"';
		return $rez;
	 }
    /*******************************/
    /** Собственные методы класса **/
    /*******************************/
    /** 
     * Эта функция вставляет переданные данные в файл
     * и возвращает его как строку
     * @param object $data - входные данные
     * @return mixed string или false
     */
    protected function insert_content($filepath)
    {
        //подключаем обработчик данных';
        $path = $this->dof->plugin_path('modlib','templater','/lib.php');
        require_once($path);
        $placeholder = new dof_modlib_templater_placeholder($this->data);
        //проверяем наличие файла шаблона';
        if ( ! $rez = $placeholder->get_content($filepath) )
        {//файл шаблона не существует';
            //возвращаем свой файл с сообщением об ошибке
            $filepath = $this->template_path('formats/html/template.html', false);
            $rez = file_get_contents($filepath);
        }
        //возвращаем файл
        return $rez;
    }
}?>