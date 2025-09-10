<?php

return [
    
    /*
    |--------------------------------------------------------------------------
    | Spécifications DCP (Digital Cinema Package)
    |--------------------------------------------------------------------------
    |
    | Configuration exhaustive des spécifications techniques pour les DCP
    | selon les standards Interop et SMPTE utilisés dans l'industrie cinématographique.
    |
    */

    // =========================================
    // SPÉCIFICATIONS VIDÉO
    // =========================================
    'video' => [
        'resolutions' => [
            '2K' => [
                'width' => 2048,
                'height' => 1080,
                'description' => 'DCI 2K (2048×1080)',
                'standard' => ['Interop', 'SMPTE'],
            ],
            '4K' => [
                'width' => 4096,
                'height' => 2160,
                'description' => 'DCI 4K (4096×2160)',
                'standard' => ['SMPTE'],
            ],
            '8K' => [
                'width' => 8192,
                'height' => 4320,
                'description' => '8K (rare, spécifique)',
                'standard' => ['SMPTE'],
            ],
        ],
        
        'frame_rates' => [
            '24' => [
                'fps' => 24,
                'description' => '24 fps (standard cinéma)',
                'standard' => ['Interop', 'SMPTE'],
            ],
            '25' => [
                'fps' => 25,
                'description' => '25 fps (européen)',
                'standard' => ['Interop', 'SMPTE'],
            ],
            '30' => [
                'fps' => 30,
                'description' => '30 fps (rare)',
                'standard' => ['SMPTE'],
            ],
            'HFR' => [
                'fps' => [48, 60, 120],
                'description' => 'High Frame Rate',
                'standard' => ['SMPTE'],
            ],
        ],

        'aspect_ratios' => [
            'Flat' => [
                'ratio' => '1.85:1',
                'description' => 'Format Flat (1.85:1)',
            ],
            'Scope' => [
                'ratio' => '2.39:1',
                'description' => 'Format Scope (2.39:1)',
            ],
            'Full Container' => [
                'ratio' => '1.90:1',
                'description' => 'Full container',
            ],
        ],

        'color' => [
            'color_space' => 'XYZ',
            'standard' => 'DCI standard',
            'compression' => 'JPEG2000',
            'bit_depth' => '12-bit',
        ],

        '3d' => [
            '2D' => 'Projection 2D standard',
            '3D' => 'Projection 3D stéréoscopique (polarisation, active/passive)',
        ],

        'hdr' => [
            'SDR' => 'Standard Dynamic Range',
            'Dolby Vision' => 'Dolby Vision (spécifique à certains formats propriétaires)',
            'HDR10' => 'HDR10 (rare en DCP)',
        ],
    ],

    // =========================================
    // SPÉCIFICATIONS AUDIO
    // =========================================
    'audio' => [
        'channels' => [
            '5.1' => [
                'channels' => 6,
                'description' => '5.1 surround (standard)',
                'standard' => ['Interop', 'SMPTE'],
            ],
            '7.1' => [
                'channels' => 8,
                'description' => '7.1 surround',
                'standard' => ['SMPTE'],
            ],
            'Dolby Atmos' => [
                'description' => 'Dolby Atmos (object-based)',
                'standard' => ['SMPTE'],
            ],
            'Auro 11.1' => [
                'channels' => 12,
                'description' => 'Auro 11.1',
                'standard' => ['SMPTE'],
            ],
            'Mono' => [
                'channels' => 1,
                'description' => 'Mono (rare, archives)',
                'standard' => ['Interop', 'SMPTE'],
            ],
            'Stereo' => [
                'channels' => 2,
                'description' => 'Stéréo (rare pour cinéma)',
                'standard' => ['Interop', 'SMPTE'],
            ],
        ],

        'sample_rates' => [
            '48 kHz' => [
                'frequency' => 48000,
                'description' => '48 kHz (standard)',
                'standard' => ['Interop', 'SMPTE'],
            ],
            '96 kHz' => [
                'frequency' => 96000,
                'description' => '96 kHz (optionnel)',
                'standard' => ['SMPTE'],
            ],
        ],

        'bit_depth' => [
            'depth' => '24-bit',
            'format' => 'PCM non compressé',
        ],

        'versions' => [
            'OV' => 'Original Version',
            'VF' => 'Version Française',
            'VO' => 'Version Originale',
            'VOST' => 'Version Originale Sous-Titrée',
        ],
    ],

    // =========================================
    // SPÉCIFICATIONS ACCESSIBILITÉ
    // =========================================
    'accessibility' => [
        'subtitles' => [
            'Open subtitles' => 'Sous-titres brûlés dans l\'image',
            'Closed captions' => 'Sous-titres XML pour lecteurs externes',
            'Multi-language' => 'Multiples langues possibles',
        ],

        'hearing_impaired' => [
            'description' => 'Piste audio séparée (commentaire simplifié, mixage spécifique)',
        ],

        'audio_description' => [
            'description' => 'VI-N (Visually Impaired Narrative / Audiodescription)',
            'format' => 'Piste audio additionnelle',
        ],

        'languages' => [
            'FR' => 'Français',
            'EN' => 'English',
            'ES' => 'Español',
            'DE' => 'Deutsch',
            'IT' => 'Italiano',
            'PT' => 'Português',
            'NL' => 'Nederlands',
        ],
    ],

    // =========================================
    // SPÉCIFICATIONS FORMAT
    // =========================================
    'format' => [
        'dcp_types' => [
            'Interop DCP' => [
                'description' => 'Ancien standard, moins flexible',
                'max_audio_channels' => '5.1',
            ],
            'SMPTE DCP' => [
                'description' => 'Standard actuel, recommandé',
                'features' => ['Atmos possible', 'Plus flexible'],
            ],
        ],

        'encryption' => [
            'Non chiffré' => 'DCP non sécurisé',
            'Chiffré (avec KDM)' => 'Chiffré avec Key Delivery Message',
        ],

        'content_types' => [
            'FTR' => 'Feature film (long métrage)',
            'SHR' => 'Short (court métrage)',
            'TLR' => 'Trailer (bande-annonce)',
            'ADV' => 'Advertisement (publicité)',
            'POL' => 'Policy (politique)',
        ],

        'structure' => [
            'CPL' => 'Composition Playlist - définit quelle version est projetée',
            'PKL' => 'Packing list - liste des assets',
            'ASSETMAP' => 'Index du contenu',
            'Subtitles' => 'XML + police optionnelle',
            'Certificates' => 'Sécurité et signatures',
        ],
    ],

    // =========================================
    // SPÉCIFICATIONS TECHNIQUES
    // =========================================
    'technical' => [
        'file_size_limits' => [
            'min' => 0.1, // GB
            'max' => 500, // GB
            'typical_2k' => [50, 150], // GB range
            'typical_4k' => [150, 400], // GB range
        ],

        'validation' => [
            'checksum_algorithms' => ['SHA-1', 'MD5'],
            'integrity_check' => true,
        ],

        'mastering' => [
            'date_format' => 'YYYY-MM-DD',
            'required_metadata' => ['title', 'duration', 'resolution', 'audio_format'],
        ],
    ],

    // =========================================
    // MÉTADONNÉES & GESTION
    // =========================================
    'metadata' => [
        'title_format' => [
            'pattern' => 'MOVIE_TITLE_FTR_F_EN-XX_51_2K_20250902_OV',
            'components' => [
                'title' => 'Titre du film',
                'content_type' => 'FTR/SHR/TLR/etc.',
                'version' => 'OV/VF/VOST/etc.',
                'territory' => 'FR-FR/EN-US/etc.',
                'audio' => '51/71/ATMOS/etc.',
                'resolution' => '2K/4K/etc.',
                'date' => 'YYYYMMDD',
                'type' => 'OV/VF/etc.',
            ],
        ],

        'territories' => [
            'FR-FR' => 'France',
            'EN-US' => 'États-Unis',
            'DE-AT' => 'Allemagne/Autriche',
            'ES-ES' => 'Espagne',
            'IT-IT' => 'Italie',
        ],

        'countries' => [
            'FR' => 'France',
            'US' => 'États-Unis',
            'GB' => 'Royaume-Uni',
            'DE' => 'Allemagne',
            'IT' => 'Italie',
            'ES' => 'Espagne',
            'CA' => 'Canada',
            'AU' => 'Australie',
            'JP' => 'Japon',
            'KR' => 'Corée du Sud',
            'CN' => 'Chine',
        ],

        'genres' => [
            'DRAMA' => 'Drame',
            'COMEDY' => 'Comédie',
            'ACTION' => 'Action',
            'THRILLER' => 'Thriller',
            'HORROR' => 'Horreur',
            'SCI-FI' => 'Science-Fiction',
            'ROMANCE' => 'Romance',
            'DOCUMENTARY' => 'Documentaire',
            'ANIMATION' => 'Animation',
            'FANTASY' => 'Fantastique',
        ],
    ],

    // =========================================
    // CONFIGURATION DE VALIDATION
    // =========================================
    'validation' => [
        'required_parameters' => [
            'video' => ['resolution', 'frame_rate', 'aspect_ratio'],
            'audio' => ['channels', 'sample_rate', 'bit_depth'],
            'format' => ['dcp_type', 'content_type'],
            'metadata' => ['title', 'year'],
        ],

        'parameter_ranges' => [
            'year' => [
                'min' => 1900,
                'max' => 2030,
            ],
            'duration' => [
                'min' => 1, // minutes
                'max' => 600, // minutes
            ],
            'file_size' => [
                'min' => 0.1, // GB
                'max' => 500, // GB
            ],
        ],
    ],

    // =========================================
    // STANDARDS ET COMPATIBILITÉ
    // =========================================
    'standards' => [
        'Interop' => [
            'description' => 'Standard historique',
            'limitations' => [
                'Audio maximum 5.1',
                'Moins de flexibilité',
            ],
        ],
        'SMPTE' => [
            'description' => 'Standard actuel recommandé',
            'features' => [
                'Support Dolby Atmos',
                'Plus grande flexibilité',
                'Meilleure sécurité',
            ],
        ],
    ],

    // =========================================
    // EXTRACTION AUTOMATIQUE
    // =========================================
    'extraction' => [
        'patterns' => [
            'resolution' => 'MainPicture|Resolution',
            'frame_rate' => 'EditRate',
            'audio_channels' => 'MainSound|Channels',
            'duration' => 'Duration',
            'language' => 'MainLanguage',
            'color_space' => 'ColorSpace',
            'encryption' => 'Encrypted',
            'uuid' => 'UUID',
        ],

        'confidence_thresholds' => [
            'high' => 0.9,
            'medium' => 0.7,
            'low' => 0.5,
        ],
    ],
];
