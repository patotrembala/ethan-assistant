# Ethan IA

O módulo Ethan IA oferece apoio aos técnicos em três atividades:

1. análise de chamados, com categoria, prioridade, possível causa, diagnóstico sugerido e próximos passos;
2. criação de relatório técnico de encerramento a partir do problema e dos procedimentos informados;
3. chat **Pergunte ao Ethan** para dúvidas de suporte.

## Configuração por ambiente

O código do módulo é o mesmo no computador local e na hospedagem. A chave da API é privada e deve ser configurada separadamente em cada ambiente.

```powershell
Copy-Item config/ai.example.php config/ai.local.php
```

Edite `config/ai.local.php` e preencha `api_key`. O arquivo está protegido pelo `.gitignore` e não deve ser enviado ao GitHub.

Na InfinityFree, crie o arquivo `config/ai.local.php` diretamente pelo gerenciador de arquivos. Sem esse arquivo, a tela continua acessível, mas os botões ficam desativados e exibem **Configuração pendente**.

## Controles de segurança e custo

- A chave nunca é enviada ao navegador.
- As requisições usam `store: false`.
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
