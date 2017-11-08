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
 * Импорт данных в Электронный Деканат. Класс импорта из csv.
 *
 * @package    sync
 * @subpackage import
 * @author     Polikarpov Alexander <polikarpovst@gmail.com>
 * @copyright  2016
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

global $DOF;

// Подключение базового класса импорта
require_once $DOF->plugin_path('sync', 'import','/classes/import_base.php');

class dof_sync_import_csv extends dof_sync_import_base
{
    /**
     * Разделитель полей
     *
     * @var string
     */
    protected $delimiter;
    
    /**
     * Текущая строка импорта
     * 
     * @var int
     */
    protected $currentline = 1;
    
    /**
     * Карта полей файла импорта
     * 
     * Хранит местоположение данных в строке файла импорта
     * Пример [2 => 'userid', 5 => 'contractid']
     *
     * @var array
     */
    protected $fields = [];
    
    /**
     * Доступные для импорта поля
     * 
     * Список полей, которые поддерживает класс импорта
     *
     * @var array
     */
    protected $availablefields = [];
    
    /**
     * Конструктор
     *
     * @param dof_control $dof -  Объект деканата для доступа к общим методам
     * @param string $type - Тип импорта(ожидаемые данные)
     * @param string $importfilepath -  Объект деканата для доступа к общим методам
     * @param string $delimiter - Тип разделителя ячеек
     * 
     * @throws dof_exception - При ошибке во время инициализации импортера
     */
    public function __construct($dof, $type, $importfilepath, $delimiter)
    {
        // Базовый конструктор
        parent::__construct($dof, $type, $importfilepath);
        
        // Разделитель ячеек
        $this->delimiter = $delimiter;
        
        // Формирование поддерживаемых полей в зависимости от типа импорта
        $this->set_avalilablefields();
    }
    
    /**
     * Процесс импорта
     *
     * @param array $options - Дополнительные опции обработки
     *
     * @throws dof_exception - При критических ошибках во время импорта файла
     *
     * @return void
     */
    public function import($options)
    {
        // Подготовка к импорту
        parent::import($options);
    
        // Открытие файла
        $importfile = fopen($this->importfilepath, 'r');
    
        // Анализатор полей
        $fields = (array)fgetcsv($importfile, 0, $this->delimiter);
        $this->fields_analyzer($fields);
        
        // Логирование процесса
        $currenttime = dof_userdate(time(), '%Y_%m_%d_%I:%M:%S', 99, false);
        $this->add_log('--Import process started ('.$currenttime.')--');
        
        while ( ( $row = fgetcsv($importfile, 0, $this->delimiter) ) !== false )
        {// Получение строки файла
            // Текущая строка
            $this->currentline++;
            // Подготовка результата обработки строки
            $this->importresult[$this->currentline] = new stdClass();

            // Получение данных из строки
            $rowdata = $this->get_rowdata($row);
            if ( $rowdata === null )
            {// Ошибка формирования данных строки
                $this->importresult[$this->currentline]->errors['rowdata_getting_error'] =
                    $this->dof->get_string('import_csv_error_rowdata_getting_error', 'import', null, 'sync');
                continue;
            }
            unset($row);
            
            // Процесс выполнения действий
            $this->execute_actions($rowdata, $this->importresult[$this->currentline]);
        }
        
        // Логирование процесса
        $currenttime = dof_userdate(time(), '%Y_%m_%d_%I:%M:%S', 99, false);
        $this->add_log('--Import process ended ('.$currenttime.')--');
    }
    
    /**
     * Установка доступных полей импорта
     * 
     * В зависимости от типа определяет список полей импорта
     *
     * @param string $type - Тип импорта
     *
     * @return void
     */
    protected function set_avalilablefields()
    {
        if ( $this->type === 'programmsbcs' )
        {// Импорт подписок на программы
            $this->availablefields =
            [
                // Договор на обучение
                'studentcontract_num' => $this->dof->get_string('field_studentcontract_num', 'import', null, 'sync'),
                'studentcontract_startdate' => $this->dof->get_string('field_studentcontract_startdate', 'import', null, 'sync'),
                'studentcontract_notice' => $this->dof->get_string('field_studentcontract_notice', 'import', null, 'sync'),
                // Студент по договору
                'student_email' => $this->dof->get_string('field_student_email', 'import', null, 'sync'),
                'student_firstname' => $this->dof->get_string('field_student_firstname', 'import', null, 'sync'),
                'student_lastname' => $this->dof->get_string('field_student_lastname', 'import', null, 'sync'),
                'student_middlename' => $this->dof->get_string('field_student_middlename', 'import', null, 'sync'),
                'student_birthdate' => $this->dof->get_string('field_student_birthdate', 'import', null, 'sync'),
                'student_gender' => $this->dof->get_string('field_student_gender', 'import', null, 'sync'),
                // Законный представитель по договору
                'parent_email' => $this->dof->get_string('field_parent_email', 'import', null, 'sync'),
                'parent_firstname' => $this->dof->get_string('field_parent_firstname', 'import', null, 'sync'),
                'parent_lastname' => $this->dof->get_string('field_parent_lastname', 'import', null, 'sync'),
                'parent_middlename' => $this->dof->get_string('field_parent_middlename', 'import', null, 'sync'),
                'parent_birthdate' => $this->dof->get_string('field_parent_birthdate', 'import', null, 'sync'),
                'parent_gender' => $this->dof->get_string('field_parent_gender', 'import', null, 'sync'),
                // Менеджер по договору
                'seller_email' => $this->dof->get_string('field_seller_email', 'import', null, 'sync'),
                'seller_firstname' => $this->dof->get_string('field_seller_firstname', 'import', null, 'sync'),
                'seller_lastname' => $this->dof->get_string('field_seller_lastname', 'import', null, 'sync'),
                'seller_middlename' => $this->dof->get_string('field_seller_middlename', 'import', null, 'sync'),
                'seller_birthdate' => $this->dof->get_string('field_seller_birthdate', 'import', null, 'sync'),
                'seller_gender' => $this->dof->get_string('field_seller_gender', 'import', null, 'sync'),
                // Куратор по договору
                'curator_email' => $this->dof->get_string('field_curator_email', 'import', null, 'sync'),
                'curator_firstname' => $this->dof->get_string('field_curator_firstname', 'import', null, 'sync'),
                'curator_lastname' => $this->dof->get_string('field_', 'import', null, 'sync'),
                'curator_middlename' => $this->dof->get_string('field_curator_lastname', 'import', null, 'sync'),
                'curator_birthdate' => $this->dof->get_string('field_curator_birthdate', 'import', null, 'sync'),
                'curator_gender' => $this->dof->get_string('field_curator_gender', 'import', null, 'sync'),
                // Работник по договору
                /*'employee_email' => $this->dof->get_string('field_employee_email', 'import', null, 'sync'),
                'employee_firstname' => $this->dof->get_string('field_employee_firstname', 'import', null, 'sync'),
                'employee_lastname' => $this->dof->get_string('field_employee_lastname', 'import', null, 'sync'),
                'employee_middlename' => $this->dof->get_string('field_employee_middlename', 'import', null, 'sync'),
                'employee_birthdate' => $this->dof->get_string('field_employee_birthdate', 'import', null, 'sync'),
                'employee_gender' => $this->dof->get_string('field_employee_gender', 'import', null, 'sync'),*/
                // Программа обучения
                'programm_code' => $this->dof->get_string('field_programm_code', 'import', null, 'sync'),
                /*'programm_name' => $this->dof->get_string('field_programm_name', 'import', null, 'sync'),
                'programm_about' => $this->dof->get_string('field_programm_about', 'import', null, 'sync'),
                'programm_notice' => $this->dof->get_string('field_programm_notice', 'import', null, 'sync'),
                'programm_agenums' => $this->dof->get_string('field_programm_agenums', 'import', null, 'sync'),
                'programm_duration' => $this->dof->get_string('field_programm_duration', 'import', null, 'sync'),
                'programm_ahours' => $this->dof->get_string('field_programm_ahours', 'import', null, 'sync'),
                'programm_billingtext' => $this->dof->get_string('field_programm_billingtext', 'import', null, 'sync'),
                'programm_billingrules' => $this->dof->get_string('field_programm_billingrules', 'import', null, 'sync'),
                'programm_flowagenums' => $this->dof->get_string('field_programm_flowagenums', 'import', null, 'sync'),
                'programm_edulevel' => $this->dof->get_string('field_programm_edulevel', 'import', null, 'sync'),*/
                // Академическая группа
                'agroup_code' => $this->dof->get_string('field_agroup_code', 'import', null, 'sync'),
                /*'agroup_name' => $this->dof->get_string('field_agroup_name', 'import', null, 'sync'),
                 'agroup_programm' => $this->dof->get_string('field_agroup_programm', 'import', null, 'sync'),
                 'agroup_agenum' => $this->dof->get_string('field_agroup_agenum', 'import', null, 'sync'),
                 'agroup_salfactor' => $this->dof->get_string('field_agroup_salfactor', 'import', null, 'sync'),*/
                // Подписка на программу
                'programmsbc_agenum' => $this->dof->get_string('field_programmsbc_agenum', 'import', null, 'sync'),
                'programmsbc_edutype' => $this->dof->get_string('field_programmsbc_edutype', 'import', null, 'sync'),
                'programmsbc_eduform' => $this->dof->get_string('field_programmsbc_eduform', 'import', null, 'sync'),
                'programmsbc_freeattendance' => $this->dof->get_string('field_programmsbc_freeattendance', 'import', null, 'sync'),
                'programmsbc_datestart' => $this->dof->get_string('field_programmsbc_startdate', 'import', null, 'sync'),
                'programmsbc_salfactor' => $this->dof->get_string('field_programmsbc_salfactor', 'import', null, 'sync')
            ];
        }
    }
    
    /**
     * Анализатор полей
     * 
     * Производит анализ полей для определения объектов импорта
     * 
     * @param array $fields - Поля csv файла
     * 
     * @return void
     */
    protected function fields_analyzer($fields)
    {
        // Формирование карты полей
        foreach ( $fields as $fieldnum => $fieldcode )
        {
            // Проверка валидности поля
            if ( ! empty($fieldcode) && isset($this->availablefields[$fieldcode]) )
            {// Поле поддерживается импортером
                // Добавление позиции поля
                $this->fields[$fieldcode] = $fieldnum;
            }
        }
    }
    
    /**
     * Получение данных из строки
     *
     * Формирует массив значений из доступных к импорту элементов строки
     * 
     * @param array $notfilteredrow
     * 
     * @return array|null - Массив с данными по строке или null при ошибках формирования данных
     */
    protected function get_rowdata($notfilteredrow)
    {
        // Формирование данных по строке
        $rowdata = [];
        foreach ( $this->fields as $fieldcode => $fieldposition )
        {
            if ( ! isset($notfilteredrow[$fieldposition]) )
            {// Поле не найдено в строке
                return null;
            }
            $rowdata[$fieldcode] = $notfilteredrow[$fieldposition];
        }
        
        return $rowdata;
    }
    
    /**
     * Исполнить действия по импорту элементов в Деканат 
     * на основе переданных данных из строки
     * 
     * @param array $rowdata - Массив данных из строки
     * @param stdClass $resultdata - Ссылка на данные с результатом импорта
     * 
     * @return void
     */
    protected function execute_actions($rowdata, &$resultdata)
    {
        // Логирование процесса
        $this->add_log('Row '.$this->currentline.' import');
        
        $action = 'action_'.$this->type;
        if ( method_exists($this, $action) )
        {// Исполнение задачи
            // Логирование процесса
            $this->add_log($action.' started');
            $this->$action($rowdata, $resultdata);
        }
    }
    
    /**
     * Задача по созданию подписки на программу
     * 
     * @param array $rowdata - Массив данных из строки
     * @param stdClass $resultdata - Ссылка на данные с результатом импорта
     * 
     * @return bool - Результат исполнения задачи
     */
    protected function action_programmsbcs($rowdata, &$resultdata)
    {
        $defaultdepid = (int)$this->dof->storage('departments')->get_default_id();
        $currentdepid = optional_param('departmentid', $defaultdepid, PARAM_INT);
        // Заполнение данных по договору
        $studentcontract = new stdClass();
        if ( isset($rowdata['studentcontract_num']) )
        {// Указан номер договора
            $studentcontract->num = $rowdata['studentcontract_num'];
        }
        if ( isset($rowdata['studentcontract_startdate']) )
        {// Указана дата создания договора
            $studentcontract->date = $rowdata['studentcontract_startdate'];
        }
        if ( isset($rowdata['studentcontract_notice']) )
        {// Указана заметка по договору
            $studentcontract->notice = $rowdata['studentcontract_notice'];
        }
        
        // Заполнение данных по студенту
        $student = new stdClass();
        if ( isset($rowdata['student_email']) )
        {// Указан email студента
            $student->email = $rowdata['student_email'];
        }
        if ( isset($rowdata['student_firstname']) )
        {// Указано имя студента
            $student->firstname = $rowdata['student_firstname'];
        }
        if ( isset($rowdata['student_lastname']) )
        {// Указано фамилия студента
            $student->lastname = $rowdata['student_lastname'];
        }
        if ( isset($rowdata['student_middlename']) )
        {// Указано отчество студента
            $student->middlename = $rowdata['student_middlename'];
        }
        if ( isset($rowdata['student_birthdate']) )
        {// Указана дата рождения студента
            $student->birthdate = $rowdata['student_birthdate'];
        }
        if ( isset($rowdata['student_gender']) )
        {// Указан пол студента
            $student->gender = $rowdata['student_gender'];
        }
        
        // Заполнение данных по законному представителю
        $parent = new stdClass();
        if ( isset($rowdata['parent_email']) )
        {// Указан email законного представителя
            $parent->email = $rowdata['parent_email'];
        }
        if ( isset($rowdata['parent_firstname']) )
        {// Указано имя законного представителя
            $parent->firstname = $rowdata['parent_firstname'];
        }
        if ( isset($rowdata['parent_lastname']) )
        {// Указано фамилия законного представителя
            $parent->lastname = $rowdata['parent_lastname'];
        }
        if ( isset($rowdata['parent_middlename']) )
        {// Указано отчество законного представителя
            $parent->middlename = $rowdata['parent_middlename'];
        }
        if ( isset($rowdata['parent_birthdate']) )
        {// Указана дата рождения законного представителя
            $parent->birthdate = $rowdata['parent_birthdate'];
        }
        if ( isset($rowdata['parent_gender']) )
        {// Указан пол законного представителя
            $parent->gender = $rowdata['parent_gender'];
        }
        
        // Заполнение данных по менеджеру
        $seller = new stdClass();
        if ( isset($rowdata['seller_email']) )
        {// Указан email менеджера
            $seller->email = $rowdata['seller_email'];
        }
        if ( isset($rowdata['seller_firstname']) )
        {// Указано имя менеджера
            $seller->firstname = $rowdata['seller_firstname'];
        }
        if ( isset($rowdata['seller_lastname']) )
        {// Указано фамилия менеджера
            $seller->lastname = $rowdata['seller_lastname'];
        }
        if ( isset($rowdata['seller_middlename']) )
        {// Указано отчество менеджера
            $seller->middlename = $rowdata['seller_middlename'];
        }
        if ( isset($rowdata['seller_birthdate']) )
        {// Указана дата рождения менеджера
            $seller->birthdate = $rowdata['seller_birthdate'];
        }
        if ( isset($rowdata['seller_gender']) )
        {// Указан пол менеджера
            $seller->gender = $rowdata['seller_gender'];
        }
        
        // Заполнение данных по куратору
        $curator = new stdClass();
        if ( isset($rowdata['curator_email']) )
        {// Указан email менеджера
            $curator->email = $rowdata['curator_email'];
        }
        if ( isset($rowdata['curator_firstname']) )
        {// Указано имя менеджера
            $curator->firstname = $rowdata['curator_firstname'];
        }
        if ( isset($rowdata['curator_lastname']) )
        {// Указано фамилия менеджера
            $curator->lastname = $rowdata['curator_lastname'];
        }
        if ( isset($rowdata['curator_middlename']) )
        {// Указано отчество менеджера
            $curator->middlename = $rowdata['curator_middlename'];
        }
        if ( isset($rowdata['curator_birthdate']) )
        {// Указана дата рождения менеджера
            $curator->birthdate = $rowdata['curator_birthdate'];
        }
        if ( isset($rowdata['curator_gender']) )
        {// Указан пол менеджера
            $curator->gender = $rowdata['curator_gender'];
        }
        
        // Заполнение данных по программе
        $programm = new stdClass();
        if ( isset($rowdata['programm_code']) )
        {// Указан код программы
            $programm->code = $rowdata['programm_code'];
        }
        
        // Заполнение данных по группе 
        $agroup = new stdClass();
        if ( isset($rowdata['agroup_code']) )
        {// Указан код академической группы
            $agroup->code = $rowdata['agroup_code'];
        }
        
        // Заполнение данных о подписке
        $programmsbc = new stdClass();
        if ( isset($rowdata['programmsbc_agenum']) )
        {// Указан номер параллели по подписке
            $programmsbc->agenum = $rowdata['programmsbc_agenum'];
        }
        if ( isset($rowdata['programmsbc_edutype']) )
        {// Указан номер параллели по подписке
            $programmsbc->edutype = $rowdata['programmsbc_edutype'];
        }
        if ( isset($rowdata['programmsbc_eduform']) )
        {// Указан номер параллели по подписке
            $programmsbc->eduform = $rowdata['programmsbc_eduform'];
        }
        if ( isset($rowdata['programmsbc_freeattendance']) )
        {// Указан номер параллели по подписке
            $programmsbc->freeattendance = $rowdata['programmsbc_freeattendance'];
        }
        if ( isset($rowdata['programmsbc_datestart']) )
        {// Указан номер параллели по подписке
            $programmsbc->datestart = $rowdata['programmsbc_datestart'];
        }
        if ( isset($rowdata['programmsbc_salfactor']) )
        {// Указан номер параллели по подписке
            $programmsbc->salfactor = $rowdata['programmsbc_salfactor'];
        }
        
        // Формирование данных для создания подписки
        if ( ! isset($programmsbc->contractid) )
        {// Договор не указан
            $programmsbc->contractid = $studentcontract;
            if ( ! isset($programmsbc->contractid->studentid) )
            {// Студент не указан
                $programmsbc->contractid->studentid = $student;
            }
            if ( ! isset($programmsbc->contractid->clientid) )
            {// Законный представитель не указан
                $programmsbc->contractid->clientid = $parent;
            }
            if ( ! isset($programmsbc->contractid->sellerid) )
            {// Менеджер не указан
                $programmsbc->contractid->sellerid = $seller;
            }
            if ( ! isset($programmsbc->contractid->curatorid) )
            {// Куратор не указан
                $programmsbc->contractid->curatorid = $curator;
            }
        }
        if ( ! isset($programmsbc->programmid) )
        {// Программа не указана
            $programmsbc->programmid = $programm;
        }
        if ( ! isset($programmsbc->agroupid) )
        {// Академическая группа не указана
            $programmsbc->agroupid = $agroup;
        }
        
        // Очистка от пустых данных
        $this->clean_rowdata($programmsbc);
        
        // Инициализация процесса создания подписки с автозаполнением недостающих данных
        $options = [];
        $options['departmentid'] = $currentdepid;
        $options['notexist_action'] = 'create';
        if ( $this->simulation )
        {// Симуляция процесса без добавления элементов в БД
            $options['simulation'] = true;
        }
        $resultdata->report = [];
        $this->dof->storage('programmsbcs')->import($programmsbc, $resultdata->report, $options);
    }
    
    protected function clean_rowdata(&$rowdata)
    {
        foreach ( $rowdata as $field => &$value )
        {
            if ( is_object($value) )
            {
                $this->clean_rowdata($value);
                $check = (array)$value;
                if ( empty($check) )
                {
                    unset($rowdata->$field);
                }
            } elseif ( trim((string)$value) === '' )
            {
                unset($rowdata->$field);
            }
        }
    }
}