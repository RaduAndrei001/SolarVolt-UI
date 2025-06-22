-- Tabela panouri solare cu suport pentru MAC, IP, status, last_update
CREATE TABLE IF NOT EXISTS solar_panels (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    mac VARCHAR(32) UNIQUE NOT NULL,
    ip VARCHAR(32) DEFAULT NULL,
    status ENUM('active','inactive','faulty') DEFAULT 'active',
    last_update TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
);


-- Tabela date panou (include predicted_battery)
CREATE TABLE IF NOT EXISTS solar_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    panel_id INT NOT NULL,
    light_voltage FLOAT NOT NULL,
    battery_voltage FLOAT NOT NULL,
    predicted_battery FLOAT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (panel_id) REFERENCES solar_panels(id)
);


-- Tabela date meteo (include predicted_temperature)
CREATE TABLE IF NOT EXISTS meteo_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    temperature FLOAT NOT NULL,
    pressure FLOAT NOT NULL,
    `condition` VARCHAR(32) NOT NULL,
    predicted_temperature FLOAT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
