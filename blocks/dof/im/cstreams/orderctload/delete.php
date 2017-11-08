<?php 

// Подключаем библиотеки
require_once(dirname(realpath(__FILE__)).'/lib.php');

// Принятые данные
$id = required_param('id', PARAM_INT);
$delete = optional_param('delete', 0, PARAM_BOOL);

// проверки
// Не найден приказ
if ( ! $order = $DOF->storage('orders')->get($id) OR
       $order->code != 'change_teacher' )
{// Вывод сообщения и ничего не делаем
    $errorlink = $DOF->url_im('cstreams','/orderctload/list.php',$addvars);
    $DOF->print_error('order_notfound', $errorlink, $id, 'im', 'cstreams');
}
// Проверка прав
if ( ! $DOF->workflow('orders')->is_access('changestatus',$id) OR
       $order->ownerid != $DOF->storage('persons')->get_by_moodleid_id() )
{
    $errorlink = $DOF->url_im('cstreams','/orderctload/list.php',$addvars);
    $DOF->print_error('no_access', $errorlink, $order->id, 'im', 'cstreams');
}
if ( ! is_null($order->signdate) )
{
    $errorlink = $DOF->url_im('cstreams','/orderctload/list.php',$addvars);
    $DOF->print_error('order_wrote', $errorlink, $order->id, 'im', 'cstreams');
}
// Ссылки на подтверждение и непотдверждение сохранения приказа
$linkyes = $DOF->url_im('cstreams', '/orderctload/delete.php?id='.$id.'&delete=1', $addvars);
$linkno = $DOF->url_im('cstreams', '/orderctload/list.php',$addvars);

if ( $delete )
{
    // Делаем удаление записи (меняем только статус)
    $DOF->workflow('orders')->change($order->id,'canceled');
    redirect($linkno);
}else
{
   
    // Добавление уровней навигации
    $DOF->modlib('nvg')->add_level($DOF->get_string('order_change_teacher', 'university'), $DOF->url_im('cstreams','/orderctload/index.php'),$addvars);
    $DOF->modlib('nvg')->add_level($DOF->get_string('list_orders', 'cstreams'), $DOF->url_im('cstreams','/orderctload/list.php',$addvars));
    $DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
    // Вывод названия удаляемого элемента
    echo '<div align="center" style="color:red;font-size:25px;">' .$order->id.'</div><br>';
    
    // Спросим об удалении
    $DOF->modlib('widgets')->notice_yesno($DOF->get_string('delete_yes','cstreams'), $linkyes, $linkno);
    
    // Печать подвала
    $DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
}

?>