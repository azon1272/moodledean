<?php

$string['title'] = 'Договора';
$string['page_main_name'] = 'Договора на обучение';

$string['error_contract_not_created'] = 'Договор не создан';
$string['error_contract_not_found'] = 'Договор не найден. ID: {$a}';
$string['error_contract_changestatus'] = 'Не удалось сменить статус у договора. ID: {$a}';

$string['error_save_contract'] = 'Ошибка при сохранении договора';
$string['error_save_empty_data'] = 'Данные для сохранения договора не переданы';
$string['error_save_invalid_data'] = 'Формат данных для сохранения договора не валиден';
$string['error_save_contract_not_found'] = 'Указанный договор не найден';
$string['error_save_create_clientdata_notfound'] = 'Для создания договора не переданы данные о клиенте';
$string['error_save_seller_not_found'] = 'При сохранении договора не найден указанный Агент';
$string['error_save_client_not_found'] = 'При сохранении договора не найден указанный Клиент';
$string['error_save_student_not_found'] = 'При сохранении договора не найден указанный Студент';
$string['error_save_curator_not_found'] = 'При сохранении договора не найден указанный Куратор';
$string['error_save_contract_notunique_num'] = 'Номер договора не уникален';

$string['error_import_empty_data'] = 'Данные для импорта договора не переданы';
$string['error_import_student_import'] = 'Ошибка импорта ученика по договору';
$string['error_import_student_not_set'] = 'Ученик по договору не указан';
$string['error_import_seller_import'] = 'Ошибка импорта менеджера по договору';
$string['error_import_client_import'] = 'Ошибка импорта законного представителя по договору';
$string['error_import_curator_import'] = 'Ошибка импорта куратора по договору';
$string['error_import_contract_not_found'] = 'Указанный договор не найден';
$string['error_import_contract_multiple_found'] = 'Найдено несколько договоров';

$string['contract_fullname'] = '{$a->personfullname} Договор № {$a->num} от {$a->date}';
?>