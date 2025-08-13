# Biblioteca Arco-Íris - Sistema Completo

## 🚀 Como Usar

### 1. Execute o Script SQL
- Abra o **phpMyAdmin**
- Execute o arquivo **`biblioteca_completo.sql`**
- **Pronto!** O sistema está funcionando com dados reais

## 📊 O que está incluído:

- ✅ **Banco de dados** criado automaticamente
- ✅ **7 tabelas** com estrutura completa
- ✅ **1 usuário admin** (CPF: 11111111111, Senha: 123456)
- ✅ **28 livros** com informações completas
- ✅ **3 fornecedores** cadastrados
- ✅ **Sistema pronto** para criar usuários, empréstimos, agendamentos e doações

## 🔑 Credenciais de Acesso:

**ADMIN:**
- CPF: `11111111111`
- Senha: `123456`

**USUÁRIOS:**
- Serão criados através do sistema de registro

## 📁 Arquivos PHP:

- `config.php` - Conexão com banco
- `auth.php` - Login/Registro/Logout
- `livros.php` - Gestão de livros
- `emprestimos.php` - Gestão de empréstimos
- `fornecedores.php` - Gestão de fornecedores
- `agendamentos.php` - Gestão de agendamentos
- `doacoes.php` - Gestão de doações
- `usuarios.php` - Gestão de usuários
- `relatorios.php` - Relatórios e estatísticas

## 🚨 **IMPORTANTE - Problemas Corrigidos:**

✅ **Senha do admin agora está hasheada corretamente**
✅ **Campo `email` adicionado à tabela de usuários**
✅ **CPF do admin corrigido para formato válido**
✅ **Campo `tipo_usuario` corrigido para usar `is_admin`**
✅ **Sistema de login funcionando corretamente**

## 🔧 **Como Testar:**

1. **Execute o `biblioteca_completo.sql` no phpMyAdmin**
2. **Faça login como admin:**
   - CPF: `11111111111`
   - Senha: `123456`
3. **Teste o registro de usuários**
4. **Teste o login dos usuários registrados**

**Dica:** Execute apenas o `biblioteca_completo.sql` e tudo funcionará automaticamente! 🎉
