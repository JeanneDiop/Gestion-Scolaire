<?php
namespace App\Console\Commands;

use App\Models\Programme;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class StoreProgrammeByClass extends Command
{
    protected $signature = 'store:programme {file} {niveau_education} {niveau_classe}';
    protected $description = 'Enregistre les programmes pour un niveau spécifique à partir d\'un fichier Excel dans resources/programmes';

    public function handle()
    {
        // Récupération des arguments
        $fileName = $this->argument('file');
        $niveauEducation = $this->argument('niveau_education');
        $niveauClasse = $this->argument('niveau_classe');

        // Chemin vers le fichier Excel
        $filePath = resource_path("programmes/{$fileName}");

        // Vérifiez si le fichier existe
        if (!file_exists($filePath)) {
            $this->error("Le fichier {$filePath} n'existe pas dans le dossier Programmes.");
            return;
        }

        // Charger et traiter le fichier Excel
        $this->importFile($filePath, $niveauEducation, $niveauClasse);

        $this->info("Le programme pour {$niveauClasse} - {$niveauEducation} a été enregistré avec succès à partir du fichier: {$fileName}.");
    }

    private function importFile(string $filePath, string $niveauEducation, string $niveauClasse)
    {
        $spreadsheet = $this->loadSpreadsheet($filePath);
        $worksheet = $spreadsheet->getActiveSheet();

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


            // Compléter avec des valeurs nulles si moins de 9 colonnes
            while (count($cells) < 11) {
                $cells[] = null;
            }


            // Créer un enregistrement dans la base de données
            Programme::create([
                'niveau_education' => $niveauEducation,
                'niveau_classe' => $niveauClasse,
                'matiere' => $cells[0] ?? null,
                'categorie' => $cells[1] ?? null,
                'competences_essentielles' => $cells[2] ?? null,
                'leçons' => $cells[3] ?? null,
                'type_exercices' => $cells[4] ?? null,
                'volume_horaire' => $cells[5] ?? null,
                'duree_seance' => $cells[6] ?? null,
                'mode_evaluation' => $cells[7] ?? null,
                'heure_debut' => $cells[8] ?? null,
                'heure_fin' => $cells[9] ?? null,
                'bareme' => $cells[10] ?? null,
                'source' => 'import_excel',
                'file_name' => basename($filePath),
            ]);
            $this->info("Ajout de l'enregistrement avec le fichier : " . basename($filePath));
        }

        $this->info("Programmes pour {$niveauClasse} - {$niveauEducation} enregistrés avec succès à partir du fichier: " . basename($filePath));
    }

    private function loadSpreadsheet(string $path): Spreadsheet
    {
        return IOFactory::load($path);
    }
}

