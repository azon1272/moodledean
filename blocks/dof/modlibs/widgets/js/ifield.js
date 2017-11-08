function dof_modlib_widgets_ifield_set_editable(divid, fieldtype, textid, savepath, loadpath)
{
    $('#'+divid).editable(savepath, {
        type     : fieldtype,
        loadurl  : loadpath,
        submit   : 'OK',
        cancel   : 'Отмена',
        name     : 'data',
        callback : function (value, settings) {
            // @todo сделать здась обработку ошибок, если они возникли в процессе запроса
            
            }
        });
}