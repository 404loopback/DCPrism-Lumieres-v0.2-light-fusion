# Comparaison des schémas de base de données
## DCPrism (ancien SQLite) vs DCPrism-Laravel (nouveau MySQL/PostgreSQL)

---

## 📊 Vue d'ensemble

Le nouveau schéma DCPrism-Laravel représente une **réimplémentation partielle** du système original, avec un focus différent :
- **Ancien** : Gestion complète de la chaîne DCP (cinémas, projections, versions linguistiques)
- **Nouveau** : Système de soumission et validation de contenu pour festivals

---

## 🆕 Tables présentes uniquement dans DCPrism-Laravel

| Table | Description |
|-------|-------------|
| `roles` | Rôles utilisateur (système RBAC séparé) |
| `permissions` | Permissions (système RBAC séparé) |
| `role_user` | Association rôles-utilisateurs |
| `parameters` | Système de paramètres génériques |
| `movie_parameters` | Paramètres spécifiques aux films |
| `nomenclatures` | Règles de nomenclature (simplifié) |
| `movie_metadata` | Métadonnées des films |
| `validation_results` | Résultats de validation séparés |
| `uploads` | Gestion des uploads |
| `job_progress` | Suivi des tâches asynchrones |
| `personal_access_tokens` | Tokens d'authentification Laravel |
| `telescope_entries` | Debug/monitoring Laravel Telescope |
| `jobs` | Queue system Laravel |
| `activity_log` | Logs d'activité utilisateur |
| `cache` | Cache Laravel |
| `metrics_history` | Historique des métriques |

---

## 🗑️ Tables présentes uniquement dans l'ancien DCPrism

### 🎬 Système de projection
| Table | Description |
|-------|-------------|
| `cinemas` | Gestion des cinémas |
| `screens` | Écrans de projection |
| `screenings` | Programmation des séances |
| `cinema_movies` | Association cinémas-films |

### 🔧 Gestion technique DCP
| Table | Description |
|-------|-------------|
| `dcps` | Fichiers DCP avec validation |
| `cpls` | Composition Playlist |
| `pkls` | Packing Lists |
| `kdms` | Key Delivery Messages |
| `pkl_cpl` | Associations PKL-CPL |
| `dcp_pkl` | Associations DCP-PKL |

### 🌐 Versions linguistiques
| Table | Description |
|-------|-------------|
| `versions` | Versions linguistiques (VO, VOST, DUB, etc.) |
| `version_parameters` | Paramètres par version |
| `langs` | Langues disponibles |

### 🔐 Authentification & Upload
| Table | Description |
|-------|-------------|
| `upload_tokens` | Tokens d'upload temporaires |
| `b2_configs` | Configuration Backblaze par festival |

### 📅 Système d'événements
| Table | Description |
|-------|-------------|
| `calendar_events` | Événements du calendrier |
| `user_events` | Événements utilisateur |
| `event_types` | Types d'événements |
| `notifications` | Système de notifications |

### 💼 Gestion commerciale
| Table | Description |
|-------|-------------|
| `contracts` | Contrats avec les festivals |

### 🎨 Ressources visuelles
| Table | Description |
|-------|-------------|
| `festivalresources` | Ressources visuelles des festivals |

### 📝 Métadonnées
| Table | Description |
|-------|-------------|
| `movieinfo` | Métadonnées séparées des films |

---

## 🔄 Tables communes avec différences majeures

### 🎭 Table `movies`

#### Ancien DCPrism (Simple)
```sql
movies (
  id, title, format, source_email, 
  status='new', expected_versions, 
  created_by, created_at, updated_at
)
```

#### DCPrism-Laravel (Enrichi)
```sql
movies (
  id, title, source_email, status='pending',
  format, expected_versions, created_by,
  description, duration, genre, year,
  country, language, backblaze_folder,
  backblaze_file_id, upload_progress,
  DCP_metadata, technical_notes,
  validated_by, validated_at,
  file_path, file_size, original_filename,
  uploaded_at, created_at, updated_at
)
```

**Différences clés :**
- ➕ Métadonnées intégrées (duration, genre, year, country, language)
- ➕ Gestion d'upload avancée (progress, file info)
- ➕ Workflow de validation (validated_by, validated_at)
- ➕ Stockage Backblaze intégré
- 🔄 Status étendu (pending, uploading, validated, etc.)

### 🎪 Table `festivals`

#### Ancien DCPrism
```sql
festivals (
  id, name, subdomain, description, email, website,
  contact_phone, start_date, end_date, submission_deadline,
  is_active, backblaze_folder, max_storage, max_file_size,
  accept_submissions, accepted_formats, format='FTR',
  created_at, updated_at
)
```

#### DCPrism-Laravel
```sql
festivals (
  id, name, subdomain, description, email, website,
  contact_phone, start_date, end_date, submission_deadline,
  is_active, accept_submissions, nomenclature_separator,
  nomenclature_template, technical_requirements,
  accepted_formats, max_storage, max_file_size,
  backblaze_folder, storage_status, storage_info,
  storage_last_tested_at, is_public, created_at, updated_at
)
```

**Différences clés :**
- ➕ Gestion de nomenclature intégrée
- ➕ Monitoring du stockage (storage_status, storage_info)
- ➕ Visibilité publique (is_public)
- ➕ Exigences techniques (technical_requirements)
- ➖ Champ format supprimé

### 👥 Table `users`

#### Ancien DCPrism (Custom)
```sql
users (
  id, name, email, email_verified_at, password,
  login_token, token_expires_at, login_pin, pin_expires_at,
  cinema_id, role='source', can_supervise, is_active,
  mfa_enabled, mfa_secret, phone, last_login_at,
  created_at, updated_at
)
```

#### DCPrism-Laravel (Standard Laravel)
```sql
users (
  id, name, email, email_verified_at,
  password, remember_token,
  created_at, updated_at
)
```

**Différences clés :**
- ➖ Système d'authentification custom supprimé
- ➖ MFA intégré supprimé
- ➖ Association cinéma supprimée
- ➖ Tokens de login custom supprimés
- ➕ Structure Laravel standard (remember_token)

### 🔗 Table `movie_festivals`

#### Ancien DCPrism (Simple)
```sql
movie_festivals (
  id, movie_id, festival_id,
  created_at, updated_at
)
```

#### DCPrism-Laravel (Enrichi)
```sql
movie_festivals (
  id, movie_id, festival_id,
  submission_status='pending', selected_versions,
  technical_notes, priority,
  created_at, updated_at
)
```

**Différences clés :**
- ➕ Workflow de soumission (submission_status)
- ➕ Sélection de versions
- ➕ Notes techniques
- ➕ Système de priorité

---

## 🏗️ Différences architecturales

### Ancien DCPrism
- **Focus** : Gestion complète de la chaîne DCP
- **Complexité** : Très technique (CPL, PKL, KDM)
- **Use case** : Cinémas + Festivals
- **Auth** : Système custom avec tokens/PIN
- **Rôles** : Enum simple dans table users

### DCPrism-Laravel
- **Focus** : Soumission et validation pour festivals
- **Complexité** : Workflow simplifié
- **Use case** : Festivals principalement
- **Auth** : Laravel standard + RBAC
- **Rôles** : Système RBAC complet séparé

---

## 💡 Recommandations

### Migration progressive
1. **Conserver** : Tables DCP techniques si gestion cinémas nécessaire
2. **Intégrer** : Système d'événements et calendrier
3. **Moderniser** : Authentification vers Laravel standard
4. **Enrichir** : Métadonnées movies du nouveau vers l'ancien

### Fonctionnalités à récupérer
- 🎬 Gestion des cinémas et programmation
- 🔧 Pipeline technique DCP complet
- 🌐 Versions linguistiques avancées
- 📅 Système d'événements
- 🔐 Upload tokens pour sécurité

### Fonctionnalités modernes à conserver
- 📊 Système de métriques
- 🔍 Monitoring (Telescope)
- 📝 Activity logs
- ⚡ Queue system
- 🛡️ RBAC séparé
