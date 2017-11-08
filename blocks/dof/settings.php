<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Free Dean's Office settings and presets.
 *
 * @package    block
 * @subpackage dof
 * @copyright  2013 Ilya Fastenko
 * @author     Kirill Krasnoschekov, Ilya Fastenko - based on code by Petr Skoda and others
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/*
 * Т.к. это страница для admin/settings.php, весь вывод делается
 * через $settings и объекты admin_setting_*.
 */
defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/blocks/dof/otapilib.php');

////////////////////////////////////////
// Конфигурация
$pcode = 'deansoffice';
$code_cfg = 'block_dof';
$code_str = 'block_dof';
$code_param = 'dof_';
$link_base = new moodle_url('/admin/settings.php', array('section'=>'blocksettingdof'));

$tariffcodes = array(
        'free',
        'Д-1', 'Д-2', 'Д-3', 'Д-Люкс',
        'Р',
        'П-Ла', 'П-Ко', 'П-Ве', 'П-Ун',
);

////////////////////////////////////////
// Подготовка

$errors = array();

$otserial = get_config($code_cfg, 'otserial');
$otkey = get_config($code_cfg, 'otkey');

// класс для связи с ot api
$otapi = new block_dof_otserial();

if (!$ADMIN->fulltree)
{
    return;
}

////////////////////////////////////////
// Маршрутизация

$action = optional_param("{$code_param}action", null, PARAM_TEXT);
switch ($action)
{
    // Получаем серийник
    case 'getotserial':
        // Если серийник уже есть -- не разрешаем это действие
        if (!empty($otserial) AND !empty($otkey)) {
            $errors[] = get_string('already_has_serial', $code_str);
        } else {
            //отправляем запрос на получение серийника
            $otdata = $otapi->get_otserial();
            // Запрос успешен?
            if (isset($otdata->status) AND preg_match('/^error/', $otdata->status)) {
                $errors[] = $otdata->message;
            } elseif (!empty($otdata->otserial) AND !empty($otdata->otkey)) {
                //сохраняем данные в $CFG
                set_config('otserial', $otdata->otserial, $code_cfg);
                set_config('otkey', $otdata->otkey, $code_cfg);
                // перезагружаем страницу
                redirect($link_base);
            }
        }
        break;

//     // Сбрасываем серийник
//     case 'reset':
//         unset_config('otserial', $code_cfg);
//         unset_config('otkey', $code_cfg);
//         redirect($link_base);
//         break;
}

// Готовим cерийник для печати
$otserial_formatted = substr($otserial, 0, 4)."-"
    .substr($otserial, 4, 4)."-"
    .substr($otserial, 8, 4)."-"
    .substr($otserial, 12, 4)."-"
    .substr($otserial, 16);


////////////////////////////////////////
// Вывод

// Ошибки
if (count($errors) > 0) {
    $settings->add(new admin_setting_heading("$code_str/errors",
            '', $OUTPUT->notification(implode('<br>\n', $errors))));
}

// Информация
if (!empty($otserial) AND !empty($otkey)) {
    ////////////////////////////////////////
    // Инфо о регистрации в OT API
    // Серийный номер
    $settings->add(new admin_setting_heading("$code_str/otserial",
            get_string('otserial', $code_str), $otserial_formatted));
//     // Кнопка сброса серийника (для отладки?)
//     $link_reset = new moodle_url($link_base, array("{$code_param}action"=>'reset'));
//     $settings->add(new admin_setting_heading("$code_str/otserial_reset",
//             '', html_writer::link($link_reset, get_string('reset_otserial', $code_str))));


    // запрос на статус серийника
    $ret_status = $otapi->get_otserial_status($otserial, $otkey);
    if ( !(($ret_status instanceof stdClass) AND (isset($ret_status->status))) ) {
        $settings->add(new admin_setting_heading("$code_str/otserial_check",
                '', $OUTPUT->notification(
                        get_string('otserial_check_fail', $code_str, ''))
        ));
    } elseif ($ret_status->status === 'ok') {
        // Серийник принимается
        $settings->add(new admin_setting_heading("$code_str/otserial_check",
                '', $OUTPUT->notification(
                        get_string('otserial_check_ok', $code_str), 'notifysuccess')));

        ////////////////////////////////////////
        // Тариф и его опции
        $settings->add(new admin_setting_heading("$code_str/otservice",
                get_string('otservice', $code_str,$ret_status->tariff),
                ''));

        // Разрешен ли возвращённый тариф для данного продукта?
        if (!in_array($ret_status->tariff, $tariffcodes))
        {// Нештатная ситуация, говорящая о том, что на сервере OT что-то не в порядке
            $settings->add(new admin_setting_heading("$code_str/otserial_tariff_check",
                    '', $OUTPUT->notification(
                            get_string('otserial_tariff_wrong', $code_str))));
        }

        // Какой тариф?
        switch ($ret_status->tariff)
        {
            // Бесплатный
            case 'free':
                // Отображаем ссылку на форму заявки
                $link_ord = $otapi->url("otclients/{$pcode}/new/");
                /*$settings->add(new admin_setting_heading("$code_str/otservice_send_order",
                        '',
                        html_writer::link($link_ord, get_string('otservice_send_order', $code_str))));*/
                break;

            // Платный тариф
            // пока здесь все разрешенные тарифы, кроме free.
            // Если нужны разные действия внутри тарифов, нужно добавить проверку внутри этого case
            default:
                ////////////////////////////////////////
                // Срок обслуживания
                $link_renew = $otapi->url("otclients/{$pcode}/renew/");
                if (!empty($ret_status->expirytime) AND ($ret_status->expirytime < time()))
                {// Срок обслуживания истёк
                    $settings->add(new admin_setting_heading("$code_str/otservice_expiry_time",
                            '',
                            $OUTPUT->notification(get_string('otservice_expired', $code_str))));
                    // Ссылка на продление
                   /* $settings->add(new admin_setting_heading("$code_str/service_renew",
                            '',
                            html_writer::link($link_renew, get_string('otservice_renew', $code_str))));*/
                    break;
                }
                else
                {// Срок обслуживания в порядке
                    if (($ret_status->expirytime == 0) AND (is_numeric($ret_status->expirytime)))
                    {// Срок обслуживания бессрочный
                        $settings->add(new admin_setting_heading("$code_str/otservice_expiry_time",
                                '',
                                $OUTPUT->notification(get_string('otservice_unlimited', $code_str), 'notifysuccess')));
                    }
                    else
                    {// Срок действителен до
                        $settings->add(new admin_setting_heading("$code_str/otservice_expiry_time",
                                '',
                                $OUTPUT->notification(get_string('otservice_active', $code_str, date('Y-m-d H:i', $ret_status->expirytime)), 'notifysuccess')));
                    }
                    // Ссылка на смену
                    /*$settings->add(new admin_setting_heading("$code_str/service_renew",
                            '',
                            html_writer::link($link_renew, get_string('otservice_change_tariff', $code_str))));*/
                }

                ////////////////////////////////////////
                // Здесь могут быть особенности настройки Dean's Office
        }
    } else {
        // Сервер забраковал серийник
        $settings->add(new admin_setting_heading("$code_str/otserial_check",
                '', $OUTPUT->notification(
                        get_string('otserial_check_fail', $code_str, @$ret_status->message))
        ));
    }
} else {
    // Серийник не получен
    // Кнопка получения серийника
    $link_get = new moodle_url($link_base, array("{$code_param}action"=>'getotserial'));
    $settings->add(new admin_setting_heading("$code_str/get_otserial",
            get_string('otserial', $code_str),
            html_writer::link($link_get, get_string('get_otserial', $code_str))));
}

?>
