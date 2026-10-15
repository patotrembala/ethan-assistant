# Pendências do Ethan Assistant

Lista de funcionalidades e configurações que ainda precisam ser concluídas.

## 1. IA do Ethan Assistant — MVP implementado, configuração pendente

- Configurar uma chave privada da API no ambiente local e na hospedagem.
- Testar a comunicação de saída da InfinityFree com a API.
- Conectar a análise aos chamados reais quando a persistência desse módulo for concluída.
- Criar avaliações com exemplos reais anonimizados para medir a qualidade das respostas.

## 2. Configuração do e-mail oficial

- Conta planejada: `ethanassistant1@gmail.com`.
- Reativar `admin_mfa_enabled` após configurar e testar a senha de aplicativo do e-mail oficial.
- Aguardar o Google liberar a criação da senha de aplicativo.
- Substituir o remetente atual no arquivo privado `config/mail.local.php`.
- Testar o envio de códigos administrativos e links de redefinição de senha.
- Revogar a senha de aplicativo da conta antiga somente após a confirmação do novo envio.

## 3. Limitações dos usuários não administradores

- Revisar todas as telas e rotas acessíveis aos técnicos.
- Impedir criação, edição e exclusão de clientes por usuários não administradores.
- Permitir ao técnico visualizar somente os clientes e atividades relacionados ao seu trabalho.
- Manter a criação e a assunção de ordens de serviço conforme as regras do projeto.
- Bloquear áreas administrativas, auditoria, desempenho geral, usuários, tipos de serviço e solicitações LGPD.
- Aplicar as restrições no backend, e não apenas ocultar botões na interface.
- Criar testes de autorização para tentativas de acesso direto pelas URLs.
