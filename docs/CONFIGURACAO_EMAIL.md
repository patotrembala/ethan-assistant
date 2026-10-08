# Configuração do e-mail do sistema

O Ethan Assistant utiliza uma conta de e-mail exclusiva para enviar códigos de autenticação administrativa e links de redefinição de senha.

## Conta oficial planejada

- E-mail: `ethanassistant1@gmail.com`
- Situação: aguardando o Google liberar a criação da senha de aplicativo.
- Até a liberação, a configuração SMTP existente deve ser mantida para não interromper a recuperação de senha.

## Como concluir a troca

1. Acessar a conta oficial em um dispositivo e uma rede já reconhecidos pelo Google.
2. Ativar a verificação em duas etapas.
3. Abrir a página **Senhas de app** da Conta Google.
4. Criar uma senha de aplicativo com o nome `Ethan Assistant`.
5. Atualizar o arquivo privado `config/mail.local.php` no servidor:
   - `username`: e-mail oficial;
   - `password`: senha de aplicativo de 16 caracteres;
   - `from_email`: e-mail oficial;
   - `from_name`: `Ethan Assistant`.
6. Testar o envio por meio do fluxo **Esqueci minha senha**.
7. Revogar a senha de aplicativo da conta antiga somente depois que o teste for concluído com sucesso.

## Segurança

- Nunca utilizar a senha normal da conta Gmail no sistema.
- Nunca enviar a senha de aplicativo em conversas, documentos ou commits.
- O arquivo `config/mail.local.php` é ignorado pelo Git e deve existir apenas no ambiente configurado.
- Em caso de suspeita de vazamento, revogar a senha de aplicativo imediatamente e gerar outra.
