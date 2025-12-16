<?php

namespace App\Models;

use App\Services\MregEstProponenteQuery;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

class MregEstProponente extends Model
{
  // Importante: como usas Query\Builder, Eloquent no hace mapping de atributos,
  // pero sí construye colecciones que podemos post-procesar.

  public function newQuery(): Builder
  {
    return MregEstProponenteQuery::builder();
  }
}
