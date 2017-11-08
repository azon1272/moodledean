<?php 

// Подключаем библиотеки
require_once('lib.php');

// принятые данные
$id = required_param('id', PARAM_INT);
$delete = optional_param('delete', 0, PARAM_BOOL);

// проверки
// не найден отчет
if ( ! $report  = $DOF->storage('reports')->get($id) )
{// вывод сообщения и ничего не делаем
    print_error($DOF->get_string('notfound_report','reports', $id));
}
// проверка прав
if ( ! $DOF->storage('reports')->is_access('delete',$id) AND $report->personid != $DOF->storage('persons')->get_by_moodleid_id() )
{
    print_error($DOF->get_string('no_access','reports',$report->name));
}
// ссылки на подтверждение и непотдверждение сохранения приказа
$linkyes = $DOF->url_im('reports', '/delete.php?id='.$id.'&delete=1', $addvars);
$linkno = $DOF->url_im('reports', '/index.php',$addvars);

if ( $delete )
{
    // Делаем физическое удаление записи
    $DOF->storage('reports')->delete_report($report);
    redirect($linkno);
}else
{
   
    $DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
    // вывод названия удаляемого элемента
    echo '<div align="center" style="color:red;font-size:25px;">' . $report->name.'</div><br>';
    
    // спросим об удалении
    $DOF->modlib('widgets')->notice_yesno($DOF->get_string('delete_yes','reports'), $linkyes, $linkno);
    
    //печать подвала
    $DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
}

?>