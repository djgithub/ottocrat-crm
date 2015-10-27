<?php /* Smarty version Smarty-3.1.7, created on 2015-10-27 12:36:23
         compiled from "C:\wamp\www\projects\ottocratcrm6.3\includes\runtime/../../layouts/vlayout\modules\Settings\Ottocrat\ListViewHeader.tpl" */ ?>
<?php /*%%SmartyHeaderCode:16628562f6fc7cb1e14-21180311%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'cec55a56f2f5f0165413e1c47bf8d92d099c55f4' => 
    array (
      0 => 'C:\\wamp\\www\\projects\\ottocratcrm6.3\\includes\\runtime/../../layouts/vlayout\\modules\\Settings\\Ottocrat\\ListViewHeader.tpl',
      1 => 1443436059,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '16628562f6fc7cb1e14-21180311',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'MODULE' => 0,
    'QUALIFIED_MODULE' => 0,
    'LISTVIEW_LINKS' => 0,
    'LISTVIEW_BASICACTION' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_562f6fc7ddbb9',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_562f6fc7ddbb9')) {function content_562f6fc7ddbb9($_smarty_tpl) {?>
<div class="container-fluid"><div class="widget_header row-fluid"><h3><?php echo vtranslate($_smarty_tpl->tpl_vars['MODULE']->value,$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</h3></div><hr><?php if ($_smarty_tpl->tpl_vars['MODULE']->value=='Groups'){?><div><?php echo vtranslate('Groups Desc',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</div><?php }?><?php if ($_smarty_tpl->tpl_vars['MODULE']->value=='PickListDependency'){?><div><?php echo vtranslate('LBL_PICKLIST_DEPENDENCY_TEXT',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</div><?php }?><?php if ($_smarty_tpl->tpl_vars['MODULE']->value=='Profiles'){?><div>Profiles provide you the fine grained access control to Ottocrat CRM. Profiles can be used to regulate, or completely disable user's access on modules, fields, and other actions (eg.Import).<ul> <li>With profiles you can set the user privileges to delete, create/edit or view data.</li><li>	Like <b><i>Sharing Access</i></b>, profiles also play vital role in ensuring security by limiting the activities on records.Please note that the *settings of the global privileges are always superior to the other privilege settings.</li<li>	Roles are based on profiles.One or more profiles can be linked to Roles.</li><li>	<b>Ottocrat CRM</b> comes with a set of pre-defined profiles(ex: 'Administrator') which you can use and change but not delete.</li></div><?php }?><div class="row-fluid"><span class="span8 btn-toolbar"><?php  $_smarty_tpl->tpl_vars['LISTVIEW_BASICACTION'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['LISTVIEW_BASICACTION']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['LISTVIEW_LINKS']->value['LISTVIEWBASIC']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['LISTVIEW_BASICACTION']->key => $_smarty_tpl->tpl_vars['LISTVIEW_BASICACTION']->value){
$_smarty_tpl->tpl_vars['LISTVIEW_BASICACTION']->_loop = true;
?><button class="btn addButton" <?php if (stripos($_smarty_tpl->tpl_vars['LISTVIEW_BASICACTION']->value->getUrl(),'javascript:')===0){?> onclick='<?php echo substr($_smarty_tpl->tpl_vars['LISTVIEW_BASICACTION']->value->getUrl(),strlen("javascript:"));?>
;'<?php }else{ ?> onclick='window.location.href="<?php echo $_smarty_tpl->tpl_vars['LISTVIEW_BASICACTION']->value->getUrl();?>
"' <?php }?>><i class="icon-plus"></i>&nbsp;<strong><?php echo vtranslate('LBL_ADD_RECORD',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</strong></button><?php } ?></span><span class="span4 btn-toolbar"><?php echo $_smarty_tpl->getSubTemplate (vtemplate_path('ListViewActions.tpl',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value), $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>
</span></div><div class="clearfix"></div><div class="listViewContentDiv" id="listViewContents"><?php }} ?>