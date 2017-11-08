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

/** Класс для создания стандартного двустороннего списка "добавить/удалить"
 * 
 */
class dof_modlib_widgets_addremove
{
    /**
     * @var dof_control
     */
    protected $dof;
    
    /**
     * @var string - id формы на странице. Используется javascript-функцией getElementById
     */
    protected $formid;
    
    /**
     * @var string - страница обработчика формы
     */
    protected $action;
    
    /**
     * @var служебные html-настройки элемента: например ширина select-столбцов
     */
    protected $options;
    
    /**
     * @var object - объект содержащий стандартные строки перевода для формы
     */
    protected $formstrings;
    
    /**
     * @var array - список записей для добавления
     */
    protected $addlist;
    
    /**
     * @var array - список записей для удаления
     */
    protected $removelist;
    
    protected $addgroup;
    protected $removegroup;
    
    /** Конструктор класса, устанавливает связь с переменной $DOF и определяет id формы
     * 
     * @param dof_control $DOF
     * @param string $action - страница обработчика формы
     * @param string $formid[optional] - id формы на странице. Используется javascript-функцией getElementById
     */
    function __construct(dof_control $DOF, $action='', $formid='dof_modlib_widgets_addremove')
    {
        $this->dof    = $DOF;
        // устанавливаем переданный id формы
        $this->formid = $formid;
        // и переданный адрес обработчика
        $this->action = $action;
        // устанавливаем руссификацию по умолчанию
        $this->formstrings = $this->get_form_default_strings();
        // устанавливаем html-настройки по умолчанию
        $this->options     = $this->get_form_default_options();
    }
    
    /** Установить описания значений формы по умолчанию
     * 
     * @return bool
     * @param object $values - значения по умолчанию для описания формы
     * @todo описать в комментариях назначение каждого поля
     * @todo обрабатывать все значения из values через htmlspecialchars
     */
    public function set_default_strings($values)
    {
        if ( ! is_object($values) )
        {// неправильный формат данных
            return false;
        }
        // заменяем стандартные строки синхронизации присланными
        $this->formstrings = dof_object_merge($this->get_form_default_strings(),$values);
        return true;
    }
    
    /** Установить параметры элемента - ширину полей для выборки и т. п.
     * 
     * @return bool
     * @param object $options - объект со значениями по умолчанию для формы
     *                     Список возможных полей:
     *                     $options->columswidth = '250px' - ширина select-стобцов 
     */
    public function set_default_options($options)
    {
        if ( ! is_object($options) )
        {// неправильный формат данных
            return false;
        }
        
        // заменяем стандартные настройки присланными
        $this->options = dof_object_merge($this->get_form_default_options(),$options);
        return true;
    }
    
    protected function get_form_default_options()
    {
        $options = new stdClass();
        // устанавливаем ширину обоих столбцов
        $options->columswidth = '100%';
        
        return $options;
    }
    
    protected function get_form_default_extradata()
    {
        $extradata = new stdClass();
        // устанавливаем ширину обоих столбцов
        $extradata->maxelementlength = 35;
        
        return $extradata;
    }
    
    /** Получить стандартные надписи по умолчанию для всей формы 
     * 
     * @return object - объект со стандартными значениями по умолчанию
     * @todo сделать какие-нибудь надписи для списка добавляемых и удаляемых предметов по умолчанию
     */
    protected function get_form_default_strings()
    {
        $defaults = new stdClass();
        // устанавливаем для формы id, переданный в конструкторе
        $defaults->formid      = $this->formid;
        // устанавиливаем страницу обработчика формы
        $defaults->action      = $this->action;
        // надпись над добавленными элементами
        $defaults->addlabel    = '';
        // надпись над удаленными элементами.
        $defaults->removelabel = '';
        // надпись на стрелочке "добавить"
        $defaults->addarrow    = '';
        // надпись на стрелочке "удалить"
        $defaults->removearrow = '';
        return $defaults;
    }
    
    /** Получить стандартные строки русификации для поиска 
     * 
     * @return object - объект со стандартными строками перевода
     */
    protected function get_search_default_strings()
    {
        $defaults = new stdClass();
        $defaults->searchlabel  = $this->dof->modlib('ig')->igs('search');
        $defaults->searchtext   = '';
        $defaults->searchbutton = $this->dof->modlib('ig')->igs('find');
        $defaults->showall      = $this->dof->modlib('ig')->igs('show_all');
        
        return $defaults;
    }
    
    /** Добавить список записей которые будут добавляться
     * 
     * @return bool 
     * @param array $options - массив записей для добавления в формате array('id' => 'отображаемое имя')
     * @param array $extradata - массив дополнительных настроек для каждого элемента (например стиль и 
     * атрибуты тега option). Используется, если нужно настроить внешний вид списка, или нескольких элементов
     * 
     * @todo доделать обработку стилей элементов
     */
    public function set_add_list($options, $extradata=null)
    {
        if ( is_null($extradata) )
        {// передано null значение - преобразуем в объект
            $extradata = new stdClass();
        }
        $extradata = dof_object_merge($this->get_form_default_extradata(),$extradata);
        if ( ! $list = $this->get_simple_options_template_data($options,$extradata) )
        {// неправильный формат данных, они не добавились
            return false;
        }
        // добавляем данные в нужном формате в массив
        $this->addlist = $list;
        return true;
    }
    
    /** Добавить список записей которые будут удаляться
     * 
     * @return 
     * @param array $options - массив записей для добавления в формате array('id' => 'отображаемое имя')
     * @param array $extradata - массив дополнительных настроек для каждого элемента (например стиль и 
     * атрибуты тега option). Используется, если нужно настроить особый внешний вид списка, или нескольких элементов
     * 
     * @todo доделать обработку стилей элементов
     */
    public function set_remove_list($options, $extradata=null)
    {
        if ( is_null($extradata) )
        {// передано null значение - преобразуем в объект
            $extradata = new stdClass();
        }
        $extradata = dof_object_merge($this->get_form_default_extradata(),$extradata);
        if ( ! $list = $this->get_simple_options_template_data($options,$extradata) )
        {// неправильный формат данных, они не добавились
            return false;
        }
        // добавляем данные в нужном формате в массив
        $this->removelist = $list;
        return true;
    }
    
    /** Получить данные для объекта templater для простого массива добавления/удаления элементов
     * 
     * @return 
     * @param array $options - массив записей для добавления в формате array('id' => 'отображаемое имя')
     * @param array $extradata - массив дополнительных настроек для каждого элемента (например стиль и 
     * атрибуты тега option). Используется, если нужно настроить особый внешний вид списка, или нескольких элементов
     * 
     * @todo проработать использование стилей и дополнительных опций
     * @todo отработать ситуацию с пустым массивом результатов
     */
    protected function get_simple_options_template_data($options, $extradata=null)
    {
        if ( ! is_array($options) )
        {// неправильный формат данных
            return false;
        }
        $result = array();
        foreach ( $options as $id=>$option )
        {// из каждого элемента массива делаем элемент для templater'a
            $element = new stdClass();
            $element->id          = $id;
            $element->fullname    = htmlspecialchars($option);
            $element->name        = $element->fullname;
            if ( !empty($extradata->elements[$id]) )
            {// добавляем к элементу значения из $extradata
                $element          =  dof_object_merge($element,$extradata->elements[$id]);
            }
            if ( isset($extradata->maxelementlength) AND mb_strlen($element->name,'utf-8') > $extradata->maxelementlength )
            {// если полное название элемента превышает допустимое значение - обрезаем его
                $element->name    = mb_substr($element->name,0,$extradata->maxelementlength-1,'utf-8').'...';
            }
            $result[$element->id] = $element;
        }
        return $result;
    }
    
    /** Добавить список записей которые будут добавляться
     * 
     * @return 
     * @param array $options - массив объектов, записей для добавления в формате 
     *     $object->name      = 'название категории'
     *     $object->options[] = массив записей в формате array('id' => 'отображаемое имя')
     */
    public function set_complex_add_list($options)
    {
        if ( ! $list = $this->get_complex_options_template_data($options, 'add') )
        {// неправильный формат данных, они не добавились
            return false;
        }
        // добавляем данные в нужном формате в массив
        $this->addgroup = $list;
        return true;
    }
    
    /** Добавить список записей которые будут удаляться
     * 
     * @return 
     * @param array $options - массив объектов, записей для добавления в формате 
     *     $object->name      = 'название категории'
     *     $object->options[] = массив записей в формате array('id' => 'отображаемое имя')
     */
    public function set_complex_remove_list($options)
    {
        if ( ! $list = $this->get_complex_options_template_data($options, 'remove') )
        {// неправильный формат данных, они не добавились
            return false;
        }
        // добавляем данные в нужном формате в массив
        $this->removegroup = $list;
        return true;
    }
    
    /** Получить данные для объекта templater для сложного массива добавления/удаления элементов
     * 
     * @return 
     * @param array $groups - массив объектов, записей для добавления в формате 
     *     $object->name      = название категории записей (для тега <optgroup>)
     *     $object->options[] = массив записей в формате array('id' => 'отображаемое имя')
     * 
     * @todo более внимательно проверить массив объектов на соответствие формату данных
     * @todo предусмотреть случай нескольких уровней вложенности
     * @todo добавить возможность дополнительного форматирования
     */
    protected function get_complex_options_template_data($groups, $type, $extradata=null)
    {
        if ( is_null($extradata) )
        {// передано null значение - преобразуем в объект
            $extradata = new stdClass();
        }
        $extradata = dof_object_merge($this->get_form_default_extradata(),$extradata);
        if ( ! is_array($groups) )
        {// неправильный формат данных
            return false;
        }
        $result = array();
        foreach ( $groups as $groupdata )
        {// перебираем все добавляемые варианты
            $group           = new stdClass();
            
            // приводим массив к нужному для select-элемента виду
            if ( $type == 'add' )
            {
                // получаем название группы
                $group->addfullname = htmlspecialchars($groupdata->name);
                $group->addname     = $group->addfullname;
                if ( isset($extradata->maxelementlength) AND mb_strlen($group->addname,'utf-8') > $extradata->maxelementlength )
                {// если полное название элемента превышает допустимое значение - обрезаем его
                    $group->addname = mb_substr($group->addname,0,$extradata->maxelementlength-1,'utf-8').'...';
                }
                $group->addelements = $this->get_simple_options_template_data($groupdata->options,$extradata);
            }else
            {
                $group->removefullname = htmlspecialchars($groupdata->name);
                $group->removename     = $group->removefullname;
                if ( isset($extradata->maxelementlength) AND mb_strlen($group->removename,'utf-8') > $extradata->maxelementlength )
                {// если полное название элемента превышает допустимое значение - обрезаем его
                    $group->removename = mb_substr($group->removename,0,$extradata->maxelementlength-1,'utf-8').'...';
                }
                $group->removeelements = $this->get_simple_options_template_data($groupdata->options,$extradata);
            }
            
            // добавляем группу в итоговый массив для шаблонизатора
            $result[] = $group;
        }
        return $result;
    }
    
    /** Получить объект для использования в шаблоне формы
     * 
     * @return object
     * @todo доделать поиск
     * @todo разобраться с тем, чтобы стрелочки в разных браузерах выглядели корректно
     */
    protected function assemble_templater_data()
    {
        // создаем объект, который будет использоваться в шаблоне
        $template = new stdClass();
        // записываем в форму строки перевода
        $template = dof_object_merge($template, $this->formstrings);
        // записываем в форму html-параметры отображения
        $template = dof_object_merge($template, $this->options);
        // добавляем в форму список тех, кого будем удалять
        $template->removelist  = $this->removelist;
        // добавляем в форму список тех, кого будем добавлять
        $template->addlist     = $this->addlist;
        // то же самое проделываем и для случаем со сложными списками
        $template->addgroup    = $this->addgroup;
        $template->removegroup = $this->removegroup;
        
        return $template;
    }
    
    /** Проверить данные из массива формы добавления/удаления предметов
	 * 
	 * @param array $data
	 * @return array - массив с проверенными безопасными данными
	 */
	public function check_add_remove_array($data)
	{
		$result = array();
		if ( ! is_array($data) )
		{// переданы неверные данные
			return false;
		}
		foreach ($data as $item)
		{// перебираем весь список идентификаторов, и приводим его к нормальному виду
			if ( ! is_numeric($item) )
			{// если значение не числовое - пропустим его и не внесем в итоговый массив
				continue;
			}
			// если значение числовое - запишем его в итоговый массив
			$result[] = intval($item);
		}
		
		return $result;
	}
    
    /** Распечатать элемент на странице
     * @param object $usertemplate - пользовательские данные для вставки в шаблон
     * @return null
     */
    public function print_html($usertemplate=null)
    {
        print($this->get_html($usertemplate));
    }
    
    /** Получить html-код элемента
     * @param object $usertemplate - пользовательские данные для вставки в шаблон
     * @return string
     */
    public function get_html($usertemplate=null)
    {
        if ( is_null($usertemplate) )
        {// передано null значение - преобразуем в объект
            $usertemplate = new stdClass();
        }
        // получаем данные для шаблона
        $data = $this->assemble_templater_data();
        // к пользовательским данным, добавляем основные
        $data = dof_object_merge($usertemplate,$data);
        // Создаем объект документа 
        $templater = $this->dof->modlib('templater')->template('modlib', 'widgets', $data, 'addremove');
        // возвращаем html-код формы
        return $templater->get_file('html');
    }
}


?>