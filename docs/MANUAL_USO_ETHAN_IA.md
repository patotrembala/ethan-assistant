# Manual de Uso — Ethan IA (rascunho para o manual geral)

> Este capítulo deve ser incorporado ao Manual do Usuário geral após a finalização das demais telas. As indicações de captura de tela ficam reservadas para a versão final.

## 1. Finalidade

O Ethan é o assistente de diagnóstico técnico do Ethan Assistant. Ele ajuda o responsável pelo atendimento a:

- interpretar a descrição de um problema;
- levantar hipóteses e possíveis causas;
- sugerir testes e próximos passos;
- indicar categoria e prioridade quando houver informações suficientes;
- organizar um resumo técnico para o encerramento do chamado.

As respostas são sugestões de apoio. O técnico deve validar cada orientação antes de aplicá-la. O Ethan não acessa equipamentos, não executa comandos e não altera ordens de serviço automaticamente.

## 2. Quem pode acessar

O módulo pode ser utilizado pelos perfis **Administrador** e **Técnico**. Cada usuário precisa entrar com sua própria conta do Assistance. A autorização do serviço de IA permanece protegida no servidor e não é exibida aos usuários.

## 3. Como iniciar um chamado assistido

1. Entre no Ethan Assistant com uma conta autorizada.
2. No menu lateral, selecione **Ethan IA**.
3. Confirme se o cabeçalho apresenta o estado **Disponível**.
4. No campo de mensagem, descreva o problema, os sintomas observados, o equipamento envolvido e o que já foi testado.
5. Se preferir, escolha um dos exemplos apresentados na tela, como **Computador sem vídeo**, **Sem acesso à internet** ou **Impressora offline**.
6. Selecione **Enviar** ou pressione `Enter`.
7. Aguarde a resposta antes de enviar uma nova mensagem.

**Captura de tela prevista:** tela inicial do Ethan com o campo de mensagem e os exemplos de chamados.

### Exemplo de primeira mensagem

> O computador liga e os ventiladores funcionam, mas não aparece imagem. Já testei outro cabo HDMI e outro monitor.

Uma boa descrição reduz perguntas desnecessárias e melhora a qualidade das sugestões.

## 4. Como conduzir a conversa

O atendimento funciona como uma conversa entre técnicos:

1. Leia a hipótese inicial e as perguntas apresentadas pelo Ethan.
2. Realize somente testes adequados ao ambiente e às suas permissões.
3. Informe o resultado de cada teste de forma objetiva.
4. Continue até obter uma hipótese suficientemente fundamentada e os próximos passos.
5. Quando o problema for resolvido, informe a solução aplicada e o resultado da validação final.

O Ethan considera o histórico recente da conversa. Para quebrar uma linha sem enviar a mensagem, use `Shift + Enter`.

**Captura de tela prevista:** conversa contendo uma mensagem do técnico e uma resposta do Ethan.

## 5. Categoria, prioridade e diagnóstico

Quando houver informações suficientes, o Ethan pode apresentar:

- categoria do atendimento;
- prioridade sugerida;
- possível causa;
- diagnóstico sugerido;
- testes recomendados;
- próximos passos;
- alertas de segurança ou risco de perda de dados.

Esses dados devem ser revisados pelo técnico antes de serem registrados na ordem de serviço. Uma hipótese da IA não deve ser apresentada ao cliente como diagnóstico definitivo sem testes.

## 6. Relatório técnico de encerramento

Ao finalizar o atendimento, informe na conversa:

- o problema confirmado;
- os testes realizados;
- a causa identificada, quando conhecida;
- a solução aplicada;
- o resultado da validação final.

Em seguida, solicite ao Ethan um resumo pronto para encerramento. Exemplo:

> O problema foi resolvido após a substituição do cabo de rede. O link voltou a operar e o acesso à internet foi validado. Gere o relatório técnico de encerramento.

Revise o texto antes de copiá-lo para a ordem de serviço. Remova qualquer informação desnecessária e confirme se as ações descritas correspondem ao que realmente foi executado.

## 7. Iniciar um novo chamado

Para encerrar a conversa atual e começar outro atendimento:

1. Selecione **Novo chamado** no canto superior da tela.
2. Confirme que o histórico anterior deixou de aparecer.
3. Digite a descrição do novo problema.

O botão limpa a conversa da sessão atual. Antes de utilizá-lo, copie para a ordem de serviço qualquer resumo que ainda precise ser registrado.

## 8. Cuidados de segurança e privacidade

Nunca informe no chat:

- senhas;
- códigos de autenticação;
- chaves de API ou tokens;
- dados bancários;
- documentos pessoais desnecessários;
- informações de clientes que não sejam essenciais ao diagnóstico.

Prefira descrever funções e sintomas. Em vez de informar o nome completo de uma pessoa, use expressões como “usuário do financeiro” ou “estação da recepção”, quando isso for suficiente.

Comandos e procedimentos sugeridos devem ser avaliados antes da execução. Em ações com risco de perda de dados, faça backup e obtenha a autorização necessária.

## 9. Mensagens e problemas comuns

### Ethan indisponível ou configuração pendente

O módulo ainda não reconheceu uma configuração válida. O usuário deve comunicar o administrador; não deve inserir chaves diretamente na tela.

### A conexão com o ChatGPT oscilou

Aguarde alguns segundos e envie novamente a mesma mensagem. Não clique repetidamente em **Enviar**.

### Limite temporário atingido

Aguarde o período indicado antes de fazer outra solicitação. O limite existe para proteger a conta e a hospedagem.

### Página informa limite de recursos da hospedagem

Aguarde alguns minutos e atualize a página. Evite abrir várias conversas ou enviar diversas mensagens ao mesmo tempo. Se o problema se repetir com frequência, o administrador deve avaliar uma hospedagem com mais recursos.

### A resposta parece incorreta ou incompleta

Não aplique a sugestão automaticamente. Acrescente sintomas, resultados de testes e contexto do equipamento. Se ainda houver dúvida, continue o diagnóstico por procedimentos técnicos convencionais ou peça apoio a outro profissional.

## 10. Boas práticas

- Faça uma pergunta por vez e aguarde a resposta.
- Informe resultados reais, não suposições.
- Diferencie o relato do usuário daquilo que foi comprovado em teste.
- Valide conectividade, funcionamento e estabilidade antes de encerrar.
- Registre na ordem de serviço somente o que realmente foi realizado.
- Use o Ethan como apoio, não como substituto da decisão técnica.

## 11. Roteiro de teste de aceitação

| Cenário | Procedimento | Resultado esperado |
| --- | --- | --- |
| Abertura válida | Descrever um problema com sintomas e teste já realizado | Ethan responde com hipótese inicial e perguntas objetivas |
| Continuação | Informar o resultado de um teste solicitado | Ethan considera o histórico e sugere o próximo passo |
| Encerramento | Confirmar a solução e pedir um relatório | Ethan gera um resumo técnico coerente com a conversa |
| Campo vazio | Tentar enviar sem escrever | O navegador impede o envio |
| Clique repetido | Selecionar **Enviar** mais de uma vez durante o processamento | O botão permanece bloqueado enquanto a mensagem é enviada |
| Nova conversa | Selecionar **Novo chamado** | O histórico da sessão é limpo e a tela inicial reaparece |
| Dado sensível | Tentar incluir senha, token ou chave | O usuário é orientado a remover o dado; o manual proíbe esse uso |
| Oscilação externa | Simular falha ou demora do serviço | O sistema apresenta mensagem clara e permite nova tentativa controlada |

## 12. Capturas necessárias para a versão final

Quando o Manual do Usuário geral for produzido, incluir imagens de:

1. acesso ao módulo pelo menu lateral;
2. tela inicial e sugestões de chamados;
3. conversa em andamento;
4. classificação e próximos passos;
5. relatório técnico de encerramento;
6. botão **Novo chamado**;
7. mensagem de erro ou indisponibilidade, sem expor credenciais ou dados pessoais.
