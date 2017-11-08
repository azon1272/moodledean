<?php
/*
 * Описание файла
 */
abstract class dof_modlib_templater_format
{
	/** 
	 * Экземпляр объекта $DOF
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
    /** 
     * Данные для обработки в формате stdClass Object
	 * @var object
	 */
    protected $data;
    /** 
     * Конструктор класса
 	 * @param object $dof - объект $DOF, чтобы сделать его глобальным
	 * @param object $templater - объект от класса dof_modlib_templater
	 * @param string $plugintype - тип плагина
	 * @param string $pluginname - имя плагина
	 * @param string $templatename - имя шаблона
     */
    public function __construct($dof, $plugintype, $pluginname, $templatename=null)
    {
        $this->dof = $dof;
        $this->plugintype   = $plugintype;
        $this->pluginname   = $pluginname;
        $this->templatename = $templatename;
    }
    /** 
     * Возвращает данные отформатированные 
     * как файл определенного типа в виде строки 
     * @param object $options - дополнительные параметры
     * @return string 
     */
    public function get_file($options=null)
    {
        
    }
    /** 
     * Записывает данные для обработки в поле $data
     * @param object $options
     * @return string
     */
    public function set_data($data)
    {
        $this->data = $data;
    }
    /** 
     * Возвращает имя файла для экспорта
     * @param object $options
     * @return string
     */
    public function get_filename($options=null)
    {
        
    }
    /** 
     * Возвращает mime-тип файла для экспорта
     * @param object $options
     * @return string
     */
    public function get_mimetype($options=null)
    {
        
    }
    /** 
     * Путь к папке данного формата (работает через класс package)
     * @param string $adds
     * @return string
     */
    public function template_path($adds=null, $fromplugin=null)
    {
        return $this->dof->modlib('templater')->
            template_path($this->plugintype, $this->pluginname, $this->templatename, $adds, $fromplugin);
    }
	/** 
	 * Возвращает содержание всех заголовков,
	 * необходимых для отправки файла
	 * @return array массив строк заголовков
	 */
	 public function get_headers($options = null)
	 {
	 	$rez = array();
		$rez[] = 'Content-Type:'.$this->get_mimetype().'; charset=utf8';
        $rez[] = 'Content-Disposition: attachment; filename="'.$this->get_filename($options).'"';
		return $rez;
	 }
	 /** 
	  * Посылает заголовки перед отправкой файла
	  * @return void
	  */
	 public function send_headers($options = null)
	 {
		 foreach ( $this->get_headers($options) as $one )
		 {
		 	header($one);
		 }	
	 }
}
?>