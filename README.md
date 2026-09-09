# O'flix

Application Symfony 5.4 de catalogue de films et series avec front-office, back-office, favoris, critiques utilisateur et API JSON securisee par JWT.

## Prerequis

- PHP 8.0 ou plus recent
- Composer
- MySQL ou MariaDB
- Extensions PHP courantes pour Symfony : `ctype`, `iconv`, `pdo_mysql`, `openssl`
- Symfony CLI optionnelle pour lancer le serveur local
- Apache/Nginx en production, avec le document root pointe vers `public/`

## Installation Locale

1. Cloner le projet.

```bash
git clone <url-du-depot>
cd oflix
```

2. Installer les dependances PHP.

```bash
composer install
```

3. Creer le fichier d'environnement local.

```bash
cp .env .env.local
```

Sous Windows PowerShell :

```powershell
Copy-Item .env .env.local
```

4. Configurer les variables dans `.env.local`.

```dotenv
APP_ENV=dev
APP_SECRET=change_me
DATABASE_URL="mysql://user:password@127.0.0.1:3306/oflix?serverVersion=8.2.0&charset=utf8mb4"
TMDB_API_KEY=change_me
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_PASSPHRASE=change_me
```

Ne pas versionner `.env.local` : il contient des informations propres a la machine et des secrets.

## Base De Donnees

Creer la base, appliquer les migrations, puis charger les fixtures si besoin.

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load
```

La commande de fixtures vide la base avant d'inserer les donnees de demonstration. Elle cree notamment les comptes suivants :

- `admin@admin.com` / `admin`
- `manager@manager.com` / `manager`
- `user@user.com` / `user`

## Import Du Catalogue Depuis TMDb

Le catalogue peut etre alimente automatiquement depuis [TMDb](https://www.themoviedb.org/) (films et series populaires/les mieux notes), en plus ou a la place des fixtures. Necessite une cle `TMDB_API_KEY` valide dans `.env.local`.

```bash
php bin/console app:movies:import
php bin/console app:movies:import --pages=2
php bin/console app:movies:import --providers=netflix,disney
```

L'option `--pages` (5 par defaut) definit le nombre de pages recuperees pour chacune des listes "popular" et "top_rated", pour les films et pour les series. La commande est relancable : les films deja importes sont mis a jour plutot que dupliques.

L'option `--providers` filtre l'import sur des plateformes de streaming disponibles en France (`netflix`, `prime`, `disney`, `crunchyroll`, `adn`, combinables avec des virgules) au lieu des listes "popular"/"top_rated". Attention : la disponibilite d'un titre sur une plateforme evolue dans le temps cote TMDb, les donnees importees peuvent donc se perimer plus vite qu'avec l'import par defaut.

## Cles JWT

L'API utilise LexikJWTAuthenticationBundle. Generer les cles JWT en local si elles n'existent pas encore.

```bash
php bin/console lexik:jwt:generate-keypair
```

Les fichiers `config/jwt/*.pem` sont ignores par Git. En production, ils doivent etre generes ou fournis par l'environnement de deploiement.

## Lancer Le Projet En Local

Avec Symfony CLI :

```bash
symfony server:start
```

Sans Symfony CLI, configurer Apache/Nginx pour servir le dossier `public/` comme racine web.

Pages utiles :

- Front-office : `/`
- Catalogue : `/catalogue`
- Connexion : `/login`
- Back-office : `/back/movie/`
- API login JWT : `/api/login_check`

## Tests Et Validation

Symfony ne charge pas `.env.local` en environnement `test`. Creer donc un fichier `.env.test.local` avec une URL de base de test.

```dotenv
DATABASE_URL="mysql://user:password@127.0.0.1:3306/oflix?serverVersion=8.2.0&charset=utf8mb4"
```

Doctrine ajoute automatiquement le suffixe `_test` grace a `config/packages/test/doctrine.yaml`. Avec l'exemple ci-dessus, la base utilisee sera `oflix_test`.

Initialiser la base de test :

```bash
php bin/console doctrine:database:create --env=test --if-not-exists
php -d xdebug.mode=off bin/console doctrine:schema:update --force --env=test
php bin/console doctrine:fixtures:load --env=test
```

Lancer les tests :

```bash
php -d xdebug.mode=off bin/phpunit
```

Sur cette configuration Windows/Wamp, Xdebug peut provoquer un arret silencieux de PHPUnit sur les tests front. Le lancer avec `-d xdebug.mode=off` evite ce probleme.

Commandes de verification utiles :

```bash
composer validate --strict
php bin/console lint:yaml config --parse-tags
php bin/console lint:twig templates
php bin/console lint:container
php bin/console doctrine:schema:validate
php -d xdebug.mode=off bin/phpunit
```

## Deploiement Production

1. Recuperer le code sur le serveur.

```bash
git pull origin master
```

2. Installer les dependances sans les outils de developpement.

```bash
composer install --no-dev --optimize-autoloader
```

3. Configurer les variables d'environnement production.

```dotenv
APP_ENV=prod
APP_DEBUG=0
APP_SECRET=une_valeur_longue_et_unique
DATABASE_URL="mysql://user:password@host:3306/oflix?serverVersion=8.2.0&charset=utf8mb4"
TMDB_API_KEY=cle_tmdb
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_PASSPHRASE=passphrase_jwt
```

4. Generer ou installer les cles JWT.

```bash
php bin/console lexik:jwt:generate-keypair --env=prod
```

5. Appliquer les migrations.

```bash
php bin/console doctrine:migrations:migrate --no-interaction --env=prod
```

6. Preparer le cache et les assets.

```bash
php bin/console cache:clear --env=prod
php bin/console cache:warmup --env=prod
php bin/console assets:install public --env=prod
```

7. Verifier les droits d'ecriture.

Le serveur web doit pouvoir ecrire dans `var/cache/` et `var/log/`.

8. Configurer le serveur web.

Le document root doit pointer vers le dossier `public/`. Ne jamais exposer la racine du projet directement.

Exemple Apache minimal :

```apache
<VirtualHost *:80>
    ServerName oflix.example.com
    DocumentRoot /var/www/oflix/public

    <Directory /var/www/oflix/public>
        AllowOverride All
        Require all granted
        FallbackResource /index.php
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/oflix_error.log
    CustomLog ${APACHE_LOG_DIR}/oflix_access.log combined
</VirtualHost>
```

Activer HTTPS en production, par exemple avec un certificat Let's Encrypt.

## Checklist Avant Mise En Ligne

- `APP_ENV=prod` et `APP_DEBUG=0`
- `.env.local` ou variables serveur configurees avec de vrais secrets
- Base de donnees creee et migrations appliquees
- Cles JWT presentes et protegees
- `public/` configure comme document root
- `var/cache/` et `var/log/` accessibles en ecriture par le serveur web
- `composer install --no-dev --optimize-autoloader` execute
- `cache:clear` et `cache:warmup` executes
- HTTPS actif
- Back-office protege par les roles Symfony
