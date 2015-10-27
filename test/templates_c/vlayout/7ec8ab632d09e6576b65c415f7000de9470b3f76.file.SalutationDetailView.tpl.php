<?php /* Smarty version Smarty-3.1.7, created on 2015-10-27 12:15:19
         compiled from "C:\wamp\www\projects\ottocratcrm6.3\includes\runtime/../../layouts/vlayout\modules\Ottocrat\uitypes\SalutationDetailView.tpl" */ ?>
<?php /*%%SmartyHeaderCode:11396562f6ad71b9997-78453513%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '7ec8ab632d09e6576b65c415f7000de9470b3f76' => 
    array (
      0 => 'C:\\wamp\\www\\projects\\ottocratcrm6.3\\includes\\runtime/../../layouts/vlayout\\modules\\Ottocrat\\uitypes\\SalutationDetailView.tpl',
      1 => 1443436063,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '11396562f6ad71b9997-78453513',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'RECORD' => 0,
    'FIELD_MODEL' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_562f6ad722251',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_562f6ad722251')) {function content_562f6ad722251($_smarty_tpl) {?>


<?php echo $_smarty_tpl->tpl_vars['RECORD']->value->getDisplayValue('salutationtype');?>


<?php echo $_smarty_tpl->tpl_vars['FIELD_MODEL']->value->getDisplayValue($_smarty_tpl->tpl_vars['FIELD_MODEL']->value->get('fieldvalue'),$_smarty_tpl->tpl_vars['RECORD']->value->getId(),$_smarty_tpl->tpl_vars['RECORD']->value);?>
<?php }} ?>