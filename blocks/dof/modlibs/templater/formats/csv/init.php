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
class dof_modlib_templater_format_csv extends dof_modlib_templater_format{
    /**
     * @see dof_modlib_templater_format::get_file()
     */
    public function get_file($options = NULL)
    {
        if ( isset($options->title) )
        {//покажем заголовки таблицы или нет - 
            return $newcontent = $this->insert_content($options->title);
        }
        //однозначно вернем таблицу c заголовками
        return $newcontent = $this->insert_content();
    }
    /**
     * @see dof_modlib_templater_format::get_filename()
     */
    public function get_filename($options=null)
    {
        // устанавливаем имя файла и расширение по умолчанию
        $filename  = $this->templatename;
        $extention = 'csv';
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
        return 'text/csv';
    }
	/** 
	 * @see dof_modlib_templater_format::get_headers()
	 */
	public function get_headers($options = null)
	{
	 	$rez = array();
		$rez[] = 'Content-Type:'.$this->get_mimetype($options).'; charset=utf8';
        $rez[] = 'Content-Disposition: attachment; filename="'.$this->get_filename($options).'"';
		return $rez;
	 }
    /*******************************/
    /** Собственные методы класса **/
    /*******************************/
    /** 
     * Эта функция вставляет переданные данные в файл
     * и возвращает его как строку
     * @param bool $title - входные данные
     * @return mixed string или false
     */
    protected function insert_content($title = true)
    {
        //получаем имя поле с данными экспорта
        if ( ! $field = $this->get_field_name() )
        {//имя поля не получено - работать не с чем';
            return '';
        }
        if ( ! $data = $this->get_data_forexport($field) )
        {//нет данных для экспорта';
            return '';
        }
        if ( ! $head = $this->get_title($data) )
        {//надо получить заголовок, но мы его не получили
            return '';
        }
        //превращаем входные данные в массив
        $adata = $this->obj_to_array($data);
        if ( ! $rez = $this->create_output($adata, $head, $title) )
        {//создать текст для экспорта не удалось';
            return '';
        }
        return $rez;
    }
    /**
     * Возвращает имя поля, которое 
     * содержит данные для обработки.
     * Возвращает имя первого подходящего свойства объекта
     * @return mixed string - имя поля или bool false
     */
    protected function get_field_name()
    {
        if ( ! is_object($this->data) )
        {//данные пусты - сообщим об ошибке';
            return false;
        }
        foreach ( $this->data as $k => $v )
        {//ищем во входных данных массив
            if ( is_array($v) AND ! empty($v) )
            {//нашли подходящее поле
                return $k;
            }
        }
        //нет подходящих полей для экспорта
        return false;
    }
    /**
     * Возвращает объект для экспорта,
     * выбирая его из исходных данных по имени поля
     * @param string $name - имя свойства объекта 
     * входных данных для экспорта
     * @return mixed array - массив объектов для вставки
     * или bool false 
     */
    protected function get_data_forexport($name)
    {
        if ( ! is_string($name) )
        {//неправильные входные данные
            return false;
        }
        if ( ! isset($this->data->$name) )
        {//нет такого свойства в данных экспорта
            return false;
        }
        return $this->data->$name;
    }
    /**
     * Возвращает объект, содержащий заголовок таблицы.
     * Возвращает объект, свойства и значения которого - это  
     * имена полей первой записи.
     * @param array $data - массив объектов данных
     * @return object - объект имен полей
     */
    protected function get_title($data)
    {
        if ( ! is_array($data) OR empty($data) )
        {//неподходящие данные
            return false;
        }
        //выбираем первый элемент массива
        $first = array_shift($data);
        if ( ! is_object($first) )
        {//первый элемент не объект - это неправильно';
            return false;
        }
        $title = new stdClass();//для результата
        foreach ( $first as $key => $value)
        {//перебираем поля первой записи
            //запоминаем имя поля
            $title->$key = $key;
        }
        return $title;
    }
    /**
     * Форматирует данные экспорта 
     * в вид, нужный для экспорта
     * @param array $forexport - данные для экспорта
     * @param object $head - заголовки
     * @param boolean $title - печатать заголовки или нет
     */
    protected function create_output($forexport, $head, $title = true)
    {
        if ( ! is_array($forexport) OR empty($forexport) )
        {//входные данные не верны';
            return false;
        }
        $rez = '';//для результата
        if ( ! is_object($head) AND is_array($head) )
        {//передан заголовок неправильного формата
            return false;
        }
        //преобразуем заголовок в массив
        $ahead = $this->obj_to_array($head);
        if ( $title )
        {//в вывод надо вставить заголовок - вставляем
            $rez .= $this->create_head_string($ahead);
        }
        //вставляем данные
        foreach ( $forexport as $key => $value )
        {//перебираем данные для строк';
            $rez .= $this->create_data_string($ahead,$value);
        }
        //возвращаем результат
        return $rez;
    }
    /**
     * Создает строку заголовка таблицы
     * @param array $head - индексы - названия полей, 
     * по которым из массива данных выбираются 
     * значения для таблицы. Значения всех элементов - 
     * названия колонок таблицы  
     * @return string - строку заголовка
     */
    protected function create_head_string($head)
    {
        //создаем первую строку
        $rez = '';
        foreach ( $head as $v )
        {//сделали строку заголовков
            $rez .= $this->prepare_string($v).',';
        }
        //отрезали последнюю запятую
        $rez = substr($rez, 0, -1);
        //переходим на новую строку
        $rez .= "\n";
        return $rez;
    }
    /**
     * Создает из массива данных строку csv-файла 
     * @param array $head - массив строки заголовка
     * названия индексов - названия полей 
     * @param array $obj - массив данных для вставки в строку
     * названия индексов - названия полей, значения - данные
     * @return string - одну строку с данными
     */
    protected function create_data_string($head, $obj)
    {
        //формируем строку результата
        $rez = '';
        foreach ( $head as $key => $value )
        {//перебираем элементы строки заголовка
            if ( array_key_exists($key, $obj) )
            {//одноименное поле есть в строке данных
             //заносим его значение в строку
                $rez .= $this->prepare_string($obj[$key]);
            }
            $rez .= ',';
        }
        //отрезали последнюю запятую
        $rez = substr($rez, 0, -1);
        //переходим на новую строку
        $rez .= "\n";
        return $rez;
    }
    /**
     * Обрабатывает строку для приведения
     * в соответствие со стандартом csv:
     * - двойные кавычки в строке дублируются
     * - если строка содержит ' ' ';' ',' '\t' '\n', 
     * то она заключается в двойные кавычки 
     * @param string $str - строка, которую надо обработать
     * @return string обработанная строка
     */
    protected function prepare_string($str)
    {
        $rez = $str;
        //проверяем наличие особых символов в строке
        $blanc = stristr($str, ' ');
        $quote = stristr($str, '"');
        $comma = stristr($str, ',');
        $semi  = stristr($str, ';');
        $tab   = stristr($str, "\t");
        $crlf  = stristr($str, "\n");
        if ( $quote )
        {//в строке есть двойные кавычки - удваиваем их
            $rez = str_replace('"','""',$str );
        }
        if ( $blanc === false OR $quote === false OR 
             $comma === false OR $semi  === false OR 
             $tab   === false OR $crlf  === false   )
        {//особые символы есть - заключаем строку в кавычки
            $rez = '"'.$rez.'"';
        }
        return $rez;
    }
    /**
     * Превращает объект данных в массив
     * @param object $object 
     * @return array
     */
    protected function obj_to_array($object)
    {
        //превратили в массив верхний уровень объекта
        $ar = (array)$object;
        $rez = array();//для результата
        foreach ( $ar as $key => $value )
        {//ищем объекты внутри свойств объекта 
            if ( is_object($value) OR is_array($value) )
            {//нашли - превращаем в массив'; 
                $rez[$key] = $this->obj_to_array($value);
            }else
            {//это скаляр - так и оставляем';
                $rez[$key] = $value;
            }
        }
        return $rez;
    }
}?>