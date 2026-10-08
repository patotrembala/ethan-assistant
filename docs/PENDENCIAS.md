# Pendências do Ethan Assistant

Lista de funcionalidades e configurações que ainda precisam ser concluídas.

## 1. IA do Ethan Assistant

- Definir quais atividades a IA realizará dentro do sistema.
- Planejar como ela ajudará técnicos e administradores sem acessar informações além das permissões do usuário.
- Definir a tecnologia, os limites de uso, a proteção de dados e a interface da funcionalidade.
- Implementar e testar somente depois da definição do escopo.

## 2. Configuração do e-mail oficial

- Conta planejada: `ethanassistant1@gmail.com`.
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
