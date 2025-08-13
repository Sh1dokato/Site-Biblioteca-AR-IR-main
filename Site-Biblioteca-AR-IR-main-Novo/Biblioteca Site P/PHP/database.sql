-- Script para criar a base de dados da Biblioteca Arco-Íris
-- Execute este script no seu servidor MySQL

-- Criar base de dados
CREATE DATABASE IF NOT EXISTS biblioteca_arco_iris CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE biblioteca_arco_iris;

-- Tabela de usuários
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    cpf VARCHAR(14) UNIQUE NOT NULL,
    telefone VARCHAR(15) NOT NULL,
    email VARCHAR(100),
    senha VARCHAR(255) NOT NULL,
    tipo ENUM('Admin', 'Usuário', 'Funcionário') DEFAULT 'Usuário',
    status ENUM('Ativo', 'Inativo', 'Suspenso') DEFAULT 'Ativo',
    is_admin BOOLEAN DEFAULT FALSE,
    tem_debito BOOLEAN DEFAULT FALSE,
    tem_doacao_pendente BOOLEAN DEFAULT FALSE,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ultimo_acesso TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de livros
CREATE TABLE livros (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    autor VARCHAR(100) NOT NULL,
    isbn VARCHAR(13) UNIQUE,
    ano_publicacao INT,
    paginas INT,
    categoria VARCHAR(50),
    descricao TEXT,
    imagem VARCHAR(255),
    estoque INT DEFAULT 0,
    disponiveis INT DEFAULT 0,
    preco DECIMAL(10,2),
    editora VARCHAR(100),
    idioma VARCHAR(20) DEFAULT 'Português',
    status ENUM('Disponível', 'Indisponível', 'Em Manutenção') DEFAULT 'Disponível',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de empréstimos
CREATE TABLE emprestimos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    livro_id INT NOT NULL,
    data_emprestimo TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_devolucao_prevista DATE NOT NULL,
    data_devolucao_real TIMESTAMP NULL,
    status ENUM('Emprestado', 'Devolvido', 'Atrasado', 'Renovado') DEFAULT 'Emprestado',
    renovacoes INT DEFAULT 0,
    observacoes TEXT,
    funcionario_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (livro_id) REFERENCES livros(id) ON DELETE CASCADE,
    FOREIGN KEY (funcionario_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- Tabela de fornecedores
CREATE TABLE fornecedores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    cpf_cnpj VARCHAR(18) UNIQUE NOT NULL,
    tipo ENUM('CPF', 'CNPJ') NOT NULL,
    telefone VARCHAR(15) NOT NULL,
    email VARCHAR(100),
    cep VARCHAR(9),
    endereco VARCHAR(200),
    numero VARCHAR(10),
    complemento VARCHAR(100),
    bairro VARCHAR(100),
    cidade VARCHAR(100),
    estado CHAR(2),
    status ENUM('Ativo', 'Inativo') DEFAULT 'Ativo',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de doações
CREATE TABLE doacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    doador_id INT,
    livro_id INT,
    titulo VARCHAR(200),
    autor VARCHAR(100),
    quantidade INT DEFAULT 1,
    estado ENUM('Excelente', 'Bom', 'Regular', 'Ruim') DEFAULT 'Bom',
    observacoes TEXT,
    status ENUM('Pendente', 'Aprovada', 'Rejeitada', 'Processada') DEFAULT 'Pendente',
    data_doacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_processamento TIMESTAMP NULL,
    funcionario_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (doador_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    FOREIGN KEY (livro_id) REFERENCES livros(id) ON DELETE SET NULL,
    FOREIGN KEY (funcionario_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- Tabela de multas
CREATE TABLE multas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    emprestimo_id INT NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    dias_atraso INT NOT NULL,
    status ENUM('Pendente', 'Paga', 'Cancelada') DEFAULT 'Pendente',
    data_geracao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_pagamento TIMESTAMP NULL,
    observacoes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (emprestimo_id) REFERENCES emprestimos(id) ON DELETE CASCADE
);

-- Tabela de agendamentos
CREATE TABLE agendamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    data_agendamento DATE NOT NULL,
    horario TIME NOT NULL,
    tipo ENUM('Consulta', 'Estudo', 'Reunião', 'Outro') DEFAULT 'Consulta',
    descricao TEXT,
    status ENUM('Pendente', 'Confirmado', 'Cancelado', 'Concluído') DEFAULT 'Pendente',
    funcionario_id INT,
    observacoes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (funcionario_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- Tabela de logs de atividades
CREATE TABLE logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    acao VARCHAR(100) NOT NULL,
    detalhes TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    data_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- Tabela de configurações do sistema
CREATE TABLE configuracoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    chave VARCHAR(100) UNIQUE NOT NULL,
    valor TEXT,
    descricao TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Inserir usuário admin padrão
INSERT INTO usuarios (nome, cpf, telefone, senha, tipo, is_admin, status) VALUES 
('Admin Principal', '99999999999', '47991579860', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', TRUE, 'Ativo');

-- Inserir livros padrão
INSERT INTO livros (titulo, autor, ano_publicacao, paginas, categoria, descricao, estoque, disponiveis, imagem) VALUES
('1984', 'George Orwell', 1949, 328, 'Ficção Científica', 'Um clássico distópico sobre um regime totalitário.', 5, 5, 'IMG/1984.jpg'),
('A Arte da Guerra', 'Sun Tzu', -500, 273, 'Estratégia', 'Antigo tratado militar chinês sobre estratégia e tática.', 3, 3, 'IMG/aartedaguerra.jpg'),
('A Cabana', 'William P. Young', 2007, 240, 'Ficção', 'Uma história de superação e fé após uma tragédia.', 4, 4, 'IMG/acabana.jpg'),
('A Culpa é das Estrelas', 'John Green', 2012, 288, 'Romance', 'Dois adolescentes se apaixonam enquanto lutam contra o câncer.', 6, 6, 'IMG/aculpaedasestrelas.jpg'),
('A Menina que Roubava Livros', 'Markus Zusak', 2005, 480, 'Drama', 'A história de uma garota na Alemanha nazista que encontra consolo nos livros.', 2, 2, 'IMG/ameninaqueroubavalivros.jpg'),
('A Metamorfose', 'Franz Kafka', 1915, 100, 'Ficção', 'Um homem acorda transformado em um inseto gigante.', 3, 3, 'IMG/ametamorfose.webp'),
('A Revolução dos Bichos', 'George Orwell', 1945, 152, 'Fábula Política', 'Animais de uma fazenda se rebelam contra seus donos humanos.', 4, 4, 'IMG/arevolucaodosbichos.jpg'),
('As Crônicas de Nárnia', 'C.S. Lewis', 1950, 768, 'Fantasia', 'Aventuras mágicas em um mundo fantástico.', 5, 5, 'IMG/ascronicasdenarnia.jpg'),
('Memórias Póstumas de Brás Cubas', 'Machado de Assis', 1881, 208, 'Romance', 'Narrativa inovadora de um defunto-autor.', 2, 2, 'IMG/asmemoriaspostumasdebrascuba.jpg'),
('Capitães da Areia', 'Jorge Amado', 1937, 256, 'Literatura Brasileira', 'A vida de meninos de rua em Salvador.', 3, 3, 'IMG/capitaesdaareia.jpg'),
('Cem Anos de Solidão', 'Gabriel García Márquez', 1967, 448, 'Realismo Mágico', 'A saga da família Buendía em Macondo.', 4, 4, 'IMG/cemanosdesolidao.jpg'),
('Dom Casmurro', 'Machado de Assis', 1899, 256, 'Romance', 'A dúvida sobre a traição de Capitu.', 2, 2, 'IMG/domcasmurro.webp'),
('Dom Quixote', 'Miguel de Cervantes', 1605, 992, 'Romance', 'As aventuras do cavaleiro da triste figura.', 3, 3, 'IMG/domquixote.jpg'),
('Elon Musk', 'Ashlee Vance', 2015, 416, 'Biografia', 'A vida e carreira do empreendedor Elon Musk.', 2, 2, 'IMG/elonmusk.jpg'),
('Harry Potter e a Pedra Filosofal', 'J.K. Rowling', 1997, 264, 'Fantasia', 'O início da jornada do jovem bruxo Harry Potter em Hogwarts.', 5, 5, 'IMG/harrypotereapedrafilosofal.jpg'),
('Macunaíma', 'Mário de Andrade', 1928, 192, 'Literatura Brasileira', 'As aventuras do herói sem nenhum caráter, uma alegoria do povo brasileiro.', 3, 3, 'IMG/macunaima.jpg'),
('O Alquimista', 'Paulo Coelho', 1988, 208, 'Ficção', 'A jornada de um pastor em busca de seu tesouro pessoal.', 4, 4, 'IMG/oalquimista.jpg'),
('O Código Da Vinci', 'Dan Brown', 2003, 432, 'Suspense', 'Um professor de simbologia investiga um assassinato no Museu do Louvre.', 3, 3, 'IMG/ocodigodavinci.jpg'),
('O Cortiço', 'Aluísio Azevedo', 1890, 256, 'Literatura Brasileira', 'A vida dos moradores de um cortiço no Rio de Janeiro do século XIX.', 5, 5, 'IMG/ocortico.jpg'),
('O Diário de Anne Frank', 'Anne Frank', 1947, 352, 'Biografia', 'O diário de uma jovem judia durante a ocupação nazista na Holanda.', 3, 3, 'IMG/odiariodeannnefrank.jpg'),
('O Hobbit', 'J.R.R. Tolkien', 1937, 336, 'Fantasia', 'A aventura do hobbit Bilbo Bolseiro em uma jornada para recuperar um tesouro roubado.', 4, 4, 'IMG/ohobbit.jpg'),
('O Nome do Vento', 'Patrick Rothfuss', 2007, 656, 'Fantasia', 'A história do lendário Kvothe, contada por ele mesmo.', 2, 2, 'IMG/onomedovento.jpg'),
('O Príncipe', 'Nicolau Maquiavel', 1532, 176, 'Política', 'Um tratado sobre política e poder, escrito para Lorenzo de Médici.', 4, 4, 'IMG/oprincipe.jpg'),
('Orgulho e Preconceito', 'Jane Austen', 1813, 424, 'Romance', 'A história de Elizabeth Bennet e Mr. Darcy em uma sociedade regida por convenções sociais.', 3, 3, 'IMG/orgulhoepreconceito.jpg'),
('O Senhor dos Anéis', 'J.R.R. Tolkien', 1954, 1200, 'Fantasia', 'A épica jornada para destruir o Um Anel e derrotar o Senhor do Escuro.', 2, 2, 'IMG/osenhordosaneis.webp'),
('Percy Jackson e o Ladrão de Raios', 'Rick Riordan', 2005, 400, 'Fantasia Jovem', 'Um garoto descobre que é filho de um deus grego e precisa impedir uma guerra entre os deuses.', 4, 4, 'IMG/percyjacksoneoladraoderaios.jpg'),
('O Pequeno Príncipe', 'Antoine de Saint-Exupéry', 1943, 96, 'Literatura Infantil', 'A história de um príncipe que viaja pelos planetas e aprende sobre amor e amizade.', 5, 5, 'IMG/pequenoprincipe.jpg'),
('Vidas Secas', 'Graciliano Ramos', 1938, 176, 'Literatura Brasileira', 'A saga de uma família de retirantes pelo sertão nordestino.', 3, 3, 'IMG/vidassecas.jpg');

-- Inserir configurações padrão
INSERT INTO configuracoes (chave, valor, descricao) VALUES
('prazo_emprestimo', '7', 'Prazo padrão para empréstimos em dias'),
('max_emprestimos', '5', 'Máximo de empréstimos por usuário'),
('multa_dia', '2.00', 'Valor da multa por dia de atraso'),
('renovacoes_max', '2', 'Máximo de renovações permitidas'),
('email_notificacao', 'true', 'Habilitar notificações por email'),
('manutencao', 'false', 'Modo de manutenção do sistema');

-- Criar índices para melhor performance
CREATE INDEX idx_usuarios_cpf ON usuarios(cpf);
CREATE INDEX idx_usuarios_email ON usuarios(email);
CREATE INDEX idx_livros_titulo ON livros(titulo);
CREATE INDEX idx_livros_autor ON livros(autor);
CREATE INDEX idx_livros_categoria ON livros(categoria);
CREATE INDEX idx_emprestimos_usuario ON emprestimos(usuario_id);
CREATE INDEX idx_emprestimos_livro ON emprestimos(livro_id);
CREATE INDEX idx_emprestimos_status ON emprestimos(status);
CREATE INDEX idx_emprestimos_data ON emprestimos(data_emprestimo);
CREATE INDEX idx_fornecedores_cpf_cnpj ON fornecedores(cpf_cnpj);
CREATE INDEX idx_logs_usuario ON logs(usuario_id);
CREATE INDEX idx_logs_data ON logs(data_hora);

-- Criar views úteis
CREATE VIEW vw_emprestimos_ativos AS
SELECT 
    e.id,
    e.usuario_id,
    u.nome as nome_usuario,
    e.livro_id,
    l.titulo as titulo_livro,
    l.autor,
    e.data_emprestimo,
    e.data_devolucao_prevista,
    e.status,
    e.renovacoes,
    DATEDIFF(CURDATE(), e.data_devolucao_prevista) as dias_atraso
FROM emprestimos e
JOIN usuarios u ON e.usuario_id = u.id
JOIN livros l ON e.livro_id = l.id
WHERE e.status IN ('Emprestado', 'Renovado');

CREATE VIEW vw_livros_populares AS
SELECT 
    l.id,
    l.titulo,
    l.autor,
    l.categoria,
    COUNT(e.id) as total_emprestimos,
    l.estoque,
    l.disponiveis
FROM livros l
LEFT JOIN emprestimos e ON l.id = e.livro_id
GROUP BY l.id
ORDER BY total_emprestimos DESC;

CREATE VIEW vw_usuarios_debito AS
SELECT 
    u.id,
    u.nome,
    u.cpf,
    u.telefone,
    COUNT(e.id) as emprestimos_ativos,
    SUM(CASE WHEN e.status = 'Atrasado' THEN 1 ELSE 0 END) as emprestimos_atrasados
FROM usuarios u
LEFT JOIN emprestimos e ON u.id = e.usuario_id AND e.status IN ('Emprestado', 'Renovado', 'Atrasado')
GROUP BY u.id
HAVING emprestimos_atrasados > 0 OR emprestimos_ativos > 0;

