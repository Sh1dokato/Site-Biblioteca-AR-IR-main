# Sistema de Biblioteca Arco-Íris - Backend PHP

Este é o backend completo do sistema de gerenciamento de biblioteca, desenvolvido em PHP com MySQL.

## 📋 Requisitos

- PHP 7.4 ou superior
- MySQL 5.7 ou superior
- Servidor web (Apache/Nginx)
- Extensões PHP: PDO, PDO_MySQL

## 🚀 Instalação

### 1. Configuração do Banco de Dados

1. Abra o phpMyAdmin ou seu cliente MySQL preferido
2. Copie e cole o conteúdo do arquivo `biblioteca.sql` no console SQL
3. Execute o script para criar o banco de dados e as tabelas

### 2. Configuração do PHP

1. Edite o arquivo `config.php` e ajuste as configurações de conexão:
   ```php
   $host = 'localhost';
   $dbname = 'biblioteca_arco_iris';
   $username = 'root';  // Seu usuário MySQL
   $password = '';      // Sua senha MySQL
   ```

### 3. Estrutura de Arquivos

```
PHP/
├── config.php          # Configurações do banco de dados
├── auth.php            # Autenticação (login/registro)
├── livros.php          # Gerenciamento de livros
├── emprestimos.php     # Gerenciamento de empréstimos
├── fornecedores.php    # Gerenciamento de fornecedores
├── agendamentos.php    # Gerenciamento de agendamentos
├── doacoes.php         # Gerenciamento de doações
├── usuarios.php        # Gerenciamento de usuários
├── relatorios.php      # Relatórios e estatísticas
├── biblioteca.sql      # Script do banco de dados
└── README.md           # Este arquivo
```

## 🔐 Autenticação

### Login de Administrador Padrão
- **CPF:** 12345678901
- **Telefone:** 11999999999
- **Senha:** password

### Login de Usuários de Exemplo
- **CPF:** 11122233344 | **Telefone:** 11888888888 | **Senha:** password
- **CPF:** 55566677788 | **Telefone:** 11777777777 | **Senha:** password
- **CPF:** 99988877766 | **Telefone:** 11666666666 | **Senha:** password

## 📚 Funcionalidades

### 1. Autenticação (`auth.php`)
- **POST** `acao=login` - Fazer login
- **POST** `acao=registrar` - Registrar novo usuário
- **POST** `acao=logout` - Fazer logout

### 2. Livros (`livros.php`)
- **GET** `acao=listar` - Listar todos os livros
- **POST** `acao=adicionar` - Adicionar novo livro
- **POST** `acao=editar` - Editar livro existente
- **POST** `acao=excluir` - Excluir livro
- **GET** `acao=buscar&termo=...` - Buscar livros

### 3. Empréstimos (`emprestimos.php`)
- **POST** `acao=criar` - Criar novo empréstimo
- **GET** `acao=listar` - Listar todos os empréstimos (admin)
- **GET** `acao=listar_usuario` - Listar empréstimos do usuário
- **POST** `acao=devolver` - Devolver livro
- **POST** `acao=pagar_multa` - Pagar multa
- **POST** `acao=calcular_multa` - Calcular multa

### 4. Fornecedores (`fornecedores.php`)
- **GET** `acao=listar` - Listar fornecedores
- **POST** `acao=adicionar` - Adicionar fornecedor
- **POST** `acao=editar` - Editar fornecedor
- **POST** `acao=excluir` - Excluir fornecedor

### 5. Agendamentos (`agendamentos.php`)
- **POST** `acao=criar` - Criar agendamento
- **GET** `acao=listar` - Listar agendamentos (admin)
- **GET** `acao=listar_usuario` - Listar agendamentos do usuário
- **POST** `acao=confirmar` - Confirmar agendamento (admin)
- **POST** `acao=cancelar` - Cancelar agendamento

### 6. Doações (`doacoes.php`)
- **POST** `acao=criar` - Criar doação
- **GET** `acao=listar` - Listar doações (admin)
- **GET** `acao=listar_usuario` - Listar doações do usuário
- **POST** `acao=aprovar` - Aprovar doação (admin)
- **POST** `acao=rejeitar` - Rejeitar doação (admin)

### 7. Usuários (`usuarios.php`)
- **GET** `acao=listar` - Listar usuários (admin)
- **POST** `acao=editar` - Editar usuário (admin)
- **POST** `acao=excluir` - Excluir usuário (admin)
- **POST** `acao=alterar_senha` - Alterar senha
- **GET** `acao=perfil` - Obter perfil do usuário
- **POST** `acao=atualizar_perfil` - Atualizar perfil

### 8. Relatórios (`relatorios.php`)
- **GET** `acao=estatisticas_gerais` - Estatísticas gerais
- **GET** `acao=livros_mais_emprestados` - Livros mais emprestados
- **GET** `acao=usuarios_mais_ativos` - Usuários mais ativos
- **GET** `acao=emprestimos_por_periodo` - Empréstimos por período
- **GET** `acao=multas_por_periodo` - Multas por período
- **GET** `acao=doacoes_por_periodo` - Doações por período

## 🔧 Exemplos de Uso

### Login
```javascript
fetch('PHP/auth.php', {
    method: 'POST',
    body: new FormData({
        acao: 'login',
        cpf: '12345678901',
        telefone: '11999999999',
        senha: 'password'
    })
})
.then(response => response.json())
.then(data => console.log(data));
```

### Listar Livros
```javascript
fetch('PHP/livros.php?acao=listar')
.then(response => response.json())
.then(data => console.log(data));
```

### Criar Empréstimo
```javascript
fetch('PHP/emprestimos.php', {
    method: 'POST',
    body: new FormData({
        acao: 'criar',
        livro_id: '1',
        dias: '15'
    })
})
.then(response => response.json())
.then(data => console.log(data));
```

## 🛡️ Segurança

- Todas as senhas são hasheadas com `password_hash()`
- Validação de entrada em todos os campos
- Proteção contra SQL Injection usando prepared statements
- Verificação de permissões de administrador
- CORS configurado para permitir requisições do frontend

## 📊 Banco de Dados

O sistema utiliza as seguintes tabelas:
- `usuarios` - Usuários do sistema
- `livros` - Acervo da biblioteca
- `emprestimos` - Registro de empréstimos
- `fornecedores` - Fornecedores de livros
- `agendamentos` - Agendamentos de retirada
- `doacoes` - Doações recebidas
- `pagamentos_multa` - Pagamentos de multas

## 🐛 Solução de Problemas

### Erro de Conexão com Banco
- Verifique se o MySQL está rodando
- Confirme as credenciais no `config.php`
- Certifique-se de que o banco `biblioteca_arco_iris` existe

### Erro de Permissão
- Verifique se o usuário MySQL tem permissões adequadas
- Confirme se as tabelas foram criadas corretamente

### Erro de Sessão
- Certifique-se de que as sessões PHP estão habilitadas
- Verifique se o diretório de sessões tem permissões de escrita

## 📝 Notas

- O sistema usa "soft delete" (marca como inativo em vez de excluir)
- Multas são calculadas automaticamente (R$ 0,50 por dia de atraso)
- Doações de livros são automaticamente adicionadas ao acervo quando aprovadas
- O sistema suporta diferentes tipos de doação: livros, itens de higiene e dinheiro

## 🤝 Suporte

Para dúvidas ou problemas, consulte a documentação ou entre em contato com o desenvolvedor.
