# 📓 Diário de Bordo - Salgados Dona Sogra

## 📜 Histórico de Sessões e Decisões

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
