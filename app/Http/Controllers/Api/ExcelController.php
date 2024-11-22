<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Programme;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ExcelController extends Controller
{
    public function modifierFichierExcel($fichierId)
    {
        // Récupérer le fichier depuis la base de données
        $fichier = Programme::findOrFail($fichierId);
        $chemin = storage_path('app/' . $fichier->chemin);

        // Vérifiez si le fichier existe
        if (!file_exists($chemin)) {
            return response()->json(['error' => 'Fichier introuvable.'], 404);
        }

        // Charger le fichier Excel existant
        $spreadsheet = IOFactory::load($chemin);
        $worksheet = $spreadsheet->getActiveSheet();

        // Exemple de modification : Ajouter du texte dans la cellule A1
        $worksheet->setCellValue('A1', 'Texte Modifié');

        // Sauvegarder le fichier modifié avec un nouveau nom
        $nouveauChemin = storage_path('app/public/ressource/programmes/modifie_' . $fichier->nom);
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($nouveauChemin);
        $nouveauFichier = new Programme();
        $nouveauFichier->chemin = 'public/ressource/programmes/modifie_' . $fichier->nom;
        $nouveauFichier->nom = 'modifie_' . $fichier->nom;
        $nouveauFichier->type = $fichier->type;
        $nouveauFichier->save();

        return response()->json(['message' => 'Fichier Excel modifié et sauvegardé avec succès', 'fichier' => $nouveauFichier], 200);
    }

    // Méthode pour récupérer tous les fichiers
    public function listerFichiers()
    {
        // Récupérer tous les fichiers associés aux programmes
        $fichiers = Programme::all();

        return response()->json(['fichiers' => $fichiers], 200);
    }
}
