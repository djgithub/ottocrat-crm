<?php /* Smarty version Smarty-3.1.7, created on 2015-10-25 12:09:28
         compiled from "C:\wamp\www\projects\ottocratcrm6.3\includes\runtime/../../layouts/vlayout\modules\Ottocrat\uitypes\FieldSearchView.tpl" */ ?>
<?php /*%%SmartyHeaderCode:25797562cc678a11a03-07563013%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '7de67894da46b2c04af5298a8489df98a70dd220' => 
    array (
      0 => 'C:\\wamp\\www\\projects\\ottocratcrm6.3\\includes\\runtime/../../layouts/vlayout\\modules\\Ottocrat\\uitypes\\FieldSearchView.tpl',
      1 => 1443436063,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '25797562cc678a11a03-07563013',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'FIELD_MODEL' => 0,
    'SEARCH_INFO' => 0,
    'FIELD_INFO' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_562cc678cc0f6',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_562cc678cc0f6')) {function content_562cc678cc0f6($_smarty_tpl) {?>
<?php $_smarty_tpl->tpl_vars["FIELD_INFO"] = new Smarty_variable(Zend_Json::encode($_smarty_tpl->tpl_vars['FIELD_MODEL']->value->getFieldInfo()), null, 0);?><div class="row-fluid"><input type="text" name="<?php echo $_smarty_tpl->tpl_vars['FIELD_MODEL']->value->get('name');?>
" class="span9 listSearchContributor" value="<?php echo $_smarty_tpl->tpl_vars['SEARCH_INFO']->value['searchValue'];?>
" data-fieldinfo='<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['FIELD_INFO']->value, ENT_QUOTES, 'UTF-8', true);?>
'/></div><?php }} ?>