<?php 
/**
 * Класс для замены полей ввода данными
 * с помощью HTML_Template_Sigma
 */
class dof_modlib_templater_placeholder
{
    private $tpl;
    private $data;
    /**
     * конструктор 
     * @param $data - входные данные,
     * которые надо вставить в шаблон
     */
    function __construct($data)
    {
        global $DOF;
        // Подключаем сигму
        $DOF->modlib('pear')->sigma();
        // Помещаем объект в свойство класса
        $this->tpl = new HTML_Template_Sigma();
        // Подключаем дополнительные функции для обработки данных в шаблоне
        $this->tpl->setCallbackFunction('date', array(&$this, '_date'));
        $this->tpl->setCallbackFunction('mb_substr', array(&$this, '_mb_substr'));
        $this->tpl->setCallbackFunction('get_value', array(&$this, '_get_value'));
        $this->tpl->setCallbackFunction('get_string', array(&$this, '_get_string'));
        //помещаем данные в свойство класса
        $this->data = new stdClass();
        $this->data = $data;
    }
    /** 
     * Эта функция вставляет переданные данные в файл
     * и возвращает его как строку
     * @param object $data - входные данные
     * @return mixed string или false
     */
    public function get_content($filepath)
    {
        //проверяем наличие файла шаблона';
        if ( ! file_exists($filepath) )
        {//файл шаблона не существует';
            return false;
        }
        //подключаем файл шаблона';
        $this->tpl->loadTemplateFile($filepath);
        //вставляем туда данные';
        if ( ! $this->insert_data() )
        {//вставка прошла с ошибкой;
            return false;
        }
        //возвращаем файл
        return $this->tpl->get();
    }
    /** 
     * Эта функция вставляет поля и блоки из объекта данных в файл.
     * @param object $data - входные данные
     * @return bool
     */
    private function insert_data()
    {
        if ( ! isset($this->data) OR ! is_object($this->data) )
        {//данных для вставки не обнаружено';
            return false;
        }
        //вставляем поля верхнего уровня 
        $rez = $this->set_fields($this->data);
        //теперь вставляем блоки
        $adata = (array)$this->data;
        foreach ($adata as $blockname => $blockdata )
        {//ищем блоки в данных верхнего уровня
            if ( $this->tpl->blockExists($blockname) )
            {//нашли - вставляем
                $rez = $rez AND $this->setblock_subblock($blockname, $blockdata);
            }
        }
        return $rez;
    }
    /**
     * Вставляет блок и все вложенные
     *  в него блоки в документ
     * @param string $blockname - имя текущего блока
     * @param array $blockdata - смешанный массив данных для вставки:
     * объекты - поля для вставки или массивы - вложенные блоки 
     * @return bool 
     */
    private function setblock_subblock($blockname, $blockdata)
    {
        $ablockdata = (array)$blockdata; 
        $rez = true;
        foreach ($ablockdata as $one)
        {//перебираем данные текущего блока для вставки
            if ( ! is_object($one) )
            {//неправильный формат
                continue;
            }
            //вставляем экземпляр полей текущего блока 
            $rez = $rez AND $this->set_fields($one);
            $aone = (array)$one;
            foreach ( $aone as $subblockname => $subblockdata )
            {//ищем вложенные блоки 
                if ( $this->tpl->blockExists($subblockname) )
                {//это блок;
                    //вставляем экземпляр дочернего блока '.$subblockname;
                    $rez = $rez AND $this->setblock_subblock($subblockname, $subblockdata);
                }
            }
            //загоняем экземпляр текущего
            //и всех дочерних блоков в документ     
            $rez = $rez AND $this->tpl->parse($blockname);
        }
        return $rez;
    }   
    /** 
     * Вставляет блок данных в шаблон
     * @param string $blockname имя блока
     * @param array $blockdata - содержимое блока
     * @return bool
     */
    private function set_block($blockname,$blockdata)
    {
        $result = $this->tpl->blockExists($blockname);
        if( ! $result )
        {// такой блок в шаблоне не найден';
            return false;
        }
        if ( ! is_array($blockdata) )
        {//неправильный формат блока';
            return false;
        }
        foreach ( $blockdata as $row )
        {//вставляем поля в шаблон';
            $this->set_fields($row);
            $this->tpl->parse($blockname);
        }
        //раз до сюда дошли - значит все нормально';
        return true;
    }
    /**
     * Заменяет несколько полей в шаблоне
     * @param object $fields - набор пар имя_поля = значение_поля
     * @return bool true, если все замены прошли успешно
     * или false в остальных случаях
     */
    private function set_fields($fields)
    {
        if ( ! is_object($fields) )
        {//данные неправильного типа
            return false;
        }
        $rez = true;
        //превращаем объект в массив
        $afields = (array)$fields;
        foreach ( $afields as $name => $value )
        {//перебираем поля замены
            if ( is_scalar($value) )
            {//это действительно значение поля - вставляем
                $rez = $rez AND $this->set_field($name, $value);
            }
            elseif  ( is_object($value) )
			//если поле является вложенным объектом
            {
		        //превращаем объект в массив
        		$afields_child = (array)$value;
            	foreach ( $afields_child as $name_child => $value_child )
        		{//перебираем поля вложенного объекта для замены
        			if ( is_scalar($value_child) )
            		{//это действительно значение поля - вставляем
            			$rez = $rez AND $this->set_field("$name.$name_child", $value_child);
            		}
        		}	
            }
        }
        return $rez;
    }
    /** 
     * Вставляет поле в шаблон
     * @param string $name - имя поля
     * @param string $value - значение поля
     * @return bool;
     */
    private function set_field($name, $value)
    {
        if ( ! is_scalar($name) AND ! is_scalar($value) )
        {//данные неправильного типа
            return false;
        }
        $this->tpl->setVariable($name,$value);
        return true;
    }
    
    function _date($fofmat,$value = null)
    {
        global $DOF;
    	if ($value == '') return '';
        if ( is_null($value) )
        {// null-значение переопределяем в текущую дату
            $value = time();
        }
        return $DOF->storage('persons')->get_userdate($value,$fofmat);
    }
    
    function _mb_substr($value,$start = 0,$length = 1)
    {
    	if ((mb_substr($value,0,1,'utf-8') == '{') AND mb_substr(strrev($value),0,1,'utf-8') == '}') return '';
        return mb_substr($value,$start,$length,'utf-8');
    }

    /**
     * Возвращает значение поля в записи с указанным id
     *
     * @access private
     * @param  int $id - id записи в таблице
     * @param  string $field - поле в таблице
     * @param  string $code - имя плагина справочника
     * @return string значение поля
     */
    function _get_value($id,$field,$code)
    {
    	global $DOF;
    	if (isset($DOF->storage($code)->get($id)->$field)) return $DOF->storage($code)->get($id)->$field;
    	return false;
    }

    function _get_string($identifier, $pluginname = NULL, $a = NULL, $plugintype = 'im')
    {
    	global $DOF;
        return $DOF->get_string($identifier, $pluginname, $a, $plugintype);
    }
}
?>