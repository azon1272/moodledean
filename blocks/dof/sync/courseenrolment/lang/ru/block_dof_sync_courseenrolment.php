<?php
$string['title'] = 'Подписка на курс';
$string['page_main_name'] = 'Подписка на курс';

//*****************************************************************************
// Относящееся к синхронизации
$string['start_sync'] = 'Начало синхронизации\n';
$string['end_sync_success'] = 'Синхронизация завершена успешно\n';
$string['end_sync_err'] = 'Синхронизация завершена с ошибками\n';
$string['start_sync_cstream'] = 'Начало синхронизации cstream с id = $a';
$string['end_sync_cstream_success'] = 'Синхронизация cstream с id = $a завершена успешно\n';
$string['end_sync_cstream_err'] = 'Синхронизация cstream с id = $a завершена с ошибками\n';

$string['not_found_cfg_param'] = 'Не найден параметр \'$a\' в файле конфигурации';
$string['table_is_empty'] = 'Таблица \'$a\' пуста';
$string['error_get_from_table'] = 'Ошибка получения данных из таблицы \'$a\'';
$string['sync_disabled'] = 'Синхронизация учебного процесса (id = $a) отключена для дисциплины, к которой он относится';
$string['error_get'] = 'Не удается получить $a';
$string['not_found_course'] = 'Не найден курс id = $a';
$string['error_get_grade'] = 'Ошибка получения оценки (courseid = $a->courseid, userid = $a->userid)';
$string['error_get_scalegrade'] = 'Ошибка получения оценки приведенной к шкале (cpassedid = $a)';
$string['not_rated'] = 'Пока нет оценки (courseid = $a->courseid, userid = $a->userid)';
$string['not_found_scale'] = 'Не найдена шкала у programmitem (id = $a)';
$string['error_bring_to_scale'] = 'Ошибка приведения оценки к шкале (pitemid = $a->pitemid, grade = $a->grade)';
$string['cstream_is_closed'] = 'Учебный процесс закрыт (id = $a)';
$string['error_gen_journal'] = 'Ошибка генерации ведомости для учебного процесса (id = $a)';
$string['error_save_journal'] = 'Ошибка сохранения ведомости для учебного процесса (id = $a)';
$string['grades_not_changed'] = 'Оценки не изменились (cstreamid = $a)';
$string['error_sign_order'] = 'Ошибка подписания приказа (id = $a)';
$string['error_execute_order'] = 'Ошибка исполнения приказа (id = $a)';
$string['not_passed_yet'] = 'Пока нет оценки (cpassedid = $a)';
$string['not_passed_yet_but_included'] = 'Пока нет оценки, но включено в ведомость (cpassedid = $a)';
$string['unsatisf_grade_not_included'] = 'Неудовлетворительная оценка не включена в ведомость (cpassedid = $a)';
$string['error_open_file'] = 'Ошибка открытия файла \'$a\'';
$string['error_gen_journal'] = 'Ошибка создания ведомости (cpassedid = $a)';
$string['empty_cstream'] = 'В учебном процессе (id = $a) нет cpassed`ов';
$string['nothing_sync_cstream'] = 'Нет оценок которые можно синхронизировать в учебном процессе (id = $a)';
$string['cur_user_havent_person'] = 'Нет персоны для текущего пользователя (mdluserid = $a)';
$string['person_havent_eagreement'] = 'Преподаватель учебного процесса не находится в активном статусе (personid = $a)';


?>