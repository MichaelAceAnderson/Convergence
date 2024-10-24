-- Utiliser une transaction pour éviter les erreurs de création de tables
START TRANSACTION;
-- Définir la zone horaire à Paris
SET GLOBAL time_zone = '+01:00';
-- Désactiver la vérification des clés étrangères pour éviter les erreurs de création de tables
SET FOREIGN_KEY_CHECKS=0;

-- ------------- Tables ---------------
-- Supprimer la table des utilisateurs si elle existe
DROP TABLE IF EXISTS c_user;
-- Créer la table des utilisateurs
CREATE TABLE IF NOT EXISTS c_user(
  id_user INT NOT NULL AUTO_INCREMENT,
  nickname VARCHAR(64) NOT NULL,
  password TEXT NOT NULL,
  p_img_url TEXT,
  description TEXT,
  is_mod BOOLEAN,
  register_date TIMESTAMP(0) NOT NULL DEFAULT now(),
  CONSTRAINT PK_c_user PRIMARY KEY(id_user),
  CONSTRAINT AK_c_user UNIQUE(nickname)
);

-- Supprimer la table des posts si elle existe
DROP TABLE IF EXISTS c_post;
-- Créer la table des posts
CREATE TABLE IF NOT EXISTS c_post(
  id_post INT AUTO_INCREMENT,
  content TEXT NOT NULL,
  media_url TEXT,
  creation_date TIMESTAMP(0) NOT NULL DEFAULT now(),
  id_user_author INT NOT NULL,
  CONSTRAINT PK_c_post PRIMARY KEY(id_post),
  CONSTRAINT FK_c_post_c_user_author FOREIGN KEY(id_user_author) REFERENCES c_user(id_user)
);

-- Supprimer la table des focalisateurs si elle existe
DROP TABLE IF EXISTS c_focuser;
-- Créer la table des focalisateurs
CREATE TABLE IF NOT EXISTS c_focuser(
  id_user_focuser INT NOT NULL,
  id_user_focused INT NOT NULL,
  CONSTRAINT PK_c_focuser PRIMARY KEY(id_user_focuser, id_user_focused),
  CONSTRAINT FK_c_focuser_c_user_focuser FOREIGN KEY(id_user_focuser) REFERENCES c_user(id_user),
  CONSTRAINT FK_c_focuser_c_user_focused FOREIGN KEY(id_user_focused) REFERENCES c_user(id_user)
);


-- Supprimer la table des réactions si elle existe
DROP TABLE IF EXISTS c_post_reaction;
-- Créer la table des réactions
CREATE TABLE IF NOT EXISTS c_post_reaction(
  id_post_reacted INT NOT NULL,
  id_user_reacted INT NOT NULL,
  reaction_type INT NOT NULL,
  CONSTRAINT PK_c_post_reaction PRIMARY KEY(id_post_reacted, id_user_reacted),
  CONSTRAINT FK_c_post_reaction_c_post FOREIGN KEY(id_post_reacted) REFERENCES c_post(id_post),
  CONSTRAINT FK_c_post_reaction_c_user FOREIGN KEY(id_user_reacted) REFERENCES c_user(id_user)
);

-- ------------- Utilisateurs ---------------
-- Créer les utilisateurs s'ils n'existent pas
-- NOTE: En spécifiant l'hôte "localhost", on limite l'accès à l'utilisateur à la machine locale
-- Si l'un de ces utilisateurs se connecte depuis l'extérieur, l'accès lui sera refusé même avec le bon mot de passe

-- -- Utilisateur avec droits de sélection (lecture)
-- CREATE USER IF NOT EXISTS c_admin@'localhost' IDENTIFIED BY 'C770rwx';

-- -- ------------- Privilèges ---------------
-- -- Donner tous les droits à l'utilisateur administrateur du site sur la base convergence
-- GRANT SELECT, UPDATE, DELETE, INSERT ON convergence.* TO c_admin;

 -- Valider la transaction
COMMIT;

-- ------------------------------------------