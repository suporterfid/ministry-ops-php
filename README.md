# Ministry Ops (PHP + MySQL for Shared Web Hosting)

Versão do projeto [Ministry Ops](https://github.com/suporterfid/ministry-ops) desenvolvida em **PHP 8 + MySQL**, totalmente autônoma e preparada para hospedagem em contas de hospedagem compartilhada padrão (**cPanel**, Hostinger, Locaweb, KingHost) sem qualquer dependência de serviços externos de banco de dados ou backend (sem Supabase, sem Node.js server, sem Deno, etc.).

---

## 🚀 Funcionalidades Principais

- 📱 **Mobile-First & Responsivo**: Design moderno com navegação inferior mobile (bottom tabbar), menu lateral no desktop e tema escuro.
- 🏢 **Multi-Tenant (Multi-Igrejas)**: Suporte a múltiplas igrejas e congregações no mesmo banco de dados com isolamento por tenant e alteração de contexto em tempo real.
- 🔐 **Autenticação Nativa**: Sistema de login por e-mail e senha com hash seguro (`password_hash`), gerenciamento de sessão PHP e controle de acesso baseado em funções (Owner, Org Admin, Team Leader, Volunteer).
- 📅 **Gestão de Escalas (Minha Escala)**: Visualização de próximas escalas, confirmação de presença com pontuação e formulário de recusa com motivo e comentários.
- 🔄 **Troca de Escalas (Swaps)**: Solicitação de troca por voluntários, mural de trocas abertas na igreja, candidatura de voluntários voluntários para cobrir e fila de aprovação da liderança.
- 📍 **Check-in por Geolocalização (GPS)**: Validação de presença em tempo real utilizando a API de geolocalização do navegador (fórmula de Haversine em JS e PHP), com verificação de raio (metros) e janela de horário, além de exceção manual auditada para os líderes.
- 📢 **Boletins & Comunicados**: Feed de avisos da liderança com confirmação de leitura ("Marcar como lido").
- 🏆 **Gamificação & Ranking**: Sistema de pontos por presença/confirmação antecipada, sequências (streaks) de cultos, conquistas (badges) e tabela de classificação (Leaderboard).
- ⚙️ **Painel Administrativo**: Métricas em tempo real (taxa de confirmação, check-ins no dia, pendências), gestão de membros e aprovação de solicitações de ingresso.

---

## 🛠️ Requisitos no Servidor (Shared Hosting / cPanel)

- **Servidor Web**: Apache (com `mod_rewrite` habilitado) ou Nginx.
- **PHP**: Versão 8.0, 8.1, 8.2 ou superior.
- **Extensões PHP**: `pdo_mysql`, `mbstring`, `json`, `session`.
- **Banco de Dados**: MySQL 5.7+, MySQL 8.0+ ou MariaDB 10.x+.

---

## 📦 Como Instalar no cPanel / Hostinger (Produção)

1. **Upload dos Arquivos**:
   - Compacte os arquivos do projeto em um arquivo `.zip`.
   - No cPanel (Gerenciador de Arquivos) ou via FTP, envie o conteúdo para a pasta pública (ex: `public_html/` ou `public_html/ministry-ops/`).

2. **Criação do Banco de Dados**:
   - Acesse o cPanel -> **Bancos de Dados MySQL**.
   - Crie um banco de dados (ex: `usuario_ministryops`).
   - Crie um usuário MySQL e atribua **Todos os Privilégios** ao banco.

3. **Importação do Schema e Dados Iniciais**:
   - Abra o **phpMyAdmin** no cPanel.
   - Selecione o banco de dados criado e acesse a aba **Importar**.
   - Importe primeiro o arquivo `sql/schema.sql`.
   - Em seguida, importe o arquivo `sql/seed.sql`.

4. **Configuração de Conexão**:
   - Edite o arquivo `config/config.php` (ou defina variáveis de ambiente na hospedagem):
     ```php
     define('DB_HOST', 'localhost');
     define('DB_PORT', '3306');
     define('DB_NAME', 'seu_usuario_ministryops');
     define('DB_USER', 'seu_usuario_mysql');
     define('DB_PASS', 'sua_senha_mysql');
     ```

5. **Pronto!**:
   - Acesse o endereço do seu site no navegador.
   - O arquivo `.htaccess` redirecionará as URLs amigáveis automaticamente.

---

## 🐳 Executando Localmente com Docker Compose

Para testar e desenvolver localmente com um comando:

```bash
docker compose up -d --build
```

O container compilará o PHP 8.2 + Apache e iniciará um banco de dados MySQL 8.0 já populado com o `schema.sql` e `seed.sql`.

- **URL da Aplicação**: http://localhost:8080

### Credenciais de Teste Demo (Seed)

| Papel | E-mail | Senha |
|---|---|---|
| **Administrador / Líder** | `admin@ministry-ops.test` | `password123` |
| **Líder de Equipe** | `leader@ministry-ops.test` | `password123` |
| **Voluntário A** | `volunteer@ministry-ops.test` | `password123` |
| **Voluntário B** | `lucas.voluntario@ministry-ops.test` | `password123` |

---

## 📁 Estrutura de Arquivos

```
/
├── index.php                 # Entry Point Front Controller & Rotas
├── config/
│   ├── config.php            # Configurações globais e timezone
│   └── database.php          # Conexão PDO com o MySQL
├── src/
│   ├── Core/                 # Auth, Router e Helpers
│   ├── Models/               # Lógica de acesso ao banco (User, Assignment, Swap, Checkin, etc.)
│   └── Controllers/          # Controladores de telas e fluxos
├── templates/                # Views HTML (Dashboard, Escalas, Trocas, Check-in, Admin, etc.)
├── public/
│   ├── css/style.css         # Sistema de design responsivo dark mode
│   └── js/app.js             # Lógica de geolocalização e modais
├── sql/
│   ├── schema.sql            # Script de criação de tabelas MySQL
│   └── seed.sql              # Dados demonstrativos iniciais
├── .htaccess                 # Regras de URL amigável para Apache
├── Dockerfile                # Imagem Docker PHP 8.2 Apache
└── docker-compose.yml        # Orquestração local PHP + MySQL
```