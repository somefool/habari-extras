CREATE SEQUENCE {rateit_log}_pkey_seq;
CREATE TABLE {rateit_log} (
  id INTEGER NOT NULL DEFAULT nextval('{rateit_log}_pkey_seq'),
  post_id INTEGER NOT NULL,
  rating SMALLINT NOT NULL,
  timestamp TIMESTAMP NOT NULL,
  ip BIGINT NOT NULL,
  PRIMARY KEY ( id ),
  UNIQUE ( post_id, ip )
);

CREATE INDEX rateit_log_ip_key ON {rateit_log}( ip );
CREATE INDEX rateit_log_post_id_key ON {rateit_log}( post_id );
