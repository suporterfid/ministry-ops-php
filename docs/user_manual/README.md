# 📖 Manual do Usuário Final — Ministry Ops (PHP + MySQL)

Bem-vindo ao **Manual do Usuário Final do Ministry Ops**. Este documento compõe o guia oficial de utilização do sistema de gestão de voluntários, escalas, check-in por geolocalização e engajamento para ministérios e igrejas local.

---

## 🗺️ Sumário da Documentação

| Seção | Título | Descrição |
|---|---|---|
| **01** | [Instalação e Ambiente Local](01_instalacao_e_ambiente_local.md) | Como rodar o sistema localmente com Docker Compose e acessar as credenciais demo. |
| **02** | [Autenticação & Multi-Tenant](02_autenticacao_e_multi_tenant.md) | Cadastro, login, seleção de organização/igreja e solicitações de ingresso. |
| **03** | [Fluxos do Voluntário](03_fluxos_do_voluntario.md) | Gestão da Minha Escala, Troca de Escalas, Check-in por GPS, Gamificação e Boletins. |
| **04** | [Fluxos do Administrador / Líder](04_fluxos_do_administrador.md) | Painel Admin, aprovação de trocas/ingressos, gestão de operações, eventos e escalas. |
| **05** | [Matriz de Exceções e Resolução de Problemas](05_matriz_de_excecoes_e_tratamento.md) | Guia completo visual e prático para identificação e correção de erros e mensagens de exceção. |

---

## 🎯 Visão Geral da Arquitetura do Usuário

O **Ministry Ops** foi desenhado com foco na experiência do voluntário e dos líderes:

- 📱 **Interface Responsiva**: Otimizada para smartphones (com barra de navegação inferior) e desktops (com menu lateral estendido).
- 🏢 **Isolamento Multi-Tenant**: Um voluntário pode pertencer a uma ou mais organizações/igrejas e alternar entre elas em tempo real.
- 📍 **Check-in Geolocalizado (GPS)**: Validação de presença com conferência de raio em metros e janela temporal do culto/evento.
- 🏆 **Gamificação**: Acumulo de pontos por confirmação antecipada, pontualidade e sequências de cultos (streaks).
