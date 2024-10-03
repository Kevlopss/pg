<?php
if (!isset($_GET['formato'])) {
    die('Formato no especificado');
}

$formato = $_GET['formato'];

if ($formato == 'excel') {
    $file = 'reporte_de_caso.xlsx';
    $filename = 'reporte_de_caso.xlsx';
    $content_type = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
} elseif ($formato == 'pdf') {
    $file = 'reporte_de_caso.pdf';
    $filename = 'reporte_de_caso.pdf';
    $content_type = 'application/pdf';
} else {
    die('Formato no soportado');
}

$file_path = __DIR__ . '/' . $file;

if (!file_exists($file_path)) {
    die('El archivo no existe');
}

header('Content-Description: File Transfer');
header('Content-Type: ' . $content_type);
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($file_path));
header('Pragma: public');
header('Cache-Control: must-revalidate');
readfile($file_path);
exit();
