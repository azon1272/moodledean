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


/** Класс стандартных функций интерфейса
 * 
 */
class dof_modlib_phpexcel implements dof_plugin_modlib
{
    protected $dof;
    
    protected $php_excel;
    // **********************************************
    // Методы, предусмотренные интерфейсом plugin
    // **********************************************
    /** 
     * Метод, реализующий инсталяцию плагина в систему
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
    /** 
     * Метод, реализующий обновление плагина в системе
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
    /** 
     * Возвращает версию установленного плагина
     * @return string
     * @access public
     */
    public function version()
    {
        return 2013051400;
    }
    /** 
     * Возвращает версии интерфейса Деканата, 
     * с которыми этот плагин может работать
     * @return string
     * @access public
     */
    public function compat_dof()
    {
        return 'aquarium';
    }

    /** 
     * Возвращает версии стандарта плагина этого типа, 
     * которым этот плагин соответствует
     * @return string
     * @access public
     */
    public function compat()
    {
        return 'neon';
    }
    
    /** 
     * Возвращает тип плагина
     * @return string 
     * @access public
     */
    public function type()
    {
        return 'modlib';
    }
    /** 
     * Возвращает короткое имя плагина
     * Оно должно быть уникально среди плагинов этого типа
     * @return string
     * @access public
     */
    public function code()
    {
        return 'phpexcel';
    }
    /** 
     * Возвращает список плагинов, 
     * без которых этот плагин работать не может
     * @return array
     * @access public
     */
    public function need_plugins()
    {
        return array();
    }
    /** 
     * Список обрабатываемых плагином событий 
     * @return array - array(array('plugintype'=>..,'plugincode'=>..,'eventcode'=>..),...)
     * @access public
     */
    public function list_catch_events()
    {
        return array();
    }
    /** 
     * Требуется ли запуск cron в плагине
     * @return bool
     * @access public
     */
    public function is_cron()
    {
        return false;
    }
    /** 
     * Проверяет полномочия на совершение действий
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
    /** 
     * Обработать событие
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
    /** 
     * Запустить обработку периодических процессов
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
    /** 
     * Обработать задание, отложенное ранее в связи с его длительностью
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
     * Конструктор
     */
    public function __construct($dof)
    {
        $this->dof = $dof;
        
        // родключаем основной класс библиотеки
        require_once("lib/PHPExcel.php");
        
        $this->php_excel = new PHPExcel();
    }
    
    /** Версия ядра библиотеки phpexcel. Изменяется каждый раз при обновлении ядра
     *
     * @return string
     */
    public function phpexcel_version()
    {
        return '1.7.8';
    }
       
    /**
     * Генерация excel файла
     * @param array $data - данные для экспорта
     * @param array $headers - заголовки данных
     * @param array $format - формат отправляемых данных
     * @return file|false
     */
    public function send_file($data, $headers=null, $format='')
    {  
        $filename = "sync_dnevniru_export_".$format."(".dof_userdate(time(),'%Y-%m-%d').").xlsx";
       
        // удаляем базовый worksheet
        $this->php_excel->removeSheetByIndex(0);
                
        $it = 0;
        foreach ($data as $key => $tab)
        {
            $col = 0; $row = 1;
            
            // создаем новую вкладку
            $title = $this->dof->get_string($key, 'dnevnikru', NULL, 'sync');
            $this->php_excel->addSheet(new PHPExcel_Worksheet($this->php_excel, $title), $it+1);
            $this->php_excel->setActiveSheetIndex($it);
            
            $worksheet = $this->php_excel->getActiveSheet();
            
            if ( is_array($headers) AND !empty($headers) )
            {// существуют заголовки данных - выводим
                foreach ($headers[$key] as $header)
                {// устанавливаем и именуем колонки
                    $str = $this->dof->get_string($header, 'dnevnikru', NULL, 'sync');
                    $worksheet->setCellValueByColumnAndRow($col, 1, $header);
                    ++$col;
                }
                ++$row;
            }
              
            foreach ($tab as $one)
            {// заполняем вкладку данными
                $col = 0;
                foreach ($one as $str)
                {
                    $worksheet->setCellValueByColumnAndRow($col++, $row, $str);
                }
                ++$row;
            }
            
            $toCol = $worksheet->getColumnDimension($worksheet->getHighestColumn())->getColumnIndex();
            for($i = 'A'; $i !== $toCol; $i++) 
            {// устанавливаем ширину колонок
                $worksheet->getColumnDimension($i)->setWidth(12);
            }
            $worksheet->calculateColumnWidths();
            
            ++$it;
        }
        
        // сохраняем файл, отправляем на выгрузку
        $objWriter = new PHPExcel_Writer_Excel2007($this->php_excel);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $objWriter->save('php://output');
        exit();
    }
}
?>