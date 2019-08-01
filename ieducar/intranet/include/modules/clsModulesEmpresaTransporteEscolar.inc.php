<?php

/**
 * i-Educar - Sistema de gestão escolar
 *
 * Copyright (C) 2006  Prefeitura Municipal de Itajaí
 *                     <ctima@itajai.sc.gov.br>
 *
 * Este programa é software livre; você pode redistribuí-lo e/ou modificá-lo
 * sob os termos da Licença Pública Geral GNU conforme publicada pela Free
 * Software Foundation; tanto a versão 2 da Licença, como (a seu critério)
 * qualquer versão posterior.
 *
 * Este programa é distribuí­do na expectativa de que seja útil, porém, SEM
 * NENHUMA GARANTIA; nem mesmo a garantia implí­cita de COMERCIABILIDADE OU
 * ADEQUAÇÃO A UMA FINALIDADE ESPECÍFICA. Consulte a Licença Pública Geral
 * do GNU para mais detalhes.
 *
 * Você deve ter recebido uma cópia da Licença Pública Geral do GNU junto
 * com este programa; se não, escreva para a Free Software Foundation, Inc., no
 * endereço 59 Temple Street, Suite 330, Boston, MA 02111-1307 USA.
 *
 * @author    Lucas Schmoeller da Silva <lucas@portabilis.com.br>
 * @category  i-Educar
 * @license   @@license@@
 * @package   Module
 * @since     07/2013
 * @version   $Id$
 */

use Illuminate\Support\Facades\Session;

require_once 'include/pmieducar/geral.inc.php';
require_once 'include/modules/clsModulesAuditoriaGeral.inc.php';

/**
 * clsModulesEmpresaTransporteEscolar class.
 *
 * @author    Lucas Schmoeller da Silva <lucas@portabilis.com.br>
 * @category  i-Educar
 * @license   @@license@@
 * @package   Module
 * @since     07/2013
 * @version   @@package_version@@
 */
class clsModulesEmpresaTransporteEscolar
{
  var $cod_empresa_transporte_escolar;
  var $ref_idpes;
  var $ref_resp_idpes;
  var $observacao;
  var $pessoa_logada;

  /**
   * Armazena o total de resultados obtidos na última chamada ao método lista().
   * @var int
   */
  var $_total;

  /**
   * Nome do schema.
   * @var string
   */
  var $_schema;

  /**
   * Nome da tabela.
   * @var string
   */
  var $_tabela;

  /**
   * Lista separada por vírgula, com os campos que devem ser selecionados na
   * próxima chamado ao método lista().
   * @var string
   */
  var $_campos_lista;

  /**
   * Lista com todos os campos da tabela separados por vírgula, padrão para
   * seleção no método lista.
   * @var string
   */
  var $_todos_campos;

  /**
   * Valor que define a quantidade de registros a ser retornada pelo método lista().
   * @var int
   */
  var $_limite_quantidade;

  /**
   * Define o valor de offset no retorno dos registros no método lista().
   * @var int
   */
  var $_limite_offset;

  /**
   * Define o campo para ser usado como padrão de ordenação no método lista().
   * @var string
   */
  var $_campo_order_by;

  /**
   * Construtor.
   */
  function __construct($cod_empresa_transporte_escolar = NULL, 
                                              $ref_idpes = NULL, $ref_resp_idpes = NULL, 
                                              $observacao = NULL)
  {
    $db = new clsBanco();
    $this->_schema = "modules.";
    $this->_tabela = "{$this->_schema}empresa_transporte_escolar";

    $this->pessoa_logada = Session::get('id_pessoa');

    $this->_campos_lista = $this->_todos_campos = " cod_empresa_transporte_escolar, ref_idpes, ref_resp_idpes, observacao ";

    

    if (is_numeric($cod_empresa_transporte_escolar)) {
      $this->cod_empresa_transporte_escolar = $cod_empresa_transporte_escolar;
    }

    if (is_numeric($ref_idpes)) {
      $this->ref_idpes = $ref_idpes;
    }

    if (is_numeric($ref_resp_idpes)) {
      $this->ref_resp_idpes = $ref_resp_idpes;
    }

    if (is_string($observacao)) {
      $this->observacao = $observacao;
    }

  }

  /**
   * Cria um novo registro.
   * @return bool
   */
  function cadastra()
  {
    if (is_numeric($this->ref_idpes) && is_numeric($this->ref_resp_idpes))
    {
      $db = new clsBanco();

      $campos  = '';
      $valores = '';
      $gruda   = '';

      if (is_numeric($this->ref_idpes)) {
        $campos .= "{$gruda}ref_idpes";
        $valores .= "{$gruda}'{$this->ref_idpes}'";
        $gruda = ", ";
      }

      if (is_numeric($this->ref_resp_idpes)) {
        $campos .= "{$gruda}ref_resp_idpes";
        $valores .= "{$gruda}'{$this->ref_resp_idpes}'";
        $gruda = ", ";
      }

      if (is_string($this->observacao)) {
        $campos .= "{$gruda}observacao";
        $valores .= "{$gruda}'{$this->observacao}'";
        $gruda = ", ";
      }

      $db->Consulta("INSERT INTO {$this->_tabela} ( $campos ) VALUES( $valores )");

      $this->cod_empresa_transporte_escolar = $db->InsertId("{$this->_tabela}_seq");

      if($this->cod_empresa_transporte_escolar){
        $detalhe = $this->detalhe();
        $auditoria = new clsModulesAuditoriaGeral("empresa_transporte_escolar", $this->pessoa_logada, $this->cod_empresa_transporte_escolar);
        $auditoria->inclusao($detalhe);
      }
      return $this->cod_empresa_transporte_escolar;
    }

    return FALSE;
  }

  /**
   * Edita os dados de um registro.
   * @return bool
   */
  function edita()
  {
    if (is_numeric($this->cod_empresa_transporte_escolar)) {
      $db  = new clsBanco();
      $set = '';

      if (is_numeric($this->ref_idpes)) {
        $set .= "{$gruda}ref_idpes = '{$this->ref_idpes}'";
        $gruda = ", ";
      }

      if (is_numeric($this->ref_resp_idpes)) {
        $set .= "{$gruda}ref_resp_idpes = '{$this->ref_resp_idpes}'";
        $gruda = ", ";
      }

      if (is_string($this->observacao)) {
        $set .= "{$gruda}observacao = '{$this->observacao}'";
        $gruda = ", ";
      }
      if ($set) {
        $detalheAntigo = $this->detalhe();
        $db->Consulta("UPDATE {$this->_tabela} SET $set WHERE cod_empresa_transporte_escolar = '{$this->cod_empresa_transporte_escolar}'");
        $auditoria = new clsModulesAuditoriaGeral("empresa_transporte_escolar", $this->pessoa_logada,$this->cod_empresa_transporte_escolar);
        $auditoria->alteracao($detalheAntigo, $this->detalhe());
        return TRUE;
      }
    }

    return FALSE;
  }

  /**
   * Retorna uma lista de registros filtrados de acordo com os parâmetros.
   * @return array
   */
  function lista($cod_empresa_transporte_escolar = NULL, $ref_idpes = NULL,
    $ref_resp_idpes = NULL, $nm_idpes = NULL,
    $nm_resp_idpes = NULL)
  {
    $sql = "SELECT {$this->_campos_lista}, (
          SELECT
            nome
          FROM
            cadastro.pessoa
          WHERE
            idpes = ref_idpes
         ) AS nome_empresa , (SELECT nome FROM cadastro.pessoa WHERE idpes = ref_resp_idpes) AS nome_responsavel, (
          SELECT
            nome
          FROM
            cadastro.pessoa
          WHERE
            idpes = ref_idpes
         ) AS nome_empresa , (SELECT '(' || ddd || ')' || fone  FROM cadastro.fone_pessoa WHERE idpes = ref_idpes limit 1) AS telefone  FROM {$this->_tabela}";
    $filtros = "";

    $whereAnd = " WHERE ";

    if (is_numeric($cod_empresa_transporte_escolar)) {
      $filtros .= "{$whereAnd} cod_empresa_transporte_escolar = '{$cod_empresa_transporte_escolar}'";
      $whereAnd = " AND ";
    }

    if (is_numeric($ref_idpes)) {
      $filtros .= "{$whereAnd} ref_idpes = '{$ref_idpes}'";
      $whereAnd = " AND ";
    }

    if (is_numeric($ref_resp_idpes)) {
      $filtros .= "{$whereAnd} ref_resp_idpes = '{$ref_resp_idpes}'";
      $whereAnd = " AND ";
    }

    if (is_string($nm_idpes)) {
      $filtros .= "
        {$whereAnd} exists (
          SELECT
            1
          FROM
            cadastro.pessoa
          WHERE
            cadastro.pessoa.idpes = ref_idpes
            AND translate(upper(nome),'ÅÁÀÃÂÄÉÈÊËÍÌÎÏÓÒÕÔÖÚÙÛÜÇÝÑ','AAAAAAEEEEIIIIOOOOOUUUUCYN') LIKE translate(upper('%{$nm_idpes}%'),'ÅÁÀÃÂÄÉÈÊËÍÌÎÏÓÒÕÔÖÚÙÛÜÇÝÑ','AAAAAAEEEEIIIIOOOOOUUUUCYN')
        )";

      $whereAnd = ' AND ';
    }  

    if (is_string($nm_resp_idpes)) {
      $filtros .= "
        {$whereAnd} exists (
          SELECT
            1
          FROM
            cadastro.pessoa
          WHERE
            cadastro.pessoa.idpes = ref_resp_idpes
            AND translate(upper(nome),'ÅÁÀÃÂÄÉÈÊËÍÌÎÏÓÒÕÔÖÚÙÛÜÇÝÑ','AAAAAAEEEEIIIIOOOOOUUUUCYN') LIKE translate(upper('%{$nm_resp_idpes}%'),'ÅÁÀÃÂÄÉÈÊËÍÌÎÏÓÒÕÔÖÚÙÛÜÇÝÑ','AAAAAAEEEEIIIIOOOOOUUUUCYN')
        )";

      $whereAnd = ' AND ';
    }  

    $db = new clsBanco();
    $countCampos = count(explode(',', $this->_campos_lista))+2;
    $resultado = array();

    $sql .= $filtros . $this->getOrderby() . $this->getLimite();

    $this->_total = $db->CampoUnico("SELECT COUNT(0) FROM {$this->_tabela} {$filtros}");

    $db->Consulta($sql);

    if ($countCampos > 1) {
      while ($db->ProximoRegistro()) {
        $tupla = $db->Tupla();
        $tupla["_total"] = $this->_total;
        $resultado[] = $tupla;
      }
    }
    else {
      while ($db->ProximoRegistro()) {
        $tupla = $db->Tupla();
        $resultado[] = $tupla[$this->_campos_lista];
      }
    }
    if (count($resultado)) {
      return $resultado;
    }

    return FALSE;
  }

  /**
   * Retorna um array com os dados de um registro.
   * @return array
   */
  function detalhe()
  {
    if (is_numeric($this->cod_empresa_transporte_escolar)) {
      $db = new clsBanco();
      $db->Consulta("SELECT {$this->_todos_campos}, (
          SELECT
            nome
          FROM
            cadastro.pessoa
          WHERE
            idpes = ref_idpes
         ) AS nome_empresa , (SELECT nome FROM cadastro.pessoa WHERE idpes = ref_resp_idpes) AS nome_responsavel FROM {$this->_tabela} WHERE cod_empresa_transporte_escolar = '{$this->cod_empresa_transporte_escolar}'");
      $db->ProximoRegistro();
      return $db->Tupla();
    }

    return FALSE;
  }

  /**
   * Retorna um array com os dados de um registro.
   * @return array
   */
  function existe()
  {
    if (is_numeric($this->cod_empresa_transporte_escolar)) {
      $db = new clsBanco();
      $db->Consulta("SELECT 1 FROM {$this->_tabela} WHERE cod_empresa_transporte_escolar = '{$this->cod_empresa_transporte_escolar}'");
      $db->ProximoRegistro();
      return $db->Tupla();
    }

    return FALSE;
  }

  /**
   * Exclui um registro.
   * @return bool
   */
  function excluir()
  {
    if (is_numeric($this->cod_empresa_transporte_escolar)) {
      $detalhe = $this->detalhe();

      $sql = "DELETE FROM {$this->_tabela} WHERE cod_empresa_transporte_escolar = '{$this->cod_empresa_transporte_escolar}'";
      $db = new clsBanco();
      $db->Consulta($sql);

      $auditoria = new clsModulesAuditoriaGeral("empresa_transporte_escolar", $this->pessoa_logada, $this->cod_empresa_transporte_escolar);
      $auditoria->exclusao($detalhe);

      return true;
    }

    return FALSE;
  }

  /**
   * Define quais campos da tabela serão selecionados no método Lista().
   */
  function setCamposLista($str_campos)
  {
    $this->_campos_lista = $str_campos;
  }

  /**
   * Define que o método Lista() deverpa retornar todos os campos da tabela.
   */
  function resetCamposLista()
  {
    $this->_campos_lista = $this->_todos_campos;
  }

  /**
   * Define limites de retorno para o método Lista().
   */
  function setLimite($intLimiteQtd, $intLimiteOffset = NULL)
  {
    $this->_limite_quantidade = $intLimiteQtd;
    $this->_limite_offset = $intLimiteOffset;
  }

  /**
   * Retorna a string com o trecho da query responsável pelo limite de
   * registros retornados/afetados.
   *
   * @return string
   */
  function getLimite()
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
   * Define o campo para ser utilizado como ordenação no método Lista().
   */
  function setOrderby($strNomeCampo)
  {
    if (is_string($strNomeCampo) && $strNomeCampo ) {
      $this->_campo_order_by = $strNomeCampo;
    }
  }

  /**
   * Retorna a string com o trecho da query responsável pela Ordenação dos
   * registros.
   *
   * @return string
   */
  function getOrderby()
  {
    if (is_string($this->_campo_order_by)) {
      return " ORDER BY {$this->_campo_order_by} ";
    }
    return '';
  }
}
