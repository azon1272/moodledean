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
YUI.add('moodle-block_dof-dragdrop', function(Y) {

    var CSS = {
        ACTIONAREA: '.actions',
        ACTIVITY : 'activity',
        ACTIVITYINSTANCE : 'activityinstance',
        CONTENT : 'content',
        COURSECONTENT : 'course-content',
        DEFAULTLISTSELECTOR : 'ddropitem',
        EDITINGMOVE : 'editing_move',
        ICONCLASS : 'iconsmall',
        JUMPMENU : 'jumpmenu',
        LEFT : 'left',
        LIGHTBOX : 'lightbox',
        MOVEDOWN : 'movedown',
        MOVEUP : 'moveup',
        PAGECONTENT : 'page-content',
        RIGHT : 'right',
        SECTION : 'section',
        SECTIONADDMENUS : 'section_add_menus',
        SECTIONHANDLE : 'section-handle',
        SUMMARY : 'summary',
        SECTIONDRAGGABLE: 'sectiondraggable'
    };

    var DRAGDISCIPLINE = function() {
        DRAGDISCIPLINE.superclass.constructor.apply(this, arguments);
    };
    Y.extend(DRAGDISCIPLINE, M.core.dragdrop, {
        initializer : function(params) {
            // Set group for parent class
            this.groups = ['resource'];
            this.samenodeclass = CSS.ACTIVITY;
            this.parentnodeclass = CSS.SECTION;
            this.resourcedraghandle = this.get_drag_handle(M.util.get_string('movecoursemodule', 'moodle'), CSS.EDITINGMOVE, CSS.ICONCLASS, true);

            this.samenodelabel = {
                identifier: 'afterresource',
                component: 'moodle'
            };
            this.parentnodelabel = {
                identifier: 'totopofsection',
                component: 'moodle'
            };

            // Прикрепляем классы на перемещаемые (drag&drop) элементы формы
            return;
            this.attach_dragdrop(params.submitparams);
            var disciplinelistselector = CSS.DEFAULTLISTSELECTOR;
            // Передали параметр с селектором
            if (params.listselector) {
                disciplinelistselector = params.listselector;
            }
            
            // По всем перемещаемым нодам:
            if (disciplinelistselector) {
                this.setup_dragdrop(disciplinelistselector);

                // Initialise drag & drop for all resources/activities
                var nodeselector = disciplinelistselector.slice(CSS.COURSECONTENT.length+2)+' li.'+CSS.ACTIVITY;
                var del = new Y.DD.Delegate({
                    container: '.'+CSS.COURSECONTENT,
                    nodes: nodeselector,
                    target: true,
                    handles: ['.' + CSS.EDITINGMOVE],
                    dragConfig: {groups: this.groups}
                });
                del.dd.plug(Y.Plugin.DDProxy, {
                    // Don't move the node at the end of the drag
                    moveOnEnd: false,
                    cloneNode: true
                });
                del.dd.plug(Y.Plugin.DDConstrained, {
                    // Keep it inside the .course-content
                    constrain: '#'+CSS.PAGECONTENT
                });
                del.dd.plug(Y.Plugin.DDWinScroll);

                M.block_dof.register_module(this);
                M.block_dof.dragres = this;
            }
        },
        
        /**
         * Добавляет классы dragdrop элементам формы elements
         * 
         * @param {array} elements
         * @return void
         */
        attach_dragdrops: function(elements) {
            // Добавить в план
            if ( elements.addtoplan ) {
                Y.Array.each(elements.addtoplan, function(item, index) {
                    f.attach_dragdrop_class('addtoplan', item, index);
                });
            }
            // Исключить из плана
            if ( elements.excludefromplan ) {
                Y.Array.each(elements.excludefromplan, function(item, index) {
                    f.attach_dragdrop_class('addtoplan', item, index);
                });
            }
            // Добавить в параллель
            if ( elements.addtoagenum ) {
                Y.Object.each(elements.addtoagenum, function(item, index) {
                    f.attach_dragdrop_class('addtoagenum', item, index);
                });
            }
            // Перенести в параллель
            if ( elements.transfertoagenum ) {
                Y.Object.each(elements.transfertoagenum, function(item, index) {
                    f.attach_dragdrop_class('transfertoagenum', item, index);
                });
            }
        },
        
        /**
         * Добавить класс dragdrop к элементу формы
         * 
         * @param {string} element
         * @param {string} item - id элемента
         * @return void
         */
        attach_dragdrop_class: function(element, elementid) {
            var ancestor = 'field';
            var ancestorNode = null;
            switch (element) {
                case 'addtoplan':
                case 'excludefromplan':
                case 'addtoagenum':
                case 'transfertoagenum':
                    ancestor  = 'tr';
                    ancestorNode = Y.one('#' + elementid).ancestor(ancestor);
                    break;
                default:
                    ancestorNode = Y.one('#' + elementid).ancestor(ancestor)
                    break;
            }
            
            if (ancestorNode) {
//                var r = ancestorNode.get('region');
//                ancestorNode.get('parentNode').appendChild();
            }
        },
        

         /**
         * Применить возможности drag&drop к указанному селектору или ноду
         *
         * @param baseselector CSS-селектор или нода, ограничивающие область
         * @return void
         */
        setup_dragdrop : function(baseselector) {
            Y.Node.all(baseselector).each(function(sectionnode) {
                var resources = sectionnode.one('.'+CSS.CONTENT+' ul.'+CSS.SECTION);
                // See if resources ul exists, if not create one
                if (!resources) {
                    var resources = Y.Node.create('<ul></ul>');
                    resources.addClass(CSS.SECTION);
                    sectionnode.one('.'+CSS.CONTENT+' div.'+CSS.SUMMARY).insert(resources, 'after');
                }
                resources.setAttribute('data-draggroups', this.groups.join(' '));
                // Define empty ul as droptarget, so that item could be moved to empty list
                var tar = new Y.DD.Drop({
                    node: resources,
                    groups: this.groups,
                    padding: '20 0 20 0'
                });

                // Initialise each resource/activity in this section
                //this.setup_for_resource('#'+sectionnode.get('id')+' li.'+CSS.ACTIVITY);
            }, this);
        },
        
        /**
         * Применить возможности drag&drop указанному селектору или ноду, который относится к дисциплине(ам)
         *
         * @param baseselector CSS-селектор или нода, которым необходимо ограничить область
         * @return void
         */
//        setup_for_resource : function(baseselector) {
//            Y.Node.all(baseselector).each(function(resourcesnode) {
//                // Replace move icons
//                var move = resourcesnode.one('a.'+CSS.EDITINGMOVE);
//                if (move) {
//                    move.replace(this.resourcedraghandle.cloneNode(true));
//                }
//            }, this);
//        },

        /**
         * Обработчик при начале перетаскивания объекта
         * 
         * @param {EventFacade} e
         * @returns {undefined}
         */
        drag_start : function(e) {
            // Занижаем opacity 
            e.target.get('node').setStyle('opacity', '.8');
        },

        /**
         * Обработчик при окончании перетаскивания объекта
         * 
         * @param {EventFacade} e
         * @returns {undefined}
         */
        drag_end : function(e) {
            // Возвращаем opacity обратно
            e.target.get('node').setStyle('opacity', '1');
        },

        /**
         * Обработчик при промахе объекта мимо области для бросания
         * 
         * @param {type} e
         * @returns {undefined}
         */
        drag_dropmiss : function(e) {
            // Missed the target, but we assume the user intended to drop it
            // on the last last ghost node location, e.drag and e.drop should be
            // prepared by global_drag_dropmiss parent so simulate drop_hit(e).
            this.drop_hit(e);
        },

        /**
         * Обработчик при попадании объекта в область для бросания
         * 
         * @param {type} e
         * @returns {undefined}
         */
        drop_hit : function(e) {
            var drag = e.drag;
            // Get a reference to our drag node
            var dragnode = drag.get('node');
            var dropnode = e.drop.get('node');

            // Add spinner if it not there
            var actionarea = dragnode.one(CSS.ACTIONAREA);
            var spinner = M.util.add_spinner(Y, actionarea);

            var params = {};

            // Handle any variables which we must pass back through to
            var pageparams = this.get('config').pageparams;
            var varname;
            for (varname in pageparams) {
                params[varname] = pageparams[varname];
            }

            // Prepare request parameters
            params.sesskey = M.cfg.sesskey;
            params.courseId = this.get('courseid');
            params['class'] = 'resource';
            params.field = 'move';
            params.id = Number(Y.Moodle.core_course.util.cm.getId(dragnode));
//            params.sectionId = Y.Moodle.core_course.util.section.getId(dropnode.ancestor(M.block_dof.format.get_section_wrapper(Y), true));

            if (dragnode.next()) {
                params.beforeId = Number(Y.Moodle.core_course.util.cm.getId(dragnode.next()));
            }

            // Do AJAX request
            var uri = M.cfg.wwwroot + this.get('ajaxurl');

            Y.io(uri, {
                method: 'POST',
                data: params,
                on: {
                    start : function(tid) {
                        this.lock_drag_handle(drag, CSS.EDITINGMOVE);
                        spinner.show();
                    },
                    success: function(tid, response) {
                        var responsetext = Y.JSON.parse(response.responseText);
                        var params = {element: dragnode, visible: responsetext.visible};
//                        M.block_dof.coursebase.invoke_function('set_visibility_resource_ui', params);
                        this.unlock_drag_handle(drag, CSS.EDITINGMOVE);
                        window.setTimeout(function(e) {
                            spinner.hide();
                        }, 250);
                    },
                    failure: function(tid, response) {
                        this.ajax_failure(response);
                        this.unlock_drag_handle(drag, CSS.SECTIONHANDLE);
                        spinner.hide();
                        // TODO: revert nodes location
                    }
                },
                context:this
            });
        }
    }, {
        NAME : 'course-dragdrop-resource',
        ATTRS : {
            courseid : {
                value : null
            },
            ajaxurl : {
                'value' : 0
            },
            config : {
                'value' : 0
            },
            listselector : {
                'value' : null
            },
        }
    });

    M.block_dof = M.block_dof || {};
    M.block_dof.init_learningplan_dragdrop = function(params) {
        new DRAGDISCIPLINE(params);
    }
}, '@VERSION@', {requires:['base', 'node', 'io', 'dom', 'dd', 'dd-scroll', 'moodle-core-dragdrop', 'moodle-core-notification', 'moodle-course-coursebase', 'moodle-course-util']});

YUI().use('dd-delegate', 'dd-drop-plugin', 'dd-constrain', 'dd-proxy', function(Y) {
    var del = new Y.DD.Delegate({
        container: '#id_ages0 tbody',
        nodes: 'tr'
    });

    del.on('drag:start', function(e) {
        e.target.get('node').setStyle('opacity', '.8');
    });
    del.on('drag:end', function(e) {
        e.target.get('node').setStyle('opacity', '1');
    });

    del.dd.plug(Y.Plugin.DDConstrained, {
        constrain2node: '#form-container'
    });

    del.dd.plug(Y.Plugin.DDProxy, {
//        moveOnEnd: true,
        cloneNode: true
    });

    var drop = Y.one('#id_ages1 tbody').plug(Y.Plugin.Drop);
    drop.drop.on('drop:hit', function(e) {
        var child = e.drag.get('node');
        e.drag.get('node').remove();
        Y.one('#id_ages1 tbody').get();
        Y.one('#id_ages1 tbody').appendChild(child);
    });


});