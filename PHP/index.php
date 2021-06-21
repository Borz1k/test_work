<?
function prr($arr=[]){
	echo "<pre>";
	print_r($arr);
	echo "</pre>";
}

spl_autoload_register(function ($class) {
    include str_replace("\\", "/", $class) . '.php';
});

/* Подключение к базе MySQL*/
$config_db = [
    'host'=>'localhost',/* Сервер */
    'name'=>'test_db',/* Имя базы*/
    'user'=>'root',/* Пользователь */
    'password'=>'root'/* Пароль */
];

$db = new DB\MySQL($config_db);

$res1 = $db->Select("fruits", "", "weight>150");
$res2 = $db->Select("books LEFT JOIN authors ON (books.author_id=authors.id)", "books.*, authors.author_name");
$res3 = $db->Select("books LEFT JOIN authors ON (books.author_id=authors.id)", "authors.author_name, COUNT(books.id) AS _books_count", "", "GROUP BY books.author_id");

prr($res1);
prr($res2);
prr($res3);

$Book = new Books($db);
$Book->setAuthor(1);
echo "Количество книг: ".$Book->authorBooks();

$Fruit = new Fruits($db);
if($idNewFruit = $Fruit->addFruit(["name"=>"mango", "weight"=>179])){
	echo "id нового фрукта: ".$idNewFruit;
}else{
	echo "Не удалось добавить фруктыы";
}

//prr($db->log);
