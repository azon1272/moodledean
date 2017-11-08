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


//загрузка библиотек верхнего уровня
require_once(dirname(realpath(__FILE__))."/../lib.php");


/**
 * Класс для формирования приказа
 */
class dof_im_learningorders_ordertransfer 
{
    /**
     * @var dof_control
     */
    protected $dof;
    public $order;
    public function __construct($dof, $orderid = null)
    {
        global $addvars;
        $this->dof = $dof;
        if ( is_null($orderid) )
        {
            $order = $this->dof->im('learningorders')->order('transfer');
            // сохраняем новый приказ
            $orderobj = new stdClass();
            if ( ! $personid = $this->dof->storage('persons')->get_by_moodleid_id() )
    		{// если id персоны не найден 
    			return false;
    		}
    		//сохраняем автора приказа
            $orderobj->ownerid = $personid;
            
            if ( isset($addvars['departmentid']) AND $addvars['departmentid'] )
            {// установим выбранное на странице id подразделения 
                $orderobj->departmentid = $addvars['departmentid'];
            }else
            {// установим id подразделения из сведений о том кто формирует приказ
                $orderobj->departmentid = $this->dof->storage('persons')->get_field($personid,'departmentid');
            }
            
            //дата создания приказа
            $orderobj->date = time();
            //добавляем данные, о которых приказ
            $orderobj->data = new stdClass();
            if( isset($addvars['departmentid']) AND $addvars['departmentid'] )
            {
                $orderobj->data->departments = array($addvars['departmentid']);
            }
            // сохраняем приказ в БД и привязываем экземпляр приказа к id
            $order->save($orderobj);
        }else
        {
            $order = $this->dof->im('learningorders')->order('transfer',$orderid);
        }
        $this->order = $order;
    } 
    
    /**
     * Метод извлекает данные приказа
     * @return object - объект приказа
     */    
    public function get_order_data()
    {
        return $this->order->load($this->order->get_id());
    }
    
    /**
     * Метод извлекает данные периоды из приказа
     * @return array or false
     */    
    public function set_period()
    {// обьявим начальные даные
        $ages = array();
        $ages['from'] = array();
        $ages['where'] = array();
        $ages['base'] = null;
        // берем объект - приказ
        if ( ! $object = $this->get_order_data() )
        {// не получили приказ
            return false;
        }
        if (isset($object->data) AND isset($object->data->ages) )
        {// усли данные о периодах в приказе есть
            if ( isset($object->data->ages->from) )
            {// запомним массив периодов откуда
                $ages['from'] = $object->data->ages->from;            
            }
            if ( isset($object->data->ages->where) )
            {// и массив периодов куда
                $ages['where'] = $object->data->ages->where; 
                $ages['base'] = $object->data->ages->base;       
            }
        }
        // возвратим массив массивов
        return $ages;
    }

    /**
     * Метод извлекает данные подразделения из приказа
     * @return array or false
     */    
    public function set_depart()
    {// обьявим начальные даные
        $departments = array();
        // берем объект - приказ
        if ( ! $object = $this->get_order_data() )
        {// не получили приказ
            return false;
        }
        if (isset($object->data) AND isset($object->data->departments) )
        {// усли данные о периодах в приказе есть
            $departments = $object->data->departments; 
        }
        // возвратим массив массивов
        return $departments;
    }    
    
    /** Сохраняет массив переводов в данные приказа
     * @param array $from - массив периодов откуда переводим
     * @param array $where - массив периодов куда переводим
     * @param array $depart - массив подразделений, которые переводим переводим
     * @return unknown_type
     */
    public function add_ages_in_order($from = null, $where = null, $depart = null)
    {
        $rez = true;
        if ( ! is_null($from) )
        {// если передан массив для передачи откуда
            // извлечем данные из приказа
            $orderdata = $this->get_order_data();
            if ( empty($orderdata->data->ages) )
            {
                $orderdata->data->ages = new stdClass();
            }
            if ( isset($orderdata->data->ages->from) )
            {// если там что-то уже было - добавим к уже имеющимся
                $orderdata->data->ages->from = array_unique(array_merge($orderdata->data->ages->from,$from));
            }else
            {// не было - добавим так
                $orderdata->data->ages->from = $from;
            }
            // сохраняем в приказ
            $rez = $rez AND $this->order->save($orderdata);   
        }
        if ( ! is_null($where) )
        {// если передан массив для передачи куда
            // извлечем данные из приказа
            $orderdata = $this->get_order_data();
            if ( empty($orderdata->data->ages) )
            {
                $orderdata->data->ages = new stdClass();
            }
            if ( isset($orderdata->data->ages->where) )
            {// если там что-то уже было - добавим к уже имеющимся
                $orderdata->data->ages->where = array_unique(array_merge($orderdata->data->ages->where,$where));
            }else
            {// не было - добавим так
                $orderdata->data->ages->where = $where;
                // первый элемент добавим как основной
                $orderdata->data->ages->base = $where[0];
            }
            // сохраняем в приказ
            $rez = $rez AND $this->order->save($orderdata);
        }
        if ( ! is_null($depart) )
        {// если передан массив для передачи куда
            // извлечем данные из приказа
            $orderdata = $this->get_order_data();
            if ( isset($orderdata->data->departments) )
            {// если там что-то уже было - добавим к уже имеющимся
                $orderdata->data->departments = array_unique(array_merge($orderdata->data->departments,$depart));
            }else
            {// не было - добавим так
                $orderdata->data->departments = $depart;
            }
            // сохраняем в приказ
            $rez = $rez AND $this->order->save($orderdata);
        }
        return $rez;
    }
  
    /**Удаляет все записи из $list, совпадающие с записями из $dellist
     * 
     * @param array $dellist - список id периодов, которые нужно удалить
     * @param array $list - список id периодов, из которого нужно удалить записи
     * @return array - изменннная $list 
     */
    protected function delete_ages_by_list( $dellist, $list)
    {
        if($dellist AND is_array($dellist) AND $list AND is_array($list) )
        {//проверим, переданы ли нам списки
            foreach($dellist as $ageid)
            {
                $key = array_search($ageid,$list);
                if ( !($key === false ) ) 
                {// удалим значение, если оно есть
                        unset($list[$key]);
                }
            }
        }
        return $list;
    }
    
    /**Удаляет все записи периодов из приказа, совпадающие с записями из соответствующих списков $from, $where 
     * 
     * @param array $from [optional] - список id периодов, которые нужно удалить из $order->data->ages->from (по умолчанию = array())
     * @param array $where [optional] - список id периодов, которые нужно удалить из $order->data->ages->where (по умолчанию = array())
     * @return 
     */
    public function delete_ages($from = null, $where = null, $depart = null)
    {
        if( ! $order = $this->get_order_data() )
        {//если не смогли получить приказ, то нет смысла продолжать
            return false;
        }
        if( ( ! is_null($from) OR ! is_null($where) ) AND
            ( ! isset($order->data->ages) OR ! is_object($order->data->ages) ) )
        {//если не установлена data, то нет смысла продолжать
            return false;
        }
        if( ! is_null($from) )
        {//если установлен список $from
            $order->data->ages->from = $this->delete_ages_by_list( $from, $order->data->ages->from);
        }
        if( ! is_null($where) )
        {//если установлен список $where
            $order->data->ages->where = $this->delete_ages_by_list( $where, $order->data->ages->where);
            if( isset($order->data->ages->where) AND $order->data->ages->where )
            { // берем текущий элемент
                $order->data->ages->base = key($order->data->ages->where);
            }else
            {
                $order->data->ages->base = NULL;
            }
        }
        if( ! is_null($depart) )
        {//если установлен список $depart
            $order->data->departments = $this->delete_ages_by_list( $depart, $order->data->departments);
        }
        return $this->order->save($order);
    }
    
    /**Меняет базовый период
     * 
     * @param int $number - номер периода в списке where
     * @return 
     */
    public function chage_base( $number)
    {
        if ( ! $order = $this->get_order_data() )
        {//если не смогли получить приказ, то нет смысла продолжать
            return false;
        }
        if ( ! isset($order->data->ages->where) OR ! is_array($order->data->ages->where) )
        {//если не установлен список периодов where, то тоже нет смысла продолжать
            return false;
        }
        if ( ! in_array($number,$order->data->ages->where ) )
        {//если в списке where нет элемента под номером $number, то опять-таки нет смысла продолжать
            return false;
        }
        $order->data->ages->base = $number;
        return $this->order->save($order);
    }
    
    /** Создает монстра
     * Формирует и сохраняет данные о переводе в приказ
     * @return bool true или false в зависимости от того удалось ли сохранить монстра в приказ
     */
    public function formating_transfer_order()
    {
        if( ! $orderdata = $this->get_order_data() )
        {//если не смогли получить приказ, то нет смысла продолжать
            return false;
        }
        if ( empty($orderdata->data->ages->from) OR empty($orderdata->data->ages->where) 
                  OR empty($orderdata->data->ages->base) )
        {// если периоды не указаны, то мы не сможем создать приказ
            return false;
        }
        if ( empty($orderdata->data->departments) )
        {// если нам попался пустой массив подразделений 
            // удалим его от греха подальше
            unset($orderdata->data->departments);
        }
        // начинаем создавать монстра
        $student = new stdClass();
        $agroups = array();
        // смотрим историю обучения групп
        if ( $historygroups = $this->dof->storage('agrouphistory')->get_records(array
                              ('ageid'=>$orderdata->data->ages->from)) )
        {// данные есть - занесем их всех в монстра
            foreach ( $historygroups as $historygroup )
            {
                if ( in_array($this->dof->storage('agroups')->get_field($historygroup->agroupid,'status'),
                     array('plan','active','formated')) )
                {// если группа удовлетворяет нужным статусам
                    if ( isset($orderdata->data->departments) AND 
                        ! in_array($this->dof->storage('agroups')->get_field($historygroup->agroupid,'departmentid'),
                            $orderdata->data->departments) )
                    {// но не находится в выбранном подразделении - пропустим
                        continue;
                    }
                    if ( $this->dof->storage('agroups')->get_field($historygroup->agroupid,'agenum')
                         != $historygroup->agenum )
                    {// старая история групп - не включаем группу в приказ
                        continue;
                    }
                    $programmid = $this->dof->storage('agroups')->get_field($historygroup->agroupid,'programmid');
                    if ( $this->dof->storage('agroups')->get_field($historygroup->agroupid,'agenum')
                             >= $this->dof->storage('programms')->get_field($programmid,'agenums') )
                    {// выпускная группа - добавим в исключенные из приказа
                        $agroups[$historygroup->agroupid] = 
                         $this->set_values_for_group($historygroup->agroupid, $historygroup->ageid, $orderdata->data->ages->base, false, true);
                    }else
                    {// остальных переводим
                        $agroups[$historygroup->agroupid] = 
                         $this->set_values_for_group($historygroup->agroupid, $historygroup->ageid, $orderdata->data->ages->base);
                    }
                    $student->transfer[$historygroup->agenum][$historygroup->agroupid] = null;
                    $student->condtransfer[$historygroup->agenum][$historygroup->agroupid] = null;
                }
            }
        } 
        // смотрим историю обучения учащихся
        if ( $historylearning = $this->dof->storage('learninghistory')->get_records(array
                              ('ageid'=>$orderdata->data->ages->from)) )
        {// данные есть - занесем их всех в монстра
            foreach ( $historylearning as $lrn )
            {
                if ( ! $sbc = $this->dof->storage('programmsbcs')->get($lrn->programmsbcid) )
                {// подписки нет(что врядли) - но на всякий случай пропустим
                    continue;
                }
                if ( isset($orderdata->data->departments) AND 
                    ! in_array($sbc->departmentid, $orderdata->data->departments) )
                {// но не находится в выбранном подразделении - пропустим
                    continue;
                }
                // разбрасываем подписки по местам
                if ( $sbc->status == 'active')
                {// статус активный - считаем, что ученик переведен
                    if ( $sbc->agenum < 1 )
                    {// подписки с -1 паралелью не должны попадать в приказ
                        continue;
                    }
                    if ( $sbc->agenum >= $this->dof->storage('programms')->get_field($sbc->programmid,'agenums') )
                    {// если параллель больше нужной - это выпускник, его в приказ не включаем
                        $student->exclude[$sbc->agenum][0][$sbc->id] = 
                                 $this->set_values_for_sbc($sbc, $lrn->ageid, $orderdata->data->ages->base,'none');
                    }elseif ( isset($student->transfer[$sbc->agenum]) AND isset($sbc->agroupid) AND
                           array_key_exists($sbc->agroupid,$student->transfer[$sbc->agenum]) )
                    {// если в монстре есть группы, и подписка групповая, и группа подписки имеется в монстре
                        // отнесем подписку в группу
                        $student->transfer[$sbc->agenum][$sbc->agroupid][$sbc->id] = 
                                 $this->set_values_for_sbc($sbc, $lrn->ageid, $orderdata->data->ages->base,'active');
                    }elseif ( isset($sbc->agroupid) AND ! empty($sbc->agroupid) )
                    {// ученик числится группы, но его группа не переводится
                        $student->exclude[$sbc->agenum][0][$sbc->id] = 
                                 $this->set_values_for_sbc($sbc, $lrn->ageid, $orderdata->data->ages->base,'none',0);
                    }else
                    {// подписка была индивидуалиной
                         $student->transfer[$sbc->agenum][0][$sbc->id] = 
                                 $this->set_values_for_sbc($sbc, $lrn->ageid, $orderdata->data->ages->base,'active');
                    }
                }
                if ( $sbc->status == 'suspend')
                {// приостановленные не попадают в приказ
                    $student->exclude[$sbc->agenum][0][$sbc->id] = 
                                 $this->set_values_for_sbc($sbc, $lrn->ageid, $orderdata->data->ages->base,'none');
                }
                if ( $sbc->status == 'condactive')
                {// если условно переведен(что врядли) - то оставим его условно переведенным
                    if ( $sbc->agenum >= $this->dof->storage('programms')->get_field($sbc->programmid,'agenums') )
                    {// если параллель больше нужной - это выпускник, его в приказ не включаем
                        $student->exclude[$sbc->agenum][0][$sbc->id] = 
                                 $this->set_values_for_sbc($sbc, $lrn->ageid, $orderdata->data->ages->base,'none');
                    }elseif ( isset($student->condtransfer[$sbc->agenum]) AND isset($sbc->agroupid) AND
                           array_key_exists($sbc->agroupid,$student->condtransfer[$sbc->agenum]) )
                    {// если в монстре есть группы, и подписка групповая, и группа подписки имеется в монстре
                        // отнесем подписку в группу
                        $student->condtransfer[$sbc->agenum][$sbc->agroupid][$sbc->id] = 
                                 $this->set_values_for_sbc($sbc, $lrn->ageid, $orderdata->data->ages->base,'condactive');
                    }elseif ( isset($sbc->agroupid) AND ! empty($sbc->agroupid) )
                    {// ученик числится группы, но его группа не переводится
                        $student->exclude[$sbc->agenum][0][$sbc->id] = 
                                 $this->set_values_for_sbc($sbc, $lrn->ageid, $orderdata->data->ages->base,'none',0);
                    }else
                    {// подписка была индивидуалиной
                         $student->condtransfer[$sbc->agenum][0][$sbc->id] = 
                                 $this->set_values_for_sbc($sbc, $lrn->ageid, $orderdata->data->ages->base,'condactive');
                    }
                } 
            }
        }
        // найдем всех академщиков вне зависимости от периода
        if ( isset($orderdata->data->departments) )
        {// если есть подразделение - только из этих подразделений
            $academsbcs = $this->dof->storage('programmsbcs')->get_records(array
                         ('status'=>'onleave','departmentid'=>$orderdata->data->departments));
        }else
        {// выбираем из всех подразделений
            $academsbcs = $this->dof->storage('programmsbcs')->get_records(array('status'=>'onleave'));
        }
        if ( $academsbcs )
        {// если академики нашлись - не учитываем их
            foreach ( $academsbcs as $academsbc )
            {
                $student->exclude[$academsbc->agenum][0][$academsbc->id] = 
                                 $this->set_values_for_sbc($academsbc, 0, $orderdata->data->ages->base,'none');
            }
        }
        if ( empty($student->transfer) AND empty($student->condtransfer) AND empty($student->exclude) )
        {// пустые данные - удалим не нужное, если было
            unset($orderdata->data->student);
            return $this->order->save($orderdata);
        }
        // сохраним монстра в приказ
        $orderdata->data->student = $student;
        $orderdata->data->groups = $agroups;
        // зачистим данные перед вставкой на всякий случай
        $orderdata->data->student = $this->delete_null_values($orderdata->data->student);
        return $this->order->save($orderdata);
        // МОНСТР СОЗДАН!!!
    }
    
    /** Формирует данные для подписки на программу для сохранения в приказ
     * @param object $sbc - объект подписки на программу
     * @param int $ageid - id старого периода
     * @param int $newageid - id нового периода
     * @param int $newagroupid - id новой группы
     * @return object - объект данных
     */
    public function set_values_for_sbc($sbc, $ageid, $newageid, $newstatus, $newagroupid = null, $newtype=null)
    {
        if ( ! is_object($sbc) )
        {
            $sbc = $this->dof->storage('programmsbcs')->get($sbc);
        }
        $return = new stdClass();
        $studentid = $this->dof->storage('contracts')->get_field($sbc->contractid,'studentid');
        $return->studentname = $this->dof->storage('persons')->get_field($studentid,'sortname');
        $return->programname = $this->dof->storage('programms')->get_field($sbc->programmid,'name').'['.
                               $this->dof->storage('programms')->get_field($sbc->programmid,'code').']' ;
        $return->oldagenum = $sbc->agenum;
        $return->oldageid = $ageid;
        $return->oldstatus = $sbc->status;
        $return->oldagroupid = $return->newagroupid = $sbc->agroupid;
        $return->newageid = $newageid;
        $return->newstatus = $newstatus;
        if ( isset($newtype) AND in_array($newtype,array('notransfer','restore','academ')) )
        {// не переведенных оставляем в той же паралели
            $return->newagenum = $sbc->agenum;
        }else 
        {
            $return->newagenum = $sbc->agenum+1;
        }
        
        if ( ! is_null($newagroupid) )
        {// если группа есть - обновим ее
            $return->newagroupid = $newagroupid;
        }
        return $return;
        
    }
    
    /** Формирует данные для группы для сохранения в приказ
     * @param object $sbc - объект подписки на программу
     * @param int $ageid - id старого периода
     * @param int $newageid - id нового периода
     * @param int $newagroupid - id новой группы
     * @return object - объект данных
     */
    public function set_values_for_group($group, $ageid, $newageid, $transfer = true, $empty=false)
    {
        if ( ! is_object($group) )
        {
            $group = $this->dof->storage('agroups')->get($group);
        }
        $return = new stdClass();
        $return->name = $group->name;
        $return->programname = $this->dof->storage('programms')->get_field($group->programmid,'name').'['.
                               $this->dof->storage('programms')->get_field($group->programmid,'code').']' ;
        $return->code = $group->code;
        $return->oldagenum = $group->agenum;
        $return->oldageid = $ageid;
        $return->newageid = $newageid;
        $return->exclude = $empty;
        if ( ! $transfer )
        {// не переведенных оставляем в той же паралели
            $return->newagenum = $group->agenum;
        }else 
        {
            $return->newagenum = $group->agenum+1;
        }
        return $return;
        
    }

    /** Собирает данные для шаблона 
     * @param string $type - тип (transfer, condtransfer, academ, notransfer)
     * @return array or bool - сложную запись(детище МОНСТРА)
     */
    public function get_output($type)
    {// загрyжаем ордер
        if ( ! $order = $this->get_order_data() )
        {// не нашли ордер
            return false;
        }
        // добавляем к монстру ПУЧТЫЕ группы
        if ( isset($order->data->groups) )
        {// есть группы - покажем
            $order = $this->get_output_only_group($order);
        }    
                
        if ( ! isset($order->data->student->$type) )
        {// нет переведённых
            return false;
        }
        
        return $this->get_output_mas($order, $type);
    }
    
    /** Вставляет группы из {groups} в {student} для 
     * отображения их в шаблонизаторе 
     * @param $order - приказ, сложная запись
     * @return $
     */    
    public function get_output_only_group($order)
    {// получаем доступ к группам   
        foreach ( $order->data->groups as $id=>$obj )
        {
            // исключены     
            if ( isset($obj->exclude) AND $obj->exclude AND
                    ! isset($order->data->student->exclude[$obj->oldagenum][$id]) )
            {
                $order->data->student->exclude[$obj->oldagenum][$id] = array();
                continue;
            }
            // переведены
            if ( $obj->newagenum == ($obj->oldagenum + 1) AND
                    ! isset($order->data->student->transfer[$obj->oldagenum][$id])  AND 
                    ! isset($order->data->student->condtransfer[$obj->oldagenum][$id]))
            {// нет группы в МОНCTРЕ  - добавим
                $order->data->student->transfer[$obj->oldagenum][$id] = array();
                continue;
            }
            // на повтроное обучение
            if ( $obj->newagenum == $obj->oldagenum AND
                    ! isset($order->data->student->notransfer[$obj->oldagenum][$id]) AND
                    ! isset($order->data->student->exclude[$obj->oldagenum][$id]) )
            {
                $order->data->student->notransfer[$obj->oldagenum][$id] = array();
                continue;
            }   
            
        } 
        return $order;        
    }
    
    /** Собирает данные для шаблона 
     * @param $type string $type - тип (transfer, condtransfer, academ, notransfer)
     * @return $outdata - сложную запись(детище МОНСТРА)
     */    
    public function get_output_mas($order, $type)
    {
        $depid = optional_param('departmentid', 0, PARAM_INT);
        $addvars = array();
        $addvars['departmentid'] = $depid;
        $transfer = $order->data->student->$type;
        // сортируем по возрастанию параллелей
        ksort($transfer);
        $edit = false;
        if ( empty($order->signdate) AND empty($order->signerid) )
        {// редактировать можно
            $edit = true;
            //Оставляем колонку действия
            $studentstyle = '';
            $action = $this->dof->get_string('action','learningorders');
        }else
        {//Убираем колонку действия, если нельзя редактировать
            $studentstyle = "style='display:none;'";
            $action = '';
        }
        if( $type != 'exclude' )
        {//Убираем колонку причины, если не тип исключённые
            $reasonstyle = "style='display:none;'";
            $reason = '';
        }else
        {//Оставляем колонку причины, если тип исключённые
            $reasonstyle = '';
            $reason = $this->dof->get_string('reason','learningorders');
        }
        
        $anchor='';     
        $outdata = new stdClass();
        $outdata->types = $this->dof->get_string($type,'learningorders');
                
        $outdata->typesanchor = "<a name='$type'></a>";
        $anchor = $anchor."<ul style='list-style-type:none; font-size:14px;padding-left:0px;'>
                          <li> <b><a href='#$type'>$outdata->types</a></b></li>";
        if ( $type == 'exclude' )
        {
            $outdata->style = "style=' background-color:#d4d4d4;'";
        }
        $outdata->newparallel = array();
        $anchor = $anchor."<ul style='list-style-type:none;font-size:12px;padding-left:10px;'>" ;
        foreach ( $transfer as $key=>$newparallel  )
        {// перебираем паралели
            $new = new stdClass();
            $new->parallels = $key;
            $new->parallelna = $key+1;
            if ( $type == 'transfer' OR $type == 'condtransfer' )
            {
                $new->remove_na_parallel = $this->dof->get_string('remove_na_parallel','learningorders',($key+1));
                $new->remove_parallel = $this->dof->get_string('remove_parallel','learningorders',$key);
                $new->typeparal = "<a name='$type.$key|$new->parallelna'></a>";
                $anchor = $anchor." <li><a href='#$type.$key|$new->parallelna'>$new->remove_parallel$new->remove_na_parallel</a></li>";
            }else 
            {
                $new->remove_na_parallel = $this->dof->get_string('remove_na_parallel','learningorders',$key);  
                $new->typeparal = "<a name='$type.$key'></a>";
                $anchor = $anchor."<li> <a href='#$type.$key'> $new->remove_na_parallel </a></li>";  
            } 
            $group_mas = array();  
            $no_group_mas = array();
            $anchor = $anchor."<ul style='list-style-type:none;font-size:12px;padding-left:10px;'>" ;
            foreach ( $newparallel as $id=>$group )
            {// перебираем группы
               if ( $id == 0 )
                {// студенты без групп и передаём ещё параллель = $key
                    $anchor = $anchor." <li><a href='#$type.$key.0'>".$this->dof->get_string('no_group','learningorders')."</a></li>";
                    
                    $mas_progid = array();
                    foreach ($group as $sbcid=>$value) 
                    {// массив программ
                        if ( ! $sbc = $this->dof->storage('programmsbcs')->get($sbcid) )
                        {// нет - берем следующий
                            continue;
                        }                     
                        $progid = $sbc->programmid;
                        $mas_progid[] = $progid;
                    }    
                    // уберем одинаковые елемнты
                    $mas_progid = array_unique($mas_progid);
                    $anchor = $anchor."<ul style='list-style-type:none;padding-left:20px;'>" ;
                    foreach ($mas_progid as $value=>$progid)
                    {
                        $no_group = new stdClass();             
                        $no_group->typenogroup = "<a name='$type.$key.0'></a>";   
                        $no_group->no_group = $this->dof->get_string('no_group','learningorders');
                        $no_group->parall = $this->dof->get_string('remove_na_parallel','learningorders',$key); 
                        $no_group->firstnamee = $this->dof->get_string('firstname','learningorders');
                        $no_group->middlenamee = $this->dof->get_string('middlename','learningorders');
                        $no_group->lastnamee = $this->dof->get_string('lastname','learningorders'); 
                        $no_group->reasonstylee = $reasonstyle;
                        $no_group->reasone = $reason;
                        $no_group->actionstyle = $studentstyle;
                        $no_group->action = $action;
                        $programm = $this->dof->storage('programms')->get($progid); 
                        $no_group->prog = "$programm->name[$programm->code]";
                        $anchor = $anchor." <li><a href='#$type.$key.0.$progid'>$no_group->prog</a></li>";
                        $no_group->typenoprog = "<a name='$type.$key.0.$progid'></a>"; 
                        // строка под группой 
                        if ( $type == 'transfer' OR $type == 'condtransfer' )
                        {
                            $no_group->string = $new->remove_parallel.' '.$new->remove_na_parallel;
                        }else 
                        {
                            $no_group->string = $new->remove_na_parallel;  
                        }
                        $no_group->student1 = $this->get_student($group, $key, $type, null, $edit,$progid,$newparallel, $reasonstyle, $studentstyle);
                        if ( $edit )
                        {// ссылка ИЗМЕНИТЬ
                            $no_group->change_group = '<a href="'.$this->dof->url_im('learningorders', '/ordertransfer/changeoptions.php?type='.$type.'&orderid='.$this->order->get_id().'&programm='.$progid.
            	                   '&agenum='.$key.'&newageid='.$this->get_student_newage($group),$addvars).'" 
                                    title="'. $this->dof->modlib('ig')->igs('change') .'">
                                    <img src="'.$this->dof->url_im('learningorders', '/icons/edit.png').'" ></a> ';
                            if ( $type != 'exclude' )
                            {// ссылка для массового исключения
                                $no_group->change_group .='<a href="'.$this->dof->url_im('learningorders', '/ordertransfer/excludeall.php?type='.$type.'&orderid='.$this->order->get_id().'&programm='.$progid.
                	                    '&agenum='.$key.'&newageid='.$this->get_student_newage($group),$addvars).'" 
                                        title="'.$this->dof->get_string('exclude_form','learningorders').'">
                                        <img src="'.$this->dof->url_im('learningorders', '/icons/exclude.png').'" ></a>'; 
                            }              
                        }
                        $no_group_mas[] = $no_group;
                    }
                    $anchor = $anchor."</ul>" ;
                }else
                {// формируем новый бъект
                    
                    $obj = new stdClass();
                    $obj = $this->dof->storage('agroups')->get($id);
                    $obj->groupname = "$obj->name[$obj->code]"; 
                    $obj->groups = $this->dof->get_string('group','learningorders');
                    $anchor = $anchor." <li><a href='#$type.$key.$id'>$obj->groups $obj->groupname</a></li>";
                    $obj->typegroup = "<a name='$type.$key.$id'></a>"; 
                    $obj->paral = $this->dof->get_string('remove_na_parallel','learningorders',$key);
                    $programm = $this->dof->storage('programms')->get($obj->programmid); 
                    $obj->prog = "$programm->name[$programm->code]";
                    $obj->firstnamee = $this->dof->get_string('firstname','learningorders');
                    $obj->middlenamee = $this->dof->get_string('middlename','learningorders');
                    $obj->lastnamee = $this->dof->get_string('lastname','learningorders');  
                    $obj->reasonstylee = $reasonstyle;
                    $obj->reasone = $reason;
                    $obj->actionstyle = $studentstyle;
                    $obj->action = $action;
                    
                    $anchor = $anchor."<li>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href='#$type.$key.$id.$obj->programmid'>$obj->prog</a></li>";   
                    $obj->typeprog = "<a name='$type.$key.$id.$obj->programmid'></a>"; 
                    // строка под группой 
                    if ( $type == 'transfer' OR $type == 'condtransfer' )
                    {
                        $obj->string = $new->remove_parallel.' '.$new->remove_na_parallel;
                    }else 
                    {
                        $obj->string = $new->remove_na_parallel;  
                    }
                   
                    // и передаём ещё параллель = $key
                    $obj->student = $this->get_student($group, $key, $type, $id, $edit,null,$newparallel, $reasonstyle, $studentstyle);
                    //var_dump($group);echo"<br>$id<br>";
                    // объект группы
                    $objgroup = $order->data->groups[$id];
                    // var_dump($objgroup);echo "<br><br>";
                    if ( $edit )
                    {// ссылка ИЗМЕНИТЬ
                        $obj->change_group = '<a href="'.$this->dof->url_im('learningorders', '/ordertransfer/changeoptions.php?type='.$type.'&orderid='.$this->order->get_id().'&groupid='.$id.
                                '&agenum='.$objgroup->oldagenum.'&newageid='.$objgroup->newageid,$addvars).'" 
                                title="'. $this->dof->modlib('ig')->igs('change').'">
                                <img src="'.$this->dof->url_im('learningorders', '/icons/edit.png').'" ></a> ';
                        if ( $type != 'exclude' )
                        {// ссылка для массового исключения
                            $obj->change_group .= '<a href="'.$this->dof->url_im('learningorders', '/ordertransfer/excludeall.php?type='.$type.'&orderid='.$this->order->get_id().'&groupid='.$id.
                                '&agenum='.$objgroup->oldagenum.'&newageid='.$objgroup->newageid,$addvars).'" 
                                title="'.$this->dof->get_string('exclude_form','learningorders').'">
                                <img src="'.$this->dof->url_im('learningorders', '/icons/exclude.png').'" ></a>';   
                        }            
                    }
                    $group_mas[] = $obj;
                }
                
            }
            $anchor = $anchor."</ul>";  
            usort($group_mas, array('dof_im_learningorders_ordertransfer','sort_by_groupname'));
            usort($no_group_mas, array('dof_im_learningorders_ordertransfer','sort_by_nogroupprog'));
            $new->group = $group_mas;
            $new->no_group = $no_group_mas;
            if ( empty($new->group) AND ( ! isset($new->no_group) OR empty($new->no_group)) )
            {// махи параллель не показываем
                continue;
            }
            $outdata->newparallel[] = $new;
            
        }
        $anchor = $anchor."</ul></ul>"; 
        $outdata->anchor = $anchor;
        return $outdata;
    }   

    
    /** Собирает данные по ученикам
     * @param (mix)$sbcs - запись подписок 
     * @return (int)newageid - новый период или 0
     */
    private function get_student_newage($sbcs)
    {
        foreach ( $sbcs as $key=>$sbc )
        {// перебираем подписки - ищем студентов
            if ( ! $programsbcs = $this->dof->storage('programmsbcs')->get($key) )
            {// не существование контракта
                continue;
            }
            $contractid = $programsbcs->contractid;
            if ( ! $contract = $this->dof->storage('contracts')->get($contractid) )
            {// не существование 
                continue;
            }
            // новый период
            return $sbc->newageid;
        }
        return 0;
    }    
        
    
    /** Собирает данные по ученикам
     * @param (mix)$sbcs - запись(массив подписок)
     * @param (int)$paralnum - параллель (текущая) 
     * @param (string)$type  - тип пеервода 
     * @param (int)$groupid  - id группы, по умолчанию false
     * @param (boоl)$edit  - флаг редактирования, по умолчанию false 
     * @param (int)$progid - id программы(programm) , по умолчанию null
     * @param (mix)$groups - массив групп, по умолчанию null
     * @param (string)$reasonstyle  - служит для правильной отрисовки таблицы, по умолчанию - style='display:none;'
     * @param (string)$studentstyle - служит для правильной отрисовки таблицы, по умолчанию - style='display:none;'
     * @return object - запись
     */
    private function get_student($sbcs, $paralnum, $type, $groupid=false, $edit=false, $progid=null, $groups = null, $reasonstyle = "style='display:none;'", $studentstyle = "style='display:none;'")
    {// массив для группп
        $depid = optional_param('departmentid', 0, PARAM_INT);
        $addvars = array();
        $addvars['departmentid'] = $depid;
        $student_mas = array();
        if ( empty($sbcs) )
        {
            return $student_mas;
        }

        foreach ( $sbcs as $key=>$sbc )
        {// перебираем подписки - ищем студентов
            if ( ! $programsbcs = $this->dof->storage('programmsbcs')->get($key) )
            {// не существование контракта
                continue;
            }
            $contractid = $programsbcs->contractid;
            if ( ! $contract = $this->dof->storage('contracts')->get($contractid) )
            {// не существование 
                continue;
            }
   
            // формируем новый бъект
            $obj = new stdClass();
            if ( isset($progid) )
            {// передали программу - выбираем ток студентов из этой программы
                if ( $progid != $programsbcs->programmid )
                {// не наш студент - пропускаем
                    continue;                 
                }
            }
            // нет программы - берем всех студентов
            $student = $this->dof->storage('persons')->get($contract->studentid);
              
            $obj->lastname   = $student->lastname;
            $obj->firstname  = $student->firstname;
            if ( !isset($student->middlename) )
            {
                $student->middlename = '';
            }
            $obj->middlename = $student->middlename;
            $obj->sortname   = $student->sortname;
            $obj->reasonstyle = $reasonstyle;
            $obj->studentstyle = $studentstyle;
            // ссылка ИЗМЕНИТЬ 
            if ( $edit )
            {// ссылка изменить
                $obj->reason = '';
                if ( $type == 'exclude' )
                {// дадим пояснения почему студент исключен из приказа
                    if ( $sbc->oldstatus == 'onleave' )
                    {// в академе
                        $obj->reason .= '-'.$this->dof->get_string('academ','learningorders').'<br>';
                    }
                    if ( $sbc->oldstatus == 'suspend' )
                    {// приостановленная подписка
                        $obj->reason .= '-'.$this->dof->get_string('suspendsbc','learningorders').'<br>';
                    }
                    if ( $sbc->oldagenum >= $this->dof->storage('programms')->get_field($programsbcs->programmid,'agenums') )
                    {// выпускник
                        $obj->reason .= '-'.$this->dof->get_string('graduate','learningorders').'<br>';
                    }
                    if ( $sbc->oldagroupid != 0 AND ! empty($groups) AND 
                                 ! array_key_exists($sbc->oldagroupid,$groups))
                    {// приостановленная подписка
                        $obj->reason .= '-'.$this->dof->get_string('nogroupsbc','learningorders').'<br>';
                    }
                }
                $obj->student = '<a href="'.$this->dof->url_im('learningorders', '/ordertransfer/changeoptions.php?type='.$type.'&orderid='.$this->order->get_id().'&groupid='.$groupid.
                            '&sbcid='.$key.'&agenum='.$paralnum.'&newageid='.$sbc->newageid,$addvars).'"
                            title="'. $this->dof->modlib('ig')->igs('change') .'">
                            <img src="'.$this->dof->url_im('learningorders', '/icons/edit.png').'" ></a> ';       
            }
            $student_mas[] = $obj;
            usort($student_mas, array('dof_im_learningorders_ordertransfer','sort_by_name'));
            $i = 0;
            foreach ( $student_mas as $key=>$obj )
            {// нумеруем
                $i++;
                $obj->i = $i;
            }
            
        }
        return $student_mas;
    } 

    /** Вывести шаблон transfer на экран
     * 
     * @param int $eventid - id редактируемого учебного события (таблица schevents)
     */
    public function print_texttable($a=null)
    {// получаем массив со структурой документов
        $anchor1 = '';
        $transfer = new stdClass;
        $transfer->type = array();
        if ( $transferdata = $this->get_output('transfer') )
        {// обращаемся к шаблонизатору для вывода таблицы
            $transfer->type[] = $transferdata;
            $anchor1 .= $transferdata->anchor;
        }
        if ( $transferdata = $this->get_output('condtransfer') )
        {// обращаемся к шаблонизатору для вывода таблицы
            $transfer->type[] = $transferdata;
            $anchor1 .= $transferdata->anchor;
        }
        if ( $transferdata = $this->get_output('notransfer') )
        {// обращаемся к шаблонизатору для вывода таблицы
            $transfer->type[] = $transferdata;
            $anchor1 .= $transferdata->anchor;
        }
        if ( $transferdata = $this->get_output('restore') )
        {// обращаемся к шаблонизатору для вывода таблицы
            $transfer->type[] = $transferdata;
            $anchor1 .= $transferdata->anchor;
        }
        if ( $transferdata = $this->get_output('academ') )
        {// обращаемся к шаблонизатору для вывода таблицы
            $transfer->type[] = $transferdata;
            $anchor1 .= $transferdata->anchor;
        }
        if ( $transferdata = $this->get_output('exclude') )
        {// обращаемся к шаблонизатору для вывода таблицы
            $transfer->type[] = $transferdata;
            $anchor1 .= $transferdata->anchor;
        }
        // якорь
        $transfer->anchortmp =$anchor1;
        
        $templater_package = $this->dof->modlib('templater')->template('im', 'learningorders', $transfer, 'transfer');
        return print($templater_package->get_file('html'));
        return false;
    }    
    
    /** Сохраняет измененные данные приказа по студентот БЕЗ ГРУПП
     * @param $type - тип перевода
     * @param $sbcid - id подписки на программу
     * @param $data - данные для изменения
     * @param $flag - пропускать или нет ученика для редактирования
     * @return bool
     */
    public function save_options_students_nogroup($type,$data,$flag=false)
    {
        if ( ! $orderdata = $this->get_order_data() )
        {// не нашли ордер
            return false;
        }
        $student = $orderdata->data->student->$type;
        $sbcids = $student[$data->agenum][$data->agroupid];
        foreach ( $sbcids as $sbcid=>$val )
        {// перебираем все подписки
            $sbc = 'change'.$sbcid;
            if ( ! isset($sbc) AND ! $flag)
            {// нет - берем следующюю подписку
                continue;
            }
            // для того флаг, чтоб не было notice, когда 
            // формируем сами ордера(1 поля не будет там)
            if ( $flag AND $data->prog != $this->dof->storage('programmsbcs')->get_field($sbcid,'programmid') )
            {// программы не совпали - пропускаем
                continue;
            }
            // изменяем студента - работаем с ним
            // переобъявляем
            $student = $orderdata->data->student->$type;
            // удаляем студента
            unset($student[$data->agenum][$data->agroupid][$sbcid]);
            $orderdata->data->student->$type = $student;
            
            $student = 'student'.$sbcid;

            if ( ! $flag )
            {
                $mas = $data->$student;
                $newtype = dof_im_learningorders_ordertransfer_get_transfertype($mas[0]);
                $newagroupid = $mas[1];            
                $newageid    = $mas[2];
            }else
            {// для массового перевода студентов
                $newtype = 'exclude';
                $newagroupid = $data->agroupid;            
                $newageid    = $data->newageid;                
            }

            
            switch ($newtype)
            {// для разных типов формируем разные данные
                // для востановленных и переведенных - активный статус
                case 'restore':
                case 'transfer': $status = 'active'; break;
                // для условно переведенных статус условно действующий
                case 'condtransfer': $status = 'condactive'; break;
                // для академиков - академ
                case 'academ': $status = 'onleave'; break;
                // для исключенных и непереведенных (прям каторга какая-то) - не важно
                case 'exclude':
                case 'notransfer': $status = 'none'; break;
            }            
            if ( isset($orderdata->data->student->$newtype) )
            {// если они до этого были
                $newstudent = $orderdata->data->student->$newtype;
            }else 
            {// затираем массив
                $newstudent = array();        
            }
            $newstudent[$data->agenum][$newagroupid][$sbcid] = 
                     $this->set_values_for_sbc($sbcid, $val->oldageid, $newageid,$status, $newagroupid, $newtype);
            $orderdata->data->student->$newtype = $newstudent;
            $this->order->save($orderdata);
       }
        // зачистим данные перед сохранением
        $orderdata->data->student = $this->delete_null_values($orderdata->data->student);
        // сохраняем новые данные в приказ    
        return $this->order->save($orderdata);
    }
    
    /** Сохраняет измененные данные приказа по студенту
     * @param $type - тип перевода
     * @param $sbcid - id подписки на программу
     * @param $data - данные для изменения
     * @return bool
     */
    public function save_options_student($type,$sbcid=null,$data)
    {
         if ( ! $orderdata = $this->get_order_data() )
        {// не нашли ордер
            return false;
        }
       // print_object($orderdata); die;
        if ( $sbcid )
        {// редактируется один студент
            // загружаем данные из приказа для удаления
            $student = $orderdata->data->student->$type;
            $sbc = $student[$data->agenum][$data->agroupid][$sbcid];
            // удаляем студента
            unset($student[$data->agenum][$data->agroupid][$sbcid]);
            $orderdata->data->student->$type = $student;  
            // преобразование данных для нужного нам типа
            $student = 'student'.$sbcid;
            $mas = $data->$student;
            $newtype = dof_im_learningorders_ordertransfer_get_transfertype($mas[0]);
            $data->newagroupid = $mas[1];
            if ( $mas[2] != 0 )
            {// 
                 $data->newageid = $mas[2]; 
            }            
        
            switch ($newtype)
            {// для разных типов формируем разные данные
                // для востановленных и переведенных - активный статус
                case 'restore':
                case 'transfer': $status = 'active'; break;
                // для условно переведенных статус условно действующий
                case 'condtransfer': $status = 'condactive'; break;
                // для академиков - академ
                case 'academ': $status = 'onleave'; break;
                // для исключенных и непереведенных (прям каторга какая-то) - не важно
                case 'exclude':
                case 'notransfer': $status = 'none'; break;
            }
            if ( isset($orderdata->data->student->$newtype) )
            {// если они до этого были
                $newstudent = $orderdata->data->student->$newtype;
            }else 
            {// затираем массив
                $newstudent = array();        
            }
            $newstudent[$data->agenum][$data->newagroupid][$sbcid] = 
                     $this->set_values_for_sbc($sbcid, $sbc->oldageid, $data->newageid,$status, $data->newagroupid, $newtype);
            $orderdata->data->student->$newtype = $newstudent;
        }else 
        {// редактируется список студентов со страницы редактирования группы или группа
            $student = $orderdata->data->student->$type;
            $sbcids = $student[$data->agenum][$data->agroupid];
            foreach ( $sbcids as $sbcid => $value )
            {// перебираем все подписки
                $sbc = 'change'.$sbcid;
                // переобъявляем
                $student = $orderdata->data->student->$type; 
                // удаляем студента
                unset($student[$data->agenum][$data->agroupid][$sbcid]);
                $orderdata->data->student->$type = $student;
                
                if ( isset($data->$sbc) AND $data->$sbc )
                {// есть чекбокс - посылаем данные из чекбокса
                    $student = 'student'.$sbcid;
                    $mas = $data->$student;
                    $newtype = dof_im_learningorders_ordertransfer_get_transfertype($mas[0]);
                    $newagroupid = $mas[1];
                    $newageid = $mas[2]; 
                }else 
                {// иначе данные группы
                    $newtype = $data->newtype;    
                    $newageid = $data->newageid;
                    if ($newtype == 'academ')
                    {// если академ, то учеников в безгруппы
                        $newagroupid = 0; 
                    }else 
                    {
                        $newagroupid = $data->agroupid;
                    }
                }   
                    
                switch ($newtype)
                {// для разных типов формируем разные данные
                    // для востановленных и переведенных - активный статус
                    case 'restore':
                    case 'transfer': $status = 'active'; break;
                    // для условно переведенных статус условно действующий
                    case 'condtransfer': $status = 'condactive'; break;
                    // для академиков - академ
                    case 'academ': $status = 'onleave'; break;
                    // для исключенных и непереведенных (прям каторга какая-то) - не важно
                    case 'exclude':
                    case 'notransfer': $status = 'none'; break;
                }    
                if ( isset($orderdata->data->student->$newtype) )
                {// если они до этого были
                    $newstudent = $orderdata->data->student->$newtype;
                }else 
                {// затираем массив
                    $newstudent = array();        
                }
                $newstudent[$data->agenum][$newagroupid][$sbcid] = 
                         $this->set_values_for_sbc($sbcid, $value->oldageid, $newageid, $status, $newagroupid, $newtype);
                $orderdata->data->student->$newtype = $newstudent; 
                $this->order->save($orderdata); 
             } 
             // меняем везде у ЭТОЙ группы период и период учащихся(для transfer и notransfer)
             if ( $type == 'notransfer' OR $type == 'transfer' )
             {
                 $this->change_age_group($type,$data->agroupid,$data->agenum,$data->newageid);
                 // поменяли ордер - загрузим его заново
                 $orderdata = $this->get_order_data();
             }
        }
        // зачистим данные перед сохранением
        $orderdata->data->student = $this->delete_null_values($orderdata->data->student);
        // сохраняем новые данные в приказ    
        return $this->order->save($orderdata);
    }

    /** Поменяли период группы в 1 типе перевода 
     *  меняем и в других, если она писутствует
     * @param int $agroupid - id группы для которой меняеться период
     * @param int $agenum - номер параллели в которой учится руппа
     * @param string $type - тип перевода в котором находится группа
     */
    public function change_age_group($type,$agroupid,$agenum,$newageid)
    {// типы, где могут быть группы как таковые
        if ( $type == 'transfer' )
        {// группа transfer - ищем её и в condtransfer
            $type2 = 'condtransfer';
        }else 
        {// иначе ищем и в transfer
            $type2 = 'transfer';
        }

        // загружаем ордер
        $orderdata = $this->get_order_data();
        if ( isset($orderdata->data->student->$type2) )
        {// есть тип
            $student = $orderdata->data->student->$type2;
            if ( isset($student[$agenum][$agroupid]) )
            {// есть паралель и группа
                $sbcs = $student[$agenum][$agroupid];
                foreach ($sbcs as $sbcid=>$val)
                {// удаляем студента
                    $student = $orderdata->data->student->$type2;
                    unset($student[$agenum][$agroupid][$sbcid]);
                    $orderdata->data->student->$type2 = $student;
                    // перезаписываем
                    $newstudent = $orderdata->data->student->$type2;
                    $val->newageid = $newageid;
                    $newstudent[$agenum][$agroupid][$sbcid] = $val;
                    $orderdata->data->student->$type2 = $newstudent;
                    // сохраняем
                    $this->order->save($orderdata);    
                }     
            }
        } 
        return true; 
    }
    
    /** Получить период, в который будет переводиться группа в зависимости от id группы
     * 
     * @return int|bool
     * @param int $agroupid - id группы для которой получается период
     * @param int $agenum - номер параллели в которой учатся все группы
     * @param string $type - тип перевода в котором находится группа
     */
    public function get_ageid_by_agroupid($agroupid)
    {
        if ( ! $agroupid )
        {// учеников без группы провускаем
            return false;
        }
        
        // получаем всю информацию по приказу
        $orderdata = $this->get_order_data();
        
        if ( isset($orderdata->data->groups[$agroupid]) )
        {// получаем группу из приказа
            $group = $orderdata->data->groups[$agroupid];
            return $group->newageid;
        }
        // ничего не нашли
        return false;
    }
    
    /** Сохраняет измененные данные приказа по группе
     * @param $type - тип перевода
     * @param $agroupid - id группы
     * @param $data - данные для изменения
     * @return bool
     */
    public function save_options_agroup($type,$data)
    {
        if ( ! $orderdata = $this->get_order_data() )
        {// не нашли ордер
            return false;
        }
        // работа с группой
        $rez = true;
        if ( isset($orderdata->data->groups[$data->agroupid]) )
        {
            $rez = $rez AND $this->save_change_group($data->newtype,$data->newageid,$data->agroupid);
            // зануляет учеников по группе, которая употребляеться в других местах
            $rez = $rez AND $this->change_group_type($type,$data->newtype,$data->agenum,$data->agroupid);
        }else 
        {
            return false;
        }

        // работа со студентами
        $student = $orderdata->data->student->$type;
        if ( isset($student[$data->agenum][$data->agroupid]) )
        {// если подписки есть
            // переведем группу
            $rez = $rez AND $this->save_options_student($type,null,$data);
        }
        return $rez;
    }

    /** Сохраняет измененные данные приказа только группы
     * @param $type - тип перевода
     * @param $agroupid - id группы
     * @param $data - данные для изменения
     * @return bool
     */
    public function save_change_group($newtype,$newageid,$agroupid)
    {// загружаем приказ    
        $orderdata = $this->get_order_data();
        // объект группы для редактирования
        $group = $orderdata->data->groups[$agroupid];
        // в зависимости о типа перевода меняем данные группы
        switch ( $newtype )
        {
            case 'transfer': 
            case 'condtransfer': 
                $group->newagenum = $group->oldagenum + 1;
                $group->newageid = $newageid;
                $group->exclude = false; 
                break;
            case 'notransfer': 
                $group->newagenum = $group->oldagenum;
                $group->newageid = $newageid;
                $group->exclude = false;             
                break;
            case 'academ':    
            case 'exclude': 
                $group->newagenum = $group->oldagenum;
                $group->newageid = $group->oldageid;
                $group->exclude = true;
                break;               
        }
        
       $orderdata->data->groups[$agroupid] = $group;
        
       return $this->order->save($orderdata); 
       // $orderdata = $this->get_order_data();
       // print_object($orderdata->data->groups[$agroupid]); die();
    }

    /** Выкидывает учеников из группы изходя из типов перевода и перехода
     * @param $type - тип перевода откуда
     * @param $newtype - тип перевода куда
     * @param $agenum - параллель(откуда)
     * @param $agroupid - id группы
     * @return bool - true
     */
    public function change_group_type($type,$newtype,$agenum,$agroupid)  
    {
        $rez = true;
        switch ( $type )
        {
            case 'transfer': 
                if ( in_array($newtype, array('notransfer','academ','exclude')) )
                {// меняем группы 
                    $rez = $rez AND $this->throw_out_from_group('condtransfer', $agenum, $agroupid);
                    $rez = $rez AND $this->throw_out_from_group('restore', $agenum+1, $agroupid);
                    break;
                }
            case 'condtransfer': 
                if ( in_array($newtype, array('notransfer','academ','exclude')) )
                {// меняем группы 
                    $rez = $rez AND $this->throw_out_from_group('transfer', $agenum, $agroupid);
                    $rez = $rez AND $this->throw_out_from_group('restore', $agenum+1, $agroupid);
                    break;
                }            
            case 'notransfer': 
                if ( in_array($newtype, array('transfer','condtransfer')) )
                {// меняем группы 
                    $rez = $rez AND $this->throw_out_from_group('exclude', $agenum, $agroupid);
                    $rez = $rez AND $this->throw_out_from_group('restore', $agenum, $agroupid);
                    break;
                }             
            case 'exclude': 
                if ( in_array($newtype, array('transfer','condtransfer')) )
                {// меняем группы 
                    $rez = $rez AND$this->throw_out_from_group('notransfer', $agenum, $agroupid);
                    $rez = $rez AND$this->throw_out_from_group('restore', $agenum, $agroupid);
                    break;
                }       
        }        
        return $rez;
    }
    
    /** Выкидывает учеников из группы
     * @param $type - тип перевода
     * @param $agenum - параллель(чтоб проще искать группу)
     * @param $agroupid - id группы
     * @return bool
     */
    public function throw_out_from_group($type,$agenum,$agroupid)
    {// загружаем приказ    
        $orderdata = $this->get_order_data();
        // объект группы для редактирования
        if ( empty($orderdata->data->student->{$type}[$agenum][$agroupid]) )
        {// нету группы - ничего делать не надо
            return true;
        }
        $group = $orderdata->data->student->{$type}[$agenum][$agroupid];
        unset($orderdata->data->student->{$type}[$agenum][$agroupid]);
        // в зависимости о типа перевода меняем данные группы
        foreach ( $group as $sbcid=>$sbcdata )
        {
            $orderdata->data->student->{$type}[$agenum][0][$sbcid] = 
                     $this->set_values_for_sbc($sbcid, $sbcdata->oldageid, $sbcdata->newageid,$sbcdata->status, 0, $type);
        }
        return $this->order->save($orderdata); 
        // $orderdata = $this->get_order_data();
        // print_object($orderdata->data->groups[$agroupid]); die();
    }
    
    
    /** Зачищает данные приказа по студенту от пустых значений
     * @param $orderdata - данные приказа по студентам
     * @return object - зачищенные данные
     */
    private function delete_null_values($orderdata)
    {
        if ( isset($orderdata->transfer) )
        {// если массив переведенных есть - зачистим его
            $orderdata->transfer = $this->delete_null_values_type($orderdata->transfer);
        }
        if ( empty($orderdata->transfer) )
        {//'если массив переведенных пустой, удалим его';
            unset($orderdata->transfer);
        }
        if ( isset($orderdata->condtransfer) )
        {// если массив условно переведенных есть - зачистим его
            $orderdata->condtransfer = $this->delete_null_values_type($orderdata->condtransfer);
        }
        if ( empty($orderdata->condtransfer) )
        {//'если массив условно переведенных пустой, удалим его';
            unset($orderdata->condtransfer);
        }
        if ( isset($orderdata->academ) )
        {// если массив взявших академический есть - зачистим его
            $orderdata->academ = $this->delete_null_values_type($orderdata->academ);
        }
        if ( empty($orderdata->academ) )
        {//'если массив взявших академ пустой, удалим его';
            unset($orderdata->academ);
        }
        if ( isset($orderdata->notransfer) )
        {// если массив не переведенных есть - зачистим его
            $orderdata->notransfer = $this->delete_null_values_type($orderdata->notransfer);
        }
        if ( empty($orderdata->notransfer) )
        {// 'если массив не переведенных пустой, удалим его';
            unset($orderdata->notransfer);
        }
        if ( isset($orderdata->restore) )
        {// если массив не переведенных есть - зачистим его
            $orderdata->restore = $this->delete_null_values_type($orderdata->restore);
        }
        if ( empty($orderdata->restore) )
        {// 'если массив не переведенных пустой, удалим его';
            unset($orderdata->restore);
        }
        if ( isset($orderdata->exclude) )
        {// если массив не переведенных есть - зачистим его
            $orderdata->exclude = $this->delete_null_values_type($orderdata->exclude);
        }
        if ( empty($orderdata->exclude) )
        {// 'если массив не переведенных пустой, удалим его';
            unset($orderdata->exclude);
        }
        return $orderdata;
    }
    
    /** Зачищает данные приказа по студенту от пустых значений одного типа
     * @param $typedada - данные приказа по студентам конкретного типа
     * @return object - зачищенные данные
     */
    private function delete_null_values_type($typedada)
    {
        if ( empty($typedada) )
        {// массив типа и так пустой - вернем его
            return $typedada;
        }
        foreach ( $typedada as $idagenum => $agenumdata )
        {
            if ( empty($agenumdata) )
            {//'если массив параллелей пустой, удалим его';
                unset($typedada[$idagenum]);
            }else
            {// иначе зачищаем группы
                foreach ( $agenumdata as $idagroup => $agroupdata )
                {
                    if ( empty($agroupdata) )
                    {//'если массив групп пустой, удалим его';
                        unset($typedada[$idagenum][$idagroup]);
                    }else
                    {// иначе зачищаем студентов
                        foreach ( $agroupdata as $idsbc => $sbcdata )
                        {
                            if ( empty($agroupdata) )
                            {//'если массив учеников пустой, удалим его';
                                unset($typedada[$idagenum][$idagroup][$idsbc]);
                            }
                        }
                        if ( empty($agenumdata[$idagroup]) )
                        {// 'если после зачистки массив групп стал пустой, удалим его';
                            unset($typedada[$idagenum][$idagroup]);
                        }
                    }
                }
                if ( empty($typedada[$idagenum]) )
                {//'если после зачистки массив параллелей стал пустой, удалим его';
                    unset($typedada[$idagenum]);
                }
            }
        }
        return $typedada;
    }

    /** Выполняет проверку, чтобы с одним и тем же студентом
     * не проделали разные операции, т.е. на момент формирования ордера он 
     * может присутствовать в нескольких, и , если, в 1 с ним что-то сделали
     * что б другие ордера тогда не подписывались
     * @param $student - запись (часть ордера)
     * @return bool
     */    
    public function check_order_data()
    {
        $orderdata = $this->get_order_data();
        if ( isset($orderdata->data->groups) )
        {// пустой
            foreach ( $orderdata->data->groups as $id=>$obj )
            {
                if ( $this->dof->storage('agroups')->get_field($id,'agenum')
                         != $obj->oldagenum )
                {// группа уже не учится в этой параллели
                    return false;
                }
            }
        }
        if ( empty($orderdata->data->student) )
        {// пустой
            return false;
        }
        // массив всевозможных "переводов"
        $array = array('transfer','condtransfer','notransfer','restore','academ');
        foreach ($array as $type)
        {     
            switch ($type)
            {// определим масив статусов
                case 'restore'      :  $mas = array('onleave');break; 
                case 'transfer'     :  $mas = array('active','condactive');break;
                case 'condtransfer' :  $mas = array('active','condactive');break;           
                case 'notransfer'   :  $mas = array('active','condactive','suspend');break;
                case 'academ'       :  $mas = array('active','condactive','suspend');break;                      
            }
            $student = $orderdata->data->student;  
            if ( isset($student->$type) )
            {// массив transfer
                $type = $student->$type;
                foreach ($type as $key=>$groups)
                {// массив групп
                    foreach ($groups as $key=>$sbcids)
                    {// массив подписок 
                        foreach ($sbcids as $key=>$obj)
                        {// подписка - объект
                            $sbc = $this->dof->storage('programmsbcs')->get($key);
                            if (  ! in_array($sbc->status, $mas) OR $sbc->agenum != $obj->oldagenum )
                            {
                                return false;
                            }
                           // if ( $obj->newagroupid == '3' ) {printobject($obj); echo "<br>"; print_object($orderdata->data->groups[$obj->newagroupid]);die();}
                            if ( $obj->newagroupid AND isset($orderdata->data->groups[$obj->newagroupid]) 
                                 AND $obj->newagenum != $orderdata->data->groups[$obj->newagroupid]->newagenum )
                            {// если параллель группы и ученика не совпадают
                                return false;
                            }
                        }
                    }
                }
            }
        }
        return true;
    }
    
    /** Проверить и определить, какие группы нужно удалить из приказа, а какие оставить
     * 
     * @return bool 
     * @param object $orderdata - объект со всеми данными приказа
     */
    public function recheck_excluded_groups()
    {
        if ( ! $orderdata = $this->get_order_data() )
        {// не нашли приказ
            return false;
        }
        $newgroups = array();
        // перебираем все группы, вошедшие в приказ
        foreach ( $orderdata->data->groups as $groupid=>$group )
        {
            if ( $this->group_is_excluded($orderdata, $groupid, $group) )
            {// если группу надо будет исключить - отметим это
                $group->exclude = true;
            }else
            {// если не надо - то тоже отметим
                $group->exclude = false;
            }
            $newgroups[$groupid] = $group;
        }
        // изменяем данные приказа
        $orderdata->data->groups = $newgroups;
        // сохраняем то что получилось
        return $this->order->save($orderdata);
    }
    
    /** Определить, действительно ли нужно исключить группу из приказа
     * @todo выяснить возможно ли что период не изменяется, а параллель изменяется? 
     * Какой группу считать в этом случае?
     * 
     * @return bool 
     * @param object $orderdata - объект с полной информацией о приказе
     * @param int $groupid - id группы в таблице agroups
     * @param object $group - данные группы из сформированного приказа
     */
    protected function group_is_excluded($orderdata, $groupid, $group)
    {
        if ( $group->oldageid != $group->newageid )
        {// период группы изменился - значит она должна попасть в приказ
            return false;
        }
        if ( $group->oldagenum != $group->newagenum )
        {// у группы изменилась параллель- она должна попасть в приказ
            return false;
        }
        $types = array('transfer', 'condtransfer', 'notransfer');
        foreach ( $types as $type )
        {
            if ( ! isset($orderdata->data->student->$type) OR 
                 ! is_array($orderdata->data->student->$type) )
            {// нет учеников с тапим типом перевода
                continue;
            }
            // собираем все периоды группы
            $agenums = $orderdata->data->student->$type;
            foreach ( $agenums as $agenum=>$studentgroups )
            {// в каждой параллели ищем учеников группы
                if ( ! is_array($studentgroups) )
                {// в этой параллели нет групп
                    continue;
                }
                foreach ( $studentgroups as $id=>$students )
                {// перебираем все группы параллели
                    if ( $id == $groupid )
                    {// нашелся хотя бы один ученик, ссылающийся на группу - 
                        // мы не можем ее исключить
                        return false;
                    }
                }
            }
            
        }
        // мы не нашли ни одного присутствующего в приказе ученика, 
        // ссылающегося на эту группу - значит ее нужно исключить из приказа
        return true;
    }
    
    /** Удаляет исключенных учеников, которые не должны попасть в приказ
     * @param $orderdata - данные приказа по студентам
     * @return object - зачищенные данные
     */
    public function delete_exclude()
    {
        // перепроверяем данные приказа - определяем, какие группы действительно нужно исключить
        $this->recheck_excluded_groups();
        if ( ! $orderdata = $this->get_order_data() )
        {// не нашли приказ
            return false;
        }
        // удаляем исключенных учеников
        unset($orderdata->data->student->exclude);
        // получаем все группы из приказа
        $oldgroups = $orderdata->data->groups;
        // в этом массиве будем хранить новый список групп - удалив все исключенные
        $newgroups = array();
        // удаляем исключенные группэ
        foreach ( $oldgroups as $id=>$group )
        {
            if ( $group->exclude )
            {// удаляем из приказа группы которые нужно исключить
                continue;
            }
            // поле "исключить из приказа" нам больше не нужно - поэтому 
            // оно не должно попасть в сохраненный приказ
            unset($group->exclude);
            // все остальные данные сохраняем как есть
            $newgroups[$id] = $group;
        }
        $orderdata->data->groups = $newgroups;
        
        return $this->order->save($orderdata);
    }

    /** Сортировка смертных по имени
     * @return unknown_type
     */
    private function sort_by_name($obj1,$obj2)
    {
        return strnatcmp($obj1->sortname, $obj2->sortname);
    }
    
    /** Сортировка групп по имени
     * @return unknown_type
     */
    private function sort_by_groupname($obj1,$obj2)
    {
        return strnatcmp($obj1->groupname, $obj2->groupname);
    }

    /** Сортировка без групп по программе
     * @return unknown_type
     */
    private function sort_by_nogroupprog($obj1,$obj2)
    {
        return strnatcmp($obj1->prog, $obj2->prog);
    }    
    
    /* Массов исключает студентов, группы из приказа
     * @param int $agenum - паралель, с которой надо исключить
     * @param int $progid - программ итз коорой надо исключить
     * @param int $groupuid - группа, которую надо исключить, по умолчанию null
     * 
     * @return bool
     */
    public function getout_from_order($type, $agenum, $group=null)
    {
 
        if( $formdata->agroupid != 0 )
        {// менялась вся группа и/или ученики
            print_object($formdata); die('3');
            
            $order->save_options_agroup($formdata->type,$formdata);
            
        }else 
        {// ученики без группы(много)
            print_object($formdata); die('2');
            
            $order->save_options_students_nogroup($formdata->type, $formdata);
        }        
        
        
        
        
        
        
        
    }


}

/** Функция для обхода бага в QuickForm-элементе 'hierselect'
 * Получает тип перевода ученика в зависимости от его номера 
 * 
 * @return mixed $type - string: тмп перевода ученика
 *                       bool(false): не существующее значение 
 * @param int $number - номер опции, указанной в hierselect
*/
function dof_im_learningorders_ordertransfer_get_transfertype($number)
{
    switch ( $number )
    {
        case 1: return 'transfer';
        case 2: return 'condtransfer';
        case 3: return 'notransfer';
        case 4: return 'academ';
        case 5: return 'exclude';
        case 6: return 'restore';
    }
    return false;
}


?>