# Como Popular o Banco de Dados com Dados do Site

## 📋 Passos para Popular o Banco

### 1. Primeiro, Crie o Banco de Dados
Execute o arquivo `biblioteca.sql` no phpMyAdmin para criar a estrutura do banco:
- Abra o phpMyAdmin
- Crie um novo banco chamado `biblioteca_arco_iris`
- Execute o script `biblioteca.sql`

### 2. Depois, Popule com os Dados do Site
Execute o arquivo `popular_banco.sql` para inserir todos os dados encontrados nos arquivos HTML:

## 📊 Dados que Serão Inseridos

### 👥 Usuários (3 usuários)
- **João Silva** - CPF: 111.222.333-44, Telefone: (11) 8888-8888
- **Pedro Costa** - CPF: 555.666.777-88, Telefone: (11) 7777-7777  
- **Roberta Santos** - CPF: 999.888.777-66, Telefone: (11) 6666-6666

**Senha para todos:** `123456` (hash já incluído)

### 📚 Livros (28 livros)
Todos os livros encontrados no arquivo `registro.html`:
- 1984, A Arte da Guerra, A Cabana, A Culpa é das Estrelas
- A Menina que Roubava Livros, A Metamorfose, A Revolução dos Bichos
- As Crônicas de Nárnia, Memórias Póstumas de Brás Cubas
- Capitães da Areia, Cem Anos de Solidão, Dom Casmurro
- Dom Quixote, Elon Musk, Harry Potter e a Pedra Filosofal
- Macunaíma, O Alquimista, O Código Da Vinci, O Cortiço
- O Diário de Anne Frank, O Hobbit, O Nome do Vento
- O Príncipe, Orgulho e Preconceito, O Senhor dos Anéis
- Percy Jackson e o Ladrão de Raios, O Pequeno Príncipe, Vidas Secas

### 🏢 Fornecedores (3 fornecedores)
- **Editora Livros & Cia** - CNPJ: 12.345.678/0001-90
- **João Silva** - CPF: 123.456.789-00
- **Roberta Santos** - CPF: 987.654.321-00

### 📖 Empréstimos (3 empréstimos ativos)
- João Silva → Dom Casmurro (com multa de R$ 15,50)
- João Silva → O Alquimista (sem multa)
- Roberta Santos → 1984 (com multa de R$ 25,00)

### 📅 Agendamentos (2 agendamentos)
- João Silva → Harry Potter (25/01/2025 às 14:00)
- Roberta Santos → As Crônicas de Nárnia (26/01/2025 às 16:00)

### 🎁 Doações (3 doações pendentes)
- Doação de livro "Dom Casmurro"
- Doação de itens de higiene
- Doação em dinheiro (R$ 50,00)

### 💰 Pagamentos de Multa (1 pagamento)
- Pagamento da multa de R$ 15,50 via PIX

## ⚠️ Importante

1. **Execute primeiro** `biblioteca.sql` para criar a estrutura
2. **Depois execute** `popular_banco.sql` para inserir os dados
3. **Todos os usuários** têm a senha `123456`
4. **O admin** já está criado no `biblioteca.sql` (admin/admin)
5. **Os dados** são baseados no que estava nos arquivos HTML do site

## 🔄 Como Executar

### No phpMyAdmin:
1. Selecione o banco `biblioteca_arco_iris`
2. Vá na aba "SQL"
3. Cole o conteúdo de `biblioteca.sql` e execute
4. Depois cole o conteúdo de `popular_banco.sql` e execute

### Via linha de comando:
```bash
mysql -u root -p biblioteca_arco_iris < biblioteca.sql
mysql -u root -p biblioteca_arco_iris < popular_banco.sql
```

## ✅ Após a Execução

O banco estará completamente populado com:
- ✅ Estrutura das tabelas criada
- ✅ Admin configurado (admin/admin)
- ✅ 3 usuários com dados reais
- ✅ 28 livros com informações completas
- ✅ 3 fornecedores cadastrados
- ✅ 3 empréstimos ativos
- ✅ 2 agendamentos pendentes
- ✅ 3 doações pendentes
- ✅ 1 pagamento de multa registrado

**Agora o sistema está pronto para funcionar com dados reais!** 🎉
