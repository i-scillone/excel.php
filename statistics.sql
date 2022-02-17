CREATE TABLE IF NOT EXISTS "reati" (
  "proc" integer NOT NULL,
  "fonte" text NULL,
  "anno_fonte" integer NULL,
  "num_fonte" integer NULL,
  "art" integer NULL,
  "dupl" text NULL,
  "sub" text NULL,
  "tipo" text NULL,
  "aggr" text NULL,
  "iter" integer NULL,
  "chiave" text NOT NULL
);
CREATE TABLE proc(num INTEGER NOT NULL,mag TEXT,iscr TEXT,defin TEXT,tipo_def TEXT,chiave TEXT NOT NULL);
