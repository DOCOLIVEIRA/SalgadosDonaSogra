Salgados Dona Sogra – Sistema Integrado (Web & Admin)

Este é o repositório oficial do sistema "Salgados Dona Sogra", projeto desenvolvido em **Laravel 11** que contempla tanto a vitrine pública de produtos (Cardápio e Carrinho de Pedidos) quanto o Painel Administrativo de Gestão (Dashboard, Vendas, Estoque e Usuários).

Tecnologias e Linguagens Utilizadas

- **Linguagem Principal:** PHP 8.3
- **Framework Back-end:** Laravel 11
- **Banco de Dados:** MySQL 8.0
- **Front-end / Estilização:** Tailwind CSS (via CDN) + HTML5 (Blade Templates)
- **Gráficos e BI:** Chart.js
- **Infraestrutura/Deploy:** Docker e Docker Compose (Sail/Custom)

Arquitetura do Projeto (MVC)
Projeto arquitetural **MVC (Model-View-Controller)** nativo do Laravel:

- **Models (`app/Models`):** 
  - `User`: Responsável pela autenticação e gestão de usuários administrativos.
  - `Produto`: Gerencia o catálogo de salgados, preços, imagens e quantidade em estoque.
  - `Pedido`: Controla as vendas realizadas, status do pedido, totais e relacionamento com produtos (itens do pedido).
- **Controllers (`app/Http/Controllers`):** 
  - `Admin/DashboardController`: Lida com as estatísticas de BI (Curva ABC, Gráficos de Venda).
  - `Admin/VendaController`: Gestão do fluxo de pedidos (Aprovação, Cancelamento e Baixa/Retorno de Estoque).
  - `Admin/ProdutoController`: CRUD de produtos.
  - `Admin/UserController`: CRUD de usuários administrativos.
  - `AuthController`: Gerenciamento do ciclo de login/logout com segurança (Middleware Auth).
- **Views (`resources/views`):** 
  - Área pública: `cardapio.blade.php`, `carrinho.blade.php`, `evento.blade.php`.
  - Layout mestre Admin: `layouts/admin.blade.php` (Garante DRY - Don't Repeat Yourself).
  - Páginas Admin: `admin/dashboard.blade.php`, `admin/vendas.blade.php`, etc.

Segurança/Funcionalidades Core

- **Middleware de Autenticação (`auth`):** Todas as rotas sob o prefixo `/admin` são protegidas. Ninguém sem sessão válida tem acesso às informações gerenciais.
- **Integração de Estoque Automática:** Ao aprovar um pedido, o estoque diminui. Ao cancelar, o estoque é estornado perfeitamente.
- **Alertas de Estoque Baixo:** O painel BI alerta instantaneamente caso algum salgado possua menos de 20 unidades.

Como Rodar Localmente (Docker)

1. Clone o repositório ou navegue até a pasta do projeto.
2. Certifique-se de que o **Docker Desktop** esteja rodando.
3. Suba os containers com o Sail ou via docker-compose:
   ```bash
   docker-compose up -d
   ```
4. Instale as dependências:
   ```bash
   composer install
   ```
5. Rode as migrations e seeds:
   ```bash
   php artisan migrate --seed
   ```
6. Acesse a aplicação no navegador em `http://localhost:8080`.
   - **Loja:** `http://localhost:8080/`
   - **Admin:** `http://localhost:8080/admin`

*(Desenvolvido com foco em UI/UX moderna, layout responsivo e gestão eficiente de dados!)*
