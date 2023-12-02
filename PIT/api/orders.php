<?php 
require_once('database/db_connection.php');
header('Content-Type: application/json');

$postData = file_get_contents('php://input');
$input = json_decode($postData, true);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $userId = $input['user_id'] ?? null;
    $paymentMethod = $input['payment_type'] ?? null;
    $products = $input['products'] ?? [];
    $type = $input['type'] ?? null;

    if($type == 'nota') {
        $nota = $input['nota'] ?? null;
        $orderId = $input['order_id'] ?? null;

        if(trim($nota) === '' || trim($orderId) === '') {
            echo json_encode(['status' => false, 'message' => 'Dados inválidos! Verifique os campos e tente novamente!']);
            exit;
        }

        $stmt = $conn->prepare("UPDATE orders SET nota = ? WHERE order_id = ?");
        $stmt->bind_param("ss", $nota, $orderId);
        $result = $stmt->execute();

        if($result) {
            echo json_encode(['status' => true, 'message' => 'Nota cadastrada com sucesso!']);
        } else {
            echo json_encode(['status' => false, 'message' => 'Erro ao cadastrar nota!']);
        }
        exit;
    }

    if(trim($userId) === '' || trim($paymentMethod) === '' || count($products) === 0) {
        echo json_encode(['status' => false, 'message' => 'Dados inválidos! Verifique os campos e tente novamente!']);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO orders (user_id, payment_type) VALUES (?, ?)");
    $stmt->bind_param("ss", $userId, $paymentMethod);
    $result = $stmt->execute();

    if($result) {
        $orderId = $conn->insert_id;
        $stmt = $conn->prepare("INSERT INTO order_products (order_id, product_id, quantity) VALUES (?, ?, ?)");
        foreach($products as $product) {
            $stmt->bind_param("sss", $orderId, $product['ID'], $product['quantity']);
            $result = $stmt->execute();
        }

        //delete cart
        $stmt = $conn->prepare("DELETE FROM user_cart WHERE user_id = ?");
        $stmt->bind_param("s", $userId);
        $result = $stmt->execute();

        echo json_encode(['status' => true, 'message' => 'Pedido cadastrado com sucesso!']);
    } else {
        echo json_encode(['status' => false, 'message' => 'Erro ao cadastrar pedido!']);
    }
} else if($_SERVER['REQUEST_METHOD'] == 'GET') {
    $userId = $_GET['user'] ?? null;

    if(trim($userId) === '') {
        echo json_encode(['status' => false, 'message' => 'Dados inválidos! Verifique os campos e tente novamente!']);
        exit;
    }

    $stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ?");
    $stmt->bind_param("s", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    $orders = [];
    while($row = $result->fetch_assoc()) {
        $stmt = $conn->prepare("SELECT * FROM order_products WHERE order_id = ?");
        $stmt->bind_param("s", $row['order_id']);
        $stmt->execute();
        $resultProducts = $stmt->get_result();

        $products = [];
        while($rowProduct = $resultProducts->fetch_assoc()) {
            $stmt = $conn->prepare("SELECT * FROM products WHERE ID = ?");
            $stmt->bind_param("s", $rowProduct['product_id']);
            $stmt->execute();
            $resultProduct = $stmt->get_result();
            $product = $resultProduct->fetch_assoc();
            $product['quantity'] = $rowProduct['quantity'];
            $products[] = $product;
        }

        $row['products'] = $products;
        $orders[] = $row;
    }

    echo json_encode(['status' => true, 'message' => 'Pedidos encontrados com sucesso!', 'orders' => $orders]);
} else {
    return json_encode(['status' => false, 'message' => 'Método não suportado!']);
}
?>