<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Libro de Reclamaciones</title>

    <script>
        // Resolver el color primario: URL param del servidor tiene prioridad, luego localStorage
        window.widgetColor = (function () {
            var server = {!! json_encode($primary_color ?? '#18181b') !!};
            if (server && server !== '#18181b') { return server; }
            try {
                var stored = localStorage.getItem('claims_widget_primary_color');
                if (stored && /^#[0-9A-Fa-f]{6}$/.test(stored)) { return stored; }
            } catch (e) {}
            return '#18181b';
        }());
    </script>

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
            :primary-color="widgetColor"
        ></tenant-claims-book-form>
    </div>
</body>
</html>
