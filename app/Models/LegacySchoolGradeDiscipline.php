<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LegacySchoolGradeDiscipline extends Model
{
    protected $table = 'pmieducar.escola_serie_disciplina';

    protected $primaryKey = 'ref_ref_cod_serie';

    protected $fillable = [
        'ref_ref_cod_serie',
        'ref_ref_cod_escola',
        'ref_cod_disciplina',
        'ativo',
        'carga_horaria',
        'etapas_especificas',
        'etapas_utilizadas',
        'updated_at',
        'anos_letivos',
    ];

    public $timestamps = false;
}
