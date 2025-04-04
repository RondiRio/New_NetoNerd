<?php
session_start();
include 'bandoDeDados/conexao.php'; // Certifique-se do nome correto da pasta

try {
    // Pegando dados do formulário e sanitizando entrada
    $titulo = str_replace('#', '-', $_POST['titulo']);
    $categoria = str_replace('#', '-', $_POST['categoria']);
    $descricao = str_replace('#', '-', $_POST['descricao']);
    $usuario = str_replace('#', '_', $_POST['usuario']);

    // Obtendo o último protocolo do banco de dados
    $query = "SELECT MAX(protocolo) as ultimo_protocolo FROM chamados";
    $result = $conn->query($query);

    if (!$result) {
        throw new Exception("Erro ao buscar o último protocolo: " . $conn->error);
    }

    $row = $result->fetch_assoc();
    $ultimo_protocolo = isset($row['ultimo_protocolo']) ? intval(substr($row['ultimo_protocolo'], 4)) : 0;
    $novo_protocolo = $ultimo_protocolo + 1;
    $ano_atual = date('Y');
    $protocolo_com_ano = $ano_atual . str_pad($novo_protocolo, 4, '0', STR_PAD_LEFT);

    // Query de inserção
    // $id_tecnico = 0; // Inicializando técnico_id como 0, pois será atribuído depois
    $sql = "INSERT INTO chamados (cliente_id, titulo, categoria, descricao, protocolo, nome_usuario) 
            VALUES (?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception("Erro ao preparar a query: " . $conn->error);
    }

    // Definindo tecnico_id como NULL, pois ele será atribuído depois
    // $tecnico_id = NULL;

    // Ligando os parâmetros
    $stmt->bind_param("isssss", $_SESSION['id'], $titulo, $categoria, $descricao, $protocolo_com_ano, $usuario);

    
    if (!$stmt->execute()) {
        throw new Exception("Erro ao executar a query: " . $stmt->error);
    }

    // Fechar a conexão e liberar a memória
    $stmt->close();
    $conn->close();

    // Redirecionamento após sucesso
    header('Location: abrir_chamado.php');
    exit();

} catch (Exception $e) {
    die("Erro ao registrar chamado: " . $e->getMessage());
}
?>
