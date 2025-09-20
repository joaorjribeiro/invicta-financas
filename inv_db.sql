-- Cria o banco de dados inv_db
CREATE DATABASE IF NOT EXISTS inv_db
DEFAULT CHARACTER SET utf8mb4
DEFAULT COLLATE utf8mb4_unicode_ci;

-- Usa o banco de dados recém-criado
USE inv_db;

-- --------------------------------------------------------
-- Estrutura da tabela `usuarios`
-- Armazena as informações de login e perfil dos usuários.
-- --------------------------------------------------------
-- Estrutura da tabela `usuarios` com a correção dos avisos
CREATE TABLE IF NOT EXISTS usuarios (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(191) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    perfil ENUM('usuario', 'admin') NOT NULL DEFAULT 'usuario',
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- --------------------------------------------------------
-- Estrutura da tabela `categorias`
-- Armazena as categorias de transações (ex: "Alimentação", "Salário", "Transporte").
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS categorias (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    tipo ENUM('receita', 'despesa') NOT NULL,
    id_usuario INT(11),
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- --------------------------------------------------------
-- Estrutura da tabela `transacoes`
-- Armazena todas as receitas e despesas do sistema.
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS transacoes (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    descricao VARCHAR(255) NOT NULL,
    valor DECIMAL(10, 2) NOT NULL,
    tipo ENUM('receita', 'despesa') NOT NULL,
    data_transacao DATE NOT NULL,
    id_categoria INT(11) NOT NULL,
    id_usuario INT(11) NOT NULL,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_categoria) REFERENCES categorias(id) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE
);

CREATE TABLE metas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    mes_ano VARCHAR(7) NOT NULL, -- formato 'YYYY-MM'
    valor_limite DECIMAL(10,2) NOT NULL,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP
 );
 

ALTER TABLE metas ADD id_categoria INT NOT NULL;
ALTER TABLE metas
ADD CONSTRAINT fk_metas_categoria 
FOREIGN KEY (id_categoria) REFERENCES categorias(id);


ALTER TABLE metas CHANGE limite valor_limite DECIMAL(10,2) NOT NULL;
