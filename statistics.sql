-- phpMyAdmin SQL Dump
-- version 5.1.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Creato il: Mar 07, 2022 alle 11:33
-- Versione del server: 10.4.22-MariaDB
-- Versione PHP: 7.4.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `from_excel`
--
CREATE DATABASE IF NOT EXISTS `from_excel` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `from_excel`;

-- --------------------------------------------------------

--
-- Struttura della tabella `proc`
--

DROP TABLE IF EXISTS `proc`;
CREATE TABLE `proc` (
  `num` int(10) UNSIGNED NOT NULL,
  `mag` varchar(40) DEFAULT NULL,
  `iscr` date DEFAULT NULL,
  `defin` date DEFAULT NULL,
  `tipo_def` varchar(60) DEFAULT NULL,
  `chiave` char(21) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `reati`
--

DROP TABLE IF EXISTS `reati`;
CREATE TABLE `reati` (
  `proc` int(10) UNSIGNED NOT NULL,
  `fonte` varchar(60) DEFAULT NULL,
  `anno_fonte` int(10) UNSIGNED DEFAULT NULL,
  `num_fonte` int(10) UNSIGNED DEFAULT NULL,
  `art` int(10) UNSIGNED DEFAULT NULL,
  `dupl` varchar(20) DEFAULT NULL,
  `sub` varchar(20) DEFAULT NULL,
  `tipo` varchar(16) DEFAULT NULL,
  `aggr` varchar(16) DEFAULT NULL,
  `iter` int(10) UNSIGNED DEFAULT NULL,
  `chiave` char(21) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
