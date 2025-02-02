<?php
  // Esta parte de configuração serve para mostrar uma mensagem no topo do painel.
  $mensagem_ativada = false;
  $mensagem_header = "Este painel está em desenvolvimento pesado. Bugs? Report an issue on GitHub!";
  $mensagem_tipo = "info";
  // ^^ Tipos de mensagem: primary, secondary, success, danger, warning, info, light, dark
  if ($mensagem_ativada){
    echo "<div class='alert alert-" . $mensagem_tipo . " alert-dismissible fade show text-center' role='alert'>" . $mensagem_header . "
    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Fechar'></button></div>";
  }
?>
<?php
  $db = new SQLite3('db.sqlite3');
  $isAdmin = $db->querySingle("SELECT * from admins WHERE id = '{$_COOKIE["user"]}' AND atividade = true");
  $db->close();
  echo "<nav class='navbar navbar-expand-lg navbar-light bg-light justify-content-center'>
  <a class='navbar-brand' href='/'>FormFill</a>
  <div class='dropdown'>";
  if (isset($_COOKIE["loggedin"])){
    require_once(__DIR__ . "/../vendor/autoload.php");
    $giae = new \juoum\GiaeConnect\GiaeConnect("giae.aejics.org");
    $giae->session=$_COOKIE["session"];
    $config = json_decode($giae->getConfInfo(), true);

    echo "<button class='btn btn-secondary dropdown-toggle' type='button' id='areaMenuButton' data-bs-toggle='dropdown' aria-expanded='false'>
      <img class='fotoutente' src='https://giae.aejics.org/" . $config['fotoutente'] . "'>  A Minha Área
      </button>
      <ul class='dropdown-menu' aria-labelledby='dropdownMenuButton'>
      <li><a class='dropdown-item' href='/'>Preencher Formulários</a></li>";
    if ($isAdmin) {
      echo "<li><a class='dropdown-item' href='/admin.php'>Painel Administrativo</a></li>";
    }
    echo "<li><a class='dropdown-item' href='/login.php?action=logout'>Terminar sessão</a></li>";
    echo "</ul>
    </div>";
  }
  echo "</nav>";
?>