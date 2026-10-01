-- SaaS Facture Electronique TTN - MySQL + SQLite compatible
CREATE TABLE IF NOT EXISTS users (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  raison_sociale TEXT NOT NULL,
  matricule_fiscal TEXT NOT NULL UNIQUE,
  registre_commerce TEXT,
  adresse TEXT,
  email TEXT NOT NULL UNIQUE,
  telephone TEXT,
  login TEXT NOT NULL UNIQUE,
  password_hash TEXT NOT NULL,
  role TEXT DEFAULT 'client',
  statut TEXT DEFAULT 'en_attente',
  forfait TEXT DEFAULT 'usage',
  date_inscription DATETIME DEFAULT CURRENT_TIMESTAMP,
  date_validation DATETIME,
  factures_utilisees INTEGER DEFAULT 0
);

CREATE TABLE IF NOT EXISTS factures (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER,
  numfact TEXT NOT NULL,
  datefact TEXT,
  mf_fournisseur TEXT,
  fournisseur TEXT,
  mf_client TEXT,
  client TEXT,
  ht REAL,
  tva REAL,
  timbre REAL,
  ttc REAL,
  xml_path TEXT,
  pdf_path TEXT,
  ref_ttn TEXT,
  qr_path TEXT,
  cev TEXT,
  statut TEXT DEFAULT 'brouillon',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
