<?php /* Smarty version Smarty-3.1.7, created on 2015-10-27 13:27:39
         compiled from "C:\wamp\www\projects\ottocratcrm6.3\includes\runtime/../../layouts/vlayout\modules\MailManager\Mainui.tpl" */ ?>
<?php /*%%SmartyHeaderCode:8397562f7bcb2cc422-79656831%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'c90ad2cb93a48694a5cbcc4e928965cba3c2df43' => 
    array (
      0 => 'C:\\wamp\\www\\projects\\ottocratcrm6.3\\includes\\runtime/../../layouts/vlayout\\modules\\MailManager\\Mainui.tpl',
      1 => 1443633578,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '8397562f7bcb2cc422-79656831',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'MAILBOX' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_562f7bcb32cfe',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_562f7bcb32cfe')) {function content_562f7bcb32cfe($_smarty_tpl) {?>

<input type="hidden" name="refresh_timeout" id="refresh_timeout" value="<?php echo $_smarty_tpl->tpl_vars['MAILBOX']->value->refreshTimeOut();?>
"/><?php }} ?>