<?php

use Tests\TestCase;

require_once 'include/pmieducar/clsPmieducarClienteSuspensao.inc.php';

class ClsBancoTest extends TestCase
{
    public function testDoCountFromObj()
    {
        $db = new clsBanco();
        $db->Conecta();

        $obj = new clsPmieducarClienteSuspensao();

        $this->assertNotEquals(true, is_null($db->doCountFromObj($obj)));
    }

    public function testConexao()
    {
        $db = new clsBanco();
        $db->Conecta();

        $this->assertTrue((bool) $db->bLink_ID);
    }

    public function testFormatacaoDeValoresBooleanos()
    {
        $data = [
            'id' => 1,
            'hasChild' => true
        ];

        $db = new clsBanco();

        $formatted = $db->formatValues($data);
        $this->assertSame('t', $formatted['hasChild']);

        $data['hasChild'] = false;
        $formatted = $db->formatValues($data);

        $this->assertSame('f', $formatted['hasChild']);
    }

    public function testOpcaoDeLancamentoDeExcecaoEFalsePorPadrao()
    {
        $db = new clsBanco();

        $this->assertFalse($db->getThrowException());
    }

    public function testConfiguracaoDeOpcaoDeLancamentoDeExcecao()
    {
        $db = new clsBanco();
        $db->setThrowException(true);

        $this->assertTrue($db->getThrowException());
    }

    public function testFetchTipoArrayDeResultadosDeUmaQuery()
    {
        $db = new clsBanco();
        $db->Consulta('SELECT spcname FROM pg_tablespace');

        $row = $db->ProximoRegistro();
        $row = $db->Tupla();

        $this->assertNotNull($row[0]);
        $this->assertNotNull($row['spcname']);
    }

    public function testFetchTipoAssocDeResultadosDeUmaQuery()
    {
        $db = new clsBanco(['fetchMode' => clsBanco::FETCH_ASSOC]);
        $db->Consulta('SELECT spcname FROM pg_tablespace');

        $row = $db->ProximoRegistro();
        $row = $db->Tupla();

        $this->assertFalse(array_key_exists(0, $row));
        $this->assertNotNull($row['spcname']);
    }
}
