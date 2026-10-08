-- Controles de força bruta e trilha de auditoria.

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
