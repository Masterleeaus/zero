CREATE TABLE IF NOT EXISTS tz_approval_queue (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    process_id VARCHAR(80) NOT NULL,
    signal_id VARCHAR(80) NULL,
    company_id BIGINT UNSIGNED NOT NULL,
    team_id BIGINT UNSIGNED NULL,
    user_id BIGINT UNSIGNED NULL,
    approval_chain JSON NOT NULL,
    approved_by JSON NULL,
    current_approver VARCHAR(120) NULL,
    status VARCHAR(32) NOT NULL DEFAULT 'pending',
    decision_meta JSON NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    decided_at TIMESTAMP NULL,
    UNIQUE KEY uniq_tz_approval_queue_process (process_id),
    INDEX idx_tz_approval_queue_company_status (company_id, status, created_at)
);
