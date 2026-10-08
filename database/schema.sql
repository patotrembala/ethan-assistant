CREATE DATABASE IF NOT EXISTS ethan_assistant
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE ethan_assistant;

CREATE TABLE IF NOT EXISTS usuarios (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  senha_hash VARCHAR(255) NOT NULL,
  perfil ENUM('admin', 'tecnico') NOT NULL DEFAULT 'tecnico',
  ativo TINYINT(1) NOT NULL DEFAULT 1,
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_usuarios_perfil_ativo (perfil, ativo)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS clientes (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  razao_social VARCHAR(180) NOT NULL,
  cnpj CHAR(14) NOT NULL UNIQUE,
  endereco VARCHAR(255) NOT NULL,
  email VARCHAR(190) NOT NULL,
  telefone VARCHAR(20) NULL,
  ativo TINYINT(1) NOT NULL DEFAULT 1,
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_clientes_razao_social (razao_social),
  INDEX idx_clientes_ativo (ativo)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS tipos_servico (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(140) NOT NULL UNIQUE,
  descricao TEXT NULL,
  ativo TINYINT(1) NOT NULL DEFAULT 1,
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS ordens_servico (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cliente_id BIGINT UNSIGNED NOT NULL,
  tipo_servico_id BIGINT UNSIGNED NOT NULL,
  tecnico_id BIGINT UNSIGNED NULL,
  equipamento_tipo VARCHAR(80) NOT NULL,
  equipamento_marca VARCHAR(80) NOT NULL,
  equipamento_modelo VARCHAR(100) NOT NULL,
  defeito TEXT NOT NULL,
  diagnostico TEXT NULL,
  observacoes TEXT NULL,
  prioridade ENUM('baixa', 'media', 'alta') NOT NULL DEFAULT 'media',
  status ENUM('Aberta', 'Em andamento', 'Em diagnostico', 'Aguardando aprovacao', 'Concluida', 'Cancelada') NOT NULL DEFAULT 'Aberta',
  abertura_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  prazo_previsto DATETIME NULL,
  conclusao_em DATETIME NULL,
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_ordens_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(id),
  CONSTRAINT fk_ordens_tipo FOREIGN KEY (tipo_servico_id) REFERENCES tipos_servico(id),
  CONSTRAINT fk_ordens_tecnico FOREIGN KEY (tecnico_id) REFERENCES usuarios(id) ON DELETE SET NULL,
  INDEX idx_ordens_status (status),
  INDEX idx_ordens_tecnico_status (tecnico_id, status),
  INDEX idx_ordens_prazo (prazo_previsto)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS chamados_online (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cliente_id BIGINT UNSIGNED NOT NULL,
  tecnico_id BIGINT UNSIGNED NOT NULL,
  descricao TEXT NOT NULL,
  solucao TEXT NULL,
  status ENUM('Aberto', 'Em atendimento', 'Concluido', 'Cancelado') NOT NULL DEFAULT 'Aberto',
  aberto_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  concluido_em DATETIME NULL,
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_chamados_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(id),
  CONSTRAINT fk_chamados_tecnico FOREIGN KEY (tecnico_id) REFERENCES usuarios(id),
  INDEX idx_chamados_tecnico_status (tecnico_id, status),
  INDEX idx_chamados_aberto_em (aberto_em)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS pendencias (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  titulo VARCHAR(160) NOT NULL,
  descricao TEXT NOT NULL,
  responsavel_id BIGINT UNSIGNED NOT NULL,
  prioridade ENUM('baixa', 'media', 'alta') NOT NULL DEFAULT 'media',
  status ENUM('Pendente', 'Em andamento', 'Concluida', 'Cancelada') NOT NULL DEFAULT 'Pendente',
  prazo DATETIME NULL,
  concluido_em DATETIME NULL,
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_pendencias_responsavel FOREIGN KEY (responsavel_id) REFERENCES usuarios(id),
  INDEX idx_pendencias_responsavel_status (responsavel_id, status),
  INDEX idx_pendencias_prazo (prazo)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS exclusoes_pendentes (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  entidade ENUM('cliente', 'ordem_servico', 'chamado_online', 'pendencia', 'tipo_servico', 'usuario') NOT NULL,
  registro_id BIGINT UNSIGNED NOT NULL,
  solicitado_por BIGINT UNSIGNED NOT NULL,
  solicitado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  executar_em DATETIME NOT NULL,
  cancelado_em DATETIME NULL,
  executado_em DATETIME NULL,
  CONSTRAINT fk_exclusoes_usuario FOREIGN KEY (solicitado_por) REFERENCES usuarios(id),
  INDEX idx_exclusoes_fila (executado_em, cancelado_em, executar_em),
  INDEX idx_exclusoes_registro (entidade, registro_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS password_reset_tokens (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  usuario_id BIGINT UNSIGNED NOT NULL,
  token_hash CHAR(64) NOT NULL UNIQUE,
  solicitado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  expira_em DATETIME NOT NULL,
  utilizado_em DATETIME NULL,
  CONSTRAINT fk_password_reset_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
  INDEX idx_password_reset_usuario (usuario_id, solicitado_em),
  INDEX idx_password_reset_validade (token_hash, utilizado_em, expira_em)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS password_reset_requests (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  usuario_id BIGINT UNSIGNED NOT NULL,
  status ENUM('pendente', 'aprovada', 'rejeitada') NOT NULL DEFAULT 'pendente',
  solicitado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  decidido_em DATETIME NULL,
  decidido_por BIGINT UNSIGNED NULL,
  CONSTRAINT fk_password_reset_request_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
  CONSTRAINT fk_password_reset_request_admin FOREIGN KEY (decidido_por) REFERENCES usuarios(id) ON DELETE SET NULL,
  INDEX idx_password_reset_request_status (status, solicitado_em),
  INDEX idx_password_reset_request_usuario (usuario_id, solicitado_em)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS login_attempts (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email_hash CHAR(64) NOT NULL,
  ip_hash CHAR(64) NOT NULL,
  sucesso TINYINT(1) NOT NULL DEFAULT 0,
  tentativa_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_login_attempts_email (email_hash, tentativa_em),
  INDEX idx_login_attempts_ip (ip_hash, tentativa_em)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS audit_logs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  usuario_id BIGINT UNSIGNED NULL,
  acao VARCHAR(80) NOT NULL,
  entidade VARCHAR(80) NOT NULL,
  registro_id BIGINT UNSIGNED NULL,
  detalhes_json TEXT NULL,
  ip_hash CHAR(64) NOT NULL,
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_audit_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
  INDEX idx_audit_usuario_data (usuario_id, criado_em),
  INDEX idx_audit_entidade_data (entidade, criado_em)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS privacy_requests (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  protocolo VARCHAR(32) NOT NULL UNIQUE,
  nome VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL,
  tipo ENUM('acesso', 'correcao', 'eliminacao', 'bloqueio', 'portabilidade', 'informacao', 'outro') NOT NULL,
  descricao TEXT NOT NULL,
  status ENUM('recebida', 'em_analise', 'concluida', 'recusada') NOT NULL DEFAULT 'recebida',
  solicitado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  concluido_em DATETIME NULL,
  responsavel_id BIGINT UNSIGNED NULL,
  CONSTRAINT fk_privacy_responsavel FOREIGN KEY (responsavel_id) REFERENCES usuarios(id) ON DELETE SET NULL,
  INDEX idx_privacy_status_data (status, solicitado_em),
  INDEX idx_privacy_email_data (email, solicitado_em)
) ENGINE=InnoDB;

INSERT IGNORE INTO tipos_servico (nome, descricao) VALUES
  ('Formatacao com backup', 'Formatacao do sistema com copia previa dos dados do cliente.'),
  ('Formatacao sem backup', 'Formatacao do sistema sem copia de dados.'),
  ('Higienizacao', 'Limpeza interna e externa do equipamento.'),
  ('Manutencao preventiva', 'Verificacao preventiva de hardware e software.'),
  ('Diagnostico', 'Analise para identificacao de falhas.'),
  ('Suporte remoto', 'Atendimento tecnico realizado remotamente.'),
  ('Suporte presencial', 'Atendimento tecnico realizado no local.'),
  ('Manutencao de impressoras', 'Diagnostico e manutencao de impressoras.'),
  ('Redes', 'Instalacao, configuracao e manutencao de redes.');
