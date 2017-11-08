<?php 

// Подключаем библиотеки
require_once('lib.php');

// принятые данные
$id = required_param('id', PARAM_INT);
$delete = optional_param('delete', 0, PARAM_BOOL);

// проверки
// не найден отчет
if ( ! $order  = $DOF->storage('orders')->get($id) )
{// вывод сообщения и ничего не делаем
    print_error($DOF->get_string('notfound_report','orders', $id));
}
// проверка прав
if ( ! $DOF->workflow('orders')->is_access('changestatus',$id) AND ! is_null($order->exdate)
       AND $order->ownerid != $DOF->storage('persons')->get_by_moodleid_id() )
{
    print_error($DOF->get_string('no_access','journal',$order->id));
}
// ссылки на подтверждение и непотдверждение сохранения приказа
$linkyes = $DOF->url_im('journal', '/orders/fix_day/delete.php?id='.$id.'&delete=1', $addvars);
$linkno = $DOF->url_im('journal', '/orders/fix_day/list.php',$addvars);

if ( $delete )
{
    // Делаем физическое удаление записи
    $DOF->workflow('orders')->change($order->id,'canceled');
    redirect($linkno);
}else
{
   
    $DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
    // вывод названия удаляемого элемента
    echo '<div align="center" style="color:red;font-size:25px;">' .$order->id.'</div><br>';
    
    // спросим об удалении
    $DOF->modlib('widgets')->notice_yesno($DOF->get_string('delete_yes','reports'), $linkyes, $linkno);
    
    //печать подвала
    $DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
}

?>