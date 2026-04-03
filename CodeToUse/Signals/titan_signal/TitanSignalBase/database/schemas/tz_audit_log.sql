CREATE TABLE IF NOT EXISTS tz_audit_log (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    process_id VARCHAR(80) NOT NULL,
    signal_id VARCHAR(80) NULL,
    action VARCHAR(80) NOT NULL,
    performed_by BIGINT UNSIGNED NULL,
    details JSON NULL,
    created_at TIMESTAMP NULL,
    INDEX idx_tz_audit_log_process (process_id, created_at),
    INDEX idx_tz_audit_log_action (action, created_at)
);
