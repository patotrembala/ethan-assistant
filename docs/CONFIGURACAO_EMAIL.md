# Configuração do e-mail do sistema

O Ethan Assistant utiliza uma conta de e-mail exclusiva para enviar códigos de autenticação administrativa e links de redefinição de senha.

## Conta oficial

- E-mail: `ethanassistant1@gmail.com`
- Remetente exibido: `Ethan Assistant`
- Situação: a aplicação está preparada; falta cadastrar no servidor uma senha de aplicativo gerada nessa conta.
- O e-mail pessoal não deve ser usado em novas instalações.

## Como concluir a troca

1. Acessar a conta oficial em um dispositivo e uma rede já reconhecidos pelo Google.
2. Ativar a verificação em duas etapas.
3. Abrir a página **Senhas de app** da Conta Google.
4. Criar uma senha de aplicativo com o nome `Ethan Assistant`.
5. Copiar `config/mail.example.php` para `config/mail.local.php` no servidor e preencher:
   - `username`: e-mail oficial;
   - `password`: senha de aplicativo de 16 caracteres;
   - `from_email`: e-mail oficial;
   - `from_name`: `Ethan Assistant`.
6. Testar o envio por meio do fluxo **Esqueci minha senha**.
7. Revogar a senha de aplicativo da conta antiga somente depois que o teste for concluído com sucesso.

## Configuração esperada

```php
<?php

return [
    'host' => 'smtp.gmail.com',
    'port' => 587,
    'encryption' => 'tls',
    'username' => 'ethanassistant1@gmail.com',
    'password' => 'COLE_A_SENHA_DE_APLICATIVO_DIRETAMENTE_NO_SERVIDOR',
    'from_email' => 'ethanassistant1@gmail.com',
    'from_name' => 'Ethan Assistant'
];
```

No InfinityFree, esse arquivo deve ficar em `htdocs/config/mail.local.php`. A senha não deve ser copiada para o GitHub nem enviada em conversa.

## Segurança

- Nunca utilizar a senha normal da conta Gmail no sistema.
- Nunca enviar a senha de aplicativo em conversas, documentos ou commits.
- O arquivo `config/mail.local.php` é ignorado pelo Git e deve existir apenas no ambiente configurado.
- Em caso de suspeita de vazamento, revogar a senha de aplicativo imediatamente e gerar outra.
