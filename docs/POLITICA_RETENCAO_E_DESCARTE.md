# Política de Retenção e Descarte

## Prazos operacionais propostos

- Tentativas de login pseudonimizadas: 30 dias.
- Tokens de recuperação usados ou expirados: 30 dias.
- Sessões: até 30 minutos de inatividade ou 12 horas de duração total.
- Logs de auditoria: 24 meses.
- Indicadores individuais: 24 meses, com revisão anual de necessidade.
- Solicitações de titulares e evidências da resposta: 5 anos após conclusão.
- Clientes, ordens, chamados e documentos fiscais: prazo contratual, tributário, consumerista ou de exercício de direitos validado pelo controlador.

## Procedimento

1. O administrador revisa mensalmente os registros elegíveis.
2. Dados sujeitos a obrigação de conservação são bloqueados para outras finalidades, não apagados.
3. Dados sem necessidade ou obrigação são eliminados ou anonimizados.
4. A ação é registrada na trilha de auditoria.
5. Cópias de backup seguem o mesmo ciclo na próxima rotação.

O script `scripts/maintenance.php` elimina apenas registros técnicos com prazo definido. Ele não apaga automaticamente clientes ou ordens, pois isso depende da base legal e do prazo aplicável a cada caso.
