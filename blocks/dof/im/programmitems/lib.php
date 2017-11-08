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
// Copyright (C) 2008-2999  Evgenij Cigancov (Евгений Цыганцов)           //
// Copyright (C) 2008-2999  Ilia Smirnov (Илья Смирнов)                   // 
// Copyright (C) 2008-2999  Mariya Rojayskaya (Мария Рожайская)           // 
//                                                                        //
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
 * библиотека, для вызова из веб-страниц, подключает DOF.
 */ 

//загрузка библиотек верхнего уровня
require_once(dirname(realpath(__FILE__))."/../lib.php");

$meta = optional_param('meta', 0, PARAM_INT);
$addvars['meta'] = $meta;

/**
 * Класс для формирования приказа смены статуса
 */
class dof_im_programmitems_order_status
{
	/**
     * @var dof_control
     */
    protected $dof;
    protected $gradedata;
    //protected $code;
    
    function __construct(dof_control $dof, $gradedata)
    {
    	$this->dof = $dof;
        $this->gradedata = $gradedata;
        //$this->code = $code;
    }

    /** Сформировать приказ об изменении статуса программы
     * 
     * @return true or false
     */
    public function generate_order_status()
    {
        if ( ! $orderobj = $this->order_change_status() )
        {//ошибка  формировании приказа смены статуса
            return false;
        }
        if ( ! $orderid = $this->save_order_change_status($orderobj) )
        {//ошибка  при сохранении приказа смены статуса
            return false;
        }
        return $this->sign_and_execute_order($orderid);    
    }
    
    /** Формирует приказ - сменить статус
     * 
     * @return mixed object - данные приказа для сохранения
     * или bool false в случае неудачи
     */
    public function order_change_status()
    {
        //создаем объект для записи
        $orderobj = new stdClass();
        $this->dof->storage('persons')->get_bu(NULL,true);
        if ( ! $personid = $this->dof->storage('persons')->get_by_moodleid_id() )
		{// если id персоны не найден 
			return false;
		}
        //сохраняем автора приказа
        $orderobj->ownerid = $personid;
        //подразделение, к которому он относится
        if ( ! $teacher = $this->dof->storage('persons')->get($orderobj->ownerid) )
        {// пользователя, выставляющего оценку нет в базе данных
            return false;
        }
        // установим id подразделения из сведений об учителе
        $orderobj->departmentid = $teacher->departmentid;
        //дата создания приказа
        $orderobj->date = time();
        //добавляем данные, о которых приказ
        $orderobj->data = $this->get_change_status_fororder();
        return $orderobj;
    }
    
    /** Сохраняет данные приказа
     * @param object $orderobj - данные приказа для сохранения
     * @return mixed int - id приказа
     * или bool true - если приказ не создавался
     */
    public function save_order_change_status($orderobj)
    {
    	//подключаем методы работы с приказом
        $order = $this->dof->im('programmitems')->order('change_status');
        // сохраняем приказ в БД и привязываем экземпляр приказа к id
        $order->save($orderobj);
        // вернем id приказа
        return $order->get_id();
    }
    /** Подписывает и исполняет приказ
     * @param int $orderid - id приказа
     * @return bool true в случае успеха и false в случае неудачи 
     */
    public function sign_and_execute_order($orderid)
    {
    	//подключаем методы работы с приказом
    	if ( ! $order = $this->dof->im('programmitems')->order('change_status',$orderid) )
    	{// приказа нет - это ошибка
    		return false;
    	}    	
        // подписываем приказ
        if ( ! $personid = $this->dof->storage('persons')->get_by_moodleid_id() )
		{// если id персоны не найден 
			return false;
		}
        $order->sign($personid );
        //проверяем подписан ли приказ
        if ( ! $order->is_signed() )
        {//приказ не подписан
            return false;
        }
        //исполняем приказ';
        if ( ! $order->execute() )
        {//не удалось исполнить приказ
            return false;
        }
        return true;
    }
    /** Проверяет подписан ли приказ
     * @param int $orderid - id приказа
     * @return bool true если уже подписан и false если нет
     */
    public function is_signed($orderid)
    {
    	//подключаем методы работы с приказом
    	if ( ! $order = $this->dof->im('programmitems')->order('change_status',$orderid) )
    	{// приказа уже нет - будем считать что все нормально
    		return true;
    	}
        //проверяем подписан ли приказ
        if ( ! $order->is_signed() )
        {//приказ не подписан
            return false;
        }
        return true;
    }

    /** Формирует объект данных для приказа
     * @return object - объект для формирования приказа
     */
    private function get_change_status_fororder()
    {
    	//Структура приказа:
        $order = new stdClass();
		//поля сохранения смены статуса
		$order->programmitemid = $this->gradedata->id;//id периода
		$order->datechange = time();//дата смены статуса
		$order->oldstatus = $this->gradedata->oldstatus;//старый статус
		$order->newstatus = $this->gradedata->status;//новый статус

        return $order;
    }
}
?>