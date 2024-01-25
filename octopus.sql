-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : mar. 05 sep. 2023 à 11:53
-- Version du serveur : 10.4.24-MariaDB
-- Version de PHP : 7.4.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `octopus`
--

-- --------------------------------------------------------

--
-- Structure de la table `dlc_basket`
--

CREATE TABLE `dlc_basket` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `client_family_id` int(10) UNSIGNED DEFAULT NULL,
  `dlc_category_id` int(10) UNSIGNED DEFAULT NULL,
  `restaurant_id` int(10) UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `icon` varchar(200) DEFAULT NULL,
  `color` varchar(10) NOT NULL DEFAULT '#44b03a',
  `print_label` varchar(20) NOT NULL DEFAULT 'Ent./Fab. le',
  `print_format` varchar(20) NOT NULL DEFAULT 'default',
  `active` tinyint(3) UNSIGNED NOT NULL,
  `is_fixed` tinyint(4) NOT NULL DEFAULT 0,
  `show_images` tinyint(1) NOT NULL DEFAULT 1,
  `print_minutes` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `dlc_basket_product`
--

CREATE TABLE `dlc_basket_product` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `client_family_id` int(10) UNSIGNED DEFAULT NULL,
  `restaurant_id` int(10) UNSIGNED DEFAULT NULL,
  `dlc_basket_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `dlc_second` smallint(5) UNSIGNED DEFAULT NULL,
  `dlc_second_type` varchar(10) DEFAULT NULL,
  `position` smallint(5) UNSIGNED NOT NULL,
  `ls_description` varchar(128) DEFAULT NULL,
  `ls_text1` varchar(72) DEFAULT NULL,
  `ls_text2` varchar(72) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `dlc_basket`
--
ALTER TABLE `dlc_basket`
  ADD PRIMARY KEY (`id`),
  ADD KEY `client_family_id` (`client_family_id`),
  ADD KEY `restaurant_id` (`restaurant_id`),
  ADD KEY `dlc_category_id` (`dlc_category_id`);

--
-- Index pour la table `dlc_basket_product`
--
ALTER TABLE `dlc_basket_product`
  ADD PRIMARY KEY (`id`),
  ADD KEY `client_family_id` (`client_family_id`),
  ADD KEY `restaurant_id` (`restaurant_id`),
  ADD KEY `dlc_basket_id` (`dlc_basket_id`),
  ADD KEY `product_id` (`product_id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `dlc_basket`
--
ALTER TABLE `dlc_basket`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `dlc_basket_product`
--
ALTER TABLE `dlc_basket_product`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `dlc_basket`
--
ALTER TABLE `dlc_basket`
  ADD CONSTRAINT `dlc_basket_ibfk_1` FOREIGN KEY (`client_family_id`) REFERENCES `client_families` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `dlc_basket_ibfk_2` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `dlc_category_id` FOREIGN KEY (`dlc_category_id`) REFERENCES `dlc_category` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
