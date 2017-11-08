<?php
/*
 * Класс для вывода страниц
 */
class dof_modlib_widgets_pages_navigation
{
    var $dof;
    var $code;      // код плагина
    var $count;     // количество записей
    var $limitnum;  // максимальное кол-во записей, отображенное на странице
    var $limitfrom; // номер записи, с которой начинается просмотр
    protected $limitline;     // значение, в пределах которого страницы будут отображаться в одну строку
    protected $step;          // шаг, с которым на будет идти переход другие страницы
    protected $sidecount;     // кол-во страниц для левой и правой частей
    protected $centercount;   // кол-во страниц центральной части
    
    /** Конструктор класса. Осуществляет все проверки.
     * 
     * @param dof_control - глобальный объект $DOF 
     * @param string $code - код плагина
     * @param int $count - количество записей
     * @param int $limitnum - по сколько записей на странице выводить
     * @param int $limitfrom - начиная с какой записи выводить
     * @param int $limitline - предельное значение, при котором выводятся все страницы
     * @param int $step - шаг для промежуточных страниц
     * @param int $sidecount - кол-во ссылок для левой и правой частей
     * @param int $centercount - кол-во ссылок для центральной части
     */
    function __construct(dof_control $dof, $code, $count, $limitfrom, $limitnum=NULL, 
            $limitline=12, $step=10, $sidecount=5, $centercount=7)
    {
        $this->dof            = $dof;
        $this->code           = $code;
        $this->count          = $count;
        $this->limitline      = $limitline;
        $this->step           = $step;
        $this->sidecount      = $sidecount;
        $this->centercount    = $centercount;
        
        if ( is_int($limitfrom) AND ($limitfrom > 0) )
        {// если limitfrom положительное число
            // сохраняем его
            $this->limitfrom = $limitfrom;
        }else
        {// в остальных случаях отображаем список с первой записи
            $this->limitfrom = 1;
        }
        if ( is_int($limitnum) AND ($limitnum > 0) )
        {// если limitnum положительное число
            // сохраняем его
            $this->limitnum = $limitnum;
        } elseif( is_null($limitnum) )
        {// если limitnum не указан
            $this->limitnum = $this->dof->modlib('widgets')->get_limitnum_bydefault();
        } else
        {// если их там нет 
            // будем выводить на странице 10 наименований
            $this->limitnum = 30;
        }
        
        if ( ! is_int($limitline) OR intval($limitline) < 12 )
        {// Число страниц 
            $this->limitline = 12;
        }
        
        if ( ! is_int($step) OR intval($step) < 5 )
        {// шаг не должен быть слишком мелким
            $this->step = 5;
        }
        
        if ( ! is_int($sidecount) OR intval($sidecount) < 3 )
        {// зададим минимальное значение для $sidecount
            $this->sidecount = 3; 
        }
        
        if ( ! is_int($centercount) OR intval($centercount) < 5 )
        {// зададим минимальное значение для $centercount
            $this->$centercount = 5;
        }
        
        $this->dof->modlib('nvg')->add_js('modlib', 'widgets', '/js/singleselect.js', false);
    }
    
    /** Возвращает html-строку навигации,
     * разбивая ленту таблицы или списка на страницы.
     * Нумерация страница всегда начинается с единицы
     * 
     * @param string $adds - список дополнительных параметров для ссылки в виде строки
     * @param array $vars - список дополнительных параметров для ссылки 
     * @return string строка url-адреса с полным списком ссылок 
     * на все перечисляемые страницы
     */
    public function get_navpages_list($adds=null, $vars=array() )
    {
        $result = '';
        
        // вычислим общее количество страниц
        $totalpages = $this->get_total_pages();
        if ( $totalpages > 1 )
        {
            // создим ссылки на все страницы с самого начала
            $limitfrom = 1;
            if ( $totalpages <= $this->limitline )
            {// кол-во страниц в пределах допустимого - выведем ссылки в линию
                for ($pagenum = 1; $pagenum <= $totalpages; $pagenum++)
                {
                    $result .= $this->get_page_link($pagenum, $limitfrom, $adds, $vars);
                    $limitto = $limitfrom + $this->limitnum;
                    if ( ( $limitto - $this->limitfrom ) < $this->limitnum  && ( $limitto - $this->limitfrom ) > 0 )
                    {
                        $limitfrom = $this->limitfrom;
                    } else
                    {
                        $limitfrom = $limitfrom + $this->limitnum;
                    }
                }
            } else
            {// в противном случае задаем особую структуру
                $result .= $this->get_triple_navigation($adds, $vars);
            }
        }
        
        $result = '<div class="paging dof_paging">'.$result;
        
        // ВЫПАДАЮЩИЙ СПИСОК С ЧИСЛОМ ЭЛЕМЕНТОВ НА ОДНОЙ СТРАНИЦЕ
        $currenturl = $this->dof->url_im($this->code, $adds, $vars);
        $limitselect = [
                        20 => 20,
                        50 => 50,
                        100 => 100
        ];
        $limitselect[$this->limitnum] = $this->limitnum;

        $result .= '<form style="float: right;" class="dof_single_select" method="get" action="'.$currenturl.'" >
            <span>'.$this->dof->get_string('pagination_select_limitnum', 'widgets', NULL, 'modlib').'</span>
                <select id="dof_limitnumselectfield" class="select" name="limitnum">';
        // Формирование выпадающего списка
        foreach ( $limitselect as $option )
        {
            if ( $option == $this->limitnum )
            {
                $result .= '<option selected="selected" value="'.$option.'">'.$option.'</option>';
            } else 
            {
                $result .= '<option value="'.$option.'">'.$option.'</option>';
            }
        }
        $result .= '</select>';
        // Все прочие GET параметры
        if ( ! empty($vars) )
        {
            foreach ( $vars as $name => $getvar )
            {
                if ( $name == 'limitnum' )
                {
                    continue;
                }
                if ( is_array($getvar) )
                {
                    continue;
                }
                $result .= '<input type="hidden" name="'.$name.'" value="'.$getvar.'">';
            }
        }
        $submitstring = $this->dof->get_string('select', 'widgets', null, 'modlib');
        $result .= '
                <noscript class="inline"><input type="submit" value="'.$submitstring.'" /></noscript>
                </form></div>
                <div style="clear: both;"></div>';
        
        return $result;
    }
    
    /** Возвращает ссылку на страницу или 
     * страницу без ссылки, если она является текущей
     * 
     * @param int $pagenum - номер страницы
     * @param int $limitfrom - номер записи, с которой начинается просмотр
     * @param string $adds - список дополнительных параметров для ссылки в виде строки
     * @param array $vars - список дополнительных параметров для ссылки 
     * @return string строка url-адреса со всеми параметрами 
     */
    private function get_page_link($pagenum, $limitfrom, $adds, $vars)
    {
        $result = '';
        // узнаем номер текущей страницы
        $currentpagenum = $this->get_current_page();
        if ( $currentpagenum == $pagenum )
        {// если это та страница, на которой мы сейсас находимся - то выводим ее как текст
            $result .= '<b>'.$pagenum.'</b> &nbsp;';
        }else
        {// во всех остальных случаях выводим ее как ссылку
            // добавляем номер страницы к параметрам ссылки
            $vars['limitfrom'] =  $limitfrom;
            $result .= '<a href="'.$this->dof->url_im($this->code, $adds, $vars).'">'.$pagenum.'</a> &nbsp;';
        }
        // возвращаем результат
        return $result;
    }
    
    /** Получить номер страницы по номеру текущей записи, и количеству записей на странице
     * 
     * @return int номер страницы
     */
    private function get_current_page()
    {
        // Общее число страниц
        $totalpages = $this->get_total_pages();
        // Определение номера текущей страницы
        $currentpage = ceil(($this->limitfrom - 1) / $this->limitnum) + 1 ;
        if ( $currentpage > $totalpages )
        {// Превышение лимита - переход на последнюю страницу
            $currentpage = $totalpages;
        }
        if ( $currentpage < 1 )
        {// Нормализация значения
            $currentpage = 1;
        }
        return $currentpage;
    }
    
    /** 
     * Возвращает общее количество страниц для отображения
     * @return int количество страниц
     */
    private function get_total_pages()
    {
        // Дополнительная страница, если смещение некорректное
        $additional = ($this->limitfrom - 1) / $this->limitnum;
        $additional = ceil($additional - floor($additional));
        // Страниц начиная с текущей
        $count = ceil($this->count  / $this->limitnum ) + $additional;
        return $count;
    }
    
    /** Возвращает исходный параметр номера записи, с которого начинается просмотр
     */
    public function get_current_limitfrom()
    {
        return $this->limitfrom;
    }
        
    /** Возвращает исходный параметр минимального кол-ва записей на странице
     * @return int
     */
    public function get_current_limitnum()
    {
        return $this->limitnum;
    }
    
    /** Возвращает ссылку для сдвига страницы
     * 
     * @param int $type - тип ссылки
     * @param int $limitfrom - номер записи, с которой начинается просмотр
     * @param string $adds - список дополнительных параметров для ссылки в виде строки
     * @param array $vars - список дополнительных параметров для ссылки 
     * @return string строка url-адреса со всеми параметрами 
     */
    private function get_tool_link($type, $adds, $vars)
    {
        $result = '';
        // заголовок для ссылки
        $title = $this->dof->get_string('pages_nav_page', 'widgets', null, 'modlib').'&nbsp;';
        // номер текущей страницы
        $thispagenum = $this->get_current_page();
        // кол-во страниц
        $totalpages = $this->get_total_pages();
        
        switch($type)
        {
            case 'left-next':
                // переход на страницу вперед
                $title .= ($this->sidecount + 1);
                $vars['limitfrom'] = $this->sidecount * $this->get_current_limitnum() + 1;
                $result .= '<a href="'.$this->dof->url_im($this->code, $adds, $vars).'" title="'
                        .$title.'">...</a> &nbsp;';
                break;
            case 'right-prev':
                // переход на страницу назад
                $title .= ($this->sidecount - 1);
                $vars['limitfrom'] = ($this->sidecount - 2) * $this->get_current_limitnum() + 1;
                $result .= '<a href="'.$this->dof->url_im($this->code, $adds, $vars).'" title="'
                        .$title.'">...</a> &nbsp;';
                break;
            case 'center-next':
                // переход вперед из центральной части
                $title .= ($thispagenum + 1);
                $vars['limitfrom'] = $thispagenum * $this->get_current_limitnum() + 1;
                $result .= '<a href="'.$this->dof->url_im($this->code, $adds, $vars).'" title="'
                        .$title.'">&#9658;</a> &nbsp;';
                break;
            case 'center-prev':
                // переход назад из центральной части
                $title .= ($thispagenum - 1);
                $vars['limitfrom'] = ($thispagenum - 2) * $this->get_current_limitnum() + 1;
                $result .= '<a href="'.$this->dof->url_im($this->code, $adds, $vars).'" title="'
                        .$title.'">&#9668;</a> &nbsp;';
                break;
            case 'center-next-step':
                // переход вперед из центральной части с шагом
                $page = $thispagenum + $this->step;
                $title .= ($page);
                $vars['limitfrom'] = ($thispagenum+$this->step-1) * $this->get_current_limitnum() + 1;
                if ( $thispagenum + $this->step > $totalpages )
                {// выходим за пределы - ссылку не выводим
                    return '';
                }
                $result .= '...&nbsp;&nbsp;<a href="'.$this->dof->url_im($this->code, $adds, $vars).'" title="'
                        .$title.'">'.$page.'</a> &nbsp;';
                break;
            case 'center-prev-step':
                // переход назад из центральной части с шагом
                $page = $thispagenum - $this->step;
                $title .= ($page);
                $vars['limitfrom'] = ($thispagenum-$this->step-1) * $this->get_current_limitnum() + 1;
                if ( ($thispagenum - $this->step) < 1 )
                {// выходим за пределы - ссылку не выводим
                    return '';
                }
                $result .= '<a href="'.$this->dof->url_im($this->code, $adds, $vars).'" title="'
                        .$title.'">'.$page.'</a>&nbsp;&nbsp;...&nbsp;';
                break;
            case 'one-next':
                // выводим ссылку с шагом
                $page = $this->sidecount + $this->step;
                $title .= ($page);
                if ( $page >= $totalpages - $this->sidecount )
                {// выходим за пределы - ссылку не выводим
                    return '';
                }
                $vars['limitfrom'] = ($this->sidecount+$this->step-1) * $this->get_current_limitnum() + 1;
                $result .= '<a href="'.$this->dof->url_im($this->code, $adds, $vars).'" title="'
                        .$title.'">'.$page.'</a>&nbsp;&nbsp;...&nbsp;';
                break;
        }
        // возвращаем результат
        return $result;
    }
    
    /** Возвращает номер первой центральной страницы
     * @return int
     */
    private function get_center_pagenum()
    {// определим номер текущей страницы
        $pagenum = $this->get_current_page();
        return $pagenum - ceil($this->sidecount / 2 - 1);
    }
    
    /** Определяет, является ли текущая страница крайней
     * @return boolean
     */
    private function is_side_page()
    {
        // определим номер текущей страницы
        $pagenum = $this->get_current_page();
        // количество страниц
        $totalpages = $this->get_total_pages();
        
        if ( $pagenum >= 1 AND $pagenum <= $this->sidecount)
        {// страница на левой стороне
            return true;
        }elseif ( $pagenum > $totalpages - $this->sidecount AND $pagenum <= $totalpages )
        {// страница на правой стороне
            return true;
        }
        return false;
    }
    
    /** Возвращает трехстороннюю систему навигации
     * @param string $adds - список дополнительных параметров для ссылки в виде строки
     * @param array $vars - список дополнительных параметров для ссылки
     * @return html - код навигационной цепочки
     */
    private function get_triple_navigation($adds=NULL, $vars=NULL)
    {
        $result = '';
        
        // вычислим общее количество страниц
        $totalpages = $this->get_total_pages();
        // стартовая страница
        $start = $this->get_current_page() - $this->sidecount; 
        if ( $start < 1 ) 
        {// должна быть не меньше 1
            $start = 1; 
        }
        // конечная страница
        $end = $this->get_current_page() + $this->sidecount - 1; 
        if ( $end > $totalpages ) 
        {// должна быть не более максимального кол-ва страниц
            $end = $totalpages; 
        }
        if ( $start > 1 ) 
        {// создаем ссылку на первую страницу
            $limitfrom = 1;
            $result .= $this->get_page_link(1, $limitfrom, $adds, $vars);
        }
        if ( $start > 1 )  
        {// определяем есть ли разрыв между началом и серединой
            if ( $start - 2 > 0 )
            {// есть
                $pagenum = '...';
            }else
            {// нету
                $pagenum = '';
            }
            // делаем ссылку на разрыв
            $limitfrom = 1 + $this->get_current_limitnum();
            $result .= $this->get_page_link($pagenum, $limitfrom, $adds, $vars);
        }
        // создаем промежуточные страницы
        for ($pagenum=$start; $pagenum<=$end; $pagenum++)
        {
            $limitfrom = 1 + ($pagenum-1) * $this->get_current_limitnum();
            $result .= $this->get_page_link($pagenum, $limitfrom, $adds, $vars);
        }
        if ( $end + 1 < $totalpages ) 
        {// определяем есть ли разрыв между серединой и концом
            if ( $end + 2 == $totalpages )
            {// нету
                $pagenum = '';
            }else
            {// есть
                $pagenum = '...';
            // делаем ссылку на разрыв
            $limitfrom = 1 + ($totalpages - 2) * $this->get_current_limitnum();
            $result .= $this->get_page_link($pagenum, $limitfrom, $adds, $vars);
            }
        }
        if ( $end + 1 <= $totalpages ) 
        {// создаем ссылку на последнюю страницу
            $limitfrom = 1 + ($totalpages - 1) * $this->get_current_limitnum();
            $result .= $this->get_page_link($totalpages, $limitfrom, $adds, $vars);
        }
        // возвращаем результат
        return $result;
    }
}
?>