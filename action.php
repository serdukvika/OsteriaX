<?php
include ("dbconnect.php");
if (!isset($_SESSION)) {
	session_start();
}

function out($count) {
	global $conn; // делаем переменную $conn глобальной
	$arr_out = array ();
	try {
		if (!$result = $conn->query("SELECT GBookTable.username, GBookTable.date, GBookTable.message, users.log FROM GBookTable JOIN users ON GBookTable.user_id=users.user_id ORDER BY date DESC LIMIT " . $count)) // выбор $count записей из БД, отсортированных так, что самая последняя отправленная запись будет всегда первой.
			throw new Exception('Ошибка создания таблицы: [' . $conn->error . ']');
		while ($row = $result->fetch_assoc()) // каждую запись отправляем в массив.
{
			$arr_out [] = $row;
		}
	}
	catch (Exception $e) {
		echo $e->getMessage();
	}
	return $arr_out;
}

function check_autorize($log, $pas) {
	global $conn; // делаем переменную $conn глобальной
	$sql = "SELECT log FROM Users WHERE log = '" . $log . "' AND pas='" . $pas . "';";
	// отправляем запрос к БД
	if ($result = $conn->query($sql)) {
		$n = $result->num_rows; // число строк в ответе на запрос
		if ($n != 0) {
			$_SESSION ['user_login'] = $log; // регистрируем переменную login
			return true;
		}
		else {
			return false;
		}
	}
}

function check_log($log) {
	global $conn; // делаем переменную $conn глобальной
	try {
		$sql = "SELECT log FROM Users WHERE log = '" . $log . "'";
		$result = $conn->query($sql);
		$n = $result->num_rows; // число строк в ответе на запрос
		if ($n != 0) {
			return true;
		}
		else {
			return false;
		}
	}
	catch (Exception $e) {
		$e->getMessage();
	}
}

function registration($log, $pas) {
	global $conn; // делаем переменную $conn глобальной
	$sql = "INSERT INTO Users (log, pas) VALUES (" . "'" . $log . "', " . "'" . $pas . "')";
	// отправляем запрос к БД
	if (!$conn->query($sql)) {
		return false;
	}
	else {
		$_SESSION ['user_login'] = $log; // регистрируем переменную login
		return true;
	}
}

if (isset($_REQUEST ['action'])) { // проверка действия пользователя (передан ли параметр action)
	$action = $_REQUEST ['action'];
	switch ($action) {
		case 'add' :
			add();
			break; // если action=add, выполнять функцию add()
		case 'logout' :
			logout();
			break; // если action=logout, выполнять функцию logout()
		default :
			header("Location: index.php"); // по умолчанию - перенаправление на страницу index.php
	}
}
// else
// header("Location: index.php"); // если не выбрана action - перенаправление на страницу index.php
function get_id_active_user(){
	global $conn;
	$log = $_SESSION ['user_login'];
	$user_id = false;
	try {
		$sql = "SELECT * FROM Users WHERE log = '" . $log . "'";
		$result = $conn->query($sql);
		if(!$result){
			throw new Exception('Ошибка заполнения  таблицы GBookTable: [' . $conn->error . ']');
		}
		$row = $result->fetch_assoc();
		$user_id = $row['user_id'];
	}
	catch (Exception $e) {
		$e->getMessage();
	}
	return $user_id;
}

function add() { // добавление записи
	global $conn; // делаем переменную $conn глобальной
	              // получаем переменные из формы
	$username = $_REQUEST ['username'];
	$message = $_REQUEST ['message'];
	$active_user_id = get_id_active_user();
	// добавление данных в БД
	try {
		if (!$conn->query("INSERT INTO GBookTable(username, date, message, user_id) VALUES ('$username', NOW(), '$message', '$active_user_id')"))
			throw new Exception('Ошибка заполнения  таблицы GBookTable: [' . $conn->error . ']');
		$_SESSION ['add'] = true;
		header("Location: admin_panel.php");
	}
	catch (Exception $e) {
		echo $e->getMessage();
	}
}

function logout() { // выход из аккаунта
	unset($_SESSION ['login']);
	unset($_SESSION ['pas']);
	unset($_SESSION ['add']);
	session_unset();
	header("Location: index.php");
}
?>