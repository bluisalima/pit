<?php 
require_once('database/db_connection.php');
header('Content-Type: application/json');

$postData = file_get_contents('php://input');
$input = json_decode($postData, true);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Receber dados do POST
    $userId = $input['user_id'] ?? null;
    $productId = $input['product_id'] ?? null;
    $type = $input['type'] ?? null;
    $quantity = $input['quantity'] ?? null;

    if(trim($userId) === '' || trim($productId) === '' || trim($type) === '' || trim($quantity) === '') {
        echo json_encode(['status' => false, 'message' => 'Erro interno!']);
        exit;
    }


    switch ($type) {
        case 'add':
            $stmt = $conn->prepare("SELECT * FROM user_cart WHERE user_id = ? AND product_id = ?");
            $stmt->bind_param("ss", $userId, $productId);
            $stmt->execute();
            $result = $stmt->get_result();
            $product = $result->fetch_assoc();

            if($product) {
                $result = null;
                $newQuantity = $quantity + $product['quantity'];
                $stmt = $conn->prepare("UPDATE user_cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
                $stmt->bind_param("sss", $newQuantity, $userId, $productId);
                $result = $stmt->execute();

                if($result) {
                    echo json_encode(['status' => true, 'message' => 'Produto adicionado ao carrinho com sucesso!']);
                } else {
                    echo json_encode(['status' => false, 'message' => 'Erro ao adicionar produto ao carrinho!']);
                }
            } else {
                $stmt = $conn->prepare("INSERT INTO user_cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $userId, $productId, $quantity);
                $result = $stmt->execute();

                if($result) {
                    echo json_encode(['status' => true, 'message' => 'Produto adicionado ao carrinho com sucesso!']);
                } else {
                    echo json_encode(['status' => false, 'message' => 'Erro ao adicionar produto ao carrinho!']);
                }
            }
            break;
        case 'update':
            $stmt = $conn->prepare("UPDATE user_cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
            $stmt->bind_param("sss", $quantity, $userId, $productId);
            $result = $stmt->execute();

            if($result) {
                echo json_encode(['status' => true, 'message' => 'Produto atualizado no carrinho com sucesso!']);
            } else {
                echo json_encode(['status' => false, 'message' => 'Erro ao atualizar produto no carrinho!']);
            }

            break;
        case 'remove':
            $stmt = $conn->prepare("DELETE FROM user_cart WHERE user_id = ? AND product_id = ?");
            $stmt->bind_param("ss", $userId, $productId);
            $result = $stmt->execute();

            if($result) {
                echo json_encode(['status' => true, 'message' => 'Produto removido do carrinho com sucesso!']);
            } else {
                echo json_encode(['status' => false, 'message' => 'Erro ao remover produto do carrinho!']);
            }
            break;
     }

} else {
    return json_encode(['status' => false, 'message' => 'Método não suportado!']);
}
?>