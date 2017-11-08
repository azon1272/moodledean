<?php
/*
 * Тестовый класс, созданный для проверки работы экспорта
 */
class dof_modlib_templater_format_dbg extends dof_modlib_templater_format{
    /**
     * @see dof_modlib_templater_format::__construct()
     */

    
    /**
     * @see dof_modlib_templater_format::get_file()
     */
    public function get_file($options)
    {// возвращаем информацию о переменной в структурированом виде
        return print_r($this->data, true);
    }
    
    /**
     * @see dof_modlib_templater_format::get_filename()
     */
    public function get_filename($options=null)
    {
        return 'test.txt';
    }
    
    /**
     * @see dof_modlib_templater_format::get_mimetype()
     */
    public function get_mimetype($options=null)
    {
        return 'text/plain';
    }
	/** 
	 * Возвращает содержание всех заголовков,
	 * необходимых для отправки файла
	 * @return array массив строк заголовков
	 */
	 public function get_headers()
	 {
	 	$rez = array();
		$rez[] = 'Content-Type:'.$this->get_mimetype().'; charset=utf8';
		// Показываем на экране
        // $rez[] = 'Content-Disposition: attachment; filename="'.$this->get_filename().'"';
		return $rez;
	 }
}?>