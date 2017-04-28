<?php
require_once '../../../manager/includes/protect.inc.php';
require_once 'yandexmoney.class.php';

define('MODX_MANAGER_PATH', "../../../manager/");
require_once(MODX_MANAGER_PATH . 'includes/config.inc.php');
require_once(MODX_MANAGER_PATH . '/includes/protect.inc.php');
define('MODX_API_MODE', true);
require_once(MODX_MANAGER_PATH.'/includes/document.parser.class.inc.php');


$modx = new DocumentParser;
$modx->db->connect();
$modx->getSettings();
$modx->config['site_url'] = isset($request['site_url']) ? $request['site_url'] : '';

$manager_language = $modx->config['manager_language'];
$charset = $modx->config['modx_charset'];
$dbname = $modx->db->config['dbase'];
$base_dir = $modx->config['rb_base_dir'];
$dbprefix = $modx->db->config['table_prefix'];
$mod_table = $dbprefix."yandexmoney";


$data_query = $modx->db->select("*", $mod_table, "", "id ASC", ""); 
$row = $modx->db->getRow($data_query);
$config = unserialize($row['config']);


$ym = new Yandexmoney($config);

$order_id = $ym->ProcessResult();


if ($order_id){
	$modx->db->update("status = 6", $modx->getFullTableName('manager_shopkeeper'), 'id = ' . (int)$order_id);
}

