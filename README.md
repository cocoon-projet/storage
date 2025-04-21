[![PHP Composer](https://github.com/cocoon-projet/storage/actions/workflows/ci.yml/badge.svg)](https://github.com/cocoon-projet/collection/actions/workflows/ci.yml) [![codecov](https://codecov.io/gh/cocoon-projet/storage/graph/badge.svg?token=0R7HW7AMX7)](https://codecov.io/gh/cocoon-projet/storage) ![License](https://img.shields.io/badge/Licence-MIT-green)

# Cocoon Storage Manager

Une bibliothèque PHP moderne et puissante pour la gestion des fichiers, basée sur Flysystem.

## Pré-requis

![PHP Version](https://img.shields.io/badge/php:version-8.0-blue)

## Installation

```bash
composer require cocoon-projet/storage
```

## Configuration

```php
use Cocoon\StorageManager\Storage;
use Cocoon\StorageManager\StorageConfig;

// Configuration simple
$config = new StorageConfig('/chemin/vers/le/dossier');

// Configuration avancée
$config = new StorageConfig('/chemin/vers/le/dossier', [
    'visibility' => 'public',        // Visibilité des fichiers (public/private)
    'directory_visibility' => 'public', // Visibilité des répertoires
    'case_sensitive' => true,        // Sensibilité à la casse
]);

// Initialisation
Storage::init($config);
```

## Fonctionnalités

### Gestion des fichiers

```php
// Écrire un fichier
Storage::put('fichier.txt', 'Contenu');

// Lire un fichier
$contenu = Storage::get('fichier.txt');

// Vérifier l'existence
if (Storage::exists('fichier.txt')) {
    // ...
}

// Supprimer un fichier
Storage::delete('fichier.txt');

// Copier un fichier
Storage::copy('source.txt', 'destination.txt');

// Déplacer un fichier
Storage::move('ancien.txt', 'nouveau.txt');
```

### Gestion des répertoires

```php
// Créer un répertoire
Storage::mkdir('mon-dossier');

// Supprimer un répertoire
Storage::rmdir('mon-dossier');
```

### Recherche de fichiers

#### Recherche dans un dossier spécifique

```php
// Rechercher dans un dossier spécifique
$fichiers = Storage::find()
    ->in('mon-dossier')
    ->files()
    ->get();

// Rechercher dans plusieurs dossiers
$fichiers = Storage::find()
    ->in(['dossier1', 'dossier2'])
    ->files()
    ->get();
```

#### Filtres de recherche

```php
// Rechercher tous les fichiers
$fichiers = Storage::find()
    ->files()
    ->get();

// Rechercher tous les répertoires
$repertoires = Storage::find()
    ->directories()
    ->get();

// Filtrer par taille
$petitsFichiers = Storage::find()
    ->in('mon-dossier')
    ->size('< 1MB')
    ->get();

// Filtrer par date
$fichiersRecents = Storage::find()
    ->in('mon-dossier')
    ->date('> 1 day')
    ->get();

// Filtrer par extension
$fichiersTxt = Storage::find()
    ->in('mon-dossier')
    ->only(['*.txt'])
    ->get();

// Exclure des fichiers
$fichiersNonPhp = Storage::find()
    ->in('mon-dossier')
    ->except(['*.php'])
    ->get();
```

#### Tri des résultats

```php
// Trier les résultats
$fichiersTries = Storage::find()
    ->in('mon-dossier')
    ->sortByName()          // Trier par nom
    ->sortByDate()          // Trier par date
    ->sortBySize()          // Trier par taille
    ->sortByExtension()     // Trier par extension
    ->get();
```

### Gestion avancée des fichiers

```php
// Obtenir un gestionnaire de fichier
$fichier = Storage::file('mon-fichier.txt');

// Vérifier le type MIME
$type = $fichier->mimeType();

// Obtenir la taille
$taille = $fichier->size();

// Obtenir la date de modification
$date = $fichier->dateTime();

// Obtenir le nom
$nom = $fichier->name();
```

## Fonctionnalités avancées

### Filtres de recherche

- `files()` : Rechercher uniquement les fichiers
- `directories()` : Rechercher uniquement les répertoires
- `in($path)` : Rechercher dans un dossier spécifique
- `size()` : Filtrer par taille (ex: '< 1MB', '> 100KB')
- `date()` : Filtrer par date (ex: '> 1 day', '< 1 week')
- `only()` : Inclure uniquement certains fichiers (ex: ['*.txt', '*.pdf'])
- `except()` : Exclure certains fichiers (ex: ['*.tmp', '*.log'])

### Tri des résultats

- `sortByName()` : Trier par nom
- `sortByDate()` : Trier par date
- `sortBySize()` : Trier par taille
- `sortByExtension()` : Trier par extension

## Exemple complet

```php
use Cocoon\StorageManager\Storage;
use Cocoon\StorageManager\StorageConfig;

// Configuration
$config = new StorageConfig(__DIR__ . '/storage', [
    'visibility' => 'public',
    'directory_visibility' => 'public',
    'case_sensitive' => true,
]);

// Initialisation
Storage::init($config);

// Création de dossiers
Storage::mkdir('documents');
Storage::mkdir('images');
Storage::mkdir('cache');

// Recherche dans des dossiers spécifiques
$fichiersDocuments = Storage::find()
    ->in('documents')
    ->files()
    ->get();

$fichiersCache = Storage::find()
    ->in('cache')
    ->files()
    ->get();

// Recherche avec filtres
$fichiersRecents = Storage::find()
    ->in(['documents', 'images'])
    ->files()
    ->date('> 1 day')
    ->sortByDate()
    ->get();
```

## Bonnes pratiques

1. Toujours initialiser le stockage avec une configuration appropriée
2. Utiliser la méthode `in()` pour limiter la recherche à des dossiers spécifiques
3. Combiner les filtres pour des recherches plus précises
4. Gérer les exceptions pour les opérations critiques
5. Nettoyer les fichiers temporaires après utilisation
6. Utiliser des chemins relatifs pour la portabilité

## Tests

```bash
composer test
```

## Licence

MIT License - Voir le fichier [LICENSE](LICENSE) pour plus de détails.