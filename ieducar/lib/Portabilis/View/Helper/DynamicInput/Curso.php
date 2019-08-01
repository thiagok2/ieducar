<?php

require_once 'lib/Portabilis/View/Helper/DynamicInput/CoreSelect.php';
require_once 'Portabilis/Business/Professor.php';

class Portabilis_View_Helper_DynamicInput_Curso extends Portabilis_View_Helper_DynamicInput_CoreSelect
{
    protected function inputName()
    {
        return 'ref_cod_curso';
    }

    protected function inputOptions($options)
    {
        $resources = $options['resources'];
        $instituicaoId = $this->getInstituicaoId($options['instituicaoId'] ?? null);
        $escolaId = $this->getEscolaId($options['escolaId'] ?? null);
        $userId = $this->getCurrentUserId();
        $isProfessor = Portabilis_Business_Professor::isProfessor($instituicaoId, $userId);

        if ($instituicaoId && $escolaId && empty($resources) && $isProfessor) {
            $cursos = Portabilis_Business_Professor::cursosAlocado($instituicaoId, $escolaId, $userId);
            $resources = Portabilis_Array_Utils::setAsIdValue($cursos, 'id', 'nome');
        } elseif ($escolaId && empty($resources)) {
            $resources = App_Model_IedFinder::getCursos($escolaId);
        }

        return $this->insertOption(null, 'Selecione um curso', $resources);
    }

    public function curso($options = [])
    {
        parent::select($options);
    }
}
