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
 * Базовый класс приказов
 *
 * @package     im
 * @subpackage  orders
 * @author      Dmitrii Shtolin <d.shtolin@gmail.com>
 * @copyright   2016
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

abstract class dof_storage_orders_baseorder
{
    /**
     * @var dof_control - Ссылка на объект $DOF
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
    protected $id;
    
    protected $progressbar;
    
    /** 
     * Конструктор
     * 
     * @param dof_control $dof - объект с методами ядра деканата
     */
    public function __construct(dof_control $dof, $id = NULL)
    {
        // Сохраняем ссылку на DOF, чтоб вызывать его через $this->dof
        $this->dof = $dof;
        $this->id = $id;
    }
    
    /**
     * Тип плагина, объявившего тип приказа
     */
    abstract public function plugintype();
    
    /**
     * Код плагина, объявившего тип приказа
     */
    abstract public function plugincode();
    
    /**
     * Код типа приказа
     */
    abstract public function code();
    
    /**
     * Тип базового плагина storage/orders
     *
     * @return string
     */
    public function baseptype()
    {
        return 'storage';
    }
    
    /**
     * Код базового плагина storage/orders
     *
     * @return string
     */
    public function basepcode()
    {
        return 'orders'; 
    }
    
    /**
     * Возвразает ссылку на базовый плагин
     * 
     * @return dof_storage_orders
     */
    protected function bp()
    {
        return $this->dof->storage($this->basepcode());
    }
    
    /**
     * Получить ID текущего приказа
     *
     * @return integer
     */
    public function get_id()
    {
        return $this->id;
    }
    
    /**
     * Установить ID текущего приказа
     *
     * @param integer $id - ID текущего приказа
     * 
     * @return integer
     */
    protected function set_id($id)
    {
        return $this->id = $id;
    }
    
    /**
     * Загрузить данные  приказ из БД
     *
     * @param integer $id - ID текущего приказа
     * @param bool $withoutdata - не загружать данные полностью, а только сопоставить объект с ними
     * 
     * @return mixed - объект с данными или false
     */
    public function load($id,$withoutdata=false)
    {
        // Получаем объект из БД
        if (!($order = $this->bp()->get($id)))
        {
            return false;
        }
        
        // Проверяем, того ли типа плагин
        if (
               $order->plugintype !== $this->plugintype()
            OR $order->plugincode !== $this->plugincode()
            OR $order->code !== $this->code())
        {
            return false;
        }
        // Убираем лишние данные
        unset($order->plugintype);
        unset($order->plugincode);
        unset($order->code);
        
        $this->set_id($id);

        if ( $withoutdata )
        {// Данных не просили
            if ( isset($order->sdata) )
            {
                unset($order->sdata);
            }
            
            return $order;
        }
        $order->data = new stdClass();
        if ( ! empty($order->sdata) )
        {// Убираем слеши из строки, десерализуем, и рекурсивно добавляем слеши к данным
            $order->data = unserialize($order->sdata);
        }else
        {
            $order->data = $this->load_data_base();
        }
        
        if ( isset($order->sdata) )
        {// если приказ старого типа - то проверим, есть ли в нем старые сериализованные данные
            // @todo удалить это условия когда удалим поле sdata из базы
            // сейчас оно оставлено для совместимости
            unset($order->sdata);
        }
        
        // Пропускаем данные через обработчик, добавляющий данные из других справочников
        // Обработчик должен возвращать данные в том же порядке, в котором они всегда извлекаются
        // иначе не сойдется цифровая подпись
        $order = $this->load_data($order);
        
        // Добавляем в данные приказа поля из служебных данных
        // Чтобы их можно было использовать в шаблоне
        $order->data->_departmentid = $order->departmentid;
        $order->data->_ownerid      = $order->ownerid;
        $order->data->_signerid     = $order->signerid;
        $order->data->_date         = $order->date;
        $order->data->_signdate     = $order->signdate;
        $order->data->_exdate       = $order->exdate;
        $order->data->_changedate   = $order->changedate; 
        
        return $order;
    }
    
    /** 
     * Базовая функция загрузки данных в приказ. Собирает данные приказа по частям из таблицы orderdata
     * 
     * @todo переписать этот метод, когда данные приказа будут храниться полностью в виде дерева
     * 
     * @return object|null объект с данными приказа
     */
    protected function load_data_base()
    {
        // определяем, есть ли какие-нибудь данные для приказа
        if ( ! $orderdatas = $this->dof->storage('orderdata')->
            get_records(array('orderid'=>$this->id), 'varnum ASC' ) )
        {// данных нет - ничего собирать не надо
            return new stdClass;
        }
        $orderdata = new stdClass();
        foreach ( $orderdatas as $data )
        {// собираем объект из отдельных записей, в нужном порядке
            if ( $data->scalar )
            {// это скалярное значение, записываем его как есть
                $orderdata->{$data->firstlvlname} = $data->data;
            }else
            {// это объект, его нужно десериализовать и записать
                $orderdata->{$data->firstlvlname} = unserialize($data->data);
            }
        }
        
        return $orderdata;
    }
    /**
     * Метод для дополнения операции загрузкци данных
     */
    protected function load_data($order)
    {
        return $order;
    }
    /**
     * Сохранить данные приказа в БД
     *
     * @param object $order
     * @return mixed - id или false
     */
    public function save($order)
    {
        $order = clone $order;
        
        // Добавляем поля, идентифицирующие плагин
        $order->plugintype = $this->plugintype();
        $order->plugincode = $this->plugincode();
        //Получаем версию плагина, который генерит приказ
        $order->pluginversion = $this->dof->{$order->plugintype}($order->plugincode)->version();
        $order->code = $this->code();
        // Убираем поля, которые нельзя редактировать напрямую
        unset($order->exdate);
        unset($order->changedate);
        unset($order->status);
        unset($order->sdata);
        unset($order->signerid);
        unset($order->signature);
        unset($order->signdate);
        // Удаляем автоматически-заполняемые поля из служебных данных
        unset($order->data->_departmentid);
        unset($order->data->_ownerid);
        unset($order->data->_signerid);
        unset($order->data->_date);
        unset($order->data->_signdate);
        unset($order->data->_exdate);
        unset($order->data->_changedate);
        // перезапишем
        $data = $order->data;
        unset($order->data);
        
        // Сохрангяем в БД
        if ($this->get_id())
        {
            // разбираем в справочник orderdata
            if ( $this->save_data($order,$data) )
            {// все прошло хорошо - удаляем данные
                $order->sdata = NULL;
            }
            // Обновляем
            $this->log_string(date('d.m.Y H:i:s',time())."\n");
            $this->log_string('Save old order'."\n\n");
            return $this->bp()->update($order,$this->get_id());
        }elseif ($id = $this->bp()->insert($order))
        {
            $order->id = $id;
            // разбираем в справочник orderdata
            if ( $this->save_data($order,$data) )
            {// все прошло хорошо - удаляем данные
                $order->sdata = NULL;
                // затрем дату
                $this->bp()->update($order,$id);
            }
            // Сохраняем id новой
            $this->set_id($id);
            $this->log_string(date('d.m.Y H:i:s',time())."\n");
            $this->log_string('Save new order'."\n\n");
        }
        // Не шмагла я!
        return true;
    }
    /**
     * Метод, предусмотренный для расширения логики сохранения
     * тут происходит сохранение данных в справочник orderdata- сериализация данных
     * @param object $order - сам ордер
     * @param object/array $data - данные масива для сериалицации(они тут разбираются)
     */
    protected function save_data($order, $data)
    {
        // переменная для подсчета НОМЕРА переменной(чтоб правильно собрать)
        $i = 0;
        $obj = new stdClass();
        // сохраняем ордер, на который ссылается
        $obj->orderid = $order->id;
        foreach ( $data as $key=>$value )
        {// перебираем сами данные
            $i++;
            // запоминаме имя
            $obj->firstlvlname = $key;
            // порядковый номер имени
            $obj->varnum = $i;

            if ( is_array($value) OR is_object($value) )
            {// если не расшипился до значений - сериализуеи и сохраняем
                // не скаляр
                $obj->scalar = 0;
                $obj->data = serialize($value);
                // запишем данные поле в индекс
                // исключение проблемы экранирования кавычки
                $obj->ind = mb_substr($obj->data,0,254,'utf-8');
            }else 
            {// обычный скаляр
                $obj->scalar = 1;
                $obj->data = $value;
                // запишем данные в поле индекс
                $obj->ind = mb_substr($obj->data,0,254,'utf-8');
            }
            // сохраняем запись
            if ( $element = $this->dof->storage('orderdata')->get_records(array('orderid'=>$order->id, 'firstlvlname'=>$key, 'varnum'=>$i)) )
            {// запись существует
                $this->dof->storage('orderdata')->update($obj, current($element)->id);
            }else 
            {// вставляем
                $this->dof->storage('orderdata')->insert($obj);
            }
            
        }

        return $order;
    }
    /**
     * Подписать приказ (без подписания приказ не должен исполняться)
     */
    public function sign($signerid)
    {
        if(!$order = $this->load($this->get_id(),false))
        {
            // Нет данных
            return false;
        }
        // Готовим данные для цифровой подписи
        $order2 = new stdClass();
        $order->signerid = $signerid;
        $order2->signerid = $signerid;
        $order->signdate = time();
        $order2->signdate = time();
        // Формируем цифровую подпись
        $order2->signature = $this->make_signature($order);
        // Сохраняем подпись в БД
        $this->log_string(date('d.m.Y H:i:s',time())."\n");
        $this->log_string('Sign order'."\n\n");
        return $this->bp()->update($order2,$this->get_id());
    }
    /**
     * Определяет, подписан ли приказ
     */
    public function is_signed()
    {
        if(!$order = $this->load($this->get_id(),false))
        {
            // Нет данных
            return false;
        }
        return $this->check_sign($order);
    }
    /**
     * Сформировать подпись
     */
    protected function make_signature($order)
    {
        // Удаляем автоматически-заполняемые поля из служебных данных
        unset($order->data->_departmentid);
        unset($order->data->_ownerid);
        unset($order->data->_signerid);
        unset($order->data->_date);
        unset($order->data->_signdate);
        unset($order->data->_exdate);
        unset($order->data->_changedate);
        // Создаем подпись
        $sign = sha1($order->signerid.'freedeansoffice'.$order->signdate.$order->date.trim($order->num).serialize($order->data));
        // echo "\nMake sign from: ";var_dump($order->data); echo " \nSign: {$sign}";
        return $sign;
    }
    /**
     * Проверяет корректность подписи
     */
    protected function check_sign($order)
    {
        if (
                !isset($order->signature)
             OR !isset($order->signerid) 
             OR !isset($order->signdate)
             OR !$order->signdate
           )
        {
            // Нечего проверять
            // echo 'qqq';var_dump($order);
            return false;               
        }
        // Проверяем совпадение сигнатур
        $result = ($order->signature === $this->make_signature($order));
        // var_dump($result);
        // echo "\n{$order->signature}\n{$this->make_signature($order)}";
        return $result;
    }
    /**
     * Исполнить приказ (выполнить операции и изменить статус на "исполнен")
     */
    public function execute()
    {
        if( ! $order = $this->load($this->get_id(),false) )
        {
            // Нет данных
            return false;
        }
        // Не исполнен ли уже приказ?
        if ( isset($order->exdate) and $order->exdate )
        {
            // Приказ уже был исполнен
            return false;
        }
        // проверяем подпись
        if ( ! $this->check_sign($order) )
        {
            // Подпись не верна или приказ не подписан
            return false;
        }
        if ( isset($order->crondate) AND $order->crondate > time() )
        {// приказ выполняется по крону и дата еще не наступила
            return false;
        }
        $this->log_string(date('d.m.Y H:i:s',time())."\n");
        $this->log_string('Order execution started'."\n\n");
        // Все хорошо - можем исполнять';
        // Вызываем исполнение
        if ( ! $this->execute_actions($order) )
        {
            // Исполнение завершилось неудачей
            return false;
        }
        $this->log_string(date('d.m.Y H:i:s',time())."\n");
        $this->log_string('Order execution completed'."\n\n");
        // Записываем время исполнения
        $order2 = new stdClass();
        $order2->exdate = time();
        $this->dof->workflow('orders')->change($this->get_id(),'executed');
        return $this->bp()->update($order2,$this->get_id());
        
    }
    /** Проверяет, исполнен ли приказ
     * 
     * @return bool
     */
    public function is_executed()
    {
        if( ! $order = $this->load($this->get_id(),false) )
        {// Нет данных о приказе
            return false;
        }
        if ( ! $order->exdate )
        {// дата исполнения приказа отсутствует - значит он еще не был исполнен
            return false;
        }
        return true;
    }
    /**
     * Исполнить действия, сопутствующие исполнению приказа 
     * (переопределяется в дочернем классе)
     *
     * @param object $order
     * @return bool
     */
    protected function execute_actions($order)
    {
        return true;
    }

    /**
     * Сохранить заметки к текущему приказу (не считается изменением приказа)
     */
    public function notes($notice)
    {
        if (!$this->get_id()
            OR !$this->bp()->is_exists($this->get_id()))
        {
            // Объекта нет
            return false;
        }
        // Новый объект
        $order = new stdClass();
        $order->notes = $notice;
        return $this->bp()->update($order,$this->get_id());
    }

    
    /**
     * Сохраняет номер приказа
     * 
     * @param string $num - номер приказа
     * @return boolean
     */
    public function save_num($num)
    {
        if (!$this->get_id()
            OR !$this->bp()->is_exists($this->get_id()))
        {
            // Объекта нет
            return false;
        }
        $order = $this->dof->storage('orders')->get($this->get_id());
        if ( $this->dof->storage('orders')->is_unique_order_num($num,$order->departmentid) )
        {//номер договора соответствует требованиям уникальности
            // Новый объект
            $neworderdata = new stdClass();
            $neworderdata->num = $num;
            return $this->bp()->update($neworderdata,$this->get_id());
        }
        else
        {//номер договора не уникален
            return false;
        }
    }
    
    /**
     * Получение ссылки на объект шаблона, "заправленный" данными
     * @param int $id - id приказа, либо будет использоваться загруженный
     * @return dof_modlib_templater_package 
     */
    public function template($id=null)
    {
        if (empty($this->templatertype)
            OR empty($this->templatercode)
            OR empty($this->templatertemplatename)
            OR $this->dof->plugin_exists('modlib', 'templater')
            )
        {
            // Нет никакого шаблона, или плагина modlib/templater
            return false;
        }
        // ID передали?
        if (is_null($id))
        {
            $id = $this->get_id();
        }
        // Возвращаем объект templater, которому уже переданы данные
        return $this->dof->modlib('templater')->template($this->templatertype, $this->templatercode, $this->load($id,false)->date, $this->templatertemplatename);
    }
    
    public function show_editform()
    {
        
    }
    
    public function show($id=null)
    {
        // ID передали?
        if (is_null($id))
        {
            $id = $this->get_id();
        }
        $order = $this->load($id,false);
        $str = $this->show_headers($order);
        $str .= $this->show_body($order);
        return $str;
    }
    
    public function show_tablerow( $addvars = [] )
    {
        $id = $this->get_id();
        $order = $this->load($id,false);
        
        $addvars['id'] = $order->id;
        
        // иконка просмотра
        $actions = $this->dof->modlib('ig')->icon('view_full',
            $this->dof->url_im('orders', '/view.php', $addvars), []);
        
        if ( ! in_array($order->status,
            array_keys($this->dof->workflow('orders')->get_meta_list('junk'))) )
        {
            //приказ не мусорный 
            // иконка редактирования
            $actions .= $this->dof->modlib('ig')->icon('edit_full',
                $this->dof->url_im('orders', '/edit.php', $addvars), []);
        }
        
        $owner = '';
        if( ! empty($order->ownerid) )
        {
            $owner = $this->dof->storage('persons')->get_fullname($order->ownerid);
        }

        $signer = '';
        if( ! empty($order->signerid) )
        {
            $signer = $this->dof->storage('persons')->get_fullname($order->signerid);
        }

        $date = '';
        if( ! empty($order->date) )
        {
            $date = dof_userdate($order->date, '%d/%m/%Y');
        }

        $signdate = '';
        if( ! empty($order->signdate) )
        {
            $signdate = dof_userdate($order->signdate, '%d/%m/%Y');
        }

        $exdate = '';
        if( ! empty($order->exdate) )
        {
            $exdate = dof_userdate($order->exdate, '%d/%m/%Y');
        }
        
        if (
            !isset($order->signature)
            OR !isset($order->signerid)
            OR !isset($order->signdate)
            OR !$order->signdate
            )
        {
            $validsignature = '';
        } else 
        {
            $validsignature = $this->check_sign($order) ? $this->dof->modlib('ig')->igs('yes') : $this->dof->modlib('ig')->igs('no');
        }
        
        $row = [
            'actions'=>$actions,
            'id'=>$order->id,
            'num'=>$order->num,
            'date'=>$date,
            'owner'=>$owner,
            'signer'=>$signer,
            'signdate'=>$signdate,
            'validsignature'=>$validsignature,
            'exdate'=>$exdate,
            'notes'=>$order->notes,
            'status'=>$this->dof->workflow('orders')->get_name($order->status)
        ];
        
        //return $this->show_header($order);
        return $row;
    }

    public function show_tableheader()
    {
        return [
            $this->dof->get_string('table_baseorder_actions','orders', null,'storage'),
            $this->dof->get_string('table_baseorder_id','orders', null,'storage'),
            $this->dof->get_string('table_baseorder_num','orders', null,'storage'),
            $this->dof->get_string('table_baseorder_date','orders', null,'storage'),
            $this->dof->get_string('table_baseorder_owner','orders', null,'storage'),
            $this->dof->get_string('table_baseorder_signer','orders', null,'storage'),
            $this->dof->get_string('table_baseorder_signdate','orders', null,'storage'),
            $this->dof->get_string('table_baseorder_validsignature','orders', null,'storage'),
            $this->dof->get_string('table_baseorder_exdate','orders', null,'storage'),
            $this->dof->get_string('table_baseorder_notes','orders', null,'storage'),
            $this->dof->get_string('table_baseorder_status','orders', null,'storage')
        ];
    }
    
    public function show_table($data)
    {
        $table = new stdClass();
        $table->tablealign = "center";
        $table->cellpadding = 5;
        $table->cellspacing = 5;
        $table->head = $this->show_tableheader();
        $table->align = ['center','center','center','center','center','center','center','center'];
        $table->data = $data;
        return $this->dof->modlib('widgets')->print_table($table,true);
    }

    protected function show_header($order)
    {
        return "{$order->id}<br />";
    }
    
    /**
     * @deprecated название не по стандарту, используйте show_header
     * 
     * @param object $order
     * @return string
     */
    protected function show_headers($order)
    {
        return show_header($order);
    }
        
    protected function show_body($order)
    {
        //
        return print_r($order->data,true);
    }
    
    public function progressbar($name='', $width=500, $auto_create=false)
    {
        $this->progressbar = $this->dof->modlib('widgets')->progressbar($name, $width, $auto_create);
    }
    
    public function progressbar_update($cur_amount,$max_amount,$message,$display = false)
    {
        $this->progressbar->update($cur_amount,$max_amount,$message);
        $this->log_string($message,$display);
    }
    
    
    protected function log_string($string,$display = false)
    {
        $path = $this->dof->plugin_path($this->plugintype(), $this->plugincode(), '/dat/orders/'.$this->code().'/'.$this->get_id().'.txt');
        
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
    
    
    
    /**
     * Формирование формы для фильтрации
     *
     * @param array $addvars
     * @param array $defaultvalues
     * @return dof_storage_orders_baseorder_filter
     */
    public function form_filter($addvars,$defaultvalues)
    {
        // Подключаем дополнительную библиотеку
        require_once $this->dof->plugin_path('storage', 'orders','/classes/baseorder/form.php');
                
        // Сформируем url формы
        $url = $this->dof->url_im('orders', '/list.php', $addvars);
    
        $customdata = new stdClass;
        $customdata->dof = $this->dof;
        $customdata->addvars = $addvars;
        $customdata->defaultvalues = $defaultvalues;
    
        // Сформируем форму
        $formfilter = new dof_storage_orders_baseorder_filter($url, $customdata);
        //установим значения по умолчанию
        
        $formfilter->set_data($defaultvalues);
        
        return $formfilter;
    }
    /**
     * Формирование формы для редактипрования
     * 
     * @param array $addvars
     * @param array $defaultvalues
     * @return dof_storage_orders_baseorder_edit
     */
    public function form_edit($addvars, $defaultvalues)
    {
        // Подключаем дополнительную библиотеку
        require_once $this->dof->plugin_path('storage', 'orders','/classes/baseorder/form.php');
                
        // Сформируем url формы
        $url = $this->dof->url_im('orders', '/edit.php', $addvars);
    
        $customdata = new stdClass;
        $customdata->dof = $this->dof;
        $customdata->addvars = $addvars;
        $customdata->defaultvalues = $defaultvalues;
    
        // Сформируем форму
        $formedit = new dof_storage_orders_baseorder_edit($url, $customdata);
        //установим значения по умолчанию
        $formedit->set_data($defaultvalues);
    
        return $formedit;
    }
    /**
     * Формирование формы для смены статуса
     * 
     * @param array $addvars
     * @param array $defaultvalues
     * @return dof_storage_orders_baseorder_change_status
     */
    public function form_change_status($addvars, $defaultvalues)
    {
        // Подключаем дополнительную библиотеку
        require_once $this->dof->plugin_path('storage', 'orders','/classes/baseorder/form.php');
                
        // Сформируем url формы
        $url = $this->dof->url_im('orders', '/change_status.php', $addvars);
    
        $customdata = new stdClass;
        $customdata->dof = $this->dof;
        $customdata->addvars = $addvars;
        $customdata->defaultvalues = $defaultvalues;
    
        // Сформируем форму
        $formchangestatus = new dof_storage_orders_baseorder_change_status($url, $customdata);
        //установим значения по умолчанию
        $formchangestatus->set_data($defaultvalues);
    
        return $formchangestatus;
    }
}


?>