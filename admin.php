<?php
    require 'login.php';
    $db = new SQLite3('db.sqlite3');
    $dbinfo = $db->query("SELECT * FROM admins WHERE id = '{$_COOKIE["user"]}'");
    if (!$dbinfo) {
        echo "An error occurred.\n";
        exit;
    }
    if ($dbinfo->fetchArray()['atividade'] == 1) {
        $db->close();
        if (!$_GET['action']){
        // Sidebar
            echo "</div><div class='d-flex' style='height: 85vh;'>
                <div class='flex-shrink-0 p-3 text-bg-dark' style='width: 280px;'>
                <h1>Administração</h1>        
                <ul class='nav nav-pills flex-column mb-auto'>
                    <li class='nav-item'>
                    <a href='/admin.php' class='nav-link "; if ($_SERVER['REQUEST_URI'] == "/admin.php") {echo "active";};
                    echo"'>
                        Dashboard</a></li>
                    <li>
                    <a href='/admin.php?section=manutencao' class='nav-link "; if ($_SERVER['REQUEST_URI'] == "/admin.php?section=manutencao") {echo "active";};
                    echo"'>
                        Manutenção</a></li>
                    <li>
                    <a href='/admin.php?section=forms' class='nav-link "; if ($_SERVER['REQUEST_URI'] == "/admin.php?section=forms") {echo "active";};
                    echo"'>
                        Formulários</a></li>
                    <li>
                    <a href='/admin.php?section=acessoaopainel' class='nav-link "; if ($_SERVER['REQUEST_URI'] == "/admin.php?section=acessoaopainel") {echo "active";};
                    echo"'>
                        Gestão de Administradores</a></li>
                    <li>
                </ul>
                </div><div class='flex-grow-1 d-flex align-items-center justify-content-center flex-column'>";
        } 
        if (!$_GET['action'] && !$_GET['section']){
            echo "<button type='button btn-primary' class='btn btn-primary w-30' onclick='window.open(\"admin.php?action=respostaspendentes\", \"popup\", \"width=1200,height=600,scrollbars=yes,resizable=yes\")'>Gestão de Respostas</button>";
        }else if ($_GET['action'] == "respostaspendentes") {
            $db = new SQLite3('db.sqlite3');
            // Responstas pendentes
            echo "<h1>Respostas pendentes</h1>";
            echo "<div class='d-flex justify-content-between mb-3'><form action='/admin.php?action=respostaspendentes' method='POST'>
                <div class='form-floating'>
                <div class='input-group'>
                    <input type='text' class='form-control' id='buscamanual' name='buscamanual' placeholder='Realizar busca por ID'>
                    <button type='submit' class='btn btn-primary'>Buscar</button>
                </div>
                </form></div></div>";

            if ($_POST['buscamanual']) {
                $buscamanual = $_POST['buscamanual'];
                $resultadosbusca = $db->query("SELECT * from respostas WHERE enviadorid LIKE '%{$buscamanual}%' ORDER BY pdf ASC");
                echo "<p>Busca manual por: {$buscamanual}</p>";
                echo "<table class='table table-striped table-hover'><thead><tr><th scope='col'>ID</th><th scope='col'>Formulário</th><th scope='col'>Enviado por</th><th scope='col'>Ações</th></tr></thead><tbody>";
                while ($row = $resultadosbusca->fetchArray()){
                    $nome = $db->querySingle("SELECT nome FROM cache_giae WHERE id = '{$row[2]}'");
                    $nomeformulario = json_decode(file_get_contents("formlist/{$row[1]}.json"))->nome;
                    echo "<tr>
                    <th scope='row'>{$row[0]}</th>
                    <td>{$nomeformulario} <i>(#{$row[1]})</i></td>
                    <td>{$nome} <i>($row[2])</i></td>";
                    if ($row[4]){
                        $nomerespondedor = $db->querySingle("SELECT nome FROM cache_giae WHERE id = '{$row[5]}'");
                        echo "<td>Sim (por {$nomerespondedor} <i>({$row[5]})</i>)</td>";
                    } else {
                        echo "<td>Não</td>";
                    }    
                    echo "<td><a onclick='window.open(\"/admin.php?action=viewform&formid={$row[0]}\", \"popup\", \"width=800,height=600,scrollbars=yes,resizable=yes\")' class='btn btn-primary'>Ver</a></td>
                    </tr>";    
                }
                echo "</table>";
            }
            echo "<hr><h4>Respostas pendentes:</h4>";
            $prenchidos = $db->query("SELECT * FROM respostas WHERE respondido = false ORDER BY pdf ASC");
            echo "<table class='table table-striped table-hover'><thead><tr><th scope='col'>ID</th><th scope='col'>Formulário</th><th scope='col'>Enviado por</th><th scope='col'>Respondido</th><th scope='col'>Ações</th></tr></thead><tbody>";
            while ($row = $prenchidos->fetchArray()) {
                $nome = $db->querySingle("SELECT nome FROM cache_giae WHERE id = '{$row[2]}'");
                $nomeformulario = json_decode(file_get_contents("formlist/{$row[1]}.json"))->nome;
                echo "<tr>
                <th scope='row'>{$row[0]}</th>
                <td>{$nomeformulario} <i>(#{$row[1]})</i></td>
                <td>{$nome} <i>($row[2])</i></td>";
                if ($row[4]){
                    $nomerespondedor = $db->querySingle("SELECT nome FROM cache_giae WHERE id = '{$row[5]}'");
                    echo "<td>Sim (por {$nomerespondedor} <i>({$row[5]})</i>)</td>";
                } else {
                    echo "<td>Não</td>";
                }
                echo "<td><a onclick='window.open(\"/admin.php?action=viewform&formid={$row[0]}\", \"popup\", \"width=800,height=600,scrollbars=yes,resizable=yes\")' class='btn btn-primary'>Ver</a></td>
                </tr>";
            }
            echo "</table>";
            $db->close();
        } else if ($_GET['action'] == "viewform") {
            $db = new SQLite3('db.sqlite3');
            $formid = $_GET['formid'];
            $iddoform = $db->querySingle("SELECT formid FROM respostas WHERE pdf = '{$formid}'");
            if ($_POST['resposta']) {
                $valordb = $db->prepare("UPDATE respostas SET resposta = :resposta, respondido = true, respondidoporid = :respondidoporid WHERE pdf = :pdf");
                $valordb->bindValue(':resposta', utf8_decode($_POST['resposta']), SQLITE3_TEXT);
                $valordb->bindValue(':respondidoporid', $_COOKIE['user'], SQLITE3_TEXT);
                $valordb->bindValue(':pdf', $_GET['formid'], SQLITE3_TEXT);
                $valordb->execute();
                
                echo "<div class='alert alert-success text-center' role='alert'>Resposta enviada com sucesso.</div>
                        <div class='text-center'>
                        <a href='/admin.php?action=respostaspendentes'><button type='button' class='btn btn-primary w-100' >Voltar</button></a></div>";
                include 'mail.php';
                $email = $db->querySingle("SELECT email FROM cache_giae WHERE id = '{$db->querySingle("SELECT enviadorid FROM respostas WHERE pdf = '{$formid}'")}'");
                $nome = $db->querySingle("SELECT nome FROM cache_giae WHERE id = '{$db->querySingle("SELECT enviadorid FROM respostas WHERE pdf = '{$formid}'")}'");
                $nomecompleto = $db->querySingle("SELECT nomecompleto FROM cache_giae WHERE id = '{$db->querySingle("SELECT enviadorid FROM respostas WHERE pdf = '{$formid}'")}'");
                $id = $db->querySingle("SELECT enviadorid FROM respostas WHERE pdf = '{$formid}'");
                $assunto = json_decode(file_get_contents("formlist/{$iddoform}.json"))->emailtext->assuntonotificacao;
                $textomail = json_decode(file_get_contents("formlist/{$iddoform}.json"))->emailtext->notificacao;
                $textomail = str_replace('#data#', utf8_encode(date('d/m/Y')), $textomail);
                $textomail = str_replace('§nomecompleto§', $nomecompleto, $textomail);
                $textomail = str_replace('§nome§', $nome, $textomail);
                $textomail = str_replace('§id§', $id, $textomail);
                $textomail = str_replace('§email§', $email, $textomail);
                $textomail = str_replace('§resposta§', utf8_decode($_POST['resposta']), $textomail);
                sendMail($formid, $email, $assunto, $textomail);
                $db->close();
                die();
            }
            $infoform = $db->query("SELECT * FROM respostas WHERE pdf = '{$formid}'");
            $infoform = $infoform->fetchArray();
            $nome = $db->querySingle("SELECT nome FROM cache_giae WHERE id = '{$infoform[2]}'");
            echo "Visualização de Preenchimento: {$nome} ({$infoform[2]})";
            echo "<iframe src='/{$formid}' type='application/pdf' width='100%' height='350px'></iframe>";
            echo "<div class='text-center'>";
            if (!$infoform[4]){
                // Estiver por responder:
                echo "<p>Estado: <b class='required'>Por responder</b></p>";
                echo "<form action='/admin.php?action=viewform&formid={$formid}' method='POST'>";
                echo "<h5>Resposta enviada: </h5><br><textarea id='resposta' name='resposta' cols='60' rows='10'></textarea>";
                echo "<button type='submit' class='btn btn-primary w-100'>Enviar Resposta</button></form>";
            }
            if ($infoform[4]){
                // Já tiver sido respondido:
                $nomerespondedor = $db->querySingle("SELECT nome FROM cache_giae WHERE id = '{$infoform[5]}'");
                echo "<p>Estado: <b class='respondido'>Respondido</b> (por {$nomerespondedor} <i>({$infoform[5]})</i>)</p>";
                echo "<h5>Resposta enviada: </h5><br><textarea id='resposta' name='resposta' disabled cols='60' rows='10' placeholder='{$infoform[3]}'></textarea>";
            }
            
        } else if ($_GET['section'] == "manutencao") {
            // Opções de Manutenção
            echo "<div class='flex-grow-1 d-flex align-items-center justify-content-center flex-column'>";
            echo "<h1>Manutenção</h1><hr>";
            echo "<button type='button btn-primary' class='btn btn-primary w-30' onclick='window.open(\"admin.php?action=gestao_cache\", \"popup\", \"width=1200,height=600,scrollbars=yes,resizable=yes\")'>Gestão Manual da Cache do GIAE</button>";
            echo "<hr>";
            echo "<button type='button btn-primary' class='btn btn-primary w-30' onclick='window.open(\"admin.php?action=gestao_respostas\", \"popup\", \"width=1200,height=600,scrollbars=yes,resizable=yes\")'>Gestão Manual das Respostas</button>";
        } else if ($_GET['section'] == "forms") {
            echo "<h1>Formulários</h1>";
            $formularios = scandir("formlist");
            echo "<div class='d-flex justify-content-between mb-3'>
                    <button type='button' class='btn btn-success' onclick='window.open(\"admin.php?action=upload_form\", \"popup\", \"width=1200,height=600,scrollbars=yes,resizable=yes\")'>📤 Carregar Formulário</button>
                    <a href='/formlist/exemplo.json' target='_blank' class='link-light'><button type='button' class='btn btn-success ms-2'>Formulário de Exemplo</button></a>
                  </div><hr>";
            echo "<table class='table'><tr><th scope='col'>ID</th><th scope='col'>NOME</th><th scope='col'>ATIVADO</th><th scope='col'>AÇÕES</th></tr>";
            foreach ($formularios as $formularioatual){
                if ($formularioatual == "." || $formularioatual == ".." || $formularioatual == "exemplo.json" || $formularioatual == ".htaccess") {continue;};
                $formularioatual = preg_replace('/.json$/', '', $formularioatual);
                $configformularioatual = json_decode(file_get_contents("formlist/{$formularioatual}.json"));
                echo "<tr><td>$formularioatual</td><td>$configformularioatual->nome</td><td>"; if ($configformularioatual->ativado) {echo "Sim";} else {echo "Não";}; echo "</td><td><a href='/formlist/{$formularioatual}.json' target='_blank'>DESCARREGAR</a> <a href='/form.php?formid={$formularioatual}' target='_blank'>TESTAR</a> <a href='/admin.php?action=toggle_form&formid={$formularioatual}'>TOGGLE</a>  <a href='/admin.php?action=delete_form&formid=$formularioatual'>APAGAR</a></td></tr>";
            }
        } else if ($_GET['action'] == "toggle_form") {
            $configformularioatual = json_decode(file_get_contents("formlist/{$_GET['formid']}.json"));
            if ($configformularioatual->ativado) {
                $configformularioatual->ativado = false;
            } else {
                $configformularioatual->ativado = true;
            }
            file_put_contents("formlist/{$_GET['formid']}.json", json_encode($configformularioatual));
            echo "<div class='alert alert-success text-center' role='alert'>Formulário toggle com sucesso.</div><div class='text-center'>
            <button type='button' class='btn btn-primary w-100' onclick='history.back()'>Voltar</button></div>";
        } else if ($_GET['action'] == "delete_form") {
            unlink("formlist/{$_GET['formid']}.json");
            echo "<div class='alert alert-success text-center' role='alert'>Formulário apagado com sucesso.</div><div class='text-center'>
            <button type='button' class='btn btn-primary w-100' onclick='history.back()'>Voltar</button></div>";
        } else if ($_GET['action'] == "gestao_cache") {
            echo "<h1>Gestão Manual da Cache do GIAE</h1>";
            if ($_POST['user']) {
                $db = new SQLite3('db.sqlite3');
                $result = $db->query("SELECT * FROM cache_giae WHERE id LIKE '%{$_POST['user']}%'");
                if (!$result) {
                    echo "Não existe tal utilizador.\n";
                    exit;
                }
                echo "<table class='table'><tr><th scope='col'>ID</th><th scope='col'>NOME</th><th scope='col'>NOME COMPLETO</th><th scope='col'>EMAIL</th><th scope='col'>AÇÕES</th></tr>";
                while ($row = $result->fetchArray()) {
                    $nome = utf8_encode($row[1]);
                    $nomecompleto = utf8_encode($row[2]);
                    echo "<tr><td>$row[0]</td><td>$nome</td><td>$nomecompleto</td><td>$row[3]</td><td><a href='/admin.php?action=gestao_cache&subaction=edit&user=$row[0]'>EDITAR</a>  <a href='/admin.php?action=gestao_cache&subaction=delete&user=$row[0]'>APAGAR</a></tr>";
                }
                $db->close();
            }
            if ($_GET['subaction'] == "edit") {
                if ($_POST["nome"]){
                    $db = new SQLite3('db.sqlite3');
                    $valordb = $db->prepare("UPDATE cache_giae SET nome = :nome, nomecompleto = :nomecompleto, email = :email WHERE id = :id");
                    $valordb->bindValue(':nome', utf8_decode($_POST["nome"]), SQLITE3_TEXT);
                    $valordb->bindValue(':nomecompleto', utf8_decode($_POST["nomecompleto"]), SQLITE3_TEXT);
                    $valordb->bindValue(':email', utf8_decode($_POST["email"]), SQLITE3_TEXT);
                    $valordb->bindValue(':id', $_GET['user'], SQLITE3_TEXT);
                    $valordb->execute();
                    $db->close();
                    echo "<div class='alert alert-success text-center' role='alert'>Utilizador atualizado com sucesso.</div>
                            <div class='text-center'>
                            <button type='button' class='btn btn-primary w-100' onclick='history.back()'>Voltar</button></div>";
                }
                if ($_GET['user']){
                    $db = new SQLite3('db.sqlite3');
                    $result = $db->query("SELECT * FROM cache_giae WHERE id = '{$_GET['user']}'");
                    if (!$result) {
                        echo "Não existe tal utilizador.\n";
                        exit;
                    }
                    $row = $result->fetchArray();
                    $db->close();
                    $nome = utf8_encode($row[1]);
                    $nomecompleto = utf8_encode($row[2]);
                    echo "<form action='/admin.php?action=gestao_cache&subaction=edit&user={$_GET['user']}' method='POST'>
                    <div class='form-floating'>
                        <input type='text' class='form-control' id='nome' name='nome' value='$nome'>
                        <label for='nome' class='form-label'>Nome</label>
                    </div>
                    <br>
                    <div class='form-floating'>
                        <input type='text' class='form-control' id='nome' name='nomecompleto' value='$nomecompleto'>
                        <label for='nomecompleto' class='form-label'>Nome completo</label>
                    </div>
                    <br>
                    <div class='form-floating'>
                        <input type='email' class='form-control' id='nome' name='email' value='$row[3]'>
                        <label for='email' class='form-label'>Email</label>
                    </div>
                    <br>
                    <button type='submit' class='btn btn-primary w-100'>Atualizar</button></form><hr>";
                }
            }
            if ($_GET['subaction'] == "delete") {
                $db = new SQLite3('db.sqlite3');
                $valordb = $db->prepare("DELETE FROM cache_giae WHERE id = :id");
                $valordb->bindValue(':id', $_GET['user'], SQLITE3_TEXT);
                $valordb->execute();
                $db->close();
                echo "<div class='alert alert-success text-center' role='alert'>Utilizador apagado com sucesso.</div>";
            }
            echo "<form action='/admin.php?action=gestao_cache' method='POST'>
            <div class='form-floating'>
                <input type='text' class='form-control' id='user' name='user' required placeholder='fxxxx ou axxxxx'>
                <label for='user' class='form-label'>ID p/gerir</label>
            </div></form><hr>";
        } else if ($_GET['action'] == "gestao_respostas") {
            echo "<h1>Gestão Manual da Base de Dados de Respostas</h1>";
            if ($_POST['user']) {
                $db = new SQLite3('db.sqlite3');
                $result = $db->query("SELECT * FROM respostas WHERE enviadorid LIKE '%{$_POST['user']}%'");
                if (!$result) {
                    echo "Não existe respostas desse utilizador.\n";
                    exit;
                }
                echo "<table class='table'><tr><th scope='col'>PDF</th><th scope='col'>FORMID</th><th scope='col'>ENVIADORID</th><th scope='col'>RESPOSTA</th><th scope='col'>RESPONDIDO<th scope='col'>RESPONDIDOPORID</th><th scope='col'>AÇÕES</th></tr>";
                while ($row = $result->fetchArray()) {
                    $nome = utf8_encode($db->querySingle("SELECT nome FROM cache_giae WHERE id = '{$row[2]}'"));
                    echo "<tr><td><a href='/$row[0]'>$row[0]</a></td><td>$row[1]</td><td>$row[2] <i>($nome)</i></td><td>$row[3]</td><td>$row[4]</td><td>$row[5]</td><td><a href='/admin.php?action=gestao_respostas&subaction=edit&pdf=$row[0]'>EDITAR</a>  <a href='/admin.php?action=gestao_respostas&subaction=delete&pdf=$row[0]'>APAGAR</a></tr>";
                }
                $db->close();
            }
            if ($_GET['subaction'] == "edit") {
                if ($_POST["pdf"]){
                    $db = new SQLite3('db.sqlite3');
                    $valordb = $db->prepare("UPDATE respostas SET formid = :formid, enviadorid = :enviadorid, resposta = :respostaid, respondido = :respondido, respondidoporid = :respondidoporid WHERE pdf = :pdf");
                    $valordb->bindValue(':pdf', utf8_decode($_POST["pdf"]), SQLITE3_TEXT);
                    $valordb->bindValue(':formid', utf8_decode($_POST["formid"]), SQLITE3_TEXT);
                    $valordb->bindValue(':enviadorid', utf8_decode($_POST["enviadorid"]), SQLITE3_TEXT);
                    $valordb->bindValue(':resposta', utf8_decode($_POST["resposta"]));
                    $valordb->bindValue(':respondido', utf8_decode($_POST["respondido"]), SQLITE3_TEXT);
                    $valordb->bindValue(':respondidoporid', utf8_decode($_POST["respondidoporid"]), SQLITE3_TEXT);
                    $valordb->execute();
                    $db->close();
                    echo "<div class='alert alert-success text-center' role='alert'>Resposta atualizado com sucesso.</div>
                            <div class='text-center'>
                            <button type='button' class='btn btn-primary w-100' onclick='history.back()'>Voltar</button></div>";
                }
                if ($_GET['pdf']){
                    $db = new SQLite3('db.sqlite3');
                    $result = $db->query("SELECT * FROM respostas WHERE pdf = '{$_GET['pdf']}'");
                    if (!$result) {
                        echo "Não existe tal resposta.\n";
                        exit;
                    }
                    $row = $result->fetchArray();
                    echo "<form action='/admin.php?action=gestao_respostas&subaction=edit&pdf={$_GET['pdf']}' method='POST'>";
                    echo "<div class='form-floating'>
                        <input type='text' class='form-control' id='formid' name='' value='$row[1]'></input>
                        <label for='nome' class='form-label'>Formid</label>
                        </div>";
                    echo "<div class='form-floating'>
                        <input type='text' class='form-control' id='enviadorid' name='enviadorid' value='$row[2]'></input>
                        <label for='nome' class='form-label'>Enviadorid</label>
                        </div>";
                    echo "<div class='form-floating'>
                        <input type='text' class='form-control' id='resposta' name='resposta' value='$row[3]'></input>
                        <label for='nome' class='form-label'>Resposta</label>
                        </div>";
                    echo "<div class='form-floating'>
                        <input type='text' class='form-control' id='respondido' name='respondido' value='$row[4]'></input>
                        <label for='nome' class='form-label'>Respondido</label>
                        </div>";
                    echo "<div class='form-floating'>
                        <input type='text' class='form-control' id='respondidoporid' name='respondidoporid' value='$row[5]'></input>
                        <label for='nome' class='form-label'>Respondidoporid</label>
                        </div>";
                    echo "<button type='submit' class='btn btn-primary w-100'>Atualizar</button></form><hr>";
                    $db->close();
                }
            }
            if ($_GET['subaction'] == "delete") {
                $db = new SQLite3('db.sqlite3');
                $valordb = $db->prepare("DELETE FROM respostas WHERE pdf = :id");
                $valordb->bindValue(':id', $_GET['pdf']);
                $valordb->execute();
                $db->close();
                echo "<div class='alert alert-success text-center' role='alert'>Resposta apagada com sucesso.</div>";
            }
            echo "<form action='/admin.php?action=gestao_respostas' method='POST'>
            <div class='form-floating'>
                <input type='text' class='form-control' id='user' name='user' required placeholder='fxxxx ou axxxxx'>
                <label for='user' class='form-label'>ID do submissor p/procurar</label>
            </div></form><hr>";
        } else if ($_GET['section'] == "acessoaopainel") {
            $db = new SQLite3('db.sqlite3');
            echo "<h1>Gestão de Administradores</h1>";
            echo "<div class='d-flex justify-content-between mb-3'><form action='/admin.php?section=acessoaopainel' method='POST'>
            <div class='form-floating'>
            <div class='input-group'>
                <input type='text' class='form-control' id='adminadd' name='adminadd' placeholder='Adicionar à lista'>
                <button type='submit' class='btn btn-danger'>Adicionar</button>
            </div>
            </form></div></div>";
            if ($_POST['adminadd']) {
                $valordb = $db->prepare("INSERT INTO admins(id, atividade) VALUES (:id, :atividade)");
                $valordb->bindValue(':id', $_POST['adminadd'], SQLITE3_TEXT);
                $valordb->bindValue(':atividade', 0, SQLITE3_INTEGER);
                $valordb->execute();
                echo "<div class='alert alert-success text-center' role='alert'>Administrador adicionado com sucesso.</div>
                        <div class='text-center'>
                        <button type='button' class='btn btn-primary w-100' onclick='history.back()'>Voltar</button></div>";
                die();
            }
            echo "<table class='table'><tr><th scope='col'>ID</th><th scope='col'>NOME</th><th scope='col'>ATIVIDADE</th><th scope='col'>AÇÕES</th></tr>";
            $result = $db->query("SELECT * FROM admins");
            while ($row = $result->fetchArray()) {
                $nome = utf8_encode($db->querySingle("SELECT nome FROM cache_giae WHERE id = '{$row[0]}'"));
                echo "<tr><td>$row[0]</td><td>$nome</td><td>"; if ($row[1]) {echo "Sim";} else {echo "Não";}; echo "</td><td><a href='/admin.php?action=toggle_admin&adminid={$row[0]}'>TOGGLE</a> <a href='/admin.php?action=delete_admin&adminid={$row[0]}'>DELETE</a></td></tr>";
            }
            echo "</table>";
            $db->close();
        } else if ($_GET['action'] == "toggle_admin") {
            $db = new SQLite3('db.sqlite3');
            $adminid = $_GET['adminid'];
            $currentStatus = $db->querySingle("SELECT atividade FROM admins WHERE id = '{$adminid}'");
            $newStatus = $currentStatus ? 0 : 1;
            $db->exec("UPDATE admins SET atividade = {$newStatus} WHERE id = '{$adminid}'");
            echo "<div class='alert alert-success text-center' role='alert'>Administrador toggled com sucesso.</div>
            <div class='text-center'>
            <button type='button' class='btn btn-primary w-100' onclick='history.back()'>Voltar</button></div>";
            $db->close();
        } else if ($_GET['action'] == "delete_admin") {
            $db = new SQLite3('db.sqlite3');
            $adminid = $_GET['adminid'];
            $db->exec("DELETE FROM admins WHERE id = '{$adminid}'");
            echo "<div class='alert alert-success text-center' role='alert'>Administrador apagado com sucesso.</div>
            <div class='text-center'>
            <button type='button' class='btn btn-primary w-100' onclick='history.back()'>Voltar</button></div>";
            $db->close();
        } else if ($_GET['action'] == "upload_form") {
            echo "<h1>Carregar Formulário</h1>";
            if ($_POST['formid']) {
                $formid = $_POST['formid'];
                $formjson = $_POST['formjson'];
                $formjson = json_decode($formjson);
                $formjson->ativado = true;
                $formjson = json_encode($formjson);
                file_put_contents("formlist/{$formid}.json", $formjson);
                echo "<div class='alert alert-success text-center' role='alert'>Formulário carregado com sucesso.</div>
                        <div class='text-center'>
                        <button type='button' class='btn btn-primary w-100' onclick='history.back()'>Voltar</button></div>";
                die();
            }
            echo "<form action='/admin.php?action=upload_form' method='POST'>
            <div class='form-floating'>
                <input type='text' class='form-control' id='formid' name='formid' required placeholder='ID do Formulário'>
                <label for='formid' class='form-label'>ID do Formulário</label>
            </div>
            <br>
            <div class='form-floating'>
                <textarea class='form-control' id='formjson' name='formjson' cols='60' rows='10' required placeholder='JSON do Formulário'></textarea>
                <label for='formjson' class='form-label'>JSON do Formulário (colar o conteúdo do ficheiro)</label>
            </div>
            <br>
            <button type='submit' class='btn btn-primary w-100'>Carregar</button></form>";
        }
    } else {
        http_response_code(403);
        echo "<div class='alert alert-danger text-center' role='alert'>Não tem autorização para entrar nesta página.</div>
        <div class='text-center'>
        <button type='button' class='btn btn-primary w-100' onclick='history.back()'>Voltar</button></div>";
        exit;
    }
?>