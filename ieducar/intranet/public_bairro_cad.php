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
 * @author    Prefeitura Municipal de Itajaí <ctima@itajai.sc.gov.br>
 * @category  i-Educar
 * @license   http://creativecommons.org/licenses/GPL/2.0/legalcode.pt  CC GNU GPL
 * @package   Ied_Public
 * @since     Arquivo disponível desde a versão 1.0.0
 * @version   $Id$
 */

require_once 'include/clsBase.inc.php';
require_once 'include/clsCadastro.inc.php';
require_once 'include/clsBanco.inc.php';
require_once 'include/public/geral.inc.php';
require_once 'include/public/clsPublicDistrito.inc.php';
require_once 'include/public/clsPublicSetorBai.inc.php';
require_once ("include/pmieducar/geral.inc.php");
require_once ("include/modules/clsModulesAuditoriaGeral.inc.php");

require_once 'App/Model/ZonaLocalizacao.php';

/**
 * clsIndexBase class.
 *
 * @author    Prefeitura Municipal de Itajaí <ctima@itajai.sc.gov.br>
 * @category  i-Educar
 * @license   @@license@@
 * @package   iEd_Public
 * @since     Classe disponível desde a versão 1.0.0
 * @version   @@package_version@@
 */
class clsIndexBase extends clsBase
{
  function Formular()
  {
    $this->SetTitulo($this->_instituicao . ' Bairro');
    $this->processoAp = 756;
    $this->addEstilo('localizacaoSistema');
  }
}

/**
 * indice class.
 *
 * @author    Prefeitura Municipal de Itajaí <ctima@itajai.sc.gov.br>
 * @category  i-Educar
 * @license   @@license@@
 * @package   iEd_Public
 * @since     Classe disponível desde a versão 1.0.0
 * @version   @@package_version@@
 */
class indice extends clsCadastro
{
  /**
   * Referência a usuário da sessão.
   * @var int
   */
  var $pessoa_logada;

  var $idmun;
  var $geom;
  var $idbai;
  var $nome;
  var $idpes_rev;
  var $data_rev;
  var $origem_gravacao;
  var $idpes_cad;
  var $data_cad;
  var $operacao;
  var $idsis_rev;
  var $idsis_cad;
  var $zona_localizacao;
  var $iddis;

  var $idpais;
  var $sigla_uf;

  function Inicializar()
  {
    $retorno = 'Novo';
    $this->idbai = $_GET['idbai'];

    if (is_numeric($this->idbai)) {
      $obj_bairro = new clsPublicBairro();
      $lst_bairro = $obj_bairro->lista(NULL, NULL, NULL, NULL, NULL, NULL, NULL,
        NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, $this->idbai);

      if ($lst_bairro) {
        $registro = $lst_bairro[0];
      }

      if ($registro) {
        foreach ($registro as $campo => $val) {
          $this->$campo = $val;
        }

        $retorno = 'Editar';
      }
    }

    $this->url_cancelar = ($retorno == 'Editar') ?
      'public_bairro_det.php?idbai=' . $registro['idbai'] :
      'public_bairro_lst.php';

    $this->nome_url_cancelar = 'Cancelar';

    $nomeMenu = $retorno == "Editar" ? $retorno : "Cadastrar";
    $localizacao = new LocalizacaoSistema();
    $localizacao->entradaCaminhos( array(
         $_SERVER['SERVER_NAME']."/intranet" => "In&iacute;cio",
         "educar_enderecamento_index.php"    => "Endereçamento",
         ""        => "{$nomeMenu} bairro"             
    ));
    $this->enviaLocalizacao($localizacao->montar());    

    return $retorno;
  }

  function Gerar()
  {
    // primary keys
    $this->campoOculto('idbai', $this->idbai);

    // foreign keys
    $opcoes = array('' => 'Selecione');
    if (class_exists('clsPais')) {
      $objTemp = new clsPais();
      $lista = $objTemp->lista(FALSE, FALSE, FALSE, FALSE, FALSE, 'nome ASC');

      if (is_array($lista) && count($lista)) {
        foreach ($lista as $registro) {
          $opcoes[$registro['idpais']] = $registro['nome'];
        }
      }
    }
    else {
      echo '<!--\nErro\nClasse clsPais nao encontrada\n-->';
      $opcoes = array('' => 'Erro na geracao');
    }
    $this->campoLista('idpais', 'Pais', $opcoes, $this->idpais);

    $opcoes = array('' => 'Selecione');
    if (class_exists('clsUf')) {
      if ($this->idpais) {
        $objTemp = new clsUf();

        $lista = $objTemp->lista(FALSE, FALSE, $this->idpais, FALSE, FALSE, 'nome ASC');

        if (is_array($lista) && count($lista)) {
          foreach ($lista as $registro) {
            $opcoes[$registro['sigla_uf']] = $registro['nome'];
          }
        }
      }
    }
    else {
      echo '<!--\nErro\nClasse clsUf nao encontrada\n-->';
      $opcoes = array('' => 'Erro na geracao');
    }

    $this->campoLista('sigla_uf', 'Estado', $opcoes, $this->sigla_uf);

    $opcoes = array('' => 'Selecione');
    if (class_exists('clsMunicipio')) {
      if ($this->sigla_uf) {
        $objTemp = new clsMunicipio();
        $lista = $objTemp->lista(FALSE, $this->sigla_uf, FALSE, FALSE, FALSE,
          FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, 'nome ASC');

        if (is_array($lista) && count($lista)) {
          foreach ($lista as $registro) {
            $opcoes[$registro['idmun']] = $registro['nome'];
          }
        }
      }
    }
    else {
      echo '<!--\nErro\nClasse clsMunicipio nao encontrada\n-->';
      $opcoes = array("" => "Erro na geracao");
    }

    $this->campoLista('idmun', 'Município', $opcoes, $this->idmun);

    $opcoes = array('' => 'Selecione');
    if (class_exists('clsPublicDistrito')) {
      if ($this->idmun) {
        $objTemp = new clsPublicDistrito();
        $objTemp->setOrderBy(' nome asc ');
        $lista = $objTemp->lista($this->idmun);

        if (is_array($lista) && count($lista)) {
          foreach ($lista as $registro) {
            $opcoes[$registro['iddis']] = $registro['nome'];
          }
        }
      }
    }
    else {
      echo '<!--\nErro\nClasse clsMunicipio nao encontrada\n-->';
      $opcoes = array("" => "Erro na geracao");
    }

    $this->campoLista('iddis', 'Distrito', $opcoes, $this->iddis);

    $opcoes = array('' => 'Selecione');
    if (class_exists('clsPublicSetorBai')) {
      $objTemp = new clsPublicSetorBai();
      $objTemp->setOrderBy(' nome asc ');
      $lista = $objTemp->lista();

      if (is_array($lista) && count($lista)) {
        foreach ($lista as $registro) {
          $opcoes[$registro['idsetorbai']] = $registro['nome'];
        }
      }      
    }
    else {
      echo '<!--\nErro\nClasse clsMunicipio nao encontrada\n-->';
      $opcoes = array("" => "Erro na geracao");
    }

    $this->campoLista('idsetorbai', 'Setor', $opcoes, $this->idsetorbai, NULL, NULL, NULL, NULL, NULL, FALSE);

    $zona = App_Model_ZonaLocalizacao::getInstance();
    $this->campoLista('zona_localizacao', 'Zona Localização', $zona->getEnums(),
      $this->zona_localizacao);

    $this->campoTexto('nome', 'Nome', $this->nome, 30, 255, TRUE);
  }

  function Novo()
  {
    $obj = new clsPublicBairro($this->idmun, NULL, NULL, $this->nome, NULL,
      NULL, 'U', $this->pessoa_logada, NULL, 'I', NULL, 9,
      $this->zona_localizacao, $this->iddis);
    $obj->idsetorbai = $this->idsetorbai;

    $cadastrou = $obj->cadastra();
    if ($cadastrou) {

      $enderecamento = new clsPublicBairro();
      $enderecamento->idbai = $cadastrou;
      $enderecamento = $enderecamento->detalhe();
      $auditoria = new clsModulesAuditoriaGeral("Endereçamento de Bairro", $this->pessoa_logada, $cadastrou);
      $auditoria->inclusao($enderecamento);

      $this->mensagem .= 'Cadastro efetuado com sucesso.<br>';
      $this->simpleRedirect('public_bairro_lst.php');
    }

    $this->mensagem = 'Cadastro n&atilde;o realizado.<br>';
    echo "<!--\nErro ao cadastrar clsPublicBairro\nvalores obrigatorios\nis_numeric( $this->idmun ) && is_string( $this->nome ) && is_string( $this->origem_gravacao ) && is_string( $this->operacao ) && is_numeric( $this->idsis_cad )\n-->";

    return FALSE;
  }

  function Editar()
  {
    $enderecamentoDetalhe = new clsPublicBairro(null, null, $this->idbai);
    $enderecamentoDetalhe->cadastrou = $this->idbai;
    $enderecamentoDetalheAntes = $enderecamentoDetalhe->detalhe();

    $obj = new clsPublicBairro($this->idmun, NULL, $this->idbai, $this->nome,
      $this->pessoa_logada, NULL, 'U', NULL, NULL, 'I', NULL, 9,
      $this->zona_localizacao, $this->iddis);
    $obj->idsetorbai = $this->idsetorbai;

    $editou = $obj->edita();
    if ($editou) {

      $enderecamentoDetalheDepois = $enderecamentoDetalhe->detalhe();
      $auditoria = new clsModulesAuditoriaGeral("Endereçamento de Bairro", $this->pessoa_logada, $this->idbai);
      $auditoria->alteracao($enderecamentoDetalheAntes, $enderecamentoDetalheDepois);

      $this->mensagem .= "Edi&ccedil;&atilde;o efetuada com sucesso.<br>";
      $this->simpleRedirect('public_bairro_lst.php');
    }

    $this->mensagem = 'Edi&ccedil;&atilde;o n&atilde;o realizada.<br>';
    echo "<!--\nErro ao editar clsPublicBairro\nvalores obrigatorios\nif( is_numeric( $this->idbai ) )\n-->";

    return FALSE;
  }

  function Excluir()
  {
    $obj = new clsPublicBairro(NULL, NULL, $this->idbai, NULL, $this->pessoa_logada);
    $excluiu = $obj->excluir();

    if ($excluiu) {
      $this->mensagem .= 'Exclusão efetuada com sucesso.<br>';
      $this->simpleRedirect('public_bairro_lst.php');
    }

    $this->mensagem = 'Exclusão não realizada.<br>';

    return FALSE;
  }
}

// Instancia objeto de página
$pagina = new clsIndexBase();

// Instancia objeto de conteúdo
$miolo = new indice();

// Atribui o conteúdo à página
$pagina->addForm($miolo);

// Gera o código HTML
$pagina->MakeAll();
?>
<script type='text/javascript'>
document.getElementById('idpais').onchange = function()
{
  var campoPais = document.getElementById('idpais').value;

  var campoUf= document.getElementById('sigla_uf');
  campoUf.length = 1;
  campoUf.disabled = true;
  campoUf.options[0].text = 'Carregando estado...';

  var xml_uf = new ajax( getUf );
  xml_uf.envia('public_uf_xml.php?pais=' + campoPais);
}

function getUf(xml_uf)
{
  var campoUf = document.getElementById('sigla_uf');
  var DOM_array = xml_uf.getElementsByTagName('estado');

  if (DOM_array.length) {
    campoUf.length = 1;
    campoUf.options[0].text = 'Selecione um estado';
    campoUf.disabled = false;

    for (var i = 0; i < DOM_array.length; i++) {
      campoUf.options[campoUf.options.length] = new Option(DOM_array[i].firstChild.data,
        DOM_array[i].getAttribute('sigla_uf'), false, false);
    }
  }
  else {
    campoUf.options[0].text = 'O pais não possui nenhum estado';
  }
}

document.getElementById('sigla_uf').onchange = function()
{
  var campoUf = document.getElementById('sigla_uf').value;

  var campoMunicipio= document.getElementById('idmun');
  campoMunicipio.length = 1;
  campoMunicipio.disabled = true;
  campoMunicipio.options[0].text = 'Carregando município...';

  var xml_municipio = new ajax(getMunicipio);
  xml_municipio.envia('public_municipio_xml.php?uf=' + campoUf);
}

function getMunicipio(xml_municipio)
{
  var campoMunicipio = document.getElementById('idmun');
  var DOM_array = xml_municipio.getElementsByTagName('municipio');

  if(DOM_array.length) {
    campoMunicipio.length = 1;
    campoMunicipio.options[0].text = 'Selecione um município';
    campoMunicipio.disabled = false;

    for (var i = 0; i < DOM_array.length; i++) {
      campoMunicipio.options[campoMunicipio.options.length] = new Option(DOM_array[i].firstChild.data,
        DOM_array[i].getAttribute('idmun'), false, false);
    }
  }
  else {
    campoMunicipio.options[0].text = 'O estado não possui nenhum município';
  }
}

document.getElementById('idmun').onchange = function()
{
  var campoMunicipio = document.getElementById('idmun').value;

  var campoDistrito      = document.getElementById('iddis');
  campoDistrito.length   = 1;
  campoDistrito.disabled = true;

  campoDistrito.options[0].text = 'Carregando distritos...';

  var xml_distrito = new ajax(getDistrito);
  xml_distrito.envia('public_distrito_xml.php?idmun=' + campoMunicipio);
}

function getDistrito(xml_distrito)
{
  var campoDistrito = document.getElementById('iddis');
  var DOM_array      = xml_distrito.getElementsByTagName( "distrito" );
  console.log(DOM_array);

  if (DOM_array.length) {
    campoDistrito.length          = 1;
    campoDistrito.options[0].text = 'Selecione um distrito';
    campoDistrito.disabled        = false;

    for (var i = 0; i < DOM_array.length; i++) {
      campoDistrito.options[campoDistrito.options.length] = new Option(
        DOM_array[i].firstChild.data, DOM_array[i].getAttribute('iddis'),
        false, false
      );
    }
  }
  else {
    campoDistrito.options[0].text = 'O município não possui nenhum distrito';
  }
}
</script>
