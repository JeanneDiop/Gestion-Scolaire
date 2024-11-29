<?php

namespace App\Console\Commands;

use App\Models\Programme;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class CopyAndModifyProgramme extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'copy:programme {file} {niveau_education_source} {niveau_classe_source} {niveau_education_destination} {niveau_classe_destination}';
    protected $description = 'Copie les programmes d\'un niveau/classe source et les modifie à partir d\'un fichier Excel';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Récupérer les arguments
        $fileName = $this->argument('file');
        $niveauEducationSource = $this->argument('niveau_education_source');
        $niveauClasseSource = $this->argument('niveau_classe_source');
        $niveauEducationDestination = $this->argument('niveau_education_destination');
        $niveauClasseDestination = $this->argument('niveau_classe_destination');

        // Vérifiez l'existence du fichier Excel
        $filePath = resource_path("programmes/{$fileName}");
        if (!file_exists($filePath)) {
            $this->error("Le fichier {$filePath} n'existe pas.");
            return;
        }

        // Charger les programmes avec `source=import_excel` depuis la base de données
        $programmesSource = Programme::where('niveau_education', $niveauEducationSource)
            ->where('niveau_classe', $niveauClasseSource)
            ->where('source', 'import_excel')
            ->get();

        if ($programmesSource->isEmpty()) {
            $this->error("Aucun programme trouvé pour {$niveauClasseSource} - {$niveauEducationSource} avec la source import_excel.");
            return;
        }

        // Charger le fichier Excel
        $spreadsheet = $this->loadSpreadsheet($filePath);
        $worksheet = $spreadsheet->getActiveSheet();

        // Copier et modifier les programmes
        $this->copyAndModifyProgrammes($programmesSource, $worksheet, $niveauEducationDestination, $niveauClasseDestination);

        $this->info("Les programmes ont été copiés et modifiés avec succès pour {$niveauClasseDestination} - {$niveauEducationDestination}.");
    }

    private function copyAndModifyProgrammes($programmesSource, $worksheet, $niveauEducationDestination, $niveauClasseDestination)
    {
        $counter = 0;

        foreach ($worksheet->getRowIterator() as $row) {
            // Ignore la première ligne (en-têtes)
            if ($counter === 0) {
                $counter++;
                continue;
            }

            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(true);

            $cells = [];
            foreach ($cellIterator as $cell) {
                $cells[] = $cell->getFormattedValue();
            }

            // Ignore les lignes vides
            if (implode('', $cells) === '') {
                continue;
            }

            // Compléter avec des valeurs nulles si moins de 11 colonnes
            while (count($cells) < 11) {
                $cells[] = null;
            }

            // Copier et modifier chaque programme source
            foreach ($programmesSource as $programme) {
                Programme::create([
                    'niveau_education' => $niveauEducationDestination,
                    'niveau_classe' => $niveauClasseDestination,
                    'matiere' => $cells[0] ?? $programme->matiere,
                    'categorie' => $cells[1] ?? $programme->categorie,
                    'competences_essentielles' => $cells[2] ?? $programme->competences_essentielles,
                    'leçons' => $cells[3] ?? $programme->leçons,
                    'type_exercices' => $cells[4] ?? $programme->type_exercices,
                    'volume_horaire' => $cells[5] ?? $programme->volume_horaire,
                    'duree_seance' => $cells[6] ?? $programme->duree_seance,
                    'mode_evaluation' => $cells[7] ?? $programme->mode_evaluation,
                    'heure_debut' => $cells[8] ?? $programme->heure_debut,
                    'heure_fin' => $cells[9] ?? $programme->heure_fin,
                    'bareme' => $cells[10] ?? $programme->bareme,
                    'source' => 'import_excel',
                    'file_name' => basename($filePath),
                ]);

                $this->info("Programme copié pour la matière : {$programme->matiere}.");
            }
        }
    }

    private function loadSpreadsheet(string $path): Spreadsheet
    {
        return IOFactory::load($path);
    }
    }

