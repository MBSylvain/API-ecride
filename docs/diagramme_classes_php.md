# Diagramme UML des classes PHP (EcoRide)

Ce diagramme Mermaid représente les principales classes PHP du dossier `models/` et leurs attributs.

```mermaid
classDiagram
    class Utilisateur {
        +int utilisateur_id
        +string nom
        +string prenom
        +string email
        +string password
        +string telephone
        +string adresse
        +date date_naissance
        +string photo
        +string pseudo
        +string role
        +date date_inscription
        +string compte_actif
        +date date_modification
    }
    class Reservation {
        +int reservation_id
        +int utilisateur_id
        +int trajet_id
        +int nombre_places_reservees
        +string statut
        +date date_reservation
        +date date_confirmation
        +string point_rdv
        +string commentaire
        +string bagages
        +date date_creation
    }
    class Trajet {
        +int trajet_id
        +string ville_depart
        +string ville_arrivee
        +string adresse_depart
        +string adresse_arrivee
        +date date_depart
        +string heure_depart
        +string heure_arrivee
        +int nombre_places
        +float prix
        +string description
        +bool bagages_autorises
        +bool fumeur_autorise
        +bool animaux_autorises
        +string statut
        +int utilisateur_id
        +int voiture_id
        +date date_creation
    }
    class Voiture {
        +int voiture_id
        +string marque
        +string modele
        +string immatriculation
        +string energie
        +string couleur
        +date date_premiere_immatriculation
        +int nombre_places
        +string photo_url
        +string description
        +int utilisateur_id
    }
    class Avis {
        +int avis_id
        +int utilisateur_id
        +string commentaire
        +int note
        +string statut
        +int auteur_id
        +int destinataire_id
        +int trajet_id
        +date date_creation
    }
    class Credit {
        +int credit_id
        +int utilisateur_id
        +int montant
        +string type_operation
        +date date_operation
        +string commentaire
    }
    class Notification {
        +int notification_id
        +int utilisateur_id
        +string type
        +string contenu
        +date date_envoi
        +string statut
    }
    class PreferenceConducteur {
        +int preference_id
        +int utilisateur_id
        +string type
        +string valeur
    }
    Utilisateur "1" -- "*" Reservation : reserve
    Utilisateur "1" -- "*" Trajet : propose
    Utilisateur "1" -- "*" Voiture : possede
    Utilisateur "1" -- "*" Avis : ecrit
    Utilisateur "1" -- "*" Credit : credit
    Utilisateur "1" -- "*" Notification : recoit
    Utilisateur "1" -- "*" PreferenceConducteur : a
    Trajet "1" -- "*" Reservation : concerne

    %% --- Classes administrateur ---
    class AdminAvis {
        +int avis_id
        +string statut
    }
    class AdminSignalement {
        +int id
        +int trajet_id
        +int utilisateur_id
        +date date_signalement
        +string motif
        +string description
        +string statut
        +int employe_id
        +date date_traitement
        +string action_effectuee
    }
    class AdminStatistique {
        <<service>>
        +getStatsVoitures()
        +getStatsReservations()
        +getStatsGlobales()
        +getNbUtilisateurs()
        +getNbEmployes()
        +getNbComptesParJour()
        +getStatsTrajets()
        +getStatsAvis()
    }
    class AdminTrajet {
        +int trajet_id
    }
    class AdminUtilisateur {
        +int utilisateur_id
        +string nom
        +string prenom
        +string email
        +string password
        +string role
        +bool suspendu
    }

    %% Relations admin (optionnelles, illustratives)
    AdminUtilisateur "1" -- "*" AdminSignalement : signale
    AdminUtilisateur "1" -- "*" AdminAvis : ecrit
    AdminUtilisateur "1" -- "*" AdminTrajet : gere
    AdminSignalement "*" -- "1" AdminTrajet : concerne
    AdminSignalement "*" -- "1" AdminUtilisateur : concerne
    AdminAvis "*" -- "1" AdminUtilisateur : concerne
    AdminStatistique <|.. AdminUtilisateur : analyse
    AdminStatistique <|.. AdminTrajet : analyse
    AdminStatistique <|.. AdminSignalement : analyse
    AdminStatistique <|.. AdminAvis : analyse

    Trajet "1" -- "*" Avis : concerne
    Trajet "1" -- "*" Voiture : utilise
```

> Ce diagramme UML permet de visualiser la structure objet du projet, les attributs principaux et les relations entre classes métier.