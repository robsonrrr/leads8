<?php
class Controller_Service extends Controller_Website {

    public function before()
    {
        parent::before();
    }

    public function action_update()
    {
        $get = $this->request->query();

        //s($get);
        //die();
        $data = array(
            $get['field']  => $get['value'],
        );

        $url = $_ENV['api_vallery_v1'].'/Leads/'.$get['lead'];

        $json = Request::factory($url)
            ->method('PUT')
            ->post($data)
            ->execute()
            ->body();

        //$response = json_decode( $json, true );

        return $this->response->body( $json );
    }

    public function action_auto($var='',$xtras='')
    {	

        $q="";
        //die('teste');
        if( isset($_REQUEST['term'])){
            $q = $_REQUEST['term'];
        }elseif( isset($_REQUEST['query'])){
            $q = $_REQUEST['query'];
        }elseif( isset($_REQUEST['q'])){
            $q = $_REQUEST['q'];
        }

        $vars = explode("_",$this->request->param('id'));
        $var=$vars[0];        	
        if( isset($vars[1])) {
            $id=$vars[1];
        }else{
            $id=0;	
        }

        switch(strtolower($var)) {
            case('fp'):
                $result[0] = 'Nao';
                $result[1] = 'Sim';
                echo $this->json($result);
                break ; 

            case('estado'):
                echo $this->suggestStates($q);
                break ; 
            case('cidade'):
                echo $this->suggestCities($q);
                break ; 	
            case('ncm'):
                echo $this->suggestNCM($q);
                break ; 				   
            case('sender'):
                echo $this->suggestSender($q);
            case('user'):
                echo $this->suggestUsers($q);
            case('cc'):
                $this->suggestCC($q,$id);
                break ; 			  
            case('cliente'):
                $a = $this->suggestCustomer($q);
                break ; 	
            case('transporter'):
                $a= $this->suggestTransporter($q,$id);
                echo $this->simple_json($a);
                break ; 	
            case('banks'):
                $a= $this->suggestBanks($q);
                echo $this->json($a);
                break ; 	

            case('accounts'):
                $a= $this->suggestAccounts($q);
                echo $this->json($a);
                break ; 
            case('despesas'):
                $a= $this->suggestDespesas($q);
                echo $this->json($a);
                break ; 			   				   			   
            case('unities'):
                echo $this->json($this->suggestUnities($q));
                break ; 				   			   
            case('supplier'):
                echo $this->json($this->suggestSupplier($q));
                break ; 				   			   
            case('costcenter'):
                echo $this->json($this->suggestCostCenter($q));
                break ; 				   			   

            case('segments'):
                echo $this->json($this->suggestSegments($q));
                break ; 				   			   



            case('general'):
                $a = $this->suggestCustomer($q);	
                $b = $this->suggestMachines($q);	   
                $result = array_merge($a, $b);
                echo $this->json($result);
                break ; 				   
        }
        exit;
    }

    public function action_empresas_p()
    {		
        $sql= "SELECT * FROM  `empresas` ORDER BY nome" ;
        $result =  $this->action_connect_SELECT($sql);				
        foreach($result as $arr){
            $array[$arr['id'].'|'.$arr['nome']]= $arr['nome']; 
        }
        echo $this->simple_json($array);
        exit;
    }

    public function action_nop()
    {		

        if ( isset($_REQUEST['query']) )
        {
            $q = $_REQUEST['query'];
        }else{
            $q = '' ;
        }

        $sql= sprintf("SELECT id_nop as id, nop as name  FROM  `nop` WHERE  ( nop LIKE '%%%s%%' ) ORDER BY nop", $q );
        $result =  $this->action_connect_SELECT($sql);				
        $this->response->body(json_encode($result));

    }

    public function action_segments($search='')
    {		
        //$result= array('0'=>'Nao', '1'=>'Sim');
        $sql= sprintf("	SELECT id_segments as id,  segment AS user	 ,'Segments' as cat, 'Segmento(s)' as category 	 
				FROM  `segments` 
				WHERE  ( segment LIKE   '%%%s%%' )
				ORDER BY segment ASC  ", $search) ;
        $result =  $this->action_connect_SELECT($sql);				

        //		$sql= sprintf("SELECT  id, user FROM  `users` WHERE depto='VENDAS' AND trim(user) >''  ORDER BY user ", $w);
        //		$result =  $this->action_connect_SELECT($sql);				
        foreach($result as $arr){
            if( strlen($arr['user']) >''){
                $array[$arr['id'].'|'.$arr['user']]= $arr['user']; 
            }
        }
        echo $this->simple_json($array);
        exit;


        //	return $this->formatAutocomplete($sql,'ucfirst' );
    }

    public function suggestSegments($search='')
    {		
        //$result= array('0'=>'Nao', '1'=>'Sim');
        $sql= sprintf("	SELECT id_segments as id,  segment AS Value	 ,'Segments' as cat, 'Segmento(s)' as category 	 
				FROM  `segments` 
				WHERE  ( segment LIKE   '%%%s%%' )
				ORDER BY segment ASC  ", $search) ;
        $result =  $this->action_connect_SELECT($sql);				
        return $this->formatAutocomplete($sql,'ucfirst' );
    }


    public function suggestCostCenter($search='')
    {		
        //$result= array('0'=>'Nao', '1'=>'Sim');
        $sql= sprintf("	SELECT  id, CONCAT_WS(' | ', id, nome) AS Value	 ,'Supplier' as cat, 'Fornecedor(es)' as category 	 
				FROM  `centro_de_custos` 
				WHERE  ( nome LIKE   '%%%s%%' )
				ORDER BY nome ASC  ", $search) ;
        $result =  $this->action_connect_SELECT($sql);				
        return $this->formatAutocomplete($sql,'ucfirst' );
    }


    public function suggestSupplier($search='')
    {		
        //$result= array('0'=>'Nao', '1'=>'Sim');
        $sql= sprintf("	SELECT  id, CONCAT_WS(' | ', id, nome) AS Value	 ,'Supplier' as cat, 'Fornecedor(es)' as category 	 
				FROM  `fornecedores` 
				WHERE  ( nome LIKE   '%%%s%%' )
				ORDER BY nome ASC  ", $search) ;
        $result =  $this->action_connect_SELECT($sql);				
        return $this->formatAutocomplete($sql,'ucfirst' );
    }

    public function suggestUnities($search='')
    {		
        //$result= array('0'=>'Nao', '1'=>'Sim');
        $sql= sprintf("	SELECT  EmitentePOID AS id, CONCAT_WS(' | ',  EmitentePOID, Fantasia) AS Value	 ,'Unities' as cat, 'Unidade(s)' as category 
				FROM  `Emitentes`  
				WHERE  ( Fantasia LIKE   '%%%s%%'  or  EmitentePOID LIKE   '%%%s%%' )
				 ", $search, $search) ;
        $result =  $this->action_connect_SELECT($sql);				
        return $this->formatAutocomplete($sql,'ucfirst' );
    }
    public function suggestAccounts($search='')
    {		
        //$result= array('0'=>'Nao', '1'=>'Sim');
        $sql= sprintf("SELECT  id, CONCAT(conta,'-',descricao) as Value	  ,'accounts' as cat, 'Plano de Conta(s)' as category
				FROM  `plano_de_contas` 
				WHERE  ( descricao LIKE   '%%%s%%' OR conta LIKE   '%%%s%%' )
				ORDER BY conta 
				LIMIT 30  ", $search, $search) ;
        $result =  $this->action_connect_SELECT($sql);				
        return $this->formatAutocomplete($sql,'ucfirst' );
    }
    // search suggest for Clientes
    public function suggestBanks($search='') {
        $sql = sprintf("SELECT CONCAT_WS('|', UCASE(nome),  UCASE(agencia), LCASE(conta) ) AS Label,id, CONCAT_WS('|', UCASE(nome),  LCASE(agencia), LCASE(conta) ) AS Value ,'banks' as cat, 'Bancos(s)' as category 
						FROM  `bancos`   
						WHERE  ( nome LIKE   '%%%s%%'  OR conta LIKE   '%%%s%%') 
						ORDER BY nome 
						Limit 10 ", $search, $search);
        return $this->formatAutocomplete($sql,'ucfirst' );
    }


    public function action_regimefiscal()
    {		
        //$result= array('0'=>'Nao', '1'=>'Sim');
        $sql= "SELECT  * FROM  NFE.RegimeFiscal ORDER BY Titulo" ;
        $result =  $this->action_connect_SELECT($sql);				
        foreach($result as $arr){
            $array[$arr['POID'].'|'.$arr['Titulo']]= $arr['Titulo']; 
        }
        echo $this->simple_json($array);
        exit;
    }	
    public function action_bancos()
    {		
        //$result= array('0'=>'Nao', '1'=>'Sim');
        $sql= "SELECT  * FROM  bancos WHERE nome > ''  ORDER BY nome" ;
        $result =  $this->action_connect_SELECT($sql);				
        foreach($result as $arr){
            $array[$arr['id'].'|'.$arr['nome']]= $arr['nome'].' | '. $arr['codigo_banco']; 
        }
        echo $this->simple_json($array);
        exit;
    }

    public function action_os_status()
    {		
        $sql= "SELECT id,status  FROM  repair_status ORDER BY status" ;
        $result =  $this->action_connect_SELECT($sql);				
        foreach($result as $arr){
            $array[$arr['id'].'|'.$arr['status']]= $arr['status']; 
        }
        echo $this->simple_json($array);
        exit;
    }



    public function action_warranty()
    {		

        $array['0|À Definir']= 'À Definir'; 
        $array['1|EM GARANTIA']= 'EM GARANTIA'; 
        $array['2|FORA DA  GARANTIA']= 'FORA DA GARANTIA'; 

        echo $this->simple_json($array);
        exit;
    }	

    public function action_cvfiscal()
    {		

        $array['1|Com crédito do Imposto']= 'Com crédito do Imposto'; 
        $array['2|Sem crédito do Imposto - isentas ou não tributadas']= 'Sem crédito do Imposto - isentas ou não tributadas'; 
        $array['3|Sem crédito do Imposto - outras']='Sem crédito do Imposto - outras'; 

        echo $this->simple_json($array);
        exit;
    }	


    public function action_numbers($times=12)
    {		

        for($x=1; $x<=$times; $x++){
            $array[$x.'|'.$x]= $x; 
        }
        echo $this->simple_json($array);
        exit;
    }	

    public function action_times($times=12)
    {		

        for($x=1; $x<=$times; $x++){
            $array[$x.'|'.$x.'x']= $x.'x'; 
        }
        echo $this->simple_json($array);
        exit;
    }	

    public function action_days($times=12)
    {		

        for($x=1; $x<=$times; $x++){
            $array[$x.'|'.$x.' dias']= $x.' dias'; 
        }
        echo $this->simple_json($array);
        exit;
    }	
    public function action_pricelist()
    {		
        $sql= "SELECT * FROM  receita.`pricelist`  ORDER BY modelo" ;
        $result =  $this->action_connect_SELECT($sql);				
        foreach($result as $arr){
            $array[$arr['id'].'|'.$arr['modelo']]= $arr['modelo']; 
        }
        echo $this->simple_json($array);
        exit;
    }


    public function action_importers()
    {		
        $sql= "SELECT * FROM  receita.`importadores`  ORDER BY nome" ;
        $result =  $this->action_connect_SELECT($sql);				
        foreach($result as $arr){
            $array[$arr['id'].'|'.$arr['nome']]= $arr['nome']; 
        }
        echo $this->simple_json($array);
        exit;
    }

    public function action_exporters()
    {		
        $sql= "SELECT * FROM  receita.`exportadores`  ORDER BY nome" ;
        $result =  $this->action_connect_SELECT($sql);				
        foreach($result as $arr){
            $array[$arr['id'].'|'.$arr['nome']]= $arr['nome']; 
        }
        echo $this->simple_json($array);
        exit;
    }


    public function action_receita_cat()
    {		
        $sql= "SELECT * FROM  receita.`categorias`  ORDER BY categoria_nome" ;
        $result =  $this->action_connect_SELECT($sql);				
        foreach($result as $arr){
            $array[$arr['categoria_id'].'|'.$arr['categoria_nome']]= $arr['categoria_nome']; 
        }
        echo $this->simple_json($array);
        exit;
    }


    public function action_product()
    {		
        //$result= array('0'=>'Nao', '1'=>'Sim');
        $sql= "SELECT * FROM  inv  WHERE modelo LIKE '%ZJ-%'  and VIP<9 ORDER BY id" ;
        $result =  $this->action_connect_SELECT($sql);				
        foreach($result as $arr){
            $array[$arr['id'].'|'.$arr['modelo']]= $arr['id'].' | '. $arr['modelo']; 
        }
        echo $this->simple_json($array);
        exit;
    }

    public function action_transp()
    {		
        //$result= array('0'=>'Nao', '1'=>'Sim');
        $sql= "SELECT * FROM  transportadora  ORDER BY id" ;
        $result =  $this->action_connect_SELECT($sql);				
        foreach($result as $arr){
            $array[$arr['id'].'|'.$arr['nome']]= $arr['id'].' | '. $arr['nome']; 
        }
        echo $this->simple_json($array);
        exit;
    }

    public function action_class()
    {		
        //$result= array('0'=>'Nao', '1'=>'Sim');
        $sql= "SELECT * FROM  `clientes_class`  ORDER BY id" ;
        $result =  $this->action_connect_SELECT($sql);				
        foreach($result as $arr){
            $array[$arr['id'].'|'.$arr['class']]= $arr['id'].' | '. $arr['class']; 
        }
        echo $this->simple_json($array);
        exit;
    }
    public function action_noc()
    {		
        //$result= array('0'=>'Nao', '1'=>'Sim');
        $sql= "SELECT  * FROM  ocorrencias WHERE descricao > ''  ORDER BY id" ;
        $result =  $this->action_connect_SELECT($sql);				
        foreach($result as $arr){
            $array[$arr['id'].'|'.$arr['descricao']]= $arr['id'].' | '. $arr['descricao']; 
        }
        echo $this->simple_json($array);
        exit;
    }

    public function action_ncm()
    {		
        //$result= array('0'=>'Nao', '1'=>'Sim');
        $sql= "SELECT  * FROM  produtos WHERE ncm > ''  ORDER BY ncm" ;
        $result =  $this->action_connect_SELECT($sql);				
        foreach($result as $arr){
            $array[$arr['id'].'|'.$arr['ncm']]= $arr['ncm'].' | '. $arr['nome']; 
        }
        echo $this->simple_json($array);
        exit;
    }

    public function action_prazo()
    {	
        
        $this->segmentID = $this->request->param('id');
        //s($this->segmentID);
        
        if ( isset($_REQUEST['query']) )
        {
            $q = $_REQUEST['query'];
        }else{
            $q = '' ;
        }

        $where_in = null;

        if ( $_SESSION['MM_Depto'] == 'VENDAS' and $_SESSION['MM_Nivel'] < 3 ) 
        {
            if (  2 == $this->segmentID ) 
            {
                $where_in = ' AND id in(2,6,7,11,12,13,314,320,224,100,65,14) ';
            }

            if (  6 == $this->segmentID  ) 
            {
                $where_in = ' AND id in(2,6,7,11,12,13,314,320,224,100,65,14) ';
            }

            if ( 5 == $this->segmentID  ) 
            {
                $where_in = ' AND id in(2,6,7,11,12,13,106,318,348) ';
            } 

            if (  3 == $this->segmentID  ) 
            {
               $where_in = ' AND id in(2,6,7,11,12,13,32,39,75,107,109) ';
            } 
            
            if (  1 == $this->segmentID  ) 
            {
               $where_in = ' AND id in(320,109,107,204,13) ';
            } 
        }
        
        //  $segment = $this->request->param('segment');
        //  s($segment);
        //  die();

        $sql= sprintf("SELECT id, terms as name, ativo  FROM  `terms` WHERE  ( terms LIKE '%%%s%%' ) AND ativo = 1 %s ORDER BY terms", $q, $where_in );
        $result =  $this->action_connect_SELECT($sql);	


        $this->response->body(json_encode($result));

        /*  //$result= array('0'=>'Nao', '1'=>'Sim');
        $sql= "SELECT  * FROM  `terms`  ORDER BY terms" ;
        $result =  $this->action_connect_SELECT($sql);				
        foreach($result as $arr){
            $array[$arr['id'].'|'.$arr['terms']]= $arr['terms']; 
        }
        echo $this->simple_json($array);
        exit; */
    }


    public function action_ativo()
    {		
        $array= array('1|ATIVO'=>'ATIVO', '0|INATIVO'=>'INATIVO');
        echo $this->simple_json($array);
        exit;
    }



    public function action_sim()
    {		
        $array= array('1|Sim'=>'Sim', '0|Não'=>'Não');
        echo $this->simple_json($array);
        exit;
    }

    public function action_owner()
    {		
        $array= array('1|Rolemak'=>'Rolemak', '0|Cliente'=>'Cliente');
        echo $this->simple_json($array);
        exit;
    }


    public function action_barcode()
    {	
        $barcode = Request::factory('/barcode/type/')->execute()->response;

        $array[$barcode.'|'.$barcode]= $barcode; 	
        //$array= array($barcode.'=>'.$barcode);
        echo $this->simple_json($array);
        exit;
    }

    public function action_inquiry()
    {		
        //$result= array('0'=>'Nao', '1'=>'Sim');
        $sql= "SELECT  id_inquiry_status as id, inquiry_status FROM  `inquiry_status` ORDER BY inquiry_status" ;
        $result =  $this->action_connect_SELECT($sql);				
        foreach($result as $arr){
            if( strlen($arr['inquiry_status']) >''){
                $array[$arr['id'].'|'.$arr['inquiry_status']]= $arr['inquiry_status']; 
            }
        }
        echo $this->simple_json($array);
        exit;
    }


    public function action_nfs($emit=3)
    {		

        $this->array_emit = $this->companies[$emit];	   
        $date['year']=date('Y');
        $date['month']=date('m');
        $data = $date['month'].'/'.$date['year']; 
        //print_r($this->array_emit ); 	



        $this->tb_entradas	= $this->array_emit['NFE']['entradas'];
        $this->tb_entradas_det = $this->array_emit['NFE']['entradas_det'];

        $sql = sprintf("SELECT POID AS id, CONCAT(c.nome ,'-',a.nNF ) as user,d.nome as cclient, a.*  
						FROM %s a
						LEFT JOIN fornecedores b ON ( a.FornPOID=b.id )
		                LEFT JOIN clientes     c ON ( a.ClientePOID=c.id )
						LEFT JOIN clientes_de_clientes    d ON ( a.cClientePOID=d.id )
						ORDER BY POID DESC
						LIMIT 500", $this->tb_entradas	);		
        $query = DB::query(Database::SELECT, $sql);
        $result = $query->execute(DBS)->as_array();
        //$result= array('0'=>'Nao', '1'=>'Sim');
        //	$sql= sprintf("SELECT  id, nick as user FROM  `users` WHERE ( depto='TECNICO' or level>4) AND trim(user) >''  ORDER BY user ");
        //	$result =  $this->action_connect_SELECT($sql);				
        foreach($result as $arr){
            if( strlen($arr['user']) >''){
                $array[$arr['id'].'|'.$arr['user']]= $arr['user']; 
            }
        }
        echo $this->simple_json($array);
        exit;
    }


    public function action_tecnico()
    {		

        //$result= array('0'=>'Nao', '1'=>'Sim');
        $sql= sprintf("SELECT  id, nick as user FROM  `users` WHERE ( depto='TECNICO' or level>4) AND trim(user) >''  ORDER BY user ");
        $result =  $this->action_connect_SELECT($sql);				
        foreach($result as $arr){
            if( strlen($arr['user']) >''){
                $array[$arr['id'].'|'.$arr['user']]= $arr['user']; 
            }
        }
        echo $this->simple_json($array);
        exit;
    }


    public function action_vd($depto = '')
    {		
        $w= "";
        if($depto<>'')
        {
            $w= sprintf(" AND depto='%s' ",$depto);
        }


        if ( isset($_REQUEST['query']) )
        {
            $q = $_REQUEST['query'];
        }else{
            $q = '' ;
        }

        //$result= array('0'=>'Nao', '1'=>'Sim');
        // $sql= sprintf("SELECT id_payment_type as id, payment_type as name FROM  `payment_types` WHERE  ( payment_type LIKE '%%%s%%' ) ORDER BY payment_type", $q );

        //$result= array('0'=>'Nao', '1'=>'Sim');
        $sql= sprintf("SELECT id, user as name,nick,depto,segmento  FROM  `users` WHERE ( depto='VENDAS' OR depto='FINANCEIRO' OR depto='diretoria')  AND trim(user) >'' %s AND ( user LIKE '%%%s%%' )   ORDER BY user ", $w, $q );
        $result =  $this->action_connect_SELECT($sql);				
        $this->response->body(json_encode($result));
    }

    public function action_fp()
    {		

        if ( isset($_REQUEST['query']) )
        {
            $q = $_REQUEST['query'];
        }else{
            $q = '' ;
        }

        //$result= array('0'=>'Nao', '1'=>'Sim');
        $sql= sprintf("SELECT id_payment_type as id, payment_type as name FROM  `payment_types` WHERE  ( payment_type LIKE '%%%s%%' ) ORDER BY payment_type", $q );
        //$result= array('0'=>'Nao', '1'=>'Sim');
        //$sql= "SELECT  id_payment_type, payment_type FROM  `payment_types` " ;
        $result =  $this->action_connect_SELECT($sql);				

        //foreach($result as $fp){
        //$array[$fp['id_payment_type'].'|'.$fp['payment_type']]= $fp['payment_type']; 
        //}

        $this->response->body(json_encode($result));
    }

    public function action_fornecedores()
    {		
        //$result= array('0'=>'Nao', '1'=>'Sim');
        $sql= "SELECT  id, nome	 FROM  `fornecedores` ORDER BY nome ASC  " ;
        $result =  $this->action_connect_SELECT($sql);				
        foreach($result as $fp){
            $array[$fp['id'].'|'.$fp['nome']]= $fp['nome']; 
        }
        echo $this->simple_json($array);
        exit;
    }

    public function action_despesas()
    {		
        //$result= array('0'=>'Nao', '1'=>'Sim');
        $sql= "SELECT  id, nome	 FROM  `despesas` ORDER BY nome ASC  " ;
        $result =  $this->action_connect_SELECT($sql);				
        foreach($result as $fp){
            $array[$fp['id'].'|'.$fp['nome']]= $fp['nome']; 
        }
        echo $this->simple_json($array);
        exit;
    }

    public function suggestDespesas($search='')
    {		
        //$result= array('0'=>'Nao', '1'=>'Sim');
        $sql= sprintf("SELECT  id, nome as Value  ,'Despesas' as cat, 'Despesas' as category
				FROM  `despesas` 
				WHERE  ( nome LIKE   '%%%s%%'  )
				ORDER BY nome 
				LIMIT 30  ", $search) ;
        $result =  $this->action_connect_SELECT($sql);				
        return $this->formatAutocomplete($sql,'ucfirst' );
    }	

    public function action_centrocusto()
    {		
        //$result= array('0'=>'Nao', '1'=>'Sim');
        $sql= "SELECT  id, nome	 FROM  `centro_de_custos` ORDER BY nome ASC  " ;
        $result =  $this->action_connect_SELECT($sql);				
        foreach($result as $fp){
            $array[$fp['id'].'|'.$fp['nome']]= $fp['nome']; 
        }
        echo $this->simple_json($array);
        exit;
    }

    public function action_tipoconta()
    {		
        //$result= array('0'=>'Nao', '1'=>'Sim');
        $sql= "SELECT  POID id, TipoConta as Conta	 FROM  `bctipo` ORDER BY TipoConta " ;
        $result =  $this->action_connect_SELECT($sql);				
        foreach($result as $fp){
            $array[$fp['id'].'|'.$fp['Conta']]= $fp['Conta']; 
        }
        echo $this->simple_json($array);
        exit;
    }

    public function action_contas()
    {		
        //$result= array('0'=>'Nao', '1'=>'Sim');
        $sql= "SELECT  id, CONCAT(conta,'-',descricao) as Conta	 FROM  `plano_de_contas` ORDER BY conta limit 400  " ;
        $result =  $this->action_connect_SELECT($sql);				
        foreach($result as $fp){
            $array[$fp['id'].'|'.$fp['Conta']]= $fp['Conta'].'('.ucwords(strtolower($fp['id'])).')'; 
        }
        echo $this->simple_json($array);
        exit;
    }

    public function action_segmentos()
    {		
        //$result= array('0'=>'Nao', '1'=>'Sim');
        $sql= "SELECT  id_segments, segment	 FROM  `segments`  " ;
        $result =  $this->action_connect_SELECT($sql);				
        foreach($result as $fp){
            $array[$fp['id_segments'].'|'.$fp['segment']]= $fp['segment']; 
        }
        echo $this->simple_json($array);
        exit;
    }

    public function action_emitente()
    {		
        //$result= array('0'=>'Nao', '1'=>'Sim');
        $sql= "SELECT  EmitentePOID, Fantasia	 FROM  `Emitentes`  " ;
        $result =  $this->action_connect_SELECT($sql);				
        foreach($result as $fp){
            $array[$fp['EmitentePOID'].'|'.$fp['Fantasia']]= $fp['Fantasia']; 
        }
        echo $this->simple_json($array);
        exit;
    }

    public function action_emit()
    {		
        if ( isset($_REQUEST['query']) )
        {
            $q = $_REQUEST['query'];
        }else{
            $q = '' ;
        }

        //$result= array('0'=>'Nao', '1'=>'Sim');
        $sql= sprintf("SELECT EmitentePOID as id, Fantasia as name FROM  `Emitentes`
                        WHERE  ( Fantasia LIKE '%%%s%%' ) and EmitentePOID != 2 and EmitentePOID != 99 and EmitentePOID != 7 and EmitentePOID != 5
                        ORDER BY Fantasia", $q );
        //$sql= "SELECT  EmitentePOID as id, Fantasia as name	 FROM  `Emitentes`   " ;
        $result =  $this->action_connect_SELECT($sql);				

        //foreach($result as $fp){
        //	$array[$fp['EmitentePOID'].'|'.$fp['Fantasia']]= $fp['Fantasia']; 
        //}

        $this->response->body(json_encode($result));
    }

    public function action_origem()
    {		
        $sql = ("SELECT POID as id , LEFT(Description,60) as nome FROM NFE.Origem ORDER BY POID " );
        $result =  $this->action_connect_SELECT($sql);				
        foreach($result as $arr){
            $array[$arr['id'].'|'.$arr['nome']]= $arr['id'].' | '. $arr['nome']; 
        }
        echo $this->simple_json($array);
        exit;
    }	

    public function action_motor()
    {		
        $sql = ("SELECT id, modelo, nome, qtestq FROM  `inv` 
					WHERE  	modelo LIKE  'ZJ-dol%' 
							OR  modelo LIKE  'JY-D%' 
							OR  modelo LIKE  'PL-D%' 
							OR  modelo LIKE  'WR%' 
							OR  nome LIKE  '%control%' 
					ORDER BY modelo Limit 500 " );
        $result =  $this->action_connect_SELECT($sql);				
        foreach($result as $arr){
            $array[$arr['id'].'|'.$arr['modelo']]= $arr['modelo'].' | '. $arr['nome'].' | '. $arr['qtestq'];; 
        }
        echo $this->simple_json($array);
        exit;
    }	


    public function action_ts()
    {		
        $sql = ("SELECT id, modelo,marca, nome, qtestq FROM  `inv` WHERE  modelo LIKE  '%TS%' ORDER BY modelo Limit 500 " );
        $result =  $this->action_connect_SELECT($sql);				
        foreach($result as $arr){
            $array[$arr['id'].'|'.$arr['modelo']]= $arr['modelo'].' | '. $arr['marca'].' | '. $arr['qtestq'];; 
        }
        echo $this->simple_json($array);
        exit;
    }			

    public function action_emit_return()
    {		
        //$result= array('0'=>'Nao', '1'=>'Sim');
        $sql= "SELECT  EmitentePOID, Fantasia	 FROM  `Emitentes`  WHERE EmitentePOID< 3 ORDER BY EmitentePOID ASC " ;
        $result =  $this->action_connect_SELECT($sql);				
        foreach($result as $fp){
            $array[$fp['EmitentePOID'].'|'.$fp['Fantasia']]= $fp['Fantasia']; 
        }
        echo $this->simple_json($array);
        exit;
    }	


    private function simple_json($array) {

        return json_encode($array);//format the array into json data	
    }	







    // search suggest for States
    public function suggestCities($search='') {
        $sql = sprintf("SELECT UCASE(NomeMunic) as Value, '1' as id FROM  estatisticas.`TAB_MUNICIPIOS`  WHERE  NomeMunic LIKE  '%%%s%%' GROUP BY NomeMunic  ", $search );
        return $this->formatAutocomplete($sql);

    }	

  
    
    // search suggest for C/C
    public function suggestCC($search = '', $id) {
        $sql = sprintf(
            "SELECT LCASE(nome) as name, LCASE(cidade) as Cidade, estado as Estado, id, 'cc' as cat, 'Cliente(s)' as category 
             FROM `clientes_de_clientes` 
             WHERE nome LIKE '%%%s%%' and idcli = %s 
             ORDER BY nome 
             LIMIT 15", 
            $search, 
            $id
        );
        
        $result = $this->action_connect_SELECT($sql);
        
        // Adicionar opção "resetar" com id = 0
        array_unshift($result, [
            'name' => 'Remover FD',
            'Cidade' => '',
            'Estado' => '',
            'id' => 0,
            'cat' => 'cc',
            'category' => 'Cliente(s)'
        ]);
    
        echo $this->response->body(json_encode($result));
    }



    // search suggest for Transporter
    public function suggestTransporter($search='', $estado='') {

        if($estado=='') { 
            $state=''; 
        }else{
            $state=" ( estado='".$estado."' OR  estado='' OR  estado='-' or id=15634 or id=15635 or id=15636 or id=16223) AND "; 
        }

        $sql = sprintf("SELECT  CONCAT(nome, ' - ', Cidade, ' - ', Estado, ' ( ', fantasia, ' ) ') AS name, LCASE(cidade) as Cidade, Estado, id
                              FROM  mak.`transportadora`
                              WHERE %s 
                              ( nome LIKE '%%%s%%' or fantasia LIKE '%%%s%%')
                              and ativa =1
                              ORDER BY Nome", $state, $search, $search );


        $result = $this->action_connect_SELECT($sql);

        //s($result);

        return $result;
        //$this->response->body(json_encode($result));
    }	

    // search suggest for Clientes
    public function suggestCustomer($search='') {
        $sql = sprintf("SELECT LCASE(nome) as Nome, LCASE(cidade) as Cidade, Estado, id ,'customer' as cat, 'Cliente(s)' as category, ddd,fone,
		CONTACT (lCASE(atenc),'|||',vendedor )  AS atenc FROM  `clientes`   WHERE  ( nome LIKE   '%%%s%%' or  fantasia LIKE  '%%%s%%' ) AND vip < 9 ORDER BY nome Limit 10 ", $search, $search );
        return $this->formatAutocomplete($sql,'ucfirst' );
    }	

    // search suggest for Products
    public function suggestProducts($search='') {
        $sql = sprintf("SELECT UCASE( modelo) as Modelo, UCASE(marca) as Marca, qtestq as Estoque, id, 'product' as cat, 'Produto(s)' as category FROM  `inv`   WHERE  modelo LIKE  '%%%s%%' ORDER BY modelo Limit 20 ", $search );
        return $this->formatAutocomplete($sql,'ucfirst' );
    }	

    public function suggestMachines($search='') {
        $sql = sprintf("SELECT UCASE( modelo) as Modelo, UCASE(marca) as Marca, qtestq as Estoque, id, 'product' as cat, 'Produto(s)' as category FROM  `inv`   WHERE  ( modelo LIKE  '%%%s%%'  OR nome LIKE  '%%%s%%' )   ORDER BY modelo Limit 20 ", $search );
        return $this->formatAutocomplete($sql,'ucfirst' );
    }	


    private function formatAutocomplete($sql) {

        $array  =  $this->action_connect_SELECT($sql);				
        if( isset($_REQUEST['term'])){
            return $array; //$this->json($array);
        }else{
            if ( count($array) >0 ) 
            {
                $resp='';
                foreach( $array as $key ) 
                {
                    $resp.= ucfirst($key['Value'])."\n";
                }
                return $resp;
            }
        }

    }	

    public function action_delete_cart ()
    {
        $json = Request::factory( $_ENV['api_ecommerce'].'/Carrinhos/'. $this->request->param('id') )
            ->method('DELETE')
            // ->headers("Authorization", $_SESSION['Authorization'])
            ->execute()
            ->body();        

        $this->response->body($json); 
    }

    public function action_stock() {

        $stock_arr= Request::factory('produto/stock/'.$_GET['q'])->execute()->response; 
        print('<pre>');
        print_r($stock_arr);
        //	//	print_r($geral);


    }

    private function json($array) {
        if ( count($array) >0 ) 
        {
            foreach( $array as $row ) 
            {
                switch( $row['cat'] ){
                    case('customer'):
                        //  $value = ucwords($row['Value']); 	
                        $value = ucwords($row['Nome']) ; 	
                        $label = '<table><tr><td>'.ucwords($row['Nome']).'</td></tr>'  ; 	
                        $label.= '<tr><td><span class="smaller grey">'.ucwords($row['Cidade']).''.', '. ($row['Estado']).', '.$row['ddd'].'-'.$row['fone'].''.', '. ucwords($row['atenc']).'</span></td></tr></table>'  ; 	
                        break;

                    case('cc'):
                        //  $value = ucwords($row['Value']); 	
                        $value = ucwords($row['Nome']) ; 	
                        $label = ucwords($row['Nome']); 	
                        $label.= ' | '.ucwords($row['Cidade']).'-'.($row['Estado']); 	

                        $row['txt'] = sprintf('<table><tr><td><span class="green  bold">%s</span></td></tr>', ucwords($row['Nome']))  ; 	
                        $row['txt'].= sprintf('<tr><td><span class="green">%s-%s</span></td></tr></table>', ucwords($row['Cidade']),$row['Estado']  )  ; 	
                        break;

                    case('product'):
                        $stock_arr= Request::factory('produto/stock/'.$row['id'])->execute()->response;
                        $value = $row['Modelo'] ; 	
                        $label = $row['Modelo'] . ' - '. $row['Marca'] ; // . ' (<span class="red small">'.$row['Estoque'].'</span>)' ; 	
                        //if (count($stock_arr)>0) {
                        //							  $label.= '<br>';
                        //							  foreach($stock_arr as $k => $v){
                        //							  	$label.= '<span class="blue smaller">'.$k.'</span> <span class="red small">'.$v['EstoqueDisponivel'].'</span> | ' ;
                        //							  }
                        //						  }
                        break;


                    default: 
                        $value = $row['Value'];
                        if(isset($row['Label']))
                        {
                            $label = $row['Label'];						   	
                        }else{
                            $label = $row['Value'];						   	
                        }
                        break;

                }

                $row['label']=$label;
                $row['value']=$value;
                //$row['value']=htmlentities(stripslashes( $value));
                $row['id']=(int)$row['id'];
                $row['cat']=$row['cat'];
                $row['category']=$row['category'];
                $row_set[] = $row;//build an array
            }
            return json_encode($row_set);//format the array into json data	
        }



    }	



} // End Welcome