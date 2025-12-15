<?php

namespace App\Services;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class MregEstInscritosQuery
{
  protected static function columns(): array
  {
    return [
      'matricula' => ['alias' => 'id', 'cast' => 'string'],
      'organizacion' => ['alias' => 'organization', 'cast' => 'string'],
      'razonsocial' => ['alias' => 'name', 'cast' => 'string'],
      'numid' => ['alias' => 'id_number', 'cast' => 'integer'],
      'ctrestmatricula' => ['alias' => 'status', 'cast' => 'string'],
    ];
  }

  protected static function buildSelectFromColumns(string $table): array
  {
    $selects = [];
    foreach (self::columns() as $col => $cfg) {
      $alias = $cfg['alias'];
      $qualified = "{$table}.{$col}";
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
    return $selects;
  }

  public static function unified(): Builder
  {
    $base = DB::connection('sii')
      ->table('mreg_est_inscritos')
      ->select(self::buildSelectFromColumns('mreg_est_inscritos'));

    return DB::connection('sii')->query()->fromSub($base, 'v_mreg_est_inscritos');
  }

  // Post-procesa una colección para agregar establishments como array
  public static function attachEstablishments(Collection $inscritos): Collection
  {
    $matriculas = $inscritos->pluck('id')->filter()->unique()->values();
    return $inscritos->map(function ($row) {
      $grouped = DB::connection('sii')
        ->table('mreg_est_inscritos')
        ->whereRaw("matricula in (SELECT matricula FROM mreg_est_propietarios WHERE matriculapropietario = ?)", [$row->id])
        ->select(array_merge(
          self::buildSelectFromColumns('mreg_est_inscritos'),
        ))
        ->get()
        ->toArray();
      $row->establishments = $grouped;
      return $row;
    });
  }
}
