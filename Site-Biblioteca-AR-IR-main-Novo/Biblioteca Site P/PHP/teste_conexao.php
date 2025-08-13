<?php
// Arquivo de teste para verificar conexão com banco
header('Content-Type: text/html; charset=utf-8');

echo "<h1>🔍 Teste de Conexão - Biblioteca Arco-Íris</h1>";

try {
    // Testar conexão
    $host = 'localhost';
    $dbname = 'biblioteca_arco_iris';
    $username = 'root';
    $password = '';

    echo "<h2>📡 Testando conexão...</h2>";
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    echo "✅ <strong>Conexão com banco realizada com sucesso!</strong><br>";
    echo "📊 Banco: $dbname<br>";
    echo "👤 Usuário: $username<br>";
    
    // Testar tabelas
    echo "<h2>📋 Verificando tabelas...</h2>";
    
    $tabelas = ['usuarios', 'livros', 'emprestimos', 'fornecedores', 'agendamentos', 'doacoes', 'pagamentos_multa'];
    
    foreach ($tabelas as $tabela) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM $tabela");
            $resultado = $stmt->fetch();
            echo "✅ Tabela <strong>$tabela</strong>: " . $resultado['total'] . " registros<br>";
        } catch (Exception $e) {
            echo "❌ Erro na tabela <strong>$tabela</strong>: " . $e->getMessage() . "<br>";
        }
    }
    
    // Testar usuário admin
    echo "<h2>👑 Verificando usuário admin...</h2>";
    
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE is_admin = 1 AND ativo = 1");
    $stmt->execute();
    $admin = $stmt->fetch();
    
    if ($admin) {
        echo "✅ <strong>Usuário admin encontrado!</strong><br>";
        echo "👤 Nome: " . $admin['nome'] . "<br>";
        echo "🆔 CPF: " . $admin['cpf'] . "<br>";
        echo "📱 Telefone: " . $admin['telefone'] . "<br>";
        echo "🔐 Senha hash: " . substr($admin['senha'], 0, 20) . "...<br>";
    } else {
        echo "❌ <strong>Usuário admin NÃO encontrado!</strong><br>";
    }
    
    // Testar livros
    echo "<h2>📚 Verificando livros...</h2>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM livros WHERE ativo = 1");
    $resultado = $stmt->fetch();
    echo "✅ Total de livros ativos: <strong>" . $resultado['total'] . "</strong><br>";
    
    // Mostrar alguns livros
    $stmt = $pdo->query("SELECT titulo, autor, categoria FROM livros WHERE ativo = 1 LIMIT 5");
    $livros = $stmt->fetchAll();
    
    if ($livros) {
        echo "<h3>📖 Primeiros 5 livros:</h3>";
        echo "<ul>";
        foreach ($livros as $livro) {
            echo "<li><strong>" . $livro['titulo'] . "</strong> - " . $livro['autor'] . " (" . $livro['categoria'] . ")</li>";
        }
        echo "</ul>";
    }
    
    echo "<h2>🎉 Sistema funcionando perfeitamente!</h2>";
    echo "<p><strong>Próximos passos:</strong></p>";
    echo "<ol>";
    echo "<li>Teste o login como admin (CPF: 11111111111, Senha: 123456)</li>";
    echo "<li>Teste o registro de novos usuários</li>";
    echo "<li>Teste o login dos usuários registrados</li>";
    echo "</ol>";
    
} catch (PDOException $e) {
    echo "❌ <strong>ERRO na conexão:</strong> " . $e->getMessage() . "<br>";
    echo "<h3>🔧 Possíveis soluções:</h3>";
    echo "<ul>";
    echo "<li>Verifique se o XAMPP está rodando</li>";
    echo "<li>Verifique se o MySQL está ativo</li>";
    echo "<li>Execute o arquivo <code>biblioteca_completo.sql</code> no phpMyAdmin</li>";
    echo "<li>Verifique se o banco <code>$dbname</code> existe</li>";
    echo "</ul>";
} catch (Exception $e) {
    echo "❌ <strong>ERRO geral:</strong> " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<p><em>Teste realizado em: " . date('d/m/Y H:i:s') . "</em></p>";
?>
