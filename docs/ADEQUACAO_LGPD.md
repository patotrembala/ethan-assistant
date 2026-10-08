# Avaliação de Adequação à LGPD — Ethan Assistant

**Versão:** 1.1  
**Data da avaliação:** 8 de outubro de 2026  
**Escopo:** código-fonte da branch `frontend-antigravity`, esquema MySQL e implantação atual em hospedagem compartilhada.

> Este documento é uma avaliação técnica e organizacional preliminar. Ele não substitui parecer jurídico, contrato com operador, inventário validado pelo controlador nem auditoria independente.

## 1. Conclusão executiva

O Ethan Assistant **ainda não pode ser declarado integralmente adequado à LGPD**.

O sistema já possui controles positivos, como autenticação, senhas armazenadas com hash, consultas preparadas, proteção CSRF em operações implementadas, conexão HTTPS na versão publicada e separação básica entre administrador e técnico. Porém, ainda existem lacunas relevantes de transparência, governança, atendimento aos titulares, retenção e eliminação, rastreabilidade, resposta a incidentes e segurança da hospedagem.

**Classificação atual: adequação parcial, com pendências de prioridade alta antes do uso com dados pessoais reais em produção.**

## 2. Dados tratados pelo sistema

| Categoria | Exemplos identificados | Titulares prováveis | Finalidade operacional |
| --- | --- | --- | --- |
| Identificação de usuários | nome, e-mail, perfil e estado da conta | administradores e técnicos | autenticação, autorização e atribuição de atividades |
| Credenciais | hash da senha e identificador de sessão | administradores e técnicos | controle de acesso |
| Cadastro de clientes | razão social, CNPJ, endereço, e-mail e telefone | representantes, contatos e empresários individuais | identificação do cliente e execução do atendimento |
| Ordens de serviço | equipamento, defeito, diagnóstico, observações, técnico e prazos | clientes e técnicos | execução, acompanhamento e comprovação do serviço |
| Chamados e pendências | descrição, solução, responsável, datas e estado | clientes e técnicos | organização do suporte e avaliação operacional |
| Indicadores de desempenho | serviços, chamados e pendências finalizados | técnicos | gestão da operação e acompanhamento profissional |

O CNPJ e a razão social de uma pessoa jurídica, isoladamente, não são necessariamente dados pessoais. Entretanto, e-mails nominativos, telefones, endereços, dados de empresários individuais e informações que permitam identificar pessoas naturais entram no escopo da LGPD.

O campo de observações de uma ordem de serviço menciona a possibilidade de registrar “senhas de acesso”. **Senhas de clientes não devem ser armazenadas em texto livre.** Quando uma credencial temporária for indispensável, deve existir procedimento separado, acesso restrito, expiração e eliminação segura após o atendimento.

## 3. Agentes de tratamento e responsabilidades

Antes da entrada em produção, a organização que utilizar o Ethan Assistant deve definir formalmente:

- **Controlador:** empresa ou profissional que decide por que e como os dados serão tratados.
- **Operadores:** fornecedores que tratam dados em nome do controlador, inclusive hospedagem, banco de dados, e-mail, suporte e backup.
- **Encarregado ou canal de privacidade:** contato responsável por receber solicitações de titulares e comunicações da ANPD. Agentes de pequeno porte podem ser dispensados de indicar encarregado, mas devem manter um canal de atendimento ao titular.
- **Responsáveis internos:** pessoas autorizadas a administrar usuários, responder incidentes, restaurar backups e atender pedidos de acesso, correção ou eliminação.

Esses papéis dependem da organização que efetivamente utilizará o sistema e não podem ser definidos apenas pelo código-fonte.

## 4. Bases legais e finalidades

Cada operação de tratamento deve possuir uma finalidade específica e uma base legal registrada. Para este sistema, as bases **possivelmente aplicáveis**, sujeitas à validação do controlador e de orientação jurídica, incluem:

- execução de contrato ou procedimentos preliminares para cadastro do cliente e prestação do serviço;
- cumprimento de obrigação legal ou regulatória para conservação de documentos e registros exigidos por lei;
- legítimo interesse para segurança, prevenção de fraude e gestão interna proporcional, após teste de balanceamento;
- exercício regular de direitos para conservação de evidências relacionadas ao atendimento;
- consentimento somente quando ele for realmente livre, informado, específico e revogável — não deve ser usado como base padrão para toda a operação.

O controlador deve manter um registro das operações de tratamento contendo, no mínimo: finalidade, categoria de dados, titulares, base legal, pessoas com acesso, compartilhamentos, prazo de retenção, medidas de segurança e procedimento de eliminação.

## 5. Controles já identificados

| Controle | Situação | Evidência técnica |
| --- | --- | --- |
| Autenticação obrigatória | Implementado | rotas internas redirecionam usuários sem sessão para `/login` |
| Senhas protegidas | Implementado | uso de `password_hash` e `password_verify` |
| Renovação da sessão no login | Implementado | uso de `session_regenerate_id(true)` |
| Cookie de sessão protegido | Parcial | `HttpOnly`, `SameSite=Lax` e `Secure` quando HTTPS é detectado |
| Proteção contra SQL Injection | Implementado no CRUD real de clientes e autenticação | PDO com consultas preparadas e emulação desativada |
| Proteção CSRF | Implementada nas operações reais atuais | token aleatório validado com `hash_equals` |
| Perfis de acesso | Parcial | perfis `admin` e `tecnico`; ações de clientes são restritas ao administrador |
| Recuperação de senha | Implementado | solicitação depende de aprovação administrativa; o token é aleatório, armazenado como hash, expira em 30 minutos e é de uso único |
| Proteção contra força bruta | Implementado | bloqueio temporário após cinco falhas por conta ou origem pseudonimizada |
| MFA administrativo | Implementado | código temporário enviado ao e-mail do administrador quando o SMTP está configurado |
| Expiração de sessão | Implementado | 30 minutos de inatividade e limite absoluto de 12 horas |
| Trilha de auditoria | Implementado | eventos relevantes, usuário, entidade, data e origem pseudonimizada |
| Canal do titular | Implementado | aviso público, formulário com protocolo e painel administrativo |
| Menor privilégio em clientes | Implementado | técnicos veem somente clientes ligados às suas ordens ou chamados |
| Retenção e backup | Parcial | políticas e scripts criados; execução agendada e testes ainda dependem do controlador |
| Segredos fora do Git | Implementado | arquivos `*.local.php` ignorados pelo repositório |
| Codificação segura na saída | Predominante | uso de `htmlspecialchars` nas telas analisadas |
| HTTPS público | Implementado na publicação atual | aplicação acessível por HTTPS |

## 6. Lacunas e riscos encontrados

### Prioridade crítica

1. **Hospedagem e transferência de dados não avaliadas.** É necessário identificar onde os dados ficam armazenados, quem é o operador, quais suboperadores existem e se ocorre transferência internacional. Também devem ser avaliados os termos contratuais e as garantias do provedor.

### Prioridade alta

1. O aviso e o canal técnico foram criados, mas o controlador e o contato precisam ser formalmente confirmados na configuração de produção.
2. O fluxo registra e acompanha pedidos, mas a equipe ainda precisa definir a verificação de identidade e o procedimento de resposta para cada direito.
3. A política e a limpeza técnica foram criadas; prazos jurídicos de clientes, ordens e documentos ainda precisam de validação.
4. Não há registro formal das operações de tratamento.
5. Não há plano documentado de resposta a incidentes nem procedimento de comunicação à ANPD e aos titulares quando aplicável.
6. A trilha foi implementada, mas sua revisão periódica e proteção operacional precisam ser atribuídas a um responsável.
7. O MFA por e-mail depende da disponibilidade e segurança da conta Gmail; um autenticador TOTP pode ser adotado futuramente.
8. O backup criptografado foi implementado, mas o agendamento, cópia externa e teste mensal ainda precisam ser executados.

### Prioridade média

1. Não foram identificados cabeçalhos de segurança como HSTS, CSP, `X-Content-Type-Options` e política de referência.
2. O logout é realizado por requisição GET; recomenda-se POST com CSRF.
3. A senha exige somente oito caracteres; recomenda-se política mais forte, verificação contra senhas comuns e opção de MFA para administradores.
4. O banco não registra finalidade, base legal, data prevista de descarte nem estado de uma solicitação de titular.
5. Não existe processo de revisão periódica de acessos, desativação de contas ou confirmação da necessidade de cada usuário.
6. Parte dos módulos ainda utiliza dados simulados; os controles de autorização e persistência devem ser reavaliados quando o backend real for implementado.

## 7. Direitos dos titulares

O controlador precisa oferecer um canal gratuito e acessível para o titular solicitar:

- confirmação da existência de tratamento;
- acesso aos dados;
- correção de dados incompletos, inexatos ou desatualizados;
- informação sobre compartilhamentos;
- anonimização, bloqueio ou eliminação quando cabível;
- portabilidade, conforme regulamentação aplicável;
- informação sobre consentimento, recusa e consequências;
- revogação do consentimento, quando essa for a base legal;
- revisão e explicação de decisão automatizada, caso futuramente exista.

O atendimento deve validar a identidade do solicitante, registrar protocolo, preservar os dados de terceiros e documentar a decisão. Pedidos de eliminação não devem apagar informações que precisem ser conservadas por obrigação legal ou para exercício regular de direitos; nesses casos, o dado deve ser bloqueado ou ter seu uso limitado à finalidade de conservação.

## 8. Plano de adequação recomendado

### Etapa 1 — antes de cadastrar dados reais

- mover as sessões e os segredos para fora da raiz pública;
- remover a orientação de registrar senhas de clientes;
- criar aviso de privacidade e canal do titular;
- identificar controlador, operadores, localização dos dados e eventual transferência internacional;
- definir finalidades e bases legais para cada categoria de dado;
- limitar acessos ao mínimo necessário;
- criar política de retenção e descarte;
- estabelecer backups protegidos e testar restauração;
- criar procedimento de resposta a incidentes.

### Etapa 2 — controles no sistema

- implementar limitação de tentativas de login, expiração por inatividade e MFA para administradores;
- criar trilha de auditoria para leitura relevante, criação, alteração, exportação e exclusão;
- criar módulo ou procedimento administrativo para solicitações de titulares;
- permitir exportação estruturada dos dados vinculados a um titular;
- implementar bloqueio, anonimização e eliminação conforme base legal e prazo de retenção;
- incluir cabeçalhos de segurança e reforçar a configuração dos cookies;
- restringir técnicos aos clientes e atendimentos necessários para suas funções, quando aplicável;
- revisar autorização no backend real de todos os módulos.

### Etapa 3 — governança contínua

- manter inventário simplificado das operações de tratamento;
- revisar acessos e contas periodicamente;
- treinar administradores e técnicos sobre privacidade, phishing e sigilo;
- avaliar fornecedores e registrar contratos/instruções de tratamento;
- executar testes de segurança e restauração de backup;
- revisar este documento após mudanças relevantes no sistema ou, no mínimo, anualmente.

## 9. Critérios de aceite para declarar adequação

O projeto somente deve afirmar que está adequado à LGPD quando, além das correções técnicas:

- o controlador e os operadores estiverem identificados;
- as finalidades e bases legais tiverem sido aprovadas;
- o aviso de privacidade estiver publicado e coerente com a prática;
- o canal do titular estiver operacional e testado;
- o inventário de tratamento e a política de retenção estiverem aprovados;
- os contratos com fornecedores tiverem sido avaliados;
- o plano de incidentes estiver definido e testado;
- os riscos críticos e altos deste relatório estiverem tratados ou formalmente aceitos;
- existirem evidências das medidas adotadas e de sua revisão periódica.

## 10. Checklist resumido

- [ ] Identificar controlador, operadores e canal de privacidade.
- [ ] Publicar aviso de privacidade.
- [ ] Registrar finalidades e bases legais.
- [ ] Criar inventário das operações de tratamento.
- [ ] Definir retenção, bloqueio, anonimização e descarte.
- [ ] Implementar atendimento aos direitos dos titulares.
- [ ] Retirar sessões da pasta pública.
- [ ] Proibir armazenamento de senhas de clientes em texto livre.
- [ ] Restringir acessos pelo princípio do menor privilégio.
- [ ] Implementar logs de auditoria e proteção contra força bruta.
- [ ] Definir e testar resposta a incidentes.
- [ ] Avaliar hospedagem, contratos, suboperadores e transferência internacional.
- [ ] Documentar e testar backups.
- [ ] Revisar a adequação periodicamente.

## 11. Referências oficiais

- [Lei nº 13.709/2018 — Lei Geral de Proteção de Dados Pessoais](https://www.planalto.gov.br/ccivil_03/_ato2015-2018/2018/lei/l13709.htm)
- [ANPD — Direitos dos titulares](https://www.gov.br/anpd/pt-br/assuntos/titular-de-dados/direito-dos-titulares)
- [ANPD — Guia de Segurança da Informação para Agentes de Tratamento de Pequeno Porte](https://www.gov.br/anpd/pt-br/centrais-de-conteudo/materiais-educativos-e-publicacoes/processo-guia-orientativo-sobre-seguranca-da-informacao-para-agentes-de-tratamento-de-pequeno-porte.pdf)
- [Resolução CD/ANPD nº 2/2022 — Agentes de Tratamento de Pequeno Porte](https://www.gov.br/anpd/pt-br/acesso-a-informacao/institucional/atos-normativos/regulamentacoes_anpd/resolucao-cd-anpd-no-2-de-27-de-janeiro-de-2022)
- [Resolução CD/ANPD nº 15/2024 — Comunicação de Incidente de Segurança](https://www.gov.br/anpd/pt-br/acesso-a-informacao/institucional/atos-normativos/regulamentacoes_anpd/resolucao-cd-anpd-no-15-de-24-de-abril-de-2024)

## 12. Histórico de revisão

| Versão | Data | Alteração |
| --- | --- | --- |
| 1.1 | 08/10/2026 | Atualização da avaliação após a implantação do fluxo de recuperação com aprovação administrativa. |
| 1.0 | 01/10/2026 | Avaliação técnica inicial do projeto e plano de adequação. |
