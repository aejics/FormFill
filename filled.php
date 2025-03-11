<?php
    require('login.php');
    require_once(__DIR__ . "/vendor/autoload.php");
    class PDF extends FPDF
    {
        // Gerar documento do formulário preenchido
        function criarDocumento($titulo, $texto)
        {
            mkdir ('filledforms');
            $this->AddPage();
            $this->SetXY(10, 40);
            $this->Image("src/logoaejics.png", 10, 10, 20);
            $this->Image("src/logominedu.png", 130, 15, 65);
            $this->SetFont('Arial', 'B', 20);
            $this->Cell(0, 10, $titulo, 0, 1, 'C', false);
            $this->SetFont('Arial', '', 13);
            $this->MultiCell(0, 10, $texto, 0, 'J');      
            $nomeficheiro = 'filledforms/' . date('YmdHisv') . ".pdf";
            $this->Output('F', $nomeficheiro);
            return $nomeficheiro;
        }
    }
    $pdf = new PDF();
    include 'mail.php';
    $formid = filter_input(INPUT_POST, 'formid', FILTER_UNSAFE_RAW); // Recolher o ID do Formulário
    $formulario = json_decode(file_get_contents("formlist/" . $_POST["formid"] . ".json")); // Com esse ID, buscar config do formulário
    $texto = $formulario->doc->texto; // Texto que está na config
    $textomail = $formulario->emailtext->confirmacao; // Texto do mailque está na config
    $db = new SQLite3('db.sqlite3'); // Conectar à Base de Dados
    $user = filter_input(INPUT_COOKIE, 'user', FILTER_UNSAFE_RAW); // Ir buscar ID do utilizador
    $dbresult = $db->query("SELECT * FROM cache_giae WHERE id = '{$user}'"); // Buscar dados à DB
    $dbArray = $dbresult->fetchArray(); // Colocar dados num array
    $nomecompleto = utf8_encode($dbArray[2]); // Nome Completo do Utilizador
    $nome = utf8_encode($dbArray[1]); // Nome do Utilizador
    $id = utf8_encode($dbArray[0]); // ID do Utilizador
    $email = utf8_encode($dbArray[3]); // Email do Utilizador
    // Data de Hoje (# é dados do Sistema)
    $texto = str_replace('#data#', utf8_encode(date('d/m/Y')), $texto);
    $textomail = str_replace('#data#', utf8_encode(date('d/m/Y')), $textomail);
    // Dados da Base de Dados
    $texto = str_replace('§nomecompleto§', $nomecompleto, $texto);
    $textomail = str_replace('§nomecompleto§', $nomecompleto, $textomail);
    $texto = str_replace('§nome§', $nome, $texto);
    $textomail = str_replace('§nome§', $nome, $textomail);
    $texto = str_replace('§id§', $id, $texto);
    $textomail = str_replace('§id§', $id, $textomail);
    $texto = str_replace('§email§', $email, $texto);
    $textomail = str_replace('§email§', $email, $textomail);
    // Campos do Formulário ()
    foreach ($formulario->campos as $index=>$quest){
        $texto = str_replace("&" . $quest->idcampo . "&", utf8_encode(filter_input(INPUT_POST, $quest->idcampo, FILTER_UNSAFE_RAW)), $texto);
        $textomail = str_replace("&" . $quest->idcampo . "&", utf8_encode(filter_input(INPUT_POST, $quest->idcampo, FILTER_UNSAFE_RAW)), $textomail);
    }
    // Título do Formulário
    $titulo = $formulario->nome;
    // Local do PDF preenchido
    $linkpdfpreenchido = $pdf->criarDocumento(utf8_decode($titulo), utf8_decode($texto));
    // Guardar dados na Base de Dados
    $dbInput = $db->prepare("INSERT INTO respostas (pdf, formid, enviadorid, respondido) VALUES (:pdf, :formid, :enviadorid, false)"); // Inserir dados na DB
    $dbInput->bindValue(':formid', $formid); // Formulário ID
    $dbInput->bindValue(':enviadorid', $user); // Utilizador ID
    $dbInput->bindValue(':pdf', $linkpdfpreenchido); // PDF
    $dbInput->execute(); // Executar
    $db->close();
    // Enviar email com o PDF preenchido
    sendMail($linkpdfpreenchido, $email, $formulario->emailtext->assuntoconfirmacao, $textomail);
?>
    <h3 class="text-center">Formulário preenchido com sucesso!</h1>
    <p class='font-weight-light text-center'>Foi lhe enviado uma cópia do documento preenchido por email.</p>
    <iframe src="<?php echo($linkpdfpreenchido); ?>" type="application/pdf" width="100%" height="400px"></iframe>
</body>
</html>