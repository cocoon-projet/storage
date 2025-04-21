<?php
declare(strict_types=1);

namespace Cocoon\StorageManager;

use Carbon\Carbon;
use DateTimeInterface;
use League\Flysystem\Filesystem;
use Cocoon\StorageManager\Exceptions\StorageOperationException;

/**
 * Gestionnaire de fichiers
 * 
 * Cette classe fournit une interface orientée objet pour la gestion des fichiers.
 * Elle encapsule les opérations de base sur les fichiers et ajoute des fonctionnalités
 * avancées comme la validation et la manipulation des métadonnées.
 * 
 * @package Cocoon\StorageManager
 */
class FileManager
{
    /** @var string Chemin du fichier */
    private string $path;

    /** @var Filesystem Instance du système de fichiers */
    private Filesystem $filesystem;

    /**
     * Constructeur
     * 
     * @param string $path Chemin du fichier
     * @param Filesystem $filesystem Instance du système de fichiers
     */
    public function __construct(string $path, Filesystem $filesystem)
    {
        $this->path = $path;
        $this->filesystem = $filesystem;
    }

    /**
     * Obtient le nom du fichier
     * 
     * @return string Nom du fichier
     */
    public function filename(): string
    {
        return pathinfo($this->path, PATHINFO_FILENAME);
    }

    /**
     * Obtient l'extension du fichier
     * 
     * @return string Extension du fichier
     */
    public function extension(): string
    {
        return pathinfo($this->path, PATHINFO_EXTENSION);
    }

    /**
     * Obtient le nom complet du fichier
     * 
     * @return string Nom complet du fichier
     */
    public function name(): string
    {
        return pathinfo($this->path, PATHINFO_BASENAME);
    }

    /**
     * Vérifie si le fichier existe
     * 
     * @return bool True si le fichier existe
     */
    public function exists(): bool
    {
        return $this->filesystem->fileExists($this->path);
    }

    /**
     * Lit le contenu du fichier
     * 
     * @return string Contenu du fichier
     * @throws StorageOperationException En cas d'erreur de lecture
     */
    public function read(): string
    {
        try {
            return $this->filesystem->read($this->path);
        } catch (\Exception $e) {
            throw new StorageOperationException("Échec de la lecture du fichier : {$this->path}", 0, $e);
        }
    }

    /**
     * Écrit du contenu dans le fichier
     * 
     * @param string $contents Contenu à écrire
     * @param array $config Options de configuration
     * @return void
     * @throws StorageOperationException En cas d'erreur d'écriture
     */
    public function write(string $contents, array $config = []): void
    {
        try {
            $this->filesystem->write($this->path, $contents, $config);
        } catch (\Exception $e) {
            throw new StorageOperationException("Échec de l'écriture dans le fichier : {$this->path}", 0, $e);
        }
    }

    /**
     * Supprime le fichier
     * 
     * @return void
     * @throws StorageOperationException En cas d'erreur de suppression
     */
    public function delete(): void
    {
        try {
            $this->filesystem->delete($this->path);
        } catch (\Exception $e) {
            throw new StorageOperationException("Échec de la suppression du fichier : {$this->path}", 0, $e);
        }
    }

    /**
     * Copie le fichier vers un nouvel emplacement
     * 
     * @param string $newPath Nouveau chemin
     * @return void
     * @throws StorageOperationException En cas d'erreur de copie
     */
    public function copy(string $newPath): void
    {
        try {
            $this->filesystem->copy($this->path, $newPath);
        } catch (\Exception $e) {
            throw new StorageOperationException("Échec de la copie vers : $newPath", 0, $e);
        }
    }

    /**
     * Déplace le fichier vers un nouvel emplacement
     * 
     * @param string $newPath Nouveau chemin
     * @return void
     * @throws StorageOperationException En cas d'erreur de déplacement
     */
    public function move(string $newPath): void
    {
        try {
            $this->filesystem->move($this->path, $newPath);
            $this->path = $newPath;
        } catch (\Exception $e) {
            throw new StorageOperationException("Échec du déplacement vers : $newPath", 0, $e);
        }
    }

    /**
     * Obtient la taille du fichier
     * 
     * @return int Taille en octets
     * @throws StorageOperationException En cas d'erreur
     */
    public function size(): int
    {
        try {
            return $this->filesystem->fileSize($this->path);
        } catch (\Exception $e) {
            throw new StorageOperationException("Échec de la récupération de la taille du fichier : {$this->path}", 0, $e);
        }
    }

    /**
     * Obtient la date de dernière modification
     * 
     * @return int Timestamp Unix
     * @throws StorageOperationException En cas d'erreur
     */
    public function lastModified(): int
    {
        try {
            return $this->filesystem->lastModified($this->path);
        } catch (\Exception $e) {
            throw new StorageOperationException("Échec de la récupération de la date de modification : {$this->path}", 0, $e);
        }
    }

    /**
     * Obtient le type MIME du fichier
     * 
     * @return string Type MIME
     * @throws StorageOperationException En cas d'erreur
     */
    public function mimeType(): string
    {
        try {
            return $this->filesystem->mimeType($this->path);
        } catch (\Exception $e) {
            throw new StorageOperationException("Échec de la récupération du type MIME : {$this->path}", 0, $e);
        }
    }

    /**
     * Obtient le chemin du fichier
     * 
     * @return string Chemin du fichier
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Définit la visibilité du fichier
     * 
     * @param string $visibility Visibilité ('public' ou 'private')
     * @return void
     * @throws StorageOperationException En cas d'erreur de modification
     */
    public function setVisibility(string $visibility): void
    {
        try {
            $this->filesystem->setVisibility($this->path, $visibility);
        } catch (\Exception $e) {
            throw new StorageOperationException("Échec de la modification de la visibilité", 0, $e);
        }
    }

    /**
     * Obtient la visibilité du fichier
     * 
     * @return string Visibilité ('public' ou 'private')
     * @throws StorageOperationException En cas d'erreur de lecture
     */
    public function visibility(): string
    {
        try {
            return $this->filesystem->visibility($this->path);
        } catch (\Exception $e) {
            throw new StorageOperationException("Échec de la lecture de la visibilité", 0, $e);
        }
    }

    /**
     * Génère une URL publique pour le fichier
     * 
     * @param array $config Options de configuration
     * @return string URL publique
     * @throws StorageOperationException En cas d'erreur de génération
     */
    public function publicUrl(array $config = []): string
    {
        try {
            return $this->filesystem->publicUrl($this->path, $config);
        } catch (\Exception $e) {
            throw new StorageOperationException("Échec de la génération de l'URL publique", 0, $e);
        }
    }

    /**
     * Génère une URL temporaire pour le fichier
     * 
     * @param int $expiration Durée de validité en secondes
     * @param DateTimeInterface $expiresAt Date d'expiration
     * @param array $config Options de configuration
     * @return string URL temporaire
     * @throws StorageOperationException En cas d'erreur de génération
     */
    public function temporaryUrl(int $expiration, DateTimeInterface $expiresAt, array $config = []): string
    {
        try {
            return $this->filesystem->temporaryUrl($this->path, $expiresAt, $config);
        } catch (\Exception $e) {
            throw new StorageOperationException("Échec de la génération de l'URL temporaire", 0, $e);
        }
    }

    /**
     * Calcule le checksum du fichier
     * 
     * @param string $path Chemin du fichier
     * @param array $config Options de configuration
     * @return string Checksum
     * @throws StorageOperationException En cas d'erreur de calcul
     */
    public function checksum(string $path, array $config = []): string
    {
        try {
            return $this->filesystem->checksum($path, $config);
        } catch (\Exception $e) {
            throw new StorageOperationException("Échec du calcul du checksum", 0, $e);
        }
    }

    /**
     * Obtient la date de dernière modification sous forme d'objet Carbon
     * 
     * @param string $locale Locale pour la date (par défaut: 'fr')
     * @return Carbon Instance Carbon de la date
     * @throws StorageOperationException En cas d'erreur de lecture
     */
    public function dateTime(string $locale = 'fr'): Carbon
    {
        Carbon::setLocale($locale);
        return Carbon::createFromTimestamp($this->lastModified());
    }
}
