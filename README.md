#yandex-money-cms-modx-evo

Модуль оплаты yandex-money-cms-modx-evo необходим для интеграции с сервисом [Яндекс.Касса](http://kassa.yandex.ru/) на базе CMS ModX и компонента Shopkeeper. 

Доступные платежные методы, если вы работаете как юридическое лицо:
* **Банковские карты** -  Visa (включая Electron), MasterCard и Maestro любого банка мира
* **Электронные деньги** - Яндекс.Деньги, WebMoney и QIWI Wallet
* **Наличные** - [Более 170 тысяч пунктов](https://money.yandex.ru/pay/doc.xml?id=526209) оплаты по России
* **Баланс телефона** - Билайн, МегаФон и МТС
* **Интернет банкинг** - Альфа-Клик, Сбербанк Онлайн, MasterPass и Промсвязьбанк
* **Кредитование** - Доверительный платеж (Куппи.ру)

###Требования к CMS ModX:
* редакция Evolution 1.х
* компонент Shopkeeper 1.х
* компонент FormIt

###Установка модуля
Для установки данного модуля необходимо:
* Распаковать [архив](https://github.com/yandex-money/yandex-money-cms-modx-evo/archive/master.zip) и переместить папку `assets` в папку `assets` Вашего сайта
* Перейти в административную панель CMS
* Перейти в меню `Модули` - `Управление модулями` - `Новый модуль` и создать новый модуль с названием `YandexMoney`
* Вставить в поле `Код модуля` созданного модуля `YandexMoney` следующий код:
```
include '../assets/snippets/yandexmoney/yandexmoney.class.php';

$dbname = $modx->db->config['dbase']; //имя базы данных
$dbprefix = $modx->db->config['table_prefix']; //префикс таблиц
$mod_table = $dbprefix."yandexmoney"; //таблица модуля
$theme = $modx->config['manager_theme']; //тема админки
$basePath = $modx->config['base_path']; //путь до сайта на сервере
 
$action = isset($_POST['action']) ? $_POST['action']:'';

switch($action) {
 
//Установка модуля (создание таблицы в БД)
case 'install':
  $sql = "CREATE TABLE $mod_table (id INT(11) NOT NULL AUTO_INCREMENT, config LONGTEXT, PRIMARY KEY (id))";
  $modx->db->query($sql);

  $sql = "INSERT INTO $mod_table (id, config) VALUES (1, '')";
  $modx->db->query($sql);

  header("Location: $_SERVER[REQUEST_URI]");
break;
 
//Удаление таблицы модуля
case "uninstall":
  $sql = "DROP TABLE $mod_table";
  $modx->db->query($sql);
  header("Location: $_SERVER[REQUEST_URI]");
break;
 
//Обновление записи в БД
case 'save':
  $fields = array(
     'config' => serialize($_POST['config'])
  );
  $ym_stat = new yamoney_statistics($_POST['config']);
  $query = $modx->db->update($fields, $mod_table, 'id = 1'); 
  header("Location: $_SERVER[REQUEST_URI]");
break;
 
 
//Перезагрузка страницы (сброс $_POST)
case 'reload':
  header("Location: $_SERVER[REQUEST_URI]");
break;

//Страница модуля
default:
	echo Yandexmoney::adminHeadHtml($theme);;
	if (!Yandexmoney::isInstalled($dbname, $mod_table)){
		//если таблицы не существует, выводим кнопку "Установить модуль"
		echo Yandexmoney::adminInstallHtml();
	}else{
		$data_query = $modx->db->select("*", $mod_table, "", "id ASC", ""); 
                $row = $modx->db->getRow($data_query);
                $config = unserialize($row['config']);
		
		$ym = new Yandexmoney($config);
		echo $ym->adminModuleHtml();
	}
	break;
} 
```
* Перейти в меню `Элементы` - `Управление элементами` - `Сниппеты` - `Новый сниппет` и создать новый сниппет с названием `YandexMoney`
* Вставить в поле `Код сниппета` созданного сниппета `YandexMoney` следующий код:
```
<?php
$dbname = $modx->db->config['dbase']; //имя базы данных
$dbprefix = $modx->db->config['table_prefix']; //префикс таблиц
$mod_table = $dbprefix."yandexmoney"; //таблица модуля
$theme = $modx->config['manager_theme']; //тема админки
$basePath = $modx->config['base_path']; //путь до сайта на сервере
if(!defined('YANDEXMONEY_PATH')) {
 define('YANDEXMONEY_PATH', MODX_BASE_PATH."assets/snippets/yandexmoney/");
}
require_once YANDEXMONEY_PATH.'yandexmoney.class.php';

$data_query = $modx->db->select("*", $mod_table, "", "id ASC", ""); 
$row = $modx->db->getRow($data_query);
$config = unserialize($row['config']);
$ym = new Yandexmoney($config);
if ($_POST['payment']){
 $ym->pay_method = $_POST['payment'];
}
if ($action == 'showMethods') {
 return $ym->getSelectHtml();
}
?>
```
* Перейти в меню `Элементы` - `Управление файлами` и в файл `assests/snippets/shopkeeper/shopkeeper.inc.php`вставить (после строки `<?php`) следующий код:
```
if(!function_exists('sendOrderToManager')){
	function sendOrderToManager(&$fields){
	  global $modx, $shkconf;
	 
	  if(!class_exists('Shopkeeper')) require_once MODX_BASE_PATH."assets/snippets/shopkeeper/class.shopkeeper.php";
	  if(!defined('YANDEXMONEY_PATH')) define('YANDEXMONEY_PATH', MODX_BASE_PATH."assets/snippets/yandexmoney/");
	  if(!function_exists('YandexMoneyForm')) require_once YANDEXMONEY_PATH.'yandexmoney.class.php';
	  $shopCart = new Shopkeeper($modx, $shkconf);
	  
	  $shopCart->sendOrderToManager($fields);
	  
	  YandexMoneyForm($fields);	

	  return true;
	}
}
```
* Перейти в меню `Элементы` - `Управление элементами` - `Чанки` - `Новый чанк` и создать новый чанк с названием `YandexMoney`
* Вставить в поле `Код чанка` созданного чанка `YandexMoney` следующий код:
```
[[YandexMoney?&action=`showMethods`]]
```
Пример с вставленной строкой `[[YandexMoney?&action=`showMethods`]]`:
```
<select name="payment" eform="payment::::#FUNCTION YandexMoneyValidate">
    <option value="При получении">При получении</option>
    [[YandexMoney? &action=`showMethods`]]
</select>
```
* В коде страницы с формой заказа необходимо вставить следующий код:
```
[[YandexMoney? &action=`beforeCart`]]
```
Пример с вставленной строкой `[[YandexMoney? &action=`beforeCart`]]`:
```
[[YandexMoney? &action=`beforeCart`]]
[!Shopkeeper? &cartType=`full` &priceTV=`price` &orderFormPage=`34` &currency=`руб.` &noJQuery=`1`!]
[!eForm? &formid=`shopOrderForm` &tpl=`shopOrderForm` &report=`shopOrderReport` &vericode=`1` &ccsender=`1` &gotoid=`35` &subject=`Новый заказ` &eFormOnBeforeMailSent=`populateOrderData` &eFormOnMailSent=`sendOrderToManager` !]
```
* Перейти в `Модули` - `Управление модулями` и щелкнуть по значку рядом с `YandexMoney`, а затем выбрать пункт "Установить YandexMoney"

###Лицензионный договор.
Любое использование Вами программы означает полное и безоговорочное принятие Вами условий лицензионного договора, размещенного по адресу https://money.yandex.ru/doc.xml?id=527132 (далее – «Лицензионный договор»). 
Если Вы не принимаете условия Лицензионного договора в полном объёме, Вы не имеете права использовать программу в каких-либо целях.

###Нашли ошибку или у вас есть предложение по улучшению модуля?
Пишите нам cms@yamoney.ru
При обращении необходимо:
* Указать наименование CMS и компонента магазина, а также их версии
* Указать версию платежного модуля (доступна на странице настроек модуля)
* Описать проблему или предложение
* Приложить снимок экрана (для большей информативности)
