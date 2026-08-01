-- Ministry Ops PHP - Seed Data
-- Demo Tenant, Users, Operations, Roster Assignments, Bulletins, Gamification

-- 1. Tenant Matriz
INSERT INTO tenants (id, name, code, created_at) VALUES 
('t0000000-0000-0000-0000-000000000001', 'Igreja Central Matriz', 'MATRIZ', NOW());

INSERT INTO tenant_settings (id, tenant_id, default_locale, default_timezone) VALUES
('ts000000-0000-0000-0000-000000000001', 't0000000-0000-0000-0000-000000000001', 'pt-BR', 'America/Sao_Paulo');

-- 2. Users (Password: password123)
-- Hash generated via password_hash('password123', PASSWORD_BCRYPT)
-- $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
INSERT INTO users (id, email, password_hash, created_at) VALUES
('u0000000-0000-0000-0000-000000000001', 'admin@ministry-ops.test', '$2y$10$nY5FvQaP8mfJM9z19LEXK.6utROLnMcG.GEN3hG3usU/M1ElMpFj2', NOW()),
('u0000000-0000-0000-0000-000000000002', 'leader@ministry-ops.test', '$2y$10$nY5FvQaP8mfJM9z19LEXK.6utROLnMcG.GEN3hG3usU/M1ElMpFj2', NOW()),
('u0000000-0000-0000-0000-000000000003', 'volunteer@ministry-ops.test', '$2y$10$nY5FvQaP8mfJM9z19LEXK.6utROLnMcG.GEN3hG3usU/M1ElMpFj2', NOW()),
('u0000000-0000-0000-0000-000000000004', 'lucas.voluntario@ministry-ops.test', '$2y$10$nY5FvQaP8mfJM9z19LEXK.6utROLnMcG.GEN3hG3usU/M1ElMpFj2', NOW());

-- 3. Profiles
INSERT INTO profiles (id, full_name, phone) VALUES
('u0000000-0000-0000-0000-000000000001', 'Carlos Admin', '(11) 98765-4321'),
('u0000000-0000-0000-0000-000000000002', 'Mariana Líder', '(11) 97654-3210'),
('u0000000-0000-0000-0000-000000000003', 'Gabriel Voluntário', '(11) 96543-2109'),
('u0000000-0000-0000-0000-000000000004', 'Lucas Santos', '(11) 95432-1098');

-- 4. Tenant Memberships
INSERT INTO tenant_memberships (id, tenant_id, user_id, primary_role, status) VALUES
('tm000000-0000-0000-0000-000000000001', 't0000000-0000-0000-0000-000000000001', 'u0000000-0000-0000-0000-000000000001', 'org_admin', 'active'),
('tm000000-0000-0000-0000-000000000002', 't0000000-0000-0000-0000-000000000001', 'u0000000-0000-0000-0000-000000000002', 'team_leader', 'active'),
('tm000000-0000-0000-0000-000000000003', 't0000000-0000-0000-0000-000000000001', 'u0000000-0000-0000-0000-000000000003', 'volunteer', 'active'),
('tm000000-0000-0000-0000-000000000004', 't0000000-0000-0000-0000-000000000001', 'u0000000-0000-0000-0000-000000000004', 'volunteer', 'active');

-- 5. Organization Structure (Unit, Ministry, Team, Role, Service Area)
INSERT INTO units (id, tenant_id, name) VALUES
('un000000-0000-0000-0000-000000000001', 't0000000-0000-0000-0000-000000000001', 'Campus Principal');

INSERT INTO ministries (id, tenant_id, unit_id, name) VALUES
('mn000000-0000-0000-0000-000000000001', 't0000000-0000-0000-0000-000000000001', 'un000000-0000-0000-0000-000000000001', 'Recepção e Acolhimento'),
('mn000000-0000-0000-0000-000000000002', 't0000000-0000-0000-0000-000000000001', 'un000000-0000-0000-0000-000000000001', 'Mídia e Transmissão');

INSERT INTO teams (id, tenant_id, ministry_id, name) VALUES
('tm-team-0000-0000-0000-000000000001', 't0000000-0000-0000-0000-000000000001', 'mn000000-0000-0000-0000-000000000001', 'Equipe Domingo Manhã'),
('tm-team-0000-0000-0000-000000000002', 't0000000-0000-0000-0000-000000000001', 'mn000000-0000-0000-0000-000000000001', 'Equipe Domingo Noite');

INSERT INTO roles (id, tenant_id, name) VALUES
('rl000000-0000-0000-0000-000000000001', 't0000000-0000-0000-0000-000000000001', 'Recepcionista Nave'),
('rl000000-0000-0000-0000-000000000002', 't0000000-0000-0000-0000-000000000001', 'Operador de Câmera'),
('rl000000-0000-0000-0000-000000000003', 't0000000-0000-0000-0000-000000000001', 'Coordenador de Estacionamento');

INSERT INTO service_areas (id, tenant_id, ministry_id, name, anchor_lat, anchor_lng, checkin_radius_m) VALUES
('sa000000-0000-0000-0000-000000000001', 't0000000-0000-0000-0000-000000000001', 'mn000000-0000-0000-0000-000000000001', 'Portaria Principal', -23.5505200, -46.6333080, 200);

-- 6. Volunteers Setup
INSERT INTO volunteers (id, tenant_id, user_id, is_active) VALUES
('v0000000-0000-0000-0000-000000000001', 't0000000-0000-0000-0000-000000000001', 'u0000000-0000-0000-0000-000000000003', 1),
('v0000000-0000-0000-0000-000000000002', 't0000000-0000-0000-0000-000000000001', 'u0000000-0000-0000-0000-000000000004', 1);

-- 7. Operations, Event Instances & Shifts
INSERT INTO operations (id, tenant_id, name, description, status) VALUES
('op000000-0000-0000-0000-000000000001', 't0000000-0000-0000-0000-000000000001', 'Culto de Celebração de Domingo', 'Operação regular de recepção, mídia e acolhimento nos cultos dominicais.', 'active');

INSERT INTO event_instances (id, tenant_id, operation_id, starts_at, ends_at, location_name, location_lat, location_lng) VALUES
('ev000000-0000-0000-0000-000000000001', 't0000000-0000-0000-0000-000000000001', 'op000000-0000-0000-0000-000000000001', DATE_ADD(CURRENT_DATE(), INTERVAL 1 DAY), DATE_ADD(CURRENT_DATE(), INTERVAL 1 DAY), 'Auditório Principal - Rua das Flores 100', -23.5505200, -46.6333080);

INSERT INTO shifts (id, tenant_id, event_instance_id, name, starts_at, ends_at) VALUES
('sh000000-0000-0000-0000-000000000001', 't0000000-0000-0000-0000-000000000001', 'ev000000-0000-0000-0000-000000000001', 'Turno Manhã (09:00 - 12:00)', DATE_ADD(CURRENT_DATE(), INTERVAL 1 DAY), DATE_ADD(CURRENT_DATE(), INTERVAL 1 DAY));

-- 8. Checkin Location Rule
INSERT INTO checkin_locations (id, tenant_id, service_area_id, event_instance_id, center_lat, center_lng, radius_m, window_start_mins, window_end_mins) VALUES
('cl000000-0000-0000-0000-000000000001', 't0000000-0000-0000-0000-000000000001', 'sa000000-0000-0000-0000-000000000001', 'ev000000-0000-0000-0000-000000000001', -23.5505200, -46.6333080, 300, 120, 120);

-- 9. Decline Reasons
INSERT INTO decline_reasons (id, tenant_id, label) VALUES
('dr000000-0000-0000-0000-000000000001', 't0000000-0000-0000-0000-000000000001', 'Viagem / Compromisso Familiar'),
('dr000000-0000-0000-0000-000000000002', 't0000000-0000-0000-0000-000000000001', 'Motivo de Saúde / Enfermidade'),
('dr000000-0000-0000-0000-000000000003', 't0000000-0000-0000-0000-000000000001', 'Escala de Trabalho no Emprego');

-- 10. Assignments
INSERT INTO assignments (id, tenant_id, unit_id, ministry_id, team_id, operation_id, event_instance_id, shift_id, service_area_id, required_role_id, volunteer_user_id, leader_user_id, instructions, status, starts_at, ends_at) VALUES
('as000000-0000-0000-0000-000000000001', 't0000000-0000-0000-0000-000000000001', 'un000000-0000-0000-0000-000000000001', 'mn000000-0000-0000-0000-000000000001', 'tm-team-0000-0000-0000-000000000001', 'op000000-0000-0000-0000-000000000001', 'ev000000-0000-0000-0000-000000000001', 'sh000000-0000-0000-0000-000000000001', 'sa000000-0000-0000-0000-000000000001', 'rl000000-0000-0000-0000-000000000001', 'u0000000-0000-0000-0000-000000000003', 'u0000000-0000-0000-0000-000000000002', 'Chegar com 20 minutos de antecedência e buscar o crachá na sala de apoio.', 'pending_confirmation', DATE_ADD(CURRENT_TIMESTAMP(), INTERVAL 2 HOUR), DATE_ADD(CURRENT_TIMESTAMP(), INTERVAL 5 HOUR)),
('as000000-0000-0000-0000-000000000002', 't0000000-0000-0000-0000-000000000001', 'un000000-0000-0000-0000-000000000001', 'mn000000-0000-0000-0000-000000000001', 'tm-team-0000-0000-0000-000000000001', 'op000000-0000-0000-0000-000000000001', 'ev000000-0000-0000-0000-000000000001', 'sh000000-0000-0000-0000-000000000001', 'sa000000-0000-0000-0000-000000000001', 'rl000000-0000-0000-0000-000000000001', 'u0000000-0000-0000-0000-000000000004', 'u0000000-0000-0000-0000-000000000002', 'Apoio no portão lateral de pedestres.', 'confirmed', DATE_ADD(CURRENT_TIMESTAMP(), INTERVAL 1 DAY), DATE_ADD(CURRENT_TIMESTAMP(), INTERVAL 1 DAY));

-- 11. Bulletins
INSERT INTO bulletins (id, tenant_id, title, body, status, created_by, created_at) VALUES
('b0000000-0000-0000-0000-000000000001', 't0000000-0000-0000-0000-000000000001', 'Orientação Importante: Uniforme das Escalas Dominicais', 'Lembramos a todos os voluntários que a camiseta oficial do ministério é de uso obrigatório em todas as escalas. Caso precise de uma nova camiseta, procure o seu líder de equipe.', 'published', 'u0000000-0000-0000-0000-000000000001', NOW());

-- 12. Score Rules & Events (Gamification)
INSERT INTO score_rules (id, tenant_id, code, label, points) VALUES
('sr000000-0000-0000-0000-000000000001', 't0000000-0000-0000-0000-000000000001', 'CONFIRM_EARLY', 'Confirmação com Antecedência', 10),
('sr000000-0000-0000-0000-000000000002', 't0000000-0000-0000-0000-000000000001', 'CHECKIN_ON_TIME', 'Check-in Pontual na Escala', 25),
('sr000000-0000-0000-0000-000000000003', 't0000000-0000-0000-0000-000000000001', 'SWAP_HELP', 'Ajudar um Irmão em Troca de Escala', 15);

INSERT INTO score_events (id, tenant_id, user_id, rule_code, points, metadata, created_at) VALUES
('se000000-0000-0000-0000-000000000001', 't0000000-0000-0000-0000-000000000001', 'u0000000-0000-0000-0000-000000000003', 'CONFIRM_EARLY', 10, '{"note": "Bônus boas-vindas"}', NOW()),
('se000000-0000-0000-0000-000000000002', 't0000000-0000-0000-0000-000000000001', 'u0000000-0000-0000-0000-000000000004', 'CHECKIN_ON_TIME', 25, '{"note": "Pontualidade culto anterior"}', NOW());

-- 13. Streaks & Badges
INSERT INTO streaks (id, tenant_id, user_id, streak_type, current_value, best_value) VALUES
('st000000-0000-0000-0000-000000000001', 't0000000-0000-0000-0000-000000000001', 'u0000000-0000-0000-0000-000000000003', 'on_time_checkin', 2, 5),
('st000000-0000-0000-0000-000000000002', 't0000000-0000-0000-0000-000000000001', 'u0000000-0000-0000-0000-000000000004', 'on_time_checkin', 4, 4);

INSERT INTO badges (id, tenant_id, code, family, label, description) VALUES
('bg000000-0000-0000-0000-000000000001', 't0000000-0000-0000-0000-000000000001', 'PUNCTUAL_5', 'attendance', 'Pontualidade 5 Star', 'Realizou 5 check-ins consecutivos no horário sem atrasos.'),
('bg000000-0000-0000-0000-000000000002', 't0000000-0000-0000-0000-000000000001', 'HELPFUL_SWAPPER', 'community', 'Parceiro de Escala', 'Aceitou cobrir a escala de outro voluntário.');

INSERT INTO badge_awards (id, tenant_id, badge_id, user_id) VALUES
('ba000000-0000-0000-0000-000000000001', 't0000000-0000-0000-0000-000000000001', 'bg000000-0000-0000-0000-000000000001', 'u0000000-0000-0000-0000-000000000004');
