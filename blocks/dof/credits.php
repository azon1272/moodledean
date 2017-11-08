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
// Подключаем библиотеки
require_once(dirname(realpath(__FILE__)).'/lib.php');
// Инициализация генератора HTML
$PAGE->set_context(context_system::instance());
$DOF->modlib('nvg')->add_level($DOF->get_string('title'), $DOF->url_im('standard','/index.php'));
$DOF->modlib('nvg')->add_level($DOF->get_string('aboutdof'), $CFG->wwwroot.'/blocks/dof/credits.php');
echo '<script type="text/javascript">
window.___gcfg = {lang: \'ru\'};
(function() 
{var po = document.createElement("script");
po.type = "text/javascript"; po.async = true;po.src = "https://apis.google.com/js/plusone.js";
var s = document.getElementsByTagName("script")[0];
s.parentNode.insertBefore(po, s);
})();</script>';
// Выводим шапку в режиме "портала"
$DOF->modlib('nvg')->print_header(NVG_MODE_PAGE);

echo "\n<!-- start sectionlist -->\n";
$OUTPUT->container_start();
echo "<br />";

echo "\n<!-- section  start -->\n";

echo $OUTPUT->box_start('generalbox sitetopic');
echo "\n<!-- cps: {$DOF->storage('cpassed')->count_records_select(1)} -->\n";
echo "<div class='summary'><strong>Права</strong></div>";
// логотип
echo '<a href="http://www.deansoffice.ru/">
     <div style="text-align:left;">
     <br>
     <img src="'.$DOF->url_im('standard', '/logo.png').'" alt="Free Dean\'s Office" 
     title="Free Dean\'s Office"/></a></div>';
echo "<p><a href='http://www.deansoffice.ru'>Модуль \"Электронный деканат\" для Moodle (Free Dean's Office)</a> <br />";
echo "Copyright (C) 2008-2016  Алексей Дьяченко</p>";
echo "Данный программный продукт распространяется "
                    ."по <a href='".$CFG->wwwroot."/blocks/dof/gpl.txt'>лицензии "
                    ."GNU General Public License (GPL)</a>";
echo $OUTPUT->box_end();
echo "\n<!-- section end -->\n";

//проверка прав доступа
if ($DOF->im('admin')->is_access('admin'))
{
    echo "<br />";
    echo "\n<!-- section  start -->\n";
    echo $OUTPUT->box_start('generalbox sitetopic');
    echo "<div class='summary'><strong>".$DOF->get_string('version')."</strong></div>";
    echo "{$DOF->version_text()} (build&nbsp;{$DOF->version()})";
    echo $OUTPUT->box_end();
    echo "\n<!-- section end -->\n";
}

echo "<br />";
    
// ссылки на сообщества в сетях
echo "\n<!-- section  start -->\n";
echo $OUTPUT->box_start('generalbox sitetopic');
// google+
echo "<div class='summary'><g:plus href=\"https://plus.google.com/116192694572845668259\" size=\"badge\"></g:plus></div>";
echo $OUTPUT->box_end();
echo "\n<!-- section end -->\n";

echo "<br />";

echo "\n<!-- section  start -->\n";
echo $OUTPUT->box_start('generalbox sitetopic');
echo "<div class='summary'><strong>Информация о разработчиках</strong></div>";
echo "<ul>";
echo "<li>Ведущий разработчик - Алексей Дьяченко</li>";
echo "<li>Разработчик - Сергей Зимин</li>";
echo "<li>Разработчик - Александр Поликарпов</li>";
echo "<li>Разработчик - Дмитрий Штолин</li>";
echo "<li>Разработчик - Дмитрий Иванов</li>";
echo "</ul>";
echo $OUTPUT->box_end();
echo "\n<!-- section end -->\n";

echo "<br />";

echo "\n<!-- section  start -->\n";
echo $OUTPUT->box_start('generalbox sitetopic');
echo "<div class='summary'><strong>Благодарности</strong></div>";
echo "Благодарим за поддержку проекта:";
echo "<ul>";
echo "<li><a href='http://www.opentechnology.ru'>ООО \"Открытые технологии\"</a></li>";
echo "<li><a href='http://www.home-edu.ru'>ГОУ Центр образования \"Технологии обучения\"</a></li>";
echo "<li><a href='http://www.sibadi.org'>ГОУ \"Сибирская государственная автомобильно-дорожная академия (СибАДИ)\"</a></li>";
echo "<li><a href='http://www.nspu.net/'>ГОУ ВПО \"Новосибирский государственный педагогический университет\"</a></li>";
echo "<li><a href='http://www.sssu.ru/'>ГОУ ВПО Южно-Российский государственный университет экономики и сервиса</a></li>";
echo "<li><a href='http://unic.edu.ru/'>НОУ Институт истории культур</a></li>";
echo "<li><a href='http://www.pgfa.ru/'>ГОУ ВПО Пятигорская ГФА Росздрава </a></li>";
echo "<li><a href='http://tiei.ru/'>ГОУ ВПО НП \"Тульский институт экономики и информатики\"</a></li>";
echo "<li><a href='http://mfpa.ru/'>НОУ ВПО МФПУ \"Синергия\"</a></li>";
echo "<li><a href='http://www.mosap.ru/'> НОУ ВПО Московская Академия предпринимательства при Правительстве Москвы</a></li>";
echo "<li><a href='http://tsutmb.ru/'> ФГБОУ ВО \"Тамбовского государственного университета имени Г.Р. Державина\"</a></li>";
echo "</ul>";
echo "Благодарим за помощь в работе над проектом:";
echo "<ul>";
echo "<li>Илья Смирнов</li>";
echo "<li>Евгений Цыганцов</li>";
echo "<li>Дмитрий Пупынин</li>";
echo "<li>Андрей Сычев</li>";
echo "<li>Мария Рожайская</li>";
echo "<li>Николай Коновалов</li>";
echo "</ul>";
echo $OUTPUT->box_end();


echo "\n<!-- section  start -->\n";
echo $OUTPUT->box_start('generalbox sitetopic');
echo "<div class='summary'><strong>Обратная связь</strong></div>";
echo "<ul>";
echo "<li><a href='https://sourceforge.net/apps/trac/freedeansoffice/newticket'>Сообщить об ошибке</a></li>";
echo "<li><a href='http://www.deansoffice.ru/feedback'>Написать отзыв о Free Dean's Office</a></li>";
echo "</ul>";
echo $OUTPUT->box_end();

echo "<br />";
echo "\n<!-- section  start -->\n";
echo $OUTPUT->box_start('generalbox sitetopic');
echo "<div class='summary'><strong>Координаты</strong></div>";
echo "<ul>";
echo "<li><a href='http://www.deansoffice.ru'>Сайт проекта Free Dean's Office (Электронный деканат)</a></li>";
echo "<li><a href='http://docs.deansoffice.ru'>On-line документация</a></li>";
echo "<li><a href='http://sourceforge.net/projects/freedeansoffice'>Страница проекта на sourceforge.net</a></li>";
echo "<li><a href='http://www.infoco.ru/course/view.php?id=19'>Сообщество проекта на InfoCo.ru</a></li>";
echo "<li><a href='http://www.opentechnology.ru'>ООО \"Открытые Технологии\" - услуги по внедрению, поддержке и доработке  модуля \"Электронный деканат\"
                                         для Moodle (Free Dean's Office)</a></li>";
echo "</ul>";
echo $OUTPUT->box_end();
echo "\n<!-- section end -->\n";

// echo "</table>";
$OUTPUT->container_end();
echo "\n<!-- end sectionlist -->\n";

$DOF->modlib('nvg')->print_footer(NVG_MODE_PAGE);

?>
