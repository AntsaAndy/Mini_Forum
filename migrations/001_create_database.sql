-- Migration pour créer la base de données du forum

-- Table des utilisateurs
CREATE TABLE `user` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `email` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
    `roles` json NOT NULL,
    `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `username` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
    `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
    PRIMARY KEY (`id`),
    UNIQUE KEY `UNIQ_8D93D649E7927C74` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des sujets
CREATE TABLE `topic` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `author_id` int(11) NOT NULL,
    `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `description` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
    `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
    `updated_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
    `category` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
    `is_locked` tinyint(1) NOT NULL DEFAULT 0,
    `is_pinned` tinyint(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    KEY `IDX_9D40DE1BF675F31B` (`author_id`),
    CONSTRAINT `FK_9D40DE1BF675F31B` FOREIGN KEY (`author_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des posts
CREATE TABLE `post` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `author_id` int(11) NOT NULL,
    `topic_id` int(11) NOT NULL,
    `content` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
    `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
    `updated_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
    `likes` int(11) NOT NULL DEFAULT 0,
    `is_edited` tinyint(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    KEY `IDX_5A8A6C8DF675F31B` (`author_id`),
    KEY `IDX_5A8A6C8D1F55203D` (`topic_id`),
    CONSTRAINT `FK_5A8A6C8D1F55203D` FOREIGN KEY (`topic_id`) REFERENCES `topic` (`id`) ON DELETE CASCADE,
    CONSTRAINT `FK_5A8A6C8DF675F31B` FOREIGN KEY (`author_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Données de test
INSERT INTO `user` (`email`, `roles`, `password`, `username`, `created_at`) VALUES
('admin@forum.com', '["ROLE_ADMIN"]', '$2y$13$example_hashed_password', 'Admin', NOW()),
('user@forum.com', '["ROLE_USER"]', '$2y$13$example_hashed_password', 'Utilisateur', NOW());

INSERT INTO `topic` (`author_id`, `title`, `description`, `created_at`, `updated_at`, `category`, `is_locked`, `is_pinned`) VALUES
(1, 'Bienvenue sur le forum !', 'Présentation du forum et règles de base', NOW(), NOW(), 'Général', 0, 1),
(1, 'Questions sur Symfony', 'Posez vos questions sur le framework Symfony', NOW(), NOW(), 'Symfony & PHP', 0, 0);

INSERT INTO `post` (`author_id`, `topic_id`, `content`, `created_at`, `updated_at`, `likes`, `is_edited`) VALUES
(1, 1, 'Bienvenue sur notre mini forum de discussion ! N\'hésitez pas à poser vos questions et partager vos connaissances.', NOW(), NOW(), 5, 0),
(1, 2, 'Ce sujet est dédié aux questions sur Symfony. N\'hésitez pas à demander de l\'aide !', NOW(), NOW(), 2, 0);