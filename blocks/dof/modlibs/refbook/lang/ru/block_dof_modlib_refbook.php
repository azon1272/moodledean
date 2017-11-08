<?php
$string['title'] = 'Стандарты';
$string['page_main_name'] = 'Стандарты';
// тип однородности дисциплины
$string['dis_thesame'] = 'Одинаковые дисциплины в разных учебных программах';
$string['dis_different_levels'] = 'Разные ступени изучения одной дисциплины';
$string['dis_one_purpose'] = 'Дисциплины из одной науки';
$string['dis_one_cathegory'] = 'Дисциплины одной категории';
$string['dis_unknown_cathegory'] = '&lt;Неизвестная категория&gt;'; 
// типы учебных компонент
$string['comp_federal'] = 'Федеральная';
$string['comp_regional'] = 'Региональная';
$string['comp_learnhouse'] = 'Учебного заведения';
$string['comp_department'] = 'Кафедры';
$string['comp_unknown'] = '&lt;Неизвестный тип компоненты&gt;';
// типы итогового контроля
$string['total_other'] = 'Другое';
$string['total_diplomawork'] = 'Дипломная работа';
$string['total_coursework'] = 'Курсовая работа';
$string['total_abstract'] = 'Реферат';
$string['total_gradexamination'] = 'Государственный экзамен';
$string['total_oralexamination'] = 'Устный экзамен';
$string['total_writeexamination'] = 'Письменный экзамен';
$string['total_finaltest'] = 'Зачет';
$string['total_oralquiz'] = 'Устный опрос';
$string['total_writequiz'] = 'Письменный опрос';
$string['total_writetest'] = 'Письменная проверка знаний';
$string['total_combotest'] = 'Комбинированная проверка знаний';
$string['total_discussion'] = 'Беседа';
$string['total_questionnaire'] = 'Анкетирование';
$string['total_testing'] = 'Тестирование';
$string['total_project'] = 'Проект';
$string['total_examination'] = 'Экзамен';
$string['total_unknown'] = '&lt;Неизвестный тип итогового контроля&gt;';
// Правила для уровней образования (edulevelrules.php): строки для сообщений об ошибках (начинаются с 'edr_')

// 'pitem' -> по дисциплинам
$string['edr_pitemmetaprogrammitemid']   = '* Дисциплины, созданные от одной метадисциплины &#xab;{$a}&#xbb;, не должны находиться в одной параллели&#xa;'
                                         . '* Дисциплины, созданные от одной метадисциплины &#xab;{$a}&#xbb;, не должны прерываться (идут подряд начиная с параллели N)';
$string['edr_okpitemmetaprogrammitemid'] = 'Дисциплина создана от метадисциплины &#xab;{$a}&#xbb;';

// 'agenum' -> по семестрам
$string['edr_agenumhours']            = 'Количество общих часов превышает допустимое в семестр ($a)';
$string['edr_okagenumhours']          = 'Количество общих часов не превышает допустимое в семестр ($a)';
$string['edr_agenumhourstheory']      = 'Количество часов лекций превышает допустимое в семестр ($a)';
$string['edr_okagenumhourstheory']    = 'Количество часов лекций не превышает допустимое в семестр ($a)';
$string['edr_agenumhourspractice']    = 'Количество часов практики превышает допустимое в семестр ($a)';
$string['edr_okagenumhourspractice']  = 'Количество часов практики не превышает допустимое в семестр ($a)';
$string['edr_agenumhoursweek']        = 'Количество часов в неделю превышает допустимое в семестр ($a)';
$string['edr_okagenumhoursweek']      = 'Количество часов в неделю не превышает допустимое в семестр ($a)';
$string['edr_agenumhourslab']         = 'Количество лабораторных часов превышает допустимое в семестр ($a)';
$string['edr_okagenumhourslab']       = 'Количество лабораторных часов не превышает допустимое в семестр ($a)';
$string['edr_agenumhoursind']         = 'Количество самостоятельной работы слушателя превышает допустимое в семестр ($a)';
$string['edr_okagenumhoursind']       = 'Количество самостоятельной работы слушателя не превышает допустимое в семестр ($a)';
$string['edr_agenumhourscontrol']     = 'Количество контрольных часов превышает допустимое в семестр ($a)';
$string['edr_okagenumhourscontrol']   = 'Количество контрольных часов не превышает допустимое в семестр ($a)';
$string['edr_agenumhoursclassroom']   = 'Количество аудиторных часов превышает допустимое в семестр ($a)';
$string['edr_okagenumhoursclassroom'] = 'Количество аудиторных часов не превышает допустимое в семестр ($a)';
$string['edr_agenummaxcredit']        = 'Количество зачётных единиц превышает допустимое в семестр ($a)';
$string['edr_okagenummaxcredit']      = 'Количество зачётных единиц не превышает допустимое в семестр ($a)';

// 'academicyear' -> по учебным годам
$string['edr_academicyearhours']            = 'Количество общих часов превышает допустимое в учебном году ($a)';
$string['edr_okacademicyearhours']          = 'Количество общих часов не превышает допустимое в учебном году ($a)';
$string['edr_academicyearhourstheory']      = 'Количество часов лекций превышает допустимое в учебном году ($a)';
$string['edr_okacademicyearhourstheory']    = 'Количество часов лекций не превышает допустимое в учебном году ($a)';
$string['edr_academicyearhourspractice']    = 'Количество часов практики превышает допустимое в учебном году ($a)';
$string['edr_okacademicyearhourspractice']  = 'Количество часов практики не превышает допустимое в учебном году ($a)';
$string['edr_academicyearhoursweek']        = 'Количество часов в неделю превышает допустимое в учебном году ($a)';
$string['edr_okacademicyearhoursweek']      = 'Количество часов в неделю не превышает допустимое в учебном году ($a)';
$string['edr_academicyearhourslab']         = 'Количество лабораторных часов превышает допустимое в учебном году ($a)';
$string['edr_okacademicyearhourslab']       = 'Количество лабораторных часов не превышает допустимое в учебном году ($a)';
$string['edr_academicyearhoursind']         = 'Количество самостоятельной работы слушателя превышает допустимое в учебном году ($a)';
$string['edr_okacademicyearhoursind']       = 'Количество самостоятельной работы слушателя не превышает допустимое в учебном году ($a)';
$string['edr_academicyearhourscontrol']     = 'Количество контрольных часов превышает допустимое в учебном году ($a)';
$string['edr_okacademicyearhourscontrol']   = 'Количество контрольных часов не превышает допустимое в учебном году ($a)';
$string['edr_academicyearhoursclassroom']   = 'Количество аудиторных часов превышает допустимое в учебном году ($a)';
$string['edr_okacademicyearhoursclassroom'] = 'Количество аудиторных часов не превышает допустимое в учебном году ($a)';
$string['edr_academicyearmaxcredit']        = 'Количество зачётных единиц превышает допустимое в учебном году ($a)';
$string['edr_okacademicyearmaxcredit']      = 'Количество зачётных единиц не превышает допустимое в учебном году ($a)';

//$string[''] = '';
?>