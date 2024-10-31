<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class InsertDefaultProgrammes extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        $defaultProgrammes = [
            // Maternelle - Petite section
            [
                'niveau_education' => 'Maternelle', 'classe' => 'Petite', 'activites' => [
                    ['nom_activite' => 'Jeu de construction', 'categorie' => 'Motricité et coordination', 'competences' => 'Coordination œil-main, compréhension des relations spatiales, développement de la motricité fine', 'volume_horaire' => '1h', 'duree_recommandee' => '15-20 min', 'section' => 'Petite, Moyenne'],
                    ['nom_activite' => 'Lecture d\'histoires', 'categorie' => 'Langage et communication', 'competences' => 'Enrichissement du vocabulaire, amélioration de la compréhension orale, stimulation de l\'imagination', 'volume_horaire' => '1h30', 'duree_recommandee' => '20-25 min', 'section' => 'Petite, Moyenne, Grande'],
                    ['nom_activite' => 'Chants et comptines', 'categorie' => 'Expression orale et auditive', 'competences' => 'Mémoire auditive, articulation et diction, rythme et musicalité', 'volume_horaire' => '1h', 'duree_recommandee' => '10-15 min', 'section' => 'Petite, Moyenne'],
                    ['nom_activite' => 'Dessin libre', 'categorie' => 'Créativité et expression artistique', 'competences' => 'Expression créative, exploration des couleurs et des formes, renforcement de la motricité fine', 'volume_horaire' => '1h', 'duree_recommandee' => '15-20 min', 'section' => 'Petite, Moyenne, Grande'],
                    ['nom_activite' => 'Parcours de motricité', 'categorie' => 'Motricité globale', 'competences' => 'Équilibre, coordination, développement de la force et de l\'endurance physique', 'volume_horaire' => '1h', 'duree_recommandee' => '20-30 min', 'section' => 'Petite, Moyenne'],
                    ['nom_activite' => 'Atelier peinture avec les mains', 'categorie' => 'Créativité et sensoriel', 'competences' => 'Exploration des textures et des couleurs, expression artistique, coordination', 'volume_horaire' => '1h', 'duree_recommandee' => '15-20 min', 'section' => 'Petite, Moyenne'],
                    ['nom_activite' => 'Écoute musicale', 'categorie' => 'Musique et expression rythmique', 'competences' => 'Sensibilisation à la musique, écoute active, compréhension des rythmes', 'volume_horaire' => '30 min', 'duree_recommandee' => '10-15 min', 'section' => 'Petite, Moyenne, Grande'],
                    ['nom_activite' => 'Jeux d\'eau et de sable', 'categorie' => 'Sensoriel et exploration', 'competences' => 'Stimulation des sens, manipulation, expérimentation, découverte des propriétés physiques des matériaux', 'volume_horaire' => '45 min', 'duree_recommandee' => '20-30 min', 'section' => 'Petite, Moyenne'],
                    ['nom_activite' => 'Atelier de coloriage', 'categorie' => 'Motricité fine et concentration', 'competences' => 'Précision des gestes, patience, concentration', 'volume_horaire' => '1h', 'duree_recommandee' => '15-20 min', 'section' => 'Petite, Moyenne'],
                ],
            ],
            // Maternelle - Moyenne section
            [
                'niveau_education' => 'Maternelle', 'classe' => 'Moyenne', 'activites' => [
                    ['nom_activite' => 'Jeu de construction', 'categorie' => 'Motricité et coordination', 'competences' => 'Coordination œil-main, compréhension des relations spatiales, développement de la motricité fine', 'volume_horaire' => '1h', 'duree_recommandee' => '15-20 min', 'section' => 'Moyenne'],
                    ['nom_activite' => 'Puzzles et jeux d\'association', 'categorie' => 'Logique et résolution de problèmes', 'competences' => 'Capacités d\'observation, raisonnement logique, compréhension des formes et des couleurs', 'volume_horaire' => '1h', 'duree_recommandee' => '15-20 min', 'section' => 'Moyenne, Grande'],
                    ['nom_activite' => 'Chants et comptines', 'categorie' => 'Expression orale et auditive', 'competences' => 'Mémoire auditive, articulation et diction, rythme et musicalité', 'volume_horaire' => '1h', 'duree_recommandee' => '10-15 min', 'section' => 'Petite, Moyenne'],
                    ['nom_activite' => 'Dessin libre', 'categorie' => 'Créativité et expression artistique', 'competences' => 'Expression créative, exploration des couleurs et des formes, renforcement de la motricité fine', 'volume_horaire' => '1h', 'duree_recommandee' => '15-20 min', 'section' => 'Petite, Moyenne, Grande'],
                    ['nom_activite' => 'Parcours de motricité', 'categorie' => 'Motricité globale', 'competences' => 'Équilibre, coordination, développement de la force et de l\'endurance physique', 'volume_horaire' => '1h', 'duree_recommandee' => '20-30 min', 'section' => 'Petite, Moyenne'],
                    ['nom_activite' => 'Activités de tri', 'categorie' => 'Mathématiques et logique', 'competences' => 'Reconnaissance des formes, tri par couleur, développement de la pensée logique', 'volume_horaire' => '45 min', 'duree_recommandee' => '10-15 min', 'section' => 'Moyenne, Grande'],
                    ['nom_activite' => 'Jeux de rôles', 'categorie' => 'Socialisation et expression émotionnelle', 'competences' => 'Compétences sociales, compréhension des émotions, développement de l\'imagination et de la capacité à se projeter dans différents rôles', 'volume_horaire' => '1h', 'duree_recommandee' => '15-20 min', 'section' => 'Moyenne, Grande'],
                    ['nom_activite' => 'Jeu de mémoire', 'categorie' => 'Mémoire et concentration', 'competences' => 'Renforcement de la mémoire visuelle, attention et concentration, capacités d\'association', 'volume_horaire' => '45 min', 'duree_recommandee' => '10-15 min', 'section' => 'Moyenne, Grande'],
                    ['nom_activite' => 'Activité de cuisine simple', 'categorie' => 'Motricité fine et autonomie', 'competences' => 'Autonomie, séquences d\'étapes, respect des consignes, renforcement de la motricité fine', 'volume_horaire' => '30 min', 'duree_recommandee' => '20-30 min', 'section' => 'Grande'],
                    ['nom_activite' => 'Écoute musicale', 'categorie' => 'Musique et expression rythmique', 'competences' => 'Sensibilisation à la musique, écoute active, compréhension des rythmes', 'volume_horaire' => '30 min', 'duree_recommandee' => '10-15 min', 'section' => 'Petite, Moyenne, Grande'],
                    ['nom_activite' => 'Jeux d\'eau et de sable', 'categorie' => 'Sensoriel et exploration', 'competences' => 'Stimulation des sens, manipulation, expérimentation, découverte des propriétés physiques des matériaux', 'volume_horaire' => '45 min', 'duree_recommandee' => '20-30 min', 'section' => 'Petite, Moyenne'],
                    ['nom_activite' => 'Art avec des éléments naturels', 'categorie' => 'Découverte de la nature et arts plastiques', 'competences' => 'Créativité, sensibilisation à l’environnement, exploration des textures et formes naturelles', 'volume_horaire' => '1h', 'duree_recommandee' => '20-25 min', 'section' => 'Grande'],
                    ['nom_activite' => 'Jeux de comptage', 'categorie' => 'Pré-mathématiques', 'competences' => 'Introduction au concept de nombres, comptage, reconnaissance des chiffres', 'volume_horaire' => '45 min', 'duree_recommandee' => '10-15 min', 'section' => 'Moyenne, Grande'],
                    ['nom_activite' => 'Atelier de coloriage', 'categorie' => 'Motricité fine et concentration', 'competences' => 'Précision des gestes, patience, concentration', 'volume_horaire' => '1h', 'duree_recommandee' => '15-20 min', 'section' => 'Petite, Moyenne'],
                    ['nom_activite' => 'Petites expériences scientifiques', 'categorie' => 'Sciences et curiosité naturelle', 'competences' => 'Introduction aux concepts de cause à effet, observation, compréhension des phénomènes naturels', 'volume_horaire' => '30 min', 'duree_recommandee' => '20-30 min', 'section' => 'Grande'],
                ],
            ],
            // Maternelle - Grande section
            [
                'niveau_education' => 'Maternelle',
                'classe' => 'Grande',
                'activites' => [
                    ['nom_activite' => 'Dessin libre', 'categorie' => 'Créativité et expression artistique', 'competences' => 'Expression créative, exploration des couleurs et des formes, renforcement de la motricité fine', 'volume_horaire' => '1h', 'duree_recommandee' => '15-20 min', 'section' => 'Petite, Moyenne, Grande'],
                    ['nom_activite' => 'Plantation de graines', 'categorie' => 'Sciences et découverte de l\'environnement', 'competences' => 'Découverte de la nature et du vivant, sensibilisation au respect de l\'environnement, développement de la patience et de l\'observation', 'volume_horaire' => '30 min', 'duree_recommandee' => '20-30 min', 'section' => 'Grande'],
                    // Ajoutez d'autres activités ici...
                ],
            ],
        ];

        foreach ($defaultProgrammes as $programme) {
            foreach ($programme['activites'] as $activite) {
                Programme::create(array_merge($activite, [
                    'niveau_education' => $programme['niveau_education'],
                    'classe' => $programme['classe'],
                ]));
            }
        }
    }


    /**
     * Reverse the migrations.
     */
    public function down()
    {
        // Supprimez les programmes créés pour chaque classe et niveau
        Programme::where('niveau_education', 'Maternelle')->delete();
    }
};
