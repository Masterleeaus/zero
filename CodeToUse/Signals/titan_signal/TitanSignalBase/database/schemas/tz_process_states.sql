CREATE TABLE IF NOT EXISTS tz_process_states (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    process_id VARCHAR(80) NOT NULL,
    from_state VARCHAR(80) NULL,
    to_state VARCHAR(80) NOT NULL,
    metadata JSON NULL,
    created_at TIMESTAMP NULL,
    INDEX idx_tz_process_states_process (process_id, created_at)
);
