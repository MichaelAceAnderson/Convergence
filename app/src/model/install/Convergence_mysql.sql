-- Utiliser une transaction pour éviter les erreurs de création de tables
START TRANSACTION;
-- Set the time zone to Paris
SET GLOBAL time_zone = '+01:00';
-- Disable foreign key checks to avoid table creation errors
SET FOREIGN_KEY_CHECKS=0;

-- ------------- Tables ---------------
-- Drop the user table if it exists
DROP TABLE IF EXISTS c_user;
-- Create the user table
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

-- Drop the post table if it exists
DROP TABLE IF EXISTS c_post;
-- Create the post table
CREATE TABLE IF NOT EXISTS c_post(
	id_post INT AUTO_INCREMENT,
	content TEXT NOT NULL,
	media_url TEXT,
	creation_date TIMESTAMP(0) NOT NULL DEFAULT now(),
	id_user_author INT NOT NULL,
	CONSTRAINT PK_c_post PRIMARY KEY(id_post),
	CONSTRAINT FK_c_post_c_user_author FOREIGN KEY(id_user_author) REFERENCES c_user(id_user)
);

-- Drop the focuser table if it exists
DROP TABLE IF EXISTS c_focuser;
-- Create the focuser table
CREATE TABLE IF NOT EXISTS c_focuser(
	id_user_focuser INT NOT NULL,
	id_user_focused INT NOT NULL,
	CONSTRAINT PK_c_focuser PRIMARY KEY(id_user_focuser, id_user_focused),
	CONSTRAINT FK_c_focuser_c_user_focuser FOREIGN KEY(id_user_focuser) REFERENCES c_user(id_user),
	CONSTRAINT FK_c_focuser_c_user_focused FOREIGN KEY(id_user_focused) REFERENCES c_user(id_user)
);

-- Drop the post reaction table if it exists
DROP TABLE IF EXISTS c_post_reaction;
-- Create the post reaction table
CREATE TABLE IF NOT EXISTS c_post_reaction(
	id_post_reacted INT NOT NULL,
	id_user_reacted INT NOT NULL,
	reaction_type INT NOT NULL,
	CONSTRAINT PK_c_post_reaction PRIMARY KEY(id_post_reacted, id_user_reacted),
	CONSTRAINT FK_c_post_reaction_c_post FOREIGN KEY(id_post_reacted) REFERENCES c_post(id_post),
	CONSTRAINT FK_c_post_reaction_c_user FOREIGN KEY(id_user_reacted) REFERENCES c_user(id_user)
);

-- ------------- Users ---------------
-- Create users if they do not exist
-- NOTE: By specifying the host "localhost", access is limited to the local machine
-- If any of these users connect from outside, access will be denied even with the correct password

-- -- User with select (read) rights
-- CREATE USER IF NOT EXISTS c_admin@'localhost' IDENTIFIED BY 'C770rwx';

-- ------------- Privileges ---------------
-- Grant all rights to the site administrator user on the convergence database
-- GRANT SELECT, UPDATE, DELETE, INSERT ON convergence.* TO c_admin;

-- Commit the transaction
COMMIT;

-- ------------------------------------------
