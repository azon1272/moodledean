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

//загрузка библиотеки предыдущего уровня
require_once(dirname(realpath(__FILE__))."/../lib.php");

/** Библиотека для работы с таблицей списка плагинов
 *  а также для реализации интерфейса 
 *  удаления, обновления, установки плагинов
 */
//добавим уровень списка плагинов в панель навигации
$DOF->modlib('nvg')->add_level($DOF->get_string('plugin_list', 'admin'), $DOF->url_im('admin', '/plugins/index.php'));

    /** Возвращает плагины, которые надо установить
     * 
     */
    function for_install($type)
    {
		global $DOF;
		$indir = $DOF->plugin_list_dir($type);
		$indb = $DOF->plugin_list($type);
		return array_diff_key($indir, $indb);
    }
    /** Возвращает плагины, которые надо обновлять
     * 
     */
    function for_upgrade($type)
    {
		global $DOF;
		$rez = array();
		$indir = $DOF->plugin_list_dir($type);
		$indb = $DOF->plugin_list($type);
		$both = array_intersect_key($indir, $indb);
		foreach ( $both as $code=>$plugin)
		{
			if ( $indir[$code]['version'] > $indb[$code]['version'])
			{
				$rez[$code] = $plugin;
			}
		}
		return $rez; 
    }
    /** Возвращает ошибки
     * массив в котором перечислены плагины, 
     * которые значатся в базе плагинов, 
     * но отсутствуют в файловой системе;
     * массив плагинов, у которых дата текущей версия больше даты новой версии
     */
    function plugin_error($type)
    {
		global $DOF;
		$indir = $DOF->plugin_list_dir($type);
		$indb = $DOF->plugin_list($type);
		$nodir = array_diff_key($indb, $indir);
		$both = array_intersect_key($indir, $indb);
		$noup = array();
		foreach ( $both as $code=>$plugin)
		{
			if ( $indir[$code]['version'] < $indb[$code]['version'])
			{
				$noup[$code] = $plugin;
			}
		}
		return array ('install' => $nodir,
					  'upgrade' => $noup); 
    }
	/** Возвращает строку из таблицы плагинов
     * @param string $type - тип плагина, который должен быть отбражен в строке
     * @param string - $code - уникальное имя плагина, который должен быть отбражен в строке
     * @param bool $install - true - надо вернуть ссылку на страницу установки плагина,
     * false - пустая строка
     * @param bool $upgrade - true - надо вернуть ссылку на страницу обновления плагина,
     * false - пустая строка
     * @param bool $delete - true - надо вернуть ссылку на страницу удаления плагина,
     * false - пустая строка
 	 * @return array массив для вывода строки на экран функцией print_table()
	 */
	function get_row($type, $code, $install = false, $upgrade = false, $delete = true)
	{
		global $DOF;
		//$instcell = $upcell = $delcell= '';//начальные значения
		$rez = '';//начальные значения
		if ($delete)
		{//надо вернуть ссылку на страницу удаления плагина
			$rez .= "&nbsp;<a href=\"delete.php?type={$type}&code={$code}\">".$DOF->get_string('delete', 'admin', null).'</a>&nbsp;';
			$title = $DOF->get_string('title', $code, null, $type);
		}
		if ($install)
		{//надо вернуть ссылку на страницу установки плагина
			$forinstall = for_install($type);//список плагинов для обновления
			if ( is_array($forinstall) AND array_key_exists($code, $forinstall) )
			{//текущий плагин может быть обновлен - выведем его новую версию после названия
				$title = $DOF->get_string('title', $code, null, $type).'&nbsp('.$forinstall[$code]['version'].')';
			}else
			{//обновление не требуется - выведем одно название
				$title = $DOF->get_string('title', $code, null, $type);
			}
			//надо вернуть ссылку на страницу обновления плагина
			$rez .= "&nbsp;<a href=\"install.php?type={$type}&code={$code}\">".$DOF->get_string('install', 'admin', null).'</a>&nbsp;';
		}
		if ($upgrade)
		{//возможно обновление плагина
			$forupgrade = for_upgrade($type);//список плагинов для обновления
			if ( is_array($forupgrade) AND array_key_exists($code, $forupgrade) )
			{//текущий плагин может быть обновлен - выведем его новую версию после названия
				$title = $DOF->get_string('title', $code, null, $type).'&nbsp('.$forupgrade[$code]['version'].')';
			}else
			{//обновление не требуется - выведем одно название
				$title = $DOF->get_string('title', $code, null, $type);
			}
			//надо вернуть ссылку на страницу обновления плагина
			$rez .= "&nbsp;<a href=\"upgrade.php?type={$type}&code={$code}\">".$DOF->get_string('upgrade', 'admin', null).'</a>&nbsp;';
		}
		if ( ! $install AND ! $upgrade AND ! $delete )
		{//с плагином что-то не так - вернем сообщение об ошибке
			$rez .= 'error&nbsp;&nbsp;installation&nbsp;&nbsp;plugin';
			$title = $DOF->get_string('title', $code, null, $type);
		}
		$plugin = $DOF->plugin_getrec($type, $code);
		$version = $plugin ? $plugin->version : '';
		
		// Добавляем ссылку на im
        if ($type === 'im')
        {
            $title = "<a href=\"{$DOF->url_im($code)}\">{$title}</a>";
        }
		
		return array($title, $version, $rez);
	}
	/** Возвращает таблицу плагинов одного типа
	 * @param array $plugins - массив плагинов для вывода в таблицу
	 * @return string - html-код таблицы с названием
	 */
	function plugin_table($plugins)
	{
		global $DOF;
		$rez = '';		
		$table = new stdClass();
		$table->head = array($DOF->get_string('plugin_name', 'admin'),
					  $DOF->get_string('version'),
					  $DOF->get_string('movement', 'admin'));
		$table->align = array('left', 'center', 'center');
		$table->width = '100%';
		$one = current($plugins);
		$forinstall = for_install($one['type']);
		$forupgrade = for_upgrade($one['type']);
		$pluginerror = plugin_error($one['type']);
		foreach ( $plugins as $plugin )
		{//print_object($plugin);
			if ( array_key_exists($plugin['code'], $forinstall) )
			{
				$install = true;
				$upgrade = false;
				$delete = false;
			}elseif ( array_key_exists($plugin['code'], $forupgrade) )
			{
				$install = false;
				$upgrade = true;
				$delete = true;
			}elseif ( ! array_key_exists($plugin['code'], $pluginerror['upgrade']) AND
			 		  ! array_key_exists($plugin['code'], $pluginerror['install']) )
			{
				$install = false;
				$upgrade = false;
				$delete = true;
			}else
			{
				$install = false;
				$upgrade = false;
				$delete = false;
			}
			$table->data[] = get_row($plugin['type'], $plugin['code'], $install, $upgrade, $delete, $forupgrade);
		}
//		$rez .= print_heading($DOF->get_string($plugin['type'].'s', 'admin', null), '', 3, 'main', true);
		$rez .= $DOF->modlib('widgets')->print_table($table, true);
		return $rez;
	}
?>