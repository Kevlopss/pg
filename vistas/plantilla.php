<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Inicio</title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="vistas/plugins/fontawesome-free/css/all.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="vistas/dist/css/adminlte.min.css">
</head>

<body class="hold-transition sidebar-mini sidebar-collapse">
  <?php
  // Validación login
  if (isset($_SESSION['login']) && $_SESSION['login'] == 'activa') {

   
      // Accede al ID del usuario
      $id_usuario = $_SESSION['id_usuario'];


    echo '<div class="wrapper">';
    // Nav
    include "vistas/modulos/Nav.php";
    // Menu
    include "vistas/modulos/menu.php";
    // Páginas
    includePagina();
    // Footer
    include "vistas/modulos/footer.php";
    echo '</div>';
  } else {
    include "vistas/modulos/login.php";
  }
  ?>

  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
  </aside>
  <!-- /.control-sidebar -->

  <!-- jQuery -->
  <script src="vistas/plugins/jquery/jquery.min.js"></script>
  <!-- Bootstrap 4 -->
  <script src="vistas/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
  <!-- AdminLTE App -->
  <script src="vistas/dist/js/adminlte.min.js"></script>
  <!-- AdminLTE for demo purposes -->
  <!-- <script src="vistas/dist/js/demo.js"></script> -->

</body>

</html>

<?php
// Función para incluir la página según el enlace
function includePagina()
{
  if (isset($_GET["enlace"])) {
    $enlace = $_GET["enlace"];
    $paginasPermitidas = 
    array("inicio", "configuracion", "roles", "accesos", 
    "pagos", "logout", "perfil", "reportes", "404","caja",
    "usuarios","inventario","clientes","permisos","ventas","productos",
    "movimiento");

    if (in_array($enlace, $paginasPermitidas)) {
      include "vistas/modulos/$enlace.php";
    } else {
      include "vistas/modulos/404.php";
    }
  } else {
    include "vistas/modulos/inicio.php";
  }
}
?>