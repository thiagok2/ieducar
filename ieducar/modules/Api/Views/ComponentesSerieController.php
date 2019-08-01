<?php

require_once 'lib/Portabilis/Controller/ApiCoreController.php';
require_once 'lib/Portabilis/Array/Utils.php';
require_once 'lib/Portabilis/String/Utils.php';
require_once 'lib/Portabilis/Utils/Database.php';
require_once 'include/modules/clsModulesComponenteCurricularAnoEscolar.inc.php';
require_once 'include/pmieducar/clsPmieducarEscolaSerieDisciplina.inc.php';
require_once 'ComponenteCurricular/Model/TurmaDataMapper.php';

class ComponentesSerieController extends ApiCoreController
{
    public function atualizaComponentesDaSerie()
    {
        $serieId = $this->getRequest()->serie_id;
        $componentes = json_decode($this->getRequest()->componentes);
        $arrayComponentes = [];

        foreach ($componentes as $key => $componente) {
            $arrayComponentes[$key]['id'] = $componente->id;
            $arrayComponentes[$key]['carga_horaria'] = $componente->carga_horaria;
            $arrayComponentes[$key]['tipo_nota'] = $componente->tipo_nota;
            $arrayComponentes[$key]['anos_letivos'] = $componente->anos_letivos;
        }

        $obj = new clsModulesComponenteCurricularAnoEscolar(null, $serieId, null, null, $arrayComponentes);

        $updateInfo = $obj->updateInfo();
        $componentesAtualizados = $updateInfo['update'];
        $componentesInseridos = $updateInfo['insert'];
        $componentesExcluidos = $updateInfo['delete'];

        try {
            $valido = $this->validaAtualizacao($serieId, $updateInfo);
        } catch (\Exception $e) {
            return ['msgErro' => $e->getMessage()];
        }

        if ($valido && $obj->atualizaComponentesDaSerie()) {
            if ($componentesExcluidos) {
                $this->atualizaExclusoesDeComponentes($serieId, $componentesExcluidos);
            }

            return [
                'update' => $componentesAtualizados,
                'insert' => $componentesInseridos,
                'delete' => $componentesExcluidos
            ];
        }

        return ['msgErro' => 'Erro ao alterar componentes da série.'];
    }

    protected function validaAtualizacao($serieId, $updateInfo)
    {
        $erros = [];

        if ($updateInfo['delete']) {
            foreach ($updateInfo['delete'] as $componenteId) {
                $info = Portabilis_Utils_Database::fetchPreparedQuery('
                    SELECT COUNT(cct.*), cc.nome
                    FROM modules.componente_curricular_turma cct
                    INNER JOIN modules.componente_curricular cc ON cc.id = cct.componente_curricular_id
                    WHERE TRUE
                        AND cct.componente_curricular_id = $1
                        AND cct.ano_escolar_id = $2
                    GROUP BY cc.nome
                ', ['params' => [
                    $componenteId,
                    $serieId
                ]]);

                $count = (int) $info[0]['count'] ?? 0;

                if ($count > 0) {
                    $erros[] = sprintf('Não é possível desvincular "%s" pois existem turmas vinculadas a este componente.', $info[0]['nome']);
                }

                //...

                $info = Portabilis_Utils_Database::fetchPreparedQuery('
                    SELECT COUNT(ncc.*), cc.nome
                    FROM modules.nota_componente_curricular ncc
                    INNER JOIN modules.nota_aluno na on na.id = ncc.nota_aluno_id
                    INNER JOIN pmieducar.matricula m on m.cod_matricula = na.matricula_id
                    INNER JOIN modules.componente_curricular cc on cc.id = ncc.componente_curricular_id
                    WHERE TRUE
                        AND ncc.componente_curricular_id = $1
                        AND m.ref_ref_cod_serie = $2
                    GROUP BY cc.nome
                ', ['params' => [
                    $componenteId,
                    $serieId
                ]]);

                $count = (int) $info[0]['count'] ?? 0;

                if ($count > 0) {
                    $erros[] = sprintf('Não é possível desvincular "%s" pois já existem notas lançadas para este componente nesta série.', $info[0]['nome']);
                }
            }
        }

        if ($updateInfo['update']) {
            foreach ($updateInfo['update'] as $update) {
                if (empty($update['anos_letivos_removidos'])) {
                    continue;
                }

                foreach ($update['anos_letivos_removidos'] as $ano) {
                    $info = Portabilis_Utils_Database::fetchPreparedQuery('
                        SELECT COUNT(ncc.*), cc.nome
                        FROM modules.nota_componente_curricular ncc
                        INNER JOIN modules.nota_aluno na on na.id = ncc.nota_aluno_id
                        INNER JOIN pmieducar.matricula m on m.cod_matricula = na.matricula_id
                        INNER JOIN modules.componente_curricular cc on cc.id = ncc.componente_curricular_id
                        WHERE TRUE
                            AND ncc.componente_curricular_id = $1
                            AND m.ref_ref_cod_serie = $2
                            AND m.ano = $3
                        GROUP BY cc.nome
                    ', ['params' => [
                        $update['id'],
                        $serieId,
                        $ano
                    ]]);

                    $count = (int) $info[0]['count'] ?? 0;

                    if ($count > 0) {
                        $erros[] = sprintf('Não é possível desvincular o ano %d de "%s" pois já existem notas lançadas para este componente nesta série e ano.', $ano, $info[0]['nome']);
                    }
                }
            }
        }

        if ($erros) {
            $errosDisplay = join("\n", $erros);

            throw new \Exception($errosDisplay);
        }

        return true;
    }

    public function atualizaEscolasSerieDisciplina()
    {
        $serieId = $this->getRequest()->serie_id;
        $componentes = json_decode($this->getRequest()->componentes);
        $arrayComponentes = $this->handleComponentesArray($componentes);
        $escolas = $this->getEscolasSerieBySerie($serieId);

        $escolas = array_map(function ($item){
            return $item['ref_cod_escola'];
        }, $escolas);

        $this->replicaComponentesAdicionadosNasEscolas($serieId, $arrayComponentes, $escolas);
    }

    public function atualizaComponentesEscolas()
    {
        $serieId = $this->getRequest()->serie;
        $escolas = json_decode($this->getRequest()->escolas, false);
        $componentes = json_decode($this->getRequest()->componentes, false);
        $arrayComponentes = $this->handleComponentesArray($componentes);

        $escolas = array_map(function ($item){
            return $item->id;
        }, $escolas);

        $this->replicaComponentesAdicionadosNasEscolas($serieId, $arrayComponentes, $escolas);
    }

    public function replicaComponentesAdicionadosNasEscolas($serieId, $componentes, $escolas)
    {
        if (!$escolas || !$componentes) {
            return [];
        }

        foreach ($escolas as $escola) {
            foreach ($componentes as $componente) {
                $objEscolaSerieDisciplina = new clsPmieducarEscolaSerieDisciplina($serieId, $escola,
                    $componente['id'], null, null, null, null, $componente['anos_letivos']);

                $escolaSerieDisciplina = $objEscolaSerieDisciplina->detalhe();

                if ($escolaSerieDisciplina === false){
                    $objEscolaSerieDisciplina->cadastra();
                    continue;
                }

                $objEscolaSerieDisciplina->anos_letivos = array_merge($componente['anos_letivos'], json_decode($escolaSerieDisciplina['anos_letivos'], true));
                $objEscolaSerieDisciplina->edita();
            }
        }
    }

    public function getUltimoAnoLetivoAberto()
    {
        $objEscolaAnoLetivo = new clsPmieducarEscolaAnoLetivo();
        $ultimoAnoLetivoAberto = $objEscolaAnoLetivo->getUltimoAnoLetivoAberto();

        return $ultimoAnoLetivoAberto;
    }

    public function getEscolasSerieBySerie($serieId)
    {
        $objEscolaSerie = new clsPmieducarEscolaSerie();
        $escolasDaSerie = $objEscolaSerie->lista(null, $serieId);

        if ($escolasDaSerie) {
            return $escolasDaSerie;
        }

        return false;
    }

    public function getTurmasDaSerieNoAnoLetivoAtual($serieId)
    {
        $objTurmas = new clsPmieducarTurma();
        $turmasDaSerie = $objTurmas->lista(null, null, null, $serieId, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, $this->getUltimoAnoLetivoAberto());

        if ($turmasDaSerie) {
            return $turmasDaSerie;
        }

        return false;
    }

    public function excluiEscolaSerieDisciplina($escolaId, $serieId, $disciplinaId)
    {
        $objEscolaSerieDisiciplina = new clsPmieducarEscolaSerieDisciplina($serieId, $escolaId, $disciplinaId);

        if ($objEscolaSerieDisiciplina->excluir()) {
            return true;
        }

        return false;
    }

    public function excluiComponenteDaTurma($componenteId, $turmaId)
    {
        $mapper = new ComponenteCurricular_Model_TurmaDataMapper();
        $where = ['componente_curricular_id' => $componenteId, 'turma_id' => $turmaId];
        $componente = $mapper->findAll(['componente_curricular_id', 'turma_id'], $where, [], false);

        if ($componente && $mapper->delete($componente[0])) {
            return true;
        }

        return false;
    }

    public function atualizaExclusoesDeComponentes($serieId, $componentes)
    {
        $escolas = $this->getEscolasSerieBySerie($serieId);
        $turmas = $this->getTurmasDaSerieNoAnoLetivoAtual($serieId);

        if ($escolas && $componentes) {
            foreach ($escolas as $escola) {
                foreach ($componentes as $componente) {
                    $this->excluiEscolaSerieDisciplina($escola['ref_cod_escola'], $serieId, $componente);
                }
            }
        }

        if ($turmas && $componentes) {
            foreach ($turmas as $turma) {
                foreach ($componentes as $componente) {
                    $this->excluiComponenteDaTurma($componente, $turma['cod_turma']);
                }
            }
        }
    }

    public function excluiComponentesSerie()
    {
        $serieId = $this->getRequest()->serie_id;
        $obj = new clsModulesComponenteCurricularAnoEscolar(null, $serieId);

        if ($obj->exclui()) {
            $this->excluiTodosComponenteDaTurma($serieId);
            $this->excluiTodasDisciplinasEscolaSerie($serieId);
        }
    }

    public function excluiTodasDisciplinasEscolaSerie($serieId)
    {
        $escolas = $this->getEscolasSerieBySerie($serieId);

        if ($escolas) {
            foreach ($escolas as $escola) {
                $objEscolaSerieDisciplina = new clsPmieducarEscolaSerieDisciplina($serieId, $escola['ref_cod_escola']);
                $objEscolaSerieDisciplina->excluirTodos();
            }
        }
    }

    public function excluiTodosComponenteDaTurma($serieId)
    {
        $turmas = $this->getTurmasDaSerieNoAnoLetivoAtual($serieId);
        $mapper = new ComponenteCurricular_Model_TurmaDataMapper();

        if ($turmas) {
            foreach ($turmas as $turma) {
                $where = ['turma_id' => $turma['cod_turma']];
                $componentes = $mapper->findAll(['componente_curricular_id', 'turma_id'], $where, [], false);
            }
        }

        if ($componentes) {
            foreach ($componentes as $componente) {
                $mapper->delete($componente);
            }
        }
    }

    public function existeDependencia()
    {
        $serie = $this->getRequest()->serie_id;
        $escola = $this->getRequest()->escola_id;
        $disciplinas = $this->getRequest()->disciplinas;
        $disciplinas = explode(',', $disciplinas);
        $obj = new clsPmieducarEscolaSerieDisciplina($serie, $escola, null, 1);

        return ['existe_dependencia' => $obj->existeDependencia($disciplinas)];
    }

    public function existeDispensa()
    {
        $serie = $this->getRequest()->serie_id;
        $escola = $this->getRequest()->escola_id;
        $disciplinas = $this->getRequest()->disciplinas;
        $disciplinas = explode(',', $disciplinas);
        $obj = new clsPmieducarEscolaSerieDisciplina($serie, $escola, null, 1);

        return ['existe_dispensa' => $obj->existeDispensa($disciplinas)];
    }

    private function getEscolasBySerie($serieId)
    {
        $sql = <<<'SQL'
                  SELECT escola.cod_escola, relatorio.get_nome_escola(cod_escola) AS nome_escola
                    FROM pmieducar.escola
                             JOIN pmieducar.escola_serie ON escola_serie.ref_cod_escola = escola.cod_escola
                    WHERE escola_serie.ref_cod_serie = $1
                      AND escola.ativo = 1
                      AND escola_serie.ativo = 1
                    ORDER BY nome_escola
SQL;
        $escolas = $this->fetchPreparedQuery($sql, [$serieId]);

        return ['escolas' => $escolas];
    }

    public function Gerar()
    {
        if ($this->isRequestFor('post', 'atualiza-componentes-serie')) {
            $this->appendResponse($this->atualizaComponentesDaSerie());
        } elseif ($this->isRequestFor('post', 'replica-componentes-adicionados-escolas')) {
            $this->appendResponse($this->atualizaEscolasSerieDisciplina());
        } elseif ($this->isRequestFor('post', 'exclui-componentes-serie')) {
            $this->appendResponse($this->excluiComponentesSerie());
        } elseif ($this->isRequestFor('get', 'existe-dispensa')) {
            $this->appendResponse($this->existeDispensa());
        } elseif ($this->isRequestFor('get', 'existe-dependencia')) {
            $this->appendResponse($this->existeDependencia());
        } elseif ($this->isRequestFor('get', 'get-escolas-by-serie')) {
            $this->appendResponse($this->getEscolasBySerie($this->getRequest()->serie));
        } elseif ($this->isRequestFor('post', 'atualiza-componentes-escolas')) {
            $this->appendResponse($this->atualizaComponentesEscolas());
        } else {
            $this->notImplementedOperationError();
        }
    }

    private function handleComponentesArray($componentes)
    {
        $arrayComponentes = [];

        foreach ($componentes as $key => $componente) {
            $arrayComponentes[$key]['id'] = $componente->id;
            $arrayComponentes[$key]['carga_horaria'] = $componente->carga_horaria;
            $arrayComponentes[$key]['anos_letivos'] = $componente->anos_letivos;
        }

        return $arrayComponentes;
    }
}
