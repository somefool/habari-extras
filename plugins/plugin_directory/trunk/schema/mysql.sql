CREATE TABLE {plugin_versions} (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  post_id INT UNSIGNED NOT NULL,
  url VARCHAR(255) NOT NULL,
  version VARCHAR(255) NOT NULL,
  md5 VARCHAR(255) NOT NULL,
  status VARCHAR(255) NOT NULL,
  habari_version VARCHAR(255) NOT NULL,
  requires VARCHAR(255) NOT NULL,
  provides VARCHAR(255) NOT NULL,
  recommends VARCHAR(255) NOT NULL,
  description TEXT,
  source_link VARCHAR(255) NOT NULL,
  UNIQUE KEY id (id)
);
