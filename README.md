# Ethan Assistant — Frontend & Documentação de Telas

Interface corporativa desenvolvida para o sistema de gestão técnica **Ethan Assistant**, focada em desktop/PC, com separação de perfis entre **Administrador** e **Técnico**, visual sério, de alta produtividade e total aderência às regras do projeto.

---

## 📁 Estrutura de Arquivos Implementada

```text
ethan-assistant/
├── assets/
│   ├── css/
│   │   ├── base.css          # Design tokens, variáveis, paleta sóbria e tipografia
│   │   ├── layout.css        # Sidebar corporativa, topbar, grid responsivo
│   │   ├── components.css    # Badges, marcador 'Prazo violado', botões, tabelas e modal
│   │   ├── forms.css         # Formulários, inputs, validações e barras de filtro
│   │   └── print.css         # Layout A4 para impressão da OS (RF18)
│   └── js/
│       ├── app.js            # Máscaras (CNPJ, telefone), modais e filtros de tabela
│       ├── undo-timer.js     # Contagem regressiva de 3 min para desfazer exclusões (RN08 / RF14)
│       └── validation.js     # Validações nativas de navegador (datas de OS, CNPJ, obrigatórios)
├── views/
│   ├── layouts/
│   │   ├── header.php        # Cabeçalho HTML e importação de assets
│   │   ├── sidebar.php       # Menu lateral com controle de permissão (Admin vs Técnico)
│   │   ├── topbar.php        # Identificação do usuário logado e atalho de logout
│   │   ├── flash.php         # Alertas e Banner flutuante de Desfazer Exclusão (3 min)
│   │   └── footer.php        # Modal global de confirmação e scripts
│   ├── auth/
│   │   └── login.php         # Tela de autenticação sóbria
│   ├── dashboard/
│   │   └── index.php         # Painel com indicadores individuais, prazos e visão do time
│   ├── clientes/
│   │   ├── index.php         # Listagem e busca de clientes (permissões RF03/RF04)
│   │   ├── form.php          # Formulário de cliente (apenas Admin, com máscaras)
│   │   └── show.php          # Ficha cadastral e histórico de OS
│   ├── ordens/
│   │   ├── index.php         # Listagem de OS com filtros por status/técnico/prioridade
│   │   ├── form.php          # Criação/edição (prioridade readonly para técnicos - RN04)
│   │   ├── show.php          # Detalhes, transição rápida de status e reabertura (RF09)
│   │   └── print.php         # Comprovante de OS para impressão física com assinaturas
│   ├── chamados/
│   │   ├── index.php         # Listagem de chamados online (sem prioridade - RN12)
│   │   └── form.php          # Registro e solução de atendimentos remotos
│   ├── pendencias/
│   │   ├── index.php         # Listagem de pendências internas com indicador de atraso
│   │   └── form.php          # Cadastro com prioridade e prazo limite
│   ├── tipos_servico/
│   │   ├── index.php         # Catálogo de serviços (Admin - RF11)
│   │   └── form.php          # Cadastro/edição de serviços
│   └── usuarios/
│       ├── index.php         # Gestão de técnicos e administradores (Admin - RF19)
│       └── form.php          # Criação, edição e redefinição de senha
├── index.php                 # Front Controller com mock data para pré-visualização das telas
└── docs/                     # Documentação de modelagem e requisitos do projeto
```

---

## 🚀 Como Executar e Visualizar Localmente

Para testar e navegar por todas as telas do sistema antes da conexão com o banco de dados:

1. No terminal, na raiz do projeto, execute o servidor embutido do PHP:
   ```bash
   php -S localhost:8000
   ```
2. Abra seu navegador em:
   - `http://localhost:8000/` (Dashboard com visão de **Administrador**)
   - `http://localhost:8000/?perfil=tecnico` (Dashboard alternando para visão de **Técnico**)

---

## 🎯 Conformidade com os Requisitos do Sistema

1. **Visual Corporativo e Foco em PC (RNF01):** Paleta neutra (slate/navy), tipografia limpa, tabelas de alta densidade informativa e botões discretos.
2. **Separação Rigorosa de Perfis (RN01, RN02, RN03, RN04):**
   - Na listagem de clientes, técnicos visualizam mas **não têm botões** de cadastrar, editar ou excluir.
   - No formulário de Ordem de Serviço, o campo de **prioridade** é editável apenas por administradores; para técnicos, é exibido como somente leitura.
3. **Marcador Discreto "Prazo violado" (RN11 / RF17):**
   - Exibido discretamente em tarefas atrasadas na tabela da Dashboard, listagem de OS e Pendências Internas.
4. **Espera de 3 Minutos e Desfazer Exclusão (RN08 / RF14):**
   - Modal com mensagem específica de confirmação.
   - Banner flutuante no canto inferior com contagem regressiva em tempo real (`undo-timer.js`) e botão imediato **Desfazer Exclusão**.
5. **Indicadores Construtivos (RF16 / RN10):**
   - Apresentação de metas e métricas de produtividade sem rankings competitivos.
6. **Impressão de OS (RF18):**
   - Rota `/ordens/{id}/imprimir` formatada com folha A4 limpa e campos de assinatura para cliente e técnico.
7. **Validações Client-side (RNF05 / Seção 13):**
   - O prazo previsto não pode anteceder a data de abertura da OS.
   - Máscara e validação de 14 dígitos de CNPJ.

---

## 🤝 Integração com o Backend (Codex)

- Os arquivos em `views/` estão preparados para receber variáveis passadas pelos controladores PHP (ou utilizar `$_SESSION['user']`, `$errors`, etc.).
- Os nomes dos campos dos formulários (`name="..."`) correspondem exatamente às colunas do modelo de dados especificado na Seção 7 do projeto.
