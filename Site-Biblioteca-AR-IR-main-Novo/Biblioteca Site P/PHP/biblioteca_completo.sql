-- =====================================================
-- BIBLIOTECA ARCO-ÍRIS - SCRIPT COMPLETO FUNCIONAL
-- =====================================================

-- 1️⃣ Criar e usar o banco de dados
CREATE DATABASE IF NOT EXISTS biblioteca_arco_iris;
USE biblioteca_arco_iris;

-- Desativa checagem de chave estrangeira temporariamente
SET FOREIGN_KEY_CHECKS = 0;

-- =====================================================
-- 2️⃣ CRIAR TABELAS
-- =====================================================

CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cpf VARCHAR(14) UNIQUE NOT NULL,
    telefone VARCHAR(15) NOT NULL,
    senha VARCHAR(255) NOT NULL,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    is_admin BOOLEAN DEFAULT FALSE,
    ativo BOOLEAN DEFAULT TRUE,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS livros (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    autor VARCHAR(100) NOT NULL,
    isbn VARCHAR(20) UNIQUE,
    ano_publicacao INT,
    editora VARCHAR(100),
    categoria VARCHAR(50),
    quantidade_total INT DEFAULT 1,
    quantidade_disponivel INT DEFAULT 1,
    ativo BOOLEAN DEFAULT TRUE,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS emprestimos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    livro_id INT NOT NULL,
    data_emprestimo TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_devolucao_prevista TIMESTAMP NOT NULL,
    data_devolucao_real TIMESTAMP NULL,
    status ENUM('emprestado', 'devolvido', 'atrasado', 'aguardando_devolucao') DEFAULT 'emprestado',
    multa_valor DECIMAL(10,2) DEFAULT 0.00,
    ativo BOOLEAN DEFAULT TRUE,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (livro_id) REFERENCES livros(id)
);

CREATE TABLE IF NOT EXISTS fornecedores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    cnpj_cpf VARCHAR(18) UNIQUE NOT NULL,
    telefone VARCHAR(15),
    email VARCHAR(100),
    ativo BOOLEAN DEFAULT TRUE,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS agendamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    livro_id INT NOT NULL,
    data_agendamento TIMESTAMP NOT NULL,
    horario TIME NOT NULL,
    status ENUM('agendado', 'concluido', 'cancelado') DEFAULT 'agendado',
    ativo BOOLEAN DEFAULT TRUE,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (livro_id) REFERENCES livros(id)
);

CREATE TABLE IF NOT EXISTS doacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    tipo ENUM('livro', 'higiene', 'dinheiro') NOT NULL,
    descricao TEXT,
    valor DECIMAL(10,2) DEFAULT 0.00,
    status ENUM('pendente', 'aprovada', 'rejeitada') DEFAULT 'pendente',
    ativo BOOLEAN DEFAULT TRUE,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

CREATE TABLE IF NOT EXISTS pagamentos_multa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    emprestimo_id INT NOT NULL,
    valor_pago DECIMAL(10,2) NOT NULL,
    metodo_pagamento ENUM('pix', 'boleto', 'cartao', 'dinheiro') NOT NULL,
    data_pagamento TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ativo BOOLEAN DEFAULT TRUE,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (emprestimo_id) REFERENCES emprestimos(id)
);

-- Reativa checagem de FK
SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================
-- 3️⃣ INSERIR DADOS INICIAIS
-- =====================================================

-- Usuário administrador (senha: 123456)
INSERT INTO usuarios (cpf, telefone, senha, nome, email, is_admin, ativo) VALUES 
('11111111111', '11111111111', '$2y$10$e0NRcSDglUxvP.TG.fFqge0QY3bb5Z7A1HsbH6cRYYW0g50/4lZVq', 'Administrador', 'admin@biblioteca.com', 1, 1);

-- Inserir 28 livros
INSERT INTO livros (titulo, autor, isbn, ano_publicacao, editora, categoria, quantidade_total, quantidade_disponivel) VALUES
('1984', 'George Orwell', '978-0-452-28423-4', 1949, 'Editora Livros & Cia', 'Ficção Científica', 5, 5),
('A Arte da Guerra', 'Sun Tzu', '978-0-486-42504-1', -500, 'Editora Livros & Cia', 'Estratégia', 3, 3),
('A Cabana', 'William P. Young', '978-0-8499-0160-4', 2007, 'Editora Livros & Cia', 'Ficção', 4, 4),
('A Culpa é das Estrelas', 'John Green', '978-0-525-47881-2', 2012, 'Editora Livros & Cia', 'Romance', 6, 6),
('A Menina que Roubava Livros', 'Markus Zusak', '978-0-375-84220-7', 2005, 'Editora Livros & Cia', 'Drama', 2, 2),
('A Metamorfose', 'Franz Kafka', '978-0-8052-1048-4', 1915, 'Editora Livros & Cia', 'Ficção', 3, 3),
('A Revolução dos Bichos', 'George Orwell', '978-0-452-28424-1', 1945, 'Editora Livros & Cia', 'Fábula Política', 4, 4),
('As Crônicas de Nárnia', 'C.S. Lewis', '978-0-06-440537-9', 1950, 'Editora Livros & Cia', 'Fantasia', 5, 5),
('Memórias Póstumas de Brás Cubas', 'Machado de Assis', '978-85-06-00001-1', 1881, 'Editora Livros & Cia', 'Romance', 2, 2),
('Capitães da Areia', 'Jorge Amado', '978-85-06-00002-2', 1937, 'Editora Livros & Cia', 'Literatura Brasileira', 3, 3),
('Cem Anos de Solidão', 'Gabriel García Márquez', '978-0-06-088328-7', 1967, 'Editora Livros & Cia', 'Realismo Mágico', 4, 4),
('Dom Casmurro', 'Machado de Assis', '978-85-06-00003-3', 1899, 'Editora Livros & Cia', 'Romance', 2, 2),
('Dom Quixote', 'Miguel de Cervantes', '978-0-486-40624-0', 1605, 'Editora Livros & Cia', 'Romance', 3, 3),
('Elon Musk', 'Ashlee Vance', '978-0-06-230123-9', 2015, 'Editora Livros & Cia', 'Biografia', 2, 2),
('Harry Potter e a Pedra Filosofal', 'J.K. Rowling', '978-0-7475-3269-9', 1997, 'Editora Livros & Cia', 'Fantasia', 5, 5),
('Macunaíma', 'Mário de Andrade', '978-85-06-00004-4', 1928, 'Editora Livros & Cia', 'Literatura Brasileira', 3, 3),
('O Alquimista', 'Paulo Coelho', '978-0-06-250217-9', 1988, 'Editora Livros & Cia', 'Ficção', 4, 4),
('O Código Da Vinci', 'Dan Brown', '978-0-307-47427-8', 2003, 'Editora Livros & Cia', 'Suspense', 3, 3),
('O Cortiço', 'Aluísio Azevedo', '978-85-06-00005-5', 1890, 'Editora Livros & Cia', 'Literatura Brasileira', 5, 5),
('O Diário de Anne Frank', 'Anne Frank', '978-0-553-29698-6', 1947, 'Editora Livros & Cia', 'Biografia', 3, 3),
('O Hobbit', 'J.R.R. Tolkien', '978-0-618-00221-4', 1937, 'Editora Livros & Cia', 'Fantasia', 4, 4),
('O Nome do Vento', 'Patrick Rothfuss', '978-0-756-40407-9', 2007, 'Editora Livros & Cia', 'Fantasia', 2, 2),
('O Príncipe', 'Nicolau Maquiavel', '978-0-486-27274-5', 1532, 'Editora Livros & Cia', 'Política', 4, 4),
('Orgulho e Preconceito', 'Jane Austen', '978-0-486-44093-8', 1813, 'Editora Livros & Cia', 'Romance', 3, 3),
('O Senhor dos Anéis', 'J.R.R. Tolkien', '978-0-618-00222-1', 1954, 'Editora Livros & Cia', 'Fantasia', 2, 2),
('Percy Jackson e o Ladrão de Raios', 'Rick Riordan', '978-0-7868-5629-9', 2005, 'Editora Livros & Cia', 'Fantasia Jovem', 4, 4),
('O Pequeno Príncipe', 'Antoine de Saint-Exupéry', '978-0-15-601398-7', 1943, 'Editora Livros & Cia', 'Literatura Infantil', 5, 5),
('Vidas Secas', 'Graciliano Ramos', '978-85-06-00006-6', 1938, 'Editora Livros & Cia', 'Literatura Brasileira', 3, 3);

-- Inserir fornecedores
INSERT INTO fornecedores (nome, cnpj_cpf, telefone, email, ativo) VALUES 
('Editora Livros & Cia', '12.345.678/0001-90', '(47) 3333-4444', 'contato@livroscia.com', 1),
('João Silva', '123.456.789-00', '(47) 99999-8888', 'joao@email.com', 1),
('Roberta Santos', '987.654.321-00', '(47) 88888-7777', 'Roberta@gmail.com', 1);
