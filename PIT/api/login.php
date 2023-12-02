<?php 
require_once('database/db_connection.php');
header('Content-Type: application/json');

$postData = file_get_contents('php://input');
$input = json_decode($postData, true);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Receber dados do POST
    $senha = $input['password'] ?? null;
    $email = $input['email'] ?? null;

    if(trim($email) === '' || trim($senha) === '') {
        echo json_encode(['status' => false, 'message' => 'Dados inválidos! Verifique os campos e tente novamente!']);
        exit;
    }

    $stmt = $conn->prepare("SELECT SENHA FROM users WHERE EMAIL = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $usuario = $resultado->fetch_assoc();

    if($usuario && password_verify($senha, $usuario['SENHA'])) {

        $stmt = $conn->prepare("SELECT * FROM users WHERE EMAIL = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $usuario = $resultado->fetch_assoc();
        unset($usuario['SENHA']);

        echo json_encode(['status' => true, 'message' => 'Usuário logado com sucesso!', 'user' => $usuario]);
    } else {
        echo json_encode(['status' => false, 'message' => 'Usuário ou senha inválidos!']);
    }

} else {
    return json_encode(['status' => false, 'message' => 'Método não suportado!']);
}
?>