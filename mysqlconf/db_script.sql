-- Copiando estrutura do banco de dados para inss_db
CREATE DATABASE IF NOT EXISTS `inss_db`
USE `inss_db`;

-- Copiando estrutura para tabela inss_db.register
CREATE TABLE IF NOT EXISTS `register` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(500) DEFAULT NULL,
  `nis` varchar(14) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nis` (`nis`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;