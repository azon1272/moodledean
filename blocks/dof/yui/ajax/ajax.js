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
// Copyright (C) 2008-2999  Evgenij Cigancov (Евгений Цыганцов)           //
// Copyright (C) 2008-2999  Ilia Smirnov (Илья Смирнов)                   // 
// Copyright (C) 2008-2999  Mariya Rojayskaya (Мария Рожайская)           // 
// Copyright (C) 2008-2999  Nikolay Konovalov (Николай Коновалов)         // 
//                                                                        //
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
YUI.add('moodle-block_dof-ajax', function(Y) {
    var AJAXNAME = 'block_dof_ajax';
    /**
     * Объект с конфигурацией для оверлея [индикатор выполнения AJAX-запроса]
     * @type object
     */
    var CSSOVERLAY = {
	background: 'url(' + M.cfg.wwwroot + '/blocks/dof/yui/ajax/spinner.gif' + ') no-repeat scroll center / contain #fff ',
    },
    
    PREFIXES = {
        transferid: '#aid_transfer_',
    },
    
    /**
     * Конфигурация для анимации оверлея ожидания запроса
     * @type object
     */
    ANIMWAITOVERLAY = {
        duration: 0.2,
        from: { opacity: 0 },
        to: { opacity: 0.6 }
    },
    
    PARAMS = {
        /**
         * Номер запроса для организации последовательности обновлений интерфейса
         * и описания частей для обновления (таблица, несколько таблиц)
         */
        requestid : 1,
        
        /**
         * Список выполняемых в данный момент запросов:
         * requesting[0] = {pitems: [], tablerows: [], 
         */
        requesting : [],
        
        /**
         * Параметры для выполнения запросов
         * 
         * ->sesskey
         * ->formname
         * В массивах и строках передаются id элементов формы:
         * ->addtoplan = array
         * ->excludefromplan = array
         * ->tranfertoagenum = array
         * ->addtoagenum = array
         * ->planrequired = array
         * ->planrequiredall = string
         * ->autosubscribeid = string
         */
        submitparams : {},
        
        learnplanparams : {},
        /**
         * Параметры для подсчёта часов, определения оригинальной параллели, ...
         * pitemsparams[1] = {hoursweek: 123, ...}
         */
        pitemsparams : [],
        
        /**
         * Параметры для определения параллелей, которые находятся в одном
         *  учебном году
         */
        agenumsmap: []
    };
    
    /**
     * Объект Y.IO для создания и получения запросов
     * @type Y.IO
     */
    var io = {};
    
    var AJAX = function() {
        AJAX.superclass.constructor.apply(this, arguments);
    };
    
    AJAX.prototype = {
                
        /**
         * Инициализация AJAX-скрипта:
         * 1. Инициализация пришедших из PHP параметров
         * 2. Добавляет обработчики событий на элементы формы
         * 
         * @param {array} params - параметры, пришедшие из ajax.php
         * @return void
         */
        initializer: function(params) {
            PARAMS.submitparams     = params.submitparams;
            PARAMS.learnplanparams  = params.learnplanparams;
            PARAMS.pitemsparams     = params.pitemsparams;
            // Объект для обращения к функциям внутри AJAX.prototype
            var self                = this;
            io                      = new Y.IO();
            // Удалим ненужные элементы
            Y.all('.pitemhide').remove(true);
            // Удалим пустые элементы из таблиц
            Y.all('[id*=pitems_].havenoelements tbody tr').remove(true);
            Y.all('[id*=hours_agenums_]').each(function() {
                var agenums = this.get('id').split('hours_agenums_')[1].split('_');
                for (var agenum in agenums) {
                    PARAMS.agenumsmap[agenums[agenum]] = agenums;
                }
            });
            if (!self.get('submitparams')) {
                // Элемент не найден!
                console.log('No submitparams found!');
                Y.log("No submitparams found!. Aborting", "error", "moodle-block_dof-ajax");
                return;
            }
            this.attach_events(self.get('submitparams'));
            Y.on('pitemchanged', function (e) {
                self.attach_events_pitemid(e.pitemid);
                // Удаляем оверлей, когда дисциплина обновилась
                self.detach_overlay(e.pitemid);
                // Обновим параллели
                var currentagenum = self.get_current_agenum(e.pitemid);
                self.refresh_agenum(currentagenum);
                self.sort_pitems(e.requestid, e.pitemid);
            });
            
            // Когда кликаем по параллели (переписать с делегацией событий)
//            var agenum = 0;
//            Y.one('#pitems_' + agenum).on('click', function (e) {
//                var icontypes = ['addtoplan', 'excludefromplan', 'changeagenum'];
//                for (var icontype in icontypes) {
//                    if (e.target.hasClass('icon.' + icontypes[icontype])) {
//                        
//                        // Отправляем запрос
////                        self.sumbit_element(e, icontypes[icontype], elementid, transferid);
//                    }
//                }
//            });
            // Обновляем часы и таблицы/элементы в параллелях по событию
            Y.on('agenumchanged', function (e) {
                if (Y.Lang.isUndefined(e) ||
                    Y.Lang.isUndefined(e.prevagenum) ||
                    Y.Lang.isUndefined(e.operation) ||
                    Y.Lang.isUndefined(e.agenum)) {
                    return;
                }
                // Проверяем количество дисциплин,
                //  затем скрываем или показываем таблицы в параллелях
                self.refresh_agenum(e.prevagenum);
                // Пересчитываем часы
                self.refresh_hours_agenums(e.operation, e.prevagenum);
                if (e.prevagenum !== e.agenum) {
                    self.refresh_agenum(e.agenum);
                    self.refresh_hours_agenums(e.operation, e.agenum);
                }
            });

            Y.Object.each(PARAMS.pitemsparams, function (pitem, pitemid) {
                var pitem = Y.one('#pitem_' + pitemid);
                if (pitem !== null) {
                    if (pitem.hasClass('planning') || pitem.hasClass('planned')) {
                        self.attach_events_pitemid(pitemid);
                    }
                    Y.one('#pitem_' + pitemid).setData('pitemid', pitemid);
                }
            });
            // Обрабатываем изменения, связанные с оверлеями.
            Y.on(['agenumchanged','windowresize'], function(e) {
                // Находим все дисциплины с оверлеями
                Y.all('.overlay').each(function(overlay) {
                    var id = overlay.getData('pitemid');
                    var r = Y.one('#pitem_' + id).get('region');
                    // Достать все строки, в которых они находятся
                    overlay.setXY([r.left, r.top]);
                    overlay.setStyle('width', r.width + 'px');
                    overlay.setStyle('height', r.height + 'px');
                });
            });
            
            this.assign_dragdrop();
        },
        
        /**
         * Перерисовать форму
         * 
         * @param {string} responsetext - json-строка ответа от ajax-скрипта
         * @return void
         */
        display_form: function(responsetext) {
            try {
               var response = Y.JSON.parse(responsetext);
            } catch (e) {
                console.log("JSON Parse failed!");
                return;
            }
            // Объект для обращения к функциям внутри AJAX.prototype
            var self                = this;
            PARAMS.submitparams = response.submitparams;
            // Создаём форму из HTML-кода
            var rform = Y.Node.create(response.html);
            // Отобразим форму на странице прежде, чем скрипты,
            // поскольку последние требуют, чтобы элементы уже были созданы
            Y.one('#' + self.get('container')).setContent(rform)

            // Добавляем тэг <script> на страницу и содержимое скриптов внутрь него
            var scriptel = document.createElement('script');
            scriptel.textContent = response.script;
            scriptel.type = "text/javascript";
            document.body.appendChild(scriptel);
            // События добавим заново
            self.attach_events(response.submitparams);
            Y.all('.pitemhide').remove(true);
        },
        
        /**
         * Добавляет обработчики событий элементам формы elements
         * 
         * @param {array} elements
         * @return void
         */
        attach_events: function(elements) {
            // Автоматическая подписка
            // Объект для обращения к функциям внутри AJAX.prototype
            var self = this;
            if ( elements.autosubscribeid ) {
                self.attach_event('autosubscribeid', elements.autosubscribeid);
            }
            // Запланировать обязательные дисциплины для указанной параллели
            for(var i = 0; i <= PARAMS.learnplanparams.agenums; i++) {
                var prequiredid = 'aid_planrequired_' + i;
                self.attach_event('planrequired', prequiredid, i);
            }
            // Запланировать все обязательные
            if ( elements.planrequiredall ) {
                self.attach_event('planrequiredall', elements.planrequiredall);
            }
        },
        
        /**
         * Добавить обработчики событий к дисциплине
         * 
         * @param {integer} pitemid
         * @returns {void}
         */
        attach_events_pitemid: function (pitemid) {
            // Объект для обращения к функциям внутри AJAX.prototype
            var self = this;
            // Получим список элементов, к которым нужно присоединить обработчики
            var pitemrow = Y.one('#pitem_' + pitemid),
                iclass   = '';
            if (pitemrow.hasClass('planning')) {
                iclass  = 'addtoplan';
            } else if (pitemrow.hasClass('planned')) {
                iclass  = 'excludefromplan';
            }
            Y.one('#pitem_' + pitemid + ' input.' + iclass).detach()
            // Кнопка добавления дисциплины в план
            var inputid = Y.one('#pitem_' + pitemid + ' input.' + iclass).get('id');
            // Если были обработчики, отвяжем их и привяжем новые:
            self.detach_event(inputid);
            self.attach_event(iclass, inputid);
        },
        
        /**
         * Отсоединить обработчик события от элемента формы
         * 
         * @param {string} elementid - id элемента
         * @return void
         */
        detach_event: function(elementid) {
            Y.one('#' + elementid).detach();
        },
        
        /**
         * Добавить обработчик события к элементу формы
         * 
         * @param {string} element
         * @param {string} itemid - id элемента
         * @param {string} index - дополнительный id элемента (например, transferid)
         * @return void
         */
        attach_event: function(element, itemid, index) {
            var self = this;
            var event = 'click';
            switch (element) {
                case 'addtoplan':
                    Y.one('#' + itemid).once(event, self.submit_element, self, 'addtoplan', itemid);
                    break;
                case 'excludefromplan':
                    Y.one('#' + itemid).once(event, self.submit_element, self, 'excludefromplan', itemid);
                    break;
//                case 'addtoagenum':
//                    Y.one('#' + index).once(event, self.submit_element, self, 'addtoagenum', index, itemid);
//                    break;
//                case 'transfertoagenum':
//                    Y.one('#' + index).once(event, self.submit_element, self, 'transfertoagenum', index, itemid);
//                    break;
                case 'autosubscribeid':
                    Y.one('#' + itemid).on(event, self.submit_element, self, 'autosubscribeid', itemid);
                    break;
                case 'planrequired':
                    Y.one('#' + itemid).on(event, self.submit_element, self, 'planrequired', itemid);
                    break;
                case 'planrequiredall':
                    Y.one('#' + itemid).on(event, self.submit_element, self, 'planrequiredall', itemid);
                    break;
            }
        },
        
        /**
         * Получить запрос для элемента addtoplan
         * 
         * @param {string} element - элемент формы для добавления дисциплины в план
         * @return object - часть запроса для передачи AJAX-скрипту
         */
        get_request_addtoplan: function(element) {
            // Достанем id из имени
            var addtoplan = Y.one('#' + element);
            var name = addtoplan.get('name');
            // Параллель
            var agenum = addtoplan.getAttribute('value');
            var ret = {};
            ret[name] = agenum;
            return ret;
        },
        
        /**
         * Получить запрос для элемента excludefromplan
         * 
         * @param {string} element - элемент формы для исключения дисциплины из плана
         * @return object - часть запроса для передачи AJAX-скрипту
         */
        get_request_excludefromplan: function(element) {
            // Достанем id из имени
            var excludefromplan = Y.one('#' + element);
            var name = excludefromplan.get('name');
            // Параллель
            var agenum = excludefromplan.getAttribute('value');
            var ret = {};
            ret[name] = agenum;
            return ret;
        },
        
        /**
         * Получить запрос для элемента addtoagenum
         * 
         * @param {string} element - элемент формы для добавления дисциплины в другую параллель
         * @param {string} transferid - элемент формы (select) с указанной параллелью
         * @return object - часть запроса для передачи AJAX-скрипту
         */
        get_request_addtoagenum: function(element, transferid) {
            // Достанем id из имени
            var addtoagenum = Y.one('#' + element);
            var name = addtoagenum.get('name');
            
            // Достанем указанный номер параллели
            var transfer = Y.one('#' + transferid);
            var tname    = transfer.get('name');
            var tselected = transfer.get('selectedIndex');
            var agenum = transfer.get(tselected).get('value');
            var ret = {};
            ret[name]  = agenum;
            ret[tname] = agenum;
            return ret;
        },
        
        /**
         * Получить запрос для элемента transfertoagenum
         * 
         * @param {string} element - элемент формы для переноса дисциплины в другую параллель
         * @param {string} transferid - элемент формы (select) с указанной параллелью
         * @return object - часть запроса для передачи AJAX-скрипту
         */
        get_request_transfertoagenum: function(element, transferid) {
            // Достанем id из имени
            var transfertoagenum = Y.one('#' + element);
            var name = transfertoagenum.get('name');
            
            // Достанем указанный номер параллели
            var transfer = Y.one('#' + transferid);
            var tname    = transfer.get('name');
            var tselected = transfer.get('selectedIndex');
            var agenum = transfer.get(tselected).get('value');
            var ret = {};
            ret[name]  = agenum;
            ret[tname] = agenum;
            return ret;
        },
        
        /**
         * Получить запрос для элемента autosubscribe
         * 
         * @param {string} element - элемент формы для добавления дисциплины в другую параллель
         * @return object - часть запроса для передачи AJAX-скрипту
         */
        get_request_autosubscribe: function(element) {
            // Достанем id из имени
            var autosubscribe = Y.one('#' + element);
            var name = autosubscribe.get('name');
            var ret = {};
            ret[name]  = 'plan';
            return ret;
        },
        
        /**
         * Получить запрос для элемента addtoagenum
         * 
         * @param {string} element - элемент формы для добавления дисциплины в другую параллель
         * @return object - часть запроса для передачи AJAX-скрипту
         */
        get_request_planrequired: function(element) {
            // Достанем имя элемента по его id
            var planrequired = Y.one('#' + element);
            var name = planrequired.get('name');
            var ret = {};
            ret[name]  = 'plan';
            return ret;
        },
        
        /**
         * Получить запрос для элемента addtoagenum
         * 
         * @param {string} element - элемент формы для добавления дисциплины в другую параллель
         * @return object - часть запроса для передачи AJAX-скрипту
         */
        get_request_planrequiredall: function(element) {
            var planrequiredall = Y.one('#' + element);
            var name = planrequiredall.get('name');
            var ret = {};
            ret[name]  = 'planall';
            return ret;
        },

        /**
         * Получить запрос для элемента changeagenumajax
         *
         * @param {string} pitemid - номер дисциплины
         * @param {string} agenum - номер параллели
         * @return object - часть запроса для передачи AJAX-скрипту
         */
        get_request_movetoagenum: function(pitemid, agenum) {
            // Определим, какой запрос необходимо сгенерировать
            var pitem = Y.one('#pitem_' + pitemid);
            var ret = {};
            if (pitem.hasClass('planned')) {
                ret['transfertoagenum[' + pitemid + ']']  = agenum;
            } else {
                ret['addtoagenum[' + pitemid + ']']  = agenum;
            }
            ret['transfer[' + pitemid + ']'] = agenum;
            return ret;
        },

        /**
         * Получить скрытые поля для валидации отправленной формы
         */
        get_hidden_fields: function() {
            var hidden = {};
            hidden["sesskey"] = PARAMS.submitparams.sesskey;
            hidden["_qf__" + PARAMS.submitparams.formname] = 1;
            return hidden;
        },
        
        /**
         * Получить id дисциплин, привязанных к указанному элементу
         * 
         * @param {string} type
         * @param {string} elementid
         * @returns {array|string|false}
         */
        get_pitems_from_elementid: function(type, elementid) {
            var pitemid = elementid;
            if (Y.one('#pitem_' + elementid) !== null) {
                pitemid = 'pitem_' + elementid;
            } else if (Y.one('#' + elementid) === null) {
                return false;
            }
            var element  = Y.one('#' + pitemid),
                name     = element.get('name'),
                id       = false,
                pitemids = [];
            switch (type) {
                case 'addtoplan':
                case 'excludefromplan':
                case 'addtoagenum':
                case 'transfertoagenum':
                    pitemids.push(name.substring(name.lastIndexOf("[")+1,name.lastIndexOf("]")));
                    break;
                case 'autosubscribeid':
                case 'planrequired':
                    // Определим, из какой параллели нужно достать обязательные дисциплины
                    var ancestor  = 'fieldset',
                        tablerow  = Y.one('#' + pitemid).ancestor(ancestor),
                        id        = tablerow.get('id');
                    Y.all('#' + id + ' tr.required.planning').each(function(pitem) {
                        id = pitem.getData('pitemid');
                        // Достать все строки, в которых они находятся
                        pitemids.push(id);
                    });
                    break;

                case 'planrequiredall':
                    // Достать все элементы, которые относятся к обязательным дисциплинам
                    Y.all('tr.required.planning').each(function(pitem) {
                        id = pitem.getData('pitemid');
                        pitemids.push(id);
                    });
                    break;
                case 'movetoagenum':
                    pitemids.push(elementid);
                    break;
                default:
                    console.log('Error type:' + type);
                    break;
            }
            return pitemids;
        },
        
        /**
         * Отправить запрос по клику на элемент формы:
         * 0. Предотвращает обычную отправку формы
         * 1. Составляет самостоятельно POST-запрос для передачи скрипту 
         * (из-за бага YUI с множественными submit-элементами в форме: http://yuilibrary.com/yui/docs/io/#known-issues)
         * 2. Перемещает дисциплину в нужную параллель
         * 3. Затеняет перемещаемый элемент
         * 4. Инициирует пересчёт часов в параллели и учебном году
         * 5. В случае неудачного запроса возвращает элемент на место и выполняет п.4
         * 
         * @param {EventFacade} e
         * @param {string} operation - по какому нажатому отправляем запрос [операция]
         * @param {string} elementid - id этого элемента
         * @param {string} transferid - дополнительный id 
         * @return void
         */
        submit_element: function(e, operation, elementid, transferid) {
            var self = this;
            // Предотвращаем обычную отправку данных с формы
            e.preventDefault();
            var postrequest = {};
            switch (operation) {
                case 'addtoplan':
                    postrequest = this.get_request_addtoplan(elementid);
                    break;
                case 'excludefromplan':
                    postrequest = this.get_request_excludefromplan(elementid);
                    break;
                case 'addtoagenum':
                    postrequest = this.get_request_addtoagenum(elementid, transferid);
                    break;
                case 'transfertoagenum':
                    postrequest = this.get_request_transfertoagenum(elementid, transferid);
                    break;
                case 'movetoagenum':
                    // elementid = pitemid, transferid = agenum
                    postrequest = this.get_request_movetoagenum(elementid, transferid);
                    break;
                case 'autosubscribeid':
                    postrequest = this.get_request_autosubscribe(elementid);
                    break;
                case 'planrequired':
                    postrequest = this.get_request_planrequired(elementid);
                    break;
                case 'planrequiredall':
                    postrequest = this.get_request_planrequiredall(elementid);
                    break;
            }
            postrequest = self.merge_objects(postrequest, self.get_hidden_fields());
           
            // Записываем запрос с дисциплинами в массив requesting
            // Все дисциплины, относящиеся к нажатому элементу:
            var pitemids = self.get_pitems_from_elementid(operation, elementid);
            // Проверим, какие из них уже имеют класс 'requesting':
            Y.Array.each(pitemids, function (pitemid, index) {
                if (Y.one('#pitem_' + pitemid).hasClass('requesting')) {
                    // Удаляем те дисциплины, которые обновятся в предыдущих запросах
                    delete pitemids[index];
                }
            });
            if (pitemids.length > 0) {
                if (!PARAMS.requesting[PARAMS.requestid]) {
                    PARAMS.requesting[PARAMS.requestid] = {};
                }
                PARAMS.requesting[PARAMS.requestid].pitems = pitemids;
            } else {
                console.log('pitemids empty! nothing to do.');
                return;
            }
            // Назначаем классы 'requesting' и перемещаем элементы в нужные параллели
            self.move_pitems_to_agenums(operation, PARAMS.requestid, transferid);
            
            // Оверлей и его анимация для затенения части перестраиваемых элементов формы
//            var anim = new Y.Anim(ANIM);
//            var overlay = self.attach_overlay(element, elementid, anim);
//            var backgroundcolor = overlay.getStyle('backgroundColor');
//            var color = overlay.getStyle('color');
            // Анимация
            // Отправляем запрос
//            PARAMS.requestid++;
//            return;
            var urlparams = '?onlystatus=1&type='+self.get('type')+'&'+self.get('type')+'id='+self.get('typeid');
            io.send(M.cfg.wwwroot + self.get('ajaxurl') + urlparams, {
                method: 'post',
                on: {
                    /**
                     * 
                     * @param {integer} id - id запроса
                     * @param {XMLHttpRequest} o - ответ от сервера
                     * @returns {void}
                     */
                    success: function(id, o) {
                        try {
                            var parsedResponse = Y.JSON.parse(o.responseText);
                        } catch (e) {
                            
                        }
                        PARAMS.requesting[id].sorted = parsedResponse.sorted;
                        Y.Object.each(parsedResponse.agenums, function(agenums, pitemid) {
                            PARAMS.pitemsparams[pitemid].agenums = agenums;
                        });
                        self.update_pitems('success', id, operation);
//                        self.sort_pitems(id);
                        // По окончанию запроса проверяем статус и снимаем оверлей (затенение)
//                        self.detach_overlay(null, elementid);
//                        var anim2 = new Y.Anim({
//                            duration: 0.7,
//                            to: {
//                                backgroundColor:backgroundcolor,
//                                color: color,
//                                opacity: 0,
//                            },
//                            from: {
//                                color: '#fff',
//                                backgroundColor:'#03a',
//                                opacity: 0.4,
//                            },
//                        });
//                        var overlay = self.attach_overlay(element, elementid, anim2);
//                        anim2.on('end', self.detach_overlay, self, elementid);
//                        self.detach_overlay(elementid);
//                        self.display_form(o.responseText);
                    },
                    /**
                     * При ошибке откатывает изменения назад
                     * 
                     * @param {integer} id - id запроса
                     * @param {XMLHttpRequest} result - ответ от сервера [если есть]
                     * @returns {void}
                     */
                    failure: function (id, result) {
                        // Если произошла ошибка, откатываем назад и пересчитываем часы
                        // Но, что делать, если другой запрос выполнился?
                        // ..Попробуем обновить всю форму
                        //self.display_form(o.responseText);
//                        try {
//                            var parsedResponse = Y.JSON.parse(result.response);
//                        } catch (e) {
//                        }
                    }
                },
//                form: form,
                data: postrequest,
                context: this
            }, PARAMS.requestid);
            // Инкрементируем счётчик запросов, т.к. по ним определяем,
            //  какие изменения выполнять в форме
            PARAMS.requestid++;

        },
        
        merge_objects: function(obj1, obj2) {
            var result = {};
            for (var attrname in obj1) {
                result[attrname] = obj1[attrname];
            }

            for (var attrname in obj2) {
                result[attrname] = obj2[attrname];
            }
            return result;
        },

        /**
         * Получить строки таблицы (дисциплины), связанные с элементом
         * 
         * @param {string} element - по какому нажатому элементу получаем строку 
         * @param {string} elementid - id этого элемента
         * @returns {object|array} - строка или несколько строк в массиве
         */
        get_pitem_table_rows: function(element, elementid) {
            var ancestor = 'field';
            var tablerow = null;
            switch (element) {
                case 'addtoplan':
                case 'excludefromplan':
                case 'addtoagenum':
                case 'transfertoagenum':
                    ancestor  = 'tr';
                    tablerow = Y.one('#' + elementid).ancestor(ancestor);
                    break;
                case 'autosubscribeid':
                case 'planrequired':
                    // Определим, из какой параллели нужно достать обязательные дисциплины
                    ancestor = 'fieldset';
                    tablerow = Y.one('#' + elementid).ancestor(ancestor);
                    var id = tablerow.get('id');
                    var tablerows = [];
                    Y.all('#' + id + ' .requiredpitem').each(function(node, index, nodelist) {
                        // Достать все строки, в которых они находятся
                        tablerows.push(node.ancestor('tr'));
                    });
                    return tablerows;
                    
                case 'planrequiredall':
                    // Достать все элементы, которые относятся к обязательным дисциплинам
                    var tablerows = [];
                    Y.all('.requiredpitem').each(function(node, index, nodelist) {
                        // Достать все строки, в которых они находятся
                        tablerows.push(node.ancestor('tr'));
                    });
                    return tablerows;
                default:
                    tablerow = Y.one('#' + elementid).ancestor(ancestor)
                    break;
            }
            return tablerow;
        },
        
        /**
         * Переместить строки таблиц в нужные параллели в зависимости от нажатого элемента
         * 1. Присваивает класс 'requesting' к строке с дисциплиной и id с requestid, pitemid
         * 2. Перемещает строку в нужную параллель в конец таблицы
         * 3. Прикрепляет оверлей для затенения элемента
         * 
         * @param {string} operation
         * @param {string} requestid
         * @returns {undefined}
         */
        move_pitems_to_agenums: function(operation, requestid, agenum) {
            var self = this;
            var pitems = PARAMS.requesting[requestid].pitems;
            if (pitems.length > 0) {
                if (Y.Lang.isArray(pitems)) {
                    Y.Array.each(pitems, function (pitemid, index) {
                        // Если запроса на добавление ещё нет
                        var row = Y.one('#pitem_' + pitemid);
                        if (!row.hasClass('requesting')) {
                            // Для упрощения логики upate_pitems()
                            row.addClass('requesting');
                            // Перемещаем элемент в нужную параллель, если нужно
                            self.move_row(operation, pitemid, agenum);
                            // Прикрепляем оверлей с ожиданием запроса
                            self.attach_overlay(pitemid, new Y.Anim(ANIMWAITOVERLAY));
                        }
                    });
                }
            }
        },
        
        /**
         * Выполняет перемещение строки с дисциплиной в нужную параллель и запускает
         * событие 'agenumchanged', чтобы все оверлеи изменили свою позицию
         * 
         * @param {string} operation - тип операции с элементом:
         *                           - addtoplan
         *                           - excludefromplan
         *                           - addtoagenum
         *                           - transfertoagenum
         *                           - autosubscribeid
         *                           - planrequired
         *                           - planrequiredall
         * @param {integer} pitemid - номер дисциплины, которую необходимо переместить
         * @returns {void}
         */
        move_row: function(operation, pitemid, toagenum) {
            var self = this;
            // Определим, в какую параллель нужно добавить дисциплину:
            var agenum, table,
                // Теперь достанем элемент (строку) с дисциплиной
                pitemrow = Y.one('#pitem_' + pitemid),
                currentagenum = self.get_current_agenum(pitemid);
            switch (operation) {
                case 'planrequired':
                case 'planrequiredall':
                    // Оригинальная параллель (перемещаем дисциплину туда)
                    agenum = PARAMS.pitemsparams[pitemid].agenum;
                    if (agenum === self.get_current_agenum(pitemid)) {
                        // Не перемещаем дисциплину, если она уже
                        break;
                    }
                    break;
                case 'excludefromplan':
                    // Оригинальная параллель (перемещаем дисциплину туда)
                    agenum = PARAMS.pitemsparams[pitemid].agenum;
                    if (agenum === self.get_current_agenum(pitemid)) {
                        // Не перемещаем дисциплину, если она уже 
                        break;
                    }
                    // Таблица, в которую необходимо добавить дисциплину
                    table = Y.one('#id_ages' + agenum + ' table tbody');
                    table.appendChild(pitemrow);
                    break;
                case 'addtoagenum':
                case 'transfertoagenum':
                    // Получим таблицу, в которую необходимо добавить дисциплину
                    agenum = self.get_selected_agenum(pitemid);
                    table = Y.one('#id_ages' + agenum + ' table tbody');
                    table.appendChild(pitemrow);
                    break;
                case 'movetoagenum':
                    // Получим таблицу, в которую необходимо добавить дисциплину
                    agenum = toagenum;
                    table = Y.one('#id_ages' + agenum + ' table tbody');
                    table.appendChild(pitemrow);
                    break;
                case 'addtoplan':
                    agenum = currentagenum;
                case 'autosubscribeid':
                    break;
                default:
                    break;
            }
            // Отсылаем событие для обновления позиции оверлеев
            Y.fire('agenumchanged', {'agenum'     : agenum,
                                     'prevagenum' : currentagenum,
                                     'operation'  : operation,
                                     'type'       : 'agenumchanged'});
        },
        
        /**
         * Получить выбранный номер параллели для дисциплины (при нажатии на кнопку
         * перемещения/добавления в выбранную параллель)
         * 
         * @param {integer} pitemid - id перемещаемой дисциплины
         * @returns {mixed|integer|bool} - номер параллели, либо false, если
         *   нужный элемент с параллелью не найден
         */
        get_selected_agenum: function(pitemid) {
            // Достанем id из имени
            if (Y.one(PREFIXES.transferid + pitemid)) {
                var selectagenum = Y.one(PREFIXES.transferid + pitemid),
                    tselected = selectagenum.get('selectedIndex');
                return selectagenum.get(tselected).get('value');
            }
            return false;
        },
        
        /**
         * Прикрепить оверлей к указанной дисциплине с анимацией
         * 
         * @param {object} row - <tr> содержащий поля дисциплины
         * @param {string} pitemid - номер дисциплины
         * @param {Anim} anim - анимация для оверлея
         * @returns {Node}
         */
        attach_overlay: function (pitemid, anim) {
            // Проверим, а вдруг уже оверлей есть?
            if (Y.one('#pitem_' + pitemid + '_overlay')) {
                // Возвращаем его
                return Y.one('#pitem_' + pitemid + '_overlay');
            }
            var overlay = Y.Node.create('<div class="overlay"></div>'),
                row     = Y.one('#pitem_' + pitemid);
            overlay.setData('pitemid', pitemid);
            Y.Object.each(CSSOVERLAY, function(item, index) {
                overlay.setStyle(index, item);
            });
            if (Y.Lang.isObject(row)) {
                var r = row.get('region');
                row.get('parentNode').appendChild(overlay);
                overlay.setXY([r.left, r.top]);
                overlay.setStyle('width', r.width + 'px');
                overlay.setStyle('height', r.height + 'px');
                overlay.set('id', 'pitem_' + pitemid + '_overlay');
                anim.set('node', overlay);
                anim.run();
            }
            return overlay;
            // Анимация плавного перехода к отображению обработки запроса
        },

        /**
         * Снять оверлей с дисциплины pitemid
         * 
         * @param {string} pitemid - id дисциплины, над которой находится оверлей
         * @return void
         */
        detach_overlay: function (pitemid) {
            var overlayid = 'pitem_' + pitemid + '_overlay';
            if (Y.one('#' + overlayid)) {
                Y.one('#' + overlayid).remove(true);
            }
            return;
        },
        
        /**
         * Изменить тип элемента в зависимости от операции:
         *                               - addtoplan
         *                               - excludefromplan
         *                               - addtoagenum
         *                               - transfertoagenum
         *                               - autosubscribeid
         *                               - planrequired
         *                               - planrequiredall
         * Вызывается при успешном запросе 'success'
         * Генерирует событие 'pitemchanged', чтобы автоматически развесить на элементы
         * внутри дисциплины события 'click' для отправки запросов
         * 
         * 1. Снимаем класс 'requesting' с дисциплины
         * 2. Устанавливаем id, name, меняем class элементов внутри дисциплины
         * 
         * @param {string} pitemid - строка дисциплины, в которой необходимо применить изменения
         * @param {string} operation - операция, которая применяется к дисциплине
         * @return {void}
         */
        change_element_type: function (pitemid, operation, status, requestid) {
            // Получим строку с дисциплиной и установим ей id
            var pitemrow = Y.one('#pitem_' + pitemid);
            pitemrow.removeClass('requesting');
            switch (operation) {
                case 'addtoplan':
                    // Кнопка добавления дисциплины в план
                    var input = Y.one('#pitem_' + pitemid + ' input.addtoplan');
                    input.removeClass('addtoplan');
                    input.addClass('excludefromplan');
                    input.set('id', 'aid_excludefromplan_' + pitemid);
                    input.set('name', 'excludefromplan[' + pitemid + ']');
                    // Кнопка добавления дисциплины в другую параллель
//                    var input = Y.one('#pitem_' + pitemid + ' input.changeagenumajax');
//                    input.set('id', 'aid_transfertoagenum_' + pitemid);
//                    input.set('name', 'transfertoagenum[' + pitemid + ']');
                    pitemrow.removeClass('planning');
                    pitemrow.addClass('planned');
                    break;
                case 'excludefromplan':
                    // Кнопка удаления дисциплины из плана
                    var input = Y.one('#pitem_' + pitemid + ' input.excludefromplan');
                    input.removeClass('excludefromplan');
                    input.addClass('addtoplan');
                    input.set('id', 'aid_addtoplan_' + pitemid);
                    input.set('name', 'addtoplan[' + pitemid + ']');
                    // Кнопка перемещения дисциплины в другую параллель
//                    var input = Y.one('#pitem_' + pitemid + ' input.changeagenumajax');
//                    input.set('id', 'aid_addtoagenum_' + pitemid);
//                    input.set('name', 'addtoagenum[' + pitemid + ']');
                    pitemrow.removeClass('planned');
                    pitemrow.addClass('planning');
                    break;
                case 'planrequired':
                case 'planrequiredall':
                case 'addtoagenum':
                    // Кнопка добавления дисциплины в план
                    var input = Y.one('#pitem_' + pitemid + ' input.addtoplan');
                    input.removeClass('addtoplan');
                    input.addClass('excludefromplan');
                    input.set('id', 'aid_excludefromplan_' + pitemid);
                    input.set('name', 'excludefromplan[' + pitemid + ']');
                    // Кнопка добавления дисциплины в другую параллель
//                    var input = Y.one('#pitem_' + pitemid + ' input.changeagenumajax');
//                    input.set('id', 'aid_transfertoagenum_' + pitemid);
//                    input.set('name', 'transfertoagenum[' + pitemid + ']');
                    pitemrow.removeClass('planning');
                    pitemrow.addClass('planned');
                    break;
                case 'transfertoagenum':
                    break;
                case 'movetoagenum':
                    if (pitemrow.hasClass('planning')) {
                        // Кнопка добавления дисциплины в план
                        var input = Y.one('#pitem_' + pitemid + ' input.addtoplan');
                        input.removeClass('addtoplan');
                        input.addClass('excludefromplan');
                        input.set('id', 'aid_excludefromplan_' + pitemid);
                        input.set('name', 'excludefromplan[' + pitemid + ']');
                        pitemrow.removeClass('planning');
                        pitemrow.addClass('planned');
                    }
                    break;
                case 'autosubscribeid':
                    break;
                default:
                    break;
            }   
            // Отсылаем событие для обновления позиции оверлеев
            Y.fire('pitemchanged', {'pitemid': pitemid, 'status': status,
                                    'requestid': requestid, 'type': 'pitemchanged'});
        },
        
        /**
         * Обновить дисциплины, связанные с запросом requestid
         * 
         * @param {string} status
         * @param {integer} requestid
         * @param {string} operation
         * @returns {void}
         */
        update_pitems: function(status, requestid, operation) {
            // В зависимости от статуса выполнения операции вернём дисциплины на места или
            // Добавим их в нужную параллель (изменим типы элементов)
            var self = this;
            // Получаем список дисциплин, которые нужно обновить
            if (status == 'success') {
                // Для полученной строки с дисциплиной:
                if (PARAMS.requesting[requestid].pitems.length > 0) {
                    Y.Array.each(PARAMS.requesting[requestid].pitems, function (pitemid, index) {
                        self.change_element_type(pitemid, operation, status, requestid);
                    });
                }
            } else if (status == 'failure') {
                
            }
        },
        
        /**
         * Отсортировать дисциплины согласно их расположению на сервере:
         * - Изученные дисциплины
         * - Запланированные дисциплины
         * - Незапланированные дисциплины
         * а так же сортировка по названию и оригинальным семестрам
         * 
         * @param {integer} agenum
         * @returns {void}
         */
        sort_pitems: function(requestid, pitemid) {
            var self = this;
            // Отсортированные на сервере
            var sorted = PARAMS.requesting[requestid].sorted,
                // Дисциплины, участвующие в запросе
                pitems = PARAMS.requesting[requestid].pitems;
            // Для каждой дисциплины из запроса
            if (pitemid > 0) {
                pitems = [pitemid];
            }
            Y.Array.each(pitems, function (pitemid, index) {
                // В какой параллели дисциплина находится (после перетаскивания)
                var currentagenum = self.get_current_agenum(pitemid);
                // Неотсортированный список дисциплин
                var pitemrows  = Y.all('#pitems_' + currentagenum + ' tbody tr');
                var currentpitems = [];
                pitemrows.each(function (item, index) {
                    currentpitems[index] = item.get('id').split('pitem_')[1]; 
                });
                var sortedagenum = sorted[currentagenum];
                // Сортируем вставками
                Y.Array.each(currentpitems, function (pitemid, index) {
                    if (currentpitems[index] != sortedagenum[index]) {
                        var childAnchor = Y.one('#pitem_' + currentpitems[index]);
                        childAnchor.insertBefore(Y.one('#pitem_' + sortedagenum[index]), childAnchor);
                        var removeindex = currentpitems.indexOf(sortedagenum[index]);
                        // Удалим вставленный элемент
                        currentpitems.splice(removeindex, 1);
                        // Добавим вставленный элемент на вставленное место
                        currentpitems.splice(index, 0, sortedagenum[index]);
                    }
                });
                // Обновим классы even и odd
                self.refresh_pitems_rows(currentagenum);
            });
            
        },
        
        /**
         * Добавить (отобразить) параллель:
         * 1. Таблицу с дисциплинами
         * 2. Таблицу с часами (если есть)
         * 3. Показать кнопку "Запланировать обязательные дисциплины для этой параллели" (если есть)
         * 4. Убрать надпись "Элементы отсутствуют"
         * Манипулирует классами "haveelements" и "havenoelements", которые скрывают элементы
         * 
         * @param {integer} agenum - номер параллели
         * @returns {Node}
         */
        show_agenum: function(agenum) {
            var self = this;
            var table = Y.one('#pitems_' + agenum).removeClass('havenoelements');
            // Часы в "Доступные для всех семестров" не подсчитываем
            if (agenum > 0) {
                Y.one('#hours_agenum_' + agenum).removeClass('havenoelements');
            }
            // Кнопка "Запланировать обязательные дисциплины для этой параллели"
            self.refresh_planrequired(agenum);
            // "Элементы отсутствуют"
            Y.one('#no_elements_' + agenum).addClass('haveelements');
            return table;
        },
        
        /**
         * Убрать (скрыть) параллель:
         * 1. Таблицу с дисциплинами
         * 2. Таблицу с часами (если есть)
         * 3. Убрать кнопку "Запланировать обязательные дисциплины для этой параллели" (если есть)
         * 4. Показать надпись "Элементы отсутствуют"
         * Манипулирует классами "haveelements" и "havenoelements", которые скрывают элементы
         * 
         * @param {integer} agenum - номер параллели
         * @returns {Node}
         */
        hide_agenum: function(agenum) {
            var self = this;
            var table = Y.one('#pitems_' + agenum).addClass('havenoelements');
            // Часы в "Доступные для всех семестров" не подсчитываем
            if (agenum > 0) {
                Y.one('#hours_agenum_' + agenum).addClass('havenoelements');
            }
            // Кнопка "Запланировать обязательные дисциплины для этой параллели"
            self.refresh_planrequired(agenum);
            // "Элементы отсутствуют"
            Y.one('#no_elements_' + agenum).removeClass('haveelements');
            return table;
        },
        
        /**
         * Обновить кнопки "Запланировать обязательные дисциплины ...".
         * Проверяет, есть ли в параллелях незапланированные обязательные дисциплины;
         *  при этом, если выполняются запросы, то с отображением кнопки ничего не
         *  происходит, пока они не выполнятся, иначе -- добавляются/убираются элементы
         *  с формы
         * 
         * @param {int} agenum - номер параллели
         * @returns {Boolean} - изменилось ли что-либо
         */
        refresh_planrequired: function(agenum) {
            var changed = false;
            // Есть ли сама кнопка (конкретная параллель)?
            if (Y.one('#aid_planrequired_' + agenum)) {
                // Сначала проверим, есть ли дисциплины с запросами (requesting) в параллели.
                if (!Y.Lang.isNull(Y.one('#pitems_' + agenum + ' .requesting'))) {
                    // Ничего не делаем.
                    return false;
                }
                // Если есть в параллели незапланированные обязательные дисциплины
                if (!Y.Lang.isNull(Y.one('#pitems_' + agenum + ' .required.planning'))) {
                    Y.one('#aid_planrequired_' + agenum).removeClass('havenoelements');
                    changed = true;
                } else {
                    Y.one('#aid_planrequired_' + agenum).addClass('havenoelements');
                    changed = true;
                }
            }
            // Есть ли сама кнопка (все дисциплины)?
            if (Y.one('#planrequiredall')) {
                // Сначала проверим, есть ли дисциплины с запросами (requesting) в параллели.
                if (!Y.Lang.isNull(Y.one(' .requesting'))) {
                    // Ничего не делаем.
                    return false;
                }
                // Если есть в параллелях незапланированные обязательные дисциплины
                if (!Y.Lang.isNull(Y.one(' .required.planning'))) {
                    Y.one('#planrequiredall').removeClass('havenoelements');
                    changed = true;
                } else {
                    Y.one('#planrequiredall').addClass('havenoelements');
                    changed = true;
                }
            }
            var currentagenum = PARAMS.learnplanparams.agenum;
            if (Y.one('#id_autosubscribe')) {
                // Сначала проверим, есть ли дисциплины с запросами (requesting) в параллели.
                if (!Y.Lang.isNull(Y.one('#pitems_' + currentagenum + ' .requesting'))) {
                    // Ничего не делаем.
                    return false;
                }
                // Если есть в параллели запланированные дисциплины
                if (!Y.Lang.isNull(Y.one('#pitems_' + currentagenum + ' .planned'))) {
//                    Y.one('#id_autosubscribe').removeClass('havenoelements');
                    Y.one('#id_autosubscribe').removeAttribute('disabled');
                    // Ещё одна кнопка (внизу)
                    Y.one('#id_buttonar_subscribe').removeAttribute('disabled');
                    changed = true;
                } else {
//                    Y.one('#id_autosubscribe').addClass('havenoelements');
                    Y.one('#id_autosubscribe').setAttribute('disabled', 'disabled');
                    // Ещё одна кнопка (внизу)
                    Y.one('#id_buttonar_subscribe').setAttribute('disabled', 'disabled');
                    changed = true;
                }
            }
            return changed;
        },
        
        /**
         * Обновить параллель: в зависимости от наличия/отсутствия дисциплин 
         * показывает/скрывает параллель
         * 
         * @param {type} agenum
         * @returns {undefined}
         */
        refresh_agenum: function(agenum) {
            var self = this;
            var pitem = Y.one('#pitems_' + agenum +' tr[id*=pitem_]');
            if (!pitem) {
                self.hide_agenum(agenum);
            } else {
                self.show_agenum(agenum);
            }
            // Обновим классы even и odd
            self.refresh_pitems_rows(agenum);
        },
        
        /**
         * Обновить часы: пересчитывает часы на основе текущих запланированных и
         *  изученных дисциплин
         * 
         * @param {type} agenum
         * @returns {undefined}
         */
        refresh_hours_agenum: function(operation, agenum) {
            if (agenum < 1) {
                return;
            }
            var self = this,
                // Достанем все запланированные и изученные дисциплины, а так же планируемые с запросом
                plannedlearned = Y.all('#pitems_' + agenum + ' tr.learned, #pitems_' +
                 agenum + ' tr.planned, #pitems_' + agenum + ' tr.planning.requesting'),
                fields = {'hours'        : 0, 'hourstheory'    : 0, 'hourspractice' : 0, 
                          'hoursweek'    : 0, 'hourslab'       : 0, 'hoursind'      : 0,
                          'hourscontrol' : 0, 'hoursclassroom' : 0, 'maxcredit'     : 0};
            // Посчитаем сумму всех часов и ЗЕТ
            plannedlearned.each(function(node) {
                // Если запланированная дисциплина планируетcя, она либо станет
                // незапланированной, либо останется такой же
                if (node.hasClass('planned') && node.hasClass('requesting') && operation === 'excludefromplan') {
                } else {
                    var pitemid = node.getData('pitemid');
                    Y.Object.each(fields, function(value, name) {
                        fields[name] = value + PARAMS.pitemsparams[pitemid][name];
                    });
                }
            });
            // Обновим поля в соответствующей таблице
            Y.Object.each(fields, function(value, name) {
                var hoursagenum = Y.one('#hours_agenum_' + agenum +' tbody tr td.' + name);
                if ( hoursagenum ) {
                    hoursagenum.set('innerHTML', value);
                }
            });
            // Возвратим сумму за параллель
            return fields;
        },
        
        /**
         * Обновить часы: пересчитывает часы на основе текущих запланированных и
         *  изученных дисциплин
         * 
         * @param {type} agenum
         * @returns {undefined}
         */
        refresh_hours_agenums: function(operation, agenum) {
            if (agenum < 1) {
                return;
            }
            var self = this;
            // Параллели, для которых необходимо считать часы
            var agenums = PARAMS.agenumsmap[agenum];
            // Посчитаем сумму всех часов и ЗЕТ
            var sum = {};
            for (var agenum in agenums) {
                // Заодно обновятся все параллели
                var fields = self.refresh_hours_agenum(operation, agenums[agenum]);
                for (var field in fields) {
                    if (sum[field] === undefined) {
                        sum[field] = fields[field];
                    } else {
                        sum[field] += fields[field];
                    }
                }
            }
            // Обновим поля в соответствующей таблице
            Y.Object.each(sum, function(value, name) {
                var hoursagenums = Y.one('#hours_agenums_' + agenums.join('_') +' tbody tr td.' + name);
                if ( hoursagenums ) {
                    hoursagenums.set('innerHTML', value);
                }
            });
        },
        
        /**
         * Получить текущую (отображаемую в "Семестр N") параллель дисциплины
         * 
         * @param {integer} pitemid
         * @returns {string} параллель
         */
        get_current_agenum: function(pitemid) {
            var pitemrow = Y.one('#pitem_' + pitemid),
                agenumid = pitemrow.ancestor('fieldset').get('id'),
                agenum   = agenumid.split('id_ages')[1];
            return agenum;
        },

        /**
         * Обновить строки дисциплин в выбранной параллели (чередование чётных и 
         * нечётых строк)
         * 
         * @param {integer} agenum - номер параллели
         * @returns {undefined}
         */
        refresh_pitems_rows: function(agenum) {
            var pitems = Y.all('#pitems_' + agenum + ' tbody tr'),
                even   = false;
            if (!pitems) {
                return;
            }
            pitems.each(function (pitem) {
                pitem.removeClass('r0')
                pitem.removeClass('r1');
                if (even) {
                    pitem.addClass('r1');
                } else {
                    pitem.addClass('r0');
                }
                even = !even;
            });
        },


        /**
         * Добавить элементы Drag&Drop к дисциплинам
         * 1. Удаляются старые элементы
         * 2. Генерируются элементы для всех параллелей, скрываются
         * 3. При нажатии на "якорь" отображаются только нужные, выстраиваясь по кругу
         * 
         * @returns {undefined}
         */
        assign_dragdrop: function () {
            this.assign_handlers_changeagenumajax();
            this.assign_handlers_pitemdragdrop();
        },
        
        /**
         * Добавить обработчики на перетаскивание к дисциплинам (строки)
         * 
         * @returns {undefined}
         */
        assign_handlers_pitemdragdrop: function() {
            var self = this;
            var del = new Y.DD.Delegate({
                container: 'tbody',
                nodes: 'tr.draggable',
            });
            del.on('drag:mouseDown', function(e) {
                e.target.get('node').setStyle('opacity', '.8');
                var pitemid = e.target.get('node').getData('pitemid');
                var agenums = PARAMS.pitemsparams[pitemid].agenums;
                var groups = [];
                // Сохраним позицию скролла и скролла до элемента
                var scrolltop = (document.documentElement.scrollTop||document.body.scrollTop);
                var elemscrolltop = e.target.get('node').get('region').top;
                // Смещение от края позиции скролла окна до элемента
                var offset = elemscrolltop - scrolltop;
                // Покажем те параллели, в которые мы можем бросать
                for(var ind in agenums) {
                    groups.push('ages'+ agenums[ind]);
                    self.show_agenum(agenums[ind]);
                }
                this.dd.set('groups', groups);
                elemscrolltop = e.target.get('node').get('region').top;
                // Вычтем из нового положения элемента смещение
                window.scrollTo(0, elemscrolltop - offset);
                
                Y.fire('agenumchanged');
            });
            
            del.on('drag:mouseup', function(e) {
                var element = e.target.get('node');
                element.setStyle('opacity', '1');
                var scrolltop = (document.documentElement.scrollTop||document.body.scrollTop);
                var elemscrolltop = e.target.get('node').get('region').top;
                var offset = elemscrolltop - scrolltop;
                for(var i = 1; i <= PARAMS.learnplanparams.agenums; i++) {
                    self.refresh_agenum(i);
                }
                var elemscrolltop = e.target.get('node').get('region').top;
                window.scrollTo(0, elemscrolltop - offset);
                Y.fire('agenumchanged');
                
            });
            
            del.on('drag:end', function(e) {
                e.target.get('node').setStyle('opacity', '1');
                // Сохраним позицию скролла и скролла до элемента
                var scrolltop = (document.documentElement.scrollTop||document.body.scrollTop);
                var elemscrolltop = e.target.get('node').get('region').top;
                // Смещение от края позиции скролла окна до элемента
                var offset = elemscrolltop - scrolltop;
                // Обновим параллели
                for(var i = 1; i <= PARAMS.learnplanparams.agenums; i++) {
                    self.refresh_agenum(i);
                }
                var elemscrolltop = e.target.get('node').get('region').top;
                // Вычтем из нового положения элемента смещение
                window.scrollTo(0, elemscrolltop - offset);
                Y.fire('agenumchanged');
            });

            del.dd.plug(Y.Plugin.DDConstrained, {
                constrain2node: '#form-container'
            });
            
            del.dd.plug(Y.Plugin.DDProxy, {
                resizeFrame: true,
                centerFrame: true,
                moveOnEnd: false,
                hideOnEnd: true,
                borderStyle: 'none',
                cloneNode: false
            });

            // Сделаем "слоты" для бросания дисциплин - параллели
            for(var i = 1; i <= PARAMS.learnplanparams.agenums; i++) {
                var agesid = '#pitems_' + i,
                    groups = ['ages' + i];
                var drop = Y.one(agesid).setData('agenum', i)
                                        .plug(Y.Plugin.Drop, {
                                            groups: groups
                                        });
                drop.drop.on('drop:hit', function(e) {
                    var node = e.target.get('node'),
                       agenum = node.getData('agenum');
                    var dragnode = e.drag.get('node'),
                         pitemid = dragnode.getData('pitemid');
                    self.submit_element(e, 'movetoagenum', pitemid, agenum);
                });            
            }
        },
        
        /**
         * Добавить элементы changeagenumajax к дисциплинам
         * 1. Удаляются старые элементы
         * 2. Генерируются элементы для всех параллелей, скрываются
         * 3. При нажатии на "якорь" отображаются только нужные, выстраиваясь по кругу
         * 
         * @returns {undefined}
         */
        assign_handlers_changeagenumajax: function() {
            var self = this;
            // Удалим select-элементы, изменим элементы перемещения в параллель на "якори"
            Y.all('tr.planning td select, tr.planned td select').each(function () { this.remove(true); });
            Y.all('input.changeagenum').each(function () {
                var handler = Y.Node.create('<div>0</div>'),
                    pitemid = this.ancestor('tr').get('id').split('pitem_')[1];
                handler.addClass('changeagenumajax')
                       .setData('pitemid', pitemid)
                       .set('id', 'changenum_' + this.ancestor('tr').get('id').split('pitem_')[1]);
                this.insertBefore(handler, this);
                this.remove(true);
                handler.plug(Y.Plugin.Drag);

                handler.dd.on('drag:mouseDown', function(e) {
                    var d = e.target.get('node');
                    // Скроем "перетаскиваемый" элемент
                    d.setStyle('opacity', '0');
                    var p = d.get('region');
                    var agenums = PARAMS.pitemsparams[pitemid].agenums;
                    self.view_agenums(agenums,p);
                });
                handler.dd.on('drag:mouseup', function(e) {
                    Y.all('.agenumplace').addClass('invisible');
                    // Вернём "перетаскиваемый" элемент на место
                    e.target.get('node').setStyle('opacity', '1');
                });
                handler.dd.on('drag:end', function(e) {
                    Y.all('.agenumplace').addClass('invisible');
                    // Вернём "перетаскиваемый" элемент на место
                    e.target.get('node').setStyle('opacity', '1');
                });

                handler.dd.plug(Y.Plugin.DDConstrained, {
                    constrain2node: '#form-container'
                });

                handler.dd.plug(Y.Plugin.DDProxy, {
                    moveOnEnd: false,
                    cloneNode: true
                });

            });
            for(var i = 1; i <= PARAMS.learnplanparams.agenums; i++) {
                //Угол поворота
                var agenumdrop = Y.Node.create('<div>' + i + '</div>');
                var l = 32;
                var a = 360 / PARAMS.learnplanparams.agenums;
                //радиус окружности, по которой выстраиваются блоки
                var r = PARAMS.learnplanparams.agenums * l / Math.PI / 2.0;
                var rotate = i*a;
                var drop = agenumdrop.addClass('agenumplace')
                          .addClass('invisible')
                          .set('id', 'drop_agenum_' + i)
                          .appendTo('#form-container')
                          .plug(Y.Plugin.Drop);
                drop.drop.on('drop:hit', function(e) {
                    var node = e.target.get('node'),
                       agenum = node.getData('agenum');
                    var dragnode = e.drag.get('node'),
                         pitemid = dragnode.getData('pitemid');
                    self.submit_element(e, 'movetoagenum', pitemid, agenum);
                });
            }
        },

        /**
         * Отобразить список доступных параллелей для переноса дисциплины
         * 
         * @param {object|array} agenums - список семестров, в которые можно переносить дисциплину
         * @param {object} position - позиция элемента (handler), возле которого
         *  "появляются" параллели
         * @returns {undefined}
         */
        view_agenums: function(agenums,position) {
            //Ширина и высота div'а
            var l = 32;
            //Угол поворота
            if (Y.Lang.isObject(agenums)) {
                var agenumslength = Object.keys(agenums).length;
            } else if (Y.Lang.isArray(agenums)) {
                var agenumslength = agenums.length;
            }
            var a = 360 / agenumslength;
            //радиус окружности, по которой выстраиваются блоки
            var r = agenumslength * l / Math.PI / 2.0;
            var i = 0;
            for(var ind in agenums) {
                var agenumdrop = Y.one('#drop_agenum_' + agenums[ind]);
                if (!Y.Lang.isNull(agenumdrop)) {
                    agenumdrop.removeClass('invisible')
                              .setData('agenum', agenums[ind])
                              .setStyles({
                                    'top':       position.top + 'px',
                                    'left':      position.left + 'px',
                                    'transform': 'rotate(' + i*a + 'deg) translate(0px,-' + (r+l/2) + 'px)',
                                    'width':     l + 'px',
                                    'height':    l + 'px'
                              });
                    i++;
                }
            }
        }

    };

    Y.extend(AJAX, Y.Base, AJAX.prototype, {
        NAME : AJAXNAME,
        ATTRS : {
            container : '',
            type : '',
            typeid : 0,
            submitparams : [],
            learnplanparams : {},
            pitemsparams : [],
            ajaxurl : ''
        }

    });

    M.block_dof = M.block_dof || {};
    M.block_dof.init_ajax = function(params) {
        return new AJAX(params);
    }

}, '@VERSION@', {
    requires:['anim', 'array-extras', 'base', 'dd-constrain', 'dd-delegate', 'dd-drop',
              'dd-proxy', 'event-resize', 'io', 'json-parse', 'json', 'node', 'panel']
});
