<?php

    namespace App\Http\Controllers\Api;
    
    use App\Http\Controllers\Controller;
    use App\Http\Requests\Role\CreateRoleRequest;
    use Illuminate\Http\Request;
    use Exception;
    use App\Models\Role;
    use Illuminate\Support\Facades\Cache;
    
    class RoleController extends Controller
    {
        public function store(CreateRoleRequest $request)
        {
            try {
                // Création du rôle avec le nom validé
                $role = Role::create($request->validated());
        
                return response()->json([
                    "message" => "Le rôle a bien été créé",
                    "role" => $role
                ], 201);
            } catch (Exception $e) {
                // Retourner une réponse JSON en cas d'erreur
                return response()->json([
                    "status_code" => 500,
                    "status_message" => "Une erreur est survenue",
                    "error" => $e->getMessage()
                ], 500);
            }
        }

        public function index()
        {
            try {
                // Récupérer la liste des rôles
                $roles = Role::all();
        
                return response()->json([
                    "message" => "La liste des rôles est disponible",
                    "roles" => $roles
                ], 200); // Ajout du code de statut HTTP 200
            } catch (Exception $e) {
                // Retourner une réponse JSON en cas d'erreur
                return response()->json([
                    "status_code" => 500,
                    "status_message" => "Une erreur est survenue lors de la récupération des rôles",
                    "error" => $e->getMessage()
                ], 500);
            }
        }

public function show($id)
{
    try {
        // Trouver le rôle par son ID
        $role = Role::findOrFail($id);

        return response()->json([
            "message" => "Le rôle a été trouvé avec succès",
            "role" => $role
        ], 200);
    } catch (ModelNotFoundException $e) {
        // Retourner une réponse JSON si le rôle n'est pas trouvé
        return response()->json([
            "status_code" => 404,
            "status_message" => "Le rôle spécifié n'a pas été trouvé"
        ], 404);
    } catch (Exception $e) {
        // Retourner une réponse JSON en cas d'erreur générale
        return response()->json([
            "status_code" => 500,
            "status_message" => "Une erreur est survenue lors de la récupération du rôle"
        ], 500);
    }
}

public function update(CreateRoleRequest $request, $id)
{
    try {
        // Trouver le rôle par son ID
        $role = Role::findOrFail($id);

        // Mettre à jour les attributs du rôle
        $role->nom = $request->input('nom');
        // Mettez à jour d'autres attributs si nécessaire
        $role->save();

        return response()->json([
            "message" => "Le rôle a été mis à jour avec succès",
            "role" => $role
        ], 200);
    } catch (Exception $e) {
        return response()->json([
            "status_code" => 500,
            "status_message" => "Une erreur est survenue lors de la mise à jour du rôle",
            "error" => $e->getMessage()
        ], 500);
    }
}
public function destroy(Role $role)
{
    try {
        // Vérifier si le rôle existe
        if (!$role) {
            return response()->json([
                "status_code" => 404,
                "status_message" => "Le rôle spécifié n'a pas été trouvé"
            ], 404);
        }

        // Supprimer le rôle
        $role->delete();

        return response()->json([
            "message" => "Le rôle a été supprimé avec succès"
        ], 200);
    } catch (Exception $e) {
        return response()->json([
            "status_code" => 500,
            "status_message" => "Une erreur est survenue lors de la suppression du rôle",
            "error" => $e->getMessage()
        ], 500);
    }
}

        
}

    
