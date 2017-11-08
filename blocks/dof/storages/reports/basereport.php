<?php
////////////////////////////////////////////////////////////////////////////
//                                                                        //
// NOTICE OF COPYRIGHT                                                    //
//                                                                        //
// Dean`s Office for Moodle                                               //
// Электронный деканат                                                    //
// <http://www.deansoffice.ru/>                                           //
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

/**
 * Базовый класс для объявления типов приказов в плагинах
 */
abstract class dof_storage_reports_basereport
{
    // Ссылка на объект $DOF
    /**
     * @var dof_control
     */
    protected $dof;
    // Параметры для работы с шаблоном
    protected $templatertype;
    protected $templatercode;
    protected $templatertemplatename;
    /**
     * id текущего приказа
     *
     * @var integer
     */
    protected $id = null;
    
    /** Конструктор
     * @param dof_control $dof - объект с методами ядра деканата
     * @access public
     */
    public function __construct($dof, $id=null)
    {
        // Сохраняем ссылку на DOF, чтоб вызывать его через $this->dof
        $this->dof = $dof;
        if ( ! is_null($id) )
        {// id не пустой - установим его в класс
            $this->id = $this->set_id($id);
        }
    }
    
    /**
     * Тип плагина, объявившего тип приказа
     */
    abstract function plugintype();
    /**
     * Код плагина, объявившего тип приказа
     */
    abstract function plugincode();
    /**
     * Код типа приказа
     */
    abstract function code();
    /**
     * Код типа приказа
     */
    abstract function name();
    /**
     * Тип базового плагина storage/reports
     *
     * @return string
     */
    public function baseptype()
    {
        return 'storage';
    }
    /**
     * Код базового плагина storage/reports
     *
     * @return string
     */
    public function basepcode()
    {
        return 'reports'; 
    }
    /**
     * Возвразает ссылку на базовый плагин
     * Для использования внутри объекта
     * 
     * @return dof_storage_reports
     */
    protected function bp()
    {
        return $this->dof->storage($this->basepcode());
    }
    /**
     * Получить id текущего объекта
     *
     * @return integer
     */
    public function get_id()
    {
        //
        return $this->id;
    }
    /**
     * Установить id текущего объекта
     *
     * @param integer $id
     * @return integer
     */
    protected function set_id($id)
    {
        return $this->id = $id;
    }
    
    /**
     * Установить id текущего объекта
     *
     * @param integer $id
     * @return integer
     */
    protected function get_filename()
    {
        return $this->dof->plugin_path($this->plugintype(), $this->plugincode(), '/dat/'.$this->get_id().'.dat');
    }
    /**
     * Сохранить данные отчета в БД
     *
     * @param object $order
     * @return mixed - id или false
     */
    public function save($report)
    {
        $report              = clone $report;
        // Добавляем поля, идентифицирующие плагин
        $report->plugintype  = $this->plugintype();
        $report->plugincode  = $this->plugincode();
        $report->code        = $this->code();
        $report->name        = $this->name();
        $report->status      = 'requested';
        $report->requestdate = time();
        // Убираем поля, которые нельзя редактировать напрямую
        unset($report->completedate);
        unset($report->filepath);
        // Удаляем автоматически-заполняемые поля из служебных данных
        unset($report->data->_departmentid);
        unset($report->data->_personid);
        unset($report->data->_objectid);
        unset($report->data->_name);
        unset($report->data->_completedate);
        unset($report->data->_begindate);
        unset($report->data->_enddate);
        unset($report->data->_requestdate);
        
        // Пропускаем данные через обработчик для сохранения данных в реляционную форму
        // После обработки должны остаться только те данные, которые необходимо сериализовать
        $report = $this->save_data($report);
        
        // Сериализуем оставшиеся данные, убираем слеши рекурсивно, добавляем слеши к итоговой строке
        $report->requestdata = serialize($report->data);
        // Теперь исходные данные убираем
        unset($report->data);
        
        // Сохрангяем в БД
        if ($id = $this->bp()->insert($report))
        {// Сохраняем id новой
            $this->set_id($id);
            $this->log_string(date('d.m.Y H:i:s',time())."\n");
            $this->log_string('Save report'."\n\n");
            return true;
        }
        // Не шмагла я!
        return false;
    }
    /**
     * Метод, предусмотренный для расширения логики сохранения
     */
    protected function save_data($report)
    {
        return $report;
    }
    
    /**
     * Сгенерировать данные для отчета и запихнуть в файл
     *
     * @param object $order
     * @return mixed - id или false
     */
    public function generate()
    {
        if ( ! $reportbd = $this->load() )
        {// Нет данных
            return false;
        }
        if ( $this->is_generate($reportbd) )
        {// отчет уже сгенерирован
            return false;
        }
        $report = clone $reportbd;
        // Проверяем, того ли типа плагин
        if (   $report->plugintype !== $this->plugintype()
            OR $report->plugincode !== $this->plugincode()
            OR $report->code !== $this->code())
        {
            $report->status = 'error';
            $report->completedate = time();
            $this->bp()->update($report,$this->get_id());
            return false;
        }
        // Убираем лишние данные
        unset($report->plugintype);
        unset($report->plugincode);
        unset($report->code);
        if ( isset($report->requestdata) AND mb_strlen($report->requestdata) > 0 )
        {// Убираем слеши из строки, десериализируем, и рекурсивно добавляем слеши к данным
            $report->data = unserialize($report->requestdata);
            // Убираем лишние данные
            unset($report->requestdata); 
        }
        $this->log_string(date('d.m.Y H:i:s',time())."\n");
        $this->log_string('Report generation started'."\n\n");
        // генеририм данные пользователя
        $report = $this->generate_data($report);
        // Убираем поля, которые ненадо хранить в файле
        unset($report->data->_departmentid);
        unset($report->data->_personid);
        unset($report->data->_completedate);
        $report->data->_begindate = $report->begindate;
        $report->data->_enddate = $report->enddate;
        $report->data->_objectid = $report->objectid;
        $report->data->_name = $report->name;
        $report->completedate = time();
        // дозапишем файл временем выполнения сбора отчета
        $report->data->completedate = date('d.m.Y H:i', $report->completedate);
        $filename = $this->get_filename();
        $resultfile = fopen($filename, 'w');
        if ( ! fwrite($resultfile,serialize($report->data)) )
        {// не шмагла записать
            $this->log_string(date('d.m.Y H:i:s',time())."\n");
            $this->log_string('Report generation error'."\n\n");
            $reportbd->status = 'error';
            $reportbd->completedate = time();
            $this->bp()->update($reportbd,$this->get_id());
            return false;
        }
        // завершаем работу с файлом
        fclose($resultfile);
        $this->log_string(date('d.m.Y H:i:s',time())."\n");
        $this->log_string('Report generation completed'."\n\n");
        // создаем относительный путь(moodledata)
        $filename = "{$this->get_id()}.dat"; 
        $report->filepath = $filename;
        $report->status = 'completed';
        return $this->bp()->update($report,$this->get_id());
         
    }
    /**
     * Метод проверяющий повторную генерацию файла
     *
     * @param object $order
     * @return mixed - id или false
     */
    public function is_generate($report)
    {
        if ( is_object($report) )
        {
            if ( $report->status != 'requested' AND ! is_null($report->completedate) )
            {// уже сгенерирован или была попытка его генерации
                return true;
            }
       }    
        return false;
    }
    
    /**
     * Метод, предусмотренный для расширения логики генерации файла
     */
    protected function generate_data($report)
    {
        return $report;
    }   
    /**
     * Загрузить данные отчета из БД
     *
     * @param integer $id
     * @param bool $withoutdata - не загружать данные полностью, а только сопоставить объект с ними
     * @return mixed - объект с данными или false
     */
    public function load()
    {
        if( ! $report = $this->bp()->get($this->get_id()) )
        {// Нет данных
            return false;
        }
        return $report;
    }
    /**
     * Загрузить данные отчета из файла
     *
     * @param integer $id
     * @param bool $withoutdata - не загружать данные полностью, а только сопоставить объект с ними
     * @return mixed - объект с данными или false
     */
    public function load_file()
    {
        GLOBAL $CFG;
        if( ! $reportbd = $this->load() )
        {// Нет данных
            return false;
        }
        if ( ! $this->is_generate($reportbd) )
        {// отчет еще не сгенерирован, загружать нечего
            return false;
        }
           
        
        if ( file_exists($reportbd->filepath) )
        {
            $filename = $reportbd->filepath;   
        }else 
        {
            $filename = $this->dof->plugin_path($this->plugintype(), $this->plugincode(), '/dat/'.$reportbd->filepath);
        }
              
        // загрузка большого файла отчета и десериализация 
        // данных могу занять очень много времени и памяти
        // поэтому на всякий случай увеличим лимиты
        dof_hugeprocess();
        
        if ( ! $resultfile = fopen($filename, 'r') )
        {
            return false;
        }
        if ( ! $report = unserialize(fread($resultfile, filesize($filename))) )
        {// не шмагла прочитать
            return false;
        }
        // генеририм данные пользователя
        $report = $this->load_data($report);
        // завершаем работу с файлом
        fclose($resultfile);
        // Добавляем в данные приказа поля из служебных данных
        // Чтобы их можно было использовать в шаблоне
        $report->_name         = $reportbd->name;
        $report->_departmentid = $reportbd->departmentid;
        $report->_personid     = $reportbd->personid;
        $report->_completedate = $reportbd->completedate;
        $report->_requestdate  = $reportbd->requestdate;

        return $report;
        
    }
    /**
     * Метод, предусмотренный для расширения логики загрузки данных из файла
     */
    protected function load_data($report)
    {
        return $report;
    }   
   
    /**
     * Получение ссылки на объект шаблона, "заправленный" данными
     * 
     * @param int $id - id приказа, либо будет использоваться загруженный
     * @return dof_modlib_templater_package 
     */
    public function template($id=null)
    {
        if (empty($this->templatertype)
            OR empty($this->templatercode)
            OR empty($this->templatertemplatename)
            OR ! $this->dof->plugin_exists('modlib', 'templater')
            )
        {
            // Нет никакого шаблона, или плагина modlib/templater
            return false;
        }
        // ID передали?
        if ( ! is_null($id))
        {// передали id
            $this->set_id($id);
        }
        $template = $this->load_file();
        // генерим данные пользователя
        $template = $this->template_data($template);
        // Возвращаем объект templater, которому уже переданы данные
        return $this->dof->modlib('templater')->template($this->templatertype, $this->templatercode, $template, $this->templatertemplatename);
    }
    
    /**
     * Метод, предусмотренный для расширения логики отображения данных отчета
     */
    protected function template_data($template)
    {
        return $template;
    }  
    
    public function show($id=null)
    {
        // ID передали?
        if (is_null($id))
        {
            $id = $this->get_id();
        }
        $order = $this->load_file();
        $order->id = $id;
        $str = $this->show_headers($order);
        $str .= $this->show_body($order);
        return $str;
    }
    
    protected function show_headers($order)
    {
        //
        return "{$order->id}<br />";
    }
    
    protected function show_body($order)
    {
        //
        return print_r($order,true);
    }
    
    protected function log_string($string,$display = false)
    {
        $path = $this->dof->plugin_path($this->plugintype(), $this->plugincode(), '/dat/reports/'.$this->code().'/'.$this->get_id().'.txt');
        
        $resultfile = fopen($path, 'a');
        // формируем данные для вставки в файл
        fputs($resultfile, $string);
        // завершаем работу с файлом
        fclose($resultfile);
        if ( $display )
        {
            $this->dof->mtrace(2, $string); 
        }
    }
}


?>