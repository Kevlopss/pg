
<div class="container mt-3" style="margin-bottom:calc(30%)">

    <h2 style="width:calc(100%); text-align:center; text-transform:uppercase; margin-bottom:2rem;">Reporte de factura</h2>

    <!-- Inputs de selección de fecha con Bootstrap -->
    <div class="row mb-3" >
        <div class="col-md-3">
            <label for="fechaDesde">Desde:</label>
            <input type="date" id="fechaDesde" class="form-control" value="<?php echo $fechaActual; ?>">
        </div>
        <div class="col-md-3">
            <label for="fechaHasta">Hasta:</label>
            <input type="date" id="fechaHasta" class="form-control" value="<?php echo $fechaActual; ?>">
        </div>
        <div class="col-md-3 d-flex align-items-end">
            <button id="btnGenerar" class="btn" style="background-color:#003366; color:white;">Generar</button>
        </div>
    </div>

    <!-- Tabla para mostrar los resultados -->
    <div id="tablaResultados">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th style="background-color:#003366; color:white; text-align:center;">Cod. Referencia</th>
                    <th style="background-color:#003366; color:white; text-align:center;">Factura/th>
                    <th style="background-color:#003366; color:white; text-align:center;">Estado</th>
                    <th style="background-color:#003366; color:white; text-align:center;">Encargado</th>
                </tr>
            </thead>
            <tbody>

                       

                    <tr>
                        <td colspan="7" style="text-align:center;">EN PROCESO DE CONTRATACIONDE CERTIFICADORA</td>
                    </tr>

            </tbody>
        </table>
    </div>

    <!-- Tabla de resumen -->
    <div class="container mt-3">
        <table class="table table-bordered" style="width: 100%; margin: 0 auto; background-color:#003366; color:white;">
            <thead>
                <tr style="text-align:center;">
                    <th colspan="2" style="background-color:#003366; color:white; text-align:center;">RESUMEN</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="background-color:#003366; color:white; text-align:center;">Total facturas</td>
                    <td style="text-align:center;">0</td>
                </tr>
            </tbody>
        </table>
    </div>

</div>