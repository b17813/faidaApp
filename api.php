<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$host = "localhost";
$db_name = "faida_db"; 
$username = "root";    
$password = "";        

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) { echo json_encode(["status" => "error"]); exit; }

$action = $_GET['action'] ?? '';

if ($action == 'register') {
    $user = $_POST['username'];
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    try { $stmt->execute([$user, $pass]); echo json_encode(["status" => "success"]); } 
    catch(Exception $e) { echo json_encode(["status" => "error", "message" => "Name Taken"]); }
}

if ($action == 'login') {
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$_POST['username']]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row && password_verify($_POST['password'], $row['password'])) {
        echo json_encode(["status" => "success", "user_id" => $row['id'], "username" => $row['username']]);
    } else { echo json_encode(["status" => "error", "message" => "Wrong Login"]); }
}

if ($action == 'save') {
    if (!empty($_POST['id'])) {
        $stmt = $conn->prepare("UPDATE transactions SET note=?, qty=?, price=? WHERE id=?");
        $stmt->execute([$_POST['note'], $_POST['qty'], $_POST['price'], $_POST['id']]);
    } else {
        $stmt = $conn->prepare("INSERT INTO transactions (user_id, type, note, qty, price) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$_POST['user_id'], $_POST['type'], $_POST['note'], $_POST['qty'], $_POST['price']]);
    }
    echo json_encode(["status" => "success"]);
}

if ($action == 'fetch') {
    $stmt = $conn->prepare("SELECT * FROM transactions WHERE user_id = ? ORDER BY id DESC");
    $stmt->execute([$_GET['user_id']]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
}

if ($action == 'delete') {
    $stmt = $conn->prepare("DELETE FROM transactions WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    echo json_encode(["status" => "success"]);
}
?>