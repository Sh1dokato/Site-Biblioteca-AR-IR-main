-- Script para popular o banco de dados com dados do site
-- Execute este script após criar o banco com biblioteca.sql

USE biblioteca_arco_iris;

-- Inserir usuários extraídos dos arquivos HTML
INSERT INTO usuarios (cpf, telefone, senha, nome, is_admin, ativo, data_cadastro) VALUES 
('11122233344', '11888888888', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'João Silva', 0, 1, '2025-01-15'),
('55566677788', '11777777777', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Pedro Costa', 0, 1, '2025-01-20'),
('99988877766', '11666666666', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Roberta Santos', 0, 0, '2025-01-20');

-- Inserir livros extraídos do arquivo registro.html
INSERT INTO livros (titulo, autor, isbn, ano_publicacao, editora, categoria, quantidade_total, quantidade_disponivel, ativo, data_cadastro) VALUES 
('1984', 'George Orwell', '978-0-452-28423-4', 1949, 'Editora Livros & Cia', 'Ficção Científica', 5, 5, 1, NOW()),
('A Arte da Guerra', 'Sun Tzu', '978-0-486-42504-1', -500, 'Editora Livros & Cia', 'Estratégia', 3, 3, 1, NOW()),
('A Cabana', 'William P. Young', '978-0-8499-0160-4', 2007, 'Editora Livros & Cia', 'Ficção', 4, 4, 1, NOW()),
('A Culpa é das Estrelas', 'John Green', '978-0-525-47881-2', 2012, 'Editora Livros & Cia', 'Romance', 6, 6, 1, NOW()),
('A Menina que Roubava Livros', 'Markus Zusak', '978-0-375-84220-7', 2005, 'Editora Livros & Cia', 'Drama', 2, 2, 1, NOW()),
('A Metamorfose', 'Franz Kafka', '978-0-8052-1048-4', 1915, 'Editora Livros & Cia', 'Ficção', 3, 3, 1, NOW()),
('A Revolução dos Bichos', 'George Orwell', '978-0-452-28424-1', 1945, 'Editora Livros & Cia', 'Fábula Política', 4, 4, 1, NOW()),
('As Crônicas de Nárnia', 'C.S. Lewis', '978-0-06-440537-9', 1950, 'Editora Livros & Cia', 'Fantasia', 5, 5, 1, NOW()),
('Memórias Póstumas de Brás Cubas', 'Machado de Assis', '978-85-06-00001-1', 1881, 'Editora Livros & Cia', 'Romance', 2, 2, 1, NOW()),
('Capitães da Areia', 'Jorge Amado', '978-85-06-00002-2', 1937, 'Editora Livros & Cia', 'Literatura Brasileira', 3, 3, 1, NOW()),
('Cem Anos de Solidão', 'Gabriel García Márquez', '978-0-06-088328-7', 1967, 'Editora Livros & Cia', 'Realismo Mágico', 4, 4, 1, NOW()),
('Dom Casmurro', 'Machado de Assis', '978-85-06-00003-3', 1899, 'Editora Livros & Cia', 'Romance', 2, 2, 1, NOW()),
('Dom Quixote', 'Miguel de Cervantes', '978-0-486-40624-0', 1605, 'Editora Livros & Cia', 'Romance', 3, 3, 1, NOW()),
('Elon Musk', 'Ashlee Vance', '978-0-06-230123-9', 2015, 'Editora Livros & Cia', 'Biografia', 2, 2, 1, NOW()),
('Harry Potter e a Pedra Filosofal', 'J.K. Rowling', '978-0-7475-3269-9', 1997, 'Editora Livros & Cia', 'Fantasia', 5, 5, 1, NOW()),
('Macunaíma', 'Mário de Andrade', '978-85-06-00004-4', 1928, 'Editora Livros & Cia', 'Literatura Brasileira', 3, 3, 1, NOW()),
('O Alquimista', 'Paulo Coelho', '978-0-06-250217-9', 1988, 'Editora Livros & Cia', 'Ficção', 4, 4, 1, NOW()),
('O Código Da Vinci', 'Dan Brown', '978-0-307-47427-8', 2003, 'Editora Livros & Cia', 'Suspense', 3, 3, 1, NOW()),
('O Cortiço', 'Aluísio Azevedo', '978-85-06-00005-5', 1890, 'Editora Livros & Cia', 'Literatura Brasileira', 5, 5, 1, NOW()),
('O Diário de Anne Frank', 'Anne Frank', '978-0-553-29698-6', 1947, 'Editora Livros & Cia', 'Biografia', 3, 3, 1, NOW()),
('O Hobbit', 'J.R.R. Tolkien', '978-0-618-00221-4', 1937, 'Editora Livros & Cia', 'Fantasia', 4, 4, 1, NOW()),
('O Nome do Vento', 'Patrick Rothfuss', '978-0-756-40407-9', 2007, 'Editora Livros & Cia', 'Fantasia', 2, 2, 1, NOW()),
('O Príncipe', 'Nicolau Maquiavel', '978-0-486-27274-5', 1532, 'Editora Livros & Cia', 'Política', 4, 4, 1, NOW()),
('Orgulho e Preconceito', 'Jane Austen', '978-0-486-44093-8', 1813, 'Editora Livros & Cia', 'Romance', 3, 3, 1, NOW()),
('O Senhor dos Anéis', 'J.R.R. Tolkien', '978-0-618-00222-1', 1954, 'Editora Livros & Cia', 'Fantasia', 2, 2, 1, NOW()),
('Percy Jackson e o Ladrão de Raios', 'Rick Riordan', '978-0-7868-5629-9', 2005, 'Editora Livros & Cia', 'Fantasia Jovem', 4, 4, 1, NOW()),
('O Pequeno Príncipe', 'Antoine de Saint-Exupéry', '978-0-15-601398-7', 1943, 'Editora Livros & Cia', 'Literatura Infantil', 5, 5, 1, NOW()),
('Vidas Secas', 'Graciliano Ramos', '978-85-06-00006-6', 1938, 'Editora Livros & Cia', 'Literatura Brasileira', 3, 3, 1, NOW());

-- Inserir fornecedores extraídos dos arquivos HTML
INSERT INTO fornecedores (nome, cnpj_cpf, telefone, email, ativo, data_cadastro) VALUES 
('Editora Livros & Cia', '12.345.678/0001-90', '(47) 3333-4444', 'contato@livroscia.com', 1, '2025-01-15'),
('João Silva', '123.456.789-00', '(47) 99999-8888', 'joao@email.com', 1, '2025-02-10'),
('Roberta Santos', '987.654.321-00', '(47) 88888-7777', 'Roberta@gmail.com', 0, '2025-01-20');

-- Inserir empréstimos de exemplo extraídos dos arquivos HTML
INSERT INTO emprestimos (usuario_id, livro_id, data_emprestimo, data_devolucao_prevista, data_devolucao_real, status, multa_valor, ativo, data_cadastro) VALUES 
(2, 12, '2024-01-15', '2024-02-15', NULL, 'emprestado', 15.50, 1, NOW()),
(2, 17, '2024-01-20', '2024-02-20', NULL, 'emprestado', 0.00, 1, NOW()),
(4, 1, '2024-01-10', '2024-02-10', NULL, 'emprestado', 25.00, 1, NOW());

-- Inserir agendamentos de exemplo
INSERT INTO agendamentos (usuario_id, livro_id, data_agendamento, horario, status, ativo, data_cadastro) VALUES 
(2, 15, '2025-01-25', '14:00:00', 'agendado', 1, NOW()),
(4, 8, '2025-01-26', '16:00:00', 'agendado', 1, NOW());

-- Inserir doações de exemplo
INSERT INTO doacoes (usuario_id, tipo, descricao, valor, status, ativo, data_cadastro) VALUES 
(2, 'livro', 'Doação de livro "Dom Casmurro"', 0.00, 'pendente', 1, NOW()),
(4, 'higiene', 'Doação de itens de higiene', 0.00, 'pendente', 1, NOW()),
(2, 'dinheiro', 'Doação em dinheiro para a biblioteca', 50.00, 'pendente', 1, NOW());

-- Inserir pagamentos de multa de exemplo
INSERT INTO pagamentos_multa (emprestimo_id, valor_pago, metodo_pagamento, data_pagamento, ativo, data_cadastro) VALUES 
(1, 15.50, 'pix', NOW(), 1, NOW());

-- Atualizar quantidade disponível dos livros emprestados
UPDATE livros SET quantidade_disponivel = quantidade_disponivel - 1 WHERE id IN (12, 17, 1);

-- Comentário: Execute este script após criar o banco com biblioteca.sql
-- Este script popula o banco com todos os dados encontrados nos arquivos HTML do site
