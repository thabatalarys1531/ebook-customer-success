-- CS Control — schema do banco de dados
-- Importe este arquivo inteiro no MySQL (phpMyAdmin do cPanel ou linha de comando).
-- Charset utf8mb4 para suportar acentos e emojis sem problema.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------------
-- Usuários que podem entrar no sistema
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(255) NOT NULL,
    email         VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role          ENUM('admin', 'membro') NOT NULL DEFAULT 'admin',
    status        ENUM('ativo', 'inativo') NOT NULL DEFAULT 'ativo',
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------------
-- Catálogo de módulos/telas que podem ser liberados ou bloqueados por usuário
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS permissions (
    id        INT AUTO_INCREMENT PRIMARY KEY,
    key_name  VARCHAR(100) NOT NULL UNIQUE,
    label     VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS user_permissions (
    user_id       INT NOT NULL,
    permission_id INT NOT NULL,
    allowed       TINYINT(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (user_id, permission_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------------
-- Lista de clientes mencionados nas atividades (alimenta o dropdown e a tela
-- "Clientes"). É preenchida automaticamente sempre que um nome novo aparece
-- numa atividade — não precisa ser cadastrada manualmente.
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS clients (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(255) NOT NULL UNIQUE,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------------
-- Atividades — o coração do sistema (reuniões, e-mails, cancelamentos, etc.)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS activities (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    -- 'ligacao' e 'whatsapp' foram adicionados aos 7 tipos originais da especificação
    -- para cobrir os atalhos rápidos do protótipo visual (painel "Registrar atividade rápida").
    type       ENUM('email', 'reuniao', 'ligacao', 'whatsapp', 'migracao', 'cancelamento', 'contrato', 'financeiro', 'outro') NOT NULL,
    client     VARCHAR(255) NOT NULL,
    summary    TEXT NULL,
    status     ENUM('Concluído', 'Aguardando', 'Em andamento') NOT NULL DEFAULT 'Concluído',
    priority   ENUM('Alta', 'Média', 'Baixa') NOT NULL DEFAULT 'Média',
    arr_impact DECIMAL(12, 2) NULL,
    source     ENUM('manual', 'hubspot', 'gmail', 'calendar', 'slack') NOT NULL DEFAULT 'manual',
    INDEX idx_activities_type (type),
    INDEX idx_activities_client (client),
    INDEX idx_activities_created_at (created_at),
    INDEX idx_activities_status_priority (status, priority)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------------
-- Indicadores editados manualmente até as integrações estarem completas
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS stats_manual (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    clientes_self    INT NOT NULL DEFAULT 0,
    migrados         INT NOT NULL DEFAULT 0,
    pendentes        INT NOT NULL DEFAULT 0,
    arr_total        DECIMAL(14, 2) NOT NULL DEFAULT 0,
    retencao_pct     DECIMAL(5, 2) NOT NULL DEFAULT 0,
    churn_pct        DECIMAL(5, 2) NOT NULL DEFAULT 0,
    clientes_ativos  INT NOT NULL DEFAULT 0,
    updated_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------------
-- Espelho dos eventos do Google Calendar (preenchida pela integração futura)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS agenda_events (
    id                INT AUTO_INCREMENT PRIMARY KEY,
    event_date        DATE NOT NULL,
    time              TIME NULL,
    title             VARCHAR(255) NOT NULL,
    client            VARCHAR(255) NULL,
    note              TEXT NULL,
    calendar_event_id VARCHAR(255) NULL UNIQUE,
    created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_agenda_events_date (event_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------------
-- Alertas — gerados automaticamente pelas regras ou criados manualmente
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS alerts (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    activity_id INT NULL,
    client      VARCHAR(255) NULL,
    type        ENUM('prioridade_alta', 'cancelamento', 'sem_retorno', 'manual') NOT NULL DEFAULT 'manual',
    message     TEXT NOT NULL,
    level       ENUM('vermelho', 'amarelo', 'azul') NOT NULL DEFAULT 'amarelo',
    status      ENUM('aberto', 'resolvido') NOT NULL DEFAULT 'aberto',
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    resolved_at DATETIME NULL,
    FOREIGN KEY (activity_id) REFERENCES activities(id) ON DELETE SET NULL,
    INDEX idx_alerts_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------------
-- Dados iniciais
-- ---------------------------------------------------------------------------

INSERT IGNORE INTO stats_manual (id, clientes_self, migrados, pendentes, arr_total, retencao_pct, churn_pct, clientes_ativos)
VALUES (1, 0, 0, 0, 0, 0, 0, 0);

INSERT IGNORE INTO permissions (key_name, label) VALUES
    ('dashboard',     'Minha Central / Dashboard'),
    ('clientes',      'Clientes'),
    ('atividades',    'Atividades'),
    ('agenda',        'Agenda'),
    ('contratos',     'Contratos'),
    ('financeiro',    'Financeiro'),
    ('analytics',     'Analytics'),
    ('relatorios',    'Relatórios'),
    ('configuracoes', 'Configurações'),
    ('integracoes',   'Integrações'),
    ('usuarios',      'Usuários'),
    ('permissoes',    'Permissões');
