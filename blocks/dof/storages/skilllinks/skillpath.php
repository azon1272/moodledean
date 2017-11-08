<?php
////////////////////////////////////////////////////////////////////////////
//                                                                        //
// NOTICE OF COPYRIGHT                                                    //
//                                                                        //
// Dean`s Office for Moodle                                               //
// Электронный деканат                                                    //
// <http://sourceforge.net/projects/freedeansoffice/>                     //
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

/**
 * Справочник связи компетенций с объектами. Класс формирования траектории обучения
 * 
 * @package    storage
 * @subpackage skilllinks
 * @author     Polikarpov Alexander <polikarpovst@gmail.com>
 * @copyright  2015
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class dof_storage_skilllinks_skillpath 
{
    /**
     * Объект деканата для доступа к общим методам
     * @var dof_control
     */
    protected $dof;
    
    /**
     * Массив исходных компетенций
     */
    protected $savail;
    
    /**
     * Массив целевых компетенций
     */
    protected $sdest;
    
    /**
     * Массив шагов
     */
    protected $spath;
    
    /**
     * Cчетчик перезапусков поиска
     */
    protected $laps;
    
    /** 
     * Конструктор
     * 
     * @param dof_control $dof - объект с методами ядра деканата
     */
    public function __construct($dof, $savail = array(), $sdest = array())
    {
        $this->dof = $dof;
        $this->savail = $savail;
        $this->sdest = array_diff($sdest, $savail);
        $this->spath = array();
        // Добавим нулевой шаг
        $zerostep = new stdClass;
        // Имеющиеся компетенции на выходе нулевого шага
        $zerostep->savail = $this->savail;
        // На нулевом шаге не нужно проходить курсы для получения уже имеющихся компетенций
        $zerostep->courses = array();
        // Компетенции, которые осталось пройти на конец нулевого шага
        $zerostep->sdest = $this->sdest;
        $this->spath[0] = $zerostep;
        
        $this->laps = 0;
    }
   
    public function searchSPath()
    {
        try 
        {
            // Получим целевые компетенции с последнего шага
            $laststep = end($this->spath);
            if ( empty($laststep->sdest) )
            {// Путь найден
                return true;
            }
            // Число шагов
            $steps = count($this->spath);
            // Cчетчик обратного поиска
            for ( $ireverse = 1; $ireverse < 11; ++$ireverse )
            {
                // Получим массив с курсами и недостижимыми компетенциями
                $courseskills = $this->getDestCourses($this->sdest);
                // Разбиваем результирующий массив на два
                list($destcourses, $illegalskills) = $courseskills;
                if ( ! empty($illegalskills) || empty($destcourses) )
                {// Поиск невозможен - вернем массив недостижимых компетенций
                    if ( $ireverse === 1 )
                    {// Недостижимы целевые компетенции
                        return $illegalskills;
                    }
                }
                
                // Начинаем заполнение курсами шагов изучения
                for ( $idirect = 1; $idirect <= $steps; ++$idirect )
                {// В каждый из шагов пытаемся вставить курс
                    foreach( $destcourses as $courseid )
                    {// Для каждого целевого курса
                        // Проверяем доступность курса по переданным компетенциям
                        $available = $this->isCourseOpen( $courseid, $this->spath[$idirect - 1]->savail );
                        if ( $available )
                        {// Курс доступен
                            // Добавим курс в шаг
                            $put = $this->putCourseToStage($courseid, $idirect);
                            if ( $put )
                            {// Курс добавили
                                // Перезапустим поиск
                                throw new skillkinksException('Search restart');
                            }
                        }
                    }
                }
                // Ни один курс не был добавлен - расширим список курсов, добавив 1 уровень к целевым курсам
                // Получим входящие компетенции целевых курсов
                $conds = array();
                $conds['plugintype'] = 'sync';
                $conds['plugincode'] = 'mdlskills';
                $conds['code'] = 'depend';
                $conds['objectid'] = $destcourses;
                $linksdepend = $this->dof->storage('skilllinks')->get_records($conds, '', 'id, skillid');
                if ( ! empty($linksdepend) )
                {// Есть входные компетенции
                    // Формируем массив входных компетенций курсов
                    $skills = array();
                    foreach( $linksdepend as $link )
                    {
                        $skills[$link->skillid] = $link->skillid;
                    }
                    
                    // Добавим компетенции курсов к целевым
                    $newsavail = array_merge($this->sdest, $skills);
                    $newsavail = array_unique($newsavail, SORT_NUMERIC);
                    $this->sdest = $newsavail;
                }
            }
        } catch ( skillkinksException $e )
        {
            $this->laps++;
            if ( $this->laps < 100)
            {
                $this->searchSPath();
                return true;
            }
        }
        return false;
    }
    
    /**
     * Получить путь
     * 
     * @return array
     */
    public function get_path()
    {
        return $this->spath;
    }
    
    /**
     * Получить id курсов, выдающие компетенции из входного массива
     * 
     * Резкльтат работы - два массива. В первом - курсы, предоставляющие любую из 
     * запрашиваемых компетенций. Во втором - массив недостижимых компетенций
     * 
     * @param array $sdest - Массив ID компетенций вида array($id1, $id2, $id3 ...)
     * 
     * @return array(array(), array()) - Результирующий массив
     */
    private function getDestCourses($sdest)
    {
        // Массив недостижимых компетенций(Компетенции не предоставляются ни одним курсом)
        $illegal = array();
        // Массив курсов 
        $courses = array();
        if ( empty($sdest) )
        {
            array($courses, $illegal);
        }
        // Условия поиска связей
        $conds = array();
        $conds['plugintype'] = 'sync';
        $conds['plugincode'] = 'mdlskills';
        $conds['code'] = 'provide';
        foreach ( $sdest as $skillid )
        {// Найдем связи по каждой компетенции
            $conds['skillid'] = $skillid;
            $links = $this->dof->storage('skilllinks')->get_records($conds, '', 'id, objectid');
            if ( empty($links) )
            {// Связей не найдено - добавим в список недостижимых компетенций
                $illegal[$skillid] = $skillid;
                continue;
            }
            
            // Добавим курсы, предоставляющие компетенцию
            foreach( $links as $link )
            {
                $courses[$link->objectid] = $link->objectid;
            }
        }
        
        // Вернем результат поиска
        return array($courses, $illegal);
    }
    
    /**
     * 
     * 
     */
    private function getSavailAfter()
    {
        // Получим последний шаг
        $path = array_pop($this->spath);
        if ( is_null($path) )
        {// Массив пуст, возврат исходных значений
            return $this->savail;
        }
        // 
        return $path->savail;
    }
    
    /**
     * Проверить доступность курса для переданных компетенций
     *
     * @param $courseid - ID курса
     * @param $savail - массив компетенций
     * 
     * return bool 
     */
    private function isCourseOpen($courseid, $svail)
    {
        // Получим входные компетенции для курса
        $conds = array();
        $conds['plugintype'] = 'sync';
        $conds['plugincode'] = 'mdlskills';
        $conds['code'] = 'depend';
        $conds['objectid'] = $courseid;
        $links = $this->dof->storage('skilllinks')->get_records($conds, '', 'id, skillid');
        if ( empty($links) )
        {// Курс без входных компетенций - доступен
            return true;
        }
        // Формируем массив входных компетенций для курса
        $skills = array();
        foreach( $links as $link )
        {
            $skills[] = $link->skillid;
        }
        // Вычитаем все требующиеся компетенции, которые доступны
        $diff = array_diff($skills, $svail);
        if ( empty($diff) )
        {// Курс доступен
            return true;
        }
        return false;
    }
    
    /**
     * Поместить курс на траекторию
     * 
     * Создает шаг, если его еще нет, добавляет туда курс 
     * и пересчитывает компетенции шагов
     *
     * @param $courseid - ID курса
     * @param $stage - Номер шага в траектории
     *
     * return bool
     */
    private function putCourseToStage($courseid, $stage)
    {
        // Проверяем доступность курса
        $available = $this->isCourseOpen( $courseid, $this->spath[$stage - 1]->savail );
        if ( empty($available) )
        {// Входных компетенций шага не хватает для прохождения этого курса
            return false;
        }
        // Проверяем наличие шага и создаем его при необходимости
        if ( ! isset($this->spath[$stage]) )
        {// Создаем шаг
            $step = new stdClass;
            // Имеющиеся компетенции 
            $step->savail = $this->spath[$stage - 1]->savail;
            // На нулевом шаге не нужно проходить курсы для получения уже имеющихся компетенций
            $step->courses = array();
            // Компетенции, которые осталось пройти на конец нулевого шага
            $step->sdest = $this->spath[$stage - 1]->sdest;
            $this->spath[$stage] = $step;
        }
        
        // Ищем добавляемый курс
        foreach ( $this->spath as &$st )
        {
            if ( isset( $st->courses[$courseid] ) )
            {// Курс уже был добавлен
                return false;
            }
        }
        
        // Включаем курс в шаг
        $this->spath[$stage]->courses[$courseid] = $courseid;
        // Получим выходные компетенции курса
        $conds = array();
        $conds['plugintype'] = 'sync';
        $conds['plugincode'] = 'mdlskills';
        $conds['code'] = 'provide';
        $conds['objectid'] = $courseid;
        $links = $this->dof->storage('skilllinks')->get_records($conds, '', 'id, skillid');
        if ( empty($links) )
        {// Выходных компетенций курса нет
            return true;
        }
        // Формируем массив выходныx компетенций курса
        $skills = array();
        foreach( $links as $link )
        {
            $skills[$link->skillid] = $link->skillid;
        }
        // Пересчитываем входные компетенции в соответствии с добавляем курсом
        foreach ( $this->spath as $stepnum => &$step ) 
        {
            if ( $stepnum >= $stage )
            {// Следующий или текущий шаг
                // Добавим компетенции курса
                $newsavail = array_merge($this->spath[$stepnum]->savail, $skills);
                $newsavail = array_unique($newsavail, SORT_NUMERIC);
                $this->spath[$stepnum]->savail = $newsavail;
                // Удалим целевые компетенции с шага
                $this->spath[$stepnum]->sdest = 
                    array_diff($this->spath[$stepnum]->sdest, $this->spath[$stepnum]->savail);
            }   
        }
        return true;
    }
}   

// Объявляем новый класс исключения
class skillkinksException extends Exception {}

?>