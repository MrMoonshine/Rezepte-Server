CREATE TABLE IF NOT EXISTS "recipes" (
	"id"	INTEGER NOT NULL UNIQUE,
	"title"	TEXT NOT NULL,
	"text"	INTEGER,
	PRIMARY KEY("id" AUTOINCREMENT)
);

CREATE TABLE IF NOT EXISTS "ingredients" (
	"name"	TEXT NOT NULL,
	"id"	INTEGER UNIQUE,
	PRIMARY KEY("id" AUTOINCREMENT)
);

CREATE TABLE IF NOT EXISTS "units" (
	"id"	INTEGER NOT NULL UNIQUE,
	"name"	TEXT NOT NULL UNIQUE,
	PRIMARY KEY("id" AUTOINCREMENT)
);

CREATE TABLE IF NOT EXISTS "allergenes" (
	"id"	INTEGER NOT NULL UNIQUE,
	"name"	INTEGER NOT NULL UNIQUE,
	PRIMARY KEY("id" AUTOINCREMENT)
);

CREATE TABLE IF NOT EXISTS "connection_recipe_ingredients" (
	"recipe"	INTEGER NOT NULL,
	"ingredient"	INTEGER NOT NULL,
	"amount"	INTEGER NOT NULL,
	"unit"	INTEGER NOT NULL,
	FOREIGN KEY("recipe") REFERENCES "recipes"("id"),
	FOREIGN KEY("ingredient") REFERENCES "ingredients"("id"),
	FOREIGN KEY("unit") REFERENCES "units"("id")
);

CREATE TABLE IF NOT EXISTS "connection_recipe_allergenes" (
	"recipe"	INTEGER NOT NULL,
	"allergene"	INTEGER NOT NULL,
	FOREIGN KEY("recipe") REFERENCES "recipes"("id"),
	FOREIGN KEY("allergene") REFERENCES "allergene"("id")
);

CREATE TABLE IF NOT EXISTS "backup" (
	"id"	INTEGER NOT NULL UNIQUE,
	"data"	TEXT NOT NULL,
	PRIMARY KEY("id" AUTOINCREMENT)
);

/*
	Default Units
*/
INSERT INTO "units" ("name") VALUES ("g");
INSERT INTO "units" ("name") VALUES ("kg");
INSERT INTO "units" ("name") VALUES ("l");
INSERT INTO "units" ("name") VALUES ("ml");
INSERT INTO "units" ("name") VALUES ("Essl&ouml;ffel");
INSERT INTO "units" ("name") VALUES ("Teel&ouml;ffel");
INSERT INTO "units" ("name") VALUES ("Prise");
INSERT INTO "units" ("name") VALUES ("St&uuml;ck");
INSERT INTO "units" ("name") VALUES ("Packung");
INSERT INTO "units" ("name") VALUES ("H&auml;ferl");
INSERT INTO "units" ("name") VALUES ("Noagal");
