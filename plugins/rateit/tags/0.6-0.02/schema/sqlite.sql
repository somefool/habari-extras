CREATE TABLE {rateit_log} (
  id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
  post_id INT NOT NULL,
  rating TINYINT NOT NULL,
  timestamp DATETIME NOT NULL,
  ip INT( 10 ) NOT NULL
);

CREATE UNIQUE INDEX IF NOT EXISTS post_id_ip ON {rateit_log}( post_id, ip );

