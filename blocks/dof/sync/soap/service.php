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
// Copyright (C) 2008-2999  Nikolay Konovalov (Николай Коновалов)         //
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
// подключаем библиотеки верхнего уровня
require_once(dirname(realpath(__FILE__)) . '/../../lib.php');
//ini_set("soap.wsdl_cache_enabled", "0");

/**
 * Класс для обработки soap-запросов 
 */
class blocks_dof_sync_soap_soapserver
{

    public function __construct()
    {
        global $DOF;
        $this->dof = $DOF;
    }

    /** Создать мета-контракт и привязать его к переданному внешнему id,
     *  обновить название мета-контракта (контрагента)
     * 
     * @param object $input - данные метаконтракта, переданные по SOAP:
     *      ->requestlogin - Идентификатор системы-отправителя запроса
     *      ->requesttime - Время генерации запроса
     *      ->requesthash - sha1-хеш
     *      ->id - Внешний id метаконтракта
     *      ->num - Номер метаконтракта
     *      ->departmentcode - Код подразделения
     *      ->cov [optional] - Дополнительный массив cov, содержащий дополнительные поля к объекту
     * @return object - объект содерщаший поля
     *       ->id - переданный в запросе id объекта
     *       ->dofid - внутренний id объекта
     *       ->modified - время изменения объекта
     *       ->hash - контрольная сумма из storages/sync
     *       ->errorcode - код ошибки
     */
    public function set_meta_contract($input)
    {
        return $this->dof->sync('soap')->set_method($input, __FUNCTION__);
    }

    /** Создать персону и привязать её к внешнему id, обновить персону
     * 
     * @param object $input - данные персоны, пришедшей к нам по soap
     *      ->requestlogin - Идентификатор системы-отправителя запроса
     *      ->requesttime - Время генерации запроса
     *      ->requesthash - sha1-хеш
     *      ->id - Внешний id метаконтракта
     *      ->firstname - Имя
     *      ->middlename - Отчество
     *      ->lastname - Фамилия
     *      ->preferredname - Префикс для имения (Mr. Dr. Г-н, Г-а)
     *      ->dateofbirth - Дата рождения в UTS
     *      ->gender - Пол (male, female, unknown)
     *      ->email - Основной адрес электронной почты
     *      ->phonehome - Домашний телефон
     *      ->phonework - Рабочий телефон
     *      ->phonecell - Сотовый телефон
     *      ->passtypeid - Тип удостоверения личности (1 - свидетельство о рождении, 2 - паспорт гражданина РФ, 3 - загранпасспорт, 4 - разрешение на временное проживание лица без гражданства, 5 - вид на жительство, 6 - военный билет, 7 - водительсткое удостоверение пластиковое, 8 - вод. удостоверение форма 1, 9 - вод. удостоверение международное)
     *      ->passportserial - Серия удостоверения личности (если предусмотрена типом документа)
     *      ->passportnum - Номер удостоверения личности
     *      ->passportdate - Дата выдачи удостоверения личности в UTS
     *      ->passportem - Название организации, выдавшей удостоверение личности
     *      ->citizenship - Гражданство
     *      ->departmentcode - Основной отдел, к которому приписан человек (может редактировать его данные в persons)
     *      ->about - Характеристика личности
     *      ->skype - Уникальный идентификатор в Skype
     *      ->phoneadd1 - Дополнительный телефон 1
     *      ->phoneadd2 - Дополнительный телефон 2
     *      ->phoneadd3 - Дополнительный телефон 3
     *      ->emailadd1 - Дополнительная электронная почта 1
     *      ->emailadd2 - Дополнительная электронная почта 2
     *      ->emailadd3 - Дополнительная электронная почта 3
     *      ->passportaddr - Адрес прописки по паспорту (для генерации документов)
     *      ->address - Текущий адрес (почтовый адрес)
     *      ->birthaddress - Адрес рождения персоны
     *      ->cov [optional] - Дополнительный массив cov, содержащий дополнительные поля к объекту
     * @return object - объект содерщаший поля
     *       ->id - переданный в запросе id объекта
     *       ->dofid - внутренний id объекта
     *       ->modified - время изменения объекта
     *       ->hash - контрольная сумма из storages/sync
     *       ->errorcode - код ошибки
     */
    public function set_person($input)
    {
        return $this->dof->sync('soap')->set_method($input, __FUNCTION__);
    }

    /** Создать контракт для персоны по её внешнему id, номеру и дате
     * 
     * @param object $input - данные контракта, пришедшего к нам по soap
     *      ->requestlogin - Идентификатор системы-отправителя запроса
     *      ->requesttime - Время генерации запроса
     *      ->requesthash - sha1-хеш
     *      ->id - Внешний id контракта
     *      ->date - Дата заключения в UTS
     *      ->sellerid - Менеджер по работе с клиентами (приемная комиссия, партнер) - добавляет договор, меняет статус до "подписан клиентом", отслеживает статус договора и ход обучения (id по таблице persons)
     *      ->clientid - Клиент, оплачивающий обучение (законный представитель, сам совершеннолетний ученик или куратор от организации, может принимать значение 0 или null, если клиент создается, а контракт имеет черновой вариант) (по таблице persons)
     *      ->studentid - Ученик (может принимать значение 0, если ученик создается, а контракт имеет черновой вариант) (по таблице persons)
     *      ->notes - Заметки
     *      ->departmentcode - Подразделение в таблице departments , к которому приписан контракт на обучение (например, принявшее ученика)
     *      ->curatorid - Куратор или классный руководитель данного ученика (по таблице persons или не указан), отслеживает учебный процесс, держит связь с учеником, является посредником между учеником и системой, может быть внешней персоной.
     *      ->metacontractid - id метаконтракта, к которому привязан договор, в таблице metacontracts 
     *      ->cov [optional] - Дополнительный массив cov, содержащий дополнительные поля к объекту
     * @return object - объект содерщаший поля
     *       ->id - переданный в запросе id объекта
     *       ->dofid - внутренний id объекта
     *       ->modified - время изменения объекта
     *       ->hash - контрольная сумма из storages/sync
     *       ->errorcode - код ошибки
     */
    public function set_contract($input)
    {
        return $this->dof->sync('soap')->set_method($input, __FUNCTION__);
    }
}
// создаем объект soap-сервера, который будет обрабатывать запросы
$server = new SoapServer($DOF->url_sync('soap', '/soap.php?do=wsdl'));
// регистрируем в сервере все методы класса-обработчика
$server->setClass('blocks_dof_sync_soap_soapserver');
// обрабатываем запрос
$server->handle();
?>