<?php


namespace Cocoon\StorageManager;

use Carbon\Carbon;

class FileInfo
{
    protected $info;

    public function __construct($filename)
    {
        $this->info = $filename;
    }

    /**
     * Lit la taille d'un fichier
     *
     * @return int
     */
    public function size(): ?int
    {
        return $this->info->fileSize();
    }

    /**
     * Lit la date de dernière modification
     *
     * @return int
     */
    public function lastModified(): int
    {
        return $this->info->lastModified();
    }

    /**
     * Le chemin sans le nom de fichier
     *
     * @return string
     */
    public function path(): string
    {
        return $this->info->path();
    }

    /**
     * Lit le chemin absolu d'un fichier
     *
     * @return string
     */
    public function dirname(): string
    {
        return dirname($this->info->path());
    }

    /**
     *  Lit le type de fichier
     *
     * @return string
     */
    public function type(): string
    {
        return $this->info->type();
    }

    /**
     * Récupère l'extension d'un fichier
     *
     * @return string
     */
    public function extension(): ?string
    {
        $file = explode('.', $this->filename());
        return end($file);
    }

    public function filename()
    {
        $file = explode('/', $this->info->path());
        return end($file);
    }

    public function basename()
    {
        return basename($this->info->path());
    }

    public function dateTime(string $locale = 'fr'): Carbon
    {
        carbon::setLocale($locale);
        return Carbon::createFromTimestamp($this->lastModified());
    }

    public function visibility(): string
    {
        return $this->info->visibility();
    }
}
