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

/** Класс-обертка отвечающий за стандартную отрисовку html-элементов
 * @see html_writer
 */
class dof_html_writer extends html_writer
{
    /**
     * Возвращает скрытые hidden-поля для GET-параметров
     *
     * @param moodle_url|string $url - URL, из которого требуется взять параметры
     * @param array $exclude - Массив исключаемых параметров
     * @return string - HTML-полей
     */
    public static function dof_input_hidden_params($url, $exclude) 
    {
        if ( ! is_string($url) )
        {// URL не срока
            return parent::input_hidden_params($url, (array)$exclude);
        }
        
        $html = '';
        
        // Сформировать GET параметры
        $exclude = (array)$exclude;
        $params = parse_url($url);
        if ( isset($params['query']) )
        {// Найдены параметры
            $getparams = explode('&', $params['query']);
            foreach ( $getparams as $getparam )
            {// Обработка каждого get-параметра
                $getparam = explode('=', $getparam);
                if ( isset($getparam[0]) && isset($getparam[1]) && ! in_array($getparam[0], $exclude) )
                {// Параметр определен
                    $attributes = ['type' => 'hidden', 'name' => $getparam[0], 'value' => $getparam[1]];
                    $html .= self::empty_tag('input', $attributes)."\n";
                }
            }
        }
        
        return $html;
    }
}
