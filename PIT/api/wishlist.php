<?php 
require_once('database/db_connection.php');
header('Content-Type: application/json');

$postData = file_get_contents('php://input');
$input = json_decode($postData, true);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Receber dados do POST
    $userId = $input['user_id'] ?? null;
    $productId = $input['product_id'] ?? null;

    if(trim($userId) === '' || trim($productId) === '') {
        echo json_encode(['status' => false, 'message' => 'Erro interno!']);
        exit;
    }

    $stmt = $conn->prepare("SELECT * FROM user_wishlist WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("ss", $userId, $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();

    if($result->num_rows > 0) {
        $stmt = $conn->prepare("DELETE FROM user_wishlist WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("ss", $userId, $productId);
        $result = $stmt->execute();

        if($result) {
            echo json_encode(['status' => true, 'message' => 'Produto removido da lista de desejos com sucesso!']);
        } else {
            echo json_encode(['status' => false, 'message' => 'Erro ao remover produto da lista de desejos!']);
        }
        exit;
    } else {
        $stmt = $conn->prepare("INSERT INTO user_wishlist (user_id, product_id) VALUES (?, ?)");
        $stmt->bind_param("ss", $userId, $productId);
        $result = $stmt->execute();

        if($result) {
            echo json_encode(['status' => true, 'message' => 'Produto adicionado à lista de desejos com sucesso!']);
        } else {
            echo json_encode(['status' => false, 'message' => 'Erro ao adicionar produto à lista de desejos!']);
        }
        exit;
    }

} else {
    return json_encode(['status' => false, 'message' => 'Método não suportado!']);
}
?>