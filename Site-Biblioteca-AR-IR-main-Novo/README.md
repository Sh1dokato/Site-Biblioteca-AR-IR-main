# 📚 Biblioteca Arco-Íris - Sistema de Gerenciamento

## 📋 Descrição

Sistema completo de gerenciamento de biblioteca desenvolvido em PHP e MySQL, com interface moderna e funcionalidades avançadas para controle de empréstimos, usuários, livros e relatórios.

## ✨ Funcionalidades

### 👤 Para Usuários:
- **Cadastro e Login**: Sistema de autenticação seguro
- **Catálogo de Livros**: Visualização de todos os livros disponíveis
- **Empréstimos**: Solicitar, renovar e devolver livros
- **Histórico**: Acompanhar empréstimos ativos e histórico completo
- **Perfil**: Gerenciar dados pessoais e senha
- **Sistema de Multas**: Pagamento de multas por atraso

### 🔧 Para Administradores:
- **Dashboard Completo**: Estatísticas em tempo real
- **Gestão de Usuários**: Cadastro, edição e controle de acesso
- **Gestão de Livros**: Adicionar, editar e remover livros do acervo
- **Gestão de Fornecedores**: Controle de parceiros e doações
- **Agendamentos**: Sistema de reservas de livros
- **Relatórios**: Gráficos e relatórios detalhados
- **Configurações**: Personalizar parâmetros do sistema

## 🛠️ Pré-requisitos

- **Servidor Web**: Apache/Nginx
- **PHP**: 8.0 ou superior
- **MySQL**: 5.7 ou superior (ou MariaDB 10.2+)
- **Extensões PHP**: PDO, PDO_MySQL, JSON, mbstring

## 📦 Instalação

### 1. **Configuração do Servidor**

Certifique-se de que seu servidor web está configurado e funcionando.

### 2. **Configuração do Banco de Dados**

1. Acesse o phpMyAdmin ou seu cliente MySQL preferido
2. Execute o arquivo `database.sql` completo
3. O script irá:
   - Criar o banco de dados `biblioteca_arco_iris`
   - Criar todas as tabelas necessárias
   - Inserir dados de exemplo
   - Configurar triggers, views e procedures

### 3. **Configuração da Aplicação**

1. Edite o arquivo `config/database.php`:
   ```php
   define('DB_HOST', 'localhost');     // Host do banco
   define('DB_NAME', 'biblioteca_arco_iris');  // Nome do banco
   define('DB_USER', 'root');          // Usuário do banco
   define('DB_PASS', '');              // Senha do banco
   ```

2. Certifique-se de que as permissões de escrita estão corretas para:
   - Pasta `IMG/` (para upload de imagens)
   - Pasta `config/` (para logs)

### 4. **Acesso ao Sistema**

- **URL**: `http://localhost/biblioteca-arco-iris/`
- **Admin**: 
  - CPF: `12345678901`
  - Telefone: `(11) 99999-9999`
  - Senha: `123456`

## 🚀 Como Usar

### **Primeiro Acesso (Administrador)**

1. Faça login com as credenciais de administrador
2. Acesse o painel administrativo
3. Configure as configurações básicas do sistema
4. Adicione usuários e livros conforme necessário

### **Usuários Comuns**

1. Acesse a página de registro
2. Crie sua conta com CPF, telefone e senha
3. Faça login no sistema
4. Explore o catálogo de livros
5. Solicite empréstimos

## ⚙️ Configurações do Sistema

O sistema possui configurações flexíveis que podem ser alteradas:

- **Prazo de Empréstimo**: 7 dias (padrão)
- **Limite de Empréstimos**: 5 livros por usuário
- **Valor da Multa**: R$ 0,25 por dia de atraso
- **Dias para Renovação**: 6 dias após o empréstimo

## 🗄️ Estrutura do Banco de Dados

### **Tabelas Principais:**

- **`usuarios`**: Dados dos usuários e administradores
- **`livros`**: Catálogo completo de livros
- **`autores`**: Informações dos autores
- **`categorias`**: Categorias de livros
- **`emprestimos`**: Histórico de empréstimos
- **`fornecedores`**: Fornecedores e parceiros
- **`agendamentos`**: Sistema de reservas
- **`doacoes`**: Controle de doações
- **`multas`**: Sistema de multas
- **`historico_atividades`**: Log de atividades
- **`configuracoes`**: Configurações do sistema

### **Views Úteis:**

- **`vw_livros_mais_emprestados`**: Ranking de livros populares
- **`vw_emprestimos_atrasados`**: Empréstimos em atraso
- **`vw_estatisticas_gerais`**: Estatísticas gerais

### **Procedures:**

- **`RenovarEmprestimo`**: Renovação automática
- **`CalcularMultas`**: Cálculo automático de multas

## 🔒 Segurança

- **Hash de Senhas**: Bcrypt com custo 12
- **Validação de CPF**: Algoritmo oficial brasileiro
- **Sanitização de Inputs**: Proteção contra XSS
- **Prepared Statements**: Proteção contra SQL Injection
- **Controle de Sessão**: Gerenciamento seguro de sessões
- **Log de Atividades**: Rastreamento completo de ações

## 📊 Relatórios Disponíveis

- **Empréstimos por Período**
- **Livros Mais Emprestados**
- **Usuários Mais Ativos**
- **Multas e Pagamentos**
- **Estatísticas Gerais**

## 🐛 Solução de Problemas

### **Erro de Conexão com Banco**
- Verifique as credenciais em `config/database.php`
- Certifique-se de que o MySQL está rodando
- Verifique se o banco `biblioteca_arco_iris` existe

### **Erro de Permissão**
- Verifique as permissões das pastas `IMG/` e `config/`
- Certifique-se de que o servidor web tem permissão de escrita

### **Páginas não Carregam**
- Verifique se o mod_rewrite está habilitado (Apache)
- Verifique se o PHP está configurado corretamente

### **Imagens não Aparecem**
- Verifique se as imagens estão na pasta `IMG/`
- Verifique as permissões da pasta de imagens

## 📝 Logs e Monitoramento

O sistema registra automaticamente:
- Logins e logouts
- Empréstimos e devoluções
- Alterações de dados
- Tentativas de acesso inválidas

## 🔄 Atualizações

Para atualizar o sistema:
1. Faça backup do banco de dados
2. Substitua os arquivos PHP
3. Execute scripts de migração se necessário
4. Teste todas as funcionalidades

## 📞 Suporte

Para suporte técnico:
- **Email**: suporte@bibliotecaarcoiris.com
- **Documentação**: Consulte este README
- **Issues**: Reporte problemas no repositório

## 📄 Licença

Este projeto é de uso livre para fins educacionais e comerciais.

---

**Desenvolvido com ❤️ para a Biblioteca Arco-Íris**

