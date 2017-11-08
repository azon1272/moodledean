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
 * которые должен иметь любой плагин типа storage
 */
interface dof_plugin_storage extends dof_plugin
{
    /** Вставляет запись в таблицу(ы) плагина 
     * @param object dataobject 
     * @param bool quiet - не генерировать событий
     * @return mixed bool false если операция не удалась или id вставленной записи
     * @access public
     */
    public function insert($dataobject,$quiet=false);
    /** Удаляет запись с указанным id
     * @param int id - id записи в таблице
     * @param bool quiet - не генерировать событий 
     * @return boolean true если запись удалена или ее нет;
     * false в остальных случаях
     * @access public
     */
    public function delete($id,$quiet=false);
    /** Обновляет запись данными из объекта.
     * Отсутствующие в объекте записи не изменяются.
     * Если id передан, то обновляется запись с переданным id.
     * Если id не передан обновляется запись с id, который передан в объекте
     * @param object dataobject - данные, которыми надо заменить запись в таблице 
     * @param int id - id обновляемой записи
     * @param bool quiet - не генерировать событий
     * @return boolean true если обновление прошло успешно и false во всех остальных случаях
     * @access public
     */
    public function update($dataobject,$id = NULL,$quiet=false);
    /** Возвращает запись с указанным id
     * @param int id - id записи в таблице 
     * @return mixed object - запись из таблицы или false в остальных случаях
     * @access public
     */
    public function get($id);
    /** Возвращает один объект, которые удовлетворяют заданным критериям
     * @param string field1 - название первого поля поиска 
     * @param mixed value1 - значение, которое ищется в первом поле
     * @param string field2 - название второго поля поиска
     * @param mixed value2 - значение, которое ищется во втором поле
     * @param string field3 - название третьего поля поиска
     * @param mixed value3 - значение, которое ищется в третьем поле 
     * любая переменная $valueX может содержать как одно значение 
     * так и массив значений, которые ищутся в соответствующем  поле.
     * @param string fields - поля, которые должны быть возвращены, разделенные запятыми
     * @return mixed object - объект с указанными полями, если нашлась запись,
     *  удовлетворяюшая всем трем критериям поиска или false 
     * @access public
     */
    public function get_filter($field1 = '', $value1 = '', $field2 = '', $value2 = '', 
                               $field3 = '', $value3 = '', $fields = '*');
    /** Возвращает массив объектов, удовлетворяющих нескольким значениям одного поля
     * @param string field1 - название поля для поиска
     * @param mixed value1 - может содержать как одно значение, 
     * так и массив значений, которые ищутся в указанном поле
     * @param string sort - в каком направлении и по каким полям производится сортировка
     * @param string fields поля, которые надо возвратить
     * @param int limitfrom - id, начиная с которого надо искать
     * @param int limitnum максимальное количество записей, которое надо вернуть
     * @return mixed массив объектов если что-то нашлось или false
     * @access public
     */
     //раньше было list - переименовал, так как это служебное слово
    public function get_list($field = '', $value = '', $sort = '', 
                             $fields = '*', $limitfrom = '', $limitnum = '');
    /** Проверяет наличие записи в таблице
     * @param int id - id проверяемой записи
     * @return boolean true - запись найдена, false - запись не найдена
     * @access public
     */
    public function is_exists($id);
    /** Проверяет наличие в таблице записи, которая ищется по критериям
     * @param string field1 - название первого поля поиска 
     * @param mixed value1 - значение, которое ищется в первом поле
     * @param string field2 - название второго поля поиска
     * @param mixed value2 - значение, которое ищется во втором поле
     * @param string field3 - название третьего поля поиска
     * @param mixed value3 - значение, которое ищется в третьем поле
     * любая переменная $valueX может содержать как одно значение 
     * так и массив значений, которые ищутся в соответствующем  поле.
     * @return boolean true - запись найдена, false - запись не найдена
     * @access public
     */
    public function is_exists_filter($field1 = '', $value1 = '', $field2 = '', 
                                     $value2 = '', $field3 = '', $value3 = '');
    /** Подсчитывает количество записей, найденных по критериям
     * @param string field1 - название первого поля поиска 
     * @param mixed value1 - значение, которое ищется в первом поле
     * @param string field2 - название второго поля поиска
     * @param mixed value2 - значение, которое ищется во втором поле
     * @param string field3 - название третьего поля поиска
     * @param mixed value3 - значение, которое ищется в третьем поле
     * любая переменная $valueX может содержать как одно значение 
     * так и массив значений, которые ищутся в соответствующем  поле.
     * @return mixed
     * @access public
     */
    public function count($field1 = '', $value1 = '', $field2 = '', 
                          $value2 = '', $field3 = '', $value3 = '');
}
?>
