<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Exception;
use App\Models\Vente;
use App\Http\Requests\Vente\CreateVenteRequest;
use App\Http\Requests\Vente\UpdateVenteRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;

class VenteController extends Controller
{


     // Exemple de méthode pour obtenir le taux de change (statique)
     public function store(CreateVenteRequest $request)
     {
         try {
             // Récupérer le prix_vente (par exemple, '8000 FCFA', '8000 EUR', etc.)
             $prix_vente = strval($request->prix_vente);  // Assurer que c'est bien une chaîne de caractères

             // Log des données reçues pour vérifier
             Log::info('prix_vente reçu : ' . $prix_vente);

             // Extraire le montant et la devise (ex: '8000 FCFA', '8000 EUR', etc.)
             preg_match('/([\d.,]+)\s*(FCFA|EUR|USD|EURO|DOLLAR|fcfa|euro|dollar|eur|usd)/i', $prix_vente, $matches);

             // Vérifier si la chaîne a bien été capturée correctement
             if (count($matches) < 3) {
                 // Gérer l'erreur si la chaîne ne correspond pas au format attendu
                 return response()->json([
                     'status_code' => 400,
                     'status_message' => 'Format du prix invalide',
                 ], 400);
             }

             // Convertir le montant en float (en remplaçant les virgules par des points)
             $montant = floatval(str_replace(',', '.', $matches[1]));
             $devise = strtolower($matches[2]);  // Convertir la devise en minuscules

             // Vérifier que la devise est valide
             $valid_devises = ['fcfa', 'eur', 'usd'];  // Devises en minuscule
             if (!in_array($devise, $valid_devises)) {
                 return response()->json([
                     'status_code' => 400,
                     'status_message' => 'Devise invalide',
                 ], 400);
             }

             // Création de la vente
             $vente = new Vente();
             $vente->numero_vente = $request->numero_vente ?? null;
             $vente->nom_vente = $request->nom_vente ?? null;
             $vente->description = $request->description ?? null;
             $vente->prix_vente = $montant;  // Stocker la valeur numérique du prix
             $vente->unite = $devise;  // Stocker la devise (en minuscules)
             $vente->quantite = $request->quantite ?? null;
             $vente->quantite_disponible_stock = $request->quantite_disponible_stock ?? null;
             $vente->type_vente = $request->type_vente ?? null;
             $vente->compte_comptable_id = $request->compte_comptable_id ?? null;
             $vente->user_id = $request->user_id ?? null;
             $vente->save();

             // Retourner la réponse JSON avec les données de la vente
             return response()->json([
                 'status_code' => 200,
                 'status_message' => 'Vente ajoutée avec succès',
                 'data' => $vente,
             ], 200);

         } catch (Exception $e) {
             // Log l'erreur pour la déboguer
             Log::error('Erreur lors de l\'enregistrement de la vente :', ['error' => $e->getMessage()]);

             // Retourner une réponse JSON avec l'erreur
             return response()->json([
                 'status_code' => 500,
                 'status_message' => 'Erreur lors de l\'enregistrement de la vente',
                 'error' => $e->getMessage(),
             ], 500);
         }
     }














     public function update(CreateVenteRequest $request, $id)
     {
         try {
             // Récupérer la vente existante par son ID
             $vente = Vente::findOrFail($id); // Si la vente n'existe pas, une erreur 404 sera lancée

             // Récupérer le prix_vente (par exemple, '8000 FCFA', '8000 EUR', etc.)
             $prix_vente = strval($request->prix_vente);  // Assurer que c'est bien une chaîne de caractères

             // Log des données reçues pour vérifier
             Log::info('prix_vente reçu : ' . $prix_vente);

             // Extraire le montant et la devise (ex: '8000 FCFA', '8000 EUR', etc.)
             preg_match('/([\d.,]+)\s*(FCFA|EUR|USD|EURO|DOLLAR|fcfa|euro|dollar|eur|usd)/i', $prix_vente, $matches);

             // Vérifier si la chaîne a bien été capturée correctement
             if (count($matches) < 3) {
                 // Gérer l'erreur si la chaîne ne correspond pas au format attendu
                 return response()->json([
                     'status_code' => 400,
                     'status_message' => 'Format du prix invalide',
                 ], 400);
             }

             // Convertir le montant en float (en remplaçant les virgules par des points)
             $montant = floatval(str_replace(',', '.', $matches[1]));
             $devise = strtolower($matches[2]);  // Convertir la devise en minuscules

             // Vérifier que la devise est valide
             $valid_devises = ['fcfa', 'eur', 'usd'];  // Devises en minuscule
             if (!in_array($devise, $valid_devises)) {
                 return response()->json([
                     'status_code' => 400,
                     'status_message' => 'Devise invalide',
                 ], 400);
             }

             // Mettre à jour les champs de la vente avec les nouvelles données
             $vente->numero_vente = $request->numero_vente ?? $vente->numero_vente;
             $vente->nom_vente = $request->nom_vente ?? $vente->nom_vente;
             $vente->description = $request->description ?? $vente->description;
             $vente->prix_vente = $montant;  // Mettre à jour le prix
             $vente->unite = $devise;  // Mettre à jour la devise
             $vente->quantite = $request->quantite ?? $vente->quantite;
             $vente->quantite_disponible_stock = $request->quantite_disponible_stock ?? $vente->quantite_disponible_stock;
             $vente->type_vente = $request->type_vente ?? $vente->type_vente;
             $vente->compte_comptable_id = $request->compte_comptable_id ?? $vente->compte_comptable_id;
             $vente->user_id = $request->user_id ?? $vente->user_id;

             // Sauvegarder les modifications
             $vente->save();

             // Retourner la réponse JSON avec les données de la vente mise à jour
             return response()->json([
                 'status_code' => 200,
                 'status_message' => 'Vente mise à jour avec succès',
                 'data' => $vente,
             ], 200);

         } catch (Exception $e) {
             // Log l'erreur pour la déboguer
             Log::error('Erreur lors de la mise à jour de la vente :', ['error' => $e->getMessage()]);

             // Retourner une réponse JSON avec l'erreur
             return response()->json([
                 'status_code' => 500,
                 'status_message' => 'Erreur lors de la mise à jour de la vente',
                 'error' => $e->getMessage(),
             ], 500);
         }
     }




     public function show($id)
{
    try {
        // Récupérer la vente existante par son ID, avec les informations de l'utilisateur associées
        $vente = Vente::with('user')->findOrFail($id); // Assure-toi que 'user' est bien la relation définie dans le modèle Vente

        // Retourner la réponse JSON avec les données de la vente et les informations de l'utilisateur
        return response()->json([
            'status_code' => 200,
            'status_message' => 'Vente récupérée avec succès',
            'data' => $vente,
        ], 200);

    } catch (ModelNotFoundException $e) {
        // Si la vente n'est pas trouvée, retourner une erreur 404
        return response()->json([
            'status_code' => 404,
            'status_message' => 'Vente non trouvée',
        ], 404);

    } catch (Exception $e) {
        // Log l'erreur pour la déboguer
        Log::error('Erreur lors de la récupération de la vente :', ['error' => $e->getMessage()]);

        // Retourner une réponse JSON avec l'erreur
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Erreur lors de la récupération de la vente',
            'error' => $e->getMessage(),
        ], 500);
    }
}

public function index()
{
    try {
        // Récupérer toutes les ventes avec les informations de l'utilisateur associé
        $ventes = Vente::with('user')->get(); // Assure-toi que 'user' est bien une relation définie dans le modèle Vente

        // Retourner la réponse JSON avec les données des ventes
        return response()->json([
            'status_code' => 200,
            'status_message' => 'Liste des ventes récupérée avec succès',
            'data' => $ventes,
        ], 200);

    } catch (Exception $e) {
        // Log l'erreur pour la déboguer
        Log::error('Erreur lors de la récupération des ventes :', ['error' => $e->getMessage()]);

        // Retourner une réponse JSON avec l'erreur
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Erreur lors de la récupération des ventes',
            'error' => $e->getMessage(),
        ], 500);
    }
}
public function showservice($id)
{
    try {
        // Récupérer la vente par son ID uniquement si elle a type_vente = "service"
        $vente = Vente::with('user')
            ->where('type_vente', 'service') // Filtrer par type "service"
            ->findOrFail($id); // Trouver par ID

        // Retourner la réponse JSON avec les données de la vente
        return response()->json([
            'status_code' => 200,
            'status_message' => 'Vente de type "service" récupérée avec succès',
            'data' => $vente,
        ], 200);

    } catch (ModelNotFoundException $e) {
        // Si la vente n'est pas trouvée, retourner une erreur 404
        return response()->json([
            'status_code' => 404,
            'status_message' => 'Vente de type "service" non trouvée',
        ], 404);

    } catch (Exception $e) {
        // Log l'erreur pour la déboguer
        Log::error('Erreur lors de la récupération de la vente de type "service" :', ['error' => $e->getMessage()]);

        // Retourner une réponse JSON avec l'erreur
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Erreur lors de la récupération de la vente de type "service"',
            'error' => $e->getMessage(),
        ], 500);
    }
}

public function indexservice()
{
    try {
        // Récupérer toutes les ventes de type "service" avec les informations de l'utilisateur associé
        $ventes = Vente::with('user')
            ->where('type_vente', 'service') // Filtrer les ventes par type
            ->get();

        // Retourner la réponse JSON avec les données des ventes filtrées
        return response()->json([
            'status_code' => 200,
            'status_message' => 'Liste des ventes de type "service" récupérée avec succès',
            'data' => $ventes,
        ], 200);

    } catch (Exception $e) {
        // Log l'erreur pour la déboguer
        Log::error('Erreur lors de la récupération des ventes :', ['error' => $e->getMessage()]);

        // Retourner une réponse JSON avec l'erreur
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Erreur lors de la récupération des ventes',
            'error' => $e->getMessage(),
        ], 500);
    }
}

public function showproduit($id)
{
    try {
        // Récupérer la vente par son ID uniquement si elle a type_vente = "produit"
        $vente = Vente::with('user')
            ->where('type_vente', 'produit') // Filtrer par type "produit"
            ->findOrFail($id); // Trouver par ID

        // Retourner la réponse JSON avec les données de la vente
        return response()->json([
            'status_code' => 200,
            'status_message' => 'Vente de type "produit" récupérée avec succès',
            'data' => $vente,
        ], 200);

    } catch (ModelNotFoundException $e) {
        // Si la vente n'est pas trouvée, retourner une erreur 404
        return response()->json([
            'status_code' => 404,
            'status_message' => 'Vente de type "produit" non trouvée',
        ], 404);

    } catch (Exception $e) {
        // Log l'erreur pour la déboguer
        Log::error('Erreur lors de la récupération de la vente de type "produit" :', ['error' => $e->getMessage()]);

        // Retourner une réponse JSON avec l'erreur
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Erreur lors de la récupération de la vente de type "produit"',
            'error' => $e->getMessage(),
        ], 500);
    }
}

public function indexproduit()
{
    try {
        // Récupérer toutes les ventes de type "produit" avec les informations de l'utilisateur associé
        $ventes = Vente::with('user')
            ->where('type_vente', 'produit') // Filtrer les ventes par type "produit"
            ->get();

        // Retourner la réponse JSON avec les données des ventes filtrées
        return response()->json([
            'status_code' => 200,
            'status_message' => 'Liste des ventes de type "produit" récupérée avec succès',
            'data' => $ventes,
        ], 200);

    } catch (Exception $e) {
        // Log l'erreur pour la déboguer
        Log::error('Erreur lors de la récupération des ventes :', ['error' => $e->getMessage()]);

        // Retourner une réponse JSON avec l'erreur
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Erreur lors de la récupération des ventes',
            'error' => $e->getMessage(),
        ], 500);
    }
}

public function destroy($id)
{
    try {
        // Trouver la vente par son ID
        $vente = Vente::findOrFail($id);

        // Supprimer la vente
        $vente->delete();

        // Retourner une réponse JSON indiquant le succès
        return response()->json([
            'status_code' => 200,
            'status_message' => 'Vente supprimée avec succès',
        ], 200);

    } catch (Exception $e) {
        // En cas d'erreur, retourner une réponse JSON avec un message d'erreur
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de la suppression de la vente',
            'error' => $e->getMessage(),
        ], 500);
    }
}

}
