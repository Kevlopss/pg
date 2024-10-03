<?php
require __DIR__ . "/../../../fpdf/fpdf.php";
require __DIR__ . '/../../../includes/config/DbConection.php';

$contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
if ($contentType === "application/json") {
    $content = trim(file_get_contents("php://input"));
    $datos = json_decode($content, true);

    if (is_array($datos) && isset($datos['usuario'])) {
        $user = $datos['usuario'];

        $data = [
            'ticket_no' => '500',
            'fecha' => isset($datos['fecha']) ? $datos['fecha'] : '',
            'cliente' => 'Consumidor final',
            'productos' => [],
            'total' => isset($datos['total']) ? $datos['total'] : 0,
            'pago_recibido' => isset($datos['importe']) ? $datos['importe'] : 0,
            'cambio' => isset($datos['cambio']) ? $datos['cambio'] : 0
        ];

        $db = $conectarDB;

        // Procesar todos los detalles de la venta
        if (isset($datos['detalles']) && is_array($datos['detalles'])) {
            foreach ($datos['detalles'] as $detalle) {
                $Cod = $detalle['producto_id'];
                $sql = "SELECT * FROM producto WHERE Cod_producto = ?";
                $stmt = $db->prepare($sql);
                if (!$stmt) {
                    throw new Exception("Error en la preparación de la consulta del producto: " . $db->error);
                }
                $stmt->bind_param('s', $Cod);
                $stmt->execute();
                $result = $stmt->get_result();
                $producto = $result->fetch_assoc();
                $stmt->close();

                if ($producto) {
                    $data['productos'][] = [
                        'nombre' => $producto['Nombre'],
                        'cantidad' => $detalle['cantidad'],
                        'precio' => 'Q. ' . $detalle['precio_unitario'],
                        'total' => 'Q. ' . $detalle['total']
                    ];
                }
            }
        }

        // Crear el PDF
        $pdf = new FPDF('P', 'mm', array(80, 200));
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetRightMargin(0);
        
        // Encabezado
        $pdf->Cell(0, 5, 'Comprobante', 0, 1, 'C');
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 5, 'Los Tines', 0, 1, 'C');
        $pdf->Cell(0, 5, 'Quiche', 0, 1, 'C');
        $pdf->Cell(0, 5, '314 609 40 51', 0, 1, 'C');
        $pdf->Ln(2);
        
        // Información del recibo
        $pdf->Cell(20, 5, 'No:', 0, 0, 'L');
        $pdf->Cell(40, 5, $data['ticket_no'], 0, 1, 'R');
        $pdf->Cell(20, 5, 'Fecha:', 0, 0, 'L');
        $pdf->Cell(40, 5, $data['fecha'], 0, 1, 'R');
        $pdf->Cell(20, 5, 'Cajero:', 0, 0, 'L');
        // $pdf->Cell(40, 5, 'Admin23', 0, 1, 'L');
        $pdf->Cell(40, 5, $user['nombre'], 0, 1, 'R');
        $pdf->Cell(20, 5, 'Cliente:', 0, 0, 'L');
        $pdf->Cell(40, 5, $data['cliente'], 0, 1, 'R');
        $pdf->Ln(2);
        // Detalles de productos
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(20, 5, 'Producto', 0, 0, 'L');
        $pdf->Cell(10, 5, 'Cant', 0, 0, 'L');
        
        $pdf->Cell(15, 5, 'Precio', 0, 0, 'L');
        $pdf->Cell(15, 5, 'Sub Total', 0, 1, 'R');
        $pdf->SetFont('Arial', 'B', 10);
        foreach ($data['productos'] as $producto) {
            
            $pdf->Cell(20, 5, $producto['nombre'], 0, 0, 'L');
            $pdf->Cell(10, 5, $producto['cantidad'], 0, 0, 'L');
            $pdf->Cell(15, 5, $producto['precio'], 0, 0, 'C');
            $pdf->Cell(15, 5, $producto['total'], 0, 1, 'R');
        }
        $pdf->Ln(2);
        
        // Totales
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(40, 5, 'Total:', 0, 0, 'C');
        $pdf->Cell(20, 5, 'Q. ' . $data['total'], 0, 1, 'C');
        $pdf->Cell(40, 5, 'Pago recibido:', 0, 0, 'C');
        $pdf->Cell(20, 5, 'Q. ' . $data['pago_recibido'], 0, 1, 'C');
        $pdf->Cell(40, 5, 'Cambio:', 0, 0, 'C');
        $pdf->Cell(20, 5, 'Q. ' . $data['cambio'], 0, 1, 'C');
        
        $pdf->SetFont('Arial', 'B', 13);
        $pdf->Ln(4);
        $pdf->Cell(0, 5, 'Gracias por su compra!!', 0, 1, 'L');
        
        $pdf->Output();
    }
}
