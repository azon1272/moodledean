/** Изменить значения для select-элемента на полученные из ajax
 * @param array data - данные, пришедшие из json
 * @param string selectid - id select-элемента (с решеточной спереди) в котором заменяется содержимое
 * 
 * @return null
 */
function dof_ajaxselect_update_content(data, selectid, value)
{
    // Удаляем старые значения
    $(selectid).empty();
    
    // устанавливаем новые варианты в select
    for (var key in data)
    {// перебираем все что пришло из AJAX-запроса
        var val = data[key];
        if(key == value)
        {
        	$(selectid).append("<option value='"+key+"' selected='selected'>"+val+"</option>");
        }else
        {
        	$(selectid).append("<option value='"+key+"'>"+val+"</option>");
        }
        
    }
    $(selectid).change();
}

/** Получить значения для дочернего select-элемента в зависимости от значения родительского элемента
 * Вызывается когда кто-то изменяет значение в поле, от которого зависит select
 * 
 * @param string parentselectid - id элемента, на значение которого мы ореинтируемся
 * @param string childselectid - id элемента, который будет реагировать на изменения 
 * @param string url - адрес, куда отсылается ajax-запрос. В url должны быть заранее
 *                     установлены все обязательные параметры: тип запроса, тип плагина, код плагина
 * @param object customdata[optional] - дополнительные данные, которые будут переданы в функцию получения 
 *                            списка опций для select. Необязательные параметры
 * @todo предусмотреть случаи неудачного ajax-запроса
 * 
 * @return null
 */
function dof_ajaxselect_request_options(parentselectid, childselectid, url, customdata, value)
{
    // Таким образом в js задается необязательный параметр customdata
    if ( typeof(customdata) != "object" )
    {// Если дополнительные данные не переданы - то создадим для нах объект
        customdata = {};
    } 
    // получаем и запоминаем новое значение роодительского элемента
    customdata.parentvalue = $(parentselectid).val();
    // Добавляем дополнительные данные в запрос на получение select-списка (они посылаются в POST)
    // и устанавливаем тип запрооса (если мы вдруг забыли сделать это в PHP)
    args = {};
    args.data     = customdata;
    args.objectid = $(parentselectid).val();
    args.type     = "ajaxselect";
    
    // отправляем запрос на получение нового списка вариантов
    $.ajax(url,{
        // устанавливаем аргименты в post-запрос
        data: args,
        // Ожидаем от сервера json-строку
        dataType: 'json',
        // Изменяем содержимое select-элемента если запрос удался
        success: function (data) {
                dof_ajaxselect_update_content(data, childselectid, value);
            }
        }
    );
}

/** Превратить обычный select в элемент с ajax-подгрузкой вариантов
 * @param string parentselectid - id элемента, на значение которого мы ореинтируемся
 * @param string childselectid - id элемента, который будет реагировать на изменения 
 * @param string url - адрес, куда отсылается ajax-запрос. В url должны быть заранее
 *                     установлены все обязательные параметры: тип запроса, тип плагина, код плагина
 * @param object customdata[optional] - дополнительные данные, которые будут переданы в функцию получения 
 *                            списка опций для select. Необязательные параметры 
 * 
 */
function dof_ajaxselect_init(parentselectid, childselectid, url, customdata, value)
{
    // навешиваем обработчик события на родительский элемент
    // Каждый раз, когда в нем будет меняться значение мы будем получать новый набор данных
    $(parentselectid).change(function () {
        dof_ajaxselect_request_options(parentselectid, childselectid, url, customdata, value);
    });
    // При первой загрузке элемента сделаем ajax-запрос, на случай если в родительском
    // элементе уже установлено значение по умолчанию
    dof_ajaxselect_request_options(parentselectid, childselectid, url, customdata, value);
}