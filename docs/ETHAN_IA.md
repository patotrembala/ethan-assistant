# Ethan IA

O módulo Ethan IA oferece apoio aos técnicos em três atividades:

1. análise de chamados, com categoria, prioridade, possível causa, diagnóstico sugerido e próximos passos;
2. criação de relatório técnico de encerramento a partir do problema e dos procedimentos informados;
3. chat **Pergunte ao Ethan** para dúvidas de suporte.

## Opção 1 — uso por chave de API

O código do módulo é o mesmo no computador local e na hospedagem. A chave da API é privada e deve ser configurada separadamente em cada ambiente.

```powershell
Copy-Item config/ai.example.php config/ai.local.php
```

Edite `config/ai.local.php` e preencha `api_key`. O arquivo está protegido pelo `.gitignore` e não deve ser enviado ao GitHub.

Na InfinityFree, crie o arquivo `config/ai.local.php` diretamente pelo gerenciador de arquivos. Sem esse arquivo, a tela continua acessível, mas os botões ficam desativados e exibem **Configuração pendente**.

## Opção 2 — uso autorizado do plano ChatGPT

Para a demonstração escolar, o administrador pode autorizar sua própria conta pelo fluxo oficial **Entrar com ChatGPT**. Essa opção não reutiliza cookies, não pede a senha ao Ethan Assistant e não compartilha a conta com os técnicos.

1. No computador do administrador, execute:

```powershell
node scripts\chatgpt_oauth_node.mjs
```

2. O navegador abrirá automaticamente; confirme a autorização na tela da OpenAI.
3. O conector criará `config/chatgpt.oauth.local.json` fora do Git. O identificador de registro auxiliar também permanece privado.
4. Em hospedagem remota, transfira esse arquivo por um canal privado para a pasta `config` e nunca o publique no repositório.

O sistema renova o acesso automaticamente enquanto a autorização permanecer válida. Por segurança, quando essa opção está ativa, todas as rotas do Ethan IA ficam restritas ao perfil administrador. O arquivo contém credenciais sensíveis: não deve ser aberto em capturas de tela, enviado em conversas nem copiado para computadores de terceiros.

O fluxo segue os requisitos oficiais: callback local em `127.0.0.1`, PKCE, validação de `state`, `nonce` e assinatura do ID Token, escopo `chatgpt.tokens.use.direct`, entrada em lista, omissão dos campos não aceitos na prévia, requisições com `store: false` e `stream: true` e rotação do token de renovação.

## Controles de segurança e custo

- A chave nunca é enviada ao navegador.
- As requisições usam `store: false`.
- No modo ChatGPT, as respostas são consumidas por streaming e os tokens são renovados fora do navegador.
- A conta pessoal conectada só pode ser usada pelo administrador.
- Cada sessão pode realizar até 12 solicitações em dez minutos.
- O chat mantém somente as seis últimas interações na sessão e pode ser limpo pelo usuário.
- Chaves que comecem com `sk-` e tokens `Bearer` são removidos do texto antes do envio.
- As ações são registradas na auditoria, mas o conteúdo técnico não é copiado para o log.
- A interface orienta a não informar senhas, tokens ou dados pessoais desnecessários.

## Limites funcionais

As respostas são sugestões e devem ser validadas por um técnico. O módulo não executa comandos, não acessa equipamentos, não altera chamados automaticamente e não substitui testes técnicos.

Enquanto os chamados utilizarem dados simulados, a análise recebe a descrição digitada manualmente. Quando a persistência dos chamados estiver concluída, os campos poderão ser preenchidos diretamente com os dados do registro autorizado ao usuário.

## Teste local

O teste usa um transporte simulado e não consome créditos de API:

```powershell
C:\xampp\php\php.exe -d zend.assertions=1 -d assert.exception=1 tests\smoke_ethan_ai.php
```
