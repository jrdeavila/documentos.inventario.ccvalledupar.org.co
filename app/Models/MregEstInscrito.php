<?php

namespace App\Models;

use App\Services\MregEstInscritosQuery;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class MregEstInscrito extends Model
{
  // Importante: como usas Query\Builder, Eloquent no hace mapping de atributos,
  // pero sí construye colecciones que podemos post-procesar.

  public function newQuery(): Builder
  {
    return MregEstInscritosQuery::unified();
  }

  // Scopes
  public function scopeWhereName($query, $name)
  {
    // Usa bindings para evitar SQL injection
    $like = "%" . strtolower($name) . "%";
    return $query->whereRaw("
      LOWER(name) LIKE ? OR 
      LOWER(first_name) LIKE ? OR 
      LOWER(last_name) LIKE ? OR 
      LOWER(second_name) LIKE ? OR 
      LOWER(second_last_name) LIKE ?
    ", [$like, $like, $like, $like, $like]);
  }

  public function scopeWhereIdNumber($query, $idNumber)
  {
    // Si quieres búsqueda exacta: ->where('id_number', $idNumber)
    // Si quieres like: usa whereRaw/like apropiado. Aquí asumo exacta.
    return $query->where('id_number', $idNumber);
  }
}
