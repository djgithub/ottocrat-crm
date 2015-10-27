<?php /* Smarty version Smarty-3.1.7, created on 2015-10-25 12:10:18
         compiled from "C:\wamp\www\projects\ottocratcrm6.3\includes\runtime/../../layouts/vlayout\modules\Settings\Webforms\EditView.tpl" */ ?>
<?php /*%%SmartyHeaderCode:31876562cc6aa92ec94-76489204%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '2215c6b9df5759cda357614b1fc190745b930ac4' => 
    array (
      0 => 'C:\\wamp\\www\\projects\\ottocratcrm6.3\\includes\\runtime/../../layouts/vlayout\\modules\\Settings\\Webforms\\EditView.tpl',
      1 => 1443633688,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '31876562cc6aa92ec94-76489204',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'QUALIFIED_MODULE' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_562cc6aaa29cd',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_562cc6aaa29cd')) {function content_562cc6aaa29cd($_smarty_tpl) {?>
<?php echo $_smarty_tpl->getSubTemplate (vtemplate_path("EditViewBlocks.tpl",'Ottocrat'), $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>
<div class="targetFieldsTableContainer"><?php echo $_smarty_tpl->getSubTemplate (vtemplate_path("FieldsEditView.tpl",$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value), $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>
</div><br><?php echo $_smarty_tpl->getSubTemplate (vtemplate_path("EditViewActions.tpl",'Ottocrat'), $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>
<div class="row-fluid" style="margin-bottom:150px;"></div><?php }} ?>