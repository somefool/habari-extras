CREATE TABLE {rateit_log} (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  post_id INT NOT NULL,
  rating TINYINT NOT NULL,
  timestamp DATETIME NOT NULL,
  ip INT( 10 ) NOT NULL,
  INDEX ( post_id , ip )
);
