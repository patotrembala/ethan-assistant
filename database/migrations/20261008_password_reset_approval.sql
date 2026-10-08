-- Aprovação administrativa para solicitações de redefinição de senha.

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
