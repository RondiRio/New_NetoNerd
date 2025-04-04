-- Criando o Banco de Dados
CREATE DATABASE netonerd_chamados;
USE netonerd_chamados;

-- Tabela de Usuários (Técnicos e Administradores)
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    senha_hash VARCHAR(255) NOT NULL,
    tipo ENUM('tecnico', 'admin') NOT NULL,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de Clientes
CREATE TABLE clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    telefone VARCHAR(20),
    endereco TEXT,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de Equipamentos (Vinculados a Clientes)
CREATE TABLE equipamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    descricao VARCHAR(255) NOT NULL,
    numero_serie VARCHAR(100) UNIQUE,
    data_aquisicao DATE,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE
);

-- Tabela de Chamados
CREATE TABLE chamados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    tecnico_id INT,
    equipamento_id INT,
    titulo VARCHAR(255) NOT NULL,
    descricao TEXT NOT NULL,
    status ENUM('aberto', 'em andamento', 'pendente', 'resolvido', 'cancelado') DEFAULT 'aberto',
    prioridade ENUM('baixa', 'media', 'alta', 'critica') DEFAULT 'media',
    data_abertura TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_fechamento TIMESTAMP NULL,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
    FOREIGN KEY (tecnico_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    FOREIGN KEY (equipamento_id) REFERENCES equipamentos(id) ON DELETE SET NULL
);

-- Tabela de Histórico de Chamados
CREATE TABLE historico_chamados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    chamado_id INT NOT NULL,
    usuario_id INT NOT NULL,
    status_anterior ENUM('aberto', 'em andamento', 'pendente', 'resolvido', 'cancelado'),
    status_novo ENUM('aberto', 'em andamento', 'pendente', 'resolvido', 'cancelado'),
    data_alteracao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    comentario TEXT,
    FOREIGN KEY (chamado_id) REFERENCES chamados(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Tabela de Logs do Sistema
CREATE TABLE logs_sistema (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    acao VARCHAR(255) NOT NULL,
    data_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- Tabela de Métricas e Relatórios
CREATE TABLE metricas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    chamado_id INT NOT NULL,
    tempo_resolucao INT, -- Em minutos
    FOREIGN KEY (chamado_id) REFERENCES chamados(id) ON DELETE CASCADE
);

-- Índices para Otimização
CREATE INDEX idx_chamados_status ON chamados(status);
CREATE INDEX idx_chamados_prioridade ON chamados(prioridade);
CREATE INDEX idx_chamados_cliente ON chamados(cliente_id);

-- Trigger para registrar alterações de status
DELIMITER $$  
CREATE TRIGGER registrar_historico_status  
AFTER UPDATE ON chamados  
FOR EACH ROW  
BEGIN  
    IF OLD.status <> NEW.status THEN  
        INSERT INTO historico_chamados (chamado_id, usuario_id, status_anterior, status_novo, comentario)  
        VALUES (NEW.id, NEW.tecnico_id, OLD.status, NEW.status, 'Status alterado automaticamente.');  
    END IF;  
END $$  
DELIMITER ;  

-- Trigger para atribuir automaticamente chamados a técnicos
DELIMITER $$  
CREATE TRIGGER atribuir_chamado  
AFTER INSERT ON chamados  
FOR EACH ROW  
BEGIN  
    DECLARE tecnico_aleatorio INT;  
    
    -- Seleciona um técnico ativo aleatoriamente
    SELECT id INTO tecnico_aleatorio FROM usuarios WHERE tipo = 'tecnico' ORDER BY RAND() LIMIT 1;  
    
    -- Atualiza o chamado com o técnico atribuído
    UPDATE chamados SET tecnico_id = tecnico_aleatorio WHERE id = NEW.id;  
END $$  
DELIMITER ;  

-- Consulta para o dashboard do técnico
SELECT c.id, c.titulo, c.descricao, c.prioridade, c.status  
FROM chamados c  
WHERE c.tecnico_id = ? AND c.status IN ('aberto', 'em andamento');  
