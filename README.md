# Ethan Assistant

Sistema web em PHP e MySQL para organizar o fluxo de trabalho de técnicos de informática. O projeto possui autenticação, perfis de administrador e técnico, dashboard operacional e CRUD de clientes. As demais áreas estão estruturadas para a continuidade do desenvolvimento.

## Recursos atuais

- Login e primeiro acesso do administrador.
- Recuperação de senha com aprovação do administrador e link temporário enviado via SMTP.
- Controle de sessão e separação de permissões.
- MFA por e-mail para administradores, bloqueio contra força bruta e expiração por inatividade.
- Trilha de auditoria e acesso de técnicos limitado aos clientes atribuídos.
- Aviso de privacidade, canal do titular e painel administrativo de solicitações LGPD.
- Dashboard para acompanhamento operacional.
- Cadastro, visualização, edição e exclusão de clientes.
- Telas de ordens de serviço, chamados, pendências, serviços e usuários.
- Layout corporativo responsivo, com foco em computadores.
- Impressão de ordem de serviço.
- Banco de dados com consultas preparadas contra SQL Injection.
- Documentação do projeto em DOCX e PDF na pasta `docs`.
- Avaliação técnica e plano de adequação à LGPD em [`docs/ADEQUACAO_LGPD.md`](docs/ADEQUACAO_LGPD.md).

## Requisitos

- Git.
- XAMPP com Apache, PHP e MySQL.
- Navegador atualizado.

## Instalação no Windows

Abra o PowerShell e execute:

```powershell
git clone -b frontend-antigravity https://github.com/patotrembala/ethan-assistant.git
cd ethan-assistant
Copy-Item config/database.example.php config/database.local.php
Copy-Item config/auth.example.php config/auth.local.php
Copy-Item config/mail.example.php config/mail.local.php
Copy-Item config/security.example.php config/security.local.php
```

Os arquivos terminados em `.local.php` são privados e não são enviados ao GitHub. Edite:

- `config/database.local.php`: conexão do MySQL local. No XAMPP padrão, o exemplo já utiliza usuário `root` sem senha.
- `config/auth.local.php`: nome e e-mail autorizados para criar o primeiro administrador.
- `config/mail.local.php`: servidor SMTP utilizado para enviar links de recuperação de senha.
- `config/security.local.php`: chave aleatória privada usada para pseudonimizar origens nos registros de segurança.

Na hospedagem gratuita da InfinityFree, a função `mail()` não está disponível. Configure um SMTP externo. Para Gmail, ative a verificação em duas etapas e utilize uma senha de aplicativo; nunca coloque a senha normal da conta no arquivo.

## Criar o banco local

1. Inicie o Apache e o MySQL no painel do XAMPP.
2. Abra `http://localhost/phpmyadmin`.
3. Entre na aba **Importar**.
4. Selecione `database/schema.sql` e confirme a importação.

O script cria o banco `ethan_assistant`, suas oito tabelas e o catálogo inicial de serviços.

Em bancos existentes, importe em ordem `database/migrations/20261001_password_reset_tokens.sql`, `database/migrations/20261008_password_reset_approval.sql` e `database/migrations/20261008_security_controls.sql`.

## Executar o sistema

Na pasta do projeto, execute:

```powershell
C:\xampp\php\php.exe -S 127.0.0.1:8125 router.php
```

Depois abra:

- `http://127.0.0.1:8125/primeiro-acesso` para cadastrar o primeiro administrador.
- `http://127.0.0.1:8125/login` para entrar depois do cadastro.

## Trabalhar em mais de um computador

Antes de começar:

```powershell
git switch frontend-antigravity
git pull origin frontend-antigravity
```

Ao terminar:

```powershell
git add .
git commit -m "Descreva a alteração"
git push origin frontend-antigravity
```

Não envie arquivos `config/*.local.php` nem arquivos de sessão. Eles já estão protegidos pelo `.gitignore`.

## Estrutura principal

```text
assets/       estilos e scripts do navegador
config/       conexão, validações e configurações de exemplo
database/     script SQL de criação do banco
docs/         documentação do projeto
src/          classes de acesso aos dados
scripts/      manutenção de retenção e backup criptografado
storage/      diretório legado bloqueado; sessões são gravadas fora da raiz pública
tests/        testes de fumaça
views/        telas organizadas por módulo
index.php     entrada e rotas da aplicação
router.php    roteador usado pelo servidor local do PHP
```

## Hospedagem

O sistema também foi preparado para a InfinityFree. As credenciais de produção ficam somente no servidor e nunca devem ser adicionadas ao repositório.
