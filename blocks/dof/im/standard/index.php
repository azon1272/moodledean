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
require_once('lib.php');

// для первоначального входа определим пользователя
// из какого он подразделения
$depid = optional_param('departmentid',null,PARAM_INT);

$DOF->modlib('nvg')->set_url('im', 'standard', 'index.php', array('departmentid' => $depid));
//проверка прав доступа
$DOF->im('standard')->require_access('view');
if ( ! isset($depid) )
{// определяем на какую стр перенаправить пользователя
    // при первом входе в деканат
    // получили персону из деканата
    $path = $DOF->url_im('standard','/index.php?departmentid=0');
    if ( $DOF->plugin_exists('storage', 'persons') AND $right = $DOF->storage('departments')->get_right_dep() )
    {
        foreach ( $right as $depid=>$value)
        {// определим , куда его сразу впустить
            if ( in_array('view', $value) OR in_array('view/mydep', $value))
            {
                $path = $DOF->url_im('standard','/index.php?departmentid='.$depid);
                // нашли первое - дальне нет смысла продолжать
                break;
            }       
        }
        $path = $DOF->url_im('standard','/index.php?departmentid='.$depid);
        
    }
    redirect($path, 0);
}
// Выводим шапку в режиме "портала
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL, 'left');

// Выводит стандартные секции
$DOF->modlib('nvg')->print_sections($DOF->plugin_path('modlib','nvg','/cfg/center.php'));

//$pathright = $DOF->plugin_path('im', 'standard').'/cfg/right.php';
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL,'right');


?>