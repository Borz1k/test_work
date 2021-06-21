<?
function prr($arr=[]){
	echo "<pre>";
	print_r($arr);
	echo "</pre>";
}

spl_autoload_register(function ($class) {
    include 'lib/'.str_replace("\\", "/", $class) . '.php';
});

/* Подключение к базе MySQL*/
$config_db = [
    'host'=>'localhost',/* Сервер */
    'name'=>'test_db',/* Имя базы*/
    'user'=>'root',/* Пользователь */
    'password'=>'root'/* Пароль */
];

$db  = new DB\MySQL($config_db);
$tpl = new Templates\Tpl();

$tpl->path        = $_SERVER['DOCUMENT_ROOT'].'/templates/'; /* Путь к шаблонам */ 
$tpl->all_header  = "header";
$tpl->all_footer  = "footer";

$Fruit = new Fruits($db);
$is_ajax 		  = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == "XMLHttpRequest");

if($is_ajax){
	$result = ["status"=>"error"];
	$error = false;
	if(!empty($_POST['name']) && !empty($_POST['weight'])){
		$tpl->val = [
			"name" => $_POST['name'],
			"weight" => $_POST['weight']
		];
		if($Fruit->addFruit($tpl->val)>0){
			$result = [
				"status"=>"ok",
				"tpl" 	=>$tpl->fetch("blocks/tr")
			];
		}
	}
	echo json_encode($result);
	die();
}
$tpl->list_fruits = $Fruit->getAllFruit();
$tpl->display("index"); 

