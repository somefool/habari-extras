CREATE SEQUENCE {scratchpads}_pkey_seq;
CREATE TABLE {scratchpads} (
  id INTEGER NOT NULL DEFAULT nextval('{scratchpads}_pkey_seq'),
  user_id INTEGER NOT NULL,
  name VARCHAR(255) NOT NULL,
  slug VARCHAR(255) NOT NULL,
  template TEXT,
  PRIMARY KEY (id)
);

CREATE SEQUENCE {scratchpadentries}_pkey_seq;
CREATE TABLE {scratchpad_entries} (
  id INTEGER NOT NULL DEFAULT nextval('{scratchpad_entries}_pkey_seq'),
  scratchpad_id INTEGER NOT NULL,
  title VARCHAR(255) NOT NULL,
  slug VARCHAR(255) NOT NULL,
  url VARCHAR(255) NOT NULL,
  content TEXT,
  PRIMARY KEY (id)
);
