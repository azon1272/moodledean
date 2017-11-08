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
 * Интерфейс управления подразделениями. Библиотека форм.
 * 
 * @package    im
 * @subpackage departments
 * @author     Polikarpov Alexander <polikarpovst@gmail.com>
 * @copyright  2015
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Подключение базовых функций плагина
require_once('lib.php');
global $DOF;
// Подключение библиотеки форм
$DOF->modlib('widgets')->webform();

class dof_im_edit extends dof_modlib_widgets_form
{  
    /**
     * @var dof_control
     */
    protected $dof;
    
    /**
     * @var $id - ID выбранного подразделения
     */
    protected $id = 0;
    
    /**
     * @var $addvars - GET параметры для ссылки
     */
    protected $addvars = [];
    
    /**
     * @var $returnurl - URL для возврата
     */
    protected $returnurl = NULL;
    
    public function definition()
    {
        // Создание ссылки на HTML_QuickForm
        $mform =& $this->_form;
        
        // Добавляем свойства
        $this->dof = $this->_customdata->dof;
        $this->id = $this->_customdata->id;
        $this->addvars = $this->_customdata->addvars;
        if ( isset($this->_customdata->returnurl) && ! empty($this->_customdata->returnurl) )
        {// Передан url возврата
            $this->returnurl = $this->_customdata->returnurl;
        } else 
        {// Установка url возврата на страницу обработчика
            $this->returnurl = $mform->getAttribute('action');
        }
        
        // Скрытые поля
        $mform->addElement('hidden', 'sesskey', sesskey());
        $mform->setType('sesskey', PARAM_ALPHANUM);
        $mform->addElement('hidden', 'id', $this->id);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'departmentid', $this->addvars['departmentid']);
        $mform->setType('departmentid', PARAM_INT);
        $mform->addElement('hidden', 'addressid', 0);
        $mform->setType('addressid', PARAM_INT);
        
        // Заголовок формы
        if ( empty($this->id) )
        {// Создание подразделения
            $mform->addElement(
                'header',
                'form_header',
                $this->dof->get_string('newdepartment','departments')
            );
        } else
        {// Редактирование подразделения
            $mform->addElement(
                'header',
                'form_header',
                $this->dof->get_string('editdepartment','departments')
            );
        }
        
        // Поле для вывода сообщений об ошибках скрытых элементов
        $mform->addElement(
            'static',
            'hidden',
            ''
        );

        // Имя подразделения
        $mform->addElement(
            'text', 
            'name', 
            $this->dof->get_string('name', 'departments')
        );
        $mform->setType('name', PARAM_TEXT);
        
        // Код подразделения
        $mform->addElement(
            'text', 
            'code', 
            $this->dof->get_string('code','departments')
        );
        $mform->setType('code', PARAM_TEXT);
        
        // Руководитель подразделения
        $persondepartment = $this->addvars['departmentid'];
        if ( $this->id )
        {// Указано редактируемое подразделение
            $persondepartment = $this->id;
        }
        $options = [];
        $options['plugintype'] = 'storage';
        $options['plugincode'] = 'persons';
        $options['sesskey']    = sesskey();
        $options['type']       = 'autocomplete';
        $options['departmentid'] = $persondepartment;
        $options['querytype']  = 'persons_list_departmentmanager';
        $mform->addElement(
            'dof_autocomplete', 
            'managerid', 
            $this->dof->get_string('manager','departments'),
            [],
            $options
        );
        
        // Вышестоящее подразделение
        $departments = $this->get_list_leaddep();
        $mform->addElement(
            'select', 
            'leaddepid',
            $this->dof->get_string('leaddep','departments'),
            $departments
        );
        
        // Часовой пояс 
        $timezones = $this->get_list_timezone();
        $mform->addElement(
            'select', 
            'zone', 
            $this->dof->get_string('zone','departments'),
            $timezones
        );
        
        // Адрес
        $mform->addElement(
            'header', 
            'form_header_address', 
            $this->dof->get_string('departmentaddress','departments') 
        );
        
        // Страна и регион 
        $choices = get_string_manager()->get_list_of_countries(false);
        $sel = $mform->addElement(
            'hierselect', 
            'country', 
            $this->dof->get_string('addrcountryregion', 'departments'),
            NULL,
            '<br>'
        );
        $sel->setMainOptions($choices);
        $sel->setSecOptions($this->get_list_regions($choices));  
        $mform->setAdvanced('country');
        
        // Индекс
        $mform->addElement(
            'text', 
            'postalcode', 
            $this->dof->get_string('addrpostalcode','departments')
        );
        $mform->setType('postalcode', PARAM_TEXT);
        $mform->setAdvanced('postalcode');
        
        // Округ/Район
        $mform->addElement(
            'text', 
            'county', 
            $this->dof->get_string('addrcounty','departments')
        );
        $mform->setType('county', PARAM_TEXT);
        $mform->setAdvanced('county');
        
        // Населенный пункт
        $mform->addElement(
            'text', 
            'city', 
            $this->dof->get_string('addrcity','departments')
        );
        $mform->setType('city', PARAM_TEXT);
        $mform->setAdvanced('city');
        
        // Название улицы
        $mform->addElement(
            'text', 
            'streetname', 
            $this->dof->get_string('addrstreetname','departments')
        );
        $mform->setType('streetname', PARAM_TEXT);
        $mform->setAdvanced('streetname');
        
        // Тип улицы
        $typestreets = $this->dof->modlib('refbook')->get_street_types();
        $mform->addElement(
            'select', 
            'streettype', 
            $this->dof->get_string('addrstreettype','departments'),
            $typestreets
        );
        $mform->setType('streettype', PARAM_TEXT);
        $mform->setAdvanced('streettype');
        
        // Номер дома
        $mform->addElement(
            'text', 
            'number', 
            $this->dof->get_string('addrnumber','departments')
        );
        $mform->setType('number', PARAM_TEXT);
        $mform->setAdvanced('number');
        
        // Подъезд
        $mform->addElement(
            'text', 
            'gate', 
            $this->dof->get_string('addrgate','departments')
        );
        $mform->setType('gate', PARAM_TEXT);
        $mform->setAdvanced('gate');
        
        // Этаж
        $mform->addElement(
            'text', 
            'floor', 
            $this->dof->get_string('addrfloor','departments')
        );
        $mform->setType('floor', PARAM_TEXT);
        $mform->setAdvanced('floor');
        
        // Номер квартиры
        $mform->addElement(
            'text', 
            'apartment', 
            $this->dof->get_string('addrapartment','departments')
        );
        $mform->setType('apartment', PARAM_TEXT);
        $mform->setAdvanced('apartment');
        
        // Широта
        $mform->addElement(
            'text', 
            'latitude', 
            $this->dof->get_string('addrlatitude','departments')
        );
        $mform->setType('latitude', PARAM_TEXT);
        $mform->setAdvanced('latitude');
        
        // Долгота
        $mform->addElement(
            'text', 
            'longitude', 
            $this->dof->get_string('addrlongitude', 'departments')
        );
        $mform->setType('longitude', PARAM_TEXT);
        $mform->setAdvanced('longitude');
        
        $mform->closeHeaderBefore('submit');
        
        // Кнопки действий
        $group = [];
        $group[] = $mform->createElement(
            'submit', 
            'submit', 
            $this->dof->get_string('form_edit_submit', 'departments')
        );
        if (  $this->returnurl != $mform->getAttribute('action') )
        {// Установлен URL перехода
            // Добавление кнопок, требующих возврата
            $group[] = $mform->createElement(
                'submit',
                'submitclose',
                $this->dof->get_string('form_edit_submit_close', 'departments')
                );
            $group[] = $mform->createElement('cancel');
        }
        
        $mform->addGroup($group, 'buttonar', '', '', false);
        
        // Применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
    }
    
    /**
     * Задаем проверку корректности введенных значений
     */
    public function validation($data, $files)
    {
        // Cоздаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        
        // Массив ошибок
        $errors = [];
        
        if ( ! trim($data['name']) )
        {// Пустое имя
            $errors['name'] = $this->dof->get_string('err_required','departments');
        }

        if ( trim($data['code']) )
        {// Код указан
            // Проверка уникальности кода
            $departs = $this->dof->storage('departments')->get_records(['code' => trim($data['code'])]);
            unset($departs[$this->id]);
            if ( ! empty($departs) )
            {// Код не уникален
                $errors['code'] = $this->dof->get_string('err_code_not_unique','departments');
            }
        } else
        {// Код не указан
            if ( $this->id > 0 )
            {
                $errors['code'] = $this->dof->get_string('err_required','departments');
            }
        }
        if ( (int)$data['managerid']['id'] > 0 )
        {// Указан руководитель
            $person = $this->dof->storage('persons')->get((int)$data['managerid']['id']);
            if ( empty($person) )
            {// Адрес не найден
                $errors['managerid'] = $this->dof->get_string('err_manager_not_found','departments');
            }
        }
        
        // Проверка на доступность смены подразделения
        $leaddepid = $this->dof->storage('departments')->get_field($data['id'], 'leaddepid');
        if ( (int)$data['leaddepid'] != (int)$leaddepid && ! is_bool($leaddepid) )
        {// Смена Вышестоящего подразделения
            if ( ! $this->dof->storage('departments')->is_access('create', NULL, NULL, $data['leaddepid']) )
            {// Нет прав на перемещение в целевое подразделение
                $errors['leaddepid'] = $this->dof->get_string('form:error:unable_to_transfer_to_department','departments');
            }
            if ( ! $this->dof->storage('departments')->is_access('delete', NULL, NULL, $leaddepid) )
            {// Нет прав на перемещение из текущего подразделения 
                $errors['leaddepid'] = $this->dof->get_string('form:error:unable_to_transfer_from_department','departments');
            }
            if ( ! $this->dof->storage('config')->get_limitobject('departments', $data['leaddepid']) )
            {// Проверка лимитов
                $errors['leaddepid'] = $this->dof->get_string('limit_message','departments');
            }
        }
        
        if ( (int)$data['addressid'] > 0 )
        {// Указаны данные адреса
            $address = $this->dof->storage('addresses')->get((int)$data['addressid']);
            if ( empty($address) )
            {// Адрес не найден
                $errors['hidden'] = $this->dof->get_string('err_address_not_found','departments');
            }
        }
        
		// Если указана улица - требуется тип
        if ( ! empty($data['streetname']) && empty($data['streettype']) )
		{
			$errors['streettype'] = $this->dof->get_string('err_streettype','departments');
	  	}
	  	
	  	if ( (float)$data['latitude'] < 0 || (float)$data['latitude'] > 999 )
	  	{
	  	    $errors['latitude'] = $this->dof->get_string('err_notvalid', 'departments');
	  	}
	  	if ( (float)$data['longitude'] < 0 || (float)$data['longitude'] > 999 )
	  	{
	  	    $errors['longitude'] = $this->dof->get_string('err_notvalid', 'departments');
	  	}

	  	// Убираем лишние пробелы со всех полей формы
        $mform->applyFilter('__ALL__', 'trim');
        
        // Возвращаем ошибки, если они есть
        return $errors;
    }
    
    /**
     * Заполнение формы данными
     */
    public function definition_after_data()
    {
        // Cоздаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
    
        if ( ! empty($this->id) )
        {// Заполнение значениями
            $department = $this->dof->storage('departments')->get($this->id);
            if ( ! empty($department) )
            {// Заполнение достижений
                $mform->setDefault('name', $department->name);
                $mform->setDefault('code', $department->code);
                $mform->setDefault('managerid[id]', $department->managerid);
                $mform->setDefault('managerid[id_autocomplete]', $department->managerid);
                
                if ( $department->managerid > 0 )
                {// Руководитель указан
                    $fullname = $this->dof->storage('persons')->get_fullname($department->managerid);
                    if (empty($fullname) )
                    {// Персона не найдена
                        $fullname = ' - ';
                    }
                    $mform->setDefault('managerid[managerid]', $fullname.' ['.$department->managerid.']' );
                }
                $mform->setDefault('leaddepid', $department->leaddepid);
                $mform->setDefault('zone', $department->zone);
                
                // Получение адреса
                $address = $this->dof->storage('addresses')->get($department->addressid);
                if ( ! empty($address) )
                {// Адрес найден
                    $mform->setDefault('addressid', $department->addressid);
                    $mform->setDefault('country', [$address->country,$address->region]);
                    $mform->setDefault('postalcode', $address->postalcode);
                    $mform->setDefault('county', $address->county);
                    $mform->setDefault('city', $address->city);
                    $mform->setDefault('streetname', $address->streetname);
                    $mform->setDefault('streettype', $address->streettype);
                    $mform->setDefault('number', $address->number);
                    $mform->setDefault('gate', $address->gate);
                    $mform->setDefault('floor', $address->floor);
                    $mform->setDefault('apartment', $address->apartment);
                    $mform->setDefault('latitude', $address->latitude);
                    $mform->setDefault('longitude', $address->longitude);
                } else
                {// Адрес не найден
                    if ( $department->addressid != 0 )
                    {// Вывод сообщения о том, что адрес ошибочен
                        $this->dof->messages->add(
                            $this->dof->get_string('err_address_not_found','departments'), 
                            'error'
                        );
                    }
                    $mform->setDefault('addressid', 0);
                }
            } else
            {// Достижение не найдено
                $mform->setDefault('id', 0);
            }
        } else 
        {// Значения по-умолчанию
            $default = $this->dof->storage('departments')->get_default_id();
            $mform->setDefault('leaddepid', $default);
            $mform->setDefault('zone', 99);
            $mform->setDefault('country', ['RU']);
        }
    }
    
    /** 
     * Возвращает список регионов приписанных к стране
     * 
     * @param array $choices - список стран
     * @return array список регионов
     */
    private function get_list_regions($choices)
    {
        $regions = [];
        if ( ! is_array($choices) )
        {//получили не массив - это ошибка';
            return $rez;
        }
        // к каждой стране припишем ее регионы
        foreach ($choices as $key => $value)
        {
            $regions += $this->dof->modlib('refbook')->region($key);
        }
        return $regions;
    }
    
    /**
     * Сгенерировать код подразделения
     *
     * @return array - Массив типов
     */
    private function generate_code($name)
    {
        // Добавлять постфикс к коду
        $add = false;
        // Лимит попыток
        $limit = 100;
        do 
        {
            // Очищвем имя
            $name = trim($name);
            // Получаем длину имени
            $length = strlen($name);
            // Транслитерация имени
            $translit = $this->translit($name);
            if ( $length > 10 )
            {// Обрезали до 10 знаков
                $translit = substr($translit, 0, 9);
            }
            if ( $add )
            {
                $translit .= mt_rand (1000, 99999);
            }
            // Поиск подразделения с таким кодом
            $exist = $this->dof->storage('departments')->get_record(['code' => $translit]);
            if ( $exist )
            {
                $add = true;
            }
            $limit--;
            
        } while ( $exist && $limit );
        
        if ( empty($limit) )
        {// Превышен интервал попыток сгенерировать код подразделения
            return false;
        }
        
        return $translit;
    }
    
    /**
     * Транслитерация
     *
     * @return string - Массив типов
     */
    private function translit($string) 
    {
        $converter = array(
        'а' => 'a',   'б' => 'b',   'в' => 'v',
        'г' => 'g',   'д' => 'd',   'е' => 'e',
        'ё' => 'e',   'ж' => 'zh',  'з' => 'z',
        'и' => 'i',   'й' => 'y',   'к' => 'k',
        'л' => 'l',   'м' => 'm',   'н' => 'n',
        'о' => 'o',   'п' => 'p',   'р' => 'r',
        'с' => 's',   'т' => 't',   'у' => 'u',
        'ф' => 'f',   'х' => 'h',   'ц' => 'c',
        'ч' => 'ch',  'ш' => 'sh',  'щ' => 'sch',
        'ь' => '\'',  'ы' => 'y',   'ъ' => '\'',
        'э' => 'e',   'ю' => 'yu',  'я' => 'ya',
        
        'А' => 'A',   'Б' => 'B',   'В' => 'V',
        'Г' => 'G',   'Д' => 'D',   'Е' => 'E',
        'Ё' => 'E',   'Ж' => 'Zh',  'З' => 'Z',
        'И' => 'I',   'Й' => 'Y',   'К' => 'K',
        'Л' => 'L',   'М' => 'M',   'Н' => 'N',
        'О' => 'O',   'П' => 'P',   'Р' => 'R',
        'С' => 'S',   'Т' => 'T',   'У' => 'U',
        'Ф' => 'F',   'Х' => 'H',   'Ц' => 'C',
        'Ч' => 'Ch',  'Ш' => 'Sh',  'Щ' => 'Sch',
        'Ь' => '\'',  'Ы' => 'Y',   'Ъ' => '\'',
        'Э' => 'E',   'Ю' => 'Yu',  'Я' => 'Ya',
        );
        return strtr($string, $converter);
    }
    
    /**
     * Получить список вышестоящих для поля
     *
     * @param $id - ID - элемента-родителя
     * @param $level - Уровень вложенности
     *
     * @return array - Массив подразделений
     */
    protected function get_list_leaddep($id = 0, $level = 0)
    {
        $result = [];
        
        $ldepid = -1;
        if ( ! empty($this->id) )
        {// Подразделение определено
            $department = $this->dof->storage('departments')->get($this->id);
            if ( ! empty($department) )
            {
                $ldepid = $department->leaddepid;
            }
        }
        
        // Добавления корня
        if ( $id == 0 && ( $this->dof->storage('departments')->is_access('create', NULL, NULL, 0) || $ldepid == 0 ) )
        {// Добавление корневого элемента
            $result['0'] = $this->dof->get_string('none','departments');
        }
        
        // Получим cписок дочерних элементов
        $statuses = $this->dof->workflow('departments')->get_meta_list('real');
        $statuses = array_keys($statuses);
        $options['statuses'] = $statuses;
        $departments = $this->dof->storage('departments')->
            get_records(['leaddepid' => $id, 'status' => $statuses], '', 'id, name, code');
        
        if ( ! empty($departments) )
        {// Сформируем массив
            // Получим отступ
            $shift = str_pad('', $level, '-');
            foreach ( $departments as $department )
            {
                if ( $department->id == $this->id )
                {// Удалим текущий редактируемый элемент
                    continue;
                }
                if ( ! $this->dof->storage('departments')->is_access('view', NULL, NULL, $department->id) && 
                     $department->id != $ldepid )
                {// Нет прав и текущее подразделение не является вышестоящим 
                    continue;
                }
                // Сформируем элемент
                $result[$department->id] = $shift.' ['.$department->code.']'.$department->name;
                // Получим массив дочерних
                $childrens = $this->get_list_leaddep($department->id, $level + 1);
                // Добавим к исходному
                $result = $result + $childrens;
            }
        }
    
        return $result;
    }
    
    /** 
     * Вернуть список временных зон
     * 
     * @return array
     */
    protected function get_list_timezone()
    {
        $rez = get_list_of_timezones();
        $rez['99'] = get_string("serverlocaltime");
        return $rez;
    }
    
    /**
     * Обработать пришедшие из формы данные
     *
     * @return bool
     */
    public function process()
    {
        // Cоздаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        
        if ( $this->is_cancelled() )
        {// Форма отменена
            redirect($this->returnurl);
        }
        if ( $this->is_submitted() && confirm_sesskey() && 
             $this->is_validated() && $formdata = $this->get_data()
           )
        {// Сохранение данных
            $department = new stdClass();
            $address = new stdClass();
            // Данные о подразделении
            $department->name = trim($formdata->name);
            $department->code = trim(mb_strtolower($formdata->code,'utf-8'));
            $department->managerid = $formdata->managerid['id'];
            $department->leaddepid = $formdata->leaddepid;
            $department->zone = $formdata->zone;
            
            // Данные об адресе
            $address->type = '7';
            $address->postalcode = trim($formdata->postalcode);
            $address->country = $formdata->country[0];
            if (isset($formdata->country[1]))
            {
                $address->region = $formdata->country[1];
            } else
            {
                $address->region = NULL;
            }
            $address->county = trim($formdata->county);
            $address->city = trim($formdata->city);
            $address->streetname = trim($formdata->streetname);
            $address->streettype = $formdata->streettype;
            $address->number = trim($formdata->number);
            $address->gate = trim($formdata->gate);
            $address->floor = trim($formdata->floor);
            $address->apartment = trim($formdata->apartment);
            $address->latitude = (float)trim($formdata->latitude);
            $address->longitude = (float)trim($formdata->longitude);
            if ( $this->dof->storage('addresses')->is_exists($formdata->addressid) )
            {// Адрес существует
                // Обновление данных об адресе
                $aresult = $this->dof->storage('addresses')->update($address, $formdata->addressid);
                if ( empty($aresult) )
                {// Ошибка при сохранении адреса подраделения
                    $department->addressid = 0;
                    $this->errors[] = $this->dof->get_string('error_saveaddress', 'departments');
                } else 
                {// Сохранение прошло успешно
                    $department->addressid = $formdata->addressid;
                }
            } else
            {// Добавление адреса
                $aresult = $this->dof->storage('addresses')->insert($address);
                if ( empty($aresult) )
                {// Ошибка при сохранении адреса подраделения
                    $department->addressid = 0;
                    $this->errors[] = $this->dof->get_string('error_saveaddress', 'departments');
                } else
                {// Сохранение прошло успешно
                    $department->addressid = $aresult;
                }
            }
            
            if ( $formdata->id > 0 )
            {// Обновление данных о подразделении
                $result = $this->dof->storage('departments')->update($department, $formdata->id);
                if ( empty($result) )
                {// Ошибка при сохранении подраделения
                    $this->errors[] = $this->dof->get_string('error_savedepartment', 'departments');
                }
            } else 
            {// Создание нового
                if ( empty($department->code) )
                {// Автосоздание кода
                    $department->code = $this->generate_code($department->name);
                    if ( empty($department->code) )
                    {// Не удалось сгенерировать код
                        $this->errors[] = $this->dof->get_string('error_generatr_code', 'departments');
                    }
                }
                $result = $this->dof->storage('departments')->insert($department);
                if ( empty($result) )
                {// Ошибка при сохранении подраделения
                    $this->errors[] = $this->dof->get_string('error_savedepartment', 'departments');
                } else 
                {
                    $this->id = $result;
                }
            }
            if ( empty($this->errors) )
            {// Ошибок нет
                $url = $mform->getAttribute('action');
                if ( isset($formdata->submitclose) )
                {// Сохранение с возвратом на предыдущую страницу
                    $url = $this->returnurl;
                }
                $parseurl = parse_url($url);
                $query = explode('&', $parseurl['query']);
                $query[] = 'depsavesuccess=1';
                if ( isset($formdata->submit) )
                {// Сохранение 
                    $key = array_search('id=0', $query);
                    if ( ! empty($key) )
                    {// Найдено значение
                        unset($query[$key]);
                    }
                    $query[] = 'id='.$this->id;
                }
                $query = implode('&', $query);
                $url = $parseurl['path'].'?'.$query;
                redirect($url);
            }
        }
    }
    
    /**
     * Добавление сообщений о результатах работы формы
     */
    static function get_form_messages(dof_control $dof)
    {
        $param = optional_param('depsavesuccess', 0, PARAM_INT);
        if ( ! empty($param) )
        {// Сообщение об успешном сохранении
            $dof->messages->add($dof->get_string('message_depsavesuccess', 'departments'), 'message');
        }
    }
}

/**
 * Класс для вывода карточки структурного подразделения
 */
class dof_im_departments_card extends dof_im_edit
{
    
    /*
     * 
     */
    var $departmentid = 0;

    function definition()
    {
        $this->departmentid = $depid = optional_param('departmentid', 0, PARAM_INT);
        $addvars = array();
        $addvars['departmentid'] = $depid;
        $this->obj = $this->_customdata->obj;
        $this->dof = $this->_customdata->dof;
        // print_object($this->_customdata->obj);
        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        // заголовок
        $mform->addElement('header','formtitle',$this->get_departments_name($this->obj->id));
        // кодовое название
        $mform->addElement('html','&nbsp;&nbsp;&nbsp;'.$this->dof->get_string('code','departments').': '.$this->obj->code.'<br>');
        // имя подразделения
        $mform->addElement('html','&nbsp;&nbsp;&nbsp;'.$this->dof->get_string('name','departments').': '.
                                  $this->get_departments_name($this->obj->id, true).'<br>');
        // руководитель
        $mform->addElement('html','&nbsp;&nbsp;&nbsp;'.$this->dof->get_string('manager','departments').': '.
                           $this->get_manager_name($this->obj->managerid).'<br>');
        // вышестоящее подразделение
        $mform->addElement('html','&nbsp;&nbsp;&nbsp;'.$this->dof->get_string('leaddep','departments').': '.
                           $this->get_departments_name($this->obj->leaddepid).'<br>');                 
        // адрес подразделения
        $mform->addElement('html','&nbsp;&nbsp;&nbsp;'.$this->dof->get_string('departmentaddress','departments').': '.
                           $this->get_string_address($this->obj->addressid).'<br>');
        // часовой пояс
        $zone = $this->get_list_timezone();
        if ( ! isset($zone[$this->obj->zone]) )
        {//временная зона не установлена
            $timezone = '';
        }else
        {
            $timezone = $zone[$this->obj->zone];
        }
        $mform->addElement('html','&nbsp;&nbsp;&nbsp;'.$this->dof->get_string('zone','departments').': '. 
                           $timezone.'<br>');
        // статус - пока отсутствует
        // $mform->addElement('static', 'status', $this->dof->get_string('status','departments').': ');
        // дочерние подразделения
        $mform->addElement('header','formtitle',$this->dof->get_string('subordinated','departments'));
        $departments = $this->dof->storage('departments')->departments_list_subordinated($this->obj->id, $this->obj->depth,$this->obj->path,true);

        if ( $this->dof->storage('config')->get_limitobject('departments',$addvars['departmentid']) )
        {// ссылка на создание
            $mform->addElement('html','&nbsp;&nbsp;&nbsp;<a href='.$this->dof->url_im('departments',
                           '/edit.php?departmentid='.$depid.'&id=0').'>'.
                           $this->dof->get_string('newdepartment','departments').'</a><br><br>');
        }                 
        foreach ($departments as $id=>$department)
        {
            if ( $this->dof->storage('departments')->is_access('view', NULL, NULL, $id) )
            {// если есть право на просмотр
                    $department = '<a href='.$this->dof->url_im('departments','/view.php?departmentid='.$depid.'&id='.$id).'>'
                                  .$department.'</a>';
            }
            $mform->addElement('html','&nbsp;&nbsp;&nbsp;'.$department.'<br>');
        }
        // применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
        
    }
    
    /** Возвращает полное имя персоны
     * @param int $id - id персоны
     * @return string
     */
    private function get_manager_name($id)
    {
        $depid = optional_param('departmentid', 0, PARAM_INT);
        $addvars = array();
        $addvars['departmentid'] = $depid;
        if ( $this->dof->storage('persons')->is_exists($id) )
        {// если руководитель указан - выведем его имя
            $manager = $this->dof->storage('persons')->get_field($id,'sortname');
            if ( $this->dof->storage('persons')->is_access('view',$this->obj->id) )
            {
                $manager = '<a href='.$this->dof->url_im('persons','/view.php?id='.$this->obj->managerid,$addvars).'>'
                           .$manager.'</a>';
            }
            return $manager;
        }else
        {// не указан - так и напишем
            return $this->dof->get_string('nonespecify','departments');
        }
    }
    /** Возвращает имя подразделения
     * @param int $id - id подразделения
     * @param bool $edit - ссылка на редактирование, по умолчанию на просмотр
     * @return string
     */
    public function get_departments_name($id, $edit = false)
    {
        if ( $this->dof->storage('departments')->is_exists($id) )
        {// если департамент существует - выведем его имя
            $departmentname = $this->dof->storage('departments')->get_field($id,'name').' ['.
    	                      $this->dof->storage('departments')->get_field($id,'code').']';
            if ( $edit )
            {// если нам сказано передать ссылку на редактирование - передаем ее
                if ( $this->dof->storage('departments')->is_access('edit', NULL, NULL, $id) )
                {// если есть право на редактирование
                    $departmentname = '<a href='.$this->dof->url_im('departments','/edit.php?id='.$id.'&departmentid='.$this->departmentid).'>'
                                      .$departmentname.'</a>';
                }
            }else
            {// иначе ссылку на просмор
              
                if ( $this->dof->storage('departments')->is_access('view', $id, NULL, $id)  ) 
                {// если есть право на просмтр
                    $departmentname = '<a href='.$this->dof->url_im('departments','/view.php?departmentid='.$id).'>'
                                      .$departmentname.'</a>';
                }   
            } 
            return $departmentname;
        }else
        {// не существует - выведем надпись "Нет"
            return $this->dof->get_string('none','departments');
        }
    }
    
    /** Возвращает полный адрес подразделения
     * @param int $id - id адреса
     * @return string
     */
    private function get_string_address($id)
    {
        if ( ! $address = $this->dof->storage('addresses')->get($id) )
        {// адреса нет - так и напишем
            return $this->dof->get_string('nonespecify','departments');
        }else
        {// сформируем строчку адреса
            // регион
            $address->region = $this->dof->modlib('refbook')->region($address->country,$address->region);
            // улица
            $address->street = $address->streettype.'&nbsp;'.$address->streetname;
            // элементы адреса
            $mas = array('region','postalcode','city','street','number');
            $str = '';
            // перечислим элементы адреса через запятую
            foreach ($mas as $value)
            {
                if ( isset($address->$value) AND ( ! empty($address->$value) ) AND ( $address->$value <> '&nbsp;' ) )
                {// элемент присутствует - включим его в строчку и поставим запятую
                    $str .= $address->$value.', ';
                }
            }
            // уберем лишнюю последнюю запятую и вернем строчку
            return substr($str, 0, -2);
        }
    }
    
}

?>