<?php

namespace App\Services;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class MregEstProponenteQuery
{
  public static function builder(): Builder
  {
    $columns = [
      'proponente' => ['alias' => 'id', 'cast' => 'string', 'table' => 'mreg_est_proponentes'],
      'nombre' => ['alias' => 'name', 'cast' => 'string', 'table' => 'mreg_est_proponentes'],
      'identificacion' => ['alias' => 'id_number', 'cast' => 'integer', 'table' => 'mreg_est_proponentes'],
      'corto' => ['alias' => 'status', 'cast' => 'string', 'table' => 'mreg_estadoproponentes'],
      'descripcion' => ['alias' => 'status_desc', 'cast' => 'string', 'table' => 'mreg_estadoproponentes'],

    ];

    $selects = [];
    foreach ($columns as $col => $cfg) {
      $alias = $cfg['alias'];
      $qualified = "{$cfg['table']}.{$col}";
      switch ($cfg['cast'] ?? null) {
        case 'boolean':
          $selects[] = DB::raw("CASE WHEN {$qualified} = '1' THEN 1 ELSE 0 END as {$alias}");
          break;
        case 'integer':
          $selects[] = DB::raw("CAST({$qualified} as SIGNED) as {$alias}");
          break;
        default:
          $selects[] = DB::raw("{$qualified} as {$alias}");
      }
    }

    // Forzar collation en el ON del JOIN
    $base = DB::connection('sii')
      ->table('mreg_est_proponentes')
      ->leftJoin('mreg_estadoproponentes', function ($join) {
        $join->on(
          DB::raw("mreg_estadoproponentes.id COLLATE latin1_spanish_ci"),
          '=',
          DB::raw("mreg_est_proponentes.idestadoproponente COLLATE latin1_spanish_ci")
        );
      })
      ->select($selects);

    return DB::connection('sii')->query()->fromSub($base, 'v_mreg_est_proponentes');
  }
}
