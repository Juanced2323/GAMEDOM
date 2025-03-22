-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 02-03-2025 a las 19:55:39
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Base de datos: `gamedom_users`
--

-- --------------------------------------------------------

USE gamedom_users;

CREATE TABLE IF NOT EXISTS `usuarios` (
  `correo`         VARCHAR(255) NOT NULL,
  `usuario`        VARCHAR(50)  NOT NULL,
  `nombre`         VARCHAR(100) NOT NULL,
  `apellidos`      VARCHAR(100) NOT NULL,
  `edad`           INT(11)      DEFAULT NULL,
  `telefono`       VARCHAR(20)  DEFAULT NULL,
  `password`       VARCHAR(255) NOT NULL,
  `fecha_registro` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  `imagen`         LONGBLOB     DEFAULT NULL,
  PRIMARY KEY (`correo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `usuarios`
  ADD UNIQUE KEY (`usuario`);

COMMIT;