-- --------------------------------------------------------
-- Servidor:                     127.0.0.1
-- Versão do servidor:           8.4.3 - MySQL Community Server - GPL
-- OS do Servidor:               Win64
-- HeidiSQL Versão:              12.1.0.6537
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Copiando estrutura do banco de dados para ipsa_estoque
CREATE DATABASE IF NOT EXISTS `ipsa_estoque` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `ipsa_estoque`;

-- Copiando estrutura para tabela ipsa_estoque.emprestimo
CREATE TABLE IF NOT EXISTS `emprestimo` (
  `emp_id` int NOT NULL AUTO_INCREMENT,
  `emp_descricao` text,
  `usu_id` int NOT NULL,
  `emp_data_retirada` datetime NOT NULL,
  `emp_previsao_devolucao` datetime NOT NULL,
  `emp_data_devolucao` datetime DEFAULT NULL,
  `emp_evento` varchar(100) DEFAULT NULL,
  `emp_status` varchar(10) NOT NULL,
  PRIMARY KEY (`emp_id`),
  KEY `usu_id` (`usu_id`),
  CONSTRAINT `emprestimo_ibfk_1` FOREIGN KEY (`usu_id`) REFERENCES `usuario` (`usu_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Copiando dados para a tabela ipsa_estoque.emprestimo: ~0 rows (aproximadamente)

-- Copiando estrutura para tabela ipsa_estoque.equipamento
CREATE TABLE IF NOT EXISTS `equipamento` (
  `equ_id` int NOT NULL AUTO_INCREMENT,
  `equ_nome` varchar(50) NOT NULL,
  `equ_tipo` varchar(100) DEFAULT NULL,
  `equ_modelo` varchar(50) DEFAULT NULL,
  `equ_descricao` text,
  `equ_data_aquisicao` date DEFAULT NULL,
  `equ_quantidade` int unsigned NOT NULL,
  `equ_status` varchar(10) NOT NULL,
  `equ_categoria` varchar(50) DEFAULT NULL,
  `equ_foto` blob,
  PRIMARY KEY (`equ_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Copiando dados para a tabela ipsa_estoque.equipamento: ~0 rows (aproximadamente)

-- Copiando estrutura para tabela ipsa_estoque.item
CREATE TABLE IF NOT EXISTS `item` (
  `item_id` int NOT NULL AUTO_INCREMENT,
  `emp_id` int NOT NULL,
  `equ_id` int NOT NULL,
  `item_quantidade` int unsigned NOT NULL,
  PRIMARY KEY (`item_id`),
  KEY `emp_id` (`emp_id`),
  KEY `equ_id` (`equ_id`),
  CONSTRAINT `item_ibfk_1` FOREIGN KEY (`emp_id`) REFERENCES `emprestimo` (`emp_id`),
  CONSTRAINT `item_ibfk_2` FOREIGN KEY (`equ_id`) REFERENCES `equipamento` (`equ_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Copiando dados para a tabela ipsa_estoque.item: ~0 rows (aproximadamente)

-- Copiando estrutura para tabela ipsa_estoque.usuario
CREATE TABLE IF NOT EXISTS `usuario` (
  `usu_id` int NOT NULL AUTO_INCREMENT,
  `usu_nome` varchar(100) NOT NULL,
  `usu_email` varchar(255) NOT NULL,
  `usu_senha` varchar(255) NOT NULL,
  `usu_nivel` varchar(20) NOT NULL,
  PRIMARY KEY (`usu_id`),
  UNIQUE KEY `usu_email` (`usu_email`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Copiando dados para a tabela ipsa_estoque.usuario: ~0 rows (aproximadamente)

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
