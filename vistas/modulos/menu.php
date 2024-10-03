<?php

// Importar la conexión a la base de datos
require __DIR__ . '/../../includes/config/DbConection.php';

// Establecer la conexión y almacenarla en la variable $db
$db = $conectarDB;

// Accede al ID del usuario
$id_usuario = $_SESSION['id_usuario'];

// Obtener los datos del usuario
$ConsUser = "SELECT * FROM usuario WHERE id_usuario=" . $id_usuario;
$ResulUser = mysqli_query($db, $ConsUser);
$dataUser = mysqli_fetch_assoc($ResulUser);

// Obtener los permisos de acceso del usuario
$query = "SELECT * FROM acceso WHERE id_usuario=" . $id_usuario;
$resultado = mysqli_query($db, $query);
$accesos = mysqli_fetch_assoc($resultado);

// Obtener todos los elementos del menú
$ConsMenu = "SELECT * FROM menu";
$ResMenu = mysqli_query($db, $ConsMenu);
$ArrMenu = mysqli_fetch_all($ResMenu, MYSQLI_ASSOC);

// Definir el orden del menú
$menuOrder = [
    'Inicio',
    'Roles',
    'Accesos',
    'Permisos',
    'Usuarios',
    'Inventario',
    'Productos',
    'Movimiento',
    'Clientes',
    'Caja',
    'Ventas',
    'Reportes',
    'Configuración'
];

// Crear un array asociativo para un acceso más rápido a los datos del menú
$menuData = [];
foreach ($ArrMenu as $menu) {
    $menuData[$menu['nombre']] = $menu;
}
?>

<style>
    .simbol {
        background-color: #49A0AE;
        color: white;
        font-weight: bold;
        font-size: 1rem;
        border-radius: 1rem;
        padding: 0.5rem;
    }

    .bordered-img {
        border-radius: 1rem; /* Opcional: añade bordes redondeados */
    }

    .brand-text {
        font-weight: bold; /* Hace el texto más grueso */
    }
</style>

<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="#" class="brand-link">
        <img src="vistas\imgs\sis\logo.png" alt="Empresa Logo" class="w-25 h-auto elevation-5 mr-3 ml-3 bordered-img" style="opacity: 1">
        <span class="brand-text font-weight-bold">LOS TINES</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <img src="vistas\imgs\sis\user.jpg" class="img-circle elevation-2" alt="User Image">
            </div>
            <div class="info">
                <a href="#" class="d-block"><?php echo $dataUser['nombre'] . " " . $dataUser['apellido'] ?></a>
            </div>
        </div>

        <!-- SidebarSearch Form -->
        <div class="form-inline">
            <div class="input-group" data-widget="sidebar-search">
                <input class="form-control form-control-sidebar" type="search" placeholder="Search" aria-label="Search">
                <div class="input-group-append">
                    <button class="btn btn-sidebar">
                        <i class="fas fa-search fa-fw"></i>
                    </button>
                </div>
            </div>
        </div>
        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <?php foreach ($menuOrder as $menuName) : ?>
                    <?php if (isset($menuData[$menuName])) : ?>
                        <?php
                        $menu = $menuData[$menuName];
                        $mostrar = false;
                        switch ($menu['nombre']) {
                            case 'Inicio':
                                $mostrar = true;
                                break;
                            case 'Perfil':
                                $mostrar = $accesos['configurar_perfil'] == 1;
                                break;
                            case 'Roles':
                                $mostrar = $accesos['administrar_roles'] == 1;
                                break;
                            case 'Accesos':
                                $mostrar = $accesos['administrar_accesos'] == 1;
                                break;
                            case 'Permisos':
                                $mostrar = $accesos['administrar_permisos'] == 1;
                                break;
                            case 'Usuarios':
                                $mostrar = $accesos['administrar_usuarios'] == 1;
                                break;    
                            case 'Reportes':
                                $mostrar = $accesos['administrar_reportes'] == 1;
                                break;
                            case 'Caja':
                                $mostrar = $accesos['administrar_caja'] == 1;
                                break;
                            case 'Inventario':
                                $mostrar = $accesos['administrar_inventario'] == 1;
                                break;
                            case 'Clientes':
                                $mostrar = $accesos['administrar_clientes'] == 1;
                                break;
                            case 'Configuración':
                                $mostrar = $accesos['administrar_configuracion'] == 1;
                                break;
                            case 'Movimiento':
                                $mostrar = $accesos['administrar_movimientos'] == 1;
                                break;
                            case 'Ventas':
                                $mostrar = $accesos['administrar_ventas'] == 1;
                                break;
                            case 'Productos':
                                $mostrar = $accesos['administrar_productos'] == 1;
                                break;
                            // Añadir más casos según sea necesario
                        }
                        ?>
                        <?php if ($mostrar) : ?>
                            <li class="nav-item">
                                <a href="<?php echo $menu['url'] ?>" class="nav-link">
                                    <p>
                                        <?php echo $menu['nombre'] ?>
                                        <i class="right fas simbol">⮕</i>
                                    </p>
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>
                <?php endforeach; ?>
                <!-- Cerrar Sesión -->
                <li class="nav-item">
                    <a href="logout" class="nav-link">
                        <p>
                            Cerrar Sesión
                            <i class="right fas simbol">⮕</i>
                        </p>
                    </a>
                </li>
            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>
