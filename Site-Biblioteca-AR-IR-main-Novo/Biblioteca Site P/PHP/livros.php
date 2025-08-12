<?php
require_once 'config.php';

header('Content-Type: application/json');

// Função para listar livros
function listarLivros($filtros = []) {
    try {
        $pdo = conectarDB();
        
        $where = "WHERE 1=1";
        $params = [];
        
        // Filtros
        if (!empty($filtros['busca'])) {
            $where .= " AND (l.titulo LIKE ? OR l.autor LIKE ? OR l.categoria LIKE ?)";
            $busca = "%{$filtros['busca']}%";
            $params[] = $busca;
            $params[] = $busca;
            $params[] = $busca;
        }
        
        if (!empty($filtros['categoria'])) {
            $where .= " AND l.categoria = ?";
            $params[] = $filtros['categoria'];
        }
        
        if (!empty($filtros['status'])) {
            $where .= " AND l.status = ?";
            $params[] = $filtros['status'];
        }
        
        // Paginação
        $pagina = $filtros['pagina'] ?? 1;
        $por_pagina = $filtros['por_pagina'] ?? 20;
        $offset = ($pagina - 1) * $por_pagina;
        
        // Contar total
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM livros l $where");
        $stmt->execute($params);
        $total = $stmt->fetch()['total'];
        
        // Buscar livros
        $stmt = $pdo->prepare("
            SELECT 
                l.*,
                COALESCE(COUNT(e.id), 0) as total_emprestimos
            FROM livros l
            LEFT JOIN emprestimos e ON l.id = e.livro_id
            $where
            GROUP BY l.id
            ORDER BY l.titulo
            LIMIT ? OFFSET ?
        ");
        
        $params[] = $por_pagina;
        $params[] = $offset;
        $stmt->execute($params);
        $livros = $stmt->fetchAll();
        
        return [
            'success' => true,
            'livros' => $livros,
            'total' => $total,
            'pagina' => $pagina,
            'por_pagina' => $por_pagina,
            'total_paginas' => ceil($total / $por_pagina)
        ];
        
    } catch (Exception $e) {
        error_log("Erro ao listar livros: " . $e->getMessage());
        return ['success' => false, 'message' => 'Erro interno do servidor'];
    }
}

// Função para buscar livro por ID
function buscarLivro($id) {
    try {
        $pdo = conectarDB();
        
        $stmt = $pdo->prepare("
            SELECT 
                l.*,
                COALESCE(COUNT(e.id), 0) as total_emprestimos
            FROM livros l
            LEFT JOIN emprestimos e ON l.id = e.livro_id
            WHERE l.id = ?
            GROUP BY l.id
        ");
        
        $stmt->execute([$id]);
        $livro = $stmt->fetch();
        
        if (!$livro) {
            return ['success' => false, 'message' => 'Livro não encontrado'];
        }
        
        return ['success' => true, 'livro' => $livro];
        
    } catch (Exception $e) {
        error_log("Erro ao buscar livro: " . $e->getMessage());
        return ['success' => false, 'message' => 'Erro interno do servidor'];
    }
}

// Função para adicionar livro
function adicionarLivro($dados) {
    try {
        verificarAdmin(); // Apenas admins podem adicionar livros
        
        $pdo = conectarDB();
        
        // Validar dados obrigatórios
        if (empty($dados['titulo']) || empty($dados['autor'])) {
            return ['success' => false, 'message' => 'Título e autor são obrigatórios'];
        }
        
        // Verificar se ISBN já existe (se fornecido)
        if (!empty($dados['isbn'])) {
            $stmt = $pdo->prepare("SELECT id FROM livros WHERE isbn = ?");
            $stmt->execute([$dados['isbn']]);
            if ($stmt->fetch()) {
                return ['success' => false, 'message' => 'ISBN já cadastrado'];
            }
        }
        
        // Processar imagem se fornecida
        $imagem = null;
        if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
            $imagem = processarImagem($_FILES['imagem']);
            if (!$imagem['success']) {
                return $imagem;
            }
            $imagem = $imagem['caminho'];
        }
        
        // Inserir livro
        $stmt = $pdo->prepare("
            INSERT INTO livros (
                titulo, autor, isbn, ano_publicacao, paginas, categoria, 
                descricao, imagem, estoque, disponiveis, preco, editora, idioma, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $dados['titulo'],
            $dados['autor'],
            $dados['isbn'] ?? null,
            $dados['ano_publicacao'] ?? null,
            $dados['paginas'] ?? null,
            $dados['categoria'] ?? null,
            $dados['descricao'] ?? null,
            $imagem,
            $dados['estoque'] ?? 0,
            $dados['estoque'] ?? 0, // disponiveis = estoque inicialmente
            $dados['preco'] ?? null,
            $dados['editora'] ?? null,
            $dados['idioma'] ?? 'Português',
            $dados['status'] ?? 'Disponível'
        ]);
        
        $livro_id = $pdo->lastInsertId();
        
        // Log da atividade
        logAtividade($_SESSION['usuario_id'], 'Adicionar livro', "Livro '{$dados['titulo']}' adicionado");
        
        return [
            'success' => true, 
            'message' => 'Livro adicionado com sucesso',
            'livro_id' => $livro_id
        ];
        
    } catch (Exception $e) {
        error_log("Erro ao adicionar livro: " . $e->getMessage());
        return ['success' => false, 'message' => 'Erro interno do servidor'];
    }
}

// Função para editar livro
function editarLivro($id, $dados) {
    try {
        verificarAdmin(); // Apenas admins podem editar livros
        
        $pdo = conectarDB();
        
        // Verificar se livro existe
        $stmt = $pdo->prepare("SELECT id FROM livros WHERE id = ?");
        $stmt->execute([$id]);
        if (!$stmt->fetch()) {
            return ['success' => false, 'message' => 'Livro não encontrado'];
        }
        
        // Validar dados obrigatórios
        if (empty($dados['titulo']) || empty($dados['autor'])) {
            return ['success' => false, 'message' => 'Título e autor são obrigatórios'];
        }
        
        // Verificar se ISBN já existe (se alterado)
        if (!empty($dados['isbn'])) {
            $stmt = $pdo->prepare("SELECT id FROM livros WHERE isbn = ? AND id != ?");
            $stmt->execute([$dados['isbn'], $id]);
            if ($stmt->fetch()) {
                return ['success' => false, 'message' => 'ISBN já cadastrado em outro livro'];
            }
        }
        
        // Processar nova imagem se fornecida
        $imagem = null;
        if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
            $imagem_result = processarImagem($_FILES['imagem']);
            if (!$imagem_result['success']) {
                return $imagem_result;
            }
            $imagem = $imagem_result['caminho'];
        }
        
        // Preparar query de atualização
        $campos = [];
        $params = [];
        
        $campos[] = "titulo = ?";
        $params[] = $dados['titulo'];
        
        $campos[] = "autor = ?";
        $params[] = $dados['autor'];
        
        if (isset($dados['isbn'])) {
            $campos[] = "isbn = ?";
            $params[] = $dados['isbn'];
        }
        
        if (isset($dados['ano_publicacao'])) {
            $campos[] = "ano_publicacao = ?";
            $params[] = $dados['ano_publicacao'];
        }
        
        if (isset($dados['paginas'])) {
            $campos[] = "paginas = ?";
            $params[] = $dados['paginas'];
        }
        
        if (isset($dados['categoria'])) {
            $campos[] = "categoria = ?";
            $params[] = $dados['categoria'];
        }
        
        if (isset($dados['descricao'])) {
            $campos[] = "descricao = ?";
            $params[] = $dados['descricao'];
        }
        
        if ($imagem) {
            $campos[] = "imagem = ?";
            $params[] = $imagem;
        }
        
        if (isset($dados['estoque'])) {
            $campos[] = "estoque = ?";
            $params[] = $dados['estoque'];
            
            // Ajustar disponíveis se estoque foi reduzido
            $stmt = $pdo->prepare("SELECT disponiveis FROM livros WHERE id = ?");
            $stmt->execute([$id]);
            $disponiveis_atual = $stmt->fetch()['disponiveis'];
            
            if ($dados['estoque'] < $disponiveis_atual) {
                $campos[] = "disponiveis = ?";
                $params[] = $dados['estoque'];
            }
        }
        
        if (isset($dados['preco'])) {
            $campos[] = "preco = ?";
            $params[] = $dados['preco'];
        }
        
        if (isset($dados['editora'])) {
            $campos[] = "editora = ?";
            $params[] = $dados['editora'];
        }
        
        if (isset($dados['idioma'])) {
            $campos[] = "idioma = ?";
            $params[] = $dados['idioma'];
        }
        
        if (isset($dados['status'])) {
            $campos[] = "status = ?";
            $params[] = $dados['status'];
        }
        
        $campos[] = "updated_at = NOW()";
        
        $params[] = $id;
        
        $sql = "UPDATE livros SET " . implode(", ", $campos) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        // Log da atividade
        logAtividade($_SESSION['usuario_id'], 'Editar livro', "Livro ID $id editado");
        
        return ['success' => true, 'message' => 'Livro atualizado com sucesso'];
        
    } catch (Exception $e) {
        error_log("Erro ao editar livro: " . $e->getMessage());
        return ['success' => false, 'message' => 'Erro interno do servidor'];
    }
}

// Função para excluir livro
function excluirLivro($id) {
    try {
        verificarAdmin(); // Apenas admins podem excluir livros
        
        $pdo = conectarDB();
        
        // Verificar se livro existe
        $stmt = $pdo->prepare("SELECT titulo, disponiveis FROM livros WHERE id = ?");
        $stmt->execute([$id]);
        $livro = $stmt->fetch();
        
        if (!$livro) {
            return ['success' => false, 'message' => 'Livro não encontrado'];
        }
        
        // Verificar se há empréstimos ativos
        if ($livro['disponiveis'] < $livro['estoque']) {
            return ['success' => false, 'message' => 'Não é possível excluir livro com empréstimos ativos'];
        }
        
        // Excluir livro
        $stmt = $pdo->prepare("DELETE FROM livros WHERE id = ?");
        $stmt->execute([$id]);
        
        // Log da atividade
        logAtividade($_SESSION['usuario_id'], 'Excluir livro', "Livro '{$livro['titulo']}' excluído");
        
        return ['success' => true, 'message' => 'Livro excluído com sucesso'];
        
    } catch (Exception $e) {
        error_log("Erro ao excluir livro: " . $e->getMessage());
        return ['success' => false, 'message' => 'Erro interno do servidor'];
    }
}

// Função para processar imagem
function processarImagem($arquivo) {
    $extensoes_permitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $tamanho_maximo = 5 * 1024 * 1024; // 5MB
    
    // Verificar extensão
    $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
    if (!in_array($extensao, $extensoes_permitidas)) {
        return ['success' => false, 'message' => 'Tipo de arquivo não permitido'];
    }
    
    // Verificar tamanho
    if ($arquivo['size'] > $tamanho_maximo) {
        return ['success' => false, 'message' => 'Arquivo muito grande (máximo 5MB)'];
    }
    
    // Gerar nome único
    $nome_arquivo = uniqid() . '.' . $extensao;
    $caminho_destino = UPLOAD_PATH . $nome_arquivo;
    
    // Mover arquivo
    if (!move_uploaded_file($arquivo['tmp_name'], $caminho_destino)) {
        return ['success' => false, 'message' => 'Erro ao salvar arquivo'];
    }
    
    return ['success' => true, 'caminho' => 'IMG/' . $nome_arquivo];
}

// Função para buscar categorias
function buscarCategorias() {
    try {
        $pdo = conectarDB();
        
        $stmt = $pdo->prepare("SELECT DISTINCT categoria FROM livros WHERE categoria IS NOT NULL AND categoria != '' ORDER BY categoria");
        $stmt->execute();
        $categorias = $stmt->fetchAll();
        
        return ['success' => true, 'categorias' => array_column($categorias, 'categoria')];
        
    } catch (Exception $e) {
        error_log("Erro ao buscar categorias: " . $e->getMessage());
        return ['success' => false, 'message' => 'Erro interno do servidor'];
    }
}

// Função para buscar livros populares
function buscarLivrosPopulares($limite = 10) {
    try {
        $pdo = conectarDB();
        
        $stmt = $pdo->prepare("
            SELECT 
                l.id,
                l.titulo,
                l.autor,
                l.imagem,
                l.disponiveis,
                COUNT(e.id) as total_emprestimos
            FROM livros l
            LEFT JOIN emprestimos e ON l.id = e.livro_id
            GROUP BY l.id
            ORDER BY total_emprestimos DESC, l.titulo
            LIMIT ?
        ");
        
        $stmt->execute([$limite]);
        $livros = $stmt->fetchAll();
        
        return ['success' => true, 'livros' => $livros];
        
    } catch (Exception $e) {
        error_log("Erro ao buscar livros populares: " . $e->getMessage());
        return ['success' => false, 'message' => 'Erro interno do servidor'];
    }
}

// Processar requisições
$acao = $_POST['acao'] ?? $_GET['acao'] ?? '';

switch ($acao) {
    case 'listar':
        $resultado = listarLivros($_GET);
        break;
        
    case 'buscar':
        $id = $_GET['id'] ?? $_POST['id'] ?? 0;
        $resultado = buscarLivro($id);
        break;
        
    case 'adicionar':
        $resultado = adicionarLivro($_POST);
        break;
        
    case 'editar':
        $id = $_POST['id'] ?? 0;
        $resultado = editarLivro($id, $_POST);
        break;
        
    case 'excluir':
        $id = $_POST['id'] ?? $_GET['id'] ?? 0;
        $resultado = excluirLivro($id);
        break;
        
    case 'categorias':
        $resultado = buscarCategorias();
        break;
        
    case 'populares':
        $limite = $_GET['limite'] ?? 10;
        $resultado = buscarLivrosPopulares($limite);
        break;
        
    default:
        $resultado = ['success' => false, 'message' => 'Ação não especificada'];
}

echo json_encode($resultado);
?>
