-- Tabla para recordar sesión de usuario (token persistente)
CREATE TABLE IF NOT EXISTS user_remember_tokens (
    idUser INT NOT NULL,
    token VARCHAR(128) NOT NULL,
    expires DATETIME NOT NULL,
    PRIMARY KEY (idUser),
    UNIQUE KEY token_unique (token),
    FOREIGN KEY (idUser) REFERENCES users(idUser) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
