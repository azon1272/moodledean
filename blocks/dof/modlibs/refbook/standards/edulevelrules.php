<?php
// false - правило не проверяется вовсе
// true - правило проверяется в специальной функции (реализация может отличаться)
// Типы правил ('checkbox', 'text', ...) по-умолчанию записываются в modlibs/refbook/init.php:config_default()
// Дошкольное образование
$string['1'] = array();
// Начальное общее образование
$string['2'] = array();
// Основное общее образование
$string['3'] = array();
// Среднее (полное) общее образование
$string['4'] = array();
// Начальное профессиональное образование
$string['5'] = array();
// Среднее профессиональное образование (СПО)
$string['6'] = array(
    'pitem' => array(
        // Проверка метадисциплин:
        // * Дисциплины с одним родителем не должны находиться в одной параллели
        // * Дисциплины с одним родителем не должны прерываться (идут подряд начиная с параллели N)
        'metaprogrammitemid' => true
    ),
    // Правила для семестра
    'agenum' => array(
        'hours'          => 1134,
        'hourstheory'    => false,
        'hourspractice'  => false,
        'hoursweek'      => false,
        'hourslab'       => false,
        'hoursind'       => false,
        'hourscontrol'   => false,
        'hoursclassroom' => false,
        'maxcredit'      => false,
    ),
    // Правила для учебного года
    'academicyear' => array(
        'hours'          => false,
        'hourstheory'    => false,
        'hourspractice'  => false,
        'hoursweek'      => false,
        'hourslab'       => false,
        'hoursind'       => false,
        'hourscontrol'   => false,
        'hoursclassroom' => 160,
        'maxcredit'      => false,
    ),
);    
// Высшее профессиональное образование (ВПО)
$string['7'] = array(
    'pitem' => array(
        // Проверка метадисциплин:
        // * Дисциплины с одним родителем не должны находиться в одной параллели
        // * Дисциплины с одним родителем не должны прерываться (идут подряд начиная с параллели N)
        'metaprogrammitemid' => true
    ),
    // Правила для семестра
    'agenum' => array(
        'hours'          => 1134,
        'hourstheory'    => false,
        'hourspractice'  => false,
        'hoursweek'      => false,
        'hourslab'       => false,
        'hoursind'       => false,
        'hourscontrol'   => false,
        'hoursclassroom' => false,
        'maxcredit'      => 30,
    ),
    // Правила для учебного года
    'academicyear' => array(
        'hours'          => false,
        'hourstheory'    => false,
        'hourspractice'  => false,
        'hoursweek'      => false,
        'hourslab'       => false,
        'hoursind'       => false,
        'hourscontrol'   => false,
        'hoursclassroom' => 200,
        'maxcredit'      => false,
    ),
);
// Послевузовское профессиональное
$string['8'] = array();
// Профессиональная подготовка
$string['9'] = array();
// Дополнительное образование
$string['10'] = array();
?>