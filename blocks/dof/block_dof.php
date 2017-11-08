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
class block_dof extends block_base {

    /**
     * Инициализация блока
     */
    public function init() {
        $this->title = get_string('title', 'block_dof');
    }

    /**
     * Включает интерфейс настроек
     */
    public function has_config() {
        return true;
    }

    /**
     * Возвращает содержимое блока "Электронный деканат"
     */
    public function get_content() {

        require_once(dirname(realpath(__FILE__)) . '/lib.php');
        global $CFG, $COURSE, $USER, $DOF;
        $this->content         = new stdClass();
        $this->content->footer = '';
        $this->content->text   = '';
        if ($DOF->is_access('view')) {   // Пользователь имеет доступ к деканату
            $blocknotes = array();
            // определяем тип контента блока
            $type = $this->get_format_content();
            switch ($type) {
                case "main":
                    $fstring = '/cfg/blocknotesmain.php';
                    break;
                case "my":
                    $fstring = '/cfg/blocknotesmy.php';
                    break;
                case "other":
                    $fstring = '/cfg/blocknotes.php';
                    break;
            }
            if (file_exists($bn_file = dirname(realpath(__FILE__)) . $fstring)) {
                include($bn_file);
            }
            foreach ($blocknotes as $plugin) {
                // Пока содержимое берем только из модуля im/standard,
                // потом это будет управляться настройками
                $this->content->text .= $DOF->im($plugin['code'])->get_blocknotes($type);
            }
        }
        return $this->content;
    }

    /**
     * Запускается из admin/cron.php и исполняет cron() и todo() во всех плагинах
     */
    public function cron() {
        //
        $result = true;
        require_once(dirname(realpath(__FILE__)) . '/lib.php');
        global $CFG, $COURSE, $USER, $DB, $DOF;
        // Добавляем todo для проверки
        // $DOF->add_todo('im','standard','qqq');

        dof_mtrace(1, "\nLoad Dean`s Office Cron");
        // Определяем текущую загрузку системы
        $loan     = dof_get_loan();
        // Задаем уровень вывода сообщений (0-3)
        $messages = 3;

        // Исполняем задания
        dof_mtrace(1, "Load todo`s: ");
        //  Получаем список неисполненных заданий
        $todos = $DB->get_records_select('block_dof_todo', "exdate=0 AND tododate<" . time() . " AND loan<={$loan}");
        // Избегаем ошибки обработки пустых списков
        if (!$todos) {
            $todos = array();
        }
        foreach ($todos as $todo) {
            // Предварительно отмечаем событие, как исполненное
            $todo2          = new stdClass();
            $todo2->id      = $todo->id;
            $todo2->exdate  = time();
            $DB->update_record('block_dof_todo', $todo2);
            // Запускаем задание
            $todo->mixedvar = unserialize($todo->mixedvar);
            if (!is_object($todo->mixedvar)) {
                $todo->mixedvar = new stdClass();
            }
            $todo->mixedvar->personid = $todo->personid;
            $filedata                 = new stdClass;
            $filedata->plugintype     = $todo->plugintype;
            $filedata->plugincode     = $todo->plugincode;
            $filedata->filename       = $todo->todocode . '/' . $todo->id;
            $DOF->mtrace(1, "Todo: {$todo->plugintype}/{$todo->plugincode}/{$todo->todocode} ", '', true, $filedata);
            if ($DOF->plugin($todo->plugintype, $todo->plugincode)->todo($todo->todocode, $todo->intvar, $todo->mixedvar)) {
                // Обновляем время завершения исполнения
                $todo2->exdate = time();
                $DB->update_record('block_dof_todo', $todo2);
                $DOF->mtrace(1, " [ok]", "\n", true, $filedata);
            } else {
                // Помечаем снова как неисполненное
                $todo2->exdate = 0;
                $DB->update_record('block_dof_todo', $todo2);
                $DOF->mtrace(1, " [error]", true, $filedata);
                $result        = false;
            }
            // Помечаем задание как исполненное
        }
        // Исполняем cron
        dof_mtrace(1, "Load plugins cron: ");
        // Получеем список модулей, для которых нужно запускать крон
        $plugins = $DB->get_records_select('block_dof_plugins', "cron>0 AND (lastcron IS NULL OR (lastcron+cron)<" . time() . ")");
        if (!$plugins) {
            $plugins = array();
        }
        foreach ($plugins as $plugin) {
            dof_mtrace(1, "Cron: {$plugin->type}/{$plugin->code} ", '');
            // Предварительно помечаем задание, как исполненное
            // (Уменьшаем вероятность запуска двух процессов)
            $plugin2           = new stdClass();
            $plugin2->lastcron = time();
            $plugin2->id       = $plugin->id;
            $DB->update_record('block_dof_plugins', $plugin2);
            // Исполняем задание и проверяем результат
            if ($DOF->plugin($plugin->type, $plugin->code)->cron($loan, $messages)) {
                if ((time() - $plugin2->lastcron) > 30) {
                    // Обновляем время завершения исполнения
                    $plugin2->lastcron = time();
                    $DB->update_record('block_dof_plugins', $plugin2);
                }
                dof_mtrace(1, " [ok]");
            } else {
                dof_mtrace(1, " [error]");
                $result = false;
            }
        }
        dof_mtrace(1, "Finished Dean`s Office Cron");
        return $result;
    }

    /**
     * Определяет откуда запущен контент блока
     * @return boolean
     */
    public function get_format_content() {
        global $PAGE, $CFG;
        $path = $PAGE->url->out();
        if (strstr($path, "blocks/dof/im/my")) {// контент запущен со страниц im/my
            return "my";
        } else if (preg_match("{{$CFG->wwwroot}/(index.php)?$}", $path)) {
            // контент запущен с главной страницы
            return "main";
        }
        // контент запущен с остальных страниц
        return "other";
    }
}

?>
