<?PHP
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



/** Здесь перечисляется минимальный набор методов, 
 * которые должен иметь любой плагин типа workflow
 */
interface dof_workflow extends dof_plugin
{
	/** Возвращает код справочника, в котором хранятся отслеживаемые объекты
     * @return string
     * @access public
	 */
	public function get_storage();
    /** Возвращает массив всех состояний,   
     * в которых может находиться экземпляр объекта,
     * обрабатываемый этим плагином
     * @return array
     * @access public
     */
    public function get_list();
    /** Возвращает имя статуса
     * @param string status - название состояния
     * @return string
     * @access public
     */
    public function get_name($status);  
    /** Возвращает массив состояний,
     * в которые может переходить объект 
     * с указанным id из текущего состояния  
     * @param int id - id экземпляра
     * @return mixed array - массив возможных состояний или false
     * @access public
     */
    public function get_available($id);
    /** Переводит экземпляр объекта с указанным id в переданное состояние
     * @param int id - id экземпляра
     * @param string status - название состояния
     * @return boolean true - удалось перевести в указанное состояние, 
     * false - не удалось перевести в указанное состояние
     * @access public
     */
    public function change($id, $status,$opt=null);
    /** Инициализируем состояние объекта
     * @param int id - id экземпляра
     * @return boolean true - удалось инициализировать состояние объекта 
     * false - не удалось перевести в указанное состояние
     * @access public
     */
    public function init($id);
}
?>
