# Plataforma de Gerenciamento de Chamados NetoNerd (ITSM)

> **AVISO DE CONFIDENCIALIDADE**
>
> Este software e seu código-fonte são propriedade intelectual da **Neto Nerd Soluções Digitais LTDA**. O conteúdo deste repositório é estritamente confidencial e destina-se exclusivamente ao uso por funcionários e contratados autorizados da empresa. A reprodução, distribuição, modificação ou divulgação não autorizada deste material, no todo ou em parte, é estritamente proibida e sujeita às penalidades legais cabíveis.
>
> **© 2025 Neto Nerd Soluções Digitais LTDA. Todos os direitos reservados.**

---

## 1. Sumário Executivo

O projeto **"New NetoNerd"** é a plataforma interna de Gerenciamento de Serviços de TI (ITSM) da NetoNerd. O sistema foi desenvolvido para centralizar, rastrear e gerenciar todas as solicitações de suporte técnico, incidentes e problemas relacionados à infraestrutura de TI e aos ativos de computadores dos clientes.

A plataforma otimiza o fluxo de trabalho do suporte, desde a abertura do chamado pelo usuário final até sua resolução pelo técnico, fornecendo métricas, rastreabilidade e um histórico completo para auditoria e melhoria contínua dos processos de TI.

## 2. Visão Geral da Arquitetura

A aplicação foi construída sobre uma arquitetura **monolítica modular** em **PHP**, projetada para segmentar claramente as responsabilidades e os domínios de acesso, garantindo segurança e manutenibilidade. A interação com o sistema ocorre através de três portais distintos:

1.  **Portal do Cliente (`/` ou `/cliente`):** Interface para que os usuários finais (clientes) possam abrir novos chamados de suporte, acompanhar o status de suas solicitações, interagir com os técnicos e consultar uma base de conhecimento para autoatendimento.
2.  **Painel do Técnico (`/tecnico`):** Ambiente de trabalho para a equipe de suporte. Permite a visualização de filas de chamados, atribuição de tickets, atualização de status, registro de soluções e comunicação interna.
3.  **Painel de Administração (`/admin`):** O centro de controle do sistema. Utilizado pela gestão de TI para configurar o sistema, gerenciar usuários (clientes e técnicos), definir categorias de chamados, estabelecer SLAs (futuro) e extrair relatórios gerenciais sobre a performance do suporte.

## 3. Stack Tecnológico

A seleção de tecnologias priorizou a robustez, a performance e a utilização de ferramentas consolidadas no mercado, alinhadas à expertise técnica da equipe.

* **Linguagem de Backend:** **PHP 8.4** (utilizando paradigmas procedural e orientado a objetos)
* **Banco de Dados:** **MySQL 8**
* **Servidor Web:** Apache 2.4 ou Nginx, com `mod_rewrite` habilitado.
* **Frontend:** HTML5, CSS3, JavaScript (Vanilla JS) para interatividade no cliente.
* **Gerenciador de Dependências:** **Composer** para bibliotecas de backend.
* **Bibliotecas de Terceiros:**
    * **PHPMailer:** Utilizado para todo o sistema de notificação via e-mail (abertura de chamado, atualizações, redefinição de senha).

## 4. Esquema do Banco de Dados

A estrutura de dados é o alicerce do sistema de chamados. O esquema foi projetado para garantir a integridade relacional entre usuários, chamados e suas interações.

```sql
-- TABELA DE USUÁRIOS: Centraliza clientes, técnicos e administradores.
CREATE TABLE `usuarios` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nome_completo` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) UNIQUE NOT NULL,
  `senha_hash` VARCHAR(255) NOT NULL,
  `tipo_usuario` ENUM('cliente', 'tecnico', 'admin') NOT NULL,
  `departamento` VARCHAR(100), -- Opcional, para clientes corporativos
  `telefone` VARCHAR(20),
  `ativo` BOOLEAN DEFAULT TRUE,
  `data_criacao` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- TABELA DE CATEGORIAS: Para classificar os chamados.
CREATE TABLE `categorias_chamado` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nome` VARCHAR(100) NOT NULL UNIQUE,
  `descricao` TEXT
);

-- TABELA DE CHAMADOS: A entidade principal do sistema.
CREATE TABLE `chamados` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `id_cliente` INT NOT NULL,
  `id_tecnico_atribuido` INT NULL, -- Pode iniciar nulo e ser atribuído depois
  `id_categoria` INT NOT NULL,
  `titulo` VARCHAR(255) NOT NULL,
  `descricao` TEXT NOT NULL,
  `status` ENUM('aberto', 'em_atendimento', 'aguardando_cliente', 'resolvido', 'fechado', 'cancelado') NOT NULL DEFAULT 'aberto',
  `prioridade` ENUM('baixa', 'media', 'alta', 'urgente') NOT NULL DEFAULT 'media',
  `data_abertura` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `data_ultima_atualizacao` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `data_fechamento` DATETIME NULL,
  FOREIGN KEY (`id_cliente`) REFERENCES `usuarios`(`id`),
  FOREIGN KEY (`id_tecnico_atribuido`) REFERENCES `usuarios`(`id`),
  FOREIGN KEY (`id_categoria`) REFERENCES `categorias_chamado`(`id`)
);

-- TABELA DE RESPOSTAS/COMENTÁRIOS: Registra toda a comunicação dentro de um chamado.
CREATE TABLE `respostas_chamado` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `id_chamado` INT NOT NULL,
  `id_usuario` INT NOT NULL,
  `resposta` TEXT NOT NULL,
  `tipo_resposta` ENUM('publica', 'interna') DEFAULT 'publica', -- Respostas internas são visíveis apenas para técnicos/admins
  `data_resposta` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`id_chamado`) REFERENCES `chamados`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`id_usuario`) REFERENCES `usuarios`(`id`)
);

-- TABELA DE ANEXOS: Para upload de screenshots, logs, etc.
CREATE TABLE `anexos_chamado` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `id_chamado` INT NOT NULL,
  `id_resposta` INT NULL, -- Vincula a um comentário específico, se aplicável
  `nome_arquivo` VARCHAR(255) NOT NULL,
  `caminho_arquivo` VARCHAR(512) NOT NULL,
  `tipo_mime` VARCHAR(100),
  `data_upload` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`id_chamado`) REFERENCES `chamados`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`id_resposta`) REFERENCES `respostas_chamado`(`id`) ON DELETE SET NULL
);

-- BASE DE CONHECIMENTO: Artigos para autoatendimento dos clientes.
CREATE TABLE `base_conhecimento` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `id_categoria` INT NOT NULL,
  `titulo` VARCHAR(255) NOT NULL,
  `conteudo` LONGTEXT NOT NULL,
  `data_criacao` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `ultima_atualizacao` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`id_categoria`) REFERENCES `categorias_chamado`(`id`)
);
```

## 5. Configuração do Ambiente de Desenvolvimento

Procedimentos padronizados para configuração do ambiente de desenvolvimento local.

### Pré-requisitos
* Stack de desenvolvimento (XAMPP, Laragon, Docker com LAMP).
* **PHP >= 8.2**
* **MySQL >= 8.0**
* **Composer 2.x**
* **Git**

### Procedimento de Instalação
1.  **Clone o Repositório:**
    ```bash
    git clone [URL_DO_REPOSITORIO_INTERNO] New_NetoNerd
    cd New_NetoNerd
    ```
2.  **Instale as Dependências PHP:**
    ```bash
    composer install
    ```
3.  **Setup do Banco de Dados:**
    a. Crie um banco de dados MySQL (`netonerd_itsm_db`).
    b. Importe o schema do banco de dados a partir do arquivo `database/schema.sql`.
    c. Configure a conexão no arquivo `config/database.php`.
4.  **Configuração do Servidor Web:**
    * Configure o `DocumentRoot` do seu Virtual Host para a raiz do projeto.
    * Assegure que o `mod_rewrite` do Apache está habilitado para o roteamento.
5.  **Acesso:** Acesse a URL local configurada (ex: `http://netonerd-itsm.local`).

## 6. Detalhamento de Módulos e Funcionalidades

### 6.1. Portal do Cliente
* **Autenticação:** Login, registro e recuperação de senha.
* **Abertura de Chamado:** Formulário intuitivo para registrar novas solicitações, incluindo seleção de categoria, título, descrição detalhada e upload de anexos.
* **Meus Chamados:** Listagem de todos os chamados abertos pelo usuário, com status, prioridade e data da última atualização.
* **Detalhe do Chamado:** Visualização do histórico completo de interações, adição de novos comentários e anexos.
* **Base de Conhecimento:** Busca e leitura de artigos para solucionar problemas comuns sem a necessidade de abrir um chamado.

### 6.2. Painel do Técnico
* **Dashboard:** Visão geral com estatísticas pessoais (chamados resolvidos, tempo médio de resposta) e filas de chamados.
* **Fila de Chamados:** Visualização de chamados não atribuídos, permitindo que o técnico se atribua a um novo chamado.
* **Meus Chamados Atribuídos:** Foco nos chamados sob sua responsabilidade, com filtros por status e prioridade.
* **Interface de Atendimento:**
    * Alteração de status e prioridade.
    * Adição de respostas públicas (para o cliente) e internas (para a equipe).
    * Registro de tempo gasto.
    * Atribuição do chamado a outro técnico ou equipe.
    * Vinculação de artigos da base de conhecimento na resposta.

### 6.3. Painel de Administração
* **Dashboard Gerencial:** Visão macro da operação de suporte com KPIs (Key Performance Indicators) como volume de chamados, tempo de primeira resposta, tempo de resolução, etc.
* **Gerenciamento de Usuários:** CRUD completo para todas as contas de usuários (clientes, técnicos, admins).
* **Gerenciamento de Chamados:** Visão completa de todos os chamados do sistema, com poder para editar, reatribuir ou fechar qualquer ticket.
* **Configuração do Sistema:**
    * CRUD para Categorias.
    * Configuração de templates de e-mail.
    * (Roadmap) Definição de Acordos de Nível de Serviço (SLAs).
* **Gestão da Base de Conhecimento:** CRUD para os artigos de autoajuda.

## 7. Protocolos de Segurança

A segurança dos dados dos clientes e da operação é primordial.

* **Autenticação e Senhas:** As senhas são tratadas com `password_hash()` (BCRYPT). O acesso às áreas restritas é protegido por um sistema de sessão seguro.
* **Prevenção de SQL Injection:** Todas as queries ao banco de dados são parametrizadas usando `mysqli` prepared statements.
* **Proteção contra XSS:** Dados inseridos por usuários são sanitizados na entrada e escapados na saída (`htmlspecialchars`) para mitigar ataques de Cross-Site Scripting.
* **Controle de Acesso:** A lógica de backend valida rigorosamente o tipo de usuário (`tipo_usuario`) em cada requisição para garantir que as ações sejam permitidas apenas para os perfis corretos.

---
**© 2025 Neto Nerd Soluções Digitais LTDA. Todos os direitos reservados.**
