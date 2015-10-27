<?php /* Smarty version Smarty-3.1.7, created on 2015-10-25 12:09:26
         compiled from "C:\wamp\www\projects\ottocratcrm6.3\includes\runtime/../../layouts/vlayout\modules\Ottocrat\SideBar.tpl" */ ?>
<?php /*%%SmartyHeaderCode:30419562cc6766e37d3-90376655%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '327242d66547141b4b768ee9530cfaa4f1a51b07' => 
    array (
      0 => 'C:\\wamp\\www\\projects\\ottocratcrm6.3\\includes\\runtime/../../layouts/vlayout\\modules\\Ottocrat\\SideBar.tpl',
      1 => 1443436061,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '30419562cc6766e37d3-90376655',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'MODULE' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_562cc67674849',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_562cc67674849')) {function content_562cc67674849($_smarty_tpl) {?>
<div class="sideBarContents"><?php echo $_smarty_tpl->getSubTemplate (vtemplate_path('SideBarLinks.tpl',$_smarty_tpl->tpl_vars['MODULE']->value), $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>
<div class="clearfix"></div><?php echo $_smarty_tpl->getSubTemplate (vtemplate_path('SideBarWidgets.tpl',$_smarty_tpl->tpl_vars['MODULE']->value), $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>
</div><?php }} ?>