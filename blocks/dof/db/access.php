<?php
//
// Capability definitions for the rss_client block.
//
// The capabilities are loaded into the database table when the block is
// installed or updated. Whenever the capability definitions are updated,
// the module version number should be bumped up.
//
// The system has four possible values for a capability:
// CAP_ALLOW, CAP_PREVENT, CAP_PROHIBIT, and inherit (not set).
//
//
// CAPABILITY NAMING CONVENTION
//
// It is important that capability names are unique. The naming convention
// for capabilities that are specific to modules and blocks is as follows:
//   [mod/block]/<component_name>:<capabilityname>
//
// component_name should be the same as the directory name of the mod or block.
//
// Core moodle capabilities are defined thus:
//    moodle/<capabilityclass>:<capabilityname>
//
// Examples: mod/forum:viewpost
//           block/recent_activity:view
//           moodle/site:deleteuser
//
// The variable name for the capability definitions array follows the format
//   $<componenttype>_<component_name>_capabilities
//
// For the core capabilities, the variable is $moodle_capabilities.


$capabilities = array(
// Право пользоваться модулем и видеть собственную информацию
// (для студентов - собственная зачетка и т.п. для преподавателей - собственные курсы, расписание)
// Пользователи, у которых нет этого права не могут войти в Деканат совсем, даже в меню.
    'block/dof:view' => array(
       'captype' => 'read',
       'contextlevel' => CONTEXT_SYSTEM,
       'legacy' => array(
           'guest' => CAP_PREVENT,
           'user' => CAP_ALLOW,
           'manager' => CAP_ALLOW
       )
   ),
   // Право управления учебной информацией
    'block/dof:manage' => array(
       'riskbitmask' => RISK_MANAGETRUST,
       'captype' => 'write',
       'contextlevel' => CONTEXT_SYSTEM,
       'legacy' => array(
           'guest' => CAP_PREVENT,
           'manager' => CAP_ALLOW
       )
    ),
     // Право изменения данных в обход бизнес-процессов, настройки параметров бизнес-процессов
     // Это полномочие следует назначать узкому кругу квалифицированных специалистов, хорошо разбирающихся в 
     // реляционной структуре базы данных Деканата и представляющих, как их изменения повлияют на систему
    'block/dof:datamanage' => array(
       'riskbitmask' => RISK_MANAGETRUST,
       'captype' => 'write',
       'contextlevel' => CONTEXT_SYSTEM,
       'legacy' => array(
           'guest' => CAP_PREVENT,
           'manager' => CAP_ALLOW
       )
   ),
     // Право технического администрирования модуля
    'block/dof:admin' => array(
       'riskbitmask' => RISK_MANAGETRUST,
       'captype' => 'write',
       'contextlevel' => CONTEXT_SYSTEM,
       'legacy' => array(
           'guest' => CAP_PREVENT,
           'manager' => CAP_ALLOW
       )
   ),

   // Право индивидуального размещения блока
   'block/dof:myaddinstance' => array(
       'captype' => 'write',
       'contextlevel' => CONTEXT_SYSTEM,
       'archetypes' => array(
           'guest' => CAP_PREVENT,
           'user' => CAP_ALLOW,
           'manager' => CAP_ALLOW
       ),
        
       'clonepermissionsfrom' => 'moodle/my:manageblocks'
   ),
        
   // Право размещения блока
   'block/dof:addinstance' => array(
       'riskbitmask' => RISK_SPAM | RISK_XSS,
        
       'captype' => 'write',
       'contextlevel' => CONTEXT_BLOCK,
       'archetypes' => array(
           'guest' => CAP_PREVENT,
           'manager' => CAP_ALLOW
       ),
        
       'clonepermissionsfrom' => 'moodle/site:manageblocks'
   )

);

?>