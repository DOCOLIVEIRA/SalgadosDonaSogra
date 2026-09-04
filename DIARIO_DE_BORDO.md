# 📓 Diário de Bordo - Salgados Dona Sogra

## 📜 Histórico de Sessões e Decisões

### [2026-09-04] - Migração para Arquitetura Framework Laravel + Docker + WCAG + API REST
- **Agentes Envolvidos**: Orquestrador, Full Stack, DBA Master, Designer UX
- **Decisão / Escopo**: 
  - Instalação e migração completa do projeto para o **Laravel Framework**.
  - Criação de imagem Docker customizada em [`Dockerfile`](file:///f:/xamp/htdocs/donasogra/Dockerfile) com suporte ao Composer, PHP 8.3 e extensões.
  - Implementação do padrão **MVC** (Models, Views em Blade, Controllers).
  - Criação de **Migrations** e **Seeders** (`ProdutoSeeder`) para gerenciamento declarativo do banco de dados no Laravel.
  - Criação de **APIs REST** JSON (`/api/produtos` e `/api/pedidos`) com reserva temporária de estoque no banco.
  - Criação da rotina em segundo plano / Artisan Command [`LimparPedidosExpirados.php`](file:///f:/xamp/htdocs/donasogra/app/Console/Commands/LimparPedidosExpirados.php) (`php artisan pedidos:limpar-expirados`) para cancelar automaticamente pedidos sem confirmação após 24h e devolver salgados ao estoque.
  - Implementação de **Acessibilidade WCAG 2.1** (atalho skip-link, marcação semântica ARIA, controles touch mobile de 44px+) na View Blade [`cardapio.blade.php`](file:///f:/xamp/htdocs/donasogra/resources/views/cardapio.blade.php).
  - Criação de **Testes Automatizados de Integração** (PHPUnit) em [`ProdutoApiTest.php`](file:///f:/xamp/htdocs/donasogra/tests/Feature/ProdutoApiTest.php) (execução com sucesso 100% OK).
- **Alterações Realizadas**:
  - [`Dockerfile`](file:///f:/xamp/htdocs/donasogra/Dockerfile) & [`docker-compose.yml`](file:///f:/xamp/htdocs/donasogra/docker-compose.yml): Atualizados com Composer e Apache Rewrite.
  - [`database/migrations/`](file:///f:/xamp/htdocs/donasogra/database/migrations): Migrations de `produtos`, `pedidos`, `itens_pedido` e `configuracoes`.
  - [`app/Models/`](file:///f:/xamp/htdocs/donasogra/app/Models): Models Eloquent `Produto`, `Pedido`, `ItemPedido` com relacionamentos.
  - [`app/Http/Controllers/Api/`](file:///f:/xamp/htdocs/donasogra/app/Http/Controllers/Api): `ProdutoController` e `PedidoController` REST APIs.
  - [`app/Console/Commands/LimparPedidosExpirados.php`](file:///f:/xamp/htdocs/donasogra/app/Console/Commands/LimparPedidosExpirados.php): Comando de automação e devolução de estoque.
  - [`resources/views/cardapio.blade.php`](file:///f:/xamp/htdocs/donasogra/resources/views/cardapio.blade.php): Template Blade responsivo com WCAG.
  - [`tests/Feature/ProdutoApiTest.php`](file:///f:/xamp/htdocs/donasogra/tests/Feature/ProdutoApiTest.php): Suíte de testes automatizados.
- **Próximos Passos / Pendências**:
  - Conectar o envio final da compra com a API do WhatsApp a partir da resposta da API REST de pedidos.
  - Desenvolver o Painel Administrativo em Laravel para gestão de pedidos e estoque.

---

### [2026-09-02] - Inicialização do Diário de Bordo e Estruturação do Ambiente Docker
- **Agentes Envolvidos**: Orquestrador, Full Stack, DBA Master, Designer UX
- **Decisão / Escopo**: 
  - Clonagem do repositório GitHub `DOCOLIVEIRA/SalgadosDonaSogra` na branch `framework`.
  - Configuração do ambiente 100% Docker local com dois containers (`donasogra-web` em PHP 8.3/Apache e `donasogra-db` em MySQL 8.0).
  - Ajuste no [`db/schema.sql`](file:///f:/xamp/htdocs/donasogra/db/schema.sql) e [`php/config.php`](file:///f:/xamp/htdocs/donasogra/php/config.php) para conexão automática no container.
  - Alinhamento dos requisitos para transição do projeto para o framework **Laravel**, mantendo paridade entre Docker local e HostGator em produção.
  - Definição da regra de negócio de cancelamento automático de pedidos expirados (Laravel Scheduler/Commands) para proteger o estoque.
  - Criação da regra de orquestração de agentes em [`AGENTS.md`](file:///f:/xamp/htdocs/donasogra/AGENTS.md).
- **Alterações Realizadas**:
  - `AGENTS.md`: Regras de orquestração dos 4 agentes (Orquestrador, Designer UX, DBA Master, Full Stack).
  - `docker-compose.yml`: Configuração dos serviços `web` (PHP 8.3 Apache) e `db` (MySQL 8.0).
  - `php/config.php`: Detecção dinâmica do host `db` quando rodando em Docker.
  - `db/schema.sql`: Remoção de `CREATE DATABASE` fixo para usar o schema injetado no container.
- **Próximos Passos / Pendências**:
  - Inicializar a estrutura limpa do Laravel na branch `framework`.
  - Criar Migrations e Models no Laravel a partir do schema MySQL existente.
