<?php
    require 'login.php';
    $db = new SQLite3('db.sqlite3');
    $db->exec("CREATE TABLE cache_giae (id VARCHAR(99), nome VARCHAR(99), nomecompleto VARCHAR(99), email VARCHAR(99), PRIMARY KEY (id));");
    $db->exec("CREATE TABLE admins (id VARCHAR(99), atividade BOOLEAN, PRIMARY KEY (id));");
    $db->exec("CREATE TABLE respostas (pdf VARCHAR(99) UNIQUE NOT NULL, formid VARCHAR(99) NOT NULL, enviadorid VARCHAR(10) NOT NULL, resposta VARCHAR(999), respondido BOOL, respondidoporid VARCHAR(10), PRIMARY KEY (pdf));"); // Criar tabela para guardar dados

    $dbinfo = $db->query("SELECT * FROM cache_giae WHERE id = '{$_COOKIE["user"]}'");
    $nome = utf8_encode($dbinfo->fetchArray()[1]);
    $db->close();
    if (isset($_COOKIE["loggedin"])) {
        echo("<div class='h-100 d-flex align-items-center justify-content-center flex-column'>
            <p class='h2 mb-4'>Bem-vindo, <b>{$nome}</b></p>");
        $formularios = scandir("formlist");
        foreach ($formularios as $formularioatual){
            $formularioatual = preg_replace('/.json$/', '', $formularioatual);
            $configformularioatual = json_decode(file_get_contents("formlist/{$formularioatual}.json"));
            if ($configformularioatual->ativado){
                if ($formularioatual == "." || $formularioatual == ".." || $formularioatual == "exemplo.json" || $formularioatual == ".htaccess") {continue;};      
                echo("
                <button type='button' class='btn btn-secondary btn-lg btn-block' onclick='window.open(\"form.php?formid={$formularioatual}\", \"popup\", \"width=800,height=600,scrollbars=yes,resizable=yes\")' >
                {$configformularioatual->nome}
                <p class='h6'>{$configformularioatual->descricaoListaFormularios}</p></button><hr>
                ");
            }
        }
    };
    require 'src/footer.php';
?>
    </body>
</html>