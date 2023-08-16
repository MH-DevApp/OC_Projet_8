INSERT INTO `user` (`id`, `username`, `password`, `email`) VALUES
(1, 'user', '$2y$13$PDHPXvhBtR3AEskgcji1wePul2URDMwgVCrp9dn62jFJCj9ASd2ry', 'user@oc-p8.fr'),
(2, 'user1', '$2y$13$P/rigj61zhk0usVUWoqwT..ffJ1FvygMpoSUMM9t0BIERqH.E7VeS', 'user1@oc-p8.fr'),
(3, 'user2', '$2y$13$2fnBMbd1mS6qowDZ1ABQ0OmTPOoA4eea27KNncQfIJ.TibpBF2yvW', 'user2@oc-p8.fr');

INSERT INTO `task` (`id`, `created_at`, `title`, `content`, `is_done`) VALUES
(1, '2023-08-14 19:48:16', 'Définir les objectifs du projet', 'Organiser une réunion avec l\'équipe pour définir clairement les objectifs, les livrables et les attentes du projet web.', 0),
(2, '2023-08-14 19:48:39', 'Créer la structure du site web', 'Élaborer une maquette ou un plan détaillé de la structure du site, y compris les pages, les menus et les fonctionnalités.', 0),
(3, '2023-08-14 19:50:18', 'Élaborer le cahier des charges', 'Rédiger un cahier des charges complet qui décrit les spécifications techniques, les fonctionnalités, les délais et les ressources nécessaires.', 0),
(4, '2023-08-14 19:50:40', 'Sélectionner les technologies', 'Évaluer et choisir les outils, les langages de programmation et les plates-formes qui seront utilisés pour développer le site web.', 0),
(5, '2023-08-14 19:50:54', 'Concevoir l\'interface utilisateur', 'Travailler avec les concepteurs pour créer une interface utilisateur attrayante et conviviale en tenant compte de l\'expérience utilisateur.', 0),
(6, '2023-08-14 19:51:12', 'Développer les fonctionnalités clés', 'Programmer les fonctionnalités essentielles du site web, telles que l\'inscription des utilisateurs, la navigation, la recherche, etc.', 0),
(7, '2023-08-14 19:51:29', 'Tester et déboguer', 'Effectuer des tests approfondis pour détecter et résoudre les bugs, les erreurs d\'affichage et les problèmes de compatibilité.', 0),
(8, '2023-08-14 19:52:08', 'Optimiser les performances', 'Mettre en œuvre des techniques d\'optimisation pour garantir que le site web se charge rapidement et fonctionne efficacement.', 0),
(9, '2023-08-14 19:52:17', 'Rédiger le contenu', 'Créer et ajouter le contenu textuel, visuel et multimédia au site web, en veillant à ce qu\'il soit informatif et engageant.', 0),
(10, '2023-08-14 19:52:31', 'Lancer le site web', 'Préparer le site pour le lancement en mettant en place l\'hébergement, en effectuant les derniers tests et en assurant une transition en douceur.', 0);
