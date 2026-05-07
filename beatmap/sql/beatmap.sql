-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 07-Maio-2026 às 23:04
-- Versão do servidor: 10.4.32-MariaDB
-- versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `beatmap`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `admin_accounts`
--

CREATE TABLE `admin_accounts` (
  `id` int(11) NOT NULL,
  `name` varchar(120) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_login_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `admin_accounts`
--

INSERT INTO `admin_accounts` (`id`, `name`, `email`, `password_hash`, `is_active`, `last_login_at`, `created_at`) VALUES
(2, 'Administrador', 'admin@gmail.com', '$2y$10$WzcV/CjG1ZXCyja7sDWn6uw7K6NNV5CAnTUYqNuw7T/wZqAL4KOZy', 1, '2026-03-26 15:49:02', '2026-02-25 18:20:09');

-- --------------------------------------------------------

--
-- Estrutura da tabela `artists`
--

CREATE TABLE `artists` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `genre` varchar(100) DEFAULT NULL,
  `council` varchar(100) DEFAULT NULL,
  `district` varchar(100) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `is_confirmed` tinyint(1) DEFAULT 0,
  `moderation_status` enum('pending','approved','rejected','banned') NOT NULL DEFAULT 'approved',
  `confirmation_token` varchar(128) DEFAULT NULL,
  `token_expires` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `profile_picture` varchar(255) DEFAULT 'assets/default-avatar.png',
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL,
  `social_links` text DEFAULT NULL,
  `preview1` varchar(255) DEFAULT NULL,
  `preview2` varchar(255) DEFAULT NULL,
  `preview3` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `artists`
--

INSERT INTO `artists` (`id`, `name`, `email`, `password_hash`, `genre`, `council`, `district`, `bio`, `is_confirmed`, `moderation_status`, `confirmation_token`, `token_expires`, `created_at`, `profile_picture`, `reset_token`, `reset_expires`, `social_links`, `preview1`, `preview2`, `preview3`) VALUES
(109, 'Dillaz', 'dillaz@gmail.com', '$2y$10$jPilPpWQm/K.xLi1oWXJIukaBEk95pvYCoK7Sq6C4nAvUY9e.uiX2', 'Hip-Hop, Rap', 'Cascais', 'Lisboa', 'Rapper português conhecido pelo estilo urbano e letras introspectivas.', 1, 'approved', NULL, NULL, '2026-01-15 08:30:45', 'uploads/avatars/avatar_69a8ceece98312.25464585.png', NULL, NULL, '{\"instagram\": \"https://www.instagram.com/dillaz75/\", \"youtube\": \"https://www.youtube.com/@dillaz5681\", \"spotify\": \"https://open.spotify.com/intl-pt/artist/15p1isN7VcGsjeSq8s9YeP\"}', 'https://open.spotify.com/intl-pt/track/6vBuEFImAx36hDYiYqBtkZ?si=c71f56bf9cb74e26', 'https://open.spotify.com/intl-pt/track/0zT2mRIny4wzpMfNRVCWyI?si=d081b6992ede40e0', 'https://open.spotify.com/intl-pt/track/14eHoQNk1yzNVMRWfGrsFg?si=06f10da765a64095'),
(110, 'Piruka', 'piruka@gmail.com', '$2y$10$jPilPpWQm/K.xLi1oWXJIukaBEk95pvYCoK7Sq6C4nAvUY9e.uiX2', 'Hip-Hop, Rap', 'Cascais', 'Lisboa', 'MC português com forte presença no hip-hop nacional e mensagens pessoais nas músicas.', 1, 'approved', NULL, NULL, '2026-01-20 14:22:10', 'uploads/avatars/avatar_69a8cf715900d5.65376095.png', NULL, NULL, '{\"instagram\": \"https://www.instagram.com/pirukamc/\", \"youtube\": \"https://www.youtube.com/@piruka4885\", \"spotify\": \"https://open.spotify.com/intl-pt/artist/5iZ6jMDkRa7RKLQplJuQUC\"}', 'https://open.spotify.com/intl-pt/track/3FYnoDgy5YzFKa6bwbz4cu?si=8307de593f7f4f77', 'https://open.spotify.com/intl-pt/track/7nt9d9KSHEyOAy9BuLcloO?si=92c4411fa86f46f5', 'https://open.spotify.com/intl-pt/track/6i8QA10F1m89nO9y9AwKeo?si=fd39ea473a6843f0'),
(111, 'Toy', 'toy@gmail.com', '$2y$10$jPilPpWQm/K.xLi1oWXJIukaBEk95pvYCoK7Sq6C4nAvUY9e.uiX2', 'Música Popular', 'Setúbal', 'Setúbal', 'Cantor português de música popular, conhecido por temas festivos e grande presença em palco.', 1, 'approved', NULL, NULL, '2026-01-28 09:15:33', 'uploads/avatars/avatar_69a8cffd0ff977.09516149.png', NULL, NULL, '{\"instagram\":\"https://www.instagram.com/toy_bam/\",\"spotify\":\"https://open.spotify.com/intl-pt/artist/3ggsRmBGV01RYdNu8pRWJd\"}', 'https://open.spotify.com/intl-pt/track/3303s19agScDaCsPYdCu7i?si=e9173ccd2dfd422a', 'https://open.spotify.com/intl-pt/track/42RynyXHcGYj6Cvwrh7eIZ?si=99ce10662b434d78', 'https://open.spotify.com/intl-pt/track/7xli9G60XhgsIB3hnKiTHe?si=a6e501a4929b4851'),
(112, '2busy', '2busy@gmail.com', '$2y$10$jPilPpWQm/K.xLi1oWXJIukaBEk95pvYCoK7Sq6C4nAvUY9e.uiX2', 'Rap, Trap', 'Vila Nova de Famalicão', 'Braga', 'Artista da nova geração com sonoridade entre o rap e o trap.', 1, 'approved', NULL, NULL, '2026-02-01 16:45:22', 'uploads/avatars/avatar_69a8d06322da85.23919247.jpg', NULL, NULL, '{\"spotify\": \"https://open.spotify.com/intl-pt/artist/4U7YuiZDLuwqlrlKTWHuwE\", \"soundcloud\": \"https://soundcloud.com/way2busy\",\r\n\"instagram\":\r\n\"https://www.instagram.com/15lugg\"}', 'https://open.spotify.com/intl-pt/track/2iT4mPhSRHgDuPnq4LhTMr?si=e3835032bc6c4d0a', 'https://open.spotify.com/intl-pt/track/7M0EJ370MnOIjsVvSmlr38?si=91e3ad1589084573', 'https://open.spotify.com/intl-pt/track/0ZQwvMC6cgEkkr3MZJcTQF?si=355bd0564d154332'),
(113, 'Plutônio', 'plutonio@gmail.com', '$2y$10$jPilPpWQm/K.xLi1oWXJIukaBEk95pvYCoK7Sq6C4nAvUY9e.uiX2', 'Hip-Hop, Trap', 'Cascais', 'Lisboa', 'Artista português de hip-hop/trap com forte presença no panorama urbano nacional.', 1, 'approved', NULL, NULL, '2026-02-05 11:30:18', 'uploads/avatars/avatar_69a8d0cb460a48.88460258.jpg', NULL, NULL, '{\"instagram\": \"https://www.instagram.com/plutonio2765/\", \"spotify\": \"https://open.spotify.com/intl-pt/artist/39HJXjH5hKcCzaU0g6mv8G\"}', 'https://open.spotify.com/intl-pt/track/12j1Mq9Of1xfONYvGzhayD?si=9b3f8483c78b4726', 'https://open.spotify.com/intl-pt/track/1X3ltingEVNGRoJf1dvZfQ?si=a8b2e0cb98c6472d', 'https://open.spotify.com/intl-pt/track/5ZxHzpxIEr0rxGJN8Jsdjy?si=705faa93cb254111'),
(114, 'Ana Malhoa', 'anamalhoa@gmail.com', '$2y$10$jPilPpWQm/K.xLi1oWXJIukaBEk95pvYCoK7Sq6C4nAvUY9e.uiX2', 'Pop, Música Popular', 'Lisboa', 'Lisboa', 'Cantora portuguesa com carreira longa no pop e na música popular.', 1, 'approved', NULL, NULL, '2026-02-15 10:40:30', 'uploads/avatars/avatar_69a8d226efc5f5.24382315.jpg', NULL, NULL, '{\"spotify\":\"https:\\/\\/open.spotify.com\\/intl-pt\\/artist\\/11fQUCWMbX34Eb8XVhrW9w\",\"instagram\":\"https:\\/\\/www.instagram.com\\/anamalhoa\\/\",\"x\":\"https:\\/\\/x.com\\/anamalhoa\"}', 'https://open.spotify.com/intl-pt/track/78As1oUYYj6i8R69VrJhGY?si=9f0b1d11f48b44e4', 'https://open.spotify.com/intl-pt/track/4uGXyHLrsE4Q5fLAQH29Xt?si=293af5d5670445dd', 'https://open.spotify.com/intl-pt/track/1ciEBtxSvObtQOxs7RnwSb?si=8eb6153a1398469c'),
(115, 'Bárbara Tinoco', 'barbaratinoco@gmail.com', '$2y$10$jPilPpWQm/K.xLi1oWXJIukaBEk95pvYCoK7Sq6C4nAvUY9e.uiX2', 'Pop', 'Lisboa', 'Lisboa', 'Cantora e compositora portuguesa com sonoridade pop e escrita intimista.', 1, 'approved', NULL, NULL, '2026-02-18 15:55:12', 'uploads/avatars/avatar_69a9479fad8bb2.44992182.png', NULL, NULL, '{\"instagram\":\"https:\\/\\/www.instagram.com\\/barbaratinoco\\/\",\"spotify\":\"https:\\/\\/open.spotify.com\\/intl-pt\\/artist\\/10okQWuBo3LEA8HSZ1VUMT\"}', 'https://open.spotify.com/intl-pt/track/0r1WiGIHk8ys95AvbqCbwW?si=8624f1a0b6aa4218', 'https://open.spotify.com/intl-pt/track/0qOdDzi89OD7CBnCivfHEf?si=77e925f916bd4cf0', 'https://open.spotify.com/intl-pt/track/2hOFPpEOYj7sEUAjGeVnpx?si=22504d13fba44e72'),
(116, 'Bárbara Bandeira', 'barbarabandeira@gmail.com', '$2y$10$jPilPpWQm/K.xLi1oWXJIukaBEk95pvYCoK7Sq6C4nAvUY9e.uiX2', 'Pop, R&B', 'Setúbal', 'Setúbal', 'Cantora portuguesa de pop contemporâneo com influência R&B.', 1, 'approved', NULL, NULL, '2026-02-20 09:10:45', 'uploads/avatars/avatar_69a94820065641.21310632.png', NULL, NULL, '{\"instagram\":\"https:\\/\\/www.instagram.com\\/barbarabandeira\\/\",\"spotify\":\"https:\\/\\/open.spotify.com\\/intl-pt\\/artist\\/4zhMand4AowXuUz4VpGiTJ\"}', 'https://open.spotify.com/intl-pt/track/1lxoHeqakdySsDRvSZJHIU?si=3c483e5e6fbe493b', 'https://open.spotify.com/intl-pt/track/5yS8wgwBnmjpXwGislck0n?si=b5363e8e20da4def', 'https://open.spotify.com/intl-pt/track/4xigvdbvlzxJIIZGIg6eGT?si=edde1e33a5734ed7'),
(117, 'LON3R JOHNY', 'lon3rjohny@gmail.com', '$2y$10$jPilPpWQm/K.xLi1oWXJIukaBEk95pvYCoK7Sq6C4nAvUY9e.uiX2', 'Hip-Hop, Trap', 'Lisboa', 'Lisboa', 'Artista português de rap/trap com sonoridade melódica e linguagem da nova escola.', 1, 'approved', NULL, NULL, '2026-03-01 14:15:33', 'uploads/avatars/avatar_69a94856c32576.62083011.jpg', NULL, NULL, '{\"instagram\":\"https:\\/\\/instagram.com\\/lon3rjohny\",\"youtube\":\"https:\\/\\/youtube.com\\/@LON3RJOHNY\",\"soundcloud\":\"https:\\/\\/soundcloud.com\\/lon3rjohny\",\"spotify\":\"https:\\/\\/open.spotify.com\\/intl-pt\\/artist\\/1fV7Au7ymGP3uhDV1TfjSd\"}', 'https://open.spotify.com/intl-pt/track/3yiAe0CqqnmpUyM2Gk8byM?si=fe1e1f60198743bd', 'https://open.spotify.com/intl-pt/track/1A6tib5hSqCJAMSBRkNV55?si=5203b11aaafd4188', 'https://open.spotify.com/intl-pt/track/4r4PT8ztky9NfGINelXcxM?si=f1a54fa0acf54c38'),
(118, 'ProfJam', 'profjam@gmail.com', '$2y$10$jPilPpWQm/K.xLi1oWXJIukaBEk95pvYCoK7Sq6C4nAvUY9e.uiX2', 'Hip-Hop, Trap', 'Lisboa', 'Lisboa', 'Rapper e produtor português com influência no hip-hop e no trap nacional.', 1, 'approved', NULL, NULL, '2026-03-03 11:45:50', 'uploads/avatars/avatar_69a9487a3d0a33.18177517.png', NULL, NULL, '{\"spotify\":\"https:\\/\\/open.spotify.com\\/intl-pt\\/artist\\/3DhsjXVgWmA6X26tUugAjP\",\"instagram\":\"https:\\/\\/www.instagram.com\\/profjam6\\/\",\"youtube\":\"https:\\/\\/www.youtube.com\\/user\\/ProfJam6\"}', 'https://open.spotify.com/intl-pt/track/0hc2NfCTKQqBxQqerJQaWl?si=9d3cfccf1bbf45c1', 'https://open.spotify.com/intl-pt/track/4kfwRXcUVeeUMITRVujl9i?si=d7ced921ad3044e3', 'https://open.spotify.com/intl-pt/track/2GNlPS9dtWE9M40vwk6qhd?si=951a725771ee4895'),
(119, 'Quim Barreiros', 'quimbarreiros@gmail.com', '$2y$10$jPilPpWQm/K.xLi1oWXJIukaBEk95pvYCoK7Sq6C4nAvUY9e.uiX2', 'Pimba, Música Popular', 'Caminha', 'Viana do Castelo', 'Ícone da música popular portuguesa, reconhecido pelo estilo irreverente e festivo.', 1, 'approved', NULL, NULL, '2026-01-12 17:30:40', 'uploads/avatars/avatar_69a95757e4f828.10110822.png', NULL, NULL, '{\"instagram\":\"https:\\/\\/www.instagram.com\\/quimbarreirosoficial\\/\",\"spotify\":\"https:\\/\\/open.spotify.com\\/intl-pt\\/artist\\/2oWYReGFzdcjvgUC5aizxv\",\"youtube\":\"https:\\/\\/www.youtube.com\\/channel\\/UCVtVRSHwA8yKqeHs_QjsOtg\"}', 'https://open.spotify.com/intl-pt/track/5mu40aJSlKDFY6GT7XdcrC?si=14bad704a8684e73', 'https://open.spotify.com/intl-pt/track/7hMyxHOskG0Eq2cHQ52WnQ?si=ec53429e3c04474a', 'https://open.spotify.com/intl-pt/track/3zi74Q3yQCJ6qIZ2L5NbyW?si=dad5ae2471c94ad6'),
(120, 'Tony Carreira', 'tonycarreira@gmail.com', '$2y$10$jPilPpWQm/K.xLi1oWXJIukaBEk95pvYCoK7Sq6C4nAvUY9e.uiX2', 'Pop, Música Popular', 'Pampilhosa da Serra', 'Coimbra', 'Cantor português de pop romântico com vasta carreira em palco e televisão.', 1, 'approved', NULL, NULL, '2026-01-25 10:15:25', 'uploads/avatars/avatar_69a95811cbe316.25194593.png', NULL, NULL, '{\"instagram\":\"https:\\/\\/www.instagram.com\\/tonycarreiraoficial\\/\",\"youtube\":\"https:\\/\\/www.youtube.com\\/@tonycarreira\",\"spotify\":\"https:\\/\\/open.spotify.com\\/intl-pt\\/artist\\/6w7nHPNj2BIGTEbRrefVyu\"}', 'https://open.spotify.com/intl-pt/track/2jw6c3dtOM6Xn1Ee6SD7Xc?si=03ab7d76f9294598', 'https://open.spotify.com/intl-pt/track/3303s19agScDaCsPYdCu7i?si=2d505e88f4144cdf', 'https://open.spotify.com/intl-pt/track/71ceabjI6PWm691oukzI46?si=aa234b19f96447ea'),
(121, 'Rui Veloso', 'ruiveloso@gmail.com', '$2y$10$jPilPpWQm/K.xLi1oWXJIukaBEk95pvYCoK7Sq6C4nAvUY9e.uiX2', 'Rock, Blues', 'Porto', 'Porto', 'Referência do rock português, com forte influência do blues e da canção urbana.', 1, 'approved', NULL, NULL, '2026-03-02 12:10:30', 'uploads/avatars/avatar_69a95854689209.44431214.png', NULL, NULL, '{\"instagram\":\"https:\\/\\/www.instagram.com\\/ruivelosoficial\\/\",\"spotify\":\"https:\\/\\/open.spotify.com\\/intl-pt\\/artist\\/1HHcnYrMqv4xpwHJAzUfqO\",\"facebook\":\"https:\\/\\/www.facebook.com\\/rui.veloso.oficial\\/\"}', 'https://open.spotify.com/intl-pt/track/564tHu6RJmSLd6a7mC011x?si=c01a1f95ab4d4f42', 'https://open.spotify.com/intl-pt/track/6Fht77FJ4gY3HI1ffObfEd?si=1cef001bd4c7447e', 'https://open.spotify.com/intl-pt/track/0V80GWsSjGgFS68PtJbmLB?si=4b8090249539492a'),
(122, 'António Zambujo', 'antoniozambujo@gmail.com', '$2y$10$jPilPpWQm/K.xLi1oWXJIukaBEk95pvYCoK7Sq6C4nAvUY9e.uiX2', 'Fado, MPB', 'Beja', 'Beja', 'Cantor português com raízes no fado e influência da música brasileira.', 1, 'approved', NULL, NULL, '2026-03-03 14:40:20', 'uploads/avatars/avatar_69a9589e877cc9.09348634.png', NULL, NULL, '{\"instagram\":\"https:\\/\\/www.instagram.com\\/antonio.zambujo\\/\",\"spotify\":\"https:\\/\\/open.spotify.com\\/intl-pt\\/artist\\/72G65J87dqMi39O00Du2Je\"}', 'https://open.spotify.com/intl-pt/track/7gWuvpjf7ghzYSQoGQuU3d?si=3a457217cc4e4660', 'https://open.spotify.com/intl-pt/track/7k67pntlyJbtRnFvKTpFVh?si=d686d308508c423d', 'https://open.spotify.com/intl-pt/track/3ZPVuzw44LCPjctLlhXktP?si=3c0de6b7d0224850'),
(123, 'Pedro Abrunhosa', 'pedroabrunhosa@gmail.com', '$2y$10$jPilPpWQm/K.xLi1oWXJIukaBEk95pvYCoK7Sq6C4nAvUY9e.uiX2', 'Pop, Rock', 'Porto', 'Porto', 'Cantor e compositor português com carreira marcante no pop-rock nacional.', 1, 'approved', NULL, NULL, '2026-01-30 09:25:15', 'uploads/avatars/avatar_69a958f0637057.44701139.png', NULL, NULL, '{\"instagram\":\"https:\\/\\/www.instagram.com\\/pedro.abrunhosa\\/\",\"spotify\":\"https:\\/\\/open.spotify.com\\/intl-pt\\/artist\\/4wkGlEHElrIAnV8tBWDdAR\"}', 'https://open.spotify.com/intl-pt/track/79JW4tqG3AigegX3sKRxgi?si=09db4fbee3074105', 'https://open.spotify.com/intl-pt/track/22OYxfqknaCPnMHIFasGXD?si=e4d80145ea7440b6', 'https://open.spotify.com/intl-pt/track/1bidGYLF9SSG4QfS3byUv6?si=47b3ee67ca9b41eb'),
(124, 'Miguel Araújo', 'miguelaraujo@gmail.com', '$2y$10$jPilPpWQm/K.xLi1oWXJIukaBEk95pvYCoK7Sq6C4nAvUY9e.uiX2', 'Pop, Indie', 'Maia', 'Porto', 'Cantor e compositor português com repertório entre o pop e a canção de autor.', 1, 'approved', NULL, NULL, '2026-02-12 15:20:50', 'uploads/avatars/avatar_69a9593cd79d57.31747458.png', NULL, NULL, '{\"instagram\":\"https:\\/\\/www.instagram.com\\/miguel_araujo_insta\\/\",\"spotify\":\"https:\\/\\/open.spotify.com\\/intl-pt\\/artist\\/0A1fXDw6kferKgLY4UMxNi\",\"facebook\":\"https:\\/\\/www.facebook.com\\/miguelaraujojorge\\/\",\"linkedin\":\"https:\\/\\/pt.linkedin.com\\/in\\/miguel-ara%C3%BAjo-14608455\"}', 'https://open.spotify.com/intl-pt/track/0AX6468RmVA9ufePa4C10j?si=2c134d33e9ce4b3a', 'https://open.spotify.com/intl-pt/track/77wmFRBfK0NuCT9LTV2ob9?si=c040d64da5544b16', 'https://open.spotify.com/intl-pt/track/1Nni6w8GER4acjDKnaLmiV?si=97c7b35a7e0a4dde'),
(125, 'Chico da Tina', 'chicodatina@gmail.com', '$2y$10$jPilPpWQm/K.xLi1oWXJIukaBEk95pvYCoK7Sq6C4nAvUY9e.uiX2', 'Trap, Rap', 'Viana do Castelo', 'Viana do Castelo', 'Rapper português conhecido pela estética irreverente e sonoridade trap.', 1, 'approved', NULL, NULL, '2026-02-22 11:35:40', 'uploads/avatars/avatar_69a9599eabdf50.41097497.png', NULL, NULL, '{\"spotify\":\"https:\\/\\/open.spotify.com\\/intl-pt\\/artist\\/7xDYCf4fsGxHBp8Blo9D94\",\"instagram\":\"https:\\/\\/www.instagram.com\\/chicodaconcertina\\/\"}', 'https://open.spotify.com/intl-pt/track/5mKzzTh3y4KsGpDT9h8EmS?si=721aa9c1f12a428b', 'https://open.spotify.com/intl-pt/track/7yaxytyMMyorvKlF2g64MP?si=2abdb9e358ac421d', 'https://open.spotify.com/intl-pt/track/1O3ehXYHBBK0dYben12YVM?si=7eae317d6593409b'),
(126, 'Nininho Vaz Maia', 'nininhovazmaia@gmail.com', '$2y$10$jPilPpWQm/K.xLi1oWXJIukaBEk95pvYCoK7Sq6C4nAvUY9e.uiX2', 'Pimba', 'Lisboa', 'Lisboa', 'Imortal vedeta da pimba portuguesa', 1, 'approved', NULL, NULL, '2026-03-04 08:20:15', 'uploads/avatars/avatar_69a95a1fe5da38.29409051.jpg', NULL, NULL, '{\"instagram\":\"https:\\/\\/www.instagram.com\\/nininhovazmaia_\\/\",\"spotify\":\"https:\\/\\/open.spotify.com\\/intl-pt\\/artist\\/7bMt24fjCeUulKxEUyLdL6\",\"facebook\":\"https:\\/\\/www.facebook.com\\/nininhovazmaiaoficial\"}', 'https://open.spotify.com/intl-pt/track/6b83CJAjIXSk2cUjabJBlm?si=06865a5a2ac24ef1', 'https://open.spotify.com/intl-pt/track/6elFmzmHRGXeqyya232ZFu?si=9d317f39e6824118', 'https://open.spotify.com/intl-pt/track/2xyQaJXfp2DRElKF3aOMHP?si=b3802fce2437410f'),

--
-- Acionadores `artists`
--
DELIMITER $$
CREATE TRIGGER `trg_artists_moderation_history_after_update` AFTER UPDATE ON `artists` FOR EACH ROW INSERT INTO artist_moderation_history (artist_id, previous_status, new_status, changed_by, change_note) SELECT NEW.id, OLD.moderation_status, NEW.moderation_status, 'system', 'Alteração de estado da conta' WHERE COALESCE(OLD.moderation_status, 'approved') <> COALESCE(NEW.moderation_status, 'approved')
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `artist_moderation_history`
--

CREATE TABLE `artist_moderation_history` (
  `id` bigint(20) NOT NULL,
  `artist_id` int(11) NOT NULL,
  `previous_status` varchar(20) NOT NULL,
  `new_status` varchar(20) NOT NULL,
  `changed_by` enum('system','artist','admin') NOT NULL DEFAULT 'system',
  `changed_by_id` int(11) DEFAULT NULL,
  `change_note` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `artist_moderation_history`
--

INSERT INTO `artist_moderation_history` (`id`, `artist_id`, `previous_status`, `new_status`, `changed_by`, `changed_by_id`, `change_note`, `created_at`) VALUES
(26, 135, 'approved', 'pending', 'system', NULL, 'Alteração de estado da conta', '2026-03-26 15:46:19'),
(27, 135, 'pending', 'approved', 'system', NULL, 'Alteração de estado da conta', '2026-03-26 15:50:14');

-- --------------------------------------------------------

--
-- Estrutura da tabela `artist_reports`
--

CREATE TABLE `artist_reports` (
  `id` bigint(20) NOT NULL,
  `artist_id` int(11) NOT NULL,
  `reporter_token` varchar(64) NOT NULL,
  `reason` varchar(600) DEFAULT NULL,
  `status` enum('pending','reviewed','dismissed') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `artist_reports`
--

INSERT INTO `artist_reports` (`id`, `artist_id`, `reporter_token`, `reason`, `status`, `created_at`) VALUES
(5, 124, 'user_19', 'muito swag', 'dismissed', '2026-03-24 16:32:47'),
(6, 121, 'user_20', 'Perfil Impróprio', 'dismissed', '2026-03-26 15:36:31');

-- --------------------------------------------------------

--
-- Estrutura da tabela `artist_upvotes`
--

CREATE TABLE `artist_upvotes` (
  `id` bigint(20) NOT NULL,
  `artist_id` int(11) NOT NULL,
  `voter_token` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `artist_upvotes`
--

INSERT INTO `artist_upvotes` (`id`, `artist_id`, `voter_token`, `created_at`) VALUES
(22, 113, 'artist_127', '2026-03-05 09:45:15'),
(24, 109, 'user_13', '2026-03-05 14:13:36'),
(25, 126, 'user_13', '2026-03-05 14:13:37'),
(26, 113, 'user_13', '2026-03-05 14:13:39'),
(27, 111, 'artist_129', '2026-03-05 15:26:26'),
(28, 109, 'artist_131', '2026-03-12 11:46:48'),
(30, 123, 'user_17', '2026-03-19 15:47:51'),
(34, 111, 'artist_134', '2026-03-24 16:55:03'),
(36, 122, 'user_20', '2026-03-26 15:35:28');

-- --------------------------------------------------------

--
-- Estrutura da tabela `global_chat_messages`
--

CREATE TABLE `global_chat_messages` (
  `id` bigint(20) NOT NULL,
  `artist_id` int(11) NOT NULL,
  `message` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `global_chat_messages`
--

INSERT INTO `global_chat_messages` (`id`, `artist_id`, `message`, `created_at`) VALUES
(11, 109, 'Alô meus manos! Tão ocupados?', '2026-03-05 00:32:13'),
(12, 110, 'Ca Bu Fla Ma Nau, bro!', '2026-03-05 00:34:30'),
(13, 111, 'Esta noite, eu vou beijar, vou dançar, até me cansar!', '2026-03-05 00:37:05'),
(14, 112, 'Mekie tropa!', '2026-03-05 00:38:54'),
(15, 113, 'Space man...', '2026-03-05 00:40:32'),
(22, 135, 'Olá!🔥🔥', '2026-03-26 15:51:19');

-- --------------------------------------------------------

--
-- Estrutura da tabela `map_first_access`
--

CREATE TABLE `map_first_access` (
  `id` bigint(20) NOT NULL,
  `account_type` enum('user','artist') NOT NULL,
  `account_id` int(11) NOT NULL,
  `first_seen_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_seen_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `visit_count` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `map_first_access`
--

INSERT INTO `map_first_access` (`id`, `account_type`, `account_id`, `first_seen_at`, `last_seen_at`, `visit_count`) VALUES
(1, 'user', 7, '2026-02-24 22:19:28', '2026-03-06 08:57:55', 19),
(2, 'artist', 9, '2026-02-24 22:22:32', '2026-02-26 22:26:27', 25),
(3, 'user', 5, '2026-02-24 22:26:59', '2026-02-24 22:26:59', 1),
(4, 'user', 1, '2026-02-24 22:29:25', '2026-02-24 22:29:25', 1),
(5, 'user', 2, '2026-02-24 22:33:02', '2026-02-24 22:44:57', 5),
(6, 'user', 3, '2026-02-24 22:45:19', '2026-02-24 22:50:13', 2),
(7, 'user', 8, '2026-02-24 22:51:20', '2026-02-24 22:51:20', 1),
(8, 'user', 9, '2026-02-24 22:53:43', '2026-02-24 22:53:43', 1),
(9, 'user', 10, '2026-02-24 22:56:49', '2026-02-25 12:24:31', 5),
(10, 'artist', 17, '2026-02-25 23:17:49', '2026-02-25 23:31:38', 8),
(11, 'artist', 8, '2026-02-26 00:27:28', '2026-02-26 00:47:06', 4),
(12, 'artist', 18, '2026-02-26 08:43:28', '2026-02-26 08:46:42', 4),
(13, 'artist', 7, '2026-02-26 08:48:03', '2026-02-26 08:51:52', 2),
(14, 'user', 12, '2026-03-02 10:12:31', '2026-03-02 12:18:25', 2),
(15, 'artist', 19, '2026-03-02 15:13:01', '2026-03-04 09:50:25', 12),
(16, 'artist', 20, '2026-03-03 10:16:57', '2026-03-04 09:53:10', 30),
(17, 'artist', 109, '2026-03-05 00:28:18', '2026-03-12 11:47:43', 3),
(18, 'artist', 110, '2026-03-05 00:33:02', '2026-03-15 23:51:12', 4),
(19, 'artist', 111, '2026-03-05 00:35:01', '2026-03-26 15:52:17', 53),
(20, 'artist', 112, '2026-03-05 00:37:36', '2026-03-05 00:38:43', 2),
(21, 'artist', 113, '2026-03-05 00:39:28', '2026-03-05 14:26:09', 4),
(22, 'artist', 114, '2026-03-05 00:44:17', '2026-03-05 09:06:03', 7),
(23, 'artist', 117, '2026-03-05 09:09:23', '2026-03-05 09:16:03', 4),
(24, 'artist', 118, '2026-03-05 09:10:30', '2026-03-05 09:17:36', 4),
(25, 'artist', 115, '2026-03-05 09:13:16', '2026-03-05 09:14:11', 2),
(26, 'artist', 116, '2026-03-05 09:14:30', '2026-03-05 09:15:00', 2),
(27, 'artist', 127, '2026-03-05 09:42:05', '2026-03-05 09:44:53', 3),
(28, 'artist', 119, '2026-03-05 10:14:56', '2026-03-05 10:14:56', 1),
(29, 'artist', 120, '2026-03-05 10:15:17', '2026-03-05 10:17:09', 2),
(30, 'artist', 121, '2026-03-05 10:17:30', '2026-03-05 10:18:49', 2),
(31, 'artist', 122, '2026-03-05 10:19:01', '2026-03-05 10:19:47', 2),
(32, 'artist', 123, '2026-03-05 10:20:15', '2026-03-05 10:21:12', 2),
(33, 'artist', 124, '2026-03-05 10:21:34', '2026-03-05 10:23:02', 2),
(34, 'artist', 125, '2026-03-05 10:23:14', '2026-03-05 10:23:57', 2),
(35, 'artist', 126, '2026-03-05 10:25:21', '2026-03-05 10:26:17', 2),
(36, 'user', 13, '2026-03-05 14:10:41', '2026-03-05 14:13:59', 2),
(37, 'artist', 128, '2026-03-05 14:21:52', '2026-03-05 14:27:31', 4),
(38, 'user', 14, '2026-03-05 14:55:53', '2026-03-05 14:55:53', 1),
(39, 'artist', 129, '2026-03-05 15:21:25', '2026-03-12 09:30:50', 9),
(40, 'artist', 130, '2026-03-12 10:32:35', '2026-03-12 11:00:42', 9),
(41, 'artist', 131, '2026-03-12 11:43:20', '2026-03-13 11:24:17', 24),
(42, 'user', 15, '2026-03-12 11:50:07', '2026-03-16 19:57:43', 20),
(44, 'user', 16, '2026-03-16 22:21:10', '2026-03-18 19:10:06', 7),
(46, 'artist', 132, '2026-03-18 12:52:48', '2026-03-18 19:46:43', 5),
(47, 'user', 17, '2026-03-19 15:43:21', '2026-03-19 15:49:17', 2),
(48, 'artist', 133, '2026-03-19 15:56:59', '2026-03-19 16:08:54', 3),
(49, 'user', 18, '2026-03-24 16:21:29', '2026-03-24 16:22:06', 2),
(50, 'user', 19, '2026-03-24 16:23:25', '2026-03-24 16:33:35', 3),
(51, 'artist', 134, '2026-03-24 16:45:47', '2026-03-24 16:59:50', 5),
(52, 'user', 20, '2026-03-26 15:29:31', '2026-03-26 15:39:58', 2),
(53, 'artist', 135, '2026-03-26 15:47:00', '2026-03-26 15:54:25', 3);

-- --------------------------------------------------------

--
-- Estrutura da tabela `private_messages`
--

CREATE TABLE `private_messages` (
  `id` bigint(20) NOT NULL,
  `sender_artist_id` int(11) NOT NULL,
  `recipient_artist_id` int(11) NOT NULL,
  `message` varchar(255) DEFAULT NULL,
  `audio_path` varchar(255) DEFAULT NULL,
  `audio_original_name` varchar(255) DEFAULT NULL,
  `audio_mime_type` varchar(100) DEFAULT NULL,
  `audio_size_bytes` int(10) UNSIGNED DEFAULT NULL,
  `audio_duration_seconds` decimal(6,2) DEFAULT NULL,
  `audio_source` varchar(32) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `read_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `private_messages`
--

INSERT INTO `private_messages` (`id`, `sender_artist_id`, `recipient_artist_id`, `message`, `audio_path`, `audio_original_name`, `audio_mime_type`, `audio_size_bytes`, `audio_duration_seconds`, `audio_source`, `created_at`, `read_at`) VALUES
(39, 111, 122, 'Então, tá tudo?', NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 10:09:06', NULL),
(40, 111, 125, 'Bora fazer uma música?', NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 10:09:23', NULL),
(41, 111, 123, 'Muito bom 🔥🔥', NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 10:09:38', NULL),
(42, 111, 110, '🎹🎹🎹', NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 10:13:59', '2026-03-15 23:49:51'),
(45, 111, 110, 'aaa', '/beatmap(mapa)/uploads/private-audio/pm-audio-20260316004410-c6fdcb14a7ef.mp3', 'Playboi Carti - dothatshit! (Instrumental).mp3', 'audio/mpeg', 2501288, 187.06, 'audio_file', '2026-03-15 23:44:10', '2026-03-15 23:49:51'),
(46, 111, 110, NULL, '/beatmap(mapa)/uploads/private-audio/pm-audio-20260316004457-4abe2d5f3251.mp3', 'playboi carti - count it up (official instrumental) [prod. mexikodro].mp3', 'audio/mpeg', 4196321, 255.05, 'audio_file', '2026-03-15 23:44:57', '2026-03-15 23:49:51'),
(47, 111, 110, NULL, '/beatmap(mapa)/uploads/private-audio/pm-audio-20260316004826-5ea983e1ee98.mp3', 'dragon-studio-whoosh-08-410878.mp3', 'audio/mpeg', 61440, 1.92, 'audio_file', '2026-03-15 23:48:26', '2026-03-15 23:49:51'),
(48, 111, 110, NULL, '/beatmap(mapa)/uploads/private-audio/pm-audio-20260316004855-c3117ea53fee.webm', 'mensagem-voz-1773618534279.webm', 'video/webm', 36981, 2.34, 'voice_recording', '2026-03-15 23:48:55', '2026-03-15 23:49:51'),
(56, 135, 111, 'Olá toy!', NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-26 15:52:00', '2026-03-26 15:52:27'),
(57, 111, 135, 'Olá', NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-26 15:52:36', '2026-03-26 15:52:37'),
(58, 135, 111, NULL, '/beatmap(mapa)/uploads/private-audio/pm-audio-20260326165314-6483fb58b492.webm', 'mensagem-voz-1774540378409.webm', 'video/webm', 32859, 2.41, 'voice_recording', '2026-03-26 15:53:14', '2026-03-26 15:53:15');

-- --------------------------------------------------------

--
-- Estrutura da tabela `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `created_at`, `reset_token`, `reset_expires`) VALUES
(2, 'olaola', 'olaolaola@gmail.com', '$2y$10$TfGHfxagNEJ6rhiJRyQxO.byaFjVXrMHoqelObvM8RYq2Sx2DH8.y', '2026-02-02 11:49:55', NULL, NULL),
(3, 'nuno', 'nunoduarte@gmail.com', '$2y$10$5V5QmyZK8qP4QnD9LM28v.UG0DuDMv9tbXkA.vMP0zioCVJYKTOYG', '2026-02-02 11:50:26', NULL, NULL),
(4, 'vascoooo', 'vascoaa@gmail.com', '$2y$10$2voRFpoMSFEJSXTItj2v2uHf0Qt84EE2iGrAkcyu08887PeY8bmvq', '2026-02-02 12:00:18', NULL, NULL),
(5, 'vascoide', 'vascoide@gmail.com', '$2y$10$px0MCbVYwst1mW4dRI.vFenNwx5ZqGFdKtAfpyO259C7bMPrV9SxG', '2026-02-02 12:25:45', NULL, NULL),
(6, 'brunogamer', 'bruno@gmail.com', '$2y$10$1V7CuXiDFWI1okDuAI6FMuiULjVE.cYQChSIOXBvL2/AMypy6g5P.', '2026-02-02 13:03:00', NULL, NULL),
(7, 'tiagoneves', 'tiago@gmail.com', '$2y$10$ChFdM1.oKXDIm6FnSz1fd.TDWMX9gzfC5aawK1RBVOY1lMlzM2uUy', '2026-02-19 12:06:05', NULL, NULL),
(8, 'vascoide1', 'vass@gmail.com', '$2y$10$3kPqghcrNYEjDfhieKT5c.H6Z5fXCQMnodRHiJMtL60Gr9K8YVRJe', '2026-02-22 18:47:04', NULL, NULL),
(9, 'sarah', 'sarah@gmail.com', '$2y$10$7DcOxNwf8D6Fgh2srK/YKuu6/bxKH9Lik..7hlDtQizOIrmojFu7S', '2026-02-22 18:54:31', NULL, NULL),
(10, 'pedro', 'pedro@gmail.com', '$2y$10$NrjQKLOZxYGpQT1QBtchQOZ/PWcpbEP5TyuaF70VMruAPR7nb176.', '2026-02-22 19:03:55', NULL, NULL),
(11, 'lhjfhjaf', 'olapaulo@gmail.com', '$2y$10$rBDK1Hw9Yh4yWu3B/4JewuaeSYrHRf9.EIiXZU8z6sQC56HPf4Rli', '2026-02-23 08:19:16', NULL, NULL),
(12, 'olavasco', 'olavasco@gmail.com', '$2y$10$TGn0XrOxXmcEap6Fs15O0e5gF1E1ex1YS6qoW9elHP0lSC4HMFGO6', '2026-03-02 10:12:12', NULL, NULL),
(13, 'bruno', 'bruninho@gmail.com', '$2y$10$zYObEm3p10zlAebM9mYNyeDQ39CkyynciedDp7E8St0ixBMYResS.', '2026-03-05 14:10:11', NULL, NULL),
(14, 'basquinho', 'basquinho@gmail.com', '$2y$10$mbw3YOkE2oi5VWwQU18ExuvUJdTjcmTCpX9vqMjH.gZpJ0U2g1NeG', '2026-03-05 14:55:38', NULL, NULL),
(15, 'vascooo', 'vascooo@gmail.com', '$2y$10$6OrEaagDdqnbPj0mEiVztuwHpP4j.BFgO.AcaRm7.J7WX47NfKksy', '2026-03-12 11:49:28', NULL, NULL),
(16, 'vasco', 'vasco@gmail.com', '$2y$10$kQ0RuRufc4LUFcERAFsMoeK/KBsa75n6aPgyilv1qEAK.ULAY8vtm', '2026-03-16 20:00:09', NULL, NULL),
(17, 'joão_texeira', 'joao@gmail.com', '$2y$10$6Zw.B8XvySjuCxaEQ4bMI.0s98VFlg16e1nTHjM9yqqQoN55MbRl.', '2026-03-19 15:43:10', NULL, NULL),
(20, 'carlos', 'carlos@gmail.com', '$2y$10$1G1i3SL7ertm30BFJKfij.Cy9PKMzgAuKYIaeQGiWkhXvOTSZK1VW', '2026-03-26 15:28:30', NULL, NULL);

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `admin_accounts`
--
ALTER TABLE `admin_accounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Índices para tabela `artists`
--
ALTER TABLE `artists`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Índices para tabela `artist_moderation_history`
--
ALTER TABLE `artist_moderation_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_artist_mod_history_artist` (`artist_id`),
  ADD KEY `idx_artist_mod_history_new_status` (`new_status`),
  ADD KEY `idx_artist_mod_history_created_at` (`created_at`);

--
-- Índices para tabela `artist_reports`
--
ALTER TABLE `artist_reports`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_artist_reporter` (`artist_id`,`reporter_token`),
  ADD KEY `idx_artist_reports_artist` (`artist_id`),
  ADD KEY `idx_artist_reports_status` (`status`);

--
-- Índices para tabela `artist_upvotes`
--
ALTER TABLE `artist_upvotes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_artist_voter` (`artist_id`,`voter_token`),
  ADD KEY `idx_artist_upvotes_artist` (`artist_id`);

--
-- Índices para tabela `global_chat_messages`
--
ALTER TABLE `global_chat_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_global_chat_created` (`created_at`),
  ADD KEY `idx_global_chat_artist` (`artist_id`);

--
-- Índices para tabela `map_first_access`
--
ALTER TABLE `map_first_access`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_map_account` (`account_type`,`account_id`);

--
-- Índices para tabela `private_messages`
--
ALTER TABLE `private_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_private_messages_recipient` (`recipient_artist_id`,`created_at`),
  ADD KEY `idx_private_messages_sender` (`sender_artist_id`,`created_at`),
  ADD KEY `idx_pm_pair_sr_id` (`sender_artist_id`,`recipient_artist_id`,`id`),
  ADD KEY `idx_pm_pair_rs_id` (`recipient_artist_id`,`sender_artist_id`,`id`),
  ADD KEY `idx_pm_unread_recipient` (`recipient_artist_id`,`read_at`,`sender_artist_id`,`id`);

--
-- Índices para tabela `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `admin_accounts`
--
ALTER TABLE `admin_accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `artists`
--
ALTER TABLE `artists`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=136;

--
-- AUTO_INCREMENT de tabela `artist_moderation_history`
--
ALTER TABLE `artist_moderation_history`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT de tabela `artist_reports`
--
ALTER TABLE `artist_reports`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `artist_upvotes`
--
ALTER TABLE `artist_upvotes`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT de tabela `global_chat_messages`
--
ALTER TABLE `global_chat_messages`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de tabela `map_first_access`
--
ALTER TABLE `map_first_access`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT de tabela `private_messages`
--
ALTER TABLE `private_messages`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT de tabela `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `artist_moderation_history`
--
ALTER TABLE `artist_moderation_history`
  ADD CONSTRAINT `fk_artist_mod_history_artist` FOREIGN KEY (`artist_id`) REFERENCES `artists` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `artist_reports`
--
ALTER TABLE `artist_reports`
  ADD CONSTRAINT `fk_artist_reports_artist` FOREIGN KEY (`artist_id`) REFERENCES `artists` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `artist_upvotes`
--
ALTER TABLE `artist_upvotes`
  ADD CONSTRAINT `fk_artist_upvotes_artist` FOREIGN KEY (`artist_id`) REFERENCES `artists` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `global_chat_messages`
--
ALTER TABLE `global_chat_messages`
  ADD CONSTRAINT `fk_global_chat_artist` FOREIGN KEY (`artist_id`) REFERENCES `artists` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `private_messages`
--
ALTER TABLE `private_messages`
  ADD CONSTRAINT `fk_private_messages_recipient` FOREIGN KEY (`recipient_artist_id`) REFERENCES `artists` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_private_messages_sender` FOREIGN KEY (`sender_artist_id`) REFERENCES `artists` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
