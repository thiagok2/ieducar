<?php

require_once 'include/clsBase.inc.php';
require_once 'include/clsDetalhe.inc.php';
require_once 'include/clsBanco.inc.php';
require_once 'include/pmieducar/geral.inc.php';
require_once 'lib/Portabilis/String/Utils.php';
require_once 'lib/Portabilis/Date/Utils.php';

class clsIndexBase extends clsBase
{
    public function Formular()
    {
        $this->SetTitulo("{$this->_instituicao} i-Educar - Distribuições de uniforme escolar");
        $this->processoAp = '578';
        $this->addEstilo('localizacaoSistema');
    }
}

class indice extends clsDetalhe
{
    /**
     * Titulo no topo da pagina
     *
     * @var int
     */
    public $titulo;

    public $cod_distribuicao_uniforme;

    public $ref_cod_aluno;

    public function Gerar()
    {
        $this->titulo = 'Distribuições de uniforme escolar - Detalhe';
        $this->cod_distribuicao_uniforme = $_GET['cod_distribuicao_uniforme'];
        $this->ref_cod_aluno = $_GET['ref_cod_aluno'];

        $tmp_obj = new clsPmieducarDistribuicaoUniforme($this->cod_distribuicao_uniforme);
        $registro = $tmp_obj->detalhe();

        if (!$registro) {
            $this->simpleRedirect("educar_distribuicao_uniforme_lst.php?ref_cod_aluno={$this->ref_cod_aluno}");
        }

        $obj_aluno = new clsPmieducarAluno();
        $lst_aluno = $obj_aluno->lista($registro['ref_cod_aluno'], null, null, null, null, null, null, null, null, null, 1);

        if (is_array($lst_aluno)) {
            $det_aluno = array_shift($lst_aluno);
            $nm_aluno = $det_aluno['nome_aluno'];
        }

        if ($nm_aluno) {
            $this->addDetalhe(['Aluno', "{$nm_aluno}"]);
        }

        if ($registro['ano']) {
            $this->addDetalhe(['Ano', "{$registro['ano']}"]);
        }

        if ($registro['data']) {
            $this->addDetalhe(['Data da distribuição', Portabilis_Date_Utils::pgSQLToBr($registro['data'])]);
        }

        if ($registro['ref_cod_escola']) {
            $obj_escola = new clsPmieducarEscola();
            $lst_escola = $obj_escola->lista($registro['ref_cod_escola']);

            if (is_array($lst_escola)) {
                $det_escola = array_shift($lst_escola);
                $nm_escola = $det_escola['nome'];
                $this->addDetalhe(['Escola fornecedora', Portabilis_String_Utils::toLatin1($nm_escola)]);
            }
        }

        if (dbBool($registro['kit_completo'])) {
            $this->addDetalhe(['Recebeu kit completo', 'Sim']);
        } else {
            $this->addDetalhe(['Recebeu kit completo', Portabilis_String_Utils::toLatin1('Não')]);
            $this->addDetalhe([Portabilis_String_Utils::toLatin1('Quantidade de agasalhos (jaqueta e calça)'), $registro['agasalho_qtd'] ?: '0']);
            $this->addDetalhe(['Quantidade de camisetas (manga curta)', $registro['camiseta_curta_qtd'] ?: '0']);
            $this->addDetalhe(['Quantidade de camisetas (manga longa)', $registro['camiseta_longa_qtd'] ?: '0']);
            $this->addDetalhe(['Quantidade de camisetas infantis (sem manga)', $registro['camiseta_infantil_qtd'] ?: '0']);
            $this->addDetalhe(['Quantidade de meias', $registro['meias_qtd'] ?: '0']);
            $this->addDetalhe(['Bermudas tectels (masculino)', $registro['bermudas_tectels_qtd'] ?: '0']);
            $this->addDetalhe(['Bermudas coton (feminino)', $registro['bermudas_coton_qtd'] ?: '0']);
            $this->addDetalhe([Portabilis_String_Utils::toLatin1('Quantidade de tênis'), $registro['tenis_qtd'] ?: '0']);
        }

        $obj_permissoes = new clsPermissoes();

        if ($obj_permissoes->permissao_cadastra(578, $this->pessoa_logada, 7)) {
            $this->url_novo = "educar_distribuicao_uniforme_cad.php?ref_cod_aluno={$registro['ref_cod_aluno']}";
            $this->url_editar = "educar_distribuicao_uniforme_cad.php?ref_cod_aluno={$registro['ref_cod_aluno']}&cod_distribuicao_uniforme={$registro['cod_distribuicao_uniforme']}";
        }

        $this->url_cancelar = "educar_distribuicao_uniforme_lst.php?ref_cod_aluno={$registro['ref_cod_aluno']}";
        $this->largura = '100%';

        $this->breadcrumb('Distribuições de uniforme escolar', [
            'educar_index.php' => 'Escola'
        ]);
    }
}

$pagina = new clsIndexBase();
$pagina->addForm(new indice());
$pagina->MakeAll();
