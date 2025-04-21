<?php
declare(strict_types=1);

namespace Cocoon\StorageManager;

use League\Flysystem\Filesystem;
use Cocoon\StorageManager\Finder;
use Cocoon\StorageManager\FileManager;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\UnixVisibility\PortableVisibilityConverter;
use League\Flysystem\Visibility;
use Cocoon\StorageManager\Exceptions\StorageOperationException;
use League\Flysystem\FilesystemAdapter;

/**
 * Classe principale de gestion des fichiers
 * 
 * Cette classe fournit une interface simple et puissante pour la gestion des fichiers.
 * Elle utilise Flysystem en interne pour assurer la compatibilité avec différents systèmes de stockage.
 * 
 * Fonctionnalités principales :
 * - Gestion des fichiers (lecture, écriture, suppression)
 * - Gestion des répertoires
 * - Recherche de fichiers avec filtres
 * - Support de différents adaptateurs de stockage
 * 
 * @package Cocoon\StorageManager
 */
class Storage
{
    /** @var Filesystem|null Instance du système de fichiers */
    protected static ?Filesystem $store = null;

    /** @var string Classe de l'adaptateur par défaut */
    protected static string $defaultAdater = LocalFilesystemAdapter::class;

    /** @var FilesystemAdapter|null Adaptateur de stockage actuel */
    protected static ?FilesystemAdapter $adapter = null;

    /** @var array Liste des adaptateurs disponibles */
    protected static array $adapterListing = [];

    /** @var StorageConfig|null Configuration du stockage */
    protected static ?StorageConfig $config = null;

    /**
     * Initialise le stockage avec une configuration
     * 
     * @param string|array|StorageConfig $config Configuration du stockage
     * @return bool|null Retourne false si déjà initialisé, null sinon
     */
    public static function init(string|array|StorageConfig $config): bool|null
    {
        if (is_null(static::$adapter)) {
            if (is_string($config)) {
                $config = new StorageConfig($config);
            } elseif (is_array($config)) {
                $config = StorageConfig::fromArray($config);
            }
            static::$config = $config;

            // Création du convertisseur de visibilité
            $visibility = new PortableVisibilityConverter(
                $config->getOption('visibility', 'public') === 'public' ? 0644 : 0600,
                $config->getOption('directory_visibility', 'public') === 'public' ? 0755 : 0700
            );

            // Création de l'adaptateur
            static::$adapter = new LocalFilesystemAdapter(
                $config->getBasePath(),
                $visibility,
                LOCK_EX,
                LocalFilesystemAdapter::DISALLOW_LINKS
            );

            static::$store = new Filesystem(static::$adapter);
            return null;
        }
        return false;
    }

    /**
     * Obtient la configuration actuelle
     * 
     * @return StorageConfig|null Configuration actuelle
     */
    public static function getConfig(): ?StorageConfig
    {
        return static::$config;
    }

    /**
     * Obtient une instance de gestionnaire de fichier pour le chemin spécifié
     * 
     * @param string $path Chemin du fichier
     * @return FileManager Instance du gestionnaire de fichier
     */
    public static function file(string $path): FileManager
    {
        return new FileManager($path, static::$store);
    }

    /**
     * Obtient une instance de recherche de fichiers
     * 
     * @return Finder Instance du moteur de recherche
     */
    public static function find(): Finder
    {
        return new Finder(static::$store);
    }

    /**
     * Définit ou obtient l'adaptateur de stockage
     * 
     * @param FilesystemAdapter|null $adapter Nouvel adaptateur
     * @return FilesystemAdapter|null Adaptateur actuel
     */
    public static function adapter(?FilesystemAdapter $adapter = null): ?FilesystemAdapter
    {
        if ($adapter !== null) {
            static::$adapter = $adapter;
            static::$store = new Filesystem(static::$adapter);
        }
        return static::$adapter;
    }

    /**
     * Écrit du contenu dans un fichier
     * 
     * @param string $path Chemin du fichier
     * @param string $contents Contenu à écrire
     * @param array $config Options de configuration
     * @return void
     * @throws StorageOperationException En cas d'erreur d'écriture
     */
    public static function put(string $path, string $contents, array $config = []): void
    {
        try {
            static::$store?->write($path, $contents, $config);
        } catch (\Exception $e) {
            throw new StorageOperationException("Échec de l'écriture dans le fichier : $path", 0, $e);
        }
    }

    /**
     * Lit le contenu d'un fichier
     * 
     * @param string $path Chemin du fichier
     * @return string|false Contenu du fichier ou false en cas d'erreur
     * @throws StorageOperationException En cas d'erreur de lecture
     */
    public static function get(string $path): string|false
    {
        try {
            return static::$store?->read($path);
        } catch (\Exception $e) {
            throw new StorageOperationException("Échec de la lecture du fichier : $path", 0, $e);
        }
    }

    /**
     * Supprime un fichier
     * 
     * @param string $path Chemin du fichier
     * @return void
     * @throws StorageOperationException En cas d'erreur de suppression
     */
    public static function delete(string $path): void
    {
        try {
            static::$store?->delete($path);
        } catch (\Exception $e) {
            throw new StorageOperationException("Échec de la suppression du fichier : $path", 0, $e);
        }
    }

    /**
     * Copie un fichier vers un nouvel emplacement
     * 
     * @param string $path Chemin source
     * @param string $newpath Chemin de destination
     * @return void
     * @throws StorageOperationException En cas d'erreur de copie
     */
    public static function copy(string $path, string $newpath): void
    {
        try {
            static::$store?->copy($path, $newpath);
        } catch (\Exception $e) {
            throw new StorageOperationException("Échec de la copie de $path vers $newpath", 0, $e);
        }
    }

    /**
     * Déplace un fichier vers un nouvel emplacement
     * 
     * @param string $path Chemin source
     * @param string $newpath Chemin de destination
     * @return void
     * @throws StorageOperationException En cas d'erreur de déplacement
     */
    public static function move(string $path, string $newpath): void
    {
        try {
            static::$store?->move($path, $newpath);
        } catch (\Exception $e) {
            throw new StorageOperationException("Échec du déplacement de $path vers $newpath", 0, $e);
        }
    }

    /**
     * Vérifie l'existence d'un fichier ou d'un répertoire
     * 
     * @param string $path Chemin à vérifier
     * @return bool True si le fichier ou le répertoire existe
     */
    public static function exists(string $path): bool
    {
        return static::$store?->fileExists($path) || static::$store?->directoryExists($path);
    }

    /**
     * Crée un répertoire
     * 
     * @param string $dirname Nom du répertoire
     * @return void
     * @throws StorageOperationException En cas d'erreur de création
     */
    public static function mkdir(string $dirname): void
    {
        try {
            static::$store?->createDirectory($dirname);
        } catch (\Exception $e) {
            throw new StorageOperationException("Échec de la création du répertoire : $dirname", 0, $e);
        }
    }

    /**
     * Supprime un répertoire
     * 
     * @param string $dirname Nom du répertoire
     * @return void
     * @throws StorageOperationException En cas d'erreur de suppression
     */
    public static function rmdir(string $dirname): void
    {
        try {
            static::$store?->deleteDirectory($dirname);
        } catch (\Exception $e) {
            throw new StorageOperationException("Échec de la suppression du répertoire : $dirname", 0, $e);
        }
    }

    /**
     * Retourne l'instance du Filesystem
     *
     * @return Filesystem
     */
    public static function getFilesystem(): Filesystem
    {
        return static::$store;
    }
}
