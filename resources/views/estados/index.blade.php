{{-- resources/views/estados/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Estados de México - COPOMEX')

@section('breadcrumb')
<li class="breadcrumb-item active" aria-current="page">Estados de México</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Header Card -->
        <div class="card mb-4 fade-in">
            <div class="card-body text-center">
                <h1 class="display-6 text-primary mb-3">
                    <i class="fas fa-map-marked-alt me-3"></i>
                    Estados de México
                </h1>
                <p class="lead text-muted">
                    Catálogo completo de los 32 estados de México obtenido desde la API de COPOMEX
                </p>
                <div class="row text-center mt-4">
                    <div class="col-md-4">
                        <div class="border-end">
                            <h3 class="text-primary" id="totalEstadosHeader">{{ $totalEstados }}</h3>
                            <p class="text-muted mb-0">Estados Cargados</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border-end">
                            <h3 class="text-success">32</h3>
                            <p class="text-muted mb-0">Estados Esperados</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <h3 class="text-info" id="statusIndicator">
                            @if($totalEstados >= 32)
                            <i class="fas fa-check-circle"></i>
                            @else
                            <i class="fas fa-exclamation-circle"></i>
                            @endif
                        </h3>
                        <p class="text-muted mb-0">Estado del Sistema</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Card -->
        <div class="card shadow fade-in">
            <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-center">
                <div class="d-flex align-items-center mb-2 mb-md-0">
                    <h4 class="mb-0">
                        <i class="fas fa-table me-2"></i>
                        Listado de Estados
                    </h4>
                </div>
                <div class="d-flex flex-column flex-md-row gap-2">
                    <button type="button" class="btn btn-outline-info btn-sm" onclick="recargarTabla()">
                        <i class="fas fa-sync-alt me-1"></i>
                        Actualizar Tabla
                    </button>
                    <button type="button" class="btn btn-success" id="cargarEstadosBtn">
                        <span class="loading-spinner spinner-border spinner-border-sm me-2" role="status"></span>
                        <span class="btn-text">
                            <i class="fas fa-download me-1"></i>
                            Cargar desde COPOMEX
                        </span>
                    </button>
                </div>
            </div>

            <div class="card-body">
                @if($totalEstados == 0)
                <div class="alert alert-warning text-center" id="noDataAlert">
                    <i class="fas fa-exclamation-triangle fa-2x mb-3"></i>
                    <h5>No hay estados cargados</h5>
                    <p class="mb-3">Haz clic en "Cargar desde COPOMEX" para obtener los 32 estados de México</p>
                    <button type="button" class="btn btn-warning" onclick="$('#cargarEstadosBtn').click()">
                        <i class="fas fa-download me-1"></i>
                        Cargar Ahora
                    </button>
                </div>
                @endif

                <div class="table-responsive">
                    <table id="estadosTable" class="table table-striped table-bordered table-hover" style="width:100%">
                        <thead>
                            <tr>
                                <th class="text-center" width="8%">
                                    <i class="fas fa-hashtag"></i>
                                    ID
                                </th>
                                <th width="35%">
                                    <i class="fas fa-map-marker-alt me-1"></i>
                                    Estado
                                </th>
                                <th class="text-center" width="20%">
                                    <i class="fas fa-calendar-alt me-1"></i>
                                    Fecha de Carga
                                </th>
                                <!--th class="text-center" width="15%">
                                    <i class="fas fa-link me-1"></i>
                                    API
                                </th-->
                                <th class="text-center" width="22%">
                                    <i class="fas fa-cogs me-1"></i>
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- DataTables will populate this -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para mostrar municipios -->
<div class="modal fade" id="municipiosModal" tabindex="-1" aria-labelledby="municipiosModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="municipiosModalLabel">
                    <i class="fas fa-city me-2"></i>
                    Municipios de <span id="estadoNombre" class="fw-bold"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <!-- Loading state -->
                <div id="loadingMunicipios" class="text-center py-5">
                    <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                        <span class="visually-hidden">Cargando municipios...</span>
                    </div>
                    <h5 class="text-primary">Obteniendo municipios desde COPOMEX</h5>
                    <p class="text-muted">Por favor espera...</p>
                </div>

                <!-- Content state -->
                <div id="municipiosContent" style="display: none;">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="alert alert-info d-flex align-items-center">
                                <i class="fas fa-info-circle fa-2x me-3"></i>
                                <div>
                                    <strong>Total de municipios:</strong>
                                    <span id="totalMunicipios" class="badge bg-primary fs-6">0</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex justify-content-end">
                                <button type="button" class="btn btn-outline-primary btn-sm" id="exportarMunicipios">
                                    <i class="fas fa-download me-1"></i>
                                    Exportar Lista
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="municipiosTable" class="table table-sm table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th width="10%">#</th>
                                    <th width="70%">
                                        <i class="fas fa-building me-1"></i>
                                        Nombre del Municipio
                                    </th>
                                    <th width="20%" class="text-center">
                                        <i class="fas fa-map-marker-alt me-1"></i>
                                        Estado
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="municipiosTableBody">

                            </tbody>
                        </table>
                    </div>
                </div>

                <div id="errorMunicipios" style="display: none;" class="text-center py-5">
                    <div class="text-danger mb-3">
                        <i class="fas fa-exclamation-triangle fa-3x"></i>
                    </div>
                    <h5 class="text-danger">Error al cargar municipios</h5>
                    <p class="text-muted" id="errorMessage">Ocurrió un error al obtener los municipios.</p>
                    <button type="button" class="btn btn-danger" id="retryMunicipios">
                        <i class="fas fa-redo me-1"></i>
                        Reintentar
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>
                    Cerrar
                </button>
                <a href="#" id="verApiLink" target="_blank" class="btn btn-outline-info">
                    <i class="fas fa-code me-1"></i>
                    Ver JSON API
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        let estadosTable;
        let municipiosTable;
        let currentEstado = '';

        inicializarDataTableEstados();
        $('#cargarEstadosBtn').on('click', cargarEstados);
        $(document).on('click', '.ver-municipios', verMunicipios);
        $('#retryMunicipios').on('click', function() {
            obtenerMunicipios(currentEstado);
        });

        $('#exportarMunicipios').on('click', exportarMunicipios);

        $('#municipiosModal').on('hidden.bs.modal', function() {
            if (municipiosTable && $.fn.DataTable.isDataTable('#municipiosTable')) {
                municipiosTable.destroy();
                municipiosTable = null;
            }
            $('#municipiosTableBody').empty();
            currentEstado = '';
        });

        function inicializarDataTableEstados() {
            estadosTable = $('#estadosTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('estados.datatable') }}",
                    error: function(xhr, error, code) {
                        console.error('DataTables Error:', error, code);
                        showNotification('error', 'Error', 'No se pudieron cargar los datos de la tabla');
                    }
                },
                columns: [{
                        data: 'id',
                        name: 'id',
                        className: 'text-center fw-bold'
                    },
                    {
                        data: 'nombre',
                        name: 'nombre',
                        className: 'fw-semibold'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at',
                        className: 'text-center'
                    },
                    /*{
                        data: 'municipios_link',
                        name: 'municipios_link',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },*/
                    {
                        data: 'acciones',
                        name: 'acciones',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    }
                ],
                order: [
                    [1, 'asc']
                ],
                pageLength: 25,
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "Todos"]
                ],
                responsive: true,
                language: {
                    "processing": "Procesando...",
                    "lengthMenu": "Mostrar _MENU_ registros",
                    "zeroRecords": "No se encontraron resultados",
                    "emptyTable": "Ningún dato disponible en esta tabla",
                    "infoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
                    "infoFiltered": "(filtrado de un total de _MAX_ registros)",
                    "search": "Buscar:",
                    "loadingRecords": "Cargando...",
                    "paginate": {
                        "first": "Primero",
                        "last": "Último",
                        "next": "Siguiente",
                        "previous": "Anterior"
                    },
                    "aria": {
                        "sortAscending": ": Activar para ordenar la columna de manera ascendente",
                        "sortDescending": ": Activar para ordenar la columna de manera descendente"
                    },
                    "buttons": {
                        "copy": "Copiar",
                        "colvis": "Visibilidad",
                        "collection": "Colección",
                        "colvisRestore": "Restaurar visibilidad",
                        "copyKeys": "Presione ctrl o u2318 + C para copiar los datos de la tabla al portapapeles del sistema. <br \/> <br \/> Para cancelar, haga clic en este mensaje o presione escape.",
                        "copySuccess": {
                            "1": "Copiada 1 fila al portapapeles",
                            "_": "Copiadas %ds filas al portapapeles"
                        },
                        "copyTitle": "Copiar al portapapeles",
                        "csv": "CSV",
                        "excel": "Excel",
                        "pageLength": {
                            "-1": "Mostrar todas las filas",
                            "_": "Mostrar %d filas"
                        },
                        "pdf": "PDF",
                        "print": "Imprimir",
                        "renameState": "Cambiar nombre",
                        "updateState": "Actualizar",
                        "createState": "Crear Estado",
                        "removeAllStates": "Remover Estados",
                        "removeState": "Remover",
                        "savedStates": "Estados Guardados",
                        "stateRestore": "Estado %d"
                    },
                    "autoFill": {
                        "cancel": "Cancelar",
                        "fill": "Rellene todas las celdas con <i>%d<\/i>",
                        "fillHorizontal": "Rellenar celdas horizontalmente",
                        "fillVertical": "Rellenar celdas verticalmente"
                    },
                    "decimal": ",",
                    "searchBuilder": {
                        "add": "Añadir condición",
                        "button": {
                            "0": "Constructor de búsqueda",
                            "_": "Constructor de búsqueda (%d)"
                        },
                        "clearAll": "Borrar todo",
                        "condition": "Condición",
                        "conditions": {
                            "date": {
                                "before": "Antes",
                                "between": "Entre",
                                "empty": "Vacío",
                                "equals": "Igual a",
                                "notBetween": "No entre",
                                "not": "Diferente de",
                                "after": "Después",
                                "notEmpty": "No Vacío"
                            },
                            "number": {
                                "between": "Entre",
                                "equals": "Igual a",
                                "gt": "Mayor a",
                                "gte": "Mayor o igual a",
                                "lt": "Menor que",
                                "lte": "Menor o igual que",
                                "notBetween": "No entre",
                                "notEmpty": "No vacío",
                                "not": "Diferente de",
                                "empty": "Vacío"
                            },
                            "string": {
                                "contains": "Contiene",
                                "empty": "Vacío",
                                "endsWith": "Termina en",
                                "equals": "Igual a",
                                "startsWith": "Empieza con",
                                "not": "Diferente de",
                                "notContains": "No Contiene",
                                "notStartsWith": "No empieza con",
                                "notEndsWith": "No termina con",
                                "notEmpty": "No Vacío"
                            },
                            "array": {
                                "not": "Diferente de",
                                "equals": "Igual",
                                "empty": "Vacío",
                                "contains": "Contiene",
                                "notEmpty": "No Vacío",
                                "without": "Sin"
                            }
                        },
                        "data": "Data",
                        "deleteTitle": "Eliminar regla de filtrado",
                        "leftTitle": "Criterios anulados",
                        "logicAnd": "Y",
                        "logicOr": "O",
                        "rightTitle": "Criterios de sangría",
                        "title": {
                            "0": "Constructor de búsqueda",
                            "_": "Constructor de búsqueda (%d)"
                        },
                        "value": "Valor"
                    },
                    "searchPanes": {
                        "clearMessage": "Borrar todo",
                        "collapse": {
                            "0": "Paneles de búsqueda",
                            "_": "Paneles de búsqueda (%d)"
                        },
                        "count": "{total}",
                        "countFiltered": "{shown} ({total})",
                        "emptyPanes": "Sin paneles de búsqueda",
                        "loadMessage": "Cargando paneles de búsqueda",
                        "title": "Filtros Activos - %d",
                        "showMessage": "Mostrar Todo",
                        "collapseMessage": "Colapsar Todo"
                    },
                    "select": {
                        "cells": {
                            "1": "1 celda seleccionada",
                            "_": "%d celdas seleccionadas"
                        },
                        "columns": {
                            "1": "1 columna seleccionada",
                            "_": "%d columnas seleccionadas"
                        },
                        "rows": {
                            "1": "1 fila seleccionada",
                            "_": "%d filas seleccionadas"
                        }
                    },
                    "thousands": ".",
                    "datetime": {
                        "previous": "Anterior",
                        "hours": "Horas",
                        "minutes": "Minutos",
                        "seconds": "Segundos",
                        "unknown": "-",
                        "amPm": [
                            "AM",
                            "PM"
                        ],
                        "months": {
                            "0": "Enero",
                            "1": "Febrero",
                            "10": "Noviembre",
                            "11": "Diciembre",
                            "2": "Marzo",
                            "3": "Abril",
                            "4": "Mayo",
                            "5": "Junio",
                            "6": "Julio",
                            "7": "Agosto",
                            "8": "Septiembre",
                            "9": "Octubre"
                        },
                        "weekdays": {
                            "0": "Dom",
                            "1": "Lun",
                            "2": "Mar",
                            "4": "Jue",
                            "5": "Vie",
                            "3": "Mié",
                            "6": "Sáb"
                        },
                        "next": "Siguiente"
                    },
                    "editor": {
                        "close": "Cerrar",
                        "create": {
                            "button": "Nuevo",
                            "title": "Crear Nuevo Registro",
                            "submit": "Crear"
                        },
                        "edit": {
                            "button": "Editar",
                            "title": "Editar Registro",
                            "submit": "Actualizar"
                        },
                        "remove": {
                            "button": "Eliminar",
                            "title": "Eliminar Registro",
                            "submit": "Eliminar",
                            "confirm": {
                                "_": "¿Está seguro de que desea eliminar %d filas?",
                                "1": "¿Está seguro de que desea eliminar 1 fila?"
                            }
                        },
                        "error": {
                            "system": "Ha ocurrido un error en el sistema (<a target=\"\\\" rel=\"\\ nofollow\" href=\"\\\">Más información&lt;\\\/a&gt;).<\/a>"
                        },
                        "multi": {
                            "title": "Múltiples Valores",
                            "restore": "Deshacer Cambios",
                            "noMulti": "Este registro puede ser editado individualmente, pero no como parte de un grupo.",
                            "info": "Los elementos seleccionados contienen diferentes valores para este registro. Para editar y establecer todos los elementos de este registro con el mismo valor, haga clic o pulse aquí, de lo contrario conservarán sus valores individuales."
                        }
                    },
                    "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
                    "stateRestore": {
                        "creationModal": {
                            "button": "Crear",
                            "name": "Nombre:",
                            "order": "Clasificación",
                            "paging": "Paginación",
                            "select": "Seleccionar",
                            "columns": {
                                "search": "Búsqueda de Columna",
                                "visible": "Visibilidad de Columna"
                            },
                            "title": "Crear Nuevo Estado",
                            "toggleLabel": "Incluir:",
                            "scroller": "Posición de desplazamiento",
                            "search": "Búsqueda",
                            "searchBuilder": "Búsqueda avanzada"
                        },
                        "removeJoiner": "y",
                        "removeSubmit": "Eliminar",
                        "renameButton": "Cambiar Nombre",
                        "duplicateError": "Ya existe un Estado con este nombre.",
                        "emptyStates": "No hay Estados guardados",
                        "removeTitle": "Remover Estado",
                        "renameTitle": "Cambiar Nombre Estado",
                        "emptyError": "El nombre no puede estar vacío.",
                        "removeConfirm": "¿Seguro que quiere eliminar %s?",
                        "removeError": "Error al eliminar el Estado",
                        "renameLabel": "Nuevo nombre para %s:"
                    },
                    "infoThousands": "."
                },
                drawCallback: function() {
                    // Reinicializar tooltips después de cada draw
                    $('[data-bs-toggle="tooltip"]').tooltip();
                }
            });
        }

        function cargarEstados() {
            Swal.fire({
                title: '¿Cargar estados desde COPOMEX?',
                text: 'Se obtendrán los 32 estados de México desde la API de COPOMEX',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, cargar',
                cancelButtonText: 'Cancelar',
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return $.post("{{ route('estados.cargar') }}", {
                        force: true,
                        clear_cache: true
                    });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                    const response = result.value;

                    if (response.success) {
                        showNotification('success', '¡Éxito!', response.message);

                        // Actualizar contador en header
                        $('#totalEstadosHeader').text(response.data.total_en_bd);
                        $('#statusIndicator').html('<i class="fas fa-check-circle"></i>');

                        // Recargar tabla
                        estadosTable.ajax.reload();

                        // Ocultar alerta de no data si existe
                        $('#noDataAlert').fadeOut();

                    } else {
                        showNotification('error', 'Error', response.message);
                    }
                }
            }).catch((error) => {
                console.error('Error:', error);
                const message = error.responseJSON?.message || 'Error al cargar estados';
                showNotification('error', 'Error', message);
            });
        }

        function verMunicipios() {
            const estadoNombre = $(this).data('estado');
            currentEstado = estadoNombre;
            $('#estadoNombre').text(estadoNombre);
            $('#municipiosModal').modal('show');
            obtenerMunicipios(estadoNombre);
        }

        function obtenerMunicipios(estadoNombre) {
            // Reset modal state
            $('#loadingMunicipios').show();
            $('#municipiosContent, #errorMunicipios').hide();

            // Update API link
            const apiUrl = `{{ route('estados.municipios', ['estado' => ':estado']) }}`.replace(':estado', encodeURIComponent(estadoNombre));
            $('#verApiLink').attr('href', apiUrl);

            // Obtener municipios desde el servidor
            $.get(apiUrl)
                .done(function(response) {
                    if (response.success) {
                        mostrarMunicipios(response.data);
                    } else {
                        mostrarErrorMunicipios(response.message);
                    }
                })
                .fail(function(xhr) {
                    const message = xhr.responseJSON?.message || 'Error al obtener municipios';
                    mostrarErrorMunicipios(message);
                });
        }

        function mostrarMunicipios(data) {
            $('#totalMunicipios').text(data.total);

            // Limpiar y llenar tabla de municipios
            const tbody = $('#municipiosTableBody');
            tbody.empty();

            data.municipios.forEach(function(municipio, index) {
                const row = `
                <tr>
                    <td class="text-center fw-bold">${index + 1}</td>
                    <td>${municipio}</td>
                    <td class="text-center">
                        <span class="badge bg-info">${data.estado}</span>
                    </td>
                </tr>
            `;
                tbody.append(row);
            });

            $('#loadingMunicipios').hide();
            $('#municipiosContent').show();

            // Inicializar DataTable para municipios
            if (municipiosTable && $.fn.DataTable.isDataTable('#municipiosTable')) {
                municipiosTable.destroy();
            }

            municipiosTable = $('#municipiosTable').DataTable({
                pageLength: 25,
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "Todos"]
                ],
                responsive: true,
                language: {
                    "processing": "Procesando...",
                    "lengthMenu": "Mostrar _MENU_ registros",
                    "zeroRecords": "No se encontraron resultados",
                    "emptyTable": "Ningún dato disponible en esta tabla",
                    "infoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
                    "infoFiltered": "(filtrado de un total de _MAX_ registros)",
                    "search": "Buscar:",
                    "loadingRecords": "Cargando...",
                    "paginate": {
                        "first": "Primero",
                        "last": "Último",
                        "next": "Siguiente",
                        "previous": "Anterior"
                    },
                    "aria": {
                        "sortAscending": ": Activar para ordenar la columna de manera ascendente",
                        "sortDescending": ": Activar para ordenar la columna de manera descendente"
                    },
                    "buttons": {
                        "copy": "Copiar",
                        "colvis": "Visibilidad",
                        "collection": "Colección",
                        "colvisRestore": "Restaurar visibilidad",
                        "copyKeys": "Presione ctrl o u2318 + C para copiar los datos de la tabla al portapapeles del sistema. <br \/> <br \/> Para cancelar, haga clic en este mensaje o presione escape.",
                        "copySuccess": {
                            "1": "Copiada 1 fila al portapapeles",
                            "_": "Copiadas %ds filas al portapapeles"
                        },
                        "copyTitle": "Copiar al portapapeles",
                        "csv": "CSV",
                        "excel": "Excel",
                        "pageLength": {
                            "-1": "Mostrar todas las filas",
                            "_": "Mostrar %d filas"
                        },
                        "pdf": "PDF",
                        "print": "Imprimir",
                        "renameState": "Cambiar nombre",
                        "updateState": "Actualizar",
                        "createState": "Crear Estado",
                        "removeAllStates": "Remover Estados",
                        "removeState": "Remover",
                        "savedStates": "Estados Guardados",
                        "stateRestore": "Estado %d"
                    },
                    "autoFill": {
                        "cancel": "Cancelar",
                        "fill": "Rellene todas las celdas con <i>%d<\/i>",
                        "fillHorizontal": "Rellenar celdas horizontalmente",
                        "fillVertical": "Rellenar celdas verticalmente"
                    },
                    "decimal": ",",
                    "searchBuilder": {
                        "add": "Añadir condición",
                        "button": {
                            "0": "Constructor de búsqueda",
                            "_": "Constructor de búsqueda (%d)"
                        },
                        "clearAll": "Borrar todo",
                        "condition": "Condición",
                        "conditions": {
                            "date": {
                                "before": "Antes",
                                "between": "Entre",
                                "empty": "Vacío",
                                "equals": "Igual a",
                                "notBetween": "No entre",
                                "not": "Diferente de",
                                "after": "Después",
                                "notEmpty": "No Vacío"
                            },
                            "number": {
                                "between": "Entre",
                                "equals": "Igual a",
                                "gt": "Mayor a",
                                "gte": "Mayor o igual a",
                                "lt": "Menor que",
                                "lte": "Menor o igual que",
                                "notBetween": "No entre",
                                "notEmpty": "No vacío",
                                "not": "Diferente de",
                                "empty": "Vacío"
                            },
                            "string": {
                                "contains": "Contiene",
                                "empty": "Vacío",
                                "endsWith": "Termina en",
                                "equals": "Igual a",
                                "startsWith": "Empieza con",
                                "not": "Diferente de",
                                "notContains": "No Contiene",
                                "notStartsWith": "No empieza con",
                                "notEndsWith": "No termina con",
                                "notEmpty": "No Vacío"
                            },
                            "array": {
                                "not": "Diferente de",
                                "equals": "Igual",
                                "empty": "Vacío",
                                "contains": "Contiene",
                                "notEmpty": "No Vacío",
                                "without": "Sin"
                            }
                        },
                        "data": "Data",
                        "deleteTitle": "Eliminar regla de filtrado",
                        "leftTitle": "Criterios anulados",
                        "logicAnd": "Y",
                        "logicOr": "O",
                        "rightTitle": "Criterios de sangría",
                        "title": {
                            "0": "Constructor de búsqueda",
                            "_": "Constructor de búsqueda (%d)"
                        },
                        "value": "Valor"
                    },
                    "searchPanes": {
                        "clearMessage": "Borrar todo",
                        "collapse": {
                            "0": "Paneles de búsqueda",
                            "_": "Paneles de búsqueda (%d)"
                        },
                        "count": "{total}",
                        "countFiltered": "{shown} ({total})",
                        "emptyPanes": "Sin paneles de búsqueda",
                        "loadMessage": "Cargando paneles de búsqueda",
                        "title": "Filtros Activos - %d",
                        "showMessage": "Mostrar Todo",
                        "collapseMessage": "Colapsar Todo"
                    },
                    "select": {
                        "cells": {
                            "1": "1 celda seleccionada",
                            "_": "%d celdas seleccionadas"
                        },
                        "columns": {
                            "1": "1 columna seleccionada",
                            "_": "%d columnas seleccionadas"
                        },
                        "rows": {
                            "1": "1 fila seleccionada",
                            "_": "%d filas seleccionadas"
                        }
                    },
                    "thousands": ".",
                    "datetime": {
                        "previous": "Anterior",
                        "hours": "Horas",
                        "minutes": "Minutos",
                        "seconds": "Segundos",
                        "unknown": "-",
                        "amPm": [
                            "AM",
                            "PM"
                        ],
                        "months": {
                            "0": "Enero",
                            "1": "Febrero",
                            "10": "Noviembre",
                            "11": "Diciembre",
                            "2": "Marzo",
                            "3": "Abril",
                            "4": "Mayo",
                            "5": "Junio",
                            "6": "Julio",
                            "7": "Agosto",
                            "8": "Septiembre",
                            "9": "Octubre"
                        },
                        "weekdays": {
                            "0": "Dom",
                            "1": "Lun",
                            "2": "Mar",
                            "4": "Jue",
                            "5": "Vie",
                            "3": "Mié",
                            "6": "Sáb"
                        },
                        "next": "Siguiente"
                    },
                    "editor": {
                        "close": "Cerrar",
                        "create": {
                            "button": "Nuevo",
                            "title": "Crear Nuevo Registro",
                            "submit": "Crear"
                        },
                        "edit": {
                            "button": "Editar",
                            "title": "Editar Registro",
                            "submit": "Actualizar"
                        },
                        "remove": {
                            "button": "Eliminar",
                            "title": "Eliminar Registro",
                            "submit": "Eliminar",
                            "confirm": {
                                "_": "¿Está seguro de que desea eliminar %d filas?",
                                "1": "¿Está seguro de que desea eliminar 1 fila?"
                            }
                        },
                        "error": {
                            "system": "Ha ocurrido un error en el sistema (<a target=\"\\\" rel=\"\\ nofollow\" href=\"\\\">Más información&lt;\\\/a&gt;).<\/a>"
                        },
                        "multi": {
                            "title": "Múltiples Valores",
                            "restore": "Deshacer Cambios",
                            "noMulti": "Este registro puede ser editado individualmente, pero no como parte de un grupo.",
                            "info": "Los elementos seleccionados contienen diferentes valores para este registro. Para editar y establecer todos los elementos de este registro con el mismo valor, haga clic o pulse aquí, de lo contrario conservarán sus valores individuales."
                        }
                    },
                    "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
                    "stateRestore": {
                        "creationModal": {
                            "button": "Crear",
                            "name": "Nombre:",
                            "order": "Clasificación",
                            "paging": "Paginación",
                            "select": "Seleccionar",
                            "columns": {
                                "search": "Búsqueda de Columna",
                                "visible": "Visibilidad de Columna"
                            },
                            "title": "Crear Nuevo Estado",
                            "toggleLabel": "Incluir:",
                            "scroller": "Posición de desplazamiento",
                            "search": "Búsqueda",
                            "searchBuilder": "Búsqueda avanzada"
                        },
                        "removeJoiner": "y",
                        "removeSubmit": "Eliminar",
                        "renameButton": "Cambiar Nombre",
                        "duplicateError": "Ya existe un Estado con este nombre.",
                        "emptyStates": "No hay Estados guardados",
                        "removeTitle": "Remover Estado",
                        "renameTitle": "Cambiar Nombre Estado",
                        "emptyError": "El nombre no puede estar vacío.",
                        "removeConfirm": "¿Seguro que quiere eliminar %s?",
                        "removeError": "Error al eliminar el Estado",
                        "renameLabel": "Nuevo nombre para %s:"
                    },
                    "infoThousands": "."
                },
                columnDefs: [{
                    targets: [0, 2],
                    orderable: false
                }],
                order: [
                    [1, 'asc']
                ]
            });
        }

        function mostrarErrorMunicipios(message) {
            $('#loadingMunicipios').hide();
            $('#errorMessage').text(message);
            $('#errorMunicipios').show();
        }

        function exportarMunicipios() {
            if (!currentEstado) return;

            const municipios = [];
            $('#municipiosTableBody tr').each(function() {
                const municipio = $(this).find('td:nth-child(2)').text();
                municipios.push(municipio);
            });

            if (municipios.length === 0) {
                showNotification('warning', 'Advertencia', 'No hay municipios para exportar');
                return;
            }

            // Crear y descargar archivo CSV
            const csvContent = "data:text/csv;charset=utf-8," +
                `Estado,Municipio\n` +
                municipios.map(municipio => `"${currentEstado}","${municipio}"`).join('\n');

            const encodedUri = encodeURI(csvContent);
            const link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", `municipios_${currentEstado.replace(/\s+/g, '_')}.csv`);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            showNotification('success', '¡Éxito!', 'Lista de municipios exportada correctamente');
        }

        // Función global para recargar tabla
        window.recargarTabla = function() {
            if (estadosTable) {
                estadosTable.ajax.reload(null, false); // false = mantener página actual
                showNotification('info', 'Información', 'Tabla actualizada');
            }
        };
    });

    // Funciones adicionales para mejorar UX
    $(window).on('load', function() {
        // Agregar animación fade-in a las cards
        $('.fade-in').each(function(index) {
            $(this).delay(index * 100).animate({
                opacity: 1
            }, 300);
        });
    });

    // Manejar errores de red
    $(document).ajaxStart(function() {
        // Opcional: mostrar indicador global de carga
    }).ajaxStop(function() {
        // Opcional: ocultar indicador global de carga
    });
</script>
@endpush