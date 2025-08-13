<?php
require_once 'config.php';

// Permitir CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$acao = $_POST['acao'] ?? $_GET['acao'] ?? '';

switch ($acao) {
    case 'listar':
        listarLivros();
        break;
    case 'adicionar':
        adicionarLivro();
        break;
    case 'editar':
        editarLivro();
        break;
    case 'excluir':
        excluirLivro();
        break;
    case 'buscar':
        buscarLivros();
        break;
    default:
        respostaJson(false, 'Ação não reconhecida');
}

function listarLivros() {
    global $pdo;
    
    try {
        $stmt = $pdo->query("SELECT * FROM livros WHERE ativo = 1 ORDER BY titulo");
        $livros = $stmt->fetchAll();
        
        respostaJson(true, 'Livros listados com sucesso', $livros);
    } catch (PDOException $e) {
        respostaJson(false, 'Erro ao listar livros: ' . $e->getMessage());
    }
}

function adicionarLivro() {
    global $pdo;
    
    $titulo = limparDados($_POST['titulo'] ?? '');
    $autor = limparDados($_POST['autor'] ?? '');
    $isbn = limparDados($_POST['isbn'] ?? '');
    $categoria = limparDados($_POST['categoria'] ?? '');
    $quantidade = intval($_POST['quantidade'] ?? 1);
    
    if (empty($titulo) || empty($autor)) {
        respostaJson(false, 'Título e autor são obrigatórios');
    }
    
    if ($quantidade < 1) {
        respostaJson(false, 'Quantidade deve ser maior que zero');
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO livros (titulo, autor, isbn, categoria, quantidade_total, quantidade_disponivel) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$titulo, $autor, $isbn, $categoria, $quantidade, $quantidade]);
        
        respostaJson(true, 'Livro adicionado com sucesso');
    } catch (PDOException $e) {
        respostaJson(false, 'Erro ao adicionar livro: ' . $e->getMessage());
    }
}

function editarLivro() {
    global $pdo;
    
    $id = intval($_POST['id'] ?? 0);
    $titulo = limparDados($_POST['titulo'] ?? '');
    $autor = limparDados($_POST['autor'] ?? '');
    $isbn = limparDados($_POST['isbn'] ?? '');
    $categoria = limparDados($_POST['categoria'] ?? '');
    $quantidade = intval($_POST['quantidade'] ?? 1);
    
    if ($id <= 0) {
        respostaJson(false, 'ID do livro inválido');
    }
    
    if (empty($titulo) || empty($autor)) {
        respostaJson(false, 'Título e autor são obrigatórios');
    }
    
    try {
        // Verificar se o livro existe
        $stmt = $pdo->prepare("SELECT quantidade_total, quantidade_disponivel FROM livros WHERE id = ? AND ativo = 1");
        $stmt->execute([$id]);
        $livro = $stmt->fetch();
        
        if (!$livro) {
            respostaJson(false, 'Livro não encontrado');
        }
        
        // Calcular nova quantidade disponível
        $emprestados = $livro['quantidade_total'] - $livro['quantidade_disponivel'];
        $nova_disponivel = max(0, $quantidade - $emprestados);
        
        $stmt = $pdo->prepare("UPDATE livros SET titulo = ?, autor = ?, isbn = ?, categoria = ?, quantidade_total = ?, quantidade_disponivel = ? WHERE id = ?");
        $stmt->execute([$titulo, $autor, $isbn, $categoria, $quantidade, $nova_disponivel, $id]);
        
        respostaJson(true, 'Livro atualizado com sucesso');
    } catch (PDOException $e) {
        respostaJson(false, 'Erro ao atualizar livro: ' . $e->getMessage());
    }
}

function excluirLivro() {
    global $pdo;
    
    $id = intval($_POST['id'] ?? 0);
    
    if ($id <= 0) {
        respostaJson(false, 'ID do livro inválido');
    }
    
    try {
        // Verificar se há empréstimos ativos
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM emprestimos WHERE livro_id = ? AND status IN ('ativo', 'atrasado')");
        $stmt->execute([$id]);
        $emprestimos = $stmt->fetch();
        
        if ($emprestimos['total'] > 0) {
            respostaJson(false, 'Não é possível excluir um livro com empréstimos ativos');
        }
        
        // Marcar como inativo (soft delete)
        $stmt = $pdo->prepare("UPDATE livros SET ativo = 0 WHERE id = ?");
        $stmt->execute([$id]);
        
        respostaJson(true, 'Livro excluído com sucesso');
    } catch (PDOException $e) {
        respostaJson(false, 'Erro ao excluir livro: ' . $e->getMessage());
    }
}

function buscarLivros() {
    global $pdo;
    
    $termo = limparDados($_GET['termo'] ?? '');
    
    if (empty($termo)) {
        listarLivros();
        return;
    }
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM livros WHERE ativo = 1 AND (titulo LIKE ? OR autor LIKE ? OR categoria LIKE ?) ORDER BY titulo");
        $termo_busca = "%$termo%";
        $stmt->execute([$termo_busca, $termo_busca, $termo_busca]);
        $livros = $stmt->fetchAll();
        
        respostaJson(true, 'Busca realizada com sucesso', $livros);
    } catch (PDOException $e) {
        respostaJson(false, 'Erro na busca: ' . $e->getMessage());
    }
}
?>
