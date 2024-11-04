<?php

namespace App\Console\Commands;

use App\Models\Programme;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class StoreProgrammeByClass extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'store:programme {file} {niveau_education} {niveau_classe}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enregistre les programmes pour un niveau spécifique à partir d’un fichier Excel';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Récupération des arguments
        $filePath = $this->argument('file');
        $niveauEducation = $this->argument('niveau_education');
        $niveauClasse = $this->argument('niveau_classe');

        // Vérifie si le fichier existe
        if (!file_exists(storage_path("app/public/Programmes/{$filePath}"))) {
            $this->error("Le fichier {$filePath} n'existe pas dans le dossier Programmes.");
            return;
        }

        // Chargez le fichier Excel
        $spreadsheet = $this->loadSpreadsheet(storage_path("app/public/Programmes/{$filePath}"));
        $worksheet = $spreadsheet->getActiveSheet();

        // Initialisez le compteur
        $counter = 0;

        foreach ($worksheet->getRowIterator() as $row) {
            // Ignore la première ligne (en-têtes)
            if ($counter === 0) {
                $counter++;
                continue;
            }

            // Obtenez les cellules pour chaque ligne
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(true);

            // Récupérez les valeurs des cellules
            $cells = [];
            foreach ($cellIterator as $cell) {
                $cells[] = $cell->getFormattedValue();
            }

            // Si la ligne est vide, passez à la suivante
            if (implode('', $cells) === '') {
                continue;
            }

            // Remplir les éléments manquants avec `null` pour correspondre au nombre de colonnes
            while (count($cells) < 9) {
                $cells[] = null;
            }

            // Créez un enregistrement dans la base de données
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
                'bareme' => $cells[8] ?? null,
                'file_name' => $filePath,
            ]);
            $this->info("Ajout de l'enregistrement avec le file_name : {$filePath}");
        }

        $this->info("Programmes pour {$niveauClasse} - {$niveauEducation} enregistrés avec succès à partir du fichier: {$filePath}.");
    }

    /**
     * Charge un fichier Excel.
     */
    private function loadSpreadsheet(string $path): Spreadsheet
    {
        return IOFactory::load($path);
    }
}
