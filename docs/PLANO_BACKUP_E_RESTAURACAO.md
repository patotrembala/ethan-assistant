# Plano de Backup e Restauração

## Rotina mínima

- Backup criptografado diário do banco.
- Retenção sugerida: 7 diários, 4 semanais e 6 mensais, após validação jurídica e operacional.
- Cópia separada da hospedagem principal.
- Acesso limitado aos administradores responsáveis.
- Chave de criptografia armazenada fora do Git e fora do próprio backup.

## Teste

Uma vez por mês, restaurar a cópia em ambiente isolado, validar contagem de tabelas, autenticação, clientes e ordens, registrar resultado e eliminar com segurança o ambiente de teste.

O script `scripts/backup_database.php` gera cópia JSON criptografada com AES-256-GCM em uma pasta privada acima da raiz pública. Ele exige execução por linha de comando e a variável `ETHAN_BACKUP_KEY`.
