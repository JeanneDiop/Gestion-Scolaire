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
    // Vérifier si l'utilisateur est authentifié
    if (auth()->check()) {
        // Utilisateur standard
        $userId = auth()->id();
    } else {
        // Non authentifié
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    // Obtenir la date d'aujourd'hui
    $today = Carbon::today();

    // Construire la requête pour récupérer les historiques avec comptage des occurrences
    $historiquesQuery = Historique::whereDate('created_at', $today)
        ->whereIn('action', ['create', 'update']) // Limiter aux actions "create" et "update"
        ->select('message', DB::raw('count(*) as occurrences'))
        ->groupBy('message')
        ->orderBy('occurrences', 'desc'); // Trier par nombre d'occurrences, du plus élevé au plus bas

    // Filtrer selon l'utilisateur connecté
    $historiquesQuery->where('user_id', $userId);

    // Récupérer les historiques (comptés et groupés par message)
    $historiques = $historiquesQuery->get();

    // Retourner les résultats en format JSON
    return response()->json($historiques);
}
}
