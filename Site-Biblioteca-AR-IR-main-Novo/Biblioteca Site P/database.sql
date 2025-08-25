-- =====================================================
-- BANCO DE DADOS - BIBLIOTECA ARCO-ÍRIS
-- =====================================================

-- Criar banco de dados
CREATE DATABASE IF NOT EXISTS biblioteca_arco_iris
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE biblioteca_arco_iris;

-- =====================================================
-- TABELA DE USUÁRIOS
-- =====================================================
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    cpf VARCHAR(14) UNIQUE NOT NULL,
    telefone VARCHAR(15) NOT NULL,
    senha VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    is_admin BOOLEAN DEFAULT FALSE,
    ativo BOOLEAN DEFAULT TRUE,
    tem_debito BOOLEAN DEFAULT FALSE,
    tem_doacao_pendente BOOLEAN DEFAULT FALSE,
    total_emprestimos INT DEFAULT 0,
    total_devolvidos INT DEFAULT 0
);

-- =====================================================
-- TABELA DE CATEGORIAS DE LIVROS
-- =====================================================
CREATE TABLE categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL UNIQUE,
    descricao TEXT,
    ativo BOOLEAN DEFAULT TRUE
);

-- =====================================================
-- TABELA DE AUTORES
-- =====================================================
CREATE TABLE autores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    biografia TEXT,
    nacionalidade VARCHAR(50),
    data_nascimento DATE,
    ativo BOOLEAN DEFAULT TRUE
);

-- =====================================================
-- TABELA DE LIVROS
-- =====================================================
CREATE TABLE livros (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    autor_id INT,
    categoria_id INT,
    isbn VARCHAR(20) UNIQUE,
    ano_publicacao INT,
    numero_paginas INT,
    descricao TEXT,
    imagem_capa VARCHAR(255),
    estoque_total INT DEFAULT 0,
    estoque_disponivel INT DEFAULT 0,
    preco DECIMAL(10,2),
    editora VARCHAR(100),
    idioma VARCHAR(20) DEFAULT 'Português',
    ativo BOOLEAN DEFAULT TRUE,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ultima_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (autor_id) REFERENCES autores(id) ON DELETE SET NULL,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE SET NULL
);

-- =====================================================
-- TABELA DE FORNECEDORES
-- =====================================================
CREATE TABLE fornecedores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    cpf_cnpj VARCHAR(18) UNIQUE NOT NULL,
    telefone VARCHAR(15) NOT NULL,
    email VARCHAR(100),
    endereco TEXT,
    cidade VARCHAR(50),
    estado VARCHAR(2),
    cep VARCHAR(10),
    status ENUM('ativo', 'inativo') DEFAULT 'ativo',
    total_doacoes INT DEFAULT 0,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ultima_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =====================================================
-- TABELA DE EMPRÉSTIMOS
-- =====================================================
CREATE TABLE emprestimos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    livro_id INT NOT NULL,
    data_emprestimo TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_devolucao_prevista DATE NOT NULL,
    data_devolucao_real TIMESTAMP NULL,
    status ENUM('emprestado', 'devolvido', 'atrasado', 'aguardando_devolucao') DEFAULT 'emprestado',
    renovado BOOLEAN DEFAULT FALSE,
    observacoes TEXT,
    multa_valor DECIMAL(10,2) DEFAULT 0.00,
    multa_paga BOOLEAN DEFAULT FALSE,
    data_multa_paga TIMESTAMP NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (livro_id) REFERENCES livros(id) ON DELETE CASCADE
);

-- =====================================================
-- TABELA DE AGENDAMENTOS
-- =====================================================
CREATE TABLE agendamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    livro_id INT NOT NULL,
    data_agendamento DATE NOT NULL,
    horario TIME NOT NULL,
    status ENUM('agendado', 'concluido', 'cancelado') DEFAULT 'agendado',
    observacoes TEXT,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (livro_id) REFERENCES livros(id) ON DELETE CASCADE
);

-- =====================================================
-- TABELA DE DOAÇÕES
-- =====================================================
CREATE TABLE doacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    fornecedor_id INT,
    tipo_doacao ENUM('livro', 'item_higiene', 'dinheiro') NOT NULL,
    descricao TEXT NOT NULL,
    valor DECIMAL(10,2),
    quantidade INT DEFAULT 1,
    status ENUM('pendente', 'aprovada', 'rejeitada', 'entregue') DEFAULT 'pendente',
    data_doacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_aprovacao TIMESTAMP NULL,
    aprovado_por INT,
    observacoes TEXT,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (fornecedor_id) REFERENCES fornecedores(id) ON DELETE SET NULL,
    FOREIGN KEY (aprovado_por) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- =====================================================
-- TABELA DE MULTAS
-- =====================================================
CREATE TABLE multas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    emprestimo_id INT NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    motivo TEXT NOT NULL,
    status ENUM('pendente', 'paga', 'cancelada') DEFAULT 'pendente',
    data_geracao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_pagamento TIMESTAMP NULL,
    metodo_pagamento ENUM('pix', 'boleto', 'cartao', 'doacao') NULL,
    observacoes TEXT,
    FOREIGN KEY (emprestimo_id) REFERENCES emprestimos(id) ON DELETE CASCADE
);

-- =====================================================
-- TABELA DE HISTÓRICO DE ATIVIDADES
-- =====================================================
CREATE TABLE historico_atividades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    tipo_acao ENUM('login', 'logout', 'emprestimo', 'devolucao', 'renovacao', 'agendamento', 'doacao') NOT NULL,
    descricao TEXT NOT NULL,
    dados_json JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    data_acao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- =====================================================
-- TABELA DE CONFIGURAÇÕES DO SISTEMA
-- =====================================================
CREATE TABLE configuracoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    chave VARCHAR(50) UNIQUE NOT NULL,
    valor TEXT NOT NULL,
    descricao TEXT,
    tipo ENUM('string', 'integer', 'boolean', 'decimal', 'json') DEFAULT 'string',
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =====================================================
-- ÍNDICES PARA OTIMIZAÇÃO
-- =====================================================

-- Índices para usuários
CREATE INDEX idx_usuarios_cpf ON usuarios(cpf);
CREATE INDEX idx_usuarios_email ON usuarios(email);
CREATE INDEX idx_usuarios_admin ON usuarios(is_admin);

-- Índices para livros
CREATE INDEX idx_livros_titulo ON livros(titulo);
CREATE INDEX idx_livros_isbn ON livros(isbn);
CREATE INDEX idx_livros_autor ON livros(autor_id);
CREATE INDEX idx_livros_categoria ON livros(categoria_id);
CREATE INDEX idx_livros_ativo ON livros(ativo);

-- Índices para empréstimos
CREATE INDEX idx_emprestimos_usuario ON emprestimos(usuario_id);
CREATE INDEX idx_emprestimos_livro ON emprestimos(livro_id);
CREATE INDEX idx_emprestimos_status ON emprestimos(status);
CREATE INDEX idx_emprestimos_data ON emprestimos(data_emprestimo);

-- Índices para agendamentos
CREATE INDEX idx_agendamentos_usuario ON agendamentos(usuario_id);
CREATE INDEX idx_agendamentos_livro ON agendamentos(livro_id);
CREATE INDEX idx_agendamentos_data ON agendamentos(data_agendamento);
CREATE INDEX idx_agendamentos_status ON agendamentos(status);

-- Índices para fornecedores
CREATE INDEX idx_fornecedores_cpf_cnpj ON fornecedores(cpf_cnpj);
CREATE INDEX idx_fornecedores_status ON fornecedores(status);

-- =====================================================
-- DADOS DE EXEMPLO
-- =====================================================

-- Inserir categorias
INSERT INTO categorias (nome, descricao) VALUES
('Ficção Científica', 'Livros de ficção científica e distopia'),
('Estratégia', 'Livros sobre estratégia e táticas'),
('Ficção', 'Romances e obras de ficção'),
('Romance', 'Livros românticos e dramas'),
('Drama', 'Obras dramáticas e emocionais'),
('Fábula Política', 'Fábulas com conteúdo político'),
('Fantasia', 'Livros de fantasia e magia'),
('Literatura Brasileira', 'Obras da literatura brasileira'),
('Realismo Mágico', 'Literatura com elementos mágicos'),
('Biografia', 'Biografias e autobiografias'),
('Suspense', 'Livros de suspense e mistério'),
('Política', 'Obras sobre política e poder'),
('Fantasia Jovem', 'Fantasia para jovens'),
('Literatura Infantil', 'Livros para crianças');

-- Inserir autores
INSERT INTO autores (nome, biografia, nacionalidade, data_nascimento) VALUES
('George Orwell', 'Escritor britânico conhecido por suas obras distópicas', 'Britânico', '1903-06-25'),
('Sun Tzu', 'Estrategista militar chinês da antiguidade', 'Chinês', NULL),
('William P. Young', 'Escritor canadense autor de "A Cabana"', 'Canadense', '1955-05-11'),
('John Green', 'Escritor americano de literatura jovem', 'Americano', '1977-08-24'),
('Markus Zusak', 'Escritor australiano autor de "A Menina que Roubava Livros"', 'Australiano', '1975-06-23'),
('Franz Kafka', 'Escritor tcheco de língua alemã', 'Tcheco', '1883-07-03'),
('C.S. Lewis', 'Escritor britânico autor de "As Crônicas de Nárnia"', 'Britânico', '1898-11-29'),
('Machado de Assis', 'Escritor brasileiro, considerado o maior nome da literatura nacional', 'Brasileiro', '1839-06-21'),
('Jorge Amado', 'Escritor brasileiro, um dos mais famosos e traduzidos autores brasileiros', 'Brasileiro', '1912-08-10'),
('Gabriel García Márquez', 'Escritor colombiano, Nobel de Literatura', 'Colombiano', '1927-03-06'),
('Ashlee Vance', 'Jornalista americano especializado em tecnologia', 'Americano', '1977-01-01'),
('J.K. Rowling', 'Escritora britânica criadora da série Harry Potter', 'Britânica', '1965-07-31'),
('Mário de Andrade', 'Escritor brasileiro, um dos principais nomes do Modernismo', 'Brasileiro', '1893-10-09'),
('Paulo Coelho', 'Escritor brasileiro, um dos autores mais vendidos do mundo', 'Brasileiro', '1947-08-24'),
('Dan Brown', 'Escritor americano de thrillers', 'Americano', '1964-06-22'),
('Aluísio Azevedo', 'Escritor brasileiro do Naturalismo', 'Brasileiro', '1857-04-14'),
('Anne Frank', 'Jovem judia que escreveu um diário durante a Segunda Guerra', 'Alemã', '1929-06-12'),
('J.R.R. Tolkien', 'Escritor britânico criador de "O Senhor dos Anéis"', 'Britânico', '1892-01-03'),
('Patrick Rothfuss', 'Escritor americano de fantasia', 'Americano', '1973-06-06'),
('Nicolau Maquiavel', 'Filósofo e escritor italiano do Renascimento', 'Italiano', '1469-05-03'),
('Jane Austen', 'Escritora britânica do Romantismo', 'Britânica', '1775-12-16'),
('Rick Riordan', 'Escritor americano de literatura juvenil', 'Americano', '1964-06-05'),
('Antoine de Saint-Exupéry', 'Escritor francês autor de "O Pequeno Príncipe"', 'Francês', '1900-06-29'),
('Graciliano Ramos', 'Escritor brasileiro do Modernismo', 'Brasileiro', '1892-10-27'),
('João Guimarães Rosa', 'Escritor brasileiro, um dos maiores nomes da literatura nacional', 'Brasileiro', '1908-06-27');

-- Inserir livros
INSERT INTO livros (titulo, autor_id, categoria_id, isbn, ano_publicacao, numero_paginas, descricao, imagem_capa, estoque_total, estoque_disponivel, preco, editora) VALUES
('1984', 1, 1, '978-8535909555', 1949, 328, 'Um clássico distópico sobre um regime totalitário.', 'IMG/1984.jpg', 5, 5, 29.90, 'Companhia das Letras'),
('A Arte da Guerra', 2, 2, '978-8546500923', -500, 273, 'Antigo tratado militar chinês sobre estratégia e tática.', 'IMG/aartedaguerra.jpg', 3, 3, 24.90, 'L&PM'),
('A Cabana', 3, 3, '978-8543102079', 2007, 240, 'Uma história de superação e fé após uma tragédia.', 'IMG/acabana.jpg', 4, 4, 34.90, 'Arqueiro'),
('A Culpa é das Estrelas', 4, 4, '978-8543102062', 2012, 288, 'Dois adolescentes se apaixonam enquanto lutam contra o câncer.', 'IMG/aculpaedasestrelas.jpg', 6, 6, 29.90, 'Intrínseca'),
('A Menina que Roubava Livros', 5, 5, '978-8543102072', 2005, 480, 'A história de uma garota na Alemanha nazista que encontra consolo nos livros.', 'IMG/ameninaqueroubavalivros.jpg', 2, 2, 39.90, 'Intrínseca'),
('A Metamorfose', 6, 3, '978-8535909556', 1915, 100, 'Um homem acorda transformado em um inseto gigante.', 'IMG/ametamorfose.webp', 3, 3, 19.90, 'Companhia das Letras'),
('A Revolução dos Bichos', 1, 6, '978-8535909557', 1945, 152, 'Animais de uma fazenda se rebelam contra seus donos humanos.', 'IMG/arevolucaodosbichos.jpg', 4, 4, 24.90, 'Companhia das Letras'),
('As Crônicas de Nárnia', 7, 7, '978-8543102073', 1950, 768, 'Aventuras mágicas em um mundo fantástico.', 'IMG/ascronicasdenarnia.jpg', 5, 5, 49.90, 'Martins Fontes'),
('Memórias Póstumas de Brás Cubas', 8, 8, '978-8535909558', 1881, 208, 'Narrativa inovadora de um defunto-autor.', 'IMG/asmemoriaspostumasdebrascuba.jpg', 2, 2, 29.90, 'Companhia das Letras'),
('Capitães da Areia', 9, 8, '978-8535909559', 1937, 256, 'A vida de meninos de rua em Salvador.', 'IMG/capitaesdaareia.jpg', 3, 3, 34.90, 'Record'),
('Cem Anos de Solidão', 10, 9, '978-8535909560', 1967, 448, 'A saga da família Buendía em Macondo.', 'IMG/cemanosdesolidao.jpg', 4, 4, 39.90, 'Record'),
('Dom Casmurro', 8, 4, '978-8535909561', 1899, 256, 'A dúvida sobre a traição de Capitu.', 'IMG/domcasmurro.webp', 2, 2, 29.90, 'Companhia das Letras'),
('Dom Quixote', 11, 4, '978-8535909562', 1605, 992, 'As aventuras do cavaleiro da triste figura.', 'IMG/domquixote.jpg', 3, 3, 59.90, 'Companhia das Letras'),
('Elon Musk', 12, 10, '978-8543102074', 2015, 416, 'A vida e carreira do empreendedor Elon Musk.', 'IMG/elonmusk.jpg', 2, 2, 44.90, 'Intrínseca'),
('Harry Potter e a Pedra Filosofal', 13, 7, '978-8543102075', 1997, 264, 'O início da jornada do jovem bruxo Harry Potter em Hogwarts.', 'IMG/harrypotereapedrafilosofal.jpg', 5, 5, 34.90, 'Rocco'),
('Macunaíma', 14, 8, '978-8535909563', 1928, 192, 'As aventuras do herói sem nenhum caráter, uma alegoria do povo brasileiro.', 'IMG/macunaima.jpg', 3, 3, 24.90, 'Companhia das Letras'),
('O Alquimista', 15, 3, '978-8543102076', 1988, 208, 'A jornada de um pastor em busca de seu tesouro pessoal.', 'IMG/oalquimista.jpg', 4, 4, 29.90, 'Paralela'),
('O Código Da Vinci', 16, 11, '978-8543102077', 2003, 432, 'Um professor de simbologia investiga um assassinato no Museu do Louvre.', 'IMG/ocodigodavinci.jpg', 3, 3, 39.90, 'Sextante'),
('O Cortiço', 17, 8, '978-8535909564', 1890, 256, 'A vida dos moradores de um cortiço no Rio de Janeiro do século XIX.', 'IMG/ocortico.jpg', 5, 5, 29.90, 'Companhia das Letras'),
('O Diário de Anne Frank', 18, 10, '978-8543102078', 1947, 352, 'O diário de uma jovem judia durante a ocupação nazista na Holanda.', 'IMG/odiariodeannnefrank.jpg', 3, 3, 34.90, 'Record'),
('O Hobbit', 19, 7, '978-8543102079', 1937, 336, 'A aventura do hobbit Bilbo Bolseiro em uma jornada para recuperar um tesouro roubado.', 'IMG/ohobbit.jpg', 4, 4, 39.90, 'Martins Fontes'),
('O Nome do Vento', 20, 7, '978-8543102080', 2007, 656, 'A história do lendário Kvothe, contada por ele mesmo.', 'IMG/onomedovento.jpg', 2, 2, 44.90, 'Arqueiro'),
('O Príncipe', 21, 12, '978-8535909565', 1532, 176, 'Um tratado sobre política e poder, escrito para Lorenzo de Médici.', 'IMG/oprincipe.jpg', 4, 4, 24.90, 'Companhia das Letras'),
('Orgulho e Preconceito', 22, 4, '978-8535909566', 1813, 424, 'A história de Elizabeth Bennet e Mr. Darcy em uma sociedade regida por convenções sociais.', 'IMG/orgulhoepreconceito.jpg', 3, 3, 29.90, 'Companhia das Letras'),
('O Senhor dos Anéis', 19, 7, '978-8543102081', 1954, 1200, 'A épica jornada para destruir o Um Anel e derrotar o Senhor do Escuro.', 'IMG/osenhordosaneis.webp', 2, 2, 69.90, 'Martins Fontes'),
('Percy Jackson e o Ladrão de Raios', 23, 13, '978-8543102082', 2005, 400, 'Um garoto descobre que é filho de um deus grego e precisa impedir uma guerra entre os deuses.', 'IMG/percyjacksoneoladraoderaios.jpg', 4, 4, 34.90, 'Intrínseca'),
('O Pequeno Príncipe', 24, 14, '978-8543102083', 1943, 96, 'A história de um príncipe que viaja pelos planetas e aprende sobre amor e amizade.', 'IMG/pequenoprincipe.jpg', 5, 5, 24.90, 'Geração Editorial'),
('Vidas Secas', 25, 8, '978-8535909567', 1938, 176, 'A saga de uma família de retirantes pelo sertão nordestino.', 'IMG/vidassecas.jpg', 3, 3, 24.90, 'Record'),
('Grande Sertão: Veredas', 26, 8, '978-8535909568', 1956, 624, 'A saga de Riobaldo no sertão brasileiro.', 'IMG/grandesertaoveredas.jpg', 2, 2, 39.90, 'Nova Fronteira');

-- Inserir usuários (senha: 123456 - hash bcrypt)
INSERT INTO usuarios (nome, cpf, telefone, senha, email, is_admin) VALUES
('Administrador', '12345678901', '(11) 99999-9999', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@biblioteca.com', TRUE),
('João Silva', '98765432100', '(11) 88888-8888', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'joao@email.com', FALSE),
('Maria Santos', '11122233344', '(11) 77777-7777', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'maria@email.com', FALSE),
('Pedro Oliveira', '55566677788', '(11) 66666-6666', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'pedro@email.com', FALSE),
('Ana Costa', '99988877766', '(11) 55555-5555', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ana@email.com', FALSE);

-- Inserir fornecedores
INSERT INTO fornecedores (nome, cpf_cnpj, telefone, email, endereco, cidade, estado, cep, total_doacoes) VALUES
('Editora Livros & Cia', '12.345.678/0001-90', '(47) 3333-4444', 'contato@gmail.com', 'Rua das Editoras, 123', 'São Paulo', 'SP', '01234-567', 15),
('Distribuidora Nacional', '98.765.432/0001-10', '(11) 4444-5555', 'vendas@gmail.com', 'Av. dos Livros, 456', 'São Paulo', 'SP', '04567-890', 8),
('Livraria Central', '11.222.333/0001-44', '(21) 5555-6666', 'central@gmail.com', 'Rua Central, 789', 'Rio de Janeiro', 'RJ', '20000-123', 12),
('Editora Regional', '55.666.777/0001-88', '(31) 6666-7777', 'regional@gmail.com', 'Av. Regional, 321', 'Belo Horizonte', 'MG', '30000-456', 6);

-- Inserir configurações do sistema
INSERT INTO configuracoes (chave, valor, descricao, tipo) VALUES
('prazo_emprestimo_dias', '7', 'Prazo padrão para empréstimo em dias', 'integer'),
('limite_emprestimos_usuario', '5', 'Limite máximo de empréstimos por usuário', 'integer'),
('valor_multa_diaria', '0.25', 'Valor da multa por dia de atraso', 'decimal'),
('dias_para_renovacao', '6', 'Dias mínimos para renovar um empréstimo', 'integer'),
('nome_biblioteca', 'Biblioteca Arco-Íris', 'Nome da biblioteca', 'string'),
('email_contato', 'contato@bibliotecaarcoiris.com', 'Email de contato da biblioteca', 'string'),
('horario_funcionamento', '{"segunda": "08:00-18:00", "terca": "08:00-18:00", "quarta": "08:00-18:00", "quinta": "08:00-18:00", "sexta": "08:00-18:00", "sabado": "08:00-12:00", "domingo": "Fechado"}', 'Horário de funcionamento da biblioteca', 'json');

-- =====================================================
-- TRIGGERS PARA MANTER INTEGRIDADE
-- =====================================================

-- Trigger para atualizar estoque quando um livro é emprestado
DELIMITER //
CREATE TRIGGER after_emprestimo_insert
AFTER INSERT ON emprestimos
FOR EACH ROW
BEGIN
    UPDATE livros 
    SET estoque_disponivel = estoque_disponivel - 1
    WHERE id = NEW.livro_id;
    
    UPDATE usuarios 
    SET total_emprestimos = total_emprestimos + 1
    WHERE id = NEW.usuario_id;
END//

-- Trigger para atualizar estoque quando um livro é devolvido
CREATE TRIGGER after_emprestimo_update
AFTER UPDATE ON emprestimos
FOR EACH ROW
BEGIN
    IF NEW.status = 'devolvido' AND OLD.status != 'devolvido' THEN
        UPDATE livros 
        SET estoque_disponivel = estoque_disponivel + 1
        WHERE id = NEW.livro_id;
        
        UPDATE usuarios 
        SET total_devolvidos = total_devolvidos + 1
        WHERE id = NEW.usuario_id;
    END IF;
END//

DELIMITER ;

-- =====================================================
-- VIEWS ÚTEIS
-- =====================================================

-- View para livros mais emprestados
CREATE VIEW vw_livros_mais_emprestados AS
SELECT 
    l.id,
    l.titulo,
    a.nome as autor,
    c.nome as categoria,
    l.estoque_total,
    l.estoque_disponivel,
    COUNT(e.id) as total_emprestimos
FROM livros l
LEFT JOIN autores a ON l.autor_id = a.id
LEFT JOIN categorias c ON l.categoria_id = c.id
LEFT JOIN emprestimos e ON l.id = e.livro_id
WHERE l.ativo = TRUE
GROUP BY l.id, l.titulo, a.nome, c.nome, l.estoque_total, l.estoque_disponivel
ORDER BY total_emprestimos DESC;

-- View para empréstimos atrasados
CREATE VIEW vw_emprestimos_atrasados AS
SELECT 
    e.id,
    u.nome as usuario,
    u.cpf,
    u.telefone,
    l.titulo as livro,
    e.data_emprestimo,
    e.data_devolucao_prevista,
    DATEDIFF(CURRENT_DATE, e.data_devolucao_prevista) as dias_atraso,
    (DATEDIFF(CURRENT_DATE, e.data_devolucao_prevista) * 2.00) as multa_calculada
FROM emprestimos e
JOIN usuarios u ON e.usuario_id = u.id
JOIN livros l ON e.livro_id = l.id
WHERE e.status = 'emprestado' 
AND e.data_devolucao_prevista < CURRENT_DATE;

-- View para estatísticas gerais
CREATE VIEW vw_estatisticas_gerais AS
SELECT 
    (SELECT COUNT(*) FROM usuarios WHERE ativo = TRUE) as total_usuarios,
    (SELECT COUNT(*) FROM livros WHERE ativo = TRUE) as total_livros,
    (SELECT COUNT(*) FROM emprestimos WHERE status = 'emprestado') as livros_emprestados,
    (SELECT COUNT(*) FROM emprestimos WHERE status = 'devolvido') as livros_devolvidos,
    (SELECT COUNT(*) FROM fornecedores WHERE status = 'ativo') as total_fornecedores,
    (SELECT COUNT(*) FROM agendamentos WHERE status = 'agendado') as agendamentos_pendentes;

-- =====================================================
-- PROCEDURES ÚTEIS
-- =====================================================

-- Procedure para renovar empréstimo
DELIMITER //
CREATE PROCEDURE RenovarEmprestimo(IN emprestimo_id INT)
BEGIN
    DECLARE dias_para_renovacao INT DEFAULT 6;
    DECLARE prazo_emprestimo INT DEFAULT 7;
    DECLARE data_emprestimo DATE;
    DECLARE dias_passados INT;
    
    -- Verificar se o empréstimo existe e não foi renovado
    SELECT data_emprestimo, renovado INTO data_emprestimo, @renovado
    FROM emprestimos 
    WHERE id = emprestimo_id AND status = 'emprestado';
    
    IF @renovado = FALSE THEN
        SET dias_passados = DATEDIFF(CURRENT_DATE, data_emprestimo);
        
        IF dias_passados >= dias_para_renovacao THEN
            UPDATE emprestimos 
            SET data_devolucao_prevista = DATE_ADD(data_devolucao_prevista, INTERVAL prazo_emprestimo DAY),
                renovado = TRUE
            WHERE id = emprestimo_id;
            
            SELECT 'Empréstimo renovado com sucesso!' as mensagem;
        ELSE
            SELECT CONCAT('Aguarde ', dias_para_renovacao - dias_passados, ' dias para renovar') as mensagem;
        END IF;
    ELSE
        SELECT 'Este empréstimo já foi renovado!' as mensagem;
    END IF;
END//

-- Procedure para calcular multas
CREATE PROCEDURE CalcularMultas()
BEGIN
    DECLARE valor_multa DECIMAL(10,2) DEFAULT 0.25;
    
    -- Atualizar status para atrasado
    UPDATE emprestimos 
    SET status = 'atrasado'
    WHERE status = 'emprestado' 
    AND data_devolucao_prevista < CURRENT_DATE;
    
    -- Inserir multas para empréstimos atrasados
    INSERT INTO multas (emprestimo_id, valor, motivo, status)
    SELECT 
        e.id,
        (DATEDIFF(CURRENT_DATE, e.data_devolucao_prevista) * valor_multa) as valor,
        CONCAT('Multa por atraso de ', DATEDIFF(CURRENT_DATE, e.data_devolucao_prevista), ' dias') as motivo,
        'pendente'
    FROM emprestimos e
    WHERE e.status = 'atrasado'
    AND NOT EXISTS (SELECT 1 FROM multas m WHERE m.emprestimo_id = e.id AND m.status = 'pendente');
    
    -- Atualizar status de débito dos usuários
    UPDATE usuarios u
    SET tem_debito = TRUE
    WHERE EXISTS (
        SELECT 1 FROM emprestimos e 
        WHERE e.usuario_id = u.id 
        AND e.status = 'atrasado'
    );
END//

DELIMITER ;

-- =====================================================
-- COMANDOS FINAIS
-- =====================================================

-- Executar cálculo inicial de multas
CALL CalcularMultas();

-- Mostrar estatísticas iniciais
SELECT * FROM vw_estatisticas_gerais;

-- Verificar livros mais emprestados
SELECT * FROM vw_livros_mais_emprestados LIMIT 10;

-- Verificar empréstimos atrasados
SELECT * FROM vw_emprestimos_atrasados;
