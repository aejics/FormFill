<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="/css/main.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</head>
<body>
<br>
<div class='h-100 d-flex align-items-center justify-content-center flex-column'>
<?php
    // Caso não esteja especificado correctamente o ID do formulário (por ex. ?formid=1),
    // matar o script e dar um erro ao utilizador.
    // Esta parte do código foi implementada de maneira a 
    if (!$_GET["formid"]){
        die("<div class='alert alert-danger text-center' role='alert'>
        O formulário não está especificado. Por favor, contacte o administrador.
        </div>");
    }
?>
<?php
    // Importar configurações do formulário
    $formulario = json_decode(file_get_contents("formlist/" . $_GET["formid"] . ".json"));
    // Caso o formulário não exista, matar o script e dar um erro ao utilizador.
    if (!$formulario){
        die("<div class='alert alert-danger text-center' role='alert'>
        O formulário não existe. Por favor, contacte o administrador.
        </div>");
    }
    echo "<title>{$formulario->nome} - FormFill</title>"; // Dar título à página
    echo "<p class='h2 mb-4'>{$formulario->nome}</p>"; // Mostrar título na página
    echo "<p class='mb-4'>{$formulario->subnomePreenchimento}</p>"; // Descrição do formulário para o utilizador
    echo "<form action='devtemp.php' method='post'><input type='hidden' name='formid' value='{$_GET["formid"]}'>";
    // ^ Função HTML para devolver o formulário ao script de documento.
    // Cada campo do formulário gera um label.
    foreach ($formulario->campos as $index=>$quest){
        echo "<div class='mb-3'>";
        echo "<label for='{$quest->idcampo}' class='form-label'>{$quest->descricao}";
        if ($quest->obrigatorio){
            echo " <b class='required'>*</b>";
        };
        echo ":</label>";
        echo "<input type='{$quest->tipo}' class='form-control' id='{$quest->idcampo}' name='{$quest->idcampo}'";
        if ($quest->obrigatorio){
            echo "required";
        }
        echo "></div>";
    }
    // Botão que tem de obrigatoriamente estar no forumlário para submeter.
    echo "<button type='submit' class='btn btn-primary w-100'>Submeter</button>";
?>    
</body>
</html>
