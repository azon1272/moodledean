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

/** Проверяет, содержит ли переменная положительное целое
 * @param mixed $val
 * @return bool 
 */
function ama_utils_is_intstring($val)
{
    return is_int_string($val);
}

/** Транслителировать строку в латиницу
 * @param string $lang - двухбуквенный код языка
 * @param string $string - строка
 * @return string 
 */
function ama_utils_translit($lang, $string, $small = true)
{
    if ( $small )
    {
        $string = textlib::strtolower($string);
    }
    if ( $lang === 'ru' )
    {
        $alfabet = array(
            'а' => 'a', 'А' => 'A', 'б' => 'b', 'Б' => 'B', 'в' => 'v', 'В' => 'V',
            'г' => 'g', 'Г' => 'G', 'д' => 'd', 'Д' => 'D', 'е' => 'e', 'Е' => 'E',
            'ё' => 'jo', 'Ё' => 'Jo', 'ж' => 'zh', 'Ж' => 'Zh', 'з' => 'z', 'З' => 'Z',
            'и' => 'i', 'И' => 'I', 'й' => 'j', 'Й' => 'J', 'к' => 'k', 'К' => 'K',
            'л' => 'l', 'Л' => 'L', 'м' => 'm', 'М' => 'M', 'н' => 'n', 'Н' => 'N',
            'о' => 'o', 'О' => 'O', 'п' => 'p', 'П' => 'P', 'р' => 'r', 'Р' => 'R',
            'с' => 's', 'С' => 'S', 'т' => 't', 'Т' => 'T', 'у' => 'u', 'У' => 'U',
            'ф' => 'f', 'Ф' => 'F', 'х' => 'h', 'Х' => 'h', 'ц' => 'c', 'Ц' => 'C',
            'ч' => 'ch', 'Ч' => 'Ch', 'ш' => 'sh', 'Ш' => 'Sh', 'щ' => 'shh', 'Щ' => 'Shh',
            'ъ' => '', 'Ъ' => '', 'ы' => 'y', 'Ы' => 'Y', 'ь' => "", 'Ь' => "",
            'э' => 'e', 'Э' => 'E', 'ю' => 'ju', 'Ю' => 'Ju', 'я' => 'ja', 'Я' => 'Ja');
    }
    // Чтоб не было конфликтов перед обработкой убираем экранирование
    return addslashes(strtr(stripslashes($string), $alfabet));
}
?>