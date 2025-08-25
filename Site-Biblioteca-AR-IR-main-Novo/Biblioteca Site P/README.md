# Biblioteca Arco-Íris - Sistema de Gerenciamento

## 📚 Descrição

Sistema completo de gerenciamento de biblioteca desenvolvido em PHP com banco de dados MySQL. Permite controle de usuários, livros, empréstimos, multas, fornecedores e agendamentos.

## 🚀 Funcionalidades

### Para Usuários
- ✅ Cadastro e login de usuários
- ✅ Visualização do catálogo de livros
- ✅ Empréstimo de livros
- ✅ Renovação de empréstimos
- ✅ Devolução de livros
- ✅ Visualização de histórico de empréstimos
- ✅ Pagamento de multas
- ✅ Sistema de doações (itens de higiene)

### Para Administradores
- ✅ Gestão completa de usuários
- ✅ Gestão de livros e autores
- ✅ Controle de empréstimos e devoluções
- ✅ Sistema de multas automático
- ✅ Gestão de fornecedores
- ✅ Agendamentos de livros
- ✅ Relatórios e estatísticas
- ✅ Gráficos de uso da biblioteca

## 📋 Pré-requisitos

- **Servidor Web**: Apache/Nginx
- **PHP**: 8.0 ou superior
- **MySQL**: 5.7 ou superior (ou MariaDB 10.2+)
- **Extensões PHP**:
  - PDO
  - PDO_MySQL
  - JSON
  - Session
  - BCMath (para hash de senhas)

## 🛠️ Instalação

### 1. Configuração do Servidor

1. **Clone ou baixe o projeto** para a pasta do seu servidor web:
   ```bash
   # Se usando XAMPP
   C:\xampp\htdocs\biblioteca-arco-iris\
   
   # Se usando WAMP
   C:\wamp\www\biblioteca-arco-iris\
   
   # Se usando Linux/Apache
   /var/www/html/biblioteca-arco-iris/
   ```

2. **Inicie o servidor web e MySQL**

### 2. Configuração do Banco de Dados

1. **Acesse o phpMyAdmin** (geralmente em `http://localhost/phpmyadmin`)

2. **Crie um novo banco de dados**:
   - Nome: `biblioteca_arco_iris`
   - Collation: `utf8mb4_unicode_ci`

3. **Importe o arquivo SQL**:
   - Vá na aba "Importar"
   - Selecione o arquivo `database.sql`
   - Clique em "Executar"

4. **Verifique se as tabelas foram criadas**:
   - `usuarios`
   - `livros`
   - `autores`
   - `categorias`
   - `emprestimos`
   - `fornecedores`
   - `agendamentos`
   - `doacoes`
   - `multas`
   - `historico_atividades`
   - `configuracoes`

### 3. Configuração da Aplicação

1. **Edite o arquivo de configuração**:
   ```php
   // Arquivo: config/database.php
   
   // Altere estas configurações conforme seu ambiente:
   define('DB_HOST', 'localhost');     // Host do MySQL
   define('DB_NAME', 'biblioteca_arco_iris');  // Nome do banco
   define('DB_USER', 'root');          // Usuário do MySQL
   define('DB_PASS', '');              // Senha do MySQL (vazia para XAMPP padrão)
   ```

2. **Verifique as permissões de pasta** (Linux):
   ```bash
   chmod 755 -R /var/www/html/biblioteca-arco-iris/
   chmod 777 -R /var/www/html/biblioteca-arco-iris/logs/  # Se existir
   ```

### 4. Acesso ao Sistema

1. **Acesse o sistema** no navegador:
   ```
   http://localhost/biblioteca-arco-iris/
   ```

2. **Credenciais padrão do administrador**:
   - **CPF**: 12345678901
   - **Telefone**: (11) 99999-9999
   - **Senha**: 123456

## 📖 Como Usar

### Primeiro Acesso

1. **Faça login como administrador** com as credenciais padrão
2. **Configure as configurações do sistema**:
   - Prazo de empréstimo
   - Limite de empréstimos por usuário
   - Valor da multa diária
   - Horário de funcionamento

### Cadastro de Usuários

1. **Acesse a página de registro** (`registro.php`)
2. **Preencha os dados**:
   - Nome completo
   - CPF (válido)
   - Telefone
   - Senha (mínimo 6 caracteres)
   - Email (opcional)

### Empréstimo de Livros

1. **Faça login** como usuário
2. **Navegue pelo catálogo** de livros
3. **Clique em "Ver mais"** no livro desejado
4. **Clique em "Emprestar livro"**
5. **Confirme o empréstimo**

### Gestão Administrativa

1. **Acesse o painel administrativo** (`inicio-admin.php`)
2. **Gerencie**:
   - Usuários
   - Livros
   - Empréstimos
   - Fornecedores
   - Agendamentos
   - Relatórios

## 🔧 Configurações do Sistema

### Configurações Disponíveis

- **`prazo_emprestimo_dias`**: Prazo padrão para empréstimo (padrão: 7)
- **`limite_emprestimos_usuario`**: Máximo de empréstimos por usuário (padrão: 5)
- **`valor_multa_diaria`**: Valor da multa por dia de atraso (padrão: R$ 2,00)
- **`dias_para_renovacao`**: Dias mínimos para renovar (padrão: 6)
- **`nome_biblioteca`**: Nome da biblioteca
- **`email_contato`**: Email de contato
- **`horario_funcionamento`**: Horários de funcionamento (JSON)

### Como Alterar Configurações

1. **Via código PHP**:
   ```php
   setConfig('prazo_emprestimo_dias', '14', 'Prazo de empréstimo em dias', 'integer');
   ```

2. **Via banco de dados**:
   ```sql
   UPDATE configuracoes 
   SET valor = '14' 
   WHERE chave = 'prazo_emprestimo_dias';
   ```

## 📊 Estrutura do Banco de Dados

### Tabelas Principais

- **`usuarios`**: Dados dos usuários e administradores
- **`livros`**: Catálogo de livros
- **`autores`**: Informações dos autores
- **`categorias`**: Categorias de livros
- **`emprestimos`**: Histórico de empréstimos
- **`fornecedores`**: Fornecedores de livros
- **`agendamentos`**: Agendamentos de livros
- **`doacoes`**: Sistema de doações
- **`multas`**: Controle de multas
- **`historico_atividades`**: Log de atividades
- **`configuracoes`**: Configurações do sistema

### Relacionamentos

- Usuários → Empréstimos (1:N)
- Livros → Empréstimos (1:N)
- Autores → Livros (1:N)
- Categorias → Livros (1:N)
- Empréstimos → Multas (1:N)
- Usuários → Doações (1:N)
- Fornecedores → Doações (1:N)

## 🔒 Segurança

### Medidas Implementadas

- ✅ **Hash de senhas** com bcrypt
- ✅ **Validação de CPF** brasileiro
- ✅ **Sanitização de inputs**
- ✅ **Prepared Statements** para prevenir SQL Injection
- ✅ **Controle de sessão**
- ✅ **Log de atividades**
- ✅ **Validação de permissões**

### Recomendações de Segurança

1. **Altere a senha do administrador** após o primeiro acesso
2. **Configure HTTPS** em produção
3. **Mantenha o PHP atualizado**
4. **Configure firewall** adequadamente
5. **Faça backups regulares** do banco de dados

## 📈 Relatórios e Estatísticas

### Relatórios Disponíveis

- **Livros mais emprestados**
- **Empréstimos por período**
- **Usuários mais ativos**
- **Multas pendentes**
- **Estatísticas gerais**

### Como Gerar Relatórios

1. **Acesse o painel administrativo**
2. **Vá para "Gráficos"**
3. **Visualize as estatísticas** em tempo real
4. **Exporte relatórios** quando necessário

## 🐛 Solução de Problemas

### Problemas Comuns

1. **Erro de conexão com banco**:
   - Verifique as configurações em `config/database.php`
   - Confirme se o MySQL está rodando
   - Verifique usuário e senha do banco

2. **Página não carrega**:
   - Verifique se o servidor web está rodando
   - Confirme se o PHP está instalado
   - Verifique os logs de erro do servidor

3. **Erro de permissão**:
   - Verifique as permissões das pastas
   - Confirme se o usuário do servidor tem acesso

4. **Caracteres especiais**:
   - Verifique se o banco está usando UTF-8
   - Confirme se o PHP está configurado corretamente

### Logs de Erro

- **Apache**: `/var/log/apache2/error.log` (Linux)
- **XAMPP**: `C:\xampp\apache\logs\error.log` (Windows)
- **PHP**: Configure em `php.ini`

## 🔄 Atualizações

### Como Atualizar

1. **Faça backup** do banco de dados
2. **Faça backup** dos arquivos
3. **Substitua os arquivos** pelos novos
4. **Execute scripts de migração** se necessário
5. **Teste o sistema**

### Backup do Banco

```bash
# Backup completo
mysqldump -u root -p biblioteca_arco_iris > backup_$(date +%Y%m%d_%H%M%S).sql

# Restaurar backup
mysql -u root -p biblioteca_arco_iris < backup.sql
```

## 📞 Suporte

### Informações de Contato

- **Email**: suporte@bibliotecaarcoiris.com
- **Documentação**: Este README
- **Issues**: Use o sistema de issues do repositório

### Contribuições

1. **Fork o projeto**
2. **Crie uma branch** para sua feature
3. **Commit suas mudanças**
4. **Push para a branch**
5. **Abra um Pull Request**

## 📄 Licença

Este projeto está sob a licença MIT. Veja o arquivo `LICENSE` para mais detalhes.

## 🙏 Agradecimentos

- Comunidade PHP
- Desenvolvedores do MySQL
- Contribuidores do projeto

---

**Desenvolvido com ❤️ para a Biblioteca Arco-Íris**

*Última atualização: Janeiro 2025*
