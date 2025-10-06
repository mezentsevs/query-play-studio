CREATE TABLE IF NOT EXISTS sandbox_info (
    id SERIAL PRIMARY KEY,
    message VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO sandbox_info (message) VALUES ('PostgreSQL Sandbox is ready for user experiments');
