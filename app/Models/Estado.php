<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Estado extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'estados';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'nombre',
        'codigo_postal'
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [];

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'nombre';
    }

    /**
     * Scope para buscar por nombre
     */
    public function scopeByNombre($query, string $nombre)
    {
        return $query->where('nombre', $nombre);
    }

    /**
     * Scope para búsqueda parcial por nombre
     */
    public function scopeSearchByNombre($query, string $searchTerm)
    {
        return $query->where('nombre', 'LIKE', '%' . $searchTerm . '%');
    }

    /**
     * Scope para ordenar por nombre
     */
    public function scopeOrderedByNombre($query, string $direction = 'asc')
    {
        return $query->orderBy('nombre', $direction);
    }

    /**
     * Accessor para obtener el nombre formateado
     */
    public function getNombreFormateadoAttribute(): string
    {
        return ucwords(strtolower($this->nombre));
    }

    /**
     * Mutator para normalizar el nombre antes de guardar
     */
    public function setNombreAttribute(string $value): void
    {
        $this->attributes['nombre'] = trim($value);
    }

    /**
     * Verificar si el estado ya existe por nombre
     */
    public static function existsByNombre(string $nombre): bool
    {
        return static::where('nombre', trim($nombre))->exists();
    }

    /**
     * Obtener o crear estado por nombre
     */
    public static function findOrCreateByNombre(string $nombre, array $additionalData = []): static
    {
        return static::firstOrCreate(
            ['nombre' => trim($nombre)],
            array_merge(['codigo_postal' => null], $additionalData)
        );
    }

    /**
     * Representación en string del modelo
     */
    public function __toString(): string
    {
        return $this->nombre;
    }
}