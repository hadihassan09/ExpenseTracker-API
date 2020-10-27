<?php
require_once 'Config.php';

class ExpenseService
{
    private $config;
    private $connection;

    public function __construct()
    {
        $this->config = new Config('localhost', 'root', 'root', 'Expense Tracker');
        $this->connection = $this->config->getConnection();
    }

    public function login($email, $password){
        $sql = "SELECT id, password FROM User WHERE email=?";
        if($stmt = $this->connection->prepare($sql)){
            $stmt->bind_param('s', $email);
            if($stmt->execute()){
                if($stmt->store_result() == true){
                    $stmt->bind_result($id, $hashedPassword);
                    $stmt->fetch();
                    if(password_verify($password, $hashedPassword)){
                        $obj = new stdClass();
                        $obj->success = true;
                        $obj->message = 'Login Success';
                        $obj->token = createJWT([
                           'user_id' => $id,
                           'email' => $email,
                            'exp' => (time() + 3600)
                        ]);
                        $obj->id = $id;
                        $obj->email = $email;
                        echo json_encode($obj);
                    }
                    else{
                        $obj = new stdClass();
                        $obj->success = false;
                        $obj->message = 'Incorrect Credentials';
                        echo json_encode($obj);
                    }
                }else{
                    $obj = new stdClass();
                    $obj->success = false;
                    $obj->message = 'Incorrect Credentials';
                    echo json_encode($obj);
                }
            }else{
                $obj = new stdClass();
                $obj->success = false;
                $obj->message = 'Could Not Login: '.$this->connection->error;
                echo json_encode($obj);
            }
        }else{
            $obj = new stdClass();
            $obj->success = false;
            $obj->message = 'Could Not Login: '.$this->connection->error;
            echo json_encode($obj);
        }
    }


    public function addUser($email, $password){
        $hashedpassword = password_hash(trim($password), PASSWORD_DEFAULT);
        $stmt = $this->connection->prepare("INSERT INTO User (email, password) VALUES (?, ?)");
        $stmt->bind_param('ss', $email, $hashedpassword);
        if($stmt->execute()){
            $obj = new stdClass();
            $obj->success = true;
            $obj->message = 'user created';
            echo json_encode($obj);
        }else{
            $obj = new stdClass();
            $obj->success = false;
            $obj->message = $this->connection->error;
            echo json_encode($obj);
        }
    }


    public function addCategory($name){
        $stmt = $this->connection->prepare("INSERT INTO Category (name) VALUES (?)");
        $stmt->bind_param('s', $name);
        if($stmt->execute()){
            $obj = new stdClass();
            $obj->success = true;
            $obj->message = 'category created';
            echo json_encode($obj);
        }else{
            $obj = new stdClass();
            $obj->success = false;
            $obj->message = $this->connection->error;
            echo json_encode($obj);
        }
    }

    public function addExepnse($itemName, $price, $categoryName, $amount, $userId, $date){
        $sqlquery1="SELECT id FROM Category WHERE name='$categoryName'";
        if($this->connection->query($sqlquery1)){
            $categoryId=($this->connection->query($sqlquery1)->fetch_assoc())['id'];
            $stmt=$this->connection->prepare("INSERT INTO Expense (itemName,price,categoryId,amount,userId,date) VALUES (?,?,?,?,?,?)");
            echo $this->connection->error;
            $stmt->bind_param('siiiis',$itemName,$price,$categoryId,$amount,$userId, $date);
            if($stmt->execute()){
                $obj = new stdClass();
                $obj->success = true;
                $obj->message = 'expense created';
                echo json_encode($obj);
            }else{
                $obj = new stdClass();
                $obj->success = false;
                $obj->message = $this->connection->error;
                echo json_encode($obj);
            }
        }
        else {
            $obj = new stdClass();
            $obj->success = false;
            $obj->message = 'Category Not Found: '.$this->connection->error;
            echo json_encode($obj);
        }

    }

    public function getAllCategories()
    {
        $data = array();
        $sqlQuery = "SELECT * FROM Category";
        $result = $this->connection->query($sqlQuery);
        if ($result) {
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    array_push($data, $row);
                }
            }
            echo json_encode($data);
        } else {
            $obj = new stdClass();
            $obj->success = false;
            $obj->message = 'Could Not Get Categories: ' . $this->connection->error;
            echo json_encode($obj);
        }
    }

    public function getAllExpensesByUser($userId){ //List
        $data = array();
        $sqlQuery = "SELECT e.id, e.itemName, e.date, e.price, e.amount, e.userId, c.name FROM Expense as e, Category as c WHERE e.categoryId=c.id and userId='$userId'";
        $result = $this->connection->query($sqlQuery);
        if($result){
            if($result->num_rows>0){
                while($row = $result->fetch_assoc()){
                    array_push($data, $row);
                }
            }
            echo json_encode($data);
        }
        else{
            $obj = new stdClass();
            $obj->success = false;
            $obj->message = 'Could Not Get Expenses: ' . $this->connection->error;
            echo json_encode($obj);
        }
    }

    public function editExpense($expenseId, $name=null, $amount=null, $price=null, $categoryName=null){
        $flag = false;
        $query = "UPDATE Expense SET ";
        if($name != null){
            $query = $query."itemName='$name' ";
        }
        if($amount != null){
            if($name != null)
                $query= $query.", amount='$amount' ";
            else
                $query=$query."amount='$amount' ";
        }
        if($price != null){
            if($amount != null || $name !=null)
                $query=$query.", price='$price' ";
            else
                $query=$query."price='$price' ";
        }
        if($categoryName != null){
            $categoryId=null;
            $sqlQuery="SELECT id FROM Category WHERE name='$categoryName'";
            if($this->connection->query($sqlQuery)) {
                $categoryId = ($this->connection->query($sqlQuery)->fetch_assoc())['id'];
            }
            else{
                $flag = true;
            }
            if($flag == false){
                if($price != null || $amount != null || $price != null){
                    $query=$query.", categoryId='$categoryId' ";
                }else
                    $query=$query."categoryId='$categoryId' ";
            }
        }
        if($flag==true){
            $obj = new stdClass();
            $obj->success = false;
            $obj->message = 'Category Not Found';
            echo json_encode($obj);
        }
        else{
            $query=$query."WHERE id='$expenseId'";
            if($this->connection->query($query))
            {
                if($this->connection->affected_rows>0){
                    $obj = new stdClass();
                    $obj->success = true;
                    $obj->message = 'Expense Updated';
                    echo json_encode($obj);
                }
                else{
                    $obj = new stdClass();
                    $obj->success = false;
                    $obj->message = 'Could Not Update Expense 1'.$this->connection->error;
                    echo json_encode($obj);
                }
            }
            else{
                $obj = new stdClass();
                $obj->success = false;
                $obj->message = 'Could Not Update Expense: 2'.$this->connection->error;
                echo json_encode($obj);
            }
        }
    }

    public function deleteExpense($id, $userId){
        $sqlQuery = "DELETE FROM Expense WHERE id='$id' and userId='$userId'";
        if($this->connection->query($sqlQuery)){
            if($this->connection->affected_rows>0){
                $obj = new stdClass();
                $obj->success = true;
                $obj->message = 'Expense Deleted';
                echo json_encode($obj);
            }
            else{
                $obj = new stdClass();
                $obj->success = false;
                $obj->message = 'Could Not Delete Expense';
                echo json_encode($obj);
            }
        }
        else{
            $obj = new stdClass();
            $obj->success = false;
            $obj->message = 'Could Not Delete Expense: '.$this->connection->error;
            echo json_encode($obj);
        }
    }


    public function deleteCategory($id){
        $sqlQuery = "DELETE FROM Category WHERE id='$id'";
        if($this->connection->query($sqlQuery)){
            if($this->connection->affected_rows>0){
                $obj = new stdClass();
                $obj->success = true;
                $obj->message = 'Category Deleted';
                echo json_encode($obj);
            }
            else{
                $obj = new stdClass();
                $obj->success = false;
                $obj->message = 'Could Not Delete Category';
                echo json_encode($obj);
            }
        }
        else{
            $obj = new stdClass();
            $obj->success = false;
            $obj->message = 'Could Not Delete Expense: '.$this->connection->error;
            echo json_encode($obj);
        }
    }

    public function getExpenseByCategory($userId, $category){
        $data = array();
        $sqlQuery = "SELECT * FROM Expense as e, Category as c WHERE e.categoryId=c.id and c.name='$category' and userId='$userId'";
        $result = $this->connection->query($sqlQuery);
        if($result){
            if($result->num_rows>0){
                while($row = $result->fetch_assoc()){
                    array_push($data, $row);
                }
            }
            echo json_encode($data);
        }
        else {
            $obj = new stdClass();
            $obj->success = false;
            $obj->message = 'Could Not Get Categories: ' . $this->connection->error;
            echo json_encode($obj);
        }
    }

    public function getExpenseById($userId, $id){
        $data = array();
        $sqlQuery = "SELECT * FROM Expense as e, Category as c WHERE e.categoryId=c.id and e.id='$id' and userId='$userId'";
        $result = $this->connection->query($sqlQuery);
        if($result){
            if($result->num_rows>0){
                while($row = $result->fetch_assoc()){
                    array_push($data, $row);
                }
            }
            echo json_encode($data);
        }
        else {
            $obj = new stdClass();
            $obj->success = false;
            $obj->message = 'Could Not Get Expense: ' . $this->connection->error;
            echo json_encode($obj);
        }
    }

    public function getCategoriesPieChart()
    {
        $data = array();
        $sqlQuery = "SELECT c.name, Count(e.categoryId) as count  FROM Expense as e RIGHT JOIN Category as c ON e.categoryId=c.id GROUP BY c.id";
        $result = $this->connection->query($sqlQuery);
        if ($result) {
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    array_push($data, $row);
                }
            }
            echo json_encode($data);
        } else {
            $obj = new stdClass();
            $obj->success = false;
            $obj->message = 'Could Not Get Categories: ' . $this->connection->error;
            echo json_encode($obj);
        }
    }
}