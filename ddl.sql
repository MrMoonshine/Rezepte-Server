CREATE TABLE IF NOT EXISTS "recipes" (
	"id"	INTEGER NOT NULL UNIQUE,
	"title"	TEXT NOT NULL UNIQUE,
	"description"	TEXT,
	"time" INTEGER,
	"image" INTEGER,
	"dishtype"	INTEGER,
	"amount" INTEGER,
	PRIMARY KEY("id" AUTOINCREMENT),
	FOREIGN KEY("image") REFERENCES "images"("id")
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

CREATE TABLE IF NOT EXISTS "dishtypes" (
	"id"	INTEGER NOT NULL UNIQUE,
	"name"	INTEGER NOT NULL UNIQUE,
	PRIMARY KEY("id" AUTOINCREMENT)
);

CREATE TABLE IF NOT EXISTS "images" (
	"id"	INTEGER NOT NULL UNIQUE,
	"name"	INTEGER NOT NULL UNIQUE,
	PRIMARY KEY("id" AUTOINCREMENT)
);

CREATE TABLE IF NOT EXISTS "cri" (
	"recipe"	INTEGER NOT NULL,
	"ingredient"	INTEGER NOT NULL,
	"amount"	REAL NOT NULL,
	"unit"	INTEGER NOT NULL,
	FOREIGN KEY("recipe") REFERENCES "recipes"("id"),
	FOREIGN KEY("ingredient") REFERENCES "ingredients"("id"),
	FOREIGN KEY("unit") REFERENCES "units"("id")
);

CREATE TABLE IF NOT EXISTS "cra" (
	"recipe"	INTEGER NOT NULL,
	"allergene"	INTEGER NOT NULL,
	FOREIGN KEY("recipe") REFERENCES "recipes"("id"),
	FOREIGN KEY("allergene") REFERENCES "allergenes"("id")
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
INSERT INTO "units" ("name") VALUES ("Esslöffel");
INSERT INTO "units" ("name") VALUES ("Teelöffel");
INSERT INTO "units" ("name") VALUES ("Prise");
INSERT INTO "units" ("name") VALUES ("Stück");
INSERT INTO "units" ("name") VALUES ("Packung");
INSERT INTO "units" ("name") VALUES ("Häferl");
/*
	Create a table to prevent deletion of units.
	It simply has a foreign key to the units created above.
*/
CREATE TABLE "unit_deletion_prevention" (
	"id"	INTEGER NOT NULL,
	FOREIGN KEY("id") REFERENCES "units"("id")
);
insert into unit_deletion_prevention ("id") select "id" from "units";
/*
	Default Dish types
*/
INSERT INTO "dishtypes" ("name") VALUES ("Vorspeise");
INSERT INTO "dishtypes" ("name") VALUES ("Suppe");
INSERT INTO "dishtypes" ("name") VALUES ("Salat");
INSERT INTO "dishtypes" ("name") VALUES ("Hauptspeise");
INSERT INTO "dishtypes" ("name") VALUES ("Nachspeise");
INSERT INTO "dishtypes" ("name") VALUES ("Mehlspeise");
INSERT INTO "dishtypes" ("name") VALUES ("Cocktail");

CREATE TABLE "dishtype_deletion_prevention" (
	"id"	INTEGER NOT NULL,
	FOREIGN KEY("id") REFERENCES "dishtypes"("id")
);
insert into dishtype_deletion_prevention ("id") select "id" from "dishtypes";
/*
			V I E W S

	Main base view
*/
CREATE VIEW IF NOT EXISTS "v_recipe_main"
AS
 SELECT recipes.id     AS id,
       recipes.title  AS title,
       recipes.time   AS time,
	   recipes.amount  AS amount,
       recipes.description   AS description,
       images.NAME    AS image,
       dishtypes.NAME AS dishtype,
	   dishtypes.id AS dtid,
	   ROW_NUMBER () OVER ( ORDER BY recipes.title ) rownum
FROM   recipes
       LEFT JOIN images
              ON recipes.image = images.id
       LEFT JOIN dishtypes
              ON recipes.dishtype = dishtypes.id;
/*
	All relevant Infos for the card view
*/
CREATE VIEW IF NOT EXISTS "v_recipe_card" AS
SELECT    v_recipe_main.rownum                AS rownum,
          v_recipe_main.id                    AS id,
          v_recipe_main.title                 AS title,
          v_recipe_main.dishtype              AS dishtype,
		  v_recipe_main.dtid				  AS dtid,
          v_recipe_main.image                 AS image,
          v_recipe_main.time                  AS time,
          substr(description, 0, 100)         AS description,
          group_concat(allergenes.NAME, ", ") AS allergenes
FROM      v_recipe_main
LEFT JOIN cra
ON        cra.recipe = v_recipe_main.id
LEFT JOIN allergenes
ON        cra.allergene = allergenes.id
GROUP BY  v_recipe_main.id;
/*
	Ingredients for a recipe
*/
CREATE VIEW IF NOT EXISTS "v_recipe_ingredients" AS
SELECT    ingredients.name AS name,
          cri.amount       AS amount,
          units.name       AS unit,
		  cri.recipe	   AS recipe
FROM      cri
LEFT JOIN ingredients
ON        cri.ingredient = ingredients.id
LEFT JOIN units
ON        cri.unit = units.id;

/*
	Ingredients and Unit relations
*/
-- density is in m^3/kg or g/l
CREATE TABLE IF NOT EXISTS "ingredient_density" (
	"ingredient"	INTEGER NOT NULL UNIQUE,
	"density"	REAL NOT NULL,
	FOREIGN KEY("ingredient") REFERENCES "ingredients"("id")
);