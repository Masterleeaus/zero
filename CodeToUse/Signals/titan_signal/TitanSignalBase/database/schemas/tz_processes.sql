CREATE TABLE IF NOT EXISTS tz_processes (
    id VARCHAR(80) PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    team_id BIGINT UNSIGNED NULL,
    user_id BIGINT UNSIGNED NULL,
    entity_type VARCHAR(80) NOT NULL,
    domain VARCHAR(80) NOT NULL,
    originating_node VARCHAR(80) NULL,
    current_state VARCHAR(80) NOT NULL DEFAULT 'initiated',
    data JSON NULL,
    context JSON NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_tz_processes_company_state (company_id, current_state),
    INDEX idx_tz_processes_company_domain (company_id, domain)
);
