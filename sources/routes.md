# Routes de l'application

| URL | Méthode HTTP | Contrôleur       | Méthode | Titre HTML           | Commentaire    |
| --- | ------------ | ---------------- | ------- | -------------------- | -------------- |
| `/` | `GET`        | `MainController` | `home`  | Bienvenue sur O'flix | Page d'accueil |
| `/show/{id}` | `GET`| `MainController` | `show`  | O'flix - $titre | Page des détails d'un film ou d'un série |
| `/list` | `GET`    | `MainController` | `list`  | Bienvenue sur O'flix | Liste de films ou de séries |
| `/favorites` | `GET`| `MainController` | `favorites`  | O'flix - Mes favoris | Liste des favoris d'un utilisateur |
| `/api/movies` | `GET`| `ApiController` | `list`  | - | liste des movies |
| `/api/movies/{id}` | `GET`| `ApiController` | `movies_get`  | - | toutes les informations d'un movie |
| `/theme/toggle` | `GET`| `MainController` | `themeSwitcher`  | - | changement de theme |
