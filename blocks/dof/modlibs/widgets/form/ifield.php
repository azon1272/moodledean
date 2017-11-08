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

/** Элемент для редактирования одного поля в таблице, списке, или просто на странице
 * @todo добавить возможность указывать разные типы элементов
 * @todo добавить возможность использования select
 * @todo добавить перевод строк
 * @todo исправить потерю иконки при редактировании
 * 
 */
class dof_modlib_widgets_ifield
{
    /**
     * @var dof_control
     */
    protected $dof;
    /**
     * @var int - id редактируемого в БД объекта
     */
    protected $objectid;
    /**
     * @var string уникальное имя запроса внутри плагина
     */
    protected $queryname;
    /**
     * @var string - тип плагина, который должен предоставить данные в ответ на запрос
     */
    protected $plugintype;
    /**
     * @var string - код плагина, который должен предоставить данные в ответ на запрос
     */
    protected $plugincode;
    /**
     * @var string html-параметр id для div-элемента содержащего весь ifield полностью
     */
    protected $elementid;
    /**
     * @var string html-параметр id для div-элемента содержащего редактируемый текст
     */
    protected $textid;
    /**
     * @var string html-параметр id для img-элемента иконки редактирования
     */
    protected $iconid;
    /**
     * @var string тип input-элемента, который будет отображаться при редактировании
     *             возможные значения: text, textarea, select
     */
    protected $type = 'text';
    /**
     * @var string текст, который отображается до редактирования
     */
    protected $text;
    /**
     * @var string строка дополнительных html-параметров тега div, который содержит элемент inline-редактирования
     */
    protected $options;
    
    /** 
     * @param dof_control $dof
     * @param string $plugintype - тип плагина, который предоставляет данные для поля
     * @param string $plugincode - код плагина, который предоставляет данные для поля
     * @param string $queryname - название запроса внутри плагина
     * @param int    $objectid - id редактируемого элемента
     * @param string $type - html-тип input-элемента
     * @param string $text - текст, который отображается вместо элемента, пока не нажата кнопка "редактировать"
     *                       Разрешено html-форматирование
     * @param string|array $options - Массив или строка дополнительных html-параметров для элемента
     */
    public function __construct(dof_control $dof, $plugintype, $plugincode, $queryname, $objectid, $type, $text, $options=null)
    {
        $this->dof = $dof;
        $_options = array();
        $_options['objectid']   = $objectid;
        $_options['queryname']  = $queryname;
        $_options['plugintype'] = $plugintype;
        $_options['plugincode'] = $plugincode;
        $_options['type']       = $type;
        $_options['text']       = $text;
        $_options['options']    = $options;
        
        // устанавливаем все значения по умолчанию
        $this->set_options($_options);
        
        // подключаем все JS-библиотеки, если мы этого еще не сделали
        $this->dof->modlib('widgets')->js_init('ifield');
    }
    
    /** Получить html-код элемента одной строкой
     * @param int $objectid - id редактируемого объекта
     * @param string $text - текст, который отображается вместо элемента, пока не нажата кнопка "редактировать"
     *                       Разрешено html-форматирование
     * @param string|array $options - Массив или строка дополнительных html-параметров для элемента
     * 
     * @return string
     */
    public function get_html($objectid=null, $text=null, $options=null)
    {
        $html = '';
        
        $_options = array();
        $_options['objectid'] = $objectid;
        $_options['text']     = $text;
        $_options['options']  = $options;
        // устанавливаем все значения по умолчанию, и пересчитываем все что надо
        $this->set_options($_options);
        
        // получаем адрес запроса к json.php
        $submiturl = $this->dof->url_modlib('widgets', '/json.php', $this->get_save_params());
        // получаем адрес для подгрузки данных
        $loadurl   = $this->dof->url_modlib('widgets', '/json.php', $this->get_load_params());
        
        // скрипт, который делает поле редактируемым
        $html .= "<script>
        \$(document).ready(
            function() {
            \$('#".$this->iconid."').click(function ()
                {
                    dof_modlib_widgets_ifield_set_editable('".
                        $this->elementid."', '".$this->type."', '".$this->textid."', '".$submiturl."', '".$loadurl."')
                });
        });";
        $html .= "</script>";
        
        // сам код элемента
        $html .= '<div id="'.$this->elementid.'" '.$this->options.'>';
        // текст элемента до нажатия иконки "редактировать"
        $html .= '<div id="'.$this->textid.'">'.$this->text.'</div>';
        // иконка редактирования
        $html .= $this->dof->modlib('ig')->icon('edit', '#', array('id'=>$this->elementid.'_icon'));
        $html .= '</div>';
        
        return $html;
    }
    
    /** Задать параметры элемента
     * @param array $options - массив свойств элемента в формате ключ-зачение
     * 
     * @return bool
     */
    public function set_options($options)
    {
        if ( ! is_array($options) )
        {
            return false;
        }
        
        foreach ( $options as $name=>$value )
        {
            if ( $name == 'options' AND $value )
            {// свойства тега, заданные массивом, приводим к строке
                $this->options = dof_transform_tag_options($value);
                continue;
            }
            if ( $name == 'objectid' AND $value )
            {// создаем id для всех div и img-элементов
                $this->objectid  = $value;
                $this->elementid = 'dof_modlib_widgets_ifield_'.$this->objectid;
                $this->iconid    = $this->elementid.'_icon';
                $this->textid    = $this->elementid.'_text';
                continue;
            }
            if ( $name == 'elementid' AND $value )
            {// при изменении или ручном задании html-id для элемента нужно также переписать id иконки редактирования
                // и текста
                $this->elementid = $value;
                $this->iconid    = $this->elementid.'_icon';
                $this->textid    = $this->elementid.'_text';
                continue;
            }
            
            if ( $value )
            {// все остальные элементы сохраняем как есть
                $this->$name = $value;
            }
        }
        
        return true;
    }
    
    /** Отобразить элемент
     * @param int $objectid - id редактируемого объекта
     * @param string $text - текст, который отображается вместо элемента, пока не нажата кнопка "редактировать"
     *                       Разрешено html-форматирование
     * @param string|array $options - Массив или строка дополнительных html-параметров для элемента
     * 
     * @return null
     */
    public function display($objectid=null, $text=null, $options=null)
    {
        echo $this->get_html($objectid, $text, $options);
    }
    
    /** Получить список параметров для запроса к json.php (сохранение данных)
     * 
     * @return array
     */
    protected function get_save_params()
    {
        $params = array(
            'type'       => 'savefield',
            'plugintype' => $this->plugintype,
            'plugincode' => $this->plugincode,
            'querytype'  => $this->queryname,
            'objectid'   => $this->objectid,
            'sesskey'    => sesskey());
        
        return $params;
    }
    
    /** Получить список параметров для запроса к json.php (загрузка данных)
     * 
     * @return array
     */
    protected function get_load_params()
    {
        $params = array(
            'type'       => 'loadfield',
            'plugintype' => $this->plugintype,
            'plugincode' => $this->plugincode,
            'querytype'  => $this->queryname,
            'objectid'   => $this->objectid,
            'sesskey'    => sesskey());
        
        return $params;
    }
}
?>