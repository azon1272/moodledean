<?php
/*
 * базовый класс для всех прогрессбаров
 */
class dof_modlib_widgets_progressbar
{
    var $name;      // Название (html-атрибут name) html-элемента, представляющего прогрессбар (например картинка).
    var $width;     // Максимальная длина в пикселях.
    var $progressbar;
    var $options;

    /** 
     * Function progress_bar - Конструктор класса
     * Параметры:
     * $name    - Название (html-атрибут name) html-элемента, представляющего прогрессбар (например картинка).
     * $percent - Начальное процентное значение
     * $width   - длина в пикселях.
     * $process - название выполняемого процесса (сохранение... загрузка... и т. д.)
     * $auto_create если TRUE то функция create() будет вызвана сразу же после создания обьекта
     */
    function __construct($name = 'pbar',$width = 100,$options=null,$auto_create = true)
    {
        // @todo - пока на мудловском
        $this->progressbar = new progress_bar($name,$width);
        // задаем html-имя картинки
        $this->name    = $name;
        // задаем длину в пикселях
        $this->width   = $width;
        if($auto_create)
        {
            $this->create();
        }
        $this->options = $options;
    }
    
    /** 
     * Конструктор класса для старых версий php
     * Параметры:
     * $name    - Название (html-атрибут name) html-элемента, представляющего прогрессбар (например картинка).
     * $percent - Начальное процентное значение
     * $width   - длина в пикселях.
     * $process - название выполняемого процесса (сохранение... загрузка... и т. д.)
     * $auto_create если TRUE то функция create() будет вызвана сразу же после создания обьекта
     */
    function dof_modlib_widgets_progressbar($name = 'pbar',$width = 100,$options=null,$auto_create = true)
    {
        return $this->__construct($name,$width,$auto_create);
    }
    
    /** Function create() - выводит прогрессбар в виде html элемента.
     * (Внимание: не вызывайте эту функцию, если $auto_create 
     * в конструкторе стоит TRUE)
     */
    function create()
    {
        $this->progressbar->create();
    }
    
    /** Function create() - выводит прогрессбар в виде html элемента.
     * (Внимание: не вызывайте эту функцию, если $auto_create 
     * в конструкторе стоит TRUE)
     */
    function update($data)
    {
        $this->dof->modlib('widgets')->update_progressbar($this->options['plugintype'], $this->options['plugincode'], $this->options['querytype'], $data);
    }

    /** 
     * Function set_name() - устанавливает $name - имя html-элемента прогрессбара
     * 
     * Параметры:
     * $name - имя html-элемента
     * (эта функция бесполезна после вызома метода create())
     */
    private function set_name($name)
    {
        $this->name = $name;
    }

    /**
     * Function set_percent() - Устанавливает начальное процентное значение
     * (поле $percent) для прогрессбара
     * 
     * Параметры:
     * $percent - начальное процентное значение
     */
    function set_percent($percent)
    {
        $this->percent = $percent;
    }

    /**
     * Function set_percent_adv() - Увеличивает значение прогрессбара, на небольшое значение,
     * основываясь на номере текущей задачи, и общем количеством задач.
     * Эта функция выводит на страницу кототкую строку javascript, которая тут же исполняется
     * 
     * Параметры:
     * $cur_amount номер выполняемой задачи
     * $max_amount общее количество задач, которые надо выполнить
     */
    function set_percent_adv($cur_amount,$max_amount)
    {
        $this->percent = ($cur_amount / $max_amount) * 100;
        echo('<script>document.images.' . $this->name . '.width = ' . ($this->percent / 100) * $this->width . '</script>');
        flush();
    }

    /**
     * Function set_width() - устанавливает максимальную длинну прогрессбара.
     * 
     * parameters:
     * $width - длина в прикселях
     */
    private function set_width($width)
    {
        $this->width = $width;
    }
}
?>