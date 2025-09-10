<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Parameter;

class NewDCPParametersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * NOUVEAUX paramètres DCP uniquement - évite les doublons avec les paramètres existants :
     * AUDIO_LANG, ASPECT_RATIO, FRAME_RATE, TITLE, VERSION_TYPE, FORMAT, DURATION, YEAR, SUBTITLES
     */
    public function run(): void
    {
        echo "NewDCPParametersSeeder running...\n";
        
        $parameters = [
            
            // =========================================
            // CATÉGORIE VIDEO (nouveaux uniquement)
            // =========================================
            [
                'name' => 'Resolution',
                'code' => 'RESOLUTION',
                'type' => 'string',
                'category' => 'video',
                'description' => 'Résolution vidéo du DCP',
                'is_required' => true,
                'is_active' => true,
                'extraction_source' => 'DCP',
                'extraction_pattern' => 'MainPicture|Resolution',
                'possible_values' => ['2K', '4K', '8K'],
            ],
            [
                'name' => 'Color Space',
                'code' => 'COLOR_SPACE',
                'type' => 'string',
                'category' => 'video',
                'description' => 'Espace colorimétrique',
                'is_required' => true,
                'is_active' => true,
                'extraction_source' => 'DCP',
                'extraction_pattern' => 'ColorSpace',
                'possible_values' => ['XYZ', 'RGB'],
            ],
            [
                'name' => 'Compression',
                'code' => 'COMPRESSION',
                'type' => 'string',
                'category' => 'video',
                'description' => 'Type de compression vidéo',
                'is_required' => true,
                'is_active' => true,
                'extraction_source' => 'DCP',
                'extraction_pattern' => 'Compression',
                'possible_values' => ['JPEG2000'],
            ],
            [
                'name' => 'Bit Depth',
                'code' => 'BIT_DEPTH',
                'type' => 'string',
                'category' => 'video',
                'description' => 'Profondeur de bits vidéo',
                'is_required' => true,
                'is_active' => true,
                'extraction_source' => 'DCP',
                'extraction_pattern' => 'BitDepth',
                'possible_values' => ['12-bit'],
            ],
            [
                'name' => '3D Type',
                'code' => '3D_TYPE',
                'type' => 'string',
                'category' => 'video',
                'description' => 'Type de projection (2D/3D)',
                'is_required' => false,
                'is_active' => true,
                'extraction_source' => 'manual',
                'possible_values' => ['2D', '3D'],
            ],
            [
                'name' => 'HDR Type',
                'code' => 'HDR_TYPE',
                'type' => 'string',
                'category' => 'video',
                'description' => 'Type HDR (rare en DCP)',
                'is_required' => false,
                'is_active' => true,
                'extraction_source' => 'manual',
                'possible_values' => ['SDR', 'Dolby Vision', 'HDR10'],
            ],

            // =========================================
            // CATÉGORIE AUDIO (nouveaux uniquement)
            // =========================================
            [
                'name' => 'Audio Channels',
                'code' => 'AUDIO_CHANNELS',
                'type' => 'string',
                'category' => 'audio',
                'description' => 'Configuration des canaux audio',
                'is_required' => true,
                'is_active' => true,
                'extraction_source' => 'DCP',
                'extraction_pattern' => 'MainSound|Channels',
                'possible_values' => ['5.1', '7.1', 'Dolby Atmos', 'Auro 11.1', 'Mono', 'Stereo'],
            ],
            [
                'name' => 'Sample Rate',
                'code' => 'SAMPLE_RATE',
                'type' => 'string',
                'category' => 'audio',
                'description' => 'Fréquence d\'échantillonnage audio',
                'is_required' => true,
                'is_active' => true,
                'extraction_source' => 'DCP',
                'extraction_pattern' => 'SampleRate',
                'possible_values' => ['48 kHz', '96 kHz'],
            ],
            [
                'name' => 'Audio Bit Depth',
                'code' => 'AUDIO_BIT_DEPTH',
                'type' => 'string',
                'category' => 'audio',
                'description' => 'Profondeur de bits audio',
                'is_required' => true,
                'is_active' => true,
                'extraction_source' => 'DCP',
                'extraction_pattern' => 'AudioBitDepth',
                'possible_values' => ['24-bit PCM'],
            ],

            // =========================================
            // CATÉGORIE ACCESSIBILITY (nouveaux uniquement)  
            // =========================================
            [
                'name' => 'Subtitle Language',
                'code' => 'SUBTITLE_LANGUAGE',
                'type' => 'string',
                'category' => 'accessibility',
                'description' => 'Langue des sous-titres',
                'is_required' => false,
                'is_active' => true,
                'extraction_source' => 'DCP',
                'extraction_pattern' => 'SubtitleLanguage',
                'possible_values' => ['FR', 'EN', 'ES', 'DE', 'IT', 'PT', 'NL', 'NONE'],
            ],
            [
                'name' => 'Hearing Impaired',
                'code' => 'HEARING_IMPAIRED',
                'type' => 'bool',
                'category' => 'accessibility',
                'description' => 'Support pour malentendants (HI)',
                'is_required' => false,
                'is_active' => true,
                'extraction_source' => 'manual',
            ],
            [
                'name' => 'Audio Description',
                'code' => 'AUDIO_DESCRIPTION',
                'type' => 'bool',
                'category' => 'accessibility',
                'description' => 'Audiodescription pour malvoyants (VI-N)',
                'is_required' => false,
                'is_active' => true,
                'extraction_source' => 'manual',
            ],

            // =========================================
            // CATÉGORIE FORMAT (nouveaux uniquement)
            // =========================================
            [
                'name' => 'DCP Type',
                'code' => 'DCP_TYPE',
                'type' => 'string',
                'category' => 'format',
                'description' => 'Type de format DCP',
                'is_required' => true,
                'is_active' => true,
                'extraction_source' => 'DCP',
                'extraction_pattern' => 'DCPType',
                'possible_values' => ['Interop DCP', 'SMPTE DCP'],
            ],
            [
                'name' => 'Content Type',
                'code' => 'CONTENT_TYPE',
                'type' => 'string',
                'category' => 'format',
                'description' => 'Type de contenu',
                'is_required' => true,
                'is_active' => true,
                'extraction_source' => 'manual',
                'possible_values' => ['FTR', 'SHR', 'TLR', 'ADV', 'POL'],
            ],
            [
                'name' => 'Encryption',
                'code' => 'ENCRYPTION',
                'type' => 'string',
                'category' => 'format',
                'description' => 'État de chiffrement du DCP',
                'is_required' => true,
                'is_active' => true,
                'extraction_source' => 'DCP',
                'extraction_pattern' => 'Encrypted',
                'possible_values' => ['Non chiffré', 'Chiffré (avec KDM)'],
            ],
            [
                'name' => 'CPL Count',
                'code' => 'CPL_COUNT',
                'type' => 'int',
                'category' => 'format',
                'description' => 'Nombre de Composition Playlists',
                'is_required' => false,
                'is_active' => true,
                'extraction_source' => 'DCP',
                'extraction_pattern' => 'CPLCount',
            ],

            // =========================================
            // CATÉGORIE TECHNICAL (nouveaux uniquement)
            // =========================================
            [
                'name' => 'File Size',
                'code' => 'FILE_SIZE',
                'type' => 'float',
                'category' => 'technical',
                'description' => 'Taille du fichier DCP en GB',
                'is_required' => false,
                'is_active' => true,
                'extraction_source' => 'auto',
            ],
            [
                'name' => 'Checksum',
                'code' => 'CHECKSUM',
                'type' => 'string',
                'category' => 'technical',
                'description' => 'Somme de contrôle pour vérification d\'intégrité',
                'is_required' => false,
                'is_active' => true,
                'extraction_source' => 'DCP',
                'extraction_pattern' => 'Checksum',
            ],
            [
                'name' => 'Mastering Date',
                'code' => 'MASTERING_DATE',
                'type' => 'date',
                'category' => 'technical',
                'description' => 'Date de création du DCP',
                'is_required' => false,
                'is_active' => true,
                'extraction_source' => 'DCP',
                'extraction_pattern' => 'CreationDate',
            ],

            // =========================================
            // CATÉGORIE METADATA (nouveaux uniquement)
            // =========================================
            [
                'name' => 'Country',
                'code' => 'COUNTRY',
                'type' => 'string',
                'category' => 'metadata',
                'description' => 'Pays de production',
                'is_required' => false,
                'is_active' => true,
                'extraction_source' => 'manual',
                'possible_values' => ['FR', 'US', 'GB', 'DE', 'IT', 'ES', 'CA', 'AU', 'JP', 'KR', 'CN'],
            ],
            [
                'name' => 'Director',
                'code' => 'DIRECTOR',
                'type' => 'string',
                'category' => 'metadata',
                'description' => 'Réalisateur du film',
                'is_required' => false,
                'is_active' => true,
                'extraction_source' => 'manual',
            ],
            [
                'name' => 'Genre',
                'code' => 'GENRE',
                'type' => 'string',
                'category' => 'metadata',
                'description' => 'Genre du film',
                'is_required' => false,
                'is_active' => true,
                'extraction_source' => 'manual',
                'possible_values' => ['DRAMA', 'COMEDY', 'ACTION', 'THRILLER', 'HORROR', 'SCI-FI', 'ROMANCE', 'DOCUMENTARY', 'ANIMATION', 'FANTASY'],
            ],
            [
                'name' => 'Territory',
                'code' => 'TERRITORY',
                'type' => 'string',
                'category' => 'metadata',
                'description' => 'Territoire de diffusion (ex: FR-FR, EN-US)',
                'is_required' => false,
                'is_active' => true,
                'extraction_source' => 'manual',
                'possible_values' => ['FR-FR', 'EN-US', 'DE-AT', 'ES-ES', 'IT-IT'],
            ],

            // =========================================
            // CATÉGORIE MANAGEMENT (nouveaux uniquement)
            // =========================================
            [
                'name' => 'UUID',
                'code' => 'UUID',
                'type' => 'string',
                'category' => 'management',
                'description' => 'Identifiant unique du DCP',
                'is_required' => false,
                'is_active' => true,
                'extraction_source' => 'DCP',
                'extraction_pattern' => 'UUID',
            ],
            [
                'name' => 'Creation Date',
                'code' => 'CREATION_DATE',
                'type' => 'date',
                'category' => 'management',
                'description' => 'Date de création du package',
                'is_required' => false,
                'is_active' => true,
                'extraction_source' => 'auto',
            ],
            [
                'name' => 'Distributor',
                'code' => 'DISTRIBUTOR',
                'type' => 'string',
                'category' => 'management',
                'description' => 'Distributeur du film',
                'is_required' => false,
                'is_active' => true,
                'extraction_source' => 'manual',
            ],
        ];

        $created = 0;
        $updated = 0;

        foreach ($parameters as $parameterData) {
            // Convertir les arrays en JSON pour les champs JSON
            if (isset($parameterData['possible_values']) && is_array($parameterData['possible_values'])) {
                $parameterData['possible_values'] = json_encode($parameterData['possible_values']);
            }

            $parameter = Parameter::updateOrCreate(
                ['code' => $parameterData['code']], // condition de recherche
                $parameterData // données à mettre à jour ou créer
            );

            if ($parameter->wasRecentlyCreated) {
                $created++;
                echo "  ✅ Paramètre créé: {$parameterData['name']} ({$parameterData['code']})\n";
            } else {
                $updated++;
                echo "  ℹ️  Paramètre mis à jour: {$parameterData['name']} ({$parameterData['code']})\n";
            }
        }

        echo "📊 Résultat: {$created} paramètres créés, {$updated} mis à jour\n";
        echo "Terminé ! Extension DCP complète.\n";
    }
}
