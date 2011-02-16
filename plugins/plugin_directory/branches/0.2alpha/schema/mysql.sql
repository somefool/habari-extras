CREATE TABLE {dir_plugin_versions} (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  post_id INT UNSIGNED NOT NULL,
  url VARCHAR(255) NOT NULL,
  version VARCHAR(255) NOT NULL,
  md5 VARCHAR(255) NOT NULL,
  status VARCHAR(255) NOT NULL,
  habari_version VARCHAR(255) NOT NULL,
  requires VARCHAR(255),
  provides VARCHAR(255),
  recommends VARCHAR(255),
  description TEXT,
  source_link VARCHAR(255),
  author VARCHAR(255) NOT NULL,
  author_url VARCHAR(255) NOT NULL,
  license VARCHAR(255) NOT NULL,
  screenshot VARCHAR(255),
  instructions TEXT,
  UNIQUE KEY id (id)
);

CREATE TABLE {dir_theme_versions} (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  post_id INT UNSIGNED NOT NULL,
  url VARCHAR(255) NOT NULL,
  version VARCHAR(255) NOT NULL,
  md5 VARCHAR(255) NOT NULL,
  status VARCHAR(255) NOT NULL,
  habari_version VARCHAR(255) NOT NULL,
  requires VARCHAR(255),
  provides VARCHAR(255),
  recommends VARCHAR(255),
  description TEXT,
  source_link VARCHAR(255),
  author VARCHAR(255) NOT NULL,
  author_url VARCHAR(255) NOT NULL,
  license VARCHAR(255) NOT NULL,
  screenshot VARCHAR(255),
  instructions TEXT,
  UNIQUE KEY id (id)
);

