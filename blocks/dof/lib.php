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

// Ищем конфигурационный файл MOODLE
if ( ! file_exists(dirname(realpath(__FILE__)).'/../../config.php') )
{
    header('Location: /install.php');
    exit();
}

// Подключаем конфигурационные файлы MOODLE
require_once(dirname(realpath(__FILE__)).'/../../config.php');
global $CFG, $DB;
require_once($CFG->libdir.'/dmllib.php');
require_once($CFG->libdir.'/ddllib.php');
require_once($CFG->libdir.'/filestorage/file_exceptions.php');

// Загружаем собственные библиотеки
include_once($CFG->dirroot.'/blocks/dof/lib/utils.php');
include_once($CFG->dirroot.'/blocks/dof/lib/dof.php');
include_once($CFG->dirroot.'/blocks/dof/lib/plugin.php');
include_once($CFG->dirroot.'/blocks/dof/lib/im.php');
include_once($CFG->dirroot.'/blocks/dof/lib/modlib.php');
include_once($CFG->dirroot.'/blocks/dof/lib/storage.php');
include_once($CFG->dirroot.'/blocks/dof/lib/storage_base.php');
include_once($CFG->dirroot.'/blocks/dof/lib/sync.php');
include_once($CFG->dirroot.'/blocks/dof/lib/workflow.php');
include_once($CFG->dirroot.'/blocks/dof/lib/events.php');
include_once($CFG->dirroot.'/blocks/dof/lib/exception.php');
include_once($CFG->dirroot.'/blocks/dof/lib/message.php');

// Создаем объект контроллера
global $DOF;
$DOF = new dof_control($CFG);

// Добавление ID экземпляра блока к Контроллеру
$instances = $DB->get_records('block_instances', ['blockname' => 'dof']);
if ( empty($instances) )
{// Экземпляр не определен
    $DOF->instance = NULL;
} else 
{
    $instance = array_shift($instances);
    $DOF->instance = $instance;
}

/**
 * Подготовка сохраненных файлов блока
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param context $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @param array $options
 * 
 * @return bool
 */
function block_dof_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = []) 
{
    
    // Проверка на контекст
    if ( $context->contextlevel != CONTEXT_BLOCK ) 
    {
        return false;
    }
    
    // Проверка на зону
    if ( $filearea !== 'public' ) 
    {
        return false;
    }
    
    // Проверка прав доступа
    if ( ! has_capability('block/dof:view', $context) ) 
    {
        return false;
    }
    
    // ID файловой зоны 
    $itemid = array_shift($args);
    
    // Имя файла
    $filename = array_pop($args);
    
    // Путь файла
    if ( ! $args ) 
    {
        $filepath = '/'; 
    } else {
        $filepath = '/'.implode('/', $args).'/';
    }
    
    // Получение файлового хранилища
    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'block_dof', $filearea, $itemid, $filepath, $filename);
    if ( ! $file) 
    {// Файл не найден
        return false; 
    }

    \core\session\manager::write_close();
    send_stored_file($file, null, 0, $forcedownload, $options);
}
?>