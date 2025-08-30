<?php
class Controller_Lead_Export extends Controller_Lead_Base {

    private function products()
    {
        $this->leadID = $this->request->param('id');

        $url = $_ENV["api_vallery_v1"].'/Leads/'.$this->leadID.'/Produtos/';

        $json = Request::factory( $url )
            ->method('GET')
            ->query(array('filter' => array( 'include' => 'Detalhe' )))
            ->execute()
            ->body();

        $response = json_decode($json,true);

        return $response;
    }

    private function lead()
    {
        $this->leadID = $this->request->param('id');

        if ( ! $this->leadID ) die('sem $this->leadID');

        $url = $_ENV["api_vallery_v1"].'/Leads/'.$this->leadID;

        $json = Request::factory( $url )
            ->method('GET')
            //->query(array('filter' => array( 'include' => 'Detalhe' )))
            ->execute()
            ->body();

        $response = json_decode($json,true);

        return $response;
    }

    public function action_index()
    {
        $this->auto_render = FALSE;

        $helper = new PhpOffice\PhpSpreadsheet\Helper\Sample;
        if ($helper->isCli()) {
            $helper->log('This example should only be run from a Web Browser' . PHP_EOL);
            return;
        }

        // Create new Spreadsheet object
        $spreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet;
        // Set document properties
        $spreadsheet->getProperties()->setCreator('Maarten Balliauw')
            ->setLastModifiedBy('Maarten Balliauw')
            ->setTitle('Office 2007 XLSX Test Document')
            ->setSubject('Office 2007 XLSX Test Document')
            ->setDescription('Test document for Office 2007 XLSX, generated using PHP classes.')
            ->setKeywords('office 2007 openxml php')
            ->setCategory('Test result file');

        $spreadsheet->setActiveSheetIndex(0)
            ->setCellValue('A2', 'Código') 
            ->setCellValue('B2', 'Produto') 
            ->setCellValue('C2', 'Marca')
            ->setCellValue('D2', 'Quantidade')
            ->setCellValue('E2', 'Preço Base Unitário')
            ->setCellValue('F2', 'ST')
            ->setCellValue('G2', 'IPI')  
            ->setCellValue('H2', 'Subtotal');  

        $counter = 3;

        $lead = self::lead();
        $products = self::products();

        //s($lead,$products);
        //die();

        foreach( $products as $k => $v )  
        { 
            $products_ids[] = $v['produtoPOID'] ; // group product id
        }

        if ( !empty ($products_ids) )
        {
            //// sub routines by group
            $ids="'".implode("','",$products_ids)."'";

            // stock
            $stk_group = $this->get_stock_group( $ids, $lead['unidadeEmitentePOID'] );
        }

        foreach( $products as $k => $v )  
        { 
            $products[$k]['stock'] = $stk_group[ $v['produtoPOID'] ]['estoques'][ $lead['unidadeEmitentePOID'] ];
        }

        //s($products);
        //die();

        $total = 0;

        foreach ( $products as $k => $v )
        {
            $nome = null;

            if ( isset( $v['solr']['produtoNome'] )) $nome.= $v['solr']['produtoNome'].' ';
            if ( isset( $v['solr']['produtoDescricaoCurta'] )) $nome.= $v['solr']['produtoDescricaoCurta'].' ';
            if ( isset( $v['solr']['produtoComplemento'] )) $nome.= $v['solr']['produtoComplemento'].' ';

            if( $v['stock']['estoqueDisponivel'] > 0 )
            {
                $subtotal = round($v['produtoValor'] * $v['produtoQuantidade'] + $v['produtoST'] + $v['produtoIPI'], 2);

                $total+= $subtotal;

                // Add some data
                $spreadsheet->setActiveSheetIndex(0)
                    ->setCellValue('A'.$counter, $v['produtoPOID']) // Modelo
                    ->setCellValue('B'.$counter, $v['Detalhe']['produtoModelo']) // Modelo
                    ->setCellValue('C'.$counter, $v['Detalhe']['produtoMarca'])
                    ->setCellValue('D'.$counter, $v['produtoQuantidade'])
                    ->setCellValue('E'.$counter, $v['produtoValor'])
                    ->setCellValue('F'.$counter, $v['produtoST'])
                    ->setCellValue('G'.$counter, $v['produtoIPI'])
                    ->setCellValue('H'.$counter, $subtotal )
                    ->setCellValue('I'.$counter, $nome );
            }else{
                // Add some data
                $spreadsheet->setActiveSheetIndex(0)
                    ->setCellValue('A'.$counter, $v['produtoPOID']) // Modelo
                    ->setCellValue('B'.$counter, $v['Detalhe']['produtoModelo']) // Modelo
                    ->setCellValue('C'.$counter, $v['Detalhe']['produtoMarca'])
                    ->setCellValue('D'.$counter, $v['produtoQuantidade'])
                    ->setCellValue('E'.$counter, '0')
                    ->setCellValue('F'.$counter, '0')
                    ->setCellValue('G'.$counter, '0')
                    ->setCellValue('H'.$counter, '0')
                    ->setCellValue('I'.$counter, $nome );
            }

            $counter++;
        } 

        $spreadsheet->setActiveSheetIndex(0)
            ->setCellValue('A'.$counter, '') // Modelo
            ->setCellValue('B'.$counter, '')
            ->setCellValue('C'.$counter, '')
            ->setCellValue('D'.$counter, '')
            ->setCellValue('E'.$counter, '')
            ->setCellValue('F'.$counter, '')
            ->setCellValue('G'.$counter, '')
            ->setCellValue('H'.$counter, $total);

        $spreadsheet->getActiveSheet()->getStyle('A1');

        //s($spreadsheet);
        //die();

        // Rename worksheet
        $spreadsheet->getActiveSheet()->setTitle('Simple');
        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $spreadsheet->setActiveSheetIndex(0);

        // Redirect output to a client’s web browser (Xlsx)
        //header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Lead-'.$this->leadID.'.xlsx"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        //header('Cache-Control: max-age=1');
        // If you're serving to IE over SSL, then the following may be needed
        //header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        //header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        //header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        //header('Pragma: public'); // HTTP/1.0

        //$file = "teste.xlsx";

        $writer = new PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        //$writer->save($file);
        $writer->save('php://output');
        exit;
    }

} 