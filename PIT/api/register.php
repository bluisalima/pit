<?php 
require_once('database/db_connection.php');
header('Content-Type: application/json');

$postData = file_get_contents('php://input');
$input = json_decode($postData, true);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Receber dados do POST
    $senha = $input['password'] ?? null;
    $email = $input['email'] ?? null;
    $nome = $input['name'] ?? null;
    $birthday = $input['birthday'] ?? null;
    $cnpj = $input['cnpj'] ?? null;

    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

    if(trim($email) === '' || trim($senha) === '' || trim($nome) === '' || (trim($birthday) === '' && trim($cnpj) === '')) {
        echo json_encode(['status' => false, 'message' => 'Dados inválidos! Verifique os campos e tente novamente!', 'data' => $input]);
        exit;
    }

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows > 0) {
        echo json_encode(['status' => false, 'message' => 'E-mail já cadastrado!']);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO users (EMAIL, SENHA, NOME, DATA_NASCIMENTO, CNPJ) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $email, $senha_hash, $nome, $birthday, $cnpj);
    $result = $stmt->execute();

    if($result) {
        $user = [
            'ID' => $conn->insert_id,
            'email' => $email,
            'nome' => $nome,
            'birthday' => $birthday,
            'cnpj' => $cnpj
        ];
        echo json_encode(['status' => true, 'message' => 'Usuário cadastrado com sucesso!', 'user' => $user]);
    } else {
        echo json_encode(['status' => false, 'message' => 'Erro ao cadastrar usuário!']);
    }


} else {
    return json_encode(['status' => false, 'message' => 'Método não suportado!']);
}
?>