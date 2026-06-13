<script src="<?= $basePath ?>/assets/js/app.js"></script>
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">

<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const estudiante = document.getElementById('buscar-estudiante');

    if (estudiante) {
        new TomSelect('#buscar-estudiante', {
            create: false,
            sortField: {
                field: 'text',
                direction: 'asc'
            },
            placeholder: 'Escriba codigo, apellido o nombre...',
            maxOptions: 8,
            highlight: true,
        });
    }
});
</script>
</body>
</html>
