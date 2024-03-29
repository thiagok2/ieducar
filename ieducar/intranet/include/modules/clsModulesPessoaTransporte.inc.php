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
 * clsModulesPessoaTransporte class.
 * 
 * @author    Lucas Schmoeller da Silva <lucas@portabilis.com.br>
 * @category  i-Educar
 * @license   @@license@@
 * @package   Module
 * @since     07/2013
 * @version   @@package_version@@
 */
class clsModulesPessoaTransporte
{
  var $cod_pessoa_transporte;
  var $ref_idpes;
  var $ref_cod_rota_transporte_escolar;
  var $ref_cod_ponto_transporte_escolar;
  var $ref_idpes_destino;
  var $observacao;
  var $turno;
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
  function __construct( $cod_pessoa_transporte = NULL, $ref_cod_rota_transporte_escolar = NULL,
  $ref_idpes = null ,$ref_cod_ponto_transporte_escolar = NULL, $ref_idpes_destino = NULL,
   $observacao = NULL, $turno = NULL)
  {
    $db = new clsBanco();
    $this->_schema = "modules.";
    $this->_tabela = "{$this->_schema}pessoa_transporte";

    $this->pessoa_logada = Session::get('id_pessoa');

    $this->_campos_lista = $this->_todos_campos = "cod_pessoa_transporte, ref_cod_rota_transporte_escolar,
                                                  ref_idpes, ref_cod_ponto_transporte_escolar, ref_idpes_destino, observacao, turno"; 

    if (is_numeric($cod_pessoa_transporte)) {
      $this->cod_pessoa_transporte = $cod_pessoa_transporte;
    }

    if (is_numeric($ref_cod_rota_transporte_escolar)) {
      $this->ref_idpes = $ref_idpes;
    }

    if (is_numeric($ref_idpes)) {
      $this->ref_idpes = $ref_idpes;
    }

    if (is_numeric($ref_cod_ponto_transporte_escolar)) {
      $this->ref_cod_ponto_transporte_escolar = $ref_cod_ponto_transporte_escolar;
    }

    if (is_numeric($ref_idpes_destino)) {
      $this->ref_idpes_destino = $ref_idpes_destino;
    }

    if (is_string($observacao)) {
      $this->observacao = $observacao;
    }

    if (is_numeric($turno)) {
      $this->turno = $turno;
    }

  }

  /**
   * Cria um novo registro.
   * @return bool
   */
  function cadastra()
  {

    if (is_numeric($this->ref_idpes) && is_numeric($this->ref_cod_rota_transporte_escolar))
    {

      $db = new clsBanco();
      $campos  = '';
      $valores = '';
      $gruda   = '';

    if (is_numeric($this->cod_pessoa_transporte)) {
        $campos .= "{$gruda}cod_pessoa_transporte";
        $valores .= "{$gruda}'{$this->cod_pessoa_transporte}'";
        $gruda = ", ";
    }

    if (is_numeric($this->ref_cod_rota_transporte_escolar)) {
        $campos .= "{$gruda}ref_cod_rota_transporte_escolar";
        $valores .= "{$gruda}'{$this->ref_cod_rota_transporte_escolar}'";
        $gruda = ", ";
    }

    if (is_numeric($this->ref_idpes)) {
        $campos .= "{$gruda}ref_idpes";
        $valores .= "{$gruda}'{$this->ref_idpes}'";
        $gruda = ", ";
    }

    if (is_numeric($this->ref_cod_ponto_transporte_escolar)) {
        $campos .= "{$gruda}ref_cod_ponto_transporte_escolar";
        $valores .= "{$gruda}'{$this->ref_cod_ponto_transporte_escolar}'";
        $gruda = ", ";
    }

    if (is_numeric($this->ref_idpes_destino)) {
        $campos .= "{$gruda}ref_idpes_destino";
        $valores .= "{$gruda}'{$this->ref_idpes_destino}'";
        $gruda = ", ";
    }

    if (is_string($this->observacao)) {
        $campos .= "{$gruda}observacao";
        $valores .= "{$gruda}'{$this->observacao}'";
        $gruda = ", ";
    }

    if (is_numeric($this->turno)) {
        $campos .= "{$gruda}turno";
        $valores .= "{$gruda}'{$this->turno}'";
        $gruda = ", ";
    }

    $db->Consulta("INSERT INTO {$this->_tabela} ( $campos ) VALUES( $valores )");

    $this->cod_pessoa_transporte = $db->InsertId("{$this->_tabela}_seq");

    if($this->cod_pessoa_transporte){
      $detalhe = $this->detalhe();
      $auditoria = new clsModulesAuditoriaGeral("pessoa_transporte", $this->pessoa_logada, $this->cod_pessoa_transporte);
      $auditoria->inclusao($detalhe);
    }
    return $this->cod_pessoa_transporte;
    }

    return FALSE;
  }

  /**
   * Edita os dados de um registro.
   * @return bool
   */
  function edita()
  {

    if (is_numeric($this->cod_pessoa_transporte)) {

    $db  = new clsBanco();
    $set = '';
    
    if (is_numeric($this->ref_cod_rota_transporte_escolar)) {
        $set .= "{$gruda}ref_cod_rota_transporte_escolar = '{$this->ref_cod_rota_transporte_escolar}'";
        $gruda = ", ";
    }

    if (is_numeric($this->ref_idpes)) {
        $set .= "{$gruda}ref_idpes = '{$this->ref_idpes}'";
        $gruda = ", ";
    }

    if (is_numeric($this->ref_cod_ponto_transporte_escolar)) {
        $set .= "{$gruda}ref_cod_ponto_transporte_escolar = '{$this->ref_cod_ponto_transporte_escolar}'";
        $gruda = ", ";
    }else{
        $set .= "{$gruda}ref_cod_ponto_transporte_escolar = null";
        $gruda = ", ";
    }

    if (is_numeric($this->ref_idpes_destino)) {
        $set .= "{$gruda}ref_idpes_destino = '{$this->ref_idpes_destino}'";
        $gruda = ", ";
    }else{
        $set .= "{$gruda}ref_idpes_destino = null";
        $gruda = ", ";
    }

    if (is_string($this->observacao)) {
        $set .= "{$gruda}observacao = '{$this->observacao}'";
        $gruda = ", ";
    }

    if (is_numeric($this->turno)) {
        $set .= "{$gruda}turno = '{$this->turno}'";
        $gruda = ", ";
    }

     if ($set) {
        $detalheAntigo = $this->detalhe();
        $db->Consulta("UPDATE {$this->_tabela} SET $set WHERE cod_pessoa_transporte = '{$this->cod_pessoa_transporte}'");
        $auditoria = new clsModulesAuditoriaGeral("pessoa_transporte", $this->pessoa_logada,$this->cod_pessoa_transporte);
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
  function lista($cod_pessoa_transporte = NULL,
                 $ref_idpes = NULL,
                 $ref_cod_rota_transporte_escolar = NULL,
                 $ref_cod_ponto_transporte_escolar = NULL,
                 $ref_idpes_destino = NULL,
                 $nome_pessoa = NULL,
                 $nome_destino = NULL,
                 $ano_rota = NULL)
  {
    
    $sql = "SELECT pt.cod_pessoa_transporte,
                   pt.ref_cod_rota_transporte_escolar,
                   pt.ref_idpes,
                   pt.ref_cod_ponto_transporte_escolar,
                   pt.ref_idpes_destino,
                   pt.observacao,
                   pt.turno,
                   pd.nome AS nome_destino,
                   p.nome AS nome_pessoa,
                   rte.descricao AS nome_rota,
                   pte.descricao AS nome_ponto,
                   pd2.nome AS nome_destino2";

    $sqlConditions = "
      FROM {$this->_tabela} pt
      LEFT JOIN cadastro.pessoa pd
        ON (pd.idpes = pt.ref_idpes_destino)
      LEFT JOIN cadastro.pessoa p
        ON (p.idpes = pt.ref_idpes)
      LEFT JOIN modules.rota_transporte_escolar rte
        ON (rte.cod_rota_transporte_escolar = pt.ref_cod_rota_transporte_escolar)
      LEFT JOIN modules.ponto_transporte_escolar pte
        ON (pte.cod_ponto_transporte_escolar = pt.ref_cod_ponto_transporte_escolar)
      LEFT JOIN cadastro.pessoa pd2
        ON (
          pd2.idpes = rte.ref_idpes_destino AND
          pt.ref_cod_rota_transporte_escolar = rte.cod_rota_transporte_escolar    
        )
    ";

    $sql .= $sqlConditions;

    $filtros = "";

    $whereAnd = " WHERE ";
    $whereNomes = '';
    if (is_numeric($cod_pessoa_transporte)) {
      $filtros .= "{$whereAnd} cod_pessoa_transporte = '{$cod_pessoa_transporte}'";
      $whereAnd = " AND ";
    }


    if (is_numeric($ref_idpes)) {
      $filtros .= "{$whereAnd} ref_idpes = '{$ref_idpes}'";
      $whereAnd = " AND ";
    }

    if (is_numeric($ref_cod_rota_transporte_escolar)) {
      $filtros .= "{$whereAnd} ref_cod_rota_transporte_escolar = '{$ref_cod_rota_transporte_escolar}'";
      $whereAnd = " AND ";
    }

    if (is_numeric($ref_cod_ponto_transporte_escolar)) {
      $filtros .= "{$whereAnd} ref_cod_ponto_transporte_escolar = '{$ref_cod_ponto_transporte_escolar}'";
      $whereAnd = " AND ";
    }

    if (is_numeric($ref_idpes_destino)) {
      $filtros .= "{$whereAnd} ref_idpes_destino = '{$ref_idpes_destino}'";
      $whereAnd = " AND ";
    }

    if (is_string($nome_pessoa)) {
        $filtros .= "
        {$whereAnd} translate(upper(p.nome),'ÅÁÀÃÂÄÉÈÊËÍÌÎÏÓÒÕÔÖÚÙÛÜÇÝÑ','AAAAAAEEEEIIIIOOOOOUUUUCYN') LIKE translate(upper('%{$nome_pessoa}%'),'ÅÁÀÃÂÄÉÈÊËÍÌÎÏÓÒÕÔÖÚÙÛÜÇÝÑ','AAAAAAEEEEIIIIOOOOOUUUUCYN')";
      $whereAnd = " AND ";
    }

    if (is_string($nome_destino)) {
        $filtros .= "
        {$whereAnd} (translate(upper(pd.nome),'ÅÁÀÃÂÄÉÈÊËÍÌÎÏÓÒÕÔÖÚÙÛÜÇÝÑ','AAAAAAEEEEIIIIOOOOOUUUUCYN') LIKE translate(upper('%{$nome_destino}%'),'ÅÁÀÃÂÄÉÈÊËÍÌÎÏÓÒÕÔÖÚÙÛÜÇÝÑ','AAAAAAEEEEIIIIOOOOOUUUUCYN')) OR (translate(upper(pd2.nome),'ÅÁÀÃÂÄÉÈÊËÍÌÎÏÓÒÕÔÖÚÙÛÜÇÝÑ','AAAAAAEEEEIIIIOOOOOUUUUCYN') LIKE translate(upper('%{$nome_destino}%'),'ÅÁÀÃÂÄÉÈÊËÍÌÎÏÓÒÕÔÖÚÙÛÜÇÝÑ','AAAAAAEEEEIIIIOOOOOUUUUCYN')) ";
      $whereAnd = " AND ";
    }

    if (is_numeric($ano_rota)) {
      $filtros .= "{$whereAnd} rte.ano = '{$ano_rota}'";
      $whereAnd = " AND ";
    }

    $db = new clsBanco();
    $countCampos = count(explode(',', $this->_campos_lista))+2;
    $resultado = array();

    $sql .= $filtros . $whereNomes. $this->getOrderby() . $this->getLimite();
    
    $sqlCount = "
      SELECT COUNT(0) {$sqlConditions} {$filtros} {$whereNomes}
    ";

    $this->_total = $db->CampoUnico($sqlCount);
    /*$this->_total = $db->CampoUnico("SELECT COUNT(0)
                                       FROM {$this->_tabela} pt
                                       LEFT JOIN cadastro.pessoa pd ON (pd.idpes = pt.ref_idpes_destino)
                                       LEFT JOIN cadastro.pessoa p ON (p.idpes = pt.ref_idpes)
                                       LEFT JOIN modules.rota_transporte_escolar rte ON (rte.cod_rota_transporte_escolar = pt.ref_cod_rota_transporte_escolar)
                                       LEFT JOIN modules.ponto_transporte_escolar pte ON (pte.cod_ponto_transporte_escolar = pt.ref_cod_ponto_transporte_escolar)
                                       LEFT JOIN cadastro.pessoa pd2 ON (pd2.idpes = rte.ref_idpes_destino
                                                                     AND pt.ref_cod_rota_transporte_escolar = rte.cod_rota_transporte_escolar) {$filtros}");*/

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

    if (is_numeric($this->cod_pessoa_transporte) || is_numeric($this->ref_idpes)) {

      $db = new clsBanco();
      $sql = "SELECT {$this->_todos_campos}, (
          SELECT
            nome
          FROM
            cadastro.pessoa
          WHERE
            idpes = ref_idpes_destino
         ) AS nome_destino, (
          SELECT
            nome
          FROM
            cadastro.pessoa
          WHERE
            idpes = ref_idpes
         ) AS nome_pessoa, (
          SELECT
            descricao
          FROM
            modules.rota_transporte_escolar
          WHERE
            ref_cod_rota_transporte_escolar = cod_rota_transporte_escolar
         ) AS nome_rota, (
          SELECT
            descricao 
          FROM
            modules.ponto_transporte_escolar
          WHERE
            ref_cod_ponto_transporte_escolar = cod_ponto_transporte_escolar
         ) AS nome_ponto, (
          SELECT
            nome
          FROM
            cadastro.pessoa p, modules.rota_transporte_escolar rt
          WHERE
            p.idpes = rt.ref_idpes_destino and ref_cod_rota_transporte_escolar = rt.cod_rota_transporte_escolar
         ) AS nome_destino2 FROM {$this->_tabela} WHERE ";
      
      if(is_numeric($this->cod_pessoa_transporte))
        $sql .= " cod_pessoa_transporte = '{$this->cod_pessoa_transporte}'";
      else
        $sql .= " ref_idpes = '{$this->ref_idpes}' ORDER BY cod_pessoa_transporte DESC LIMIT 1 ";

      $db->Consulta($sql);
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
    if (is_numeric($this->cod_pessoa_transporte)) {
      $db = new clsBanco();
      $db->Consulta("SELECT 1 FROM {$this->_tabela} WHERE cod_pessoa_transporte = '{$this->cod_pessoa_transporte}'");
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
    if (is_numeric($this->cod_pessoa_transporte)) {

      $detalhe = $this->detalhe();

      $sql = "DELETE FROM {$this->_tabela} WHERE cod_pessoa_transporte = '{$this->cod_pessoa_transporte}'";
      $db = new clsBanco();
      $db->Consulta($sql);

      $auditoria = new clsModulesAuditoriaGeral("pessoa_transporte", $this->pessoa_logada, $this->cod_pessoa_transporte);
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
