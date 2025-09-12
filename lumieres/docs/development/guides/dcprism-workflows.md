# DCPrism-Laravel - Workflows Complets

## Diagramme des Workflows Interconnectés

```mermaid
graph TB
    %% === ACTEURS ===
    subgraph Actors["👥 ACTEURS"]
        DIST[Distributeur]
        FEST[Festival Manager]
        TECH[Technicien]
        CINE[Exploitant Cinéma]
        API[API Client]
    end

    %% === WORKFLOW PRINCIPAL DE SOUMISSION ===
    subgraph MainWorkflow["🎬 WORKFLOW PRINCIPAL"]
        START([Création Film]) --> UPLOAD[Upload DCP]
        UPLOAD --> QUEUE1{Queue Processing}
        QUEUE1 --> ANALYSIS[Analyse Automatique]
        ANALYSIS --> VALIDATION{Validation OK?}
        VALIDATION -->|OUI| APPROVED[Film Validé]
        VALIDATION -->|NON| REJECTED[Film Rejeté]
        VALIDATION -->|MANUEL| MANUAL[Validation Manuelle]
        MANUAL --> TECH_REVIEW[Review Technicien]
        TECH_REVIEW --> APPROVED
        TECH_REVIEW --> REJECTED
    end

    %% === WORKFLOW VERSIONS ===
    subgraph VersionWorkflow["🌐 WORKFLOW VERSIONS"]
        APPROVED --> CREATE_VERSIONS[Création Versions]
        CREATE_VERSIONS --> VO[Version Originale]
        CREATE_VERSIONS --> VOST[VO Sous-titrée]
        CREATE_VERSIONS --> DUB[Doublage]
        CREATE_VERSIONS --> VF[Version Française]
        CREATE_VERSIONS --> VOSTF[VO ST Français]
        
        VO --> NOMENCLATURE[Génération Nomenclature]
        VOST --> NOMENCLATURE
        DUB --> NOMENCLATURE
        VF --> NOMENCLATURE
        VOSTF --> NOMENCLATURE
    end

    %% === WORKFLOW PROGRAMMATION ===
    subgraph ProgrammingWorkflow["📅 WORKFLOW PROGRAMMATION"]
        NOMENCLATURE --> SELECT_CINEMAS[Sélection Cinémas]
        SELECT_CINEMAS --> CREATE_SCREENINGS[Création Séances]
        CREATE_SCREENINGS --> ASSIGN_VERSIONS[Association Versions-Séances]
        ASSIGN_VERSIONS --> SCHEDULE[Planning Finalisé]
    end

    %% === WORKFLOW FESTIVAL ===
    subgraph FestivalWorkflow["🏆 WORKFLOW FESTIVAL"]
        FEST --> CONFIG_FEST[Configuration Festival]
        CONFIG_FEST --> NOMENCLATURE_RULES[Règles Nomenclature]
        CONFIG_FEST --> DEADLINES[Deadlines Soumission]
        CONFIG_FEST --> QUOTAS[Quotas Stockage]
        
        NOMENCLATURE_RULES --> NOMENCLATURE
        START --> SUBMISSION[Soumission Festival]
        SUBMISSION --> DEADLINES
        APPROVED --> FESTIVAL_SELECTION[Sélection Festival]
    end

    %% === WORKFLOW TECHNIQUE ===
    subgraph TechnicalWorkflow["⚙️ WORKFLOW TECHNIQUE"]
        UPLOAD --> BACKBLAZE[Stockage Backblaze]
        ANALYSIS --> DCP_CHECK[Vérification DCP]
        DCP_CHECK --> TECH_SPECS[Spécifications Tech]
        DCP_CHECK --> FORMAT_VALID[Validation Format]
        DCP_CHECK --> CONTENT_ANALYSIS[Analyse Contenu]
        
        TECH_SPECS --> CONFORMITY{Conformité Standards?}
        FORMAT_VALID --> CONFORMITY
        CONTENT_ANALYSIS --> CONFORMITY
        
        CONFORMITY -->|DCI/SMPTE OK| AUTO_APPROVE[Approbation Auto]
        CONFORMITY -->|PROBLÈMES| ISSUE_DETECTION[Détection Problèmes]
        
        AUTO_APPROVE --> APPROVED
        ISSUE_DETECTION --> RECOMMENDATIONS[Recommandations]
        RECOMMENDATIONS --> MANUAL
    end

    %% === WORKFLOW PARAMÈTRES ===
    subgraph ParameterWorkflow["🔧 WORKFLOW PARAMÈTRES"]
        subgraph ParamCategories["Catégories Paramètres"]
            AUDIO_PARAMS[Audio]
            VIDEO_PARAMS[Vidéo]
            SUB_PARAMS[Sous-titres]
            CONTENT_PARAMS[Contenu]
            TECH_PARAMS[Technique]
            META_PARAMS[Métadonnées]
            ACCESS_PARAMS[Accessibilité]
        end
        
        DCP_CHECK --> EXTRACT_META[Extraction Métadonnées]
        EXTRACT_META --> AUDIO_PARAMS
        EXTRACT_META --> VIDEO_PARAMS
        EXTRACT_META --> SUB_PARAMS
        EXTRACT_META --> CONTENT_PARAMS
        EXTRACT_META --> TECH_PARAMS
        EXTRACT_META --> META_PARAMS
        EXTRACT_META --> ACCESS_PARAMS
        
        ParamCategories --> NOMENCLATURE
    end

    %% === WORKFLOW DISTRIBUTION ===
    subgraph DistributionWorkflow["📦 WORKFLOW DISTRIBUTION"]
        SCHEDULE --> GENERATE_DCPS[Génération DCPs]
        GENERATE_DCPS --> SECURE_URLS[URLs Sécurisées]
        SECURE_URLS --> CINEMA_DELIVERY[Livraison Cinémas]
        CINEMA_DELIVERY --> DOWNLOAD[Téléchargement]
        DOWNLOAD --> PROJECTION[Projection]
    end

    %% === WORKFLOW MONITORING ===
    subgraph MonitoringWorkflow["📊 WORKFLOW MONITORING"]
        subgraph Notifications["🔔 Notifications"]
            JOB_SUCCESS[Job Completed]
            JOB_FAILED[Job Failed]
            DEADLINE_ALERT[Deadline Alert]
        end
        
        subgraph Logging["📝 Audit & Logs"]
            ACTIVITY_LOG[Activity Log]
            USER_ACTIONS[Actions Utilisateur]
            SYSTEM_EVENTS[Événements Système]
        end
        
        subgraph Analytics["📈 Analytics"]
            FESTIVAL_STATS[Stats Festival]
            CINEMA_METRICS[Métriques Cinéma]
            PERFORMANCE[Performance Jobs]
        end
        
        APPROVED --> JOB_SUCCESS
        REJECTED --> JOB_FAILED
        DEADLINES --> DEADLINE_ALERT
        
        START --> ACTIVITY_LOG
        TECH_REVIEW --> USER_ACTIONS
        QUEUE1 --> SYSTEM_EVENTS
        
        FESTIVAL_SELECTION --> FESTIVAL_STATS
        CINEMA_DELIVERY --> CINEMA_METRICS
        ANALYSIS --> PERFORMANCE
    end

    %% === WORKFLOW SÉCURITÉ ===
    subgraph SecurityWorkflow["🔐 WORKFLOW SÉCURITÉ"]
        subgraph Access["Contrôle d'Accès"]
            ROLES[Rôles & Permissions]
            USER_FESTIVAL[Assignation Festival]
            TOKEN_AUTH[Auth par Token]
        end
        
        DIST --> ROLES
        FEST --> ROLES  
        TECH --> ROLES
        CINE --> ROLES
        API --> TOKEN_AUTH
        
        ROLES --> USER_FESTIVAL
        USER_FESTIVAL --> CONFIG_FEST
        TOKEN_AUTH --> UPLOAD
    end

    %% === WORKFLOW JOBS/QUEUES ===
    subgraph JobWorkflow["⚡ WORKFLOW JOBS ASYNC"]
        UPLOAD --> PROCESS_JOB[ProcessDcpUploadJob]
        PROCESS_JOB --> ANALYSIS_JOB[DcpAnalysisJob]
        ANALYSIS_JOB --> METADATA_JOB[MetadataExtractionJob]
        METADATA_JOB --> VALIDATION_JOB[DcpValidationJob]
        VALIDATION_JOB --> NOMENCLATURE_JOB[NomenclatureGenerationJob]
        
        PROCESS_JOB --> BATCH_JOB[BatchProcessingJob]
        BATCH_JOB --> ENHANCED_ANALYSIS[EnhancedDcpAnalysisJob]
        
        JobWorkflow --> MonitoringWorkflow
    end

    %% === CONNEXIONS INTER-WORKFLOWS ===
    DIST --> START
    FEST --> SUBMISSION
    TECH --> TECH_REVIEW
    CINE --> DOWNLOAD
    
    %% Feedback loops
    REJECTED --> START
    RECOMMENDATIONS --> UPLOAD
    ISSUE_DETECTION --> TECH_REVIEW
    
    %% Status updates
    APPROVED --> FESTIVAL_STATS
    SCHEDULE --> CINEMA_METRICS
    BACKBLAZE --> PERFORMANCE

    %% Styling
    classDef actorClass fill:#e1f5fe,stroke:#01579b,stroke-width:2px
    classDef workflowClass fill:#f3e5f5,stroke:#4a148c,stroke-width:2px
    classDef processClass fill:#e8f5e8,stroke:#1b5e20,stroke-width:2px
    classDef decisionClass fill:#fff3e0,stroke:#e65100,stroke-width:2px
    classDef storageClass fill:#fce4ec,stroke:#880e4f,stroke-width:2px
    
    class DIST,FEST,TECH,CINE,API actorClass
    class START,APPROVED,REJECTED processClass
    class VALIDATION,CONFORMITY decisionClass
    class BACKBLAZE,SECURE_URLS storageClass
```

## Légende des Workflows

### 🎬 **Workflow Principal**
Cycle de vie complet d'un film : création → upload → analyse → validation → distribution

### 🌐 **Workflow Versions** 
Gestion des versions linguistiques multiples avec nomenclature automatique

### 📅 **Workflow Programmation**
Planification des séances et association versions-cinémas (votre exemple)

### 🏆 **Workflow Festival**
Configuration multi-festival avec règles spécifiques et deadlines

### ⚙️ **Workflow Technique**
Analyse automatisée DCP avec vérification conformité standards cinéma

### 🔧 **Workflow Paramètres**
Extraction et gestion des métadonnées par catégories

### 📦 **Workflow Distribution** 
Génération et livraison des DCPs aux cinémas

### 📊 **Workflow Monitoring**
Surveillance, notifications et analytics en temps réel

### 🔐 **Workflow Sécurité**
Gestion des accès, rôles et authentification

### ⚡ **Workflow Jobs Async**
Architecture de jobs asynchrones pour traitement haute performance

## Points de Croisement Critiques

1. **Hub Central** : Le statut "Film Validé" connecte tous les workflows
2. **Nomenclature** : Point de convergence paramètres → versions → festivals
3. **Queue System** : Orchestration asynchrone de tous les traitements
4. **Monitoring** : Surveillance transversale de tous les processus
5. **Sécurité** : Contrôle d'accès sur tous les workflows

Cette architecture permet une **orchestration complète** de la chaîne de distribution cinéma numérique avec **traçabilité totale** et **scalabilité industrielle**.
