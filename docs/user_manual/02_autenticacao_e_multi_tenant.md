# 🔐 02. Autenticação e Gestão Multi-Tenant

O **Ministry Ops** fornece um sistema autônomo de autenticação e suporte nativo a múltiplas organizações/igrejas (Multi-Tenant).

---

## 🟢 1. Happy Path: Login de Usuário

### Passos:
1. Acesse `http://localhost:8080/login`.
2. Insira as credenciais (ex: `volunteer@ministry-ops.test` e senha `password123`).
3. Clique em **Entrar no Sistema**.

![Tela de Login](images/01_login_page.png)

> **Resultado Esperado**: O usuário é redirecionado para o Dashboard com a mensagem de boas-vindas: *"Bem-vindo de volta, [Nome do Usuário]!"*.

---

## 🚨 2. Exceções e Tratamento no Login

### Exceção 2.1: Credenciais Incorretas
Se o usuário digitar um e-mail não existente ou uma senha incorreta, o sistema bloqueia o acesso e exibe um alerta de aviso.

![Exceção Credenciais Incorretas](images/02_login_error_credentials.png)

> **Mensagem do Sistema**: `E-mail ou senha incorretos.`

### Exceção 2.2: Campos Obrigatórios Vazios
Ao clicar no botão de login sem preencher os campos de e-mail ou senha, o sistema interrompe a requisição.

![Exceção Campos Vazios](images/03_login_error_empty.png)

> **Mensagem do Sistema**: `Preencha o e-mail e a senha.`

---

## 📝 3. Cadastro de Novo Usuário (Register)

Novos voluntários podem se cadastrar informando seu Nome Completo, E-mail, Senha, Telefone e, opcionalmente, o Código da Organização (ex: `MATRIZ`).

![Tela de Cadastro](images/04_register_page.png)

### Exceção 3.1: E-mail Já Cadastrado
Caso uma pessoa tente se cadastrar utilizando um e-mail previamente registrado no banco de dados:

![Exceção E-mail Duplicado](images/05_register_error_duplicate.png)

> **Mensagem do Sistema**: `Este e-mail já está cadastrado.`

---

## 🏢 4. Solicitação e Alternância de Organização (Tenant)

Quando o voluntário não pertence a nenhuma organização ou deseja ingressar em uma nova congregação, ele acessa a tela de **Solicitar Ingresso**.

![Solicitar Ingresso](images/06_tenant_join_page.png)

### Exceção 4.1: Código de Organização Inexistente
Ao informar um código inválido (ex: `INVALID_CODE_999`):

![Exceção Código de Igreja Inválido](images/07_tenant_join_error_invalid.png)

> **Mensagem do Sistema**: `Organização com o código "INVALID_CODE_999" não foi encontrada.`
