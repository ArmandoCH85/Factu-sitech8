<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Libro de Reclamaciones</title>

    @vite(['modules/ClaimsBook/Resources/assets/js/app.js'])

    <style>
        /* Estilos de normalización para la vista embebida */
        html, body {
            margin: 0;
            padding: 0;
            background: #fff;
            font-family: 'Montserrat', 'Helvetica Neue', Arial, sans-serif;
        }
        #main-wrapper {
            padding: 0;
        }
        /* Ocultar la barra lateral y el header del panel admin en el widget */
        .page-sidebar,
        .page-header.navbar,
        aside,
        nav {
            display: none !important;
        }
    </style>
</head>
<body>
    <div id="main-wrapper">
        <tenant-claims-book-form
            :embedded="true"
            tenant-slug="{{ $tenant_slug }}"
        ></tenant-claims-book-form>
    </div>
</body>
</html>
