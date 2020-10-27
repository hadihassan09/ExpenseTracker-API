<?php
require_once 'jwtToken.php';
require_once 'Services/Expense.php';

$expenseService = new ExpenseService();

header('Content-Type: application/json; charset=UTF-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

$action = null;

$method = $_SERVER['REQUEST_METHOD'];

if(isset($_GET['action'])){
    $action = $_GET['action'];
}
else{
    echo  json_encode('Cannot Get');
}
if($method == 'POST' && $action == 'createUser'){
    $jsonBody = file_get_contents('php://input');
    $json = json_decode($jsonBody, true);
    $expenseService->addUser($json['email'], $json['password']);
}else if($method== 'POST' && $action == 'login'){
    $jsonBody = file_get_contents('php://input');
    $json = json_decode($jsonBody, true);
    $expenseService->login($json['email'], $json['password']);
}else {
    if(isset($_SERVER['HTTP_AUTHORIZATION'])){
        $token = $_SERVER['HTTP_AUTHORIZATION'];
        if(($user=verifyJWT($token)) != false) {
            if ($method == 'POST') {
                $jsonBody = file_get_contents('php://input');
                $json = json_decode($jsonBody, true);
                switch ($action) {
                    case 'addExpense':
                        $date = str_replace("T", " ", str_replace("-", ":", $json['date']));
                        $expenseService->addExepnse($json['itemName'], $json['price'], $json['category'], $json['amount'], $user->user_id, $date);
                        break;
                    case 'deleteExpense':
                        $expenseService->deleteExpense($json['expenseId'], $user->user_id);
                        break;
                    case 'editExpense':
                        $expenseService->editExpense($json['expenseId'], $json['itemName'], $json['amount'], $json['price'], $json['category']);
                        break;
                    case 'addCategory':
                        $expenseService->addCategory($json['categoryName']);
                        break;
                    case 'deleteCategory':
                        $expenseService->deleteCategory($json['categoryId']);
                        break;
                }
            }

            if ($method == 'GET') {
                switch ($action) {
                    case 'getExpenses':
                        $expenseService->getAllExpensesByUser($user->user_id);
                        break;
                    case 'getCategories':
                        $expenseService->getAllCategories();
                        break;
                    case 'getAllCategoriesCount':
                        $expenseService->getCategoriesPieChart();
                        break;
                    case 'checkToken':
                        $obj = new stdClass();
                        $obj->success = true;
                        $obj->message='Valid Token';
                        echo json_encode($obj);
                        break;
                }
            }
        }
        else{
            $obj = new stdClass();
            $obj->success = false;
            $obj->Auth = false;
            $obj->message='Invalid Token';
            echo json_encode($obj);
        }
    }
    else{
        $obj = new stdClass();
        $obj->success = false;
        $obj->Auth = false;
        $obj->message='Invalid Token';
        echo json_encode($obj);
    }
}


//$expenseService->addUser("Hadi@hadi","1234");
//$expenseService->addCategory('Hard Disk');
//$expenseService->addExepnse('TOSHIPA',1200, 'Computers', 1, 1, '2020:08:08 00:00:00');
//$expenseService->getAllCategories();
//$expenseService->getAllExpensesByUser(1);
//$expenseService->getCategoriesPieChart();
// $expenseService->editExpense(1, null, 12, null, 'Computers');
//$expenseService->deleteExpense(2);
// $expenseService->login("Hadi@hadi","1234");