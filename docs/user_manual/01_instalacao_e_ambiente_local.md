# 🚀 01. Instalação e Ambiente Local (Docker)

Este capítulo instrui o usuário e a equipe técnica sobre como executar o **Ministry Ops** localmente utilizando o ambiente containerizado oficial em **Docker Compose**.

---

## 🛠️ Requisitos Préveis

Para rodar a aplicação localmente, certifique-se de possuir instalado em sua máquina:
1. **Docker Desktop** (com suporte a Docker Compose v2+).
2. Porta local `8080` e porta `3306` livres.

---

## 🐳 Executando a Aplicação com Docker Compose

1. Abra o terminal na raiz do projeto `ministry-ops-php`.
2. Execute o comando de inicialização:

```bash
docker compose up -d --build
```

O Docker Compose iniciará dois serviços:
- `web`: Container Apache + PHP 8.2 contendo o aplicativo Ministry Ops escutando na porta **8080**.
- `db`: Container MySQL 8.0 pré-populado automaticamente com as tabelas (`schema.sql`) e os dados demonstrativos (`seed.sql`).

3. Para verificar se os serviços estão rodando com saúde:

```bash
docker compose ps
```

---

## 🌐 Acesso ao Ambiente de Testes

Após a inicialização, acesse a URL no seu navegador:

👉 **[http://localhost:8080](http://localhost:8080)**

---

## 🔑 Credenciais Demonstrativas de Teste

O ambiente de testes local já vem populado com contas pré-configuradas para validação de fluxos:

| Papel no Sistema | E-mail | Senha Padrão | Escopo de Acesso |
|---|---|---|---|
| **Administrador / Líder Geral** | `admin@ministry-ops.test` | `password123` | Acesso total ao Painel Admin, aprovações, criação de eventos e boletins. |
| **Líder de Equipe** | `leader@ministry-ops.test` | `password123` | Gestão da equipe e aprovação de escalas. |
| **Voluntário A** | `volunteer@ministry-ops.test` | `password123` | Visualização de escalas, confirmações, trocas e check-in. |
| **Voluntário B** | `lucas.voluntario@ministry-ops.test` | `password123` | Candidatura a trocas de voluntários. |

---

## 🛑 Parando o Ambiente

Para interromper o ambiente de testes e encerrar os containers:

```bash
docker compose down
```
