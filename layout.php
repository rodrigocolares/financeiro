<?php
function layout_header($titulo = "Controle Financeiro") {
    echo "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <title>$titulo</title>

        <style>
            body {
                font-family: Arial, sans-serif;
                background-color: #f4f4f4;
                margin: 0;
                padding: 0;
                position: relative;
                min-height: 100vh;
            }

            /* MARCA D'ÁGUA DO LOGO */
            body::before {
                content: '';
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: url('img/logo.png') no-repeat center center;
                background-size: 1050px; /* ajuste o tamanho do logo */
                opacity: 0.08; /* transparência da marca d'água */
                z-index: -1; /* fica atrás de tudo */
            }

            .container {
                width: 400px;
                margin: 40px auto;
                background: #fff;
                padding: 25px;
                border-radius: 8px;
                box-shadow: 0 0 10px rgba(0,0,0,0.1);
                position: relative;
                z-index: 1;
            }

            h1, h2, h3 {
                text-align: center;
            }

            a { color: #007bff; text-decoration: none; }
            a:hover { text-decoration: underline; }

            .msg-erro { color: red; text-align:center; }
            .msg-sucesso { color: green; text-align:center; }

            button {
                background: #007bff;
                color: #fff;
                border: none;
                padding: 10px 15px;
                border-radius: 5px;
                cursor: pointer;
                width: 100%;
            }

            button:hover {
                background: #0056b3;
            }

            input[type=email], input[type=password], input[type=text], input[type=date], select {
                width: 100%;
                padding: 10px;
                margin: 8px 0 15px 0;
                border: 1px solid #ccc;
                border-radius: 5px;
            }
        </style>
    </head>

    <body>
    <div class='container'>
    ";
}

function layout_footer() {
    echo "
        <p style='text-align: right; font-size: 12px; color: #777; margin-top: 20px;'>
            @created by <strong>Rodrigo Colares</strong>
        </p>
    ";

    echo "
        </div>
        </body>
        </html>
    ";
}

