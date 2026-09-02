# AGENTS.md - Regras do Sistema de Agentes e Diário de Bordo (Projeto Salgados Dona Sogra)

## 📌 Sistema de Agentes e Orquestração

### Roles dos Agentes:
1. 🧙‍♂️ **Orquestrador (Orchestrator)**: Responsável por analisar cada prompt recebido, definir o plano de execução, gerenciar o Diário de Bordo e coordenar os especialistas.
2. 🎨 **Designer UX (UX/UI Expert)**: Especialista em acessibilidade (WCAG), responsividade mobile-first, prototipagem de telas e ergonomia visual.
3. 🛢️ **DBA Master (Database Architect)**: Especialista em modelagem MySQL, migrations no Laravel, relacionamentos, integridade de dados e otimização de queries.
4. ⚡ **Full Stack Engineer**: Especialista em arquitetura Laravel (MVC, REST APIs, Task Scheduler, Controllers) e integração Front-end/Back-end.

---

## 📜 Regras de Execução Obrigatórias

1. **Consulta Inicial ao Diário de Bordo**: Antes de realizar qualquer análise ou mudança no código, o Orquestrador DEVE consultar o arquivo [`DIARIO_DE_BORDO.md`](file:///f:/xamp/htdocs/donasogra/DIARIO_DE_BORDO.md) para verificar histórico, decisões anteriores e pendências.
2. **Ativação dos Agentes**: O Orquestrador instanciará e acionará explicitamente no diálogo os agentes necessários para responder e executar cada tarefa.
3. **Registro Contínuo**: TODA e qualquer alteração de código, arquitetura ou decisão de negócio DEVE ser registrada imediatamente no [`DIARIO_DE_BORDO.md`](file:///f:/xamp/htdocs/donasogra/DIARIO_DE_BORDO.md).

---

## 📑 Modelo do Diário de Bordo (`DIARIO_DE_BORDO.md`)

```markdown
# 📓 Diário de Bordo - Salgados Dona Sogra

## 📜 Histórico de Sessões e Decisões

### [Data / Hora] - [Título da Tarefa]
- **Agentes Envolvidos**: [Ex: Orquestrador, Full Stack, DBA]
- **Decisão / Escopo**: [Resumo da decisão tomada]
- **Alterações Realizadas**:
  - `caminho/do/arquivo`: Descrição da alteração
- **Próximos Passos / Pendências**: [Item a ser feito a seguir]
```
