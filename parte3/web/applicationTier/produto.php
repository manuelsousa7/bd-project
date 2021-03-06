<html>
<head>
    <meta charset="utf-8">
    <title> Projeto de BD </title>
    <link rel="stylesheet" href="style.css">
                        <!-- load Ink's CSS -->
                        <link rel="stylesheet" type="text/css" href="../ink/css/ink-flex.min.css">
    <link rel="stylesheet" type="text/css" href="../ink/css/font-awesome.min.css">

    <!-- load Ink's CSS for IE8 -->
    <!--[if lt IE 9 ]>
        <link rel="stylesheet" href="../css/ink-ie.min.css" type="text/css" media="screen" title="no title" charset="utf-8">
    <![endif]-->

    <!-- test browser flexbox support and load legacy grid if unsupported -->
    <script type="text/javascript" src="../ink/js/modernizr.js"></script>
    <script type="text/javascript">
        Modernizr.load({
          test: Modernizr.flexbox,
          nope : '../ink/css/ink-legacy.min.css'
        });
    </script>

    <!-- load Ink's javascript files -->
    <script type="text/javascript" src="../ink/js/holder.js"></script>
    <script type="text/javascript" src="../ink/js/ink-all.min.js"></script>
    <script type="text/javascript" src="../ink/js/autoload.js"></script>

    <style type="text/css">

        body {
            background: #ededed;
        }

        header {
            border-bottom: 1px solid #cecece;
        }

        footer {
            background: #ccc;
        }

    </style>
</head>
    <body>
<?php

    $hadProblem = false;
    
    function doesCatExist($catName,$database){
        $sql = "SELECT * FROM Categoria WHERE nome = '$catName';";
        $result = $database->query($sql);

        if(($result->rowCount()==0)){
            return false;
        }
        return true;
    }
    function isRealDate($date) { 
        if (false === strtotime($date)) { 
            return false;
        } 
        list($year, $month, $day) = explode('-', $date); 
        return checkdate($month, $day, $year);
    }

    function doesSupplierExist($supplierNif,$database){
        $sql = "SELECT * FROM Fornecedor WHERE nif = '$supplierNif';";
        $result = $database->query($sql);

        if(($result->rowCount()==0)){
            return false;
        }
        return true;
    }

    function doQuery($query,$database){
        try{
            $database->query($query);
        }

        catch(PDOException $e){
            $hadProblem = true;
            $db->query("ROLLBACK;");
            echo("<div class=\"ink-alert basic error\" role=\"alert\">
            <button class=\"ink-dismiss\">&times;</button>
            <p><b>Erro:</b>ERROR In Query({$query}): {$e->getMessage()}</p>
            </div>");
        }
    }
    //TODO: Verificar quando os secondary supplier vem vazio
    function postSecondarySuppliers($supliers,$supliersName,$ean,$database){
        $sups = $supliers;
        $noms = $supliersName;
        $position = 0;

        if(count($sups) != count($noms) or count($sups) == 0 ){
            $hadProblem = true;
            $db->query("ROLLBACK;");
            echo("<div class=\"ink-alert basic error\" role=\"alert\">
            <button class=\"ink-dismiss\">&times;</button>
            <p><b>Erro:</b>ERROR: Insert the correct number of names and nifs</p>
            </div>");
            return;
        }

        foreach($sups as $suplier){

            if(!doesSupplierExist($suplier,$database)){
                
                $request = "INSERT INTO Fornecedor VALUES('$suplier','$noms[$position]');";
                doQuery($request,$database);
            }

            $request = "INSERT INTO Fornecedor_secundario VALUES('$suplier','$ean');";

            doQuery($request,$database);

            $position = $position + 1;

        }
    }

    function postProduct($ean,$designacao,$categoria,$primarioNif,$primarioNome,$secundarySuppliersNif,$secundarySuppliersName,$data,$database){
        
        postPrimarySupplier($primarioNif,$primarioNome,$database);
        $sql = "INSERT INTO Produto VALUES('$ean', '$categoria', '$primarioNif', '$designacao', '$data');";
        doQuery($sql,$database);
        postSecondarySuppliers($secundarySuppliersNif,$secundarySuppliersName,$ean,$database);

    }

    function postPrimarySupplier($primarioNif,$primarioNome,$database){
        if(! doesSupplierExist($primarioNif,$database)){
            $request = "INSERT INTO Fornecedor VALUES('$primarioNif','$primarioNome');";
            doQuery($request,$database);
        }
    }

    $ean = $_REQUEST['EAN'];
    $designacao = $_REQUEST['Designacao'];
    $categoria = $_REQUEST['Categoria'];
    $primarioNif = $_REQUEST['FornecedorPrimarioNif'];
    $primarioNome = $_REQUEST['FornecedorPrimarioNome'];
    $secundarios = $_REQUEST['FornecedoresSecundarios'];

    if ($ean == "") {
        echo("<div class=\"ink-alert basic error\" role=\"alert\">
        <button class=\"ink-dismiss\">&times;</button>
        <p><b>Erro:</b>[ERRO] EAN vazio</p>
        </div>");
        echo("<button class=\"ink-button orange\" onclick='window.history.back()' style='float:left; clear:both'>Voltar</button>");
        return;
    }
    if ($designacao == "") {
        echo("<div class=\"ink-alert basic error\" role=\"alert\">
        <button class=\"ink-dismiss\">&times;</button>
        <p><b>Erro:</b>[ERRO] Designacao vazia</p>
        </div>");
        echo("<button class=\"ink-button orange\" onclick='window.history.back()' style='float:left; clear:both'>Voltar</button>");
        return;
    }
    if ($categoria == "") {
        echo("<div class=\"ink-alert basic error\" role=\"alert\">
        <button class=\"ink-dismiss\">&times;</button>
        <p><b>Erro:</b>[ERRO] Categoria vazio</p>
        </div>");
        echo("<button class=\"ink-button orange\" onclick='window.history.back()' style='float:left; clear:both'>Voltar</button>");
        return;
    }
    if ($primarioNif == "") {
        echo("<div class=\"ink-alert basic error\" role=\"alert\">
        <button class=\"ink-dismiss\">&times;</button>
        <p><b>Erro:</b>[ERRO] FornecedorPrimarioNif vazio</p>
        </div>");
        echo("<button class=\"ink-button orange\" onclick='window.history.back()' style='float:left; clear:both'>Voltar</button>");
        return;
    }
    if ($primarioNome == "") {
        echo("<div class=\"ink-alert basic error\" role=\"alert\">
        <button class=\"ink-dismiss\">&times;</button>
        <p><b>Erro:</b>[ERRO]FornecedorPrimarioNome vazio</p>
        </div>");
        echo("<button class=\"ink-button orange\" onclick='window.history.back()' style='float:left; clear:both'>Voltar</button>");
        return;
    }
 
    $data = $_REQUEST['DataProduto'];

    #echo("<script> console.log({$primarioNif})</script>");

    $secundarySuppliersNif = array();
    $int = intval($secundarios); 
    for($i = 1; $i <= $int; ++$i) {
        $index = $i;
        $string = 'FornecedorSecundarioNif'.$index;
        $value = $_REQUEST[$string];
        if($value != $primarioNif && $value != ""){
            array_push($secundarySuppliersNif,$value);   
        }
    }
    if(count($secundarySuppliersNif) == 0){
        echo("<div class=\"ink-alert basic error\" role=\"alert\">
        <button class=\"ink-dismiss\">&times;</button>
        <p><b>Erro:</b>[ERRO] Insira pelo menos um fornecedor secundario diferente do primario</p>
        </div>");
        echo("<button class=\"ink-button orange\" onclick='window.history.back()' style='float:left; clear:both'>Voltar</button>");
        return;
    }
    
    $i = 1;
    $secundarySuppliersName = array();
    $int = intval($secundarios);
    for($i = 1; $i <= $int; ++$i) {
        $index = $i;
        $string = 'FornecedorSecundarioNome'.$index;
        $value = $_REQUEST[$string];
        if($value != ""){
            array_push($secundarySuppliersName, $value);
        }
    }

    if(count($secundarySuppliersName) != count($secundarySuppliersNif)){
        echo("<div class=\"ink-alert basic error\" role=\"alert\">
        <button class=\"ink-dismiss\">&times;</button>
        <p><b>Erro:</b>[ERRO] Insira a mesma quantidade de nomes e de nifs</p>
        </div>");
        echo("<button class=\"ink-button orange\" onclick='window.history.back()' style='float:left; clear:both'>Voltar</button>");
        return; 
    }

    if(!isRealDate($data)){
        echo("<div class=\"ink-alert basic error\" role=\"alert\">
        <button class=\"ink-dismiss\">&times;</button>
        <p><b>Erro:</b>[ERRO] Data inserida nao valida</p>
        </div>");
        echo("<button class=\"ink-button orange\" onclick='window.history.back()' style='float:left; clear:both'>Voltar</button>");
        return;
    }
    if(!doesCatExist($categoria,$db)){
        echo("<div class=\"ink-alert basic error\" role=\"alert\">
        <button class=\"ink-dismiss\">&times;</button>
        <p><b>Erro:</b>[ERRO] Categoria inserida nao existe</p>
        </div>");
        echo("<button class=\"ink-button orange\" onclick='window.history.back()' style='float:left; clear:both'>Voltar</button>");
        return;
    }

    else {
        try {
            $host = "db.ist.utl.pt";
            $user ="ist426040";
            $password = "lkdy2200";
            $dbname = $user;
            $db = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $db->query("START TRANSACTION;");

            $sql = "SELECT * FROM Reposicao WHERE ean = '$ean';";
                
            $result = $db->query($sql);

            if($result->rowCount()>0){
                $db->query("ROLLBACK;");
                echo("<div class=\"ink-alert basic error\" role=\"alert\">
                <button class=\"ink-dismiss\">&times;</button>
                <p><b>Erro:</b>[ERRO] Produto com EAN = {$ean} ja existe</p>
                </div>");
                echo("<button class=\"ink-button orange\" onclick='window.history.back()' style='float:left; clear:both'>Voltar</button>");
                return;
            }

            postProduct($ean,$designacao,$categoria,$primarioNif,$primarioNome,$secundarySuppliersNif,$secundarySuppliersName,$data,$db);
            
            $db->query("COMMIT;");
            $db = null;
            if (!$hadProblem){
                echo("<div class=\"ink-alert basic success\" role=\"alert\">
                <button class=\"ink-dismiss\">&times;</button>
                <p><b>Sucesso:</b>Insercao foi um sucesso</p>
                </div>");
            }
            echo("<button class=\"ink-button orange\" onclick='window.history.back()' style='float:left; clear:both'>Voltar</button>");
        }
        catch (Exception $e) {
            $db->query("ROLLBACK;");
            echo("<div class=\"ink-alert basic error\" role=\"alert\">
            <button class=\"ink-dismiss\">&times;</button>
            <p><b>Erro:</b> GeneralArea: {$e->getMessage()}</p>
            </div>");
            echo("<button class=\"ink-button orange\" onclick='window.history.back()' style='float:left; clear:both'>Voltar</button>");
        }
    }
?>
    </body>
</html>
