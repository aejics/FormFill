<?php
    $configformfill = array {
        'mail' => array(
            "servidor": "smtp.gmail.com",
            "porta": 465,
            "autenticacao": true,
            "tipodeseguranca": "PHPMailer::ENCRYPTION_STARTTLS ou PHPMailer::ENCRYPTION_SMTPS",
            "mail": "",
            "password": ""
        ),
        'db' => array(
            'servidor' => 'localhost',
            'user' => 'formfilluser',
            'password' => 'formfillpass',
            'db' => 'formfill',
            'porta' => 3306
        )
    }
?>