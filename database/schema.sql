CREATE DATABASE IF NOT EXISTS ebook
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE ebook;

CREATE TABLE IF NOT EXISTS livros (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(40) NOT NULL,
    editora VARCHAR(40) NOT NULL,
    edicao INT NOT NULL,
    anoPublicacao VARCHAR(4) NOT NULL,
    valor DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS autores (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(40) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS assuntos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    descricao VARCHAR(30) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS livro_autor (
    livro_id INT UNSIGNED NOT NULL,
    autor_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (livro_id, autor_id),
    CONSTRAINT fk_livro_autor_livro FOREIGN KEY (livro_id) REFERENCES livros(id) ON DELETE CASCADE,
    CONSTRAINT fk_livro_autor_autor FOREIGN KEY (autor_id) REFERENCES autores(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS livro_assunto (
    livro_id INT UNSIGNED NOT NULL,
    assunto_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (livro_id, assunto_id),
    CONSTRAINT fk_livro_assunto_livro FOREIGN KEY (livro_id) REFERENCES livros(id) ON DELETE CASCADE,
    CONSTRAINT fk_livro_assunto_assunto FOREIGN KEY (assunto_id) REFERENCES assuntos(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE OR REPLACE VIEW vw_relatorio_livros_por_autor AS
SELECT
    a.id AS autor_id,
    a.nome AS autor_nome,
    l.id AS livro_id,
    l.titulo,
    l.editora,
    l.edicao,
    l.anoPublicacao,
    l.valor,
    COALESCE(autores_agg.autores_livro, a.nome) AS autores_livro,
    COALESCE(assuntos_agg.assuntos_livro, '') AS assuntos_livro
FROM livro_autor la
INNER JOIN autores a ON a.id = la.autor_id
INNER JOIN livros l ON l.id = la.livro_id
LEFT JOIN (
    SELECT
        la2.livro_id,
        GROUP_CONCAT(DISTINCT a2.nome ORDER BY a2.nome SEPARATOR ', ') AS autores_livro
    FROM livro_autor la2
    INNER JOIN autores a2 ON a2.id = la2.autor_id
    GROUP BY la2.livro_id
) AS autores_agg ON autores_agg.livro_id = l.id
LEFT JOIN (
    SELECT
        ls.livro_id,
        GROUP_CONCAT(DISTINCT s.descricao ORDER BY s.descricao SEPARATOR ', ') AS assuntos_livro
    FROM livro_assunto ls
    INNER JOIN assuntos s ON s.id = ls.assunto_id
    GROUP BY ls.livro_id
) AS assuntos_agg ON assuntos_agg.livro_id = l.id;
