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

// этот файл подключается из recordbook.php
// @todo добавить комментарии к этому скрипту
?>
<script type="text/javascript">
// переопределяем функцию отрисовки ячеек с датами, чтобы вставить ссылки с временем
YAHOO.widget.Calendar.prototype.renderCellDefault = function(workingDate, cell) {
	return cell.innerHTML = '<a href="<?=$DOF->url_im('recordbook', '/recordbook.php?programmsbcid='.
	  $programmsbcid.'&departmentid='.$addvars['departmentid'].'&time=')?>' + 
    parseInt(workingDate.getTime()/1000) + '" >' + this.buildDayLabel(workingDate) + "</a>";
};
var cal1 = new YAHOO.widget.Calendar("calCon", "calCon", {
    // устанавливаем дату календаря по умолчанию
    pagedate: "<?=date('m/Y', $weekbegin)?>",
    start_weekday: "1"

});

// Установим русский формат отображения данных: dd.mm.yyyy, dd.mm, mm.yyyy 
cal1.cfg.setProperty("DATE_FIELD_DELIMITER", "."); 
 
cal1.cfg.setProperty("MDY_DAY_POSITION", 1); 
cal1.cfg.setProperty("MDY_MONTH_POSITION", 2); 
cal1.cfg.setProperty("MDY_YEAR_POSITION", 3); 
 
cal1.cfg.setProperty("MD_DAY_POSITION", 1); 
cal1.cfg.setProperty("MD_MONTH_POSITION", 2); 
 
// локализация
cal1.cfg.setProperty("MONTHS_SHORT",   ["Янв", "Фев", "Мар", "Апр", "Май", "Июн", "Июл", "Авг", "Сен", "Окт", "Ноя", "Дек"]); 
cal1.cfg.setProperty("MONTHS_LONG",    ["Январь", "Февраль", "Март", "Апрель", "Май", "Июнь", "Июль", "Август", "Сентябрь", "Октябрь", "Ноябрь", "Декабрь"]); 
cal1.cfg.setProperty("WEEKDAYS_1CHAR", ["В", "П", "В", "С", "Ч", "П", "С"]); 
cal1.cfg.setProperty("WEEKDAYS_SHORT", ["Вс", "Пн", "Вт", "Ср", "Чт", "Пт", "Сб"]); 
cal1.cfg.setProperty("WEEKDAYS_MEDIUM",["Вск", "Пон", "Втр", "Срд", "Чтв", "Птн", "Сбт"]); 
cal1.cfg.setProperty("WEEKDAYS_LONG",  ["Воскресенье", "Понедельник", "Вторник", "Среда", "Четверг", "Пятница", "Суббота"]);

cal1.render();
</script>