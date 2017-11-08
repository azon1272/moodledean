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
 * Интерфейс управления участниками учебного процесса. Языковые переменные.
 *
 * @package    im
 * @subpackage participants
 * @author     Polikarpov Alexander <polikarpovst@gmail.com>
 * @copyright  2016
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Общие
$string['title'] = 'Участники';
$string['tab_main'] = 'Главная';
$string['tab_students'] = 'Слушатели';
$string['tab_eagreements'] = 'Сотрудники';
$string['tab_persons'] = 'Справочник персон';
$string['tab_metacontracts'] = 'Метаконтракты';
$string['tab_import'] = 'Импорт';
$string['page_main_name'] = 'Участники учебного процесса';
$string['page_students_name'] = 'Панель управления слушателями';
$string['page_eagreements_name'] = 'Панель управления сотрудниками';
$string['page_persons_name'] = 'Справочник персон';
$string['page_metacontracts_name'] = 'Панель управления Метаконтрактами';
$string['page_filter_result_name'] = 'Результаты поиска';
$string['page_students_create_programmsbc_name'] = 'Создание подписки на программу';
$string['page_students_create_programmsbc_select_contract_name'] = 'Выбор договора на обучение';
$string['page_students_create_programmsbc_create_person_name'] = 'Создание новой персоны';
$string['page_students_create_programmsbc_create_contract_name'] = 'Создание нового договора';
$string['page_students_create_programmsbc_create_programmsbc_name'] = 'Сохранение подписки на программу';
$string['page_students_create_programmsbc_create_person_header'] = 'Студент по договору';
$string['page_students_create_programmsbc_contractpersons_name'] = 'Добавление персон по договору';
$string['page_students_export_name'] = 'Экспорт подписок на программы';
$string['page_students_import_name'] = 'Импорт подписок на программы';
$string['students_export_button'] = 'Экспорт';
$string['students_import_button'] = 'Импорт';

// Административные строки
$string['acl_interface_students'] = 'Доступ к панели управления слушателями';
$string['acl_interface_eagreements'] = 'Доступ к панели управления сотрудниками';
$string['acl_interface_persons'] = 'Доступ к справочнику персон';
$string['acl_interface_metacontracts'] = 'Доступ к панели управления метаконтрактами';
$string['acl_interface_import'] = 'Доступ к панели управления импортом';

// Формы
$string['students_filter_lastname_label'] = 'Фильтровать по Фамилии';
$string['students_filter_lastname_placeholder'] = 'Фамилия';
$string['students_filter_firstname_label'] = 'Фильтровать по Имени';
$string['students_filter_firstname_placeholder'] = 'Имя';
$string['students_filter_middlename_label'] = 'Фильтровать по Отчеству';
$string['students_filter_middlename_placeholder'] = 'Отчество';
$string['students_filter_email_label'] = 'Фильтровать по E-mail';
$string['students_filter_email_placeholder'] = 'E-mail';
$string['students_filter_telephone_placeholder'] = 'Телефон';
$string['students_filter_telephone_label'] = 'Фильтровать по номеру телефона';
$string['students_filter_programm_placeholder'] = 'Программа';
$string['students_filter_programm_label'] = 'Фильтровать по программе';
$string['students_filter_programmagenum_placeholder'] = 'Параллель';
$string['students_filter_programmagenum_label'] = 'Фильтровать по параллели';
$string['students_filter_agroup_placeholder'] = 'Группа';
$string['students_filter_agroup_label'] = 'Фильтровать по академической группе';
$string['students_filter_submit'] = 'Найти подписки';

$string['students_create_bc_fast_button'] = 'Быстрое создание подписки';
$string['students_create_bc_fast_title'] = 'Быстрое создание подписки на программу';
$string['students_fastcreate_contract_title'] = 'Создать подписку для пресоны по договору';
$string['students_fastcreate_contract_person_label'] = 'Персона';
$string['students_fastcreate_contract_contract_label'] = 'Договор';
$string['students_fastcreate_contract_new_person'] = 'Создать новую персону';
$string['students_fastcreate_contract_new_contract'] = 'Создать новый договор';
$string['students_fastcreate_contract_contract_name'] = 'Договор № {$a->num} от {$a->date}';
$string['students_fastcreate_programm_title'] = 'На программу';
$string['students_fastcreate_agroup_title'] = 'Добавить в группу и параллель';
$string['students_fastcreate_programm_new_agroup'] = 'Создать новую академическую группу';
$string['students_fastcreate_programm_no_agroup'] = 'Без академической группы';
$string['students_fastcreate_programm_no_agenum'] = 'Без параллели';
$string['students_fastcreate_submit'] = 'Создать подписку';
$string['students_fastcreate_error_data_not_found'] = 'Получены не полные данные';
$string['students_fastcreate_error_personcreate_accessdenied'] = 'У Вас нет доступа к созданию новой персоны';
$string['students_fastcreate_error_contractcreate_accessdenied'] = 'У Вас нет доступа к созданию нового договора на обучение';
$string['students_fastcreate_error_contractuse_accessdenied'] = 'У Вас нет доступа к созданию подписки для указанного договора';
$string['students_fastcreate_error_empty_programms'] = 'Не найдено ни одной программы для создания подписки';
$string['students_fastcreate_error_programmuse_accessdenied'] = 'У Вас нет доступа к созданию подписки на указанную программу';
$string['students_fastcreate_error_programm_not_set'] = 'Программа не указана';
$string['students_fastcreate_error_agroupcreate_accessdenied'] = 'У Вас нет доступа к созданию новой группы для подписки';
$string['students_fastcreate_error_agroupuse_accessdenied'] = 'У Вас нет доступа к указанной группе';
$string['students_fastcreate_error_agroup_compare_programm'] = 'Выбранная группа не принадлежит указанной программе';
$string['students_fastcreate_error_agenum_overlimit'] = 'Указанный период не найден в программе';
$string['students_fastcreate_error_person_empty_email_accessdenied'] = 'Требуется указать Email для создания новой персоны';
$string['students_fastcreate_error_personcreate_emailnotunique'] = 'Указанный Email уже зарезервирован за другой персоной';
$string['students_fastcreate_error_programm_not_found'] = 'Указанная программа не найдена';
$string['students_fastcreate_error_programmbc_exist'] = 'Данная подписка уже существует в системе';
$string['students_fastcreate_error_personcreate_accessdenied'] = 'У Вас нет доступа к созданию новой подписки';

$string['students_create_bc_button'] = 'Создание подписки';
$string['students_create_bc_title'] = 'Создание подписки на программу';
$string['students_create_programmsbc_contractpersons_student'] = 'Студент по договору';
$string['students_create_programmsbc_contractpersons_client'] = 'Законный представитель по договору';
$string['students_create_select_contract_header'] = 'Выбор договора на обучение';
$string['students_create_select_contract_contractselect_new'] = 'Создать новый договор на обучение';
$string['students_create_select_contract_personselect_new'] = 'Создать новую персону';
$string['students_create_select_contract_personselect_select'] = 'Выбрать существующую персону';
$string['students_create_select_contract_personfind_placeholder'] = 'ID, ФИО персоны';
$string['students_create_select_contract_personfind_label'] = '';
$string['students_create_select_contract_contractselect_select'] = 'Выбрать договор на обучение';
$string['students_create_select_contract_contractfind_placeholder'] = 'ФИО персоны, № договора, ID договора';
$string['students_create_select_contract_contractfind_label'] = '';
$string['students_create_select_contract_continue'] = 'Продолжить';
$string['students_create_select_contract_error_action_not_set'] = 'Действие не выбрано';
$string['students_create_select_contract_error_contractfind_not_set'] = 'Договор не указан';
$string['students_create_select_contract_error_contractfind_access_use'] = 'У Вас нет доступа к созданию подписки для указанного договора';
$string['students_create_select_contract_error_contractfind_access_edit'] = 'У Вас нет доступа к изменению персон текущего договора на обучение';
$string['students_create_select_contract_error_contractfind_access_create'] = 'У Вас нет доступа к созданию нового договора на обучение';
$string['students_create_select_contract_error_personfind_not_set'] = 'Персона для создания нового договора не указана';
$string['students_create_select_contract_error_personfind_access_use'] = 'У Вас нет доступа к созданию договора для указанной персоны';
$string['students_create_select_contract_error_personfind_access_create'] = 'У Вас нет доступа к созданию новой персоны';

$string['students_create_contract_title'] = 'Договор на обучение';
$string['students_create_programm_title'] = 'Программа обучения';
$string['students_create_eduform_title'] = 'Форма обучения';
$string['students_create_edutype_title'] = 'Тип обучения';
$string['students_create_freeattendance_title'] = 'Свободное посещение';
$string['students_create_startdate_title'] = 'Начало обучения';
$string['students_create_salfactor_title'] = 'Поправочный зарплатный коэффициент:';
$string['students_create_age_notselect'] = '--Не указан--';
$string['students_create_age_select'] = '{$a->name} ({$a->startdate} - {$a->enddate})';
$string['students_create_agestart_title'] = 'Начальный период обучения';
$string['students_create_submit'] = 'Сохранить';

$string['students_create_error_contract_not_found'] = 'Договор не найден';
$string['students_create_error_contract_access_denied'] = 'У Вас нет доступа к созданию подписки для указанного договора';
$string['students_create_error_programm_not_set'] = 'Программа не указана';
$string['students_create_error_programm_not_found'] = 'Программа не найдена';
$string['students_create_error_programm_access_denied'] = 'У Вас нет доступа к созданию подписки на указанную программу';
$string['students_create_error_agroup_not_set'] = 'Группа не указана';
$string['students_create_error_agroup_not_found'] = 'Группа не найдена';
$string['students_create_error_programm_access_denied'] = 'У Вас нет доступа к созданию подписки с добавлением в указанную группу';
$string['students_create_error_agenum_not_set'] = 'Номер параллели не указан';
$string['students_create_error_agenum_overlimit'] = 'Номер параллели не доступен для данной программы';
$string['students_create_error_agenum_overagroup'] = 'Номер параллели не доступен для указанной группы';
$string['students_create_error_age_not_found'] = 'Учебный период не найден';
$string['students_create_error_age_access_denied'] = 'У Вас нет доступа к созданию подписки с указанием выбранного учебного периода';
$string['students_create_error_sbc_overlimit'] = 'В подразделении превышен лимит подписок';
$string['students_create_error_programmbc_exist'] = 'Данная подписка на программу уже имеется в системе';

$string['students_import_error_access_denied'] = 'У Вас нет доступа к импорту подписок на программы';
$string['form_students_import_header'] = 'Импорт подписок на программы';
$string['form_students_import_description_header'] = 'Помощь';
$string['form_students_import_description'] = '<h4>Поля для импорта данных по подпискам:</h4><br/>
                <b>Договор</b><br/>
                studentcontract_num - Номер договора, по которому будет сформирована подписка. Если не указан, будет сгенерирован автоматически<br/>
                studentcontract_startdate - Дата заключения договора на обучение в формате dd.mm.yyyy<br/>
                studentcontract_notice - Заметка по договору<br/>
                <b>Студент по договору</b><br/>
                student_email - Email студента для создания нового договора<br/>
                student_firstname - Имя студента<br/>
                student_lastname - Фамилия студента<br/>
                student_middlename - Отчество студента<br/>
                student_birthdate - Дата рождения студента в формате dd.mm.yyyy<br/>
                student_gender - Пол студента в буквенном формате (m, f, м, ж)<br/>
                <b>Законный представитель по договору</b><br/>
                parent_email - Email законного представителя для создания нового договора<br/>
                parent_firstname - Имя законного представителя<br/>
                parent_lastname - Фамилия законного представителя<br/>
                parent_middlename - Отчество законного представителя<br/>
                parent_birthdate - Дата рождения законного представителя в формате dd.mm.yyyy<br/>
                parent_gender - Пол законного представителя в буквенном формате (m, f, м, ж)<br/>
                <b>Менеджер</b><br/>
                seller_email - Email менеджера для создания нового договора<br/>
                seller_firstname - Имя менеджера<br/>
                seller_lastname - Фамилия менеджера<br/>
                seller_middlename - Отчество менеджера<br/>
                seller_birthdate - Дата рождения менеджера в формате dd.mm.yyyy<br/>
                seller_gender - Пол менеджера в буквенном формате (m, f, м, ж)<br/>
                <b>Куратор</b><br/>
                curator_email - Email куратора для создания нового договора<br/>
                curator_firstname - Имя куратора<br/>
                curator_lastname - Фамилия куратора<br/>
                curator_middlename - Отчество куратора<br/>
                curator_birthdate - Дата рождения куратора в формате dd.mm.yyyy<br/>
                curator_gender - Пол куратора в буквенном формате (m, f, м, ж)<br/>
                <b>Программа обучения</b><br/>
                programm_code - Код программы для подписки<br/>
                <b>Академическая группа</b><br/>
                agroup_code - Код группы для подписки на программу<br/>
                <b>Подписка на программу</b><br/>
                programmsbc_agenum - Номер параллели<br/>
                programmsbc_edutype - Тип обучения(group - групповая, individual - индивидуальная). Если не указано, выбирается значение на основе данных о группе<br/>
                programmsbc_eduform - Форма обучения(internal - очная, correspondence - заочная, internally-correspondence - очно-заочная, external-studies - экстарнат). Если не указано. выбирается значение по - умолчанию<br/>
                programmsbc_freeattendance - Свободное посещение(0, 1)<br/>
                programmsbc_datestart - Дата начала подписки в формате dd.mm.yyyy<br/>
                programmsbc_salfactor - Поправочный коэфициент<br/>
';
$string['form_students_import_file_label'] = 'Файл для импорта';
$string['form_students_import_delimiter_label'] = 'Разделитель';
$string['form_students_import_submit'] = 'Импорт';
$string['form_students_import_check'] = 'Проверка импортируемых данных';



$string['students_export_error_access_denied'] = 'У Вас нет доступа к экспорту подписок на программы';

// Таблицы
$string['table_programmsbcs_header_actions'] = '';
$string['table_programmsbcs_header_actions_view_programmbc'] = 'Просмотр подписки';
$string['table_programmsbcs_header_actions_edit_programmbc'] = 'Редактирование подписки';
$string['table_programmsbcs_header_actions_programmitems'] = 'Изучаемые и пройденные дисциплины';
$string['table_programmsbcs_header_actions_learninghistory'] = 'История обучения по подписке';
$string['table_programmsbcs_header_actions_learningplan'] = 'Индивидуальный учебный план';
$string['table_programmsbcs_header_lastname'] = 'Фамилия';
$string['table_programmsbcs_header_firstname'] = 'Имя';
$string['table_programmsbcs_header_middlename'] = 'Отчество';
$string['table_programmsbcs_header_email'] = 'E-mail';
$string['table_programmsbcs_header_department'] = 'Подразделение';
$string['table_programmsbcs_header_programm'] = 'Программа';
$string['table_programmsbcs_header_agenum'] = 'Параллель';
$string['table_programmsbcs_header_agroup'] = 'Группа';
$string['table_programmsbcs_header_status'] = 'Статус';
$string['table_programmsbcs_title'] = 'Найденные в текущем подразделении';
$string['table_programmsbcs_collisions_title'] = 'Найденные в других подразделениях';

$string['table_import_students_header_row'] = '№ строки';
$string['table_import_students_header_status'] = 'Статус';
$string['table_import_students_header_actions'] = 'Описание';

$string['table_import_row_status_error'] = 'Ошибка';
$string['table_import_row_status_get'] = 'Подписка уже содержится в системе';
$string['table_import_row_status_save'] = 'Подписка сохранена';

$string['table_import_row_programmsbc'] = 'Импорт подписки на программу';
$string['table_import_row_programmsbc_error'] = 'Ошибка';
$string['table_import_row_programmsbc_get'] = 'Найдена в системе';
$string['table_import_row_programmsbc_save'] = 'Сохранена';
$string['table_import_row_contractid'] = 'Импорт договора на обучение по подписке';
$string['table_import_row_contractid_error'] = 'Ошибка';
$string['table_import_row_contractid_get'] = 'Найден в системе';
$string['table_import_row_contractid_save'] = 'Сохранен';
$string['table_import_row_studentid'] = 'Импорт студента по договору на обучение';
$string['table_import_row_studentid_error'] = 'Ошибка';
$string['table_import_row_studentid_get'] = 'Найден в системе';
$string['table_import_row_studentid_save'] = 'Сохранен';
$string['table_import_row_sellerid'] = 'Импорт менеджера по договору на обучение';
$string['table_import_row_sellerid_error'] = 'Ошибка';
$string['table_import_row_sellerid_get'] = 'Найден в системе';
$string['table_import_row_sellerid_save'] = 'Сохранен';
$string['table_import_row_clientid'] = 'Импорт законного представителя по договору на обучение';
$string['table_import_row_clientid_error'] = 'Ошибка';
$string['table_import_row_clientid_get'] = 'Найден в системе';
$string['table_import_row_clientid_save'] = 'Сохранен';
$string['table_import_row_curatorid'] = 'Импорт куратора по договору на обучение';
$string['table_import_row_curatorid_error'] = 'Ошибка';
$string['table_import_row_curatorid_get'] = 'Найден в системе';
$string['table_import_row_curatorid_save'] = 'Сохранен';
$string['table_import_row_programmid'] = 'Импорт программы обучения по подписке';
$string['table_import_row_programmid_error'] = 'Ошибка';
$string['table_import_row_programmid_get'] = 'Найдена в системе';
$string['table_import_row_programmid_save'] = 'Сохранена';
$string['table_import_row_agroupid'] = 'Импорт академической группы по подписке';
$string['table_import_row_agroupid_error'] = 'Ошибка';
$string['table_import_row_agroupid_get'] = 'Найдена в системе';
$string['table_import_row_agroupid_save'] = 'Сохранена';
$string['table_import_errors'] = 'Найдено ошибок: {$a}';

// Системные сообщения
$string['notice_page_filter_has_collisions'] = 'Найдены подписки в других подразделениях';
$string['notice_page_filter_empty_result'] = 'Подписки на программы не найдены';
$string['message_students_programmbs_create_success'] = 'Подписка на программу создана';
$string['page_students_create_programmsbc_error_contract_not_found'] = 'Договор не найден';
$string['page_students_create_programmsbc_error_contract_no_student'] = 'В выбранном договоре не указан ученик';

$string['error_interface_base_access_denied'] = 'У Вас нет доступа к использованию плагина';
$string['error_interface_students_access_denied'] = 'У Вас нет доступа к управлению подписками на программы';
$string['error_interface_import_access_denied'] = 'У Вас нет доступа к управлению импортом';

?>