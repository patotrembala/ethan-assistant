# Registro das Operações de Tratamento — Ethan Assistant

> Situação: modelo técnico preenchido com hipóteses iniciais. O controlador deve validar as bases legais e os prazos antes do uso definitivo.

| Operação | Dados | Titulares | Finalidade | Base legal a validar | Acesso | Retenção proposta |
| --- | --- | --- | --- | --- | --- | --- |
| Contas de acesso | nome, e-mail, perfil, hash da senha | técnicos e administradores | autenticação e atribuição de trabalho | execução de contrato/legítimo interesse | administrador; próprio usuário | vínculo ativo + 5 anos de evidências mínimas |
| Clientes | razão social, CNPJ, endereço, e-mail, telefone | contatos e empresários individuais | cadastro e prestação do serviço | execução de contrato | administrador; técnico atribuído | contrato + prazo legal aplicável |
| Ordens e chamados | equipamento, defeito, diagnóstico, datas, responsável | clientes e técnicos | executar e comprovar atendimento | execução de contrato/exercício de direitos | administrador; técnico atribuído | contrato + prazo legal aplicável |
| Desempenho | quantidades, prazos e conclusões | técnicos | gestão operacional proporcional | legítimo interesse a validar | administrador; próprio técnico quando aplicável | 24 meses, com revisão anual |
| Segurança | tentativas pseudonimizadas, sessão, auditoria | usuários | prevenção a fraude e prestação de contas | legítimo interesse/cumprimento de dever de segurança | administrador autorizado | tentativas: 30 dias; auditoria: 24 meses |
| Solicitações LGPD | nome, e-mail, pedido e decisão | titulares | atender direitos e comprovar resposta | cumprimento de obrigação legal | administrador responsável | 5 anos após conclusão, sujeito a validação |

## Regras

- Não registrar senhas ou códigos de clientes em campos livres.
- Coletar somente o necessário para cada atendimento.
- Confirmar a identidade antes de entregar, corrigir, bloquear ou excluir dados.
- Restringir técnicos aos clientes e atendimentos atribuídos.
- Revisar esta matriz anualmente e sempre que surgir nova finalidade ou fornecedor.
