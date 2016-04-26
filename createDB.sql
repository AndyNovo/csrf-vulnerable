PRAGMA foreign_keys=OFF;
BEGIN TRANSACTION;
CREATE TABLE users (username text primary key, password text);
INSERT INTO "users" VALUES('andynovo','p0o9i8u7y6t5r4e3w2q1');
CREATE TABLE messages (id integer primary key, username text, message text);
INSERT INTO "messages" VALUES(1,'andynovo','Welcome to the unpleasant chat app.  Enjoy your stay');
COMMIT;