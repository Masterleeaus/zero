CREATE TABLE IF NOT EXISTS tz_signal_queue (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    signal_id VARCHAR(80) NOT NULL,
    payload JSON NOT NULL,
    broadcast_at TIMESTAMP NULL,
    broadcast_status VARCHAR(32) DEFAULT 'pending',
    retry_count INT DEFAULT 0,
    last_error JSON NULL,
    created_at TIMESTAMP NULL,
    UNIQUE KEY uniq_tz_signal_queue_signal (signal_id),
    INDEX idx_tz_signal_queue_status (broadcast_status, created_at)
);
