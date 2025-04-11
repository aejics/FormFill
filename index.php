<?php
    require 'login.php';
    $dbinfo = $db->query("SELECT * FROM cache_giae WHERE id = '{$_COOKIE["user"]}'");
    $nome = utf8_encode($dbinfo->fetch_assoc()['nome']);
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