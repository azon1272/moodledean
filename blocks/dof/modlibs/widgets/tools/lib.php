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

/** Класс, реализующий панель инструментов
 * 
 */
class dof_modlib_widgets_tools
{
    /** Глобальный объект $DOF
     * @var dof_control
     */
    protected $dof;
    
    /** 
     * @var string - код панели инструментов
     */
    protected $toolbar;
    
    /** Конструктор класса. Получаем доступ к переменной $DOF 
     * 
     * @return dof_control
     * @param dof_control $dof
     */
    function __construct($dof)
    {
        $this->dof &= $dof;
    }
    
    /** Получить код иконки с указанными параметрами
     * 
     * @return string|bool - строка с кодом иконки, или false если такой иконки не нашлось
     * @param string $name - название иконки (латинскими буквами, например: edit)
     * @param[optional] string $pluginname - название im-плагина, для которого предназначена иконка, 
     *                             или null, если иконка из стандартного набора
     * @param[optional] int $size - размер иконки. Допустимые значения:
     *                       16 - 16х16 пикселей
     *                       48 - 48х48 пикселей
     *                       128 - 128х128 пикселей
     * @param[optional] string $link - Ссылка на страницу, если иконка является ссылкой.
     * @param[optional] string $title - всплывающая подсказка для иконки
     * @param[optional] string $notice - текстовое пояснение под иконкой
     */
    public function get_icon($name, $pluginname=null, $size=16, $link=null, $title=null, $notice=null)
    {
        
    }
    
    /** Вернуть получившуюся панель инструментов
     * 
     * @return string - html-код панели инструментов с иконками
     */
    public function get_toolbar()
    {
        return $this->toolbar;
    }
    
    /** Распечатать панель инструментов
     * 
     * @return null
     */
    public function display_toolbar()
    {
        print($this->get_toolbar());
    }
}
?>