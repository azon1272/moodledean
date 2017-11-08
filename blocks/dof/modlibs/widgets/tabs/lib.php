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

/** Класс для создания вкладки. Используется при составлении строки вкладок в modlib/widgets
 * Служит оберткой для класса Moodle, и используется для того чтобы в коде всех остальных 
 * плагинов не создавать объекты от класса, находящегося в moodle
 * Кроме этого - добавляет совместимость с PHP 5.3.x (добавлен метод __construct)
 * 
 * В класс могут быть добавлены новые методы, если будет нужно расширить функциональность
 * вкладок moodle.
 * 
 */
class dof_modlib_widgets_tabobject extends tabobject implements renderable
{
    /** Конструктор. Добавлен для совместимости с PHP 5.3
     *
     * @param string $id - уникальное имя вкладки в строке. Только латинские буквы
     * @param string $link[optional] - Ссылка, куда ведет вкладка
     * @param string $text[optional] - Название вкладки 
     * @param string $title[optional] - Всплывающая подсказка, отображается при наведении мыши на вкладку
     * @param bool $linkedwhenselected[optional] - показывать ссылку на вкладку, если она уже выбрана.
     *             true - показывать
     *             false - не показывать
     *  
     */    
    public function __construct($id, $link='', $text='', $title='', $linkedwhenselected=false)
    {
        parent::__construct($id, $link, $text, $title, $linkedwhenselected);
    }
    
    /** Получение объекта родительского класса
     * 
     * @param string $id - уникальное имя вкладки в строке. Только латинские буквы
     * @param string $link[optional] - Ссылка, куда ведет вкладка
     * @param string $text[optional] - Название вкладки 
     * @param string $title[optional] - Всплывающая подсказка, отображается при наведении мыши на вкладку
     * @param bool $linkedwhenselected[optional] - показывать ссылку на вкладку, если она уже выбрана.
     *             true - показывать
     *             false - не показывать
     */
    public static function get_tabobject($id, $link='', $text='', $title='', $linkedwhenselected=false)
    {
        return new tabobject($id, $link, $text, $title, $linkedwhenselected);
    }
}
?>