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

// Загрузка библиотек верхнего уровня
require_once(dirname(realpath(__FILE__))."/../lib.php");

// подключаем библеиотеки и стили
$DOF->modlib('widgets')->js_init('show_hide');
$DOF->modlib('nvg')->add_css('im', 'recordbook', '/style.css');

/**
 * Собирает данные для главной страницы дневника
 *
 */
class dof_im_recordbook_studentslist
{
    /**
     * Принцип работы по крупному:
     * 
     * входные данные:
     *  id того, кто смотрит страницу
     * 0. Получаем все договоры, в которых смотрящий является 
     * учеником, представителем или куратором.
     * 1. Перебираем договоры.
     *      0. Создаем заготовку результирующего массива.
     *         array([studentid] => contracts)
     *      1. Берем договор.
     *      2. Получаем все подписки ученика на потоки.
     *      3. Оставляем подписки, которые относятся 
     *         к текущему учебному периоду.
     *      4. Перебираем потоки.
     *          1. Берем поток 
     *          2. Получаем подписку на программу.
     *          3. Находим контракт в результирующем массиве.
     *          4. Если еще нет, то добавляем к объекту контракт свойство 
     *             contract->programms = array(programm).
     *          5. Записываем в свойство progamm->courses = array(course) 
     *             дисциплину, которую получем по programmitemid.
     * 2. Создаем объект для темплатера.
     * 3. Рисуем страницу.
     */
    
    
    /**
     * @var dof_control
     */
    protected $dof;
    
    /**
     * Хранит все информацию, 
     * необходимую для рисования таблицы
     * структура:
     * array([studentid][person] = запись из таблицы persons
     * array([studentid][contracts] = 
     *    array([contractid] = $contract -> {свойства объекта контракт}
     *                       $contract -> programms = 
     *       array([programmid] = $programm -> {свойства объекта программа}
     *                            $programm -> programmitems = 
     *          array([programmitemid] = объект дисциплины)
     *            )
     *         )
     *      )
     * @var array 
     */
    public $data = array();
        
    /**
     * Конструктор
     * @param dof_control $dof - методы ядра системы
     * @return void
     */
    public function __construct(dof_control $dof)
    {
        $this->dof = $dof;
    }

    /**
     * Возвращает накопленные данные
     * @return array
     */
    public function get_data()
    {
        return $this->data;
    }
    
    /**
     * Добавляет студентов и их контракты в $this->data.
     * Создает структуру данных,
     * которую надо заполнять информацией.
     * @param $userid - id пользователя, который смотрит
     * @return bool
     */
    public function set_data($clientid)
    {
        if ( ! $this->dof->storage('persons')->is_exists($clientid) )
        {// клиента нет в базе, никаких данных собрать не получится
            $this->dof->print_error('no_base_data', $this->dof->url_im('recordbook'), (int)$clientid, 'im', 'recordbook');
        }
        //получаем все контракты пользователя
        $contracts = $this->get_allstudents_contracts($clientid);
        if ( is_array($contracts) AND ! empty($contracts) )
        {//контракты есть
            foreach ( $contracts as $one )
            {//перебираем их и добавляем в структуру
                if ( ! $this->add_contract($one->studentid, $one) )
                {//не добавили';
                    return false;
                }
            }
            //все контракты добавлены
            return true;
        }else
        {//контрактов нет
            return true;
        }
    }
    /**
     * Заполняет созданную структуру данными
     * представленных в ней студентов
     * @param int $ageid - id текущего периода
     * @return bool
     */
    public function add_data()
    {
        //перебираем студентов';
        foreach ( $this->data as $uid => $one )
        {//перебираем студентов и заполняем 
            //$this->data информацией
            if ( ! $cpassed = $this->get_student_cpasseds($uid) )
            {//не получили учебные потоки студента'.$uid;
                //в текущем периоде студент не учится
                continue;
            }
            if ( ! $this->add_student_cpasseds($cpassed) )
            {//не удалось добавить дисциплины';
                return false;
            }
        }
        //ksort($this->data);
        return true;
    }

    /**
     * Возвращает все контракты, в которых пользователь 
     * числиться как студент или как законный представитель 
     * @param $userid - id пользователя. Если null, то используется $USER->id.
     * @return array массив контрактов. Сначала идут контракты, 
     * в которых пользователь является студентом, потом те, 
     * в которых он является законным представителем
     * Если ничего не найдено - возваращается пустой массив 
     */
    private function get_allstudents_contracts($userid = null)
    {
        //получаем все договоры, 
        //в которых смотрящий проходит 
        //учеником, 
        //законным представителем или куратором
        //вернуть массив договоров, в перечисленном порядке
        //договоры, в которых он не является учеником, 
        //надо отсортировать по ФИО учеников??????
        $contracts = array();
        if ( $iamstudent = $this->dof->storage('contracts')->get_list_by_student($userid) )
        {//получаем контракты смотрящего как студента
            $contracts = array_merge($contracts, $iamstudent);
        }
        if( ! empty($contracts) )
        {//клиент - студент, запомним его id
            $clientid = current($contracts)->studentid;
        }
        if ( $mystudents = $this->dof->storage('contracts')->get_list_by_client($userid) )
        {//получаем все контракты на учеников смотрящего 
            foreach ( $mystudents as $key => $one )
            {//перебираем студентов, которых представляет клиент
                if ( isset($clientid) AND $one->studentid == $clientid)
                {//ищем записи, в которых он сам является студентом
                    //если они есть - удаляем их
                    unset($mystudents[$key]);
                }
            }
            $contracts = array_merge($contracts, $mystudents);
        }
        return $contracts;
    }
    
    /**
     * Получаем все подписки на изучение дисциплин 
     * в текущем периоде 
     * @param int $studentid - id студента
     * @return mixed - array - массив записей из 
     * таблицы cpassed или bool  false
     */
    private function get_student_cpasseds($studentid)
    {
        //получаем все подписки одного студента
        $data = new stdClass();
        $data->studentid = $studentid;
        $data->status = array('active','completed','failed','reoffset','suspend');
        if ( ! $cpasseds = $this->dof->storage('cpassed')->
               get_listing($data) )
        {//не получили
            return false;
        }
        // @todo Убрать этот foreach 
        foreach ( $cpasseds as $k => $cp )
        {//перебираем контракты
            if ( ! $this->is_stream_going($cp->cstreamid, $cp->programmsbcid) )
            {//удаляем те, которые из неактивного
                // не делаем проверку на период
                //unset($cpasseds[$k]);
            }
        }
        return $cpasseds;
    }
    /**
     * Добавляем все дисциплины одного студента в 
     * общую структуру данных
     * @param array $cpasseds - массив подписок на учебные
     * дисциплины текущего периода 
     * @return bool - сообщает об отсутствии ошибок или наоборот
     */
    private function add_student_cpasseds($cpasseds)
    {
        if (! is_array($cpasseds) )
        {//переданы неправильные данные';
            return false;
        }
        
        foreach ( $cpasseds as $one )
        {//перебираем потоки и добавляем в структуру данных
            if ( ! $progsbc = $this->dof->storage('programmsbcs')->
                   get($one->programmsbcid) )
            {//не получили подписку на программу';
                return false;
            }
            if ( ! $this->is_exists_contract($one->studentid, $progsbc->contractid) )
            {//пропускаем дисциплины, которые изучаются по другим контрактам';
                continue;
            }
            //все нормально - добавляем дисциплину в структуру';
            if ( ! $this->add_programmitem($one->studentid, 
                   $progsbc->contractid, $progsbc->programmid, $progsbc->id, $one->programmitemid, $one->id) )
            {//не удалось добавить дисциплуну';
                return false;
            }
        }
        return true;
    }
    
    /***** Методы добавления элементов структуры *****/
    
    /**
     * Добавляет запись дисциплины в 
     * результирующий массив
     * @param int $studentid - id студента
     * @param int $contractid - id контракта
     * @param int $programmid - id программы
     * @param int $programmitemid - id дисциплины
     * @return bool true если дисциплина добавлена
     * или false, в ином случае
     */
    private function add_programmitem($studentid, $contractid, $programmid, $programmsbcid, $programmitemid, $cpassedid)
    {
        if ( $this->is_exists_programmitem($studentid, 
            $contractid, $programmid, $programmsbcid, $cpassedid) )
        {//дисциплина уже добавлена';
            return true;
        }
        if ( ! $item = $this->dof->storage('programmitems')->get($programmitemid) )
        {//не получили запись дисциплины';
            return false;
        }
        if ( ! $this->is_exists_programmsbc($studentid, $contractid, $programmid, $programmsbcid) )
        {//нет программы - добавляем';
            if ( ! $this->add_programmsbc($studentid, $contractid, $programmid, $programmsbcid) )
            {//не удалось добавить программу
                return false;
            }
        }
        if ( ! isset($this->data[$studentid]['contracts'][$contractid]->
             programms[$programmid]->programmsbcs[$programmsbcid]->programmitems) )
        {//не определено необходимое свойство';
            //определим
            $this->data[$studentid]['contracts'][$contractid]->
             programms[$programmid]->programmsbcs[$programmsbcid]->programmitems = array();
        }
        // заносим дополнительный идентификатор в массив для того чтобы потом создать по нему ссылку
        $item->cpassedid = $cpassedid;
        //заносим дисциплину в массив данных';
        $this->data[$studentid]['contracts'][$contractid]->
             programms[$programmid]->programmsbcs[$programmsbcid]->
             programmitems[$cpassedid] = $item;  
        return true;
    }
    
    /**
     * Добавляет программу в массив данных
     * @param int $studentid - id студента
     * @param int $contractid - id контракта
     * @param int $programmid - id программы
     * @return bool true если дисциплина добавлена
     * или false, в ином случае
     */
    private function add_programmsbc($studentid, $contractid, $programmid, $programmsbcid)
    {
        if ( $this->is_exists_programmsbc($studentid, $contractid, $programmid, $programmsbcid) )
        {//программа уже добавлена
            return true;
        }
        if ( ! $programmsbc = $this->dof->storage('programmsbcs')->get($programmsbcid) )
        {//не удалось получить запись программы
            return false;
        }
        if ( ! $this->is_exists_programm($studentid, $contractid, $programmid) )
        {//контракт не существует - добавляем
            if ( ! $this->add_programm($studentid, $contractid, $programmid) )
            {//не удалось добавить контракт
                return false;
            }
        }
        if ( ! isset($this->data[$studentid]['contracts'][$contractid]->programms[$programmid]->programmsbcs) )
        {//необходимое свойство не определено - определяем
            $this->data[$studentid]['contracts'][$contractid]->programms[$programmid]->programmsbcs = array();
        }
        //добавляет данные одной программы в 
        //результирующий массив
        $this->data[$studentid]['contracts'][$contractid]->programms[$programmid]->
            programmsbcs[$programmsbc->id] = $programmsbc;
        return true;
    }
    
    /**
     * Добавляет программу в массив данных
     * @param int $studentid - id студента
     * @param int $contractid - id контракта
     * @param int $programmid - id программы
     * @return bool true если дисциплина добавлена
     * или false, в ином случае
     */
    private function add_programm($studentid, $contractid, $programmid)
    {
        if ( $this->is_exists_programm($studentid, $contractid, $programmid) )
        {//программа уже добавлена
            return true;
        }
        if ( ! $programm = $this->dof->storage('programms')->get($programmid) )
        {//не удалось получить запись программы
            return false;
        }
        if ( ! $this->is_exists_contract($studentid, $contractid) )
        {//контракт не существует - добавляем
            if ( ! $this->add_contract($studentid, $contractid) )
            {//не удалось добавить контракт
                return false;
            }
        }
        if ( ! isset($this->data[$studentid]['contracts'][$contractid]->programms) )
        {//необходимое свойство не определено - определяем
            $this->data[$studentid]['contracts'][$contractid]->programms = array();
        }
        //добавляет данные одной программы в 
        //результирующий массив
        $this->data[$studentid]['contracts'][$contractid]->
            programms[$programm->id] = $programm;
        return true;
    }
    
    /**
     * Добавляем контракт в массив данных
     * @param int $studentid - id студента
     * @param mixed object $contractid - запись контракта или
     * int - id контракта
     * @return bool true если контракт добавлен
     * или false, в ином случае
     */
    private function add_contract($studentid, $contractid)
    {
        if ( is_int_string($contractid) )
        {//передан id контракта - получим его';
            if ( ! $contract = $this->dof->storage('contracts')->get($contractid) )
            {//не получили запись контракта
                return false;
            }
        }elseif( is_object($contractid) )
        {//передана запись контракта';
            $contract = clone $contractid;
        }else
        {//передано непонятно что';
            return false;
        }
        if ( $this->is_exists_contract($studentid, $contract->id) )
        {//контракт уже добавлен';
            return true;
        }
        if ( ! $this->is_exists_student($studentid) )
        {//студент отсутствует в структуре - добавляем';
            if ( ! $this->add_student($studentid) )
            {//не удалось добавить студента в массив данных'. $studentid;
                return false;
            }
        }
        if ( ! isset($this->data[$studentid]['contracts']) )
        {//не определено необходимый элемент - определяем
            $this->data[$studentid]['contracts'] = array();
        }
        $this->data[$studentid]['contracts'][$contract->id] = $contract;
        return true;
    }
    
    /**
     * Добавляет информацию о студенте в 
     * структуру данных
     * @param int $studentid - id студента
     * @return bool true если студент добавлен
     * или false, в ином случае
     */
    private function add_student($studentid)
    {
        if ( ! $user = $this->dof->storage('persons')->get($studentid) )
        {//не получили запись студента
            return false;
        }
        $this->data[$user->id] = array('person' => $user);
        return true;
    }
    
    /***** Методы проверки наличия элементов структуры *****/

    /**
     * Возвращает истину, если поток принадлежит 
     * к указанному учебному периоду
     * иначе возвращает ложь 
     * @param int $cstreamid - id потока 
     * @param $programmsbcid - id подписки на программу
     * @return bool
     */
    private function is_stream_going($cstreamid, $programmsbcid)
    {
        //получаем последнюю запись из learninghistory
        $last = $this->dof->storage('learninghistory')->
                        get_actual_learning_data($programmsbcid);
        if ( ! $last )
        {//не получили запись
            return false;
        }
        //из нее берем ageid
        //и проверяем - переданный нам поток с таким же ageid или нет
        $lastageid = $this->dof->storage('ages')->get_next_ageid($last->ageid, $last->agenum);
        return $this->dof->storage('cstreams')->
            is_exists(array('id'=>(int)$cstreamid, 'ageid'=>(int)$lastageid));
    }
    
    /**
     * Проверяет наличие дисциплины в данных студента
     * @param int $studentid - id студента
     * @param int $contractid - id контракта
     * @param int $programmid - id программы
     * @param int $programmitemid - id дисциплины
     * @return bool
     */
    private function is_exists_programmitem($studentid, $contractid, $programmid, $programmsbcid, $cpassedid)
    {
        if ( ! $this->is_exists_programmsbc($studentid, $contractid, $programmid, $programmsbcid) )
        {//программа не найдена
            return false;
        }
        if ( ! isset($this->data[$studentid]['contracts'][$contractid]->
                        programms[$programmid]->programmsbcs[$programmsbcid]->programmitems) )
        {//нет дисциплин 
            return false;
        }
        return isset($this->data[$studentid]['contracts'][$contractid]->
                        programms[$programmid]->programmsbcs[$programmsbcid]->programmitems[$cpassedid]) 
               AND
               is_object($this->data[$studentid]['contracts'][$contractid]->
                        programms[$programmid]->programmsbcs[$programmsbcid]->programmitems[$cpassedid]);
    }
    
    /**
     * Проверяет наличие программы в данных студента
     * @param int $studentid - id студента
     * @param int $contractid - id контракта
     * @param int $programmid - id программы
     * @return bool
     */
    private function is_exists_programmsbc($studentid, $contractid, $programmid, $programmsbcid)
    {
        if ( ! $this->is_exists_programm($studentid, $contractid, $programmid) )
        {//не нашли контракта
            return false;
        }
        //получаем контракт
        $programm = $this->data[$studentid]['contracts'][$contractid]->programms[$programmid];
        if ( ! isset($programm->programmsbcs) OR 
             ! is_array($programm->programmsbcs) )
        {//нет программ в контракте";
            return false;
        }
        if ( ! isset($programm->programmsbcs[$programmsbcid]) )
        {//нет такой программы в контракте
            return false;
        }
        return is_object($programm->programmsbcs[$programmsbcid]);
    }
    
    /**
     * Проверяет наличие программы в данных студента
     * @param int $studentid - id студента
     * @param int $contractid - id контракта
     * @param int $programmid - id программы
     * @return bool
     */
    private function is_exists_programm($studentid, $contractid, $programmid)
    {
        if ( ! $this->is_exists_contract($studentid, $contractid) )
        {//не нашли контракта
            return false;
        }
        //получаем контракт
        $contract = $this->data[$studentid]['contracts'][$contractid];
        if ( ! isset($contract->programms) OR 
             ! is_array($contract->programms) )
        {//нет программ в контракте
            return false;
        }
        if ( ! isset($contract->programms[$programmid]) )
        {//нет такой программы в контракте
            return false;
        }
        return is_object($contract->programms[$programmid]);
    }
    
    /**
     * Проверяет наличие контракта в данных студента
     * @param int $studentid - id студента
     * @param int $contractid - id контракта
     * @return bool 
     */
    private function is_exists_contract($studentid, $contractid)
    {
        if ( ! $this->is_exists_student($studentid) )
        {//не найдена ветка студента
            return false;
        }
        if ( ! isset($this->data[$studentid]['contracts']) )
        {//контракты не найдены
            return false;
        }
        if ( ! isset($this->data[$studentid]['contracts'][$contractid])  )
        {//нет нужного контракта
            return false;
        }
        return is_object($this->data[$studentid]['contracts'][$contractid]);
    }
    /**
     * Проверяет наличие в массиве данных элемента студента для 
     * хранения информации о нем
     * @param int $studentid
     * @return bool true - если студент есть
     * false - если не найден
     */
    private function is_exists_student($studentid)
    {
        if ( ! $this->data )
        {//нет структуры данных
            return false;
        }
        //нашли элемент данных нужного студента
        return isset($this->data[$studentid]);
    }
    
    /***** Методы вывода информации на экран *****/
    
    /**
     * собирает структуру для templater 
     * @param int $clientid - id того, кто просматривает список
     * @param int $ageid - id периода
     * @return object
     */
    public function get_output($clientid)
    {
        //упорядочиваем данные по ФИО студентов
        $this->arrange_students($clientid);
        //формируем выходной объект
        $outdata = new stdClass();
        $outdata->students = array();
        foreach ( $this->data as $stid => $student)
        {
            if ( ! isset($this->data[$stid]['contracts']) )
            {//на указанного студента нет контрактов
                continue;
            }
            //перебираем контракты студента и 
            foreach ($this->data[$stid]['contracts'] as $contractid => $contract )
            {//формируем выходные строки
                $outdata->students[] = $this->get_output_student($stid, $contractid, $clientid);
            }
        }
        return $outdata;
    }
    
    /**
     * Возвращает программы, изучаемые студентом по 
     * переданному контракту 
     * @param int $studentid - id студента, данные по которому надо собрать
     * @param int $contractid - id контракта, по которому учится студент
     * @param int $clientid - id клиента, который направил студента на обучение
     * @param int $ageid - id просматриваемого периода
     * @return object
     */
    private function get_output_student($studentid, $contractid, $clientid)
    {
        if ( ! isset($this->data[$studentid]['contracts'][$contractid]) )
        {//нет такого элемента
            return array();
        }
        $cont = $this->data[$studentid]['contracts'][$contractid];
        $one = new stdClass();
        //добавляем номер контракта и ФИО обучающегося
        $usr = $this->data[$studentid]['person'];
        $fullname = $this->dof->storage('persons')->get_fullname($usr);
        $one->fullname = $fullname;
        $one->num = $this->dof->get_string('personnel_number','recordbook') . ' ' . $cont->num;
        $one->numfio = "$cont->num $fullname";
        if ( $cont->status != 'work' )
        {
            $one->numfio = '<span class=gray>'.$one->numfio.'</span>';
        }
        //добавляем дисциплины учебной программы
        $one->programms = $this->get_output_programms($studentid, $cont->id, $clientid);
        //заносим программу в массив программ текущего контракта
        return $one;
    }
    
    /**
     * возвращает все дисциплины и программы, изучаемые студентом по контракту
     * @param int $studentid - id студента
     * @param int $contractid - id контракта
     * @param int $clientid - id того кто смотрит страницу
     * @param int $ageid - id периода
     * @return array
     */
    private function get_output_programms($studentid, $contractid, $clientid)
    {
        $depid = optional_param('departmentid', 0, PARAM_INT);
        $addvars = array();
        $addvars['departmentid'] = $depid;
        if ( ! isset($this->data[$studentid]['contracts']
                               [$contractid]->programms) )
        {//нет такого элемента
            return array();
        }
        $programms = array();
        $allprogs = $this->data[$studentid]['contracts']
                    [$contractid]->programms;
        foreach ( $allprogs as $prog )
        {//перебираем элементы учебной программы
            $one = new stdClass();
            $one->programm = $this->dof->get_string('program_title','recordbook') . ': ' . $prog->name;  
            if ( $prog->status != 'available' )
            {
                $one->programm = '<span class=gray>'.$one->programm.'</span>';
            }
            //добавляем дисциплины учебной программы
            $one->programmsbcs = $this->get_output_programmscbs($studentid, $contractid, $clientid, $prog->id);
            //заносим программу в массив программ текущего контракта
            $programms[] = $one;
        }
        //возвращаем результат
        return $programms;
        
    }
    
    /**
     * возвращает все дисциплины и программы, изучаемые студентом по контракту
     * @param int $studentid - id студента
     * @param int $contractid - id контракта
     * @param int $clientid - id того кто смотрит страницу
     * @param int $ageid - id периода
     * @return array
     */
    private function get_output_programmscbs($studentid, $contractid, $clientid, $programmid)
    {
        $depid = optional_param('departmentid', 0, PARAM_INT);
        $addvars = array();
        $addvars['departmentid'] = $depid;
        if ( ! isset($this->data[$studentid]['contracts']
                               [$contractid]->programms[$programmid]->programmsbcs) )
        {//нет такого элемента
            return array();
        }
        $programms = array();
        $allprogsbcs = $this->data[$studentid]['contracts']
                    [$contractid]->programms[$programmid]->programmsbcs;
        foreach ( $allprogsbcs as $progsbc )
        {//перебираем элементы учебной программы
            $one = new stdClass();
            //добавляем название программы как ссылку на 
            //страницу описания программы
            $param = '?programmsbcid='.$progsbc->id;
            $path = $this->dof->url_im('recordbook','/program.php'.$param,$addvars);
            $schedule_path = $this->dof->url_im('recordbook','/recordbook.php'.$param,$addvars);
            $one->schedule_path = '<a href="'.$schedule_path.'">' . $this->dof->get_string('lesson_schedule', 'recordbook') . '</a>';
            $one->programmsbc = $this->dof->get_string('subscription', 'recordbook') . ' № <a href="'.$path.'">'.$progsbc->id.'</a>';  
            if ( $progsbc->status != 'active' )
            {
                $one->programmsbc = '<span class=gray_link>'.$one->programmsbc.'</span>';
            }
            //добавляем дисциплины учебной программы
            $one->items = $this->get_output_items($studentid, $contractid, $programmid, $clientid, $progsbc->id);
            //заносим программу в массив программ текущего контракта
            $programms[] = $one;
        }
        //возвращаем результат
        return $programms;
        
    }
    
    /**
     * Возвращает список дисциплин, изучаемых в рамках программы
     * @param int $studentid - id студента
     * @param int $contractid - id контракта
     * @param int $programmid - id программы
     * @return array
     */
    private function get_output_items($studentid, $contractid, $programmid, $clientid, $progsbcid)
    {
        $depid = optional_param('departmentid', 0, PARAM_INT);
        $addvars = array();
        $addvars['departmentid'] = $depid;
        if ( ! isset($this->data[$studentid]['contracts']
             [$contractid]->programms[$programmid]->programmsbcs[$progsbcid]->programmitems) )
        {//нет такого элемента
            return array();
        }
        $items = array();
        //Добавляем ссылку на дневник';
        $param = '?programmsbcid='.$progsbcid;
        $path = $this->dof->url_im('recordbook','/recordbook.php'.$param,$addvars);
        $linkname = '<a href="'.$path.'">'.$this->dof->get_string('lesson_schedule','recordbook').'</a>'; 
        if ( $this->dof->storage('programmsbcs')->get_field($progsbcid,'status') != 'active' )
        {
            $linkname = '<span class=gray_link>'.$linkname.'</span>';
        }
        @$items[]->item = $linkname;
        //получаем элементы учебной программы
        $allitems = $this->data[$studentid]['contracts']
                    [$contractid]->programms[$programmid]->programmsbcs[$progsbcid]->programmitems;
        foreach ( $allitems as $item )
        {//перебираем элементы учебной программы
            $one = new stdClass();
            $param = '?cpassedid='.$item->cpassedid;
            $path = $this->dof->url_im('recordbook','/discipline.php'.$param,$addvars);
            $one->item = '<a href="'.$path.'">'.$item->name.'</a>';  
            if ( $this->dof->storage('cpassed')->get_field($item->cpassedid,'status') != 'active' )
            {
                $one->item = '<span class=gray_link>'.$one->item.'</span>';
            }
            //название каждого заносим в массив
            $items[] = $one;
        }
        //print_object($items);
        //возвращаем результат
        return $items;
    }
    /**
     * Упорядочивает собранную структуру 
     * по ФИО студентов. ФИО кдлиента идет первым.
     * Если он есть в списке студентов
     * @param int $clientid - id того, кто просматривает страницу
     * если null, то текущий пользователь.
     * @return bool true
     */
    private function arrange_students($clientid = null )
    {
        global $USER;
        //сортируем массив по именам студентов
        uasort($this->data,'arrange_pair');
        if ( is_null($clientid) )
        {//id смотрящего не передан -
            //значит это текущий пользователь
            $clientid = $USER->id;
        }
        if ( array_key_exists($clientid, $this->data) )
        {//данные того, кто смотрит всегда первые
            //получили их
            $first = array($clientid => $this->data[$clientid]);
            //удалили из массива
            unset($this->data[$clientid]);
            //вставили в начало массива
            $this->data = $first + $this->data;
        }
        return true;
    }
}

/**
 * Сравнивает два имени
 * Используется для сортировки студентов по алфавиту
 * в $this->data класса dof_im_recordbook_studentslist
 * Вызывается функцией сортировки массива uasort, 
 * которая используется в методе arrange_students
 * @param array $fullone - элемент массива 
 * $this->data из класса dof_im_recordbook_studentslist
 * @param array $fulltwo - элемент массива 
 * $this->data из класса dof_im_recordbook_studentslist
 * @return int -1, 0, 1.
 */
function arrange_pair($fullone, $fulltwo)
{
    $one = $fullone['person'];
    $two = $fulltwo['person'];
    $onefullname = $this->dof->storage('persons')->get_fullname($one);
    $twofullname = $this->dof->storage('persons')->get_fullname($two);
    return strcasecmp($onefullname, $twofullname);
    
}

/**
 * Класс отвечающий за отрисовку экрана учебной программы
 *
 */
class dof_im_recordbook_learning_program
{
    /**
     * @var dof_control
     */
    protected $dof;
    protected $prsbcid;
    
    /** Конструктор класса - создает объект $DOF
     * @param dof_control $dof
     */
    function __construct(dof_control $dof, $programsbcid)
    {
        $this->dof = $dof;
        $this->prsbcid = $programsbcid;
    }
    
    /** Получить все id необходимые для навигации 
     * по странице учебной программы (program.php)
     * 
     * @return object объект, содержащий все необходимые id для навигации
     * @param int $programsbcid - id подписки на учебную программу в таблице programmsbcs
     */
    private function get_navigation_ids($programsbcid)
    {
        $depid = optional_param('departmentid', 0, PARAM_INT);
        $addvars = array();
        $addvars['departmentid'] = $depid;
        if ( ! $programsbc = $this->dof->storage('programmsbcs')->
                                    get($programsbcid) )
        {// подписка на учебную программу не найдена - 
            // не можем получить остальные идентификаторы
            print_error($this->dof->get_string('no_learning_program', 
            'recordbook'), '', $this->dof->url_im('standard','',$addvars));
        }
        if ( ! $contract = $this->dof->storage('contracts')->
                                       get($programsbc->contractid) )
        {// не получили контракт из базы
            print_error($this->dof->get_string('no_contract_in_base', 
            'recordbook', $programsbc->contractid), '', $this->dof->url_im('standard','',$addvars));
        }
        $result = new stdClass();
        $result->studentid  = $contract->studentid;
        // получаем id учебной программы
        $result->programmid = $programsbc->programmid;
        // получаем id периода
        $learningdata = $this->dof->storage('learninghistory')->get_actual_learning_data($programsbcid);
        if ( ! $learningdata )
        {// не удалось получить данные об обучении
            // проверку контракта и подпискы выполнены сверху
            // собщим, что обучение ещё не начилось
            $result->error = $this->dof->get_string('no_learning_history', 'recordbook');
        }else
        {// вытаскиваем текущий ageid из истории обучения
            $result->ageid     = $learningdata->ageid;
        }
        //получаем id клиента, отвечающего за ученика
        $result->clientid = (int)$this->dof->storage('persons')->get_by_moodleid_id();
        if ( ! $result->clientid )
        {// не получен id клиента
            print_error($this->dof->get_string('no_client_in_base', 'recordbook', (int)$result->clientid), '', $this->dof->url_im('standard','',$addvars));
        }
        return $result;
    }
    
    /** Получить всю информацию по учебной программе
     * 
     * @return object запись из таблицы programms
     * @param int $programid - id элемента учебной программы, 
     * который надо получить
     */
    public function get_programm_data($programmid)
    {
        if ( ! is_numeric($programmid) )
        {// неверный формат исходных данных
            return false;
        }
        return $this->dof->storage('programms')->get($programmid);
    }
    
    /** Получить информацию об учебном подразделении (таблица departments)
     * 
     * @return object объект из таблицы departments
     * @param int $departmentid - id учебного подразделения, которое нужно получить
     */
    public function get_department_info($departmentid)
    {
        if ( ! is_numeric($departmentid) )
        {// неверный формат исходных данных
            return false;
        }
        return $this->dof->storage('departments')->get($departmentid);
    }
    
    /** Получить список всех элементов учебной программы (дисциплин)
     * для одной учебной программы
     * @return array - массив объектов из таблицы programmitems  
     * @param int $programid - id учебной программы (в таблице programs), по которой производится выборка
     * @param int $agenum - порядковый номер ступени обучения внутри учебной программы
     */
    public function get_programmitems_list($programmid, $agenum=0)
    {
        if ( ! is_numeric($programmid) OR ! is_numeric($agenum) )
        {// неверный формат исходных данных
            return false;
        }
        return $this->dof->storage('programmitems')->get_pitems_list($programmid, $agenum);
    }
    
    /** Распечатать таблицу с информацией об ученике, программе и подразделении
     * 
     * @return string html код таблицы или пустую строку
     * @param int $programmid - id учебной программы в таблице programms
     * @param int $studentid - id ученика в таблице persons
     */
    public function print_info_table($programsbcid)
    {
        // получаем id необходимые для получения остальных данных
        $navids = $this->get_navigation_ids($programsbcid);
        $table = new stdClass();
        // узнаем информацию об ученике
        if ( ! $student = $this->dof->storage('persons')->get_fullname($navids->studentid) )
        {//не получили данные студента
            return '';
        }
        $studentdata = array('<b>'.$this->dof->get_string('student', 'recordbook').'</b>', $student);
        // узнаем информацию об учебной программе
        if ( ! $programm = $this->get_programm_data($navids->programmid) )
        {//не получили программу обучения
            return '';
        }
        $departmentid   = $programm->departmentid;
        // заносим ее в массив
        $programmdata    = array('<b>'.$this->dof->get_string('name_title', 'recordbook').'</b>', $programm->name);
        // узнаем информацию о служебном подразделении
        if ( ! $department = $this->get_department_info($departmentid) )
        {//не получили информацию о подразделении, 
            //которое отвечает за реализацию программы
            return '';
        }
        // заносим ее в строку таблицы
        $departmentdata = array('<b>'.$this->dof->get_string('responsible_department', 'recordbook').'</b>', $department->name);
        // формируем структуру и свойства таблицы
        $table->tablealign = 'left';
        $table->data[] = $programmdata;
        $table->data[] = $departmentdata;
        $table->data[] = $studentdata;
        
        // выводим таблицу
        return $this->dof->modlib('widgets')->print_table($table, true);
    }
       
    /** Отображает таблицы с информацией о пройденных учеником 
     * предметах и оценками за них за все периоды
     * @return string html-код таблицы
     * @param int $programmid - id учебной программы в таблице programs
     * @param int $studentid - id ученика в таблице persons
     * @param int $ageid - id учебного периода в таблице ages
     * @param int $clientid - id клиента, отвечающего за ученика, из таблицы persons
     * @param int $programmsbcid - id подписки на учебную программу в таблице programmsbcs
     */
    public function print_full_progitemstable($programmsbcid)
    {
        $depid = optional_param('departmentid', 0, PARAM_INT);
        $addvars = array();
        $addvars['departmentid'] = $depid;
        $navids = $this->get_navigation_ids($programmsbcid);
        
        if ( ! $program = $this->dof->storage('programms')->get($navids->programmid) )
        {// если не удалось вывести учебную программу - сообщим об этом
            print_error($this->dof->get_string('no_programm_in_base', 'recordbook', $navids->programmid), '',
                        $this->dof->url_im('recordbook', 'index.php',$addvars));
        }
        if ( ! $this->dof->storage('persons')->is_exists($navids->studentid) )
        {// если ученика нет в базе - то выведем сообщение
            print_error($this->dof->get_string('no_student', 'recordbook', $navids->studentid), '',
                        $this->dof->url_im('recordbook', 'index.php',$addvars));
        }
        // получаем список учебных программ
        // узнаем, сколько семестров проходила данная учебная программа
        $history = $this->dof->storage('learninghistory')->get_subscribe_ages($programmsbcid);
        $rez = '';
        if ( is_array($history) )
        {
            foreach ($history as $episode)
            {
                $rez .= $this->print_oneagenum_progitemstable($navids->programmid, $navids->studentid,
                         $episode->ageid, $navids->clientid, $episode->agenum,$programmsbcid).'<br />';
            }
        }
        // Запланированные дисциплины
        // Узнаем, есть ли учебные планы, отобразим их
        $agenum = $this->dof->storage('learningplan')->get_current_agenum_type_typeid('programmsbc', $programmsbcid);
        if ( $agenum AND ($agenum + 1) <= $program->agenums )
        { // Если есть следующая параллель, отобразим планы
            $rez .= $this->dof->modlib('widgets')->print_heading(
                    $this->dof->get_string('planned_list', 'recordbook'), '', 2, 'main', true);
            for ( $agen = $agenum + 1; $agen <= $program->agenums; $agen++ )
            {
                $rez .= $this->print_oneagenum_plannedtable($programmsbcid, $agen);
            }
        }
        if ( ! $rez )
        {// если не найдено ни одно учебного периода - то сообщим об этом
            $rez .= '<p align="center">( <i>'.$this->dof->get_string('no_active_contract', 'recordbook').'</i> )</p>';
        }
        return $rez;
    }
    
    /** Печать таблицы "Перезачтённые дисциплины"
     * 
     * @param int $programmsbcid
     * @return string
     */
    public function print_reoffset_disciplines($programmsbcid)
    {
        $depid = optional_param('departmentid', 0, PARAM_INT);
        // Получим studentid и programmid
        $navids = $this->get_navigation_ids($programmsbcid);
        $navids->studentid;
        $navids->programmid;
        
        $addvars = array();
        $addvars['departmentid'] = $depid;
        // создаем шапку таблицы
        $titletable = new stdClass();
        $titletable->width = '60%';
        $titletable->align = array('center');
        // основным заголовком таблицы будет являтся название семестра
        // выводим основное содержимое
        $table = new stdClass(); 
        $table->width = '60%';
        // добавляем заголовки таблицы
        $table->head = array ($this->dof->get_string('discipline', 'recordbook'),
                           $this->dof->get_string('total_grade', 'recordbook'),
                           $this->dof->get_string('hours_on_plan', 'recordbook'));
                           //$this->dof->get_string('credits', 'recordbook'));
        // устанавливаем выравнивание
        $table->align = array('left', 'center', 'center', 'center');
        
        // получаем строки таблицы в нужном формате
        $strings = $this->get_reoffset_strings($programmsbcid, $navids->programmid, $navids->studentid);
        if ( ! empty($strings) AND is_array($strings) )
        {// выводим таблицу с предеметами
            $table->data = $strings;
            return $this->dof->modlib('widgets')->print_table($titletable, true).
                   $this->dof->modlib('widgets')->print_table($table, true);
        }else
        {// выводим сообщение о том, что в этом периоде изучаемых предметов нет
            $result = $this->dof->modlib('widgets')->print_table($titletable, true).
            '<div align="center">(<i>'.
            $this->dof->get_string('no_reoffset_subjects', 'recordbook').
            '</i>)</div>';
            return $result;
        }
    }
    
    /** Отображает таблицу с информацией о пройденных учеником 
     * предметах и оценками за них за один период
     * @param int $programmid - id учебной программы в таблице programms
     * @param int $studentid - id ученика в таблице persons
     * @param int $ageid - id учебного периода в таблице ages
     * @param int $clientid - id клиента, отвечающего за ученика, из таблицы persons
     * @param int $agenum - порядковый номер ступени обучения внутри очебной программы
     * @return string html-код таблицы
     */
    private function print_oneagenum_progitemstable($programmid, $studentid, $ageid, $clientid, $agenum,$programmsbcid=null)
    {
        $depid = optional_param('departmentid', 0, PARAM_INT);
        $addvars = array();
        $addvars['departmentid'] = $depid;
        // создаем шапку таблицы
        $titletable = new stdClass();
        $titletable->width = '60%';
        $titletable->align = array('center');
        // основным заголовком таблицы будет являтся название семестра
        $titletable->head   = array($this->dof->storage('ages')->get_field($ageid, 'name'));
        $titletable->data[] = array('<a href="'.$this->dof->url_im('recordbook', '/finalgrades.php?programmsbcid='.$programmsbcid.'&ageid='.$ageid,$addvars).'">'.
                             $this->dof->get_string('finalgrades', 'recordbook').
                             '</a>');
        // выводим основное содержимое
        $table = new stdClass(); 
        $table->width = '60%';
        // добавляем заголовки таблицы
        $table->head = array ($this->dof->get_string('discipline', 'recordbook'),
                           $this->dof->get_string('total_grade', 'recordbook'),
                           $this->dof->get_string('hours_on_plan', 'recordbook'));
                           //$this->dof->get_string('credits', 'recordbook'));
        // устанавливаем выравнивание
        $table->align = array('left', 'center', 'center', 'center');
        
        // получаем строки таблицы в нужном формате
        $strings = $this->get_age_strings($programmid, $studentid, $ageid, $agenum);
        if ( ! empty($strings) AND is_array($strings) )
        {// выводим таблицу с предеметами
            $table->data = $strings;
            return $this->dof->modlib('widgets')->print_table($titletable, true).
                   $this->dof->modlib('widgets')->print_table($table, true);
        }else
        {// выводим сообщение о том, что в этом периоде изучаемых предметов нет
            $result = $this->dof->modlib('widgets')->print_table($titletable, true).
            '<div align="center">(<i>'.
            $this->dof->get_string('no_subjects_in_this_period', 'recordbook').
            '</i>)</div>';
            return $result;
        }
    }
    
    /** Отображает таблицу с информацией о запланированных дисциплинах
     * 
     * @param int $programmsbcid - id подписки в таблице programmsbcs
     * @param int $agenum - порядковый номер ступени обучения внутри очебной программы
     * @return string html-код таблицы
     */
    private function print_oneagenum_plannedtable($programmsbcid, $agenum)
    {
        $depid = optional_param('departmentid', 0, PARAM_INT);
        $addvars = array();
        $addvars['departmentid'] = $depid;
        // выводим основное содержимое
        $table = new stdClass(); 
        $table->width = '60%';
        // добавляем заголовки таблицы
        $discipline = new html_table_cell($this->dof->get_string('discipline', 'recordbook'));
        $discipline->header = true;
        $hours = new html_table_cell($this->dof->get_string('hours_on_plan', 'recordbook'));
        $hours->header = true;
        $head = new html_table_cell($agenum . ' ' . strtolower($this->dof->get_string('agenum', 'recordbook')));
        $head->colspan = 2;
        $head->style = 'text-align: center';
        $table->head = array($head);
        $table->data = array();
        $table->data[] = array($discipline, $hours);
        
        // устанавливаем выравнивание
        $table->align = array('left', 'center');
        $table->size = array('40%', '20%');
        
        // получаем строки таблицы в нужном формате
        $strings = $this->get_planned_strings($programmsbcid, $agenum);
        if ( ! empty($strings) AND is_array($strings) )
        {// выводим таблицу с предеметами
            $table->data = array_merge($table->data, $strings);
            return $this->dof->modlib('widgets')->print_table($table, true);
        } else
        {// выводим сообщение о том, что в этом периоде запланированных предметов нет
            $result = $this->dof->modlib('widgets')->print_table($table, true).
            '<div align="center">(<i>'.
            $this->dof->get_string('no_planned_in_this_period', 'recordbook').
            '</i>)</div>';
            return $result;
        }
    }
    
    /** Отображает таблицу с информацией об академичекой разнице
     * 
     * @param int $programmsbcid - id подписки в таблице programmsbcs
     * @return string html-код таблицы
     */
    public function print_academicdebts($programmsbcid)
    {
        $depid = optional_param('departmentid', 0, PARAM_INT);
        $addvars = array();
        $addvars['departmentid'] = $depid;
        // выводим основное содержимое
        $table = new stdClass(); 
        $table->width = '60%';
        // добавляем заголовки таблицы
        $discipline = new html_table_cell($this->dof->get_string('discipline', 'recordbook'));
        $discipline->header = true;
        $grade = new html_table_cell($this->dof->get_string('total_grade', 'recordbook'));
        $grade->header = true;
        $hours = new html_table_cell($this->dof->get_string('hours_on_plan', 'recordbook'));
        $hours->header = true;
        $head = new html_table_cell($this->dof->get_string('academicdebts', 'recordbook'));
        $head->colspan = 3;
        $head->style = 'text-align: center';
        $table->head = array($discipline, $grade, $hours);
        $table->data = array();
        
        // устанавливаем выравнивание
        $table->align = array('left', 'center', 'center');
        
        // получаем строки таблицы в нужном формате
        $strings = $this->get_academicdebts_strings($programmsbcid);
        if ( ! empty($strings) AND is_array($strings) )
        {// выводим таблицу с предеметами
            $table->data = array_merge($table->data, $strings);
            return $this->dof->modlib('widgets')->print_table($table, true);
        } else
        {// выводим сообщение о том, что в этом периоде запланированных предметов нет
            $result = $this->dof->modlib('widgets')->print_table($table, true).
            '<div align="center">(<i>'.
            $this->dof->get_string('noacademicdebts', 'recordbook').
            '</i>)</div>';
            return $result;
        }
    }
    
    /** Получить массив строк таблицы для академической разницы
     * 
     * @param int $programmsbcid - id подписки в таблице programmsbcs
     * @return mixed array - массив данных для вывода в таблицу
     * или bool false
     */
    private function get_academicdebts_strings($programmsbcid)
    {
        $depid = optional_param('departmentid', 0, PARAM_INT);
        $addvars = array();
        $addvars['departmentid'] = $depid;
        
        $result = array();
        // Извлекаем из базы всю академическую разницу
        $debts = $this->dof->storage('cpassed')->get_academic_debts($programmsbcid);
        // .. и актуальное состояние cpassed по ним
        $actualdebts = $this->dof->storage('cpassed')->get_actual_academicdebts_cpassed($debts);
        unset($debts);
        $pitemsdebts = array();
        foreach ( $actualdebts as $cpassed )
        {
            if ( !isset($pitemsdebts[$cpassed->programmitemid]) )
            {
                $pitemsdebts[$cpassed->programmitemid] = $cpassed;
            }
        }

        // Для просмотра оценки
        $add = array();
        $add['programmsbcid'] = $this->prsbcid;
        $add['departmentid'] = $depid;
        if ( is_array($pitemsdebts) )
        {// Нет долгов по предметам
            foreach ( $pitemsdebts as $pid => $cpassed )
            {// Перебираем актуальные записи и ищем оценки';
                $pitem = $this->dof->storage('programmitems')->get($pid, 'id,name');
                $fullstring = '<a href="'.$this->dof->url_im('programmitems', '/view.php', 
                               array_merge(array('pitemid' => $pitem->id),$addvars)).
                              '">'.$pitem->name.'</a>';
                $add['departmentid'] = $depid;
                // Дополнительный параметр для истории оценки
                if ( empty($cpassed->grade) )
                {
                    $cpassed->grade = '-';
                }
                $add['cpassed'] = $cpassed->id;
                $grade = '<a href="'.$this->dof->url_im('recordbook','/program.php',$add).'#history" 
                                                                            title="'.$this->dof->get_string('hystori_view', 'recordbook').'">'.$cpassed->grade.'</a>';
                
                $result[] = array($fullstring, $grade, $this->get_progitem_hours($pitem->id));
            }
        }
        return $result;
    }
    
    /** Получить массив строк таблицы для отдельного периода
     * 
     * @return mixed array - массив данных для вывода в таблицу
     * или bool false
     * @param int $programmid - id учебной программы в таблице programs
     * @param int $studentid - id ученикав таблице persons
     * @param int $ageid - id учебного периода в таблице ages
     * @param int $agenum - порядковый номер ступени обучения внутри очебной программы
     */
    private function get_age_strings($programmid, $studentid, $ageid, $agenum)
    {
//        $depid = optional_param('departmentid', 0, PARAM_INT);
//        $addvars = array();
//        $addvars['departmentid'] = $depid;
        // данные для просмотра истории оценки
        $add = array();
        $add['programmsbcid'] = $this->prsbcid;
//        $add['departmentid'] = $depid;
        
        $result = array();
        // извлекаем из базы все элементы учебной программы
        $pitems  = $this->get_programmitems_list($programmid, $agenum);
        $result += $this->get_age_strings_result($pitems, $studentid, $ageid, $add);
        $pitems  = $this->get_programmitems_list($programmid, 0);
        $result += $this->get_age_strings_result($pitems, $studentid, $ageid, $add);
        return $result;
    }
    
    private function get_age_strings_result($pitems, $studentid, $ageid, $add)
    {
        global $addvars;
        $result = array();
        if ( is_array($pitems) )
        {// нет элементов учебной программы в этом семестре
            foreach ($pitems as $pitem)
            {// Перебираем учебные программы и ищем оценки;
                $data = new stdClass();
                $data->programmitemid = $pitem->id;
                $data->studentid = $studentid;
                $data->ageid = $ageid;
                $data->status =  array('active','completed','failed','reoffset','suspend');
                if ( $cpassed = $this->dof->storage('cpassed')->get_listing($data) )
                {
                    $cpassed = $this->dof->storage('cpassed')->get_norepeatid_cpassed($cpassed);
                    // Найдена подписка на дисциплину - выведем ссылку на нее
                    // Устанавливаем ссылку на страницу дисциплины
                    foreach ( $cpassed as $cpass )
                    {
                        // История обучения по дисциплине
                        $options = array(
                            'programmsbcid' => $cpass->programmsbcid,
                            'programmitemid' => $cpass->programmitemid,
                        );
                        $url = $this->dof->url_im('journal', '/cphistory.php', $addvars + $options);
                        $options = array(
                            'alt' => $this->dof->get_string('cphistory', 'journal'),
                            'title' => $this->dof->get_string('cphistory', 'journal'),
                        );
                        $cphistorylink = $this->dof->modlib('ig')->icon_plugin('history', 'im', 'journal', $url, $options);
                        $fullstring = $cphistorylink . ' <a href="'.$this->dof->url_im('recordbook', '/discipline.php', 
                                       array_merge(array('cpassedid' => $cpass->id),$addvars)).
                                      '">'.$pitem->name.'</a>';
                        $grade = '-';
                        if ( $cpass AND isset($cpass->grade) AND (bool)$cpass->grade )//AND isset($cpassed->credit))
                        {// Это пройденная дисциплина
                            // Дополнительный параметр для истории оценки
                            $grade = $cpass->grade;
                        }
                        $add['cpassed'] = $cpass->id;
                        $cpass->grade = '<a href="'.$this->dof->url_im('recordbook','/program.php',$add).'#history" 
                            title="'.$this->dof->get_string('hystori_view', 'recordbook').'">'.$grade.'</a>';
                        $result[] = array($fullstring, $cpass->grade, $this->get_progitem_hours($pitem->id));
                    }
                }else
                {// подписка на дисциплину не найдена - не записываем строку в итоговый результат
                    continue;
                }
            }
        }
        return $result;
    }

    /** Получить массив строк таблицы для запланированных дисциплин
     * 
     * @param int $programmsbcid - id подписки в таблице programmsbcs
     * @param int $agenum - номер параллели
     * @return mixed array - массив данных для вывода в таблицу
     * или bool false
     */
    private function get_planned_strings($programmsbcid, $agenum)
    {
        $depid = optional_param('departmentid', 0, PARAM_INT);
        $addvars = array();
        $addvars['departmentid'] = $depid;
        
        $result = array();
        // извлекаем из базы все элементы учебной программы
        $planned = $this->dof->storage('learningplan')->get_planned_pitems('programmsbc', $programmsbcid, $agenum);
        if ( is_array($planned) )
        {// нет элементов учебной программы в этом семестре
            foreach ($planned as $pitem)
            {// перебираем учебные программы и ищем оценки';
                $fullstring = '<a href="'.$this->dof->url_im('programmitems', '/view.php', 
                               array_merge(array('pitemid' => $pitem->id),$addvars)).
                              '">'.$pitem->name.'</a>';
                $result[] = array($fullstring, $this->get_progitem_hours($pitem->id));
            }
        }
        return $result;
    }
    
    /** Получить массив строк таблицы для перезачтённых дисциплин
     * 
     * @param int $programmid - id учебной программы в таблице programs
     * @param int $studentid - id ученикав таблице persons
     * @return mixed array - массив данных для вывода в таблицу
     * или bool false
     */
    private function get_reoffset_strings($programmsbcid, $programmid, $studentid)
    {
        $depid = optional_param('departmentid', 0, PARAM_INT);
        $addvars = array();
        $addvars['departmentid'] = $depid;
        // данные для просмотра истории оценки
        $add = array();
        $add['programmsbcid'] = $this->prsbcid;
        $add['departmentid'] = $depid;
        
        $result = array();
        // извлекаем из базы все элементы учебной программы
        $pitems = $this->dof->storage('programmitems')->get_pitems_list($programmid, false);
        if ( is_array($pitems) )
        {// нет элементов учебной программы в этом семестре
            foreach ($pitems as $pitem)
            {// перебираем учебные программы и ищем оценки';
                $data = new stdClass();
                $data->programmsbcid  = $programmsbcid;
                $data->programmitemid = $pitem->id;
                $data->studentid      = $studentid;
                $data->status         = array('reoffset');
                $data->ageid          = 0;
                
                // получили все оценки, которые и были пересданы.
                if ( $cpassed = $this->dof->storage('cpassed')->get_cpasseds_reoffset($data) )
                {
                    // Оставим только последнюю, актуальную пересдачу
                    $cpass = $this->dof->storage('cpassed')->get_actual_reoffset_cpassed($cpassed);
                    // найдена подписка на дисциплину - выведем ссылку на нее
                    // устанавливаем ссылку на страницу дисциплины
//                    foreach ( $cpassed as $cpass )
//                    {
                    $fullstring = '<a href="'.$this->dof->url_im('recordbook', '/discipline.php', 
                                   array_merge(array('cpassedid' => $cpass->id),$addvars)).
                                  '">'.$pitem->name.'</a>';
                    if ( $cpass AND isset($cpass->grade) AND (bool)$cpass->grade )//AND isset($cpassed->credit))
                    {// это пройденная дисциплина
                        // дополнитеоьный параметр для истории оценки
                        $add['cpassed'] = $cpass->id;
                        $cpass->grade = '<a href="'.$this->dof->url_im('recordbook','/program.php',$add).'#history" 
                                                                                    title="'.$this->dof->get_string('hystori_view', 'recordbook').'">'.$cpass->grade.'</a>';
                        $result[] = array($fullstring, $cpass->grade, $this->get_progitem_hours($pitem->id));
                    }
                    else
                    {// за эту дисциплину еще нет ни оценки ни кредитов
                        $add['cpassed'] = $cpass->id;
                        $cpass->grade = '<a href="'.$this->dof->url_im('recordbook','/program.php',$add).'#history" 
                                                                                    title="'.$this->dof->get_string('hystori_view', 'recordbook').'">-</a>';
                        $result[] = array($fullstring, $cpass->grade, $this->get_progitem_hours($pitem->id));
                        //$result[] = array($fullstring, '-', $this->get_progitem_hours($pitem->id));
                    }
//                    }
                }else
                {// подписка на дисциплину не найдена - не записываем строку в итоговый результат
                    continue;
                }
            }
        }
        return $result;
    }
    
    /** Получить запись из таблицы cpassed по элементу учебной программы 
     * и id периода
     * @return object запись из таблицы cpassed, или false, если ничего не нашлось
     * @param int $pitemid - id элемента учебной программы в таблице programmitems
     * @param int $ageid - id учебного периода в таблице ages
     * @param int $studentid - id ученика в таблице persons
     */
    private function get_cpassed_by_pitem($pitemid, $ageid, $studentid)
    {
        $cstreams = $this->dof->storage('cstreams')->get_records(array
            ('ageid'=>$ageid,'programmitemid'=>$pitemid,'status'=>array('active','suspend','completed')));
        if ( ! $cstreams )
        {// не удалось получить учебные потоки
            return false;
        }
        foreach ( $cstreams as $cstream )
        {//перебираем все учебные потоки, и ищем нужную подписку:
            //print_object($cstream);
            $cpassed = $this->dof->storage('cpassed')->
                get_listing(array('cstreamid'=>$cstream->id, 'studentid'=>$studentid));
            if ( $cpassed )
            {// возвращаем последнюю найденную подписку
                // @todo сделать возможным просмотр истории обучения по дисциплине
                krsort($cpassed);
                return current($cpassed);
            }
        }
        // ничего не найдено
        return false;
    }
    
    /**
     * Возвращает истину, если поток принадлежит 
     * к указанному учебному периоду
     * иначе возвращает ложь 
     * @param int $cstreamid - id потока 
     * @param $programmsbcid - id подписки на программу
     * @return bool
     */
    private function is_stream_going($cstreamid, $programmsbcid)
    {
        //получаем последнюю запись из learninghistory
        $last = $this->dof->storage('learninghistory')->
                        get_actual_learning_data($programmsbcid);
        if ( ! $last )
        {//не получили запись
            return false;
        }
        //из нее берем ageid
        //и проверяем - переданный нам поток с таким же ageid или нет
        return $this->dof->storage('cstreams')->
            is_exists(array('id'=>(int)$cstreamid, 'ageid'=>(int)$last->ageid));
    }

    /** Возвращает количество часов отведенное на изучение дисциплины
     * 
     * @param int $programmitemid - id учебной программы в таблице programmitems
     * @return string - строку из БД или пустую строку
     */
    private function get_progitem_hours($programmitemid)
    {
        if ( $hours = $this->dof->storage('programmitems')->get_field($programmitemid,'hours') )
        {
            return ($hours);
        }
        return '';
    }

    /** Получить историю по пересдаче оценка
     * 
     * @param $cpassid - id cpass, на который надо историю
     * @param integer $depid - id записи из таблицы departments
     * @return $table -  таблицу с данными
     */
    public function show_history_cpass($cpassid, $depid)
    {   
        $cpass = $this->dof->storage('cpassed')->get($cpassid);
        // название предмета
        $name = $this->dof->storage('programmitems')->get_field($cpass->programmitemid,'name');
        // выберим все оценки этого ученика по этому предмету в этом периоде
        // в зависимости от сортировки покажем историю пересдач по программе или по программе и потоку
        $value = $this->dof->storage('config')->get_config_value('finalgrade', 'storage', 'cpassed', $depid);
        if ( $value == 2 )
        {// сортировка поп рограмме и потоку
            $select = " studentid={$cpass->studentid} AND cstreamid={$cpass->cstreamid} AND programmitemid={$cpass->programmitemid} AND 
                ageid={$cpass->ageid} AND status IN ('active','reoffset','completed','failed','suspend')";            
        }else 
        {
            $select = " studentid={$cpass->studentid} AND programmitemid={$cpass->programmitemid} AND 
                ageid={$cpass->ageid} AND status IN ('active','reoffset','completed','failed','suspend')";
        } 
        $mascpassed = $this->dof->storage('cpassed')->get_records_select($select,null,'enddate');
        // рисуем таблицу
        $table = new stdClass();
        $table->tablealign = "center";
        $table->cellpadding = 2;
        $table->cellspacing = 2;
        //$table->size = array ('100px','150px','150px','200px','150px','100px');
        $table->align = array ("center","center");
        // добавим якорь
        $pitem = '<a name="history"></a>';
        // шапка таблицы
        $pitem .= '<div style="text-align:center;font-weight:bolder;" >'.$this->dof->get_string('hystori_cpass', 'recordbook',$name).
                  '('  .$this->dof->storage('ages')->get_field($cpass->ageid, 'name').  ')</div>';
        // заголовок
        $table->head = array($this->dof->get_string('time', 'recordbook'),$this->dof->get_string('total_grade', 'recordbook'));
        // учитываем ВСЕ оценки и пересдачи и где небо сдачи
        foreach ( $mascpassed as $key=>$obj )
        {// заносим данные в таблицу
            if ( $obj->grade AND $key == $cpassid )
            {// выделим текущюю оценку 
                $obj->grade = '<span style="color:green;">'.$obj->grade.'</span>';
            }            
            $table->data[] = array(dof_userdate($obj->enddate,"%m.%d.%y  %H:%M"),$obj->grade );  
        } 
       
        return $pitem.'<br>'.$this->dof->modlib('widgets')->print_table($table,true);
    } 
}

/**
 * Класс для отображения информации по учебным дисциплинам
 */
class dof_im_recordbook_discipline
{
    /**
     * @var dof_control
     */
    protected $dof;
    
    /** Конструктор класса
     * @constructor
     * @param dof_control $dof
     */
    function __construct(dof_control $dof)
    {
        $this->dof = $dof;
    }
    /** Распечатать таблицу с краткой информацией по дисциплине
     * @return string html-код таблицы или пустую строку
     * @param int $cpassedid - id подписки на учебный поток в таблице cpassed
     */
    public function print_info_table($cpassedid)
    {
        global $CFG;
        $depid = optional_param('departmentid', 0, PARAM_INT);
        $addvars = array();
        $addvars['departmentid'] = $depid;
        $table = new stdClass();
        $table->tablealign = 'left';
        //получим подписку на дисциплину
        if ( ! $cpassed = $this->dof->storage('cpassed')->get($cpassedid) )
        {//не получили подписку на дисциплину';
            print_error($this->dof->get_string('no_cpassed', 'recordbook'), '', $this->dof->url_im('standard','',$addvars));
        }
        //получим учебную программу
        if ( ! $programmitem = $this->get_pitem_info($cpassed->programmitemid) )
        {//не получили учебную программу';
            print_error($this->dof->get_string('no_programm_in_base', 'recordbook', $cpassed->programmitemid), '', $this->dof->url_im('standard','',$addvars));
        }
        // получим подписку на учебную программу, и имя академической группы
        if ( ! $programmsbc = $this->dof->storage('programmsbcs')->get($cpassed->programmsbcid) )
        {// не получили подписку на учебную программу
            $agroup = null;
        }else
        {// нашли подписку на учебную программу, ищем академическую группу
            $agroup = $this->dof->storage('agroups')->get_field($programmsbc->agroupid, 'name');
        }
        // получим учебный поток и название подразделения
        if ( ! $cstream = $this->dof->storage('cstreams')->get($cpassed->cstreamid) )
        {// не получили учебный поток
            $deptname = null;
        }else
        {// не получили запись учебного подразделения
            $deptname = $this->dof->storage('departments')->get_field($cstream->departmentid, 'name');
        }
        // получим информацию об ученике
        if ( ! $studentfio = $this->dof->storage('persons')->get_fullname($cpassed->studentid) )
        {//ученик не найден';
            print_error($this->dof->get_string('no_student', 'recordbook', $cpassed->studentid), '', $this->dof->url_im('standard','',$addvars));
        }
        // получаем преподавателя
        if ( ! $cstream OR ! $teacherfio = $this->dof->storage('persons')->get_fullname($cstream->teacherid) )
        {// оповестим пользователя о том, что учитель не указан';
            $teacherfio = '<i>('.$this->dof->get_string('teacher_not_set', 'recordbook').')</i>';
        }
        $program = trim($this->dof->storage('programms')->get_field($programmitem->programmid, 'name'));
        
        // записываем в таблицу данные о дисциплине
        // ссылка на курс в moodle
        $cname = '';
        if ( isset($programmitem->mdlcourse) AND $this->dof->modlib('ama')->course(false)->is_course($programmitem->mdlcourse) )
        {
            $course = $this->dof->modlib('ama')->course($programmitem->mdlcourse)->get();
            $cname = "<a href = ".$CFG->wwwroot."/course/view.php?id=".$programmitem->mdlcourse." >".$course->fullname."</a>";
        }
        if ( $cname )
        {
            $table->data[] = array('<b>'.$this->dof->get_string('course_moodle', 'recordbook').'</b>', $cname);
        }
        $table->data[] = array('<b>'.$this->dof->get_string('name_title', 'recordbook').'</b>', $programmitem->name);
        if ( $deptname )
        {// записываем информацию об учебном подразделении, если она есть
            $table->data[] = array('<b>'.$this->dof->get_string('responsible_department', 'recordbook').'</b>', $deptname);
        }
        if ( $program )
        {// выводим информацию ою учебной программе, если она есть
            $table->data[] = array('<b>'.$this->dof->get_string('learning_program', 'recordbook').'</b>', $program);
        }
        if ( $agroup )
        {// записываем в таблицу название академической группы, если она есть
            $table->data[] = array('<b>'.$this->dof->get_string('agroup', 'recordbook').'</b>', $agroup);
        }
        //$table->data[] = array($this->dof->storage('ages')->
        //        get_field($this->dof->storage('ages')->get_next_ageid($programmsbc->agestartid, $programmsbc->agenum), 'name'));
        // даные о преподавателе
        $table->data[] = array('<b>'.$this->dof->get_string('teacher', 'recordbook').'</b>', $teacherfio);
        // данные об ученике
        $table->data[] = array('<b>'.$this->dof->get_string('student', 'recordbook').'</b>', $studentfio);
        // ссылка на cpassed
        if ( $this->dof->storage('cpassed')->is_access('view', $cpassedid) )
        {
            $info = "<a href =".$this->dof->url_im('cpassed','/view.php?cpassedid='.$cpassedid,$addvars).">".$this->dof->modlib('ig')->igs('view')."</a>";
            $table->data[] = array('<b>'.$this->dof->get_string('view_cpassed', 'recordbook').'</b>', $info);
        }             
        // выводим таблицу на экран
        return $this->dof->modlib('widgets')->print_table($table, true);
    }
    
    /** Получить все идентификаторы, 
     * необходимые для навигации по странице дисциплины (discipline.php)
     * 
     * @return object объект содержащий список id для навигации
     * @param int $cpassedid - id подписки на учебный поток в таблице cpassed
     */
    public function get_programmsbcid($cpassedid)
    {
        if ( ! $cpassed = $this->dof->storage('cpassed')->get($cpassedid) )
        {// не найдена запись с таким id в базе - не можем продолжать обработку
            print_error($this->dof->get_string('no_cpassed'), '', $this->dof->url_im('recordbook', 'index.php',$addvars));
        }
        // получаем id подписки на дисциплину в таблице programmsbcs
        return $cpassed->programmsbcid;
    }
    
    /** Выводит на экран таблицу с информацией по всем урокам
     * 
     * @return string|bool false в случае ошибки, или если все нормально 
     * html-код таблицы
     * @param int $cpassedid - id подписки на учебный поток в таблице cpassed
     */
    public function print_lessons_table($cpassedid)
    {
        $table = new stdClass();
        // формируем заголовок таблицы
        $table->head = 
                array($this->dof->get_string('date', 'recordbook'),
                      $this->dof->get_string('subject', 'recordbook'),
                      $this->dof->get_string('homework', 'recordbook'),
                      $this->dof->get_string('presence', 'recordbook'),
                      $this->dof->get_string('grade', 'recordbook'));
        $table->align = array('left', null, null,'center', 'center');
        $table->width = '100%';
        // получаем подписку на дисциплину
        $cpassed = $this->dof->storage('cpassed')->get($cpassedid);
        
        if ( ! $cpassed )
        {// недостаточно данных для построения таблицы
            return false;
        }
        if ( ! $table->data = $this->get_lessons_data($cpassed) )
        {// не удалось получить данные для таблицы
            return false;
        }
        // выводим таблицу с посещаемостью и оценками
        return $this->dof->modlib('widgets')->print_table($table,true);
    }
    
    /** Выводит таблицу одноклассников. 
     * Одноклассники определяются как пользователь, имеющие
     * в таблице plans общий cstreamid
     * @TODO найти способ не использовать глобальные переменные moodle
     * @return string html-код таблицы с одноклассниками
     * @param int $cpassedid - id подписки на учебный поток в таблице cpassed
     */
    public function print_classmates_table($cpassedid)
    {
        global $CFG;
        $table = new stdClass();
        // устанавливаем заголовок таблицы
        $table->head = array($this->dof->get_string('fio', 'recordbook'), 
                             $this->dof->get_string('actions', 'recordbook'));
        $table->width = '50%';
        $table->align = array(null, 'center');
        // получаем список одноклассников
        if ( ! $classmates = $this->get_classmates_list($cpassedid) )
        {//не нашли список одноклассников
            return false;
        }
        foreach ($classmates as $classmate )
        {// формируем строку таблицы для отправки сообщения
            // @todo можeт не париться, и просто сделать ФИО ссылками для отправки сообщений?
            $messagelink = '';
            if ( $classmate->mdluser )
            {// если пользователь зарегестрирован в moodle - покажем ссылку на огтправку сообщений
                $messagelink = '<a href="'.$CFG->wwwroot.'/message/discussion.php?id='.$classmate->mdluser.
                // это всего лишь необходимый javascript для открытия нового окна переписки
                '"onclick="this.target=\'message_'.$classmate->mdluser.'\';return openpopup(\'/message/discussion.php?id='.
                $classmate->mdluser.'\', \'message_'.$classmate->mdluser.
                '\', \'menubar=0,location=0,scrollbars,status,resizable,width=400,height=500\', 0);">'.
                $this->dof->get_string('send_message', 'recordbook').'</a>';
            }
            if ( $classmate->id <> $this->dof->storage('cpassed')->get_field($cpassedid,'studentid') )
            {// выведем всех одноклассников, кроме него самого
                $table->data[] = array($classmate->lastname.' '.$classmate->firstname, $messagelink);
            }
        }
        return $this->dof->modlib('widgets')->print_table($table, true);
    }
    
    /** Получить информацию по элементу учебной программы
     * @return bool|object объект из таблицы programmitems или false если ничего не нашлось
     * @param int $pitemid - id элемента учебной программы из таблицы programmitems
     */
    private function get_pitem_info($pitemid)
    {
        return $this->dof->storage('programmitems')->get($pitemid);
    }
    
    /** Получить список одноклассников
     * @return array массив учеников из таблицы persons
     * @param int $cpassedid - id подписки на учебный поток в таблице cpassed
     */
    private function get_classmates_list($cpassedid)
    {
        $result = array();
        // получаем подписку ученика
        $cpassed = $this->dof->storage('cpassed')->get($cpassedid);
        if ( ! $cpassed )
        {// не получили подписку
            return false;
        }
        
        // определяем по ней учебный поток, и по нему выбираем всех одноклассников
        $cpasseds = $this->dof->storage('cpassed')->get_records(array('cstreamid'=>$cpassed->cstreamid, 
                           'status'=>array('plan','active','suspend','completed','failed','suspend')));
        if ( ! $cpasseds )
        {// не получили список учеников
            return false;
        }
        foreach ( $cpasseds as $cpassedelm )
        {// для каждого элемента - добавляем ученика в массив
            $result[$cpassedelm->studentid] = $this->dof->storage('persons')->get($cpassedelm->studentid);
        }
        return $result;
    }
    
    /** Получить массив строк для таблицы о посещаемости
     * @return array - массив строк для будущей таблицы
     * @param object $cpassed - обьект, содержащий все поля из таблицы cpassed
     */
    private function get_lessons_data($cpassed)
    {
        $result = array();
        $plans = $this->dof->storage('plans')->
                 sort_checkpoints_and_events($cpassed->cstreamid, 
                                            array('active', 'fixed','checked','completed'),
                                            array('plan', 'completed'), 1);
        if ( ! $plans )
        {// нет данных для таблицы
            return false;
        }
        // формируем строки таблицы по одной
        foreach ( $plans as $date=>$plan )
        {
            $array = array();
            $array = $this->create_lesson_string($cpassed->studentid, $plan, $date, $cpassed->id);
            if ( $array[2] != '&nbsp;')
            {// где нет отметки о посещаемости - не выводим
                $result[] = $array;
            }
        }
        return $result;
    }
    
    /** Получить строку с данными о посещаемости
     * @return array массив, в ячейках которого хранятся данные о посещаемости
     * @param int    $studentid - id ученика в таблице persons 
     * @param object $plan - объект с данными об элементе тематического планирования из таблицы plans
     * @param int    $date - дата события
     * @param int    $cpassedid - id подписки на дисциплтну в таблице cpassed
     */
    private function create_lesson_string($studentid, $plan, $date, $cpassedid)
    {
        $string = array();
        // извлекаем данные о посещаемости
        if ( isset($plan->event) AND is_object($plan->event) AND isset($plan->event->id) )
        {// дате планирования соответствует событие, значит есть статус посещаемости
            $presresult = $this->dof->storage('schpresences')->get_present_status($studentid, $plan->event->id);
            if ( $presresult === '1' )
            {// ученик присутствовал
                $presence = '<div style=" color: green">'.$this->dof->get_string('yes', 'recordbook').'</div>';
            }elseif( $presresult === '0' )
            {// ученик отсутствовал
                $presence = '<div style=" color: red">'.$this->dof->get_string('no', 'recordbook').'</div>';
            }else
            {// нет данных о посещаемости
                $presence = '&nbsp;';
            }
            // не подсвечиваем название КТ - это обычный урок
            $lightbegin = '';
            $lightent   = '';
        }else
        {// если это четвертная оценка, то не выводим данных о посещаемости, 
            $presence = '-';
            // и подсветим название темы
            $lightbegin = '<b>';
            $lightent   = '</b>';
        }
        // извлекаем оценки
        $gradeobj = $this->dof->storage('cpgrades')->get_grade_student_cpassed($cpassedid, $plan->id);
        if ( $gradeobj )
        {
            $grade = $gradeobj->grade;
        }else
        {// если оценки нет, то выводим пробел
            $grade = '&nbsp;';
        }
        
        // формируем и помещаем в массив дату события
        $string[] = $lightbegin.dof_userdate($date,'%d.%m.%y').$lightent;
        // записываем тему
        $string[] = $lightbegin.$plan->name.$lightent;
        // записываем дз
        $string[] = $lightbegin.$plan->homework.$lightent;
        // посещаемость
        $string[] = $presence;
        // оценку
        $string[] = $lightbegin.$grade.$lightent;

        return $string;
    }
}


/**
 * Класс отображения дневника учащегося
 */
class dof_im_recordbook_recordbook
{
    /**
     * Объект деканата для доступа к общим методам
     * @var dof_control
     */
    protected $dof;
    
    /**
     * Временная зона пользователя
     * 
     * @var float
     */
    protected $usertimezone;
    
    /**
     * Подписки на учебные программы
     * 
     * @var array - Массив подписок
     */
    protected $cpasseds = [];
    
    /**
     * Конструкотр
     * 
     * @param dof_control $dof - Объект деканата для доступа к общим методам
     * 
     * @return void
     */
    public function __construct(dof_control $dof)
    {
        $this->dof = $dof;
        $this->usertimezone = $this->dof->storage('persons')->get_usertimezone_as_number();
    }
    
    /** 
     * Отобразить дневник на неделю
     * 
     * @param int $programsbcid - ID подписки на программу
     * @param int $time - Метка времени выбранной недели
     * 
     * @param boolean $flag - флаг(хук) для вывода уроков за МЕСЯЦ для ученика
     *                        (эти уроки буудт выделены в календаре журнала)
     *                        
     * @return string - HTML-код дневника на неделю
     */
    public function display($programsbcid, $time, $flag = false)
    {
        // Получение данных по подписке
        $sbcdata = $this->get_programbc_data($programsbcid);
        
        // Проверка полученных данных
        if ( ! $this->dof->storage('persons')->is_exists($sbcdata->studentid) ||
             ! $this->dof->storage('ages')->is_exists($sbcdata->ageid) )
        {
            // Добавление ошибки
            $this->dof->messages->add($this->dof->get_string('no_data', 'recordbook'), 'error');
            return '';
        }
    
        // Получение подписок на учебные процессы
        $params = [];
        $params['status'] = array_keys($this->dof->workflow('cpassed')->get_meta_list('real'));
        $params['programmsbcid'] = $programsbcid;
        $this->cpasseds = $this->dof->storage('cpassed')->get_records($params);
        
        // Хук для вывода уроков за месяЦ(показ в журнаеле/календаре)
        if ( $flag )
        {// вывод всех занятия за месяц(переданный параметр $time)
            return $this->get_day_lessons($time, $flag);
        }
    
        // Получить начало недели
        $timebegin = $this->get_mondaytime($time);
        
        // Сформировать данные для шаблона
        $template_data = new stdClass();
        // Заголовки столбцов
        $template_data = $this->get_title_recordbook();
        
        $template_data->first_course_string = [];
        // Получить уроки за текущую неделю
        $days = $this->get_week_lessons($timebegin, $sbcdata->studentid);

        $i = 1;
        foreach ( $days as $noon => $day )
        {// Добавление информации по каждому дню
            $tempday = new stdClass();
            $tempday->style = '#FFF';
            if ( $i % 2 )
            {
                $tempday->style = '#DDD';
            }

            // Назваине дня недели
            $tempday->weekday         = $this->get_weekday_name($noon);
            // Номер дня недели
            $tempday->weekdaynum      = $i;
            // Массив с данными об уроках
            $tempday->course_string   = $this->get_day_lessons_string($day, $sbcdata->studentid, $tempday->style);
            // Число уроков на день
            $tempday->numberofcourses = count($tempday->course_string) + 1;
            if($i > 1) {
                $tempday->separate = '<tr class="dof_recordbook_separate_line">
                                        <td colspan="8"><hr></td>
                                    </tr>';
            }
            // Добавление дня в массив
            $template_data->first_course_string[] = $tempday;
            $i++;
        }
        
        // Добавление данных в шаблон
        $templater_package = $this->dof->modlib('templater')->
            template('im', 'recordbook', $template_data, 'recordbook');
        // Отображение дневника
        return $templater_package->get_file('html');
    }
    
    /**
     * Вернуть строки заголовков столбцов дневника
     *
     * @return stdClass - Заголовки столбцов
     */
    private function get_title_recordbook()
    {
        $result = new stdClass;
        $result->titledate     = '<div align="center">'.
            $this->dof->get_string('day', 'recordbook').'<br/>'.
            $this->dof->get_string('and', 'recordbook').'<br/>'.
            $this->dof->get_string('daynumber', 'recordbook').
            '</div>';
            $result->titlecourses  = $this->dof->get_string('subjects', 'recordbook');
            $result->titletime     = $this->dof->get_string('time', 'recordbook');
            $result->titletheme    = $this->dof->get_string('theme', 'recordbook');
            $result->titlehomework = $this->dof->get_string('homework_next_lesson', 'recordbook');
            $result->titletimework = $this->dof->get_string('timework', 'recordbook');
            $result->titlegrade    = $this->dof->get_string('grade', 'recordbook');
            $result->titleform     = $this->dof->get_string('form_lesson', 'recordbook');
    
            return $result;
    }
    
    /** 
     * Получить начало текущей недели
     *
     * @param object $time - Метка времени текущей недели
     *
     * @return int - Метка времени начала недели
     */
    private function get_mondaytime($time)
    {
        $daynumber = dof_userdate($time, '%w' , $this->usertimezone, false);
        if ( $daynumber == '0' )
        {// Воскресенье
            $daynumber = 7;
        }
        $splittime = dof_usergetdate($time, $this->usertimezone);
    
        // Получить метку начала недели по временной зоне пользователя
        return dof_make_timestamp($splittime['year'], $splittime['mon'], $splittime['mday'] - $daynumber + 1, 12, 0, 0);
    }
    
    /**
     * Вернуть название месяца строкой
     * 
     * @param int $time - Временная метка начала недели
     * 
     * @return string - Заголовок для начала недели
     */
    private function get_title_month($time)
    {
        return $this->dof->
            get_string('month', 'recordbook').': '.dof_userdate((int)$time,'%B', $this->usertimezone);
    }
    
    /**
     * Возвращает название дня недели
     * 
     * @param int $noon - Метка времени дня
     * 
     * @return string - Заголовок для начала недели
     */
    private function get_weekday_name($noon)
    {
        $datename = dof_userdate((int)$noon, '%A ', $this->usertimezone);
        $date = dof_userdate((int)$noon, '%d/%m', $this->usertimezone);

        return dof_html_writer::div(dof_html_writer::span($datename).$date, 'dof_vertical_text');
    }
    
    /**
     * Получить все уроки за неделю
     *
     * Группировка по дням
     *
     * @param integer $time - Время начала недели
     * @param object $studentid - ID ученика для которого будут извлекаться уроки и посещаемость
     *
     * @return массив объектов, каждый из которых содержит информацию
     */
    private function get_week_lessons($time, $studentid)
    {
        // Нормализация
        $time = (int)$time;
        $studentid = (int)$studentid;
    
        // Создание массива занятий на неделю
        $days = [];
        for ( $i = 0; $i < 7; $i++ )
        {
            // Получение занятий на день
            $lessons = $this->get_day_lessons($time);
            if ( ! is_array($lessons) )
            {// Занятий нет
                $lessons = [];
            }
            $days[$time] = $lessons;
    
            // Корректировка метки до следующего дня
            $nooninfo = dof_usergetdate($time, $this->usertimezone);
            $time = dof_make_timestamp($nooninfo['year'],$nooninfo['mon'],$nooninfo['mday'] + 1, 12, 0, 0);
        }
        return $days;
    }
    
    /**
     * Получить все уроки за день
     *
     * @param int $current - Метка времени текущего дня
     * @param bool $month - вычислить не за 1 день, а за месяц
     *          (использеутся для отмечания уроков в календаре, на которые есть уроки)
     *
     * @return array - Массив уроков
     */
    private function get_day_lessons($current, $month = false)
    {
        // Получение событий по подпискам на предмето-классы
        $events = [];
        foreach ( $this->cpasseds as $cpassed )
        {
            $options = [];
            // Ограничение по датам
            if ( $month )
            {
                $splittime = dof_usergetdate($current, $this->usertimezone);
                $options['timebegin'] = dof_make_timestamp($splittime['year'], $splittime['mon'], 1, 0, 0, 0);
                $options['timeend'] = dof_make_timestamp($splittime['year'], $splittime['mon'] + 1, 0, 23, 59, 59);
            } else
            {// Ограничение по дню
                $options['timebegin'] = $this->get_day_begin_time($current);
                $options['timeend'] = $this->get_day_end_time($current);
            }
            $options['status'] = ['plan' => 'plan', 'completed' => 'completed'];

            $cp_events = $this->dof->storage('schevents')->get_events_by_cpassed($cpassed, $options);
            
            // Добавление событий
            $events = $events + $cp_events;
        }
        
        // Сортировка событий по времени
        uasort($events, ['dof_im_recordbook_recordbook', 'sort_events_by_time']);
        
        return $events;
    }
    
    /**
     * Сортировака массива событий по дате начала
     * 
     * @param array $events - Массив событий
     */
    private static function sort_events_by_time($event1, $event2)
    {
        if ( $event1->date < $event2->date ) 
        {
            return -1;
        } elseif ( $event1->date > $event2->date ) 
        {
            return 1;
        } else 
        {
            return 0;
        }
    }
    
    /** 
     * Получить время начала дня (00:00:00)
     * 
     * @param int $time - Метка целевого дня
     * 
     * @return int - Метка времени на начало дня
     */
    private function get_day_begin_time($time)
    {
        $splittime = dof_usergetdate($time, $this->usertimezone);
        
        // Получить метку времени на начало дня
        return dof_make_timestamp($splittime['year'], $splittime['mon'], $splittime['mday'], 0, 0, 0);
    }
    
    /** 
     * Получить время окончания дня (23:59:59)
     * 
     * @param int $time - Метка целевого дня
     * 
     * @return int - Метка времени на конец дня
     */
    private function get_day_end_time($time)
    {
        $splittime = dof_usergetdate($time, $this->usertimezone);
        
        // Получить метку времени на конец дня
        return dof_make_timestamp($splittime['year'], $splittime['mon'], $splittime['mday'],23, 59, 59);
    }
    
    /**
     * Получить название предмета
     *
     * @param int $cstreamid - ID учебного процесса
     *
     * @return string - Название курса, к которому принадлежит дисциплина
     */
    private function get_coursename($cstreamid)
    {
        // Получение учебного процесса
        $cstream = $this->dof->storage('cstreams')->get($cstreamid);
        if ( ! $cstream )
        {// Учебный процесс не найден
            return '';
        }
        // Получение дисциплины
        $pitem = $this->dof->storage('programmitems')->get($cstream->programmitemid);
        if ( ! $cname = $pitem->name )
        {// Дисциплина не найдена
            return '';
        }
    
        // Формирование названия
        if ( isset($pitem->mdlcourse) AND $this->dof->modlib('ama')->course(false)->is_course($pitem->mdlcourse) )
        {// Указана привязка к курсу
            // Получение курса
            $course = $this->dof->modlib('ama')->course($pitem->mdlcourse)->get();
            // Название курса с ссылкой
            global $CFG;
            if ( empty($cname) )
            {// Если имя дисциплины не указано
                $cname = $course->shortname;
            }
            $cname = dof_html_writer::link($CFG->wwwroot.'/course/view.php?id='.$pitem->mdlcourse, $cname);
        } else
        {// Курс не привязан - отобразить название дисциплины
            $cname = $pitem->name;
        }
    
        return $cname;
    }
    
    /**
     * Сформировать данные урока
     *
     * @param object $event - Событие, по которому требуется собрать данные
     *
     * @return stdClass - Объект с данными для шаблонизатора
     */
    private function get_lesson_string($event, $studentid, $style = '#FFFFFF')
    {
        // Подготовка объекта для хранения данных
        $eventdata = new stdClass();
        $eventdata->course = '';
        $eventdata->homework = '';
        $eventdata->grade = '';
        $eventdata->time  = '';
        $eventdata->form  = '';
        $eventdata->timework = '';
        $eventdata->theme = '';
        $eventdata->comments = '';
        $eventdata->style = $style;
    
        if ( ! is_object($event) )
        {// Переданы неверные данные
            return $eventdata;
        }
        // Добавленеи данных о событии
        $eventdata->course = $this->get_coursename($event->cstreamid);
    
        // Добавление домашней работы по предмету
        $eventdata->homework = $this->get_homework($event->planid);
    
        if ( ! empty($event->planid) )
        {// Событие привязано к тематическому планированию
            // Добавление времени на домашнее задание
            $date = $this->dof->storage('plans')->get_field($event->planid,'homeworkhours');
            $eventdata->timework = $date/60 .$this->dof->modlib('ig')->igs('min').'. ';
            // Добавление оценок по событию
            $eventdata->grade = $this->get_grade_and_presence($studentid, $event->id, $event->planid, $event->cstreamid);
        }
        // Добавление времени начала события
        $eventdata->time  = dof_userdate($event->date, '%H:%M' , $this->usertimezone, false);
    
        // Добавление типа события
        if ( $event->form == 'internal' )
        {// Очное
            $eventdata->form  = $this->dof->get_string('internal', 'recordbook');
        } elseif ( $event->form == 'distantly' )
        {// Дистанционное
            $eventdata->form  = $this->dof->get_string('distantly', 'recordbook');
        } else
        {// Другое
            $eventdata->form = '';
        }
        // Добавление темы урока
        $eventdata->theme = $this->get_plan_theme($event->planid);
    
        // Добавление комментариев по событию
        $eventdata->comments = $this->get_comments($event->id, $studentid);

        // Добавление цвета столбца
        $eventdata->style = $style;
    
        return $eventdata;
    }
    
    /**
     * Получить тему урока
     *
     * @param int $planid - ID контрольной точки
     *
     * @return string - Тема плана
     */
    private function get_comments($eventid, $studentid)
    {
        $htmlcomments = '';
    
        // Получить посещаемость по уроку
        $params = [];
        $params['personid'] = $studentid;
        $params['eventid'] = $eventid;
        $presence = $this->dof->storage('schpresences')->get_records($params);
        if ( ! empty($presence) && $this->dof->plugin_exists('storage', 'comments') )
        {// Комментарий к посещаемости ученика на событии
            $presence = end($presence);
            // Получение списка комментариев
            $content = $this->dof->im('comments')->show_comments_list(
                'storage',
                'schpresences',
                $presence->id,
                'public',
                ['return_html' => true, 'disable_actions' => true]
            );
            if ( ! empty($content) )
            {// Комментарии есть
                $title = $this->dof->get_string('rb_recordbook_comment_title', 'recordbook');
                $label = dof_html_writer::span(
                    $this->dof->modlib('ig')->icon('feedback', NULL),
                    'btn button dof_button dof_recordbook_comment_modal_label dof_recordbook_has_comments'
                );
                $htmlcomments .= $this->dof->modlib('widgets')->modal($label, $content, $title);
            }
        }
        
        return $htmlcomments;
    }
    
    
    
    
    
    
    
    
    
    
    
    
    /**
     * Возвращает данные всех уроков за день
     * 
     * @param array - $day_lessons массив записей из schevents
     * 
     * @return array массив объектов
     */
    private function get_day_lessons_string($day_lessons, $studentid, $style = '#FFFFFF')
    {
        if ( ! is_array($day_lessons) OR empty($day_lessons) )
        {//неправильные входные данные
            //или нет уроков
            $day_lessons = array();
            for( $i=1; $i<=6; $i++)
            {//надо создать пустые строки для правильного отображения
    
                $day_lessons[] = $this->get_lesson_string('','',$style);
            }
            return $day_lessons;
        }
        $rez = array();
        foreach ( $day_lessons as $lesson )
        {//формируем строку уроков
            $present = $this->get_presence($studentid, $lesson->id);
            if ( $present === '1' OR $present === '0' OR $lesson->status == 'plan' )
            {// есть отметка о посещении - у ученика есть занятие
                $rez[] = $this->get_lesson_string($lesson, $studentid,$style);
            }
        }
        if ( ! $rez )
        {// такое возможно, когда есть урок, который мы не показываем
            for( $i=1; $i<=6; $i++)
            {//надо создать пустые строки для правильного отображения
                $rez[] =  $this->get_lesson_string('', '', $style = '#FFFFFF');
            }
        }
        return $rez;
    }
    
    /** Получить необходимые идентификаторы для навигации 
     * на странице дневника учащегося (recordbook.php)
     * 
     * @return object объект, каждое поле которого содержит 
     * необходимые id для навигации
     * @param int $programsbcid - id подписки на программу
     */
    private function get_programbc_data($programsbcid)
    {
        $result = new stdClass();
        // проверим, есть ли все необходимые идентификаторы
        if ( ! $programsbc = $this->dof->storage('programmsbcs')->get($programsbcid) )
        {// такой подписки на учебную программу нет в базе
            print_error($this->dof->get_string('no_program_subscribe', 'recordbook'), '', $this->dof->url_im('recordbook', 'index.php'));
        }
        if ( ! $contract = $this->dof->storage('contracts')->get($programsbc->contractid) )
        {// такой контракт не зарегестрирован
            print_error($this->dof->get_string('contract_not_found', 'recordbook'), '', $this->dof->url_im('recordbook', 'index.php'));
        }
        // узнаем id клиента 
        $result->clientid  = $this->dof->storage('persons')->get_by_moodleid_id();
        if ( ! $result->clientid )
        {// не получен id клиента
            print_error($this->dof->
                get_string('no_client_in_base', 'recordbook', $result->clientid), '', 
                $this->dof->url_im('standard'));
        }
        // узнаем id ученика
        $result->studentid = $contract->studentid;
        // получаем id периода
        $last = $this->dof->storage('learninghistory')->
                        get_actual_learning_data($programsbcid);
        $history = $this->dof->storage('learninghistory')->get_first_learning_data($programsbcid);
        $result->ageid = $history->ageid;
        if ( $last )
        {// если есть история берем ageid оттуда
            $result->ageid = $last->ageid;
        }
        
        return $result;
    }

    
    
    
    
    
    
    /**
     * Получить комментарии урока
     *
     *
     * @return string - Блок комментариев
     */
    private function get_plan_theme($planid)
    {
        // Получить контрольную точку
        if ( ! $planpoint = $this->dof->storage('plans')->get($planid) )
        {// Контрольная точка не найдена
            return '';
        }
    
        // Получение темы
        $theme = trim($planpoint->name);
    
        // Спойлер для слишком длинных тем
        $lengstr = mb_strlen($theme, 'utf-8');
        if ( $lengstr > 100 )
        {
            // Видимая часть
            $text1 = mb_substr($theme, 0, 100, 'utf-8');
            // Скрытая часть
            $text2 = '<span class="red '.$planid.'_Btn"><a href="" onClick="return dof_modlib_widgets_js_hide_show(\''.$planid.'_homework\',\''.$planid.'_Btn\');">...</a></span>';
            // Ссылка
            $text3 = '<span id="hideCont" class="'.$planid.'_homework">'.mb_substr($theme,100, $lengstr-100, 'utf-8').'</span>';
            $theme = $text1.$text3.$text2;
        }
        return $theme;
    }
    
    
    
   
    /** Возвращает ДЗ как строку или строку "не задано"
     * 
     * @param int $planid - id контролькой точки в таблице plans
     * @return string
     */
    private function get_homework($planid)
    {
        // получаем контрольную точку
        if ( ! $planpoint = $this->dof->storage('plans')->get($planid) )
        {// не указана контрольная точка или указана несуществующая точка - это ошибка
            // вернем строку "не задано"
            return $this->dof->get_string('no_homework', 'recordbook');
        }
        // получаем текст домашнего задания из контролькой точки
        if ( ! trim($planpoint->homework) )
        {// нет домашнего задания (нормальная ситуация)
            return $this->dof->get_string('no_homework', 'recordbook');
        }
        // напишем текст домашнего задания и рекомендуемое время для выполнения в часах
        // спрячем задание, которое больше 100 символов
        $lengstr = mb_strlen($planpoint->homework,'utf-8'); 
        if ( $lengstr > 100 )
        {
            // видимая часть
            $text1 = mb_substr($planpoint->homework,0,100,'utf-8');
            // скрытая часть
            $text2 = '<span class="red '.$planid.'_Btn"><a href="" onClick="return dof_modlib_widgets_js_hide_show(\''.$planid.'_homework\',\''.$planid.'_Btn\');">...</a></span>';
            // ссылка для нажатия
            $text3 = '<span id="hideCont" class="'.$planid.'_homework">'.mb_substr($planpoint->homework,100,$lengstr-100,'utf-8').'</span>';
            $planpoint->homework = $text1.$text3.$text2;
        }
        $homeworktext = $planpoint->homework;
        
        return $homeworktext;
    }
        
    /** Получает оценку и посещаемость за урок 
     * в пригодном для вывода на экран виде.
     * Вызывается из get_grade. 
     * @todo в будущем переделать для вывода нескольких оценок по одному ученику
     * @return string строка с оценкой и статусом присутствия ученика 
     * @param int $studentid - id ученика в таблице persons
     * @param int $eventid   - id учебного события в таблице schevents
     * @param int $planid - id контрольной точки тематического планирования в таблице plans
     * @param int $cstreamid - id учебного потока в таблице cstreams
     */
    private function get_grade_and_presence($studentid, $eventid, $planid, $cstreamid)
    {
        // объявляем итоговую переменную
        $grades = '';
        // Получаем оценку и статус посещаемости
        $presence = $this->get_presence($studentid, $eventid);
        $cpgrade  = $this->get_grade($cstreamid, $planid);
        // выведем отметку
        if ( $cpgrade AND is_object($cpgrade) )
        {// если оценка за эту дату есть - выводим ее
            $grades = $cpgrade->grade;
            if ( $presence === '0' )
            {// если ученик отсутствовал на занятии - то поставим "н"
                $grades .= '('.$this->dof->get_string('away_n_small', 'recordbook').')';
            }
        }else
        {//оценки нет
            if ( $presence === '0' )
            {// если ученик отсутствовал на занятии - то поставим "Н"
                $grades .= $this->dof->get_string('away_n', 'recordbook');
            }else
            {// если оценки нет - то выводим символ пробела, чтобы ячейка html-таблицы отобразилась
                $grades = '&nbsp;';
            }
        }
        return $grades;
    }

    /** Получить информацию о присутствии ученика на уроке
     * 
     * @return mixed false, если нет данных о посещаемости,
     * "1" - если ученик присутствовал на занятии
     * "0" - если ученика не было на занятии
     * @param int $studentid - id ученика в таблице persons
     * @param int $eventid - id события в таблице schevents
     */
    private function get_presence($studentid, $eventid)
    {
        return $this->dof->storage('schpresences')->get_present_status($studentid, $eventid);
    }
    /** Возвращает оценку по предмету за указанную дату
     * 
     * @return bool|string строка оценок, или 
     * false строка если ничего не нашлось
     * @param int $cstreamsid - id предмето-потока, 
     * соответствующего подписке ученика 
     * на дисциплину в таблице cpassed
     * @param int $planid - id контрольной точки 
     * тематического планирования в таблице plans
     */
    private function get_grade($cstreamid, $planid)
    {
        // перебираем все подписки
        foreach ( $this->cpasseds as $cpassed )
        {// перебираем все подписки из внутренней переменной
            if ( $cpassed->cstreamid == $cstreamid )
            {// если нашли нужную прекращаем перебор
                $params = array();
                $params['cpassedid'] = $cpassed->id;
                $params['planid'] = $planid;
                return $this->dof->storage('cpgrades')->get_record($params);
            }
        }//ничего не нашли
        return false;
    }
}


/** Класс для формирования зачетной книжки учащегося
 */
class dof_im_recordbook_programm_age
{
    /**
     * @var dof_control
     */
    protected $dof;
    private $programmbcsid;
    private $ageid;
        
    /** Конструктор класса
     * @constructor
     * @param dof_control $dof -  cодержит методы ядра деканата
     * @param $programmbcsid - номер подписки на программу
     * @param $ageid [optional] - номер периода
     */
    function __construct(dof_control $dof, $programmbcsid, $ageid=null)
    {
        $this->dof = $dof;
        
        // Определяем $ageid 
        $this->ageid = $ageid;
 
        $this->programmbcsid = $programmbcsid;
    }
    
    /**
     * Возвращает таблицу для зачетной книжки учащегося
     * @return string - html-код страницы
     */
    public function get_programm_age_table()
    {
        // проверим, есть ли все необходимые идентификаторы
        if ( ! $programsbc = $this->dof->storage('programmsbcs')->get($this->programmbcsid) )
        {// такой подписки на учебную программу нет в базе
            $this->dof->print_error('no_program_subscribe', '', $this->dof->url_im('recordbook'), 'im', 'recordbook');
        }
        if ( ! $contract = $this->dof->storage('contracts')->get($programsbc->contractid) )
        {// такой контракт не зарегистрирован
            $this->dof->print_error('contract_not_found', '', $this->dof->url_im('recordbook'), 'im', 'recordbook');
        }
        
        //получаем все события по параметрам
        $data = new stdClass();
        $data->ageid = $this->ageid;
        $data->status =  array('active','completed','failed','reoffset','suspend');
        $data->studentid = $contract->studentid;
        $data->programmid = $programsbc->programmid;
        if ( ! $cpassed = $this->dof->storage('cpassed')->get_listing($data) )
        {// их нет, выводить нечего
            return '';
        }
        // получили все оценки, которые и были пересданы. Оставим только итоговые(без пересдач)
        $cpassed = $this->dof->storage('cpassed')->get_norepeatid_cpassed($cpassed);
        
        // рисуем таблицу
        $table = new stdClass();
        $table->tablealign = "center";
        $table->cellpadding = 2;
        $table->cellspacing = 2;
        //$table->size = array ('100px','150px','150px','200px','150px','100px');
        $table->align = array ("center","center","center","center","center","center","center","center");
        $plans = $this->dof->storage('plans')->get_records(array('linktype'=>'ages','linkid'=>$this->ageid,'type'=>'intermediate'),'reldate ASC');
        // шапка таблицы
        $table->head = array($this->dof->get_string('discipline', 'recordbook'));
        if ( $plans )
        {// если кроме итоговой оценки существуют четвертные или, например, оценки за семестр
            foreach ( $plans as $plan )
            {// 
                $table->head[] = $plan->name;
            }
        }
        
        $table->head[] = $this->dof->get_string('total_grade', 'recordbook');
        // заносим данные в таблицу  
        $table->data = array();
        foreach ( $cpassed as $cpass )
        {// формируем строку для каждого
            $table->data[] = $this->get_one_item_string($cpass,$plans);
        }
        return $this->dof->modlib('widgets')->print_table($table,true);
    }
    
    /** Получить одну строку итоговых оценок по одному предмету
     * 
     * @return array 
     * @param object $cpass
     * @param array $plans
     */
    public function get_one_item_string($cpass,$plans)
    {   
        // массив для итоговых oценок
        $masgraids = array(); 
        if ( ! $recprog = $this->dof->storage('programmitems')->get($cpass->programmitemid) )
        {// не получили программу
            return false;
        }
        $add = array();
        $add['programmsbcid'] = $this->programmbcsid;
        $add['ageid'] = $this->ageid;
        $add['departmentid'] = optional_param('departmentid',0,PARAM_INT);
        $add['cpassed'] = $cpass->id;
        
        // название дисциплины
        if ( $cpass->status != 'active' )
        {
            $recprog->name = '<span class=gray>'.$recprog->name.'</span>';
        }
        if ( $this->dof->im('journal')->is_access('view_journal/own', $cpass->cstreamid) OR 
             $this->dof->im('journal')->is_access('view_journal', $cpass->cstreamid))
        {
            $recprog->name = '<a href="'.$this->dof->url_im('journal','/group_journal/index.php?csid='.
                             $cpass->cstreamid.'&departmentid='.$add['departmentid']).'">'.
                             $recprog->name.'</a>';
        }
        $masgraids[] = $recprog->name;
        // перебираем КТ
        if ( $plans )
        {
            foreach ($plans as $recplans)
            {
                if ( ! $graids = $this->dof->storage('cpgrades')->get_grade_student_cpassed($cpass->id, $recplans->id) )
                {
                    $masgraids[] = '';
                }else 
                {// заполняем массив итогoвыми оценками                
                    $masgraids[] = $graids->grade;    
                }
            }
        }
        // сохраняем оценку в виде ссылки, для отображения истории по ней
        if ( ! $cpass->grade )
        {
            $cpass->grade = '-';
        }
        $masgraids[] = '<a href="'.$this->dof->url_im('recordbook','/finalgrades.php',$add).'#history" 
            title="'.$this->dof->get_string('hystori_view', 'recordbook').'">'.$cpass->grade.'</a>';
        return $masgraids;
    }

    /** Получить историю по пересдаче оценка
     * 
     * @param $cpassid - id cpass, на который надо историю
     * @param integer $depid - id записи из таблицы departments
     * @return $table -  таблицу с данными
     */
    public function show_history_cpass($cpassid, $depid)
    {   
        $cpass = $this->dof->storage('cpassed')->get($cpassid);
        // название предмета
        $name = $this->dof->storage('programmitems')->get_field($cpass->programmitemid,'name');
        // выберим все оценки этого ученика по этому предмету в этом периоде
        // в зависимости от сортировки покажем историю пересдач по программе или по программе и потоку
        $value = $this->dof->storage('config')->get_config_value('finalgrade', 'storage', 'cpassed', $depid);
        if ( $value == 2 )
        {// сортировка поп рограмме и потоку
            $select = " studentid={$cpass->studentid} AND cstreamid={$cpass->cstreamid} AND programmitemid={$cpass->programmitemid} AND 
                        ageid={$cpass->ageid} AND status IN ('active','reoffset','completed','failed','suspend')";            
        }else 
        {
            $select = " studentid={$cpass->studentid} AND programmitemid={$cpass->programmitemid} AND 
                        ageid={$cpass->ageid} AND status IN ('active','reoffset','completed','failed','suspend')";
        }     
        $mascpassed = $this->dof->storage('cpassed')->get_records_select($select,null,'enddate');
        // рисуем таблицу
        $table = new stdClass();
        $table->tablealign = "center";
        $table->cellpadding = 2;
        $table->cellspacing = 2;
        //$table->size = array ('100px','150px','150px','200px','150px','100px');
        $table->align = array ("center","center");
        // добавим якорь
        $pitem = '<a name="history"></a>';
        // шапка таблицы
        $pitem .= '<div style="text-align:center;font-weight:bolder;" >'.$this->dof->get_string('hystori_cpass', 'recordbook',$name).'</div>';
        // заголовок
        $table->head = array($this->dof->get_string('time', 'recordbook'),$this->dof->get_string('total_grade', 'recordbook'));
        // учитываем ВСЕ оценки и пересдачи и где небо сдачи
        foreach ( $mascpassed as $key=>$obj )
        {
            // заносим данные в таблицу
            if ( $obj->grade AND $key == $cpassid )
            {// выделим текущюю оценку 
                $obj->grade = '<span style="color:green;">'.$obj->grade.'</span>';
            }
            $table->data[] = array(dof_userdate($obj->enddate,"%m.%d.%y  %H:%M"),$obj->grade );  
        } 
          
          /*
        do 
        { 
            // заносим данные в таблицу
            $table->data[] = array(date("m.d.y  H:i",$cpass->enddate),$cpass->grade );
            // переопределяем cpass(берем тот, который был заменен)
            $cpass = $this->dof->storage('cpassed')->get($cpass->repeatid);
        } while ( $cpass );*/
        
        return $pitem.'<br>'.$this->dof->modlib('widgets')->print_table($table,true);
    }    
    
    
    
}
?>