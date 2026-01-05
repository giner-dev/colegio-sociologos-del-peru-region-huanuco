<?php

/**
 * DEFINICIÓN DE RUTAS DEL SISTEMA
 */

// ============================================
// RUTAS DEL SISTEMA (No requieren autenticación especial)
// ============================================

// Ruta para obtener tiempo de sesión restante
$router->get('/session/time-left', function() {
    header('Content-Type: application/json');
    echo json_encode(['timeLeft' => getSessionTimeLeft()]);
    exit();
});


// ============================================
// RUTAS PÚBLICAS (Sin autenticación)
// ============================================

// Página de inicio / Login
$router->get('/', 'AuthController@showLogin');
$router->get('/login', 'AuthController@showLogin');
$router->post('/login', 'AuthController@login');
$router->get('/logout', 'AuthController@logout');

// Buscador público de colegiados
$router->get('/buscador-publico', 'BuscadorPublicoController@index');
$router->get('/buscador-publico/buscar', 'BuscadorPublicoController@buscar');

// ============================================
// RUTAS PROTEGIDAS (Requieren autenticación)
// ============================================

// Dashboard / Panel principal
$router->get('/dashboard', 'DashboardController@index');

// ============================================
// MÓDULO: COLEGIADOS
// ============================================
$router->get('/colegiados', 'ColegiadoController@index');
$router->get('/colegiados/crear', 'ColegiadoController@crear');
$router->post('/colegiados/guardar', 'ColegiadoController@guardar');
$router->get('/colegiados/ver/{id}', 'ColegiadoController@ver');
$router->get('/colegiados/editar/{id}', 'ColegiadoController@editar');
$router->post('/colegiados/actualizar/{id}', 'ColegiadoController@actualizar');
$router->post('/colegiados/eliminar/{id}', 'ColegiadoController@eliminar');
$router->get('/colegiados/importar', 'ColegiadoController@importar');
$router->post('/colegiados/procesar-excel', 'ColegiadoController@procesarExcel');
$router->get('/colegiados/resultado-importacion', 'ColegiadoController@resultadoImportacion');
$router->get('/colegiados/descargar-plantilla', 'ColegiadoController@descargarPlantilla');
$router->post('/colegiados/cambiar-estado/{id}', 'ColegiadoController@cambiarEstado');

// ============================================
// MÓDULO: PAGOS
// ============================================
$router->get('/pagos', 'PagoController@index');
$router->get('/pagos/registrar', 'PagoController@registrar');
$router->post('/pagos/guardar', 'PagoController@guardar');
$router->get('/pagos/ver/{id}', 'PagoController@ver');
$router->get('/pagos/historial/{idColegiado}', 'PagoController@historialColegiado');
$router->post('/pagos/anular/{id}', 'PagoController@anular');

// ============================================
// MÓDULO: DEUDAS
// ============================================
$router->get('/deudas', 'DeudaController@index');
$router->get('/deudas/registrar', 'DeudaController@registrar');
$router->post('/deudas/guardar', 'DeudaController@guardar');
$router->get('/deudas/colegiado/{id}', 'DeudaController@porColegiado');
$router->post('/deudas/marcar-pagada/{id}', 'DeudaController@marcarPagada');

// ============================================
// MÓDULO: EGRESOS
// ============================================
$router->get('/egresos', 'EgresoController@index');
$router->get('/egresos/registrar', 'EgresoController@registrar');
$router->post('/egresos/guardar', 'EgresoController@guardar');
$router->get('/egresos/ver/{id}', 'EgresoController@ver');
$router->get('/egresos/editar/{id}', 'EgresoController@editar');
$router->post('/egresos/actualizar/{id}', 'EgresoController@actualizar');

// ============================================
// MÓDULO: REPORTES
// ============================================
$router->get('/reportes', 'ReporteController@index');
$router->get('/reportes/ingresos', 'ReporteController@ingresos');
$router->get('/reportes/egresos', 'ReporteController@egresos');
$router->get('/reportes/balance', 'ReporteController@balance');
$router->get('/reportes/habilitados', 'ReporteController@habilitados');
$router->get('/reportes/inhabilitados', 'ReporteController@inhabilitados');
$router->get('/reportes/morosos', 'ReporteController@morosos');
$router->get('/reportes/exportar-excel', 'ReporteController@exportarExcel');

// ============================================
// MÓDULO: USUARIOS (Solo Administrador)
// ============================================
$router->get('/usuarios', 'UsuarioController@index');
$router->get('/usuarios/crear', 'UsuarioController@crear');
$router->post('/usuarios/guardar', 'UsuarioController@guardar');
$router->get('/usuarios/editar/{id}', 'UsuarioController@editar');
$router->post('/usuarios/actualizar/{id}', 'UsuarioController@actualizar');
$router->post('/usuarios/cambiar-estado/{id}', 'UsuarioController@cambiarEstado');

// ============================================
// PÁGINAS DE ERROR
// ============================================
$router->get('/sin-permisos', 'ErrorController@sinPermisos');
$router->get('/error', 'ErrorController@error');