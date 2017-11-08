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
// Copyright (C) 2008-2999  Nikolay Konovalov (Николай Коновалов)         //
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

/**
 * Класс-обёртка для вызова moodle_exception
 */
class dof_exception extends moodle_exception
{
    function __construct($errorcode, $module = '', $link = '', $a = null, $debuginfo = null)
    {
        parent::__construct($errorcode, $module, $link, $a, $debuginfo);
    }
}

/**
 * Класс-обёртка для вызова coding_exception
 */
class dof_exception_coding extends coding_exception
{
    function __construct($hint, $debuginfo = null)
    {
        parent::__construct($hint, $debuginfo);
    }
}

/**
 * Класс-обёртка для вызова dml_exception
 */
class dof_exception_dml extends dml_exception
{
    function __construct($errorcode, $a = null, $debuginfo = null)
    {
        parent::__construct($errorcode, $a, $debuginfo);
    }
}

/**
 * Класс-обёртка для вызова ddl_exception
 */
class dof_exception_ddl extends ddl_exception
{
    function __construct($errorcode, $a = NULL, $debuginfo = null)
    {
        parent::__construct($errorcode, $a, $debuginfo);
    }
}

/**
 * Класс-обёртка для вызова file_exception
 */
class dof_exception_file extends file_exception
{
    function __construct($errorcode, $a = null, $debuginfo = null)
    {
        parent::__construct($errorcode, $a, $debuginfo);
    }
}

