-- Banco de dados da Biblioteca Arco-Íris
-- Criado para o sistema de gerenciamento de biblioteca

-- Criar banco de dados
CREATE DATABASE IF NOT EXISTS biblioteca_arco_iris;
USE biblioteca_arco_iris;

-- Tabela de usuários
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cpf VARCHAR(11) UNIQUE NOT NULL,
    telefone VARCHAR(11) NOT NULL,
    senha VARCHAR(255) NOT NULL,
    nome VARCHAR(100) NOT NULL,
    tipo_usuario ENUM('usuario', 'admin') DEFAULT 'usuario',
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ativo BOOLEAN DEFAULT TRUE
);

-- Tabela de livros
CREATE TABLE livros (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    autor VARCHAR(100) NOT NULL,
    isbn VARCHAR(13),
    categoria VARCHAR(50),
    quantidade_total INT DEFAULT 1,
    quantidade_disponivel INT DEFAULT 1,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ativo BOOLEAN DEFAULT TRUE
);

-- Tabela de empréstimos
CREATE TABLE emprestimos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    livro_id INT NOT NULL,
    data_emprestimo TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_devolucao_prevista DATE NOT NULL,
    data_devolucao_real TIMESTAMP NULL,
    status ENUM('ativo', 'devolvido', 'atrasado', 'aguardando_devolucao') DEFAULT 'ativo',
    multa DECIMAL(10,2) DEFAULT 0.00,
    multa_paga BOOLEAN DEFAULT FALSE,
    observacoes TEXT,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (livro_id) REFERENCES livros(id)
);

-- Tabela de fornecedores
CREATE TABLE fornecedores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    cnpj VARCHAR(14) UNIQUE NOT NULL,
    telefone VARCHAR(11) NOT NULL,
    email VARCHAR(100),
    endereco TEXT,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ativo BOOLEAN DEFAULT TRUE
);

-- Tabela de agendamentos
CREATE TABLE agendamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    livro_id INT NOT NULL,
    data_agendamento TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_retirada_prevista DATE NOT NULL,
    status ENUM('pendente', 'confirmado', 'cancelado', 'expirado') DEFAULT 'pendente',
    observacoes TEXT,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (livro_id) REFERENCES livros(id)
);

-- Tabela de doações
CREATE TABLE doacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    tipo_doacao ENUM('livro', 'item_higiene', 'dinheiro') NOT NULL,
    descricao TEXT NOT NULL,
    valor DECIMAL(10,2) DEFAULT 0.00,
    status ENUM('pendente', 'aprovada', 'rejeitada') DEFAULT 'pendente',
    data_doacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_aprovacao TIMESTAMP NULL,
    admin_id INT NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (admin_id) REFERENCES usuarios(id)
);

-- Tabela de pagamentos de multa
CREATE TABLE pagamentos_multa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    emprestimo_id INT NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    metodo_pagamento ENUM('pix', 'boleto', 'cartao', 'doacao') NOT NULL,
    status ENUM('pendente', 'confirmado', 'cancelado') DEFAULT 'pendente',
    data_pagamento TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (emprestimo_id) REFERENCES emprestimos(id)
);

-- Inserir dados de exemplo

-- Inserir admin padrão
INSERT INTO usuarios (cpf, telefone, senha, nome, tipo_usuario) VALUES 
('12345678901', '11999999999', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador', 'admin');


-- Inserir livros de exemplo
INSERT INTO livros (titulo, autor, isbn, categoria, quantidade_total, quantidade_disponivel) VALUES 
('1984', 'George Orwell', '9788535909555', 'Ficção', 3, 3),
('O Pequeno Príncipe', 'Antoine de Saint-Exupéry', '9788573027239', 'Infantil', 5, 5),
('Dom Casmurro', 'Machado de Assis', '9788535909555', 'Literatura Brasileira', 2, 2),
('O Alquimista', 'Paulo Coelho', '9788573021527', 'Ficção', 4, 4),
('Harry Potter e a Pedra Filosofal', 'J.K. Rowling', '9788533613379', 'Fantasia', 3, 3),
('O Senhor dos Anéis', 'J.R.R. Tolkien', '9788533613379', 'Fantasia', 2, 2),
('O Código Da Vinci', 'Dan Brown', '9788535909555', 'Suspense', 3, 3),
('Orgulho e Preconceito', 'Jane Austen', '9788535909555', 'Romance', 2, 2),
('O Príncipe', 'Nicolau Maquiavel', '9788535909555', 'Filosofia', 1, 1),
('A Revolução dos Bichos', 'George Orwell', '9788535909555', 'Ficção', 2, 2);

-- Inserir fornecedores de exemplo
INSERT INTO fornecedores (nome, cnpj, telefone, email, endereco) VALUES 
('Editora Livros Ltda', '12345678000199', '11555555555', 'contato@editoralivros.com', 'Rua das Flores, 123 - São Paulo/SP'),
('Distribuidora de Livros', '98765432000188', '11444444444', 'vendas@distribuidora.com', 'Av. Paulista, 1000 - São Paulo/SP'),
('Livraria Central', '11122233000177', '11333333333', 'central@livraria.com', 'Rua Augusta, 500 - São Paulo/SP');

-- Inserir alguns empréstimos de exemplo
INSERT INTO emprestimos (usuario_id, livro_id, data_devolucao_prevista, status) VALUES 
(2, 1, DATE_ADD(CURDATE(), INTERVAL 15 DAY), 'ativo'),
(3, 2, DATE_ADD(CURDATE(), INTERVAL 10 DAY), 'ativo'),
(4, 3, DATE_SUB(CURDATE(), INTERVAL 5 DAY), 'atrasado');

-- Inserir agendamentos de exemplo
INSERT INTO agendamentos (usuario_id, livro_id, data_retirada_prevista, status) VALUES 
(2, 4, DATE_ADD(CURDATE(), INTERVAL 3 DAY), 'pendente'),
(3, 5, DATE_ADD(CURDATE(), INTERVAL 7 DAY), 'confirmado');

-- Inserir doações de exemplo
INSERT INTO doacoes (usuario_id, tipo_doacao, descricao, valor, status) VALUES 
(2, 'livro', 'Doação de livros infantis', 0.00, 'pendente'),
(3, 'item_higiene', 'Doação de álcool em gel', 0.00, 'pendente'),
(4, 'dinheiro', 'Doação em dinheiro para manutenção', 50.00, 'pendente');
