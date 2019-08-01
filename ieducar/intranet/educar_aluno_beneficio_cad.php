<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
    *                                                                        *
    *   @author Prefeitura Municipal de Itajaí                               *
    *   @updated 29/03/2007                                                  *
    *   Pacote: i-PLB Software Público Livre e Brasileiro                    *
    *                                                                        *
    *   Copyright (C) 2006  PMI - Prefeitura Municipal de Itajaí             *
    *                       ctima@itajai.sc.gov.br                           *
    *                                                                        *
    *   Este  programa  é  software livre, você pode redistribuí-lo e/ou     *
    *   modificá-lo sob os termos da Licença Pública Geral GNU, conforme     *
    *   publicada pela Free  Software  Foundation,  tanto  a versão 2 da     *
    *   Licença   como  (a  seu  critério)  qualquer  versão  mais  nova.    *
    *                                                                        *
    *   Este programa  é distribuído na expectativa de ser útil, mas SEM     *
    *   QUALQUER GARANTIA. Sem mesmo a garantia implícita de COMERCIALI-     *
    *   ZAÇÃO  ou  de ADEQUAÇÃO A QUALQUER PROPÓSITO EM PARTICULAR. Con-     *
    *   sulte  a  Licença  Pública  Geral  GNU para obter mais detalhes.     *
    *                                                                        *
    *   Você  deve  ter  recebido uma cópia da Licença Pública Geral GNU     *
    *   junto  com  este  programa. Se não, escreva para a Free Software     *
    *   Foundation,  Inc.,  59  Temple  Place,  Suite  330,  Boston,  MA     *
    *   02111-1307, USA.                                                     *
    *                                                                        *
    * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

require_once ("include/clsBase.inc.php");
require_once ("include/clsCadastro.inc.php");
require_once ("include/clsBanco.inc.php");
require_once ("include/pmieducar/geral.inc.php" );
require_once ("include/modules/clsModulesAuditoriaGeral.inc.php");

class clsIndexBase extends clsBase
{
    function Formular()
    {
        $this->SetTitulo( "{$this->_instituicao} i-Educar - Benef&iacute;cio Aluno" );
        $this->processoAp = "581";
        $this->addEstilo("localizacaoSistema");
    }
}

class indice extends clsCadastro
{
    /**
     * Referencia pega da session para o idpes do usuario atual
     *
     * @var int
     */
    var $pessoa_logada;

    var $cod_aluno_beneficio;
    var $ref_usuario_exc;
    var $ref_usuario_cad;
    var $nm_beneficio;
    var $desc_beneficio;
    var $data_cadastro;
    var $data_exclusao;
    var $ativo;

    function Inicializar()
    {
        $retorno = "Novo";


        $this->cod_aluno_beneficio=$_GET["cod_aluno_beneficio"];

        $obj_permissoes = new clsPermissoes();
        $obj_permissoes->permissao_cadastra( 581, $this->pessoa_logada,3, "educar_aluno_beneficio_lst.php" );

        if( is_numeric( $this->cod_aluno_beneficio ) )
        {

            $obj = new clsPmieducarAlunoBeneficio( $this->cod_aluno_beneficio );
            $registro  = $obj->detalhe();
            if( $registro )
            {
                foreach( $registro AS $campo => $val )  // passa todos os valores obtidos no registro para atributos do objeto
                    $this->$campo = $val;


                //** verificao de permissao para exclusao
                $this->fexcluir = $obj_permissoes->permissao_excluir(581,$this->pessoa_logada,3);
                //**

                $retorno = "Editar";
            }
        }
        $this->url_cancelar = ($retorno == "Editar") ? "educar_aluno_beneficio_det.php?cod_aluno_beneficio={$registro["cod_aluno_beneficio"]}" : "educar_aluno_beneficio_lst.php";

        $nomeMenu = $retorno == "Editar" ? $retorno : "Cadastrar";

        $this->breadcrumb($nomeMenu . ' benefícios de alunos', [
            url('intranet/educar_index.php') => 'Escola',
        ]);

        $this->nome_url_cancelar = "Cancelar";
        return $retorno;
    }

    function Gerar()
    {
        // primary keys
        $this->campoOculto( "cod_aluno_beneficio", $this->cod_aluno_beneficio );

        // foreign keys

        // text
        $this->campoTexto( "nm_beneficio", "Benef&iacute;cio", $this->nm_beneficio, 30, 255, true );
        $this->campoMemo( "desc_beneficio", "Descri&ccedil;&atilde;o Benef&iacute;cio", $this->desc_beneficio, 60, 5, false );

        // data

    }

    function Novo()
    {


        $obj = new clsPmieducarAlunoBeneficio( $this->cod_aluno_beneficio, $this->pessoa_logada, $this->pessoa_logada, $this->nm_beneficio, $this->desc_beneficio, $this->data_cadastro, $this->data_exclusao, $this->ativo );
        $cadastrou = $obj->cadastra();
        if( $cadastrou )
        {
            $beneficio = new clsPmieducarAlunoBeneficio($cadastrou);
            $beneficio = $beneficio->detalhe();

            $auditoria = new clsModulesAuditoriaGeral("aluno_beneficio", $this->pessoa_logada, $cadastrou);
            $auditoria->inclusao($beneficio);

            $this->mensagem .= "Cadastro efetuado com sucesso.<br>";
            $this->simpleRedirect('educar_aluno_beneficio_lst.php');
        }

        $this->mensagem = "Cadastro n&atilde;o realizado.<br>";
        echo "<!--\nErro ao cadastrar clsPmieducarAlunoBeneficio\nvalores obrigatorios\nis_numeric( $this->ref_usuario_cad ) && is_string( $this->nm_beneficio )\n-->";

        return false;
    }

    function Editar()
    {


        $beneficioDetalhe = new clsPmieducarAlunoBeneficio($this->cod_aluno_beneficio);
        $beneficioDetalheAntes = $beneficioDetalhe->detalhe();

        $obj = new clsPmieducarAlunoBeneficio($this->cod_aluno_beneficio, $this->pessoa_logada, $this->pessoa_logada, $this->nm_beneficio, $this->desc_beneficio, $this->data_cadastro, $this->data_exclusao, $this->ativo);
        $editou = $obj->edita();
        if( $editou )
        {
            $beneficioDetalheDepois = $beneficioDetalhe->detalhe();
            $auditoria = new clsModulesAuditoriaGeral("aluno_beneficio", $this->pessoa_logada, $this->cod_aluno_beneficio);
            $auditoria->alteracao($beneficioDetalheAntes, $beneficioDetalheDepois);

            $this->mensagem .= "Edi&ccedil;&atilde;o efetuada com sucesso.<br>";
            $this->simpleRedirect('educar_aluno_beneficio_lst.php');
        }

        $this->mensagem = "Edi&ccedil;&atilde;o n&atilde;o realizada.<br>";
        echo "<!--\nErro ao editar clsPmieducarAlunoBeneficio\nvalores obrigatorios\nif( is_numeric( $this->cod_aluno_beneficio ) && is_numeric( $this->ref_usuario_exc ) )\n-->";
        return false;
    }

    function Excluir()
    {


        $obj = new clsPmieducarAlunoBeneficio($this->cod_aluno_beneficio, $this->pessoa_logada, $this->pessoa_logada, $this->nm_beneficio, $this->desc_beneficio, $this->data_cadastro, $this->data_exclusao, 0);

        $beneficio = $obj->detalhe();

        $excluiu = $obj->excluir();
        if( $excluiu )
        {
            $auditoria = new clsModulesAuditoriaGeral("aluno_beneficio", $this->pessoa_logada, $this->cod_aluno_beneficio);
            $auditoria->exclusao($beneficio);

            $this->mensagem .= "Exclus&atilde;o efetuada com sucesso.<br>";
            $this->simpleRedirect('educar_aluno_beneficio_lst.php');
        }

        $this->mensagem = "Exclus&atilde;o n&atilde;o realizada.<br>";
        echo "<!--\nErro ao excluir clsPmieducarAlunoBeneficio\nvalores obrigatorios\nif( is_numeric( $this->cod_aluno_beneficio ) && is_numeric( $this->ref_usuario_exc ) )\n-->";
        return false;
    }
}

// cria uma extensao da classe base
$pagina = new clsIndexBase();
// cria o conteudo
$miolo = new indice();
// adiciona o conteudo na clsBase
$pagina->addForm( $miolo );
// gera o html
$pagina->MakeAll();
?>
