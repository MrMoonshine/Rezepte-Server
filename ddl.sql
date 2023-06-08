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
	"name"	TEXT NOT NULL,
	"rationable"	INTEGER,
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
