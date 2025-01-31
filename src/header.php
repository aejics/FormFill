<?php 
  echo "<div class='alert alert-warning alert-dismissible fade show text-center' role='alert'>
  Este painel está em desenvolvimento, e não está estável para uma utilização em prod.
  <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Fechar'></button>
</div>";
?>
<?php
  echo "<nav class='navbar navbar-expand-lg navbar-light bg-light justify-content-center'>
  <a class='navbar-brand' href='/'>FormFill</a>
  <div class='dropdown'>";
  if (isset($_COOKIE["loggedin"])){
    require_once(__DIR__ . "/../vendor/autoload.php");
    $giae = new \juoum\GiaeConnect\GiaeConnect("giae.aejics.org");
    $giae->session=$_COOKIE["session"];
    $config = json_decode($giae->getConfInfo(), true);
    $fotoutente = json_decode('"' . $config['fotoutente'] . '"'); // Decode Unicode da Foto do Utente
    echo "<button class='btn btn-secondary dropdown-toggle' type='button' id='areaMenuButton' data-bs-toggle='dropdown' aria-expanded='false'>
      <img class='fotoutente' src='https://giae.aejics.org/" . $fotoutente . "'>  A Minha Área
      </button>
      <ul class='dropdown-menu' aria-labelledby='dropdownMenuButton'>
      <li><a class='dropdown-item' href='/logout.php'>Terminar sessão</a></li>
    </ul>
    </div>";
  }
  echo "</nav>";
?>