<?php

require_once('include/pmieducar/geral.inc.php');

class clsPmieducarHistoricoDisciplinas
{
    public $sequencial;

    public $ref_ref_cod_aluno;

    public $ref_sequencial;

    public $nm_disciplina;

    public $nota;

    public $faltas;

    public $ordenamento;

    public $carga_horaria_disciplina;

    public $dependencia;

    public $tipo_base;

    /**
     * Armazena o total de resultados obtidos na ultima chamada ao metodo lista
     *
     * @var int
     */
    public $_total;

    /**
     * Nome do schema
     *
     * @var string
     */
    public $_schema;

    /**
     * Nome da tabela
     *
     * @var string
     */
    public $_tabela;

    /**
     * Lista separada por virgula, com os campos que devem ser selecionados na proxima chamado ao metodo lista
     *
     * @var string
     */
    public $_campos_lista;

    /**
     * Lista com todos os campos da tabela separados por virgula, padrao para selecao no metodo lista
     *
     * @var string
     */
    public $_todos_campos;

    /**
     * Valor que define a quantidade de registros a ser retornada pelo metodo lista
     *
     * @var int
     */
    public $_limite_quantidade;

    /**
     * Define o valor de offset no retorno dos registros no metodo lista
     *
     * @var int
     */
    public $_limite_offset;

    /**
     * Define o campo padrao para ser usado como padrao de ordenacao no metodo lista
     *
     * @var string
     */
    public $_campo_order_by;

    /**
     * Construtor (PHP 4)
     *
     * @return object
     */
    public function __construct(
        $sequencial = null,
        $ref_ref_cod_aluno = null,
        $ref_sequencial = null,
        $nm_disciplina = null,
        $nota = null,
        $faltas = null,
        $ordenamento = null,
        $carga_horaria_disciplina = null,
        $dependencia = false,
        $tipo_base = ComponenteCurricular_Model_TipoBase::DEFAULT
    ) {
        $db = new clsBanco();

        $this->_schema = 'pmieducar.';
        $this->_tabela = "{$this->_schema}historico_disciplinas";
        $this->_campos_lista = $this->_todos_campos = implode(', ', [
            'sequencial',
            'ref_ref_cod_aluno',
            'ref_sequencial',
            'nm_disciplina',
            'nota',
            'faltas',
            'ordenamento',
            'carga_horaria_disciplina',
            'dependencia',
            'tipo_base',
        ]);

        if (is_numeric($ref_ref_cod_aluno) && is_numeric($ref_sequencial)) {
            if (class_exists('clsPmieducarHistoricoEscolar')) {
                $tmp_obj = new clsPmieducarHistoricoEscolar($ref_ref_cod_aluno, $ref_sequencial);
                if (method_exists($tmp_obj, 'existe')) {
                    if ($tmp_obj->existe()) {
                        $this->ref_ref_cod_aluno = $ref_ref_cod_aluno;
                        $this->ref_sequencial = $ref_sequencial;
                    }
                } elseif (method_exists($tmp_obj, 'detalhe')) {
                    if ($tmp_obj->detalhe()) {
                        $this->ref_ref_cod_aluno = $ref_ref_cod_aluno;
                        $this->ref_sequencial = $ref_sequencial;
                    }
                }
            } else {
                if ($db->CampoUnico("SELECT 1 FROM pmieducar.historico_escolar WHERE ref_cod_aluno = '{$ref_ref_cod_aluno}' AND sequencial = '{$ref_sequencial}'")) {
                    $this->ref_ref_cod_aluno = $ref_ref_cod_aluno;
                    $this->ref_sequencial = $ref_sequencial;
                }
            }
        }

        if (is_numeric($sequencial)) {
            $this->sequencial = $sequencial;
        }

        if (is_string($nm_disciplina)) {
            $this->nm_disciplina = $nm_disciplina;
        }

        if (is_string($nota)) {
            $this->nota = $nota;
        }

        if (is_numeric($faltas)) {
            $this->faltas = $faltas;
        }

        if (is_numeric($ordenamento)) {
            $this->ordenamento = $ordenamento;
        }

        if (is_numeric($carga_horaria_disciplina)) {
            $this->carga_horaria_disciplina = $carga_horaria_disciplina;
        }

        if (is_bool($dependencia)) {
            $this->dependencia = $dependencia;
        }

        if (is_numeric($tipo_base)) {
            $this->tipo_base = $tipo_base;
        }
    }

    /**
     * Cria um novo registro
     *
     * @return bool
     *
     * @throws Exception
     */
    public function cadastra()
    {
        if (is_numeric($this->ref_ref_cod_aluno) && is_numeric($this->ref_sequencial) && is_string($this->nm_disciplina) && is_string($this->nota)) {
            $db = new clsBanco();

            $campos = '';
            $valores = '';
            $gruda = '';

            if (is_numeric($this->ref_ref_cod_aluno)) {
                $campos .= "{$gruda}ref_ref_cod_aluno";
                $valores .= "{$gruda}'{$this->ref_ref_cod_aluno}'";
                $gruda = ', ';
            }

            if (is_numeric($this->ref_sequencial)) {
                $campos .= "{$gruda}ref_sequencial";
                $valores .= "{$gruda}'{$this->ref_sequencial}'";
                $gruda = ', ';
            }

            if (is_string($this->nm_disciplina)) {
                $campos .= "{$gruda}nm_disciplina";
                $valores .= "{$gruda}'{$this->nm_disciplina}'";
                $gruda = ', ';
            }

            if (is_string($this->nota)) {
                $campos .= "{$gruda}nota";
                $valores .= "{$gruda}'{$this->nota}'";
                $gruda = ', ';
            }

            if (is_numeric($this->faltas)) {
                $campos .= "{$gruda}faltas";
                $valores .= "{$gruda}'{$this->faltas}'";
                $gruda = ', ';
            }

            if (is_numeric($this->ordenamento)) {
                $campos .= "{$gruda}ordenamento";
                $valores .= "{$gruda}'{$this->ordenamento}'";
                $gruda = ', ';
            }

            if (is_numeric($this->carga_horaria_disciplina)) {
                $campos .= "{$gruda}carga_horaria_disciplina";
                $valores .= "{$gruda}'{$this->carga_horaria_disciplina}'";
                $gruda = ', ';
            }

            if ($this->dependencia) {
                $campos .= "{$gruda}dependencia";
                $valores .= "{$gruda}true";
                $gruda = ', ';
            }

            if ($this->tipo_base) {
                $campos .= "{$gruda}tipo_base";
                $valores .= "{$gruda}{$this->tipo_base}";
                $gruda = ', ';
            }

            $sequencial = $db->campoUnico("SELECT COALESCE( MAX(sequencial), 0 ) + 1 FROM {$this->_tabela} WHERE ref_ref_cod_aluno = {$this->ref_ref_cod_aluno} AND ref_sequencial = {$this->ref_sequencial}");

            $db->Consulta("INSERT INTO {$this->_tabela} ( sequencial, $campos ) VALUES( $sequencial, $valores )");

            return true;
        }

        return false;
    }

    /**
     * Edita os dados de um registro
     *
     * @return bool
     *
     * @throws Exception
     */
    public function edita()
    {
        if (is_numeric($this->sequencial) && is_numeric($this->ref_ref_cod_aluno) && is_numeric($this->ref_sequencial)) {
            $db = new clsBanco();
            $set = '';
            $gruda = '';

            if (is_string($this->nm_disciplina)) {
                $set .= "{$gruda}nm_disciplina = '{$this->nm_disciplina}'";
                $gruda = ', ';
            }

            if (is_string($this->nota)) {
                $set .= "{$gruda}nota = '{$this->nota}'";
                $gruda = ', ';
            }

            if (is_numeric($this->faltas)) {
                $set .= "{$gruda}faltas = '{$this->faltas}'";
                $gruda = ', ';
            }

            if (is_numeric($this->ordenamento)) {
                $set .= "{$gruda}ordenamento = '{$this->ordenamento}'";
                $gruda = ', ';
            }

            if (is_numeric($this->carga_horaria_disciplina)) {
                $set .= "{$gruda}carga_horaria_disciplina = '{$this->carga_horaria_disciplina}'";
                $gruda = ', ';
            }

            if ($this->dependencia) {
                $set .= "{$gruda}dependencia = TRUE";
                $gruda = ', ';
            }

            if ($this->dependencia) {
                $set .= "{$gruda}tipo_base = {$this->tipo_base}";
                $gruda = ', ';
            }

            if ($set) {
                $db->Consulta("UPDATE {$this->_tabela} SET $set WHERE sequencial = '{$this->sequencial}' AND ref_ref_cod_aluno = '{$this->ref_ref_cod_aluno}' AND ref_sequencial = '{$this->ref_sequencial}'");

                return true;
            }
        }

        return false;
    }

    /**
     * Retorna uma lista filtrados de acordo com os parametros
     *
     * @return array
     *
     * @throws Exception
     */
    public function lista($int_sequencial = null, $int_ref_ref_cod_aluno = null, $int_ref_sequencial = null, $str_nm_disciplina = null, $str_nota = null, $int_faltas = null, $int_ordenamento = null, $int_carga_horaria_disciplina = null, $bool_dependencia = false)
    {
        $sql = "SELECT {$this->_campos_lista} FROM {$this->_tabela}";
        $filtros = '';

        $whereAnd = ' WHERE ';

        if (is_numeric($int_sequencial)) {
            $filtros .= "{$whereAnd} sequencial = '{$int_sequencial}'";
            $whereAnd = ' AND ';
        }

        if (is_numeric($int_ref_ref_cod_aluno)) {
            $filtros .= "{$whereAnd} ref_ref_cod_aluno = '{$int_ref_ref_cod_aluno}'";
            $whereAnd = ' AND ';
        }

        if (is_numeric($int_ref_sequencial)) {
            $filtros .= "{$whereAnd} ref_sequencial = '{$int_ref_sequencial}'";
            $whereAnd = ' AND ';
        }

        if (is_string($str_nm_disciplina)) {
            $filtros .= "{$whereAnd} nm_disciplina LIKE '%{$str_nm_disciplina}%'";
            $whereAnd = ' AND ';
        }

        if (is_string($str_nota)) {
            $filtros .= "{$whereAnd} nota LIKE '%{$str_nota}%'";
            $whereAnd = ' AND ';
        }

        if (is_numeric($int_faltas)) {
            $filtros .= "{$whereAnd} faltas = '{$int_faltas}'";
            $whereAnd = ' AND ';
        }

        if (is_numeric($int_ordenamento)) {
            $filtros .= "{$whereAnd} ordenamento = '{$int_ordenamento}'";
            $whereAnd = ' AND ';
        }

        if (is_numeric($int_carga_horaria_disciplina)) {
            $filtros .= "{$whereAnd} carga_horaria_disciplina = '{$int_carga_horaria_disciplina}'";
            $whereAnd = ' AND ';
        }

        if ($bool_dependencia) {
            $filtros .= "{$whereAnd} dependencia = TRUE";
            $whereAnd = ' AND ';
        }

        $db = new clsBanco();
        $countCampos = count(explode(',', $this->_campos_lista));
        $resultado = [];

        $sql .= $filtros . $this->getOrderby() . $this->getLimite();

        $this->_total = $db->CampoUnico("SELECT COUNT(0) FROM {$this->_tabela} {$filtros}");

        $db->Consulta($sql);

        if ($countCampos > 1) {
            while ($db->ProximoRegistro()) {
                $tupla = $db->Tupla();

                $tupla['_total'] = $this->_total;
                $resultado[] = $tupla;
            }
        } else {
            while ($db->ProximoRegistro()) {
                $tupla = $db->Tupla();
                $resultado[] = $tupla[$this->_campos_lista];
            }
        }

        if (count($resultado)) {
            return $resultado;
        }

        return false;
    }

    /**
     * Retorna um array com os dados de um registro
     *
     * @return array
     *
     * @throws Exception
     */
    public function detalhe()
    {
        if (is_numeric($this->sequencial) && is_numeric($this->ref_ref_cod_aluno) && is_numeric($this->ref_sequencial)) {
            $db = new clsBanco();
            $db->Consulta("SELECT {$this->_todos_campos} FROM {$this->_tabela} WHERE sequencial = '{$this->sequencial}' AND ref_ref_cod_aluno = '{$this->ref_ref_cod_aluno}' AND ref_sequencial = '{$this->ref_sequencial}'");
            $db->ProximoRegistro();

            return $db->Tupla();
        }

        return false;
    }

    /**
     * Retorna um array com os dados de um registro
     *
     * @return array
     *
     * @throws Exception
     */
    public function existe()
    {
        if (is_numeric($this->sequencial) && is_numeric($this->ref_ref_cod_aluno) && is_numeric($this->ref_sequencial)) {
            $db = new clsBanco();
            $db->Consulta("SELECT 1 FROM {$this->_tabela} WHERE sequencial = '{$this->sequencial}' AND ref_ref_cod_aluno = '{$this->ref_ref_cod_aluno}' AND ref_sequencial = '{$this->ref_sequencial}'");
            $db->ProximoRegistro();

            return $db->Tupla();
        }

        return false;
    }

    /**
     * Exclui um registro
     *
     * @return bool
     */
    public function excluir()
    {
        return false;
    }

    /**
     * Exclui todos os registros referentes a um historico do aluno
     */
    public function excluirTodos($ref_cod_aluno, $ref_sequencial)
    {
        if (is_numeric($ref_cod_aluno) && is_numeric($ref_sequencial)) {
            $db = new clsBanco();
            $db->Consulta("DELETE FROM {$this->_tabela} WHERE ref_ref_cod_aluno = '{$ref_cod_aluno}' AND ref_sequencial = '{$ref_sequencial}'");

            return true;
        }

        return false;
    }

    /**
     * Define quais campos da tabela serao selecionados na invocacao do metodo lista
     *
     * @return null
     */
    public function setCamposLista($str_campos)
    {
        $this->_campos_lista = $str_campos;
    }

    /**
     * Define que o metodo Lista devera retornoar todos os campos da tabela
     *
     * @return null
     */
    public function resetCamposLista()
    {
        $this->_campos_lista = $this->_todos_campos;
    }

    /**
     * Define limites de retorno para o metodo lista
     *
     * @return null
     */
    public function setLimite($intLimiteQtd, $intLimiteOffset = null)
    {
        $this->_limite_quantidade = $intLimiteQtd;
        $this->_limite_offset = $intLimiteOffset;
    }

    /**
     * Retorna a string com o trecho da query resposavel pelo Limite de registros
     *
     * @return string
     */
    public function getLimite()
    {
        if (is_numeric($this->_limite_quantidade)) {
            $retorno = " LIMIT {$this->_limite_quantidade}";
            if (is_numeric($this->_limite_offset)) {
                $retorno .= " OFFSET {$this->_limite_offset} ";
            }

            return $retorno;
        }

        return '';
    }

    /**
     * Define campo para ser utilizado como ordenacao no metolo lista
     *
     * @return null
     */
    public function setOrderby($strNomeCampo)
    {
        if (is_string($strNomeCampo) && $strNomeCampo) {
            $this->_campo_order_by = $strNomeCampo;
        }
    }

    /**
     * Retorna a string com o trecho da query resposavel pela ordenacao dos registros
     *
     * @return string
     */
    public function getOrderby()
    {
        if (is_string($this->_campo_order_by)) {
            return " ORDER BY {$this->_campo_order_by} ";
        }

        return '';
    }

    public function getMaxSequencial($ref_cod_aluno)
    {
        if (is_numeric($ref_cod_aluno)) {
            $db = new clsBanco();
            $sequencial = $db->campoUnico("SELECT COALESCE( MAX(sequencial), 0 ) FROM {$this->_tabela} WHERE ref_cod_aluno = {$ref_cod_aluno}");

            return $sequencial;
        }

        return false;
    }
}
