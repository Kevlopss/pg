<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-color: #ffcc00;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .login-box {
            background: white;
            border-radius: 10px;
            padding: 20px;
            width: calc(20%);
            box-shadow: 0px 0px 10px 0px rgba(0,0,0,0.1);
            text-align: center;
        }
        .login-box img {
            width: 200px; /* Duplicar el tamaño del logo */
            height: auto;
            
        }
        .login-box h1 {
            font-size: 24px;
            margin-bottom: 20px;
            
        }
        .login-box .input-group {
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }
        .login-box .input-group input {
            padding: 10px;
            border: 2px solid #007bff ;
            border-radius: 1rem;
            font-weight: bold;
            color: black;
            
        }
        .login-box .input-group .input-group-text {
            width: 40px;
            background-color: #fff;
            border: 1px solid #ddd;
            border: none;
            border-radius: 1rem;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color:#007bff;
            color: white ; /* Color de los íconos */
            padding: 0.8rem 0;
            margin-right: 1rem ;
        }
        div.bordes{
            width: calc(100% - 80px);
            
        }
        .login-box button {
            width: 100%;
            padding: 10px;
            background-color: #007bff; /* Color del botón */
            border: none;
            border-radius: 5rem;
            color: white;
            font-size: 16px;
            cursor: pointer;
        }
        .login-box button:hover {
            cursor: pointer; /* Cursor hand al hacer hover sobre el botón */
        }
    </style>
</head>
<body>
    <div class="login-box">
        <img src="vistas/imgs/sis/logo.png" alt="Los Tines Logo">
        <h1>Introduzca sus accesos</h1>
        <form method="post">

            <div class="input-group">
                <div class="input-group-text">
                    <span class="fas fa-user"></span>
                </div>
                <div class="bordes">
                 <input type="text" class="form-control bordes" placeholder="Usuario" name="txt_usuario">
                </div>
            </div>

            <div class="input-group">
                <div class="input-group-text">
                    <span class="fas fa-lock"></span>
                </div>
                <div class="bordes">
                 <input type="password" class="form-control" placeholder="Clave" name="txt_clave">
                </div>
            </div>

            <button type="submit">Ingresar</button>

            <?php 
                require_once('controladores/CtrlLoginUsuario.php');
                $obj_login = new ControladorUsuario();
                $obj_login->ctrlLoginUsuario();
            ?>
        </form>
    </div>
</body>
</html>
