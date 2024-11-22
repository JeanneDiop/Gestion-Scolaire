<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Historique;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
class HistoriqueController extends Controller
{

    // Méthode pour récupérer les historiques d'ajouts et de modifications d'aujourd'hui
    public function getHistoriquesAujourdhui()
    {
        // Obtenir la date d'aujourd'hui
        $today = Carbon::today();
    
        // Construire la requête pour récupérer les historiques avec comptage des occurrences
        $historiquesQuery = Historique::whereDate('created_at', $today)
            ->whereIn('action', ['create', 'update']) // Limiter aux actions "create" et "update"
            ->select('message', DB::raw('count(*) as occurrences'))
            ->groupBy('message')
            ->orderBy('occurrences', 'desc'); // Trier par nombre d'occurrences, du plus élevé au plus bas
    
        // Récupérer les historiques (comptés et groupés par message)
        $historiques = $historiquesQuery->get();
        if ($historiques->isEmpty()) {
            // Aucun historique trouvé, retourner un message spécifique
            return response()->json(['message' => "Aucune action enregistrée aujourd'hui."], 200);
        }
        // Retourner les résultats en format JSON
        return response()->json($historiques);
    }
}
