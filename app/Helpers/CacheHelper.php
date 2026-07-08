<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Cache;

/**
 * CacheHelper
 *
 * Provee métodos de caché compatibles con cualquier driver de Laravel.
 * Los drivers 'file' y 'database' no soportan etiquetas (tags). Este helper
 * detecta automáticamente si el driver activo soporta tags y, en caso contrario,
 * opera directamente sobre Cache sin tags para evitar BadMethodCallException.
 */
class CacheHelper
{
    /**
     * Drivers de caché que soportan etiquetas (tags).
     */
    protected static array $tagSupportedDrivers = ['redis', 'memcached'];

    /**
     * Indica si el driver de caché activo soporta tags.
     */
    public static function supportsTags(): bool
    {
        return in_array(config('cache.default'), static::$tagSupportedDrivers, true);
    }

    /**
     * Obtiene una instancia de caché con tags si el driver lo soporta,
     * o sin tags (Cache directamente) si no lo soporta.
     *
     * @param string|array $tags
     * @return \Illuminate\Cache\TaggedCache|\Illuminate\Cache\Repository
     */
    public static function store(string|array $tags): \Illuminate\Cache\Repository|\Illuminate\Cache\TaggedCache
    {
        if (static::supportsTags()) {
            return Cache::tags($tags);
        }

        return Cache::store(config('cache.default'));
    }

    /**
     * Recuerda un valor en caché usando tags si el driver lo soporta.
     *
     * @param string|array $tags
     * @param string $key
     * @param int $ttl Segundos
     * @param callable $callback
     * @return mixed
     */
    public static function remember(string|array $tags, string $key, int $ttl, callable $callback): mixed
    {
        if (static::supportsTags()) {
            return Cache::tags($tags)->remember($key, $ttl, $callback);
        }

        $version = static::ensureTagVersion($tags);

        return Cache::remember(static::versionedKey($tags, $version, $key), $ttl, $callback);
    }

    /**
     * Elimina una clave del caché. Usa tags si el driver lo soporta.
     *
     * @param string|array $tags
     * @param string $key
     */
    public static function forget(string|array $tags, string $key): void
    {
        if (static::supportsTags()) {
            Cache::tags($tags)->forget($key);
            return;
        }

        Cache::forget($key);
    }

    /**
     * Vacía todas las entradas asociadas a un tag. Usa tags si el driver lo
     * soporta. Si no, bumpea una versión embebida en la clave para invalidar
     * cualquier entrada versionada sin tags.
     *
     * @param string|array $tags
     */
    public static function flush(string|array $tags): void
    {
        if (static::supportsTags()) {
            Cache::tags($tags)->flush();
            return;
        }

        static::ensureTagVersion($tags);
        Cache::increment(static::versionCounterKey($tags));
    }

    /**
     * Clave estable del contador de versión para un tag (o grupo de tags).
     */
    private static function versionCounterKey(string|array $tags): string
    {
        $flat = is_array($tags) ? implode('_', $tags) : $tags;

        return "tagver_{$flat}";
    }

    /**
     * Asegura que el contador de versión para el tag exista y devuelve su valor actual.
     */
    private static function ensureTagVersion(string|array $tags): int
    {
        $counter = static::versionCounterKey($tags);
        Cache::add($counter, 1, 60 * 60 * 24 * 365);

        return (int) Cache::get($counter, 1);
    }

    /**
     * Construye la clave versionada que will use when el driver no soporta tags.
     */
    private static function versionedKey(string|array $tags, int $version, string $key): string
    {
        $counter = static::versionCounterKey($tags);

        return "{$counter}_v{$version}_{$key}";
    }
}
