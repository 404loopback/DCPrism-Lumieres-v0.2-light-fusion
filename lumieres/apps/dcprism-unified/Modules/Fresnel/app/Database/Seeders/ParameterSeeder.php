<?php

namespace Modules\Fresnel\app\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Fresnel\app\Models\Parameter;

class ParameterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Paramètres basés sur les normes SMPTE pour DCP (Digital Cinema Packages)
     * Organisés selon les catégories métier du système DCPrism
     */
    public function run(): void
    {
        // Vider les paramètres existants (optionnel - décommenter si besoin)
        // Parameter::truncate();

        // === CATÉGORIE: CONTENT (Contenu du film) ===
        $contentParameters = [
            [
                'name' => 'Titre Principal',
                'code' => 'MAIN_TITLE',
                'type' => Parameter::TYPE_STRING,
                'category' => 'content',
                'description' => 'Titre principal du film tel qu\'il apparaîtra dans les nomenclatures',
                'is_system' => true,
                'is_active' => true,
                'extraction_source' => Parameter::SOURCE_DCP,
                'validation_rules' => ['required', 'max:255']
            ],
            [
                'name' => 'Titre Original',
                'code' => 'ORIGINAL_TITLE',
                'type' => Parameter::TYPE_STRING,
                'category' => "content",
                'description' => 'Titre original du film dans sa langue d\'origine',
                'is_system' => false,
                'is_active' => true,
                'extraction_source' => Parameter::SOURCE_METADATA,
                'validation_rules' => ['max:255']
            ],
            [
                'name' => 'Année de Production',
                'code' => 'PRODUCTION_YEAR',
                'type' => Parameter::TYPE_INT,
                'category' => "content",
                'description' => 'Année de production du film',
                'is_system' => false,
                'is_active' => true,
                'extraction_source' => Parameter::SOURCE_METADATA,
                'validation_rules' => ['integer', 'min:1900', 'max:2100']
            ],
            [
                'name' => 'Genre',
                'code' => 'GENRE',
                'type' => Parameter::TYPE_STRING,
                'category' => "content",
                'description' => 'Genre cinématographique du film',
                'possible_values' => ['Action', 'Comédie', 'Drame', 'Horreur', 'Romance', 'Science-Fiction', 'Thriller', 'Documentaire', 'Animation', 'Famille'],
                'is_system' => false,
                'is_active' => true,
                'extraction_source' => Parameter::SOURCE_MANUAL
            ],
            [
                'name' => 'Réalisateur',
                'code' => 'DIRECTOR',
                'type' => Parameter::TYPE_STRING,
                'category' => "content",
                'description' => 'Nom du réalisateur principal',
                'is_system' => false,
                'is_active' => true,
                'extraction_source' => Parameter::SOURCE_METADATA,
                'validation_rules' => ['max:255']
            ],
            [
                'name' => 'Producteur',
                'code' => 'PRODUCER',
                'type' => Parameter::TYPE_STRING,
                'category' => "content",
                'description' => 'Nom du producteur principal',
                'is_system' => false,
                'is_active' => true,
                'extraction_source' => Parameter::SOURCE_METADATA,
                'validation_rules' => ['max:255']
            ],
            [
                'name' => 'Pays d\'Origine',
                'code' => 'ORIGIN_COUNTRY',
                'type' => Parameter::TYPE_STRING,
                'category' => "content",
                'description' => 'Pays de production principale du film',
                'is_system' => false,
                'is_active' => true,
                'extraction_source' => Parameter::SOURCE_METADATA
            ],
            [
                'name' => 'Classification',
                'code' => 'RATING',
                'type' => Parameter::TYPE_STRING,
                'category' => "content",
                'description' => 'Classification d\'âge du film (CSA, MPAA, etc.)',
                'possible_values' => ['Tous publics', '-12', '-16', '-18', 'G', 'PG', 'PG-13', 'R', 'NC-17'],
                'is_system' => false,
                'is_active' => true,
                'extraction_source' => Parameter::SOURCE_MANUAL
            ]
        ];

        // === CATÉGORIE: VIDEO (Paramètres vidéo SMPTE) ===
        $videoParameters = [
            [
                'name' => 'Résolution d\'Image',
                'code' => 'IMAGE_RESOLUTION',
                'type' => Parameter::TYPE_STRING,
                'category' => 'video',
                'description' => 'Résolution de l\'image selon SMPTE (2K, 4K, etc.)',
                'possible_values' => ['2K (2048x1080)', '4K (4096x2160)', 'HD (1920x1080)', 'UHD (3840x2160)'],
                'is_system' => true,
                'is_active' => true,
                'extraction_source' => Parameter::SOURCE_DCP,
                'validation_rules' => ['required']
            ],
            [
                'name' => 'Ratio d\'Aspect',
                'code' => 'ASPECT_RATIO',
                'type' => Parameter::TYPE_STRING,
                'category' => "video",
                'description' => 'Ratio d\'aspect de l\'image (SMPTE ST 428-1)',
                'possible_values' => ['1.85:1', '2.39:1', '1.33:1', '1.66:1', '2.35:1', '1.77:1', '1.90:1'],
                'is_system' => true,
                'is_active' => true,
                'extraction_source' => Parameter::SOURCE_DCP,
                'validation_rules' => ['required']
            ],
            [
                'name' => 'Fréquence d\'Images',
                'code' => 'FRAME_RATE',
                'type' => Parameter::TYPE_STRING,
                'category' => "video",
                'description' => 'Fréquence d\'images par seconde selon SMPTE',
                'possible_values' => ['24', '25', '48', '50', '60', '23.976', '29.97', '59.94'],
                'is_system' => true,
                'is_active' => true,
                'extraction_source' => Parameter::SOURCE_DCP,
                'validation_rules' => ['required']
            ],
            [
                'name' => 'Profil Couleur',
                'code' => 'COLOR_PROFILE',
                'type' => Parameter::TYPE_STRING,
                'category' => "video",
                'description' => 'Profil de couleur SMPTE (DCI-P3, Rec.709, etc.)',
                'possible_values' => ['DCI-P3', 'Rec.709', 'Rec.2020', 'XYZ', 'ACES'],
                'is_system' => true,
                'is_active' => true,
                'extraction_source' => Parameter::SOURCE_DCP
            ],
            [
                'name' => 'Encodage Vidéo',
                'code' => 'VIDEO_ENCODING',
                'type' => Parameter::TYPE_STRING,
                'category' => "video",
                'description' => 'Type d\'encodage vidéo utilisé (JPEG2000, etc.)',
                'possible_values' => ['JPEG2000', 'H.264', 'H.265', 'ProRes'],
                'is_system' => true,
                'is_active' => true,
                'extraction_source' => Parameter::SOURCE_DCP
            ],
            [
                'name' => 'Débit Vidéo Maximum',
                'code' => 'MAX_VIDEO_BITRATE',
                'type' => Parameter::TYPE_INT,
                'category' => "video",
                'description' => 'Débit vidéo maximum autorisé par SMPTE (Mbps)',
                'is_system' => false,
                'is_active' => true,
                'extraction_source' => Parameter::SOURCE_DCP,
                'validation_rules' => ['integer', 'min:1', 'max:1000']
            ],
            [
                'name' => 'Profondeur de Couleur',
                'code' => 'BIT_DEPTH',
                'type' => Parameter::TYPE_STRING,
                'category' => "video",
                'description' => 'Profondeur de couleur en bits par canal',
                'possible_values' => ['8-bit', '10-bit', '12-bit', '16-bit'],
                'is_system' => true,
                'is_active' => true,
                'extraction_source' => Parameter::SOURCE_DCP
            ],
            [
                'name' => 'Mode d\'Entrelacement',
                'code' => 'INTERLACE_MODE',
                'type' => Parameter::TYPE_STRING,
                'category' => "video",
                'description' => 'Mode d\'entrelacement de l\'image',
                'possible_values' => ['Progressive', 'Interlaced', 'PsF (Progressive segmented Frame)'],
                'is_system' => false,
                'is_active' => true,
                'extraction_source' => Parameter::SOURCE_DCP
            ]
        ];

        // === CATÉGORIE: AUDIO (Paramètres audio SMPTE) ===
        $audioParameters = [
            [
                'name' => 'Langue Audio Principale',
                'code' => 'MAIN_AUDIO_LANG',
                'type' => Parameter::TYPE_STRING,
                'category' => "audio",
                'description' => 'Langue principale de la piste audio (ISO 639-1)',
                'is_system' => true,
                'is_active' => true,
                'extraction_source' => Parameter::SOURCE_DCP,
                'validation_rules' => ['required', 'size:2']
            ],
            [
                'name' => 'Configuration Audio',
                'code' => 'AUDIO_CONFIG',
                'type' => Parameter::TYPE_STRING,
                'category' => "audio",
                'description' => 'Configuration des canaux audio selon SMPTE',
                'possible_values' => ['Mono (1.0)', 'Stéréo (2.0)', '5.1', '7.1', 'Atmos', 'DTS:X'],
                'is_system' => true,
                'is_active' => true,
                'extraction_source' => Parameter::SOURCE_DCP,
                'validation_rules' => ['required']
            ],
            [
                'name' => 'Fréquence d\'Échantillonnage',
                'code' => 'SAMPLE_RATE',
                'type' => Parameter::TYPE_STRING,
                'category' => "audio",
                'description' => 'Fréquence d\'échantillonnage audio en Hz',
                'possible_values' => ['48000', '96000', '44100', '88200', '192000'],
                'is_system' => true,
                'is_active' => true,
                'extraction_source' => Parameter::SOURCE_DCP,
                'default_value' => '48000'
            ],
            [
                'name' => 'Résolution Audio',
                'code' => 'AUDIO_BIT_DEPTH',
                'type' => Parameter::TYPE_STRING,
                'category' => "audio",
                'description' => 'Résolution audio en bits',
                'possible_values' => ['16-bit', '24-bit', '32-bit'],
                'is_system' => true,
                'is_active' => true,
                'extraction_source' => Parameter::SOURCE_DCP,
                'default_value' => '24-bit'
            ],
            [
                'name' => 'Encodage Audio',
                'code' => 'AUDIO_ENCODING',
                'type' => Parameter::TYPE_STRING,
                'category' => "audio",
                'description' => 'Format d\'encodage audio utilisé',
                'possible_values' => ['PCM', 'Dolby Digital', 'DTS', 'Dolby Atmos', 'PCM Uncompressed'],
                'is_system' => false,
                'is_active' => true,
                'extraction_source' => Parameter::SOURCE_DCP
            ],
            [
                'name' => 'Niveau de Référence',
                'code' => 'REFERENCE_LEVEL',
                'type' => Parameter::TYPE_STRING,
                'category' => "audio",
                'description' => 'Niveau de référence audio selon SMPTE (dBFS)',
                'possible_values' => ['-18 dBFS', '-20 dBFS', '-23 dBFS', '-31 dBFS'],
                'is_system' => false,
                'is_active' => true,
                'extraction_source' => Parameter::SOURCE_DCP,
                'default_value' => '-20 dBFS'
            ],
            [
                'name' => 'Mixage Audio',
                'code' => 'AUDIO_MIX_TYPE',
                'type' => Parameter::TYPE_STRING,
                'category' => "audio",
                'description' => 'Type de mixage audio (standard, immersif, etc.)',
                'possible_values' => ['Standard', 'Immersif (Atmos)', 'Binaural', 'Ambisonique'],
                'is_system' => false,
                'is_active' => true,
                'extraction_source' => Parameter::SOURCE_MANUAL
            ]
        ];

        // === CATÉGORIE: SUBTITLE (Sous-titres) ===
        $subtitleParameters = [
            [
                'name' => 'Langue des Sous-titres',
                'code' => 'SUBTITLE_LANG',
                'type' => Parameter::TYPE_STRING,
                'category' => "accessibility",
                'description' => 'Langue des sous-titres (ISO 639-1)',
                'is_system' => false,
                'is_active' => true,
                'extraction_source' => Parameter::SOURCE_DCP,
                'validation_rules' => ['size:2']
            ],
            [
                'name' => 'Type de Sous-titres',
                'code' => 'SUBTITLE_TYPE',
                'type' => Parameter::TYPE_STRING,
                'category' => "accessibility",
                'description' => 'Type de sous-titres selon SMPTE',
                'possible_values' => ['Burned-in', 'Open Caption', 'Closed Caption', 'Subtitle', 'Forced Narrative'],
                'is_system' => false,
                'is_active' => true,
                'extraction_source' => Parameter::SOURCE_DCP
            ],
            [
                'name' => 'Format des Sous-titres',
                'code' => 'SUBTITLE_FORMAT',
                'type' => Parameter::TYPE_STRING,
                'category' => "accessibility",
                'description' => 'Format technique des sous-titres',
                'possible_values' => ['SMPTE-TT', 'Interop Subtitle', 'SRT', 'ASS', 'VTT'],
                'is_system' => false,
                'is_active' => true,
                'extraction_source' => Parameter::SOURCE_DCP
            ],
            [
                'name' => 'Position des Sous-titres',
                'code' => 'SUBTITLE_POSITION',
                'type' => Parameter::TYPE_STRING,
                'category' => "accessibility",
                'description' => 'Position par défaut des sous-titres à l\'écran',
                'possible_values' => ['Bottom', 'Top', 'Center', 'Custom'],
                'is_system' => false,
                'is_active' => true,
                'extraction_source' => Parameter::SOURCE_MANUAL,
                'default_value' => 'Bottom'
            ]
        ];

        // === CATÉGORIE: TECHNICAL (Paramètres techniques DCP) ===
        $technicalParameters = [
            [
                'name' => 'Durée Totale',
                'code' => 'TOTAL_DURATION',
                'type' => Parameter::TYPE_STRING,
                'category' => "technical",
                'description' => 'Durée totale du contenu (format HH:MM:SS)',
                'is_system' => true,
                'is_active' => true,
                'extraction_source' => Parameter::SOURCE_DCP,
                'validation_rules' => ['required', 'regex:/^\\d{2}:\\d{2}:\\d{2}$/']
            ],
            [
                'name' => 'UUID du Package',
                'code' => 'PACKAGE_UUID',
                'type' => Parameter::TYPE_STRING,
                'category' => "technical",
                'description' => 'Identifiant unique du package DCP (UUID)',
                'is_system' => true,
                'is_active' => true,
                'extraction_source' => Parameter::SOURCE_DCP,
                'validation_rules' => ['uuid']
            ],
            [
                'name' => 'Version SMPTE',
                'code' => 'SMPTE_VERSION',
                'type' => Parameter::TYPE_STRING,
                'category' => "technical",
                'description' => 'Version de la norme SMPTE utilisée',
                'possible_values' => ['SMPTE (2007-2014)', 'Interop (2005)', 'SMPTE ST 429-2', 'SMPTE ST 2067-21'],
                'is_system' => true,
                'is_active' => true,
                'extraction_source' => Parameter::SOURCE_DCP
            ],
            [
                'name' => 'Taille du Package',
                'code' => 'PACKAGE_SIZE',
                'type' => Parameter::TYPE_FLOAT,
                'category' => "technical",
                'description' => 'Taille totale du package DCP en GB',
                'is_system' => true,
                'is_active' => true,
                'extraction_source' => Parameter::SOURCE_DCP,
                'validation_rules' => ['numeric', 'min:0']
            ],
            [
                'name' => 'Nombre de Bobines',
                'code' => 'REEL_COUNT',
                'type' => Parameter::TYPE_INT,
                'category' => "technical",
                'description' => 'Nombre de bobines (reels) dans le DCP',
                'is_system' => true,
                'is_active' => true,
                'extraction_source' => Parameter::SOURCE_DCP,
                'validation_rules' => ['integer', 'min:1', 'max:100']
            ],
            [
                'name' => 'Type de Chiffrement',
                'code' => 'ENCRYPTION_TYPE',
                'type' => Parameter::TYPE_STRING,
                'category' => "technical",
                'description' => 'Type de chiffrement utilisé pour sécuriser le contenu',
                'possible_values' => ['None', 'AES-128-CBC', 'AES-256-CBC', 'KDM Protected'],
                'is_system' => false,
                'is_active' => true,
                'extraction_source' => Parameter::SOURCE_DCP
            ],
            [
                'name' => 'Standard de Projection',
                'code' => 'PROJECTION_STANDARD',
                'type' => Parameter::TYPE_STRING,
                'category' => "technical",
                'description' => 'Standard de projection supporté',
                'possible_values' => ['DCI Compliant', 'SMPTE Compliant', 'Interop', 'Custom'],
                'is_system' => false,
                'is_active' => true,
                'extraction_source' => Parameter::SOURCE_DCP
            ]
        ];

        // === CATÉGORIE: ACCESSIBILITY (Accessibilité) ===
        $accessibilityParameters = [
            [
                'name' => 'Audiodescription',
                'code' => 'AUDIO_DESCRIPTION',
                'type' => Parameter::TYPE_BOOL,
                'category' => "accessibility",
                'description' => 'Présence d\'une piste audiodescription pour malvoyants',
                'is_system' => false,
                'is_active' => true,
                'extraction_source' => Parameter::SOURCE_DCP,
                'default_value' => false
            ],
            [
                'name' => 'Sous-titres Malentendants',
                'code' => 'HEARING_IMPAIRED_SUBTITLES',
                'type' => Parameter::TYPE_BOOL,
                'category' => "accessibility",
                'description' => 'Sous-titres adaptés pour malentendants (SDH)',
                'is_system' => false,
                'is_active' => true,
                'extraction_source' => Parameter::SOURCE_DCP,
                'default_value' => false
            ],
            [
                'name' => 'Langue des Signes',
                'code' => 'SIGN_LANGUAGE',
                'type' => Parameter::TYPE_BOOL,
                'category' => "accessibility",
                'description' => 'Présence d\'interprétation en langue des signes',
                'is_system' => false,
                'is_active' => true,
                'extraction_source' => Parameter::SOURCE_MANUAL,
                'default_value' => false
            ],
            [
                'name' => 'Contraste Élevé',
                'code' => 'HIGH_CONTRAST',
                'type' => Parameter::TYPE_BOOL,
                'category' => "accessibility",
                'description' => 'Version à contraste élevé disponible',
                'is_system' => false,
                'is_active' => true,
                'extraction_source' => Parameter::SOURCE_MANUAL,
                'default_value' => false
            ]
        ];

        // === CATÉGORIE: METADATA (Métadonnées de gestion) ===
        $metadataParameters = [
            [
                'name' => 'Distributeur',
                'code' => 'DISTRIBUTOR',
                'type' => Parameter::TYPE_STRING,
                'category' => 'metadata',
                'description' => 'Nom du distributeur du film',
                'is_system' => false,
                'is_active' => true,
                'extraction_source' => Parameter::SOURCE_METADATA,
                'validation_rules' => ['max:255']
            ],
            [
                'name' => 'Date de Création DCP',
                'code' => 'DCP_CREATION_DATE',
                'type' => Parameter::TYPE_DATE,
                'category' => 'metadata',
                'description' => 'Date de création du package DCP',
                'is_system' => true,
                'is_active' => true,
                'extraction_source' => Parameter::SOURCE_DCP
            ],
            [
                'name' => 'Créateur du DCP',
                'code' => 'DCP_CREATOR',
                'type' => Parameter::TYPE_STRING,
                'category' => 'metadata',
                'description' => 'Société ou personne ayant créé le DCP',
                'is_system' => false,
                'is_active' => true,
                'extraction_source' => Parameter::SOURCE_METADATA,
                'validation_rules' => ['max:255']
            ],
            [
                'name' => 'Version du DCP',
                'code' => 'DCP_VERSION',
                'type' => Parameter::TYPE_STRING,
                'category' => 'metadata',
                'description' => 'Numéro de version du DCP',
                'is_system' => false,
                'is_active' => true,
                'extraction_source' => Parameter::SOURCE_METADATA
            ],
            [
                'name' => 'Commentaires Techniques',
                'code' => 'TECHNICAL_NOTES',
                'type' => Parameter::TYPE_STRING,
                'category' => 'metadata',
                'description' => 'Notes techniques ou commentaires sur le DCP',
                'is_system' => false,
                'is_active' => true,
                'extraction_source' => Parameter::SOURCE_MANUAL
            ],
            [
                'name' => 'Statut de Validation',
                'code' => 'VALIDATION_STATUS',
                'type' => Parameter::TYPE_STRING,
                'category' => 'metadata',
                'description' => 'Statut de validation technique du DCP',
                'possible_values' => ['Pending', 'Valid', 'Invalid', 'Warning', 'Not Tested'],
                'is_system' => true,
                'is_active' => true,
                'extraction_source' => Parameter::SOURCE_AUTO,
                'default_value' => 'Pending'
            ]
        ];

        // Fusionner tous les paramètres
        $allParameters = array_merge(
            $contentParameters,
            $videoParameters,
            $audioParameters,
            $subtitleParameters,
            $technicalParameters,
            $accessibilityParameters,
            $metadataParameters
        );

        // Créer les paramètres en base de données (éviter les doublons)
        $created = 0;
        $updated = 0;
        
        foreach ($allParameters as $parameterData) {
            $parameter = Parameter::firstOrCreate(
                ['code' => $parameterData['code']],
                $parameterData
            );
            
            if ($parameter->wasRecentlyCreated) {
                $created++;
            } else {
                $updated++;
            }
        }

        $this->command->info('✅ ' . $created . ' paramètres SMPTE créés et ' . $updated . ' mis à jour avec succès !');
        $this->command->info('📊 Répartition par catégorie :');
        $this->command->info('   • Content: ' . count($contentParameters));
        $this->command->info('   • Video: ' . count($videoParameters));
        $this->command->info('   • Audio: ' . count($audioParameters));
        $this->command->info('   • Subtitle: ' . count($subtitleParameters));
        $this->command->info('   • Technical: ' . count($technicalParameters));
        $this->command->info('   • Accessibility: ' . count($accessibilityParameters));
        $this->command->info('   • Metadata: ' . count($metadataParameters));
    }
}
