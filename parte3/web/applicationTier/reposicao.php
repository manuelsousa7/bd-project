<html>
    <body>
<?php
    $ean = $_REQUEST['EAN_Reposicao'];

    if ($ean == "") {
        echo("<p>EAN vazio<p>");
        return;
    }
    
    try
    {
        $host = "db.ist.utl.pt";
        $user ="istxxxxx";
        $password = "xxxxxxx";
        $dbname = $user;
        $db = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql = "SELECT * FROM Reposicao WHERE ean = '$ean';";
                
        $result = $db->query($sql);

        if($result->rowCount()==0){
            echo ("<p> Nenhuma reposicao encontrada para o produto com EAN = {$ean}");
        }

        echo("<table border=\"0\" cellspacing=\"5\">\n");
        foreach($result as $row)
        {
            echo("<tr>\n");
            echo("<td>{$row['ean']}</td>\n");
            echo("<td>{$row['nro']}</td>\n");
            echo("<td>{$row['lado']}</td>\n");
            echo("<td>{$row['altura']}</td>\n");
            echo("<td>{$row['operador']}</td>\n");
            echo("<td>{$row['instante']}</td>\n");
            echo("<td>{$row['unidades']}</td>\n");
            echo("</tr>\n");
        }
        echo("</table>\n");

        $db = null;
    }
    catch (PDOException $e)
    {
        echo("<p>ERROR: {$e->getMessage()}</p>");
    }
?>
    </body>
</html>