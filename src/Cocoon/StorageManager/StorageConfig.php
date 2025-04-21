<?php

declare(strict_types=1);

namespace Cocoon\StorageManager;

/**
 * Configuration du stockage
 * 
 * Cette classe permet de gérer la configuration du stockage de manière
 * centralisée et flexible. Elle supporte différentes options de configuration
 * comme le chemin de base, les adaptateurs, les options de cache, etc.
 * 
 * @package Cocoon\StorageManager
 */
class StorageConfig
{
    /** @var string Chemin de base pour le stockage */
    private string $basePath;

    /** @var array Options de configuration */
    private array $options = [];

    /** @var array Options par défaut */
    private const DEFAULTS = [
        'visibility' => 'public',
        'directory_visibility' => 'public',
        'case_sensitive' => true,
        'disable_asserts' => false,
        'root' => null,
    ];

    /**
     * Constructeur
     * 
     * @param string $basePath Chemin de base pour le stockage
     * @param array $options Options de configuration
     */
    public function __construct(string $basePath, array $options = [])
    {
        $this->basePath = $basePath;
        $this->options = array_merge(self::DEFAULTS, $options);
    }

    /**
     * Obtient le chemin de base
     * 
     * @return string Chemin de base
     */
    public function getBasePath(): string
    {
        return $this->basePath;
    }

    /**
     * Obtient une option de configuration
     * 
     * @param string $key Clé de l'option
     * @param mixed $default Valeur par défaut
     * @return mixed Valeur de l'option
     */
    public function getOption(string $key, mixed $default = null): mixed
    {
        return $this->options[$key] ?? $default;
    }

    /**
     * Définit une option de configuration
     * 
     * @param string $key Clé de l'option
     * @param mixed $value Valeur de l'option
     * @return void
     */
    public function setOption(string $key, mixed $value): void
    {
        $this->options[$key] = $value;
    }

    /**
     * Obtient toutes les options de configuration
     * 
     * @return array Options de configuration
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Vérifie si une option existe
     * 
     * @param string $key Clé de l'option
     * @return bool True si l'option existe
     */
    public function hasOption(string $key): bool
    {
        return isset($this->options[$key]);
    }

    /**
     * Crée une instance de configuration à partir d'un tableau
     * 
     * @param array $config Configuration
     * @return self Instance de configuration
     */
    public static function fromArray(array $config): self
    {
        $basePath = $config['base_path'] ?? '';
        $options = $config['options'] ?? [];
        return new self($basePath, $options);
    }
} 