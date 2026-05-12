<?php
use yii\helpers\Html;
use yii\helpers\Url;
?>

<div style="padding: 20px;">
    <div style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
        <h2 style="margin-bottom: 20px; color: #333; border-bottom: 2px solid #eee; padding-bottom: 10px;">
            Control de Entrada
        </h2>

        <div style="display: flex; gap: 20px; margin-bottom: 30px; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 250px;">
                <h4 style="color: #0056b3; margin-bottom: 10px;">Busqueda Manual por CI</h4>
                <div style="display: flex; gap: 10px;">
                    <input type="text" id="manual-ci-input" placeholder="Ingrese CI del cliente..." style="padding: 10px; border-radius: 5px; border: 1px solid #ccc; flex-grow: 1;">
                    <button type="button" id="btn-manual-search" class="btn btn-primary" style="border-radius: 5px;">Verificar</button>
                </div>
            </div>

            <div style="flex: 1; min-width: 250px; text-align: center;">
                <h4 style="color: #0056b3; margin-bottom: 10px;">Estado del Dispositivo</h4>
                <div id="device-status" style="padding: 10px; border-radius: 5px; background: #e9ecef; display: inline-block;">
                    <span style="color: #666;">Verificando...</span>
                </div>
                <div style="margin-top: 10px;">
                    <button type="button" id="btn-simulate" class="btn btn-warning btn-sm" style="border-radius: 15px; font-weight: bold;">
                        Simular Acceso Biometrico
                    </button>
                </div>
            </div>
        </div>

        <div style="margin-top: 20px;">
            <h4 style="color: #333; margin-bottom: 15px;">Ultimos Ingresos</h4>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background-color: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                            <th style="padding: 12px; text-align: left;">CI</th>
                            <th style="padding: 12px; text-align: left;">Nombre</th>
                            <th style="padding: 12px; text-align: left;">Fecha/Hora</th>
                            <th style="padding: 12px; text-align: center;">Estado</th>
                        </tr>
                    </thead>
                    <tbody id="recent-attendances-body">
                        <?php if (empty($recentAttendances)): ?>
                            <tr>
                                <td colspan="4" style="text-align: center; padding: 30px; color: #666;">No hay registros recientes</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recentAttendances as $att): ?>
                                <tr style="border-bottom: 1px solid #e9ecef;">
                                    <td style="padding: 12px;"><?= Html::encode($att->CI_CLIENTE) ?></td>
                                    <td style="padding: 12px;">
                                        <?php if ($att->client): ?>
                                            <?= Html::encode($att->client->NOMBRE_COMPLETO) ?>
                                        <?php else: ?>
                                            <em>Desconocido</em>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding: 12px;"><?= date('d/m/Y H:i:s', strtotime($att->FECHA_DE_INGRESO)) ?></td>
                                    <td style="padding: 12px; text-align: center;">
                                        <span style="background-color: #28a745; color: white; padding: 4px 12px; border-radius: 15px; font-size: 12px;">Acceso</span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var manualCiInput = document.getElementById('manual-ci-input');
    var btnManualSearch = document.getElementById('btn-manual-search');
    var btnSimulate = document.getElementById('btn-simulate');
    var deviceStatus = document.getElementById('device-status');
    var lastEventId = '0';
    var pollInterval = null;

    // Polling instead of SSE (works with PHP built-in server)
    function startPolling() {
        checkDeviceStatus();

        pollInterval = setInterval(function() {
            fetch('<?= Url::to(["access-control/poll-events"]) ?>?lastId=' + lastEventId)
                .then(function(res) { return res.json(); })
                .then(function(data) {
                    if (data.deviceOnline !== undefined) {
                        updateDeviceStatus(data.deviceOnline);
                    }

                    if (data.events && data.events.length > 0) {
                        data.events.forEach(function(event) {
                            showAccessPopup(event.data);
                            lastEventId = event.id;
                        });
                        refreshAttendances();
                    }
                })
                .catch(function() {
                    // Silently fail, will retry on next interval
                });
        }, 3000);
    }

    function checkDeviceStatus() {
        fetch('<?= Url::to(["access-control/poll-events"]) ?>')
            .then(function(res) { return res.json(); })
            .then(function(data) {
                updateDeviceStatus(data.deviceOnline);
            })
            .catch(function() {
                updateDeviceStatus(false);
            });
    }

    function updateDeviceStatus(online) {
        if (online) {
            deviceStatus.innerHTML = '<span style="color: #28a745;">Dispositivo conectado</span>';
            deviceStatus.style.background = '#d4edda';
        } else {
            deviceStatus.innerHTML = '<span style="color: #dc3545;">Dispositivo no disponible (modo simulacion)</span>';
            deviceStatus.style.background = '#f8d7da';
        }
    }

    function refreshAttendances() {
        fetch('<?= Url::to(["access-control/index"]) ?>')
            .then(function(res) { return res.text(); })
            .then(function(html) {
                var parser = new DOMParser();
                var doc = parser.parseFromString(html, 'text/html');
                var newBody = doc.getElementById('recent-attendances-body');
                if (newBody) {
                    document.getElementById('recent-attendances-body').innerHTML = newBody.innerHTML;
                }
            })
            .catch(function() {});
    }

    // Manual search
    btnManualSearch.addEventListener('click', function() {
        var ci = manualCiInput.value.trim();
        if (!ci) {
            Swal.fire('Error', 'Ingrese un CI valido', 'warning');
            return;
        }

        btnManualSearch.disabled = true;
        btnManualSearch.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Verificando...';

        fetch('<?= Url::to(["access-control/manual-search"]) ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: 'ci=' + encodeURIComponent(ci)
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            showManualResult(data);
        })
        .catch(function() {
            Swal.fire('Error', 'Error de conexion', 'error');
        })
        .finally(function() {
            btnManualSearch.disabled = false;
            btnManualSearch.innerHTML = 'Verificar';
        });
    });

    manualCiInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            btnManualSearch.click();
        }
    });

    // Simulate biometric access
    btnSimulate.addEventListener('click', function() {
        var ci = manualCiInput.value.trim();
        if (!ci) {
            Swal.fire({
                title: 'Simular Acceso Biometrico',
                input: 'text',
                inputLabel: 'Ingrese CI del cliente para simular',
                inputPlaceholder: 'Ej: 13722192',
                showCancelButton: true,
                confirmButtonText: 'Simular',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#ffc107',
            }).then(function(result) {
                if (result.isConfirmed && result.value) {
                    simulateAccess(result.value);
                }
            });
        } else {
            simulateAccess(ci);
        }
    });

    function simulateAccess(ci) {
        btnSimulate.disabled = true;
        btnSimulate.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Simulando...';

        fetch('<?= Url::to(["access-control/simulate-access"]) ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: 'ci=' + encodeURIComponent(ci)
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            showAccessPopup(data);
            refreshAttendances();
        })
        .catch(function() {
            Swal.fire('Error', 'Error de simulacion', 'error');
        })
        .finally(function() {
            btnSimulate.disabled = false;
            btnSimulate.innerHTML = 'Simular Acceso Biometrico';
        });
    }

    function showAccessPopup(data) {
        if (data.status === 'granted') {
            var avatarHtml = data.user && data.user.avatar
                ? '<img src="' + data.user.avatar + '" style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 4px solid #28a745; margin-bottom: 20px;">'
                : '<div style="width: 120px; height: 120px; background: #28a745; border-radius: 50%; color: white; display: flex; align-items: center; justify-content: center; font-size: 50px; font-weight: bold; margin: 0 auto 20px;">' + (data.user ? data.user.nombre.charAt(0) : '?') + '</div>';

            Swal.fire({
                html: '<div style="padding: 10px;">' + avatarHtml +
                    '<h2 style="color: #28a745; margin: 0;">ACCESO PERMITIDO</h2>' +
                    '<h3 style="color: #333; margin: 10px 0;">' + (data.user ? data.user.nombre : '') + '</h3>' +
                    '<p style="color: #666;">CI: ' + (data.user ? data.user.ci : '') + '</p>' +
                    '<div style="background: #f8f9fa; padding: 15px; border-radius: 10px; margin-top: 15px;">' +
                    '<strong>Dias restantes:</strong> ' + (data.remainingDays || 0) + '</div></div>',
                showConfirmButton: false,
                timer: 5000,
                width: '500px',
            });
        } else {
            var avatarHtml = data.user && data.user.avatar
                ? '<img src="' + data.user.avatar + '" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 4px solid #dc3545; margin-bottom: 15px;">'
                : '<div style="width: 100px; height: 100px; background: #dc3545; border-radius: 50%; color: white; display: flex; align-items: center; justify-content: center; font-size: 40px; font-weight: bold; margin: 0 auto 15px;">?</div>';

            Swal.fire({
                html: '<div style="padding: 10px;">' + avatarHtml +
                    '<h2 style="color: #dc3545; margin: 0;">ACCESO DENEGADO</h2>' +
                    '<p style="color: #666; margin: 15px 0;">' + data.reason + '</p>' +
                    (data.user ? '<p style="color: #333;">' + data.user.nombre + ' (CI: ' + data.user.ci + ')</p>' : '') +
                    '</div>',
                showCancelButton: true,
                confirmButtonText: 'Ir a Pagos',
                cancelButtonText: 'Cerrar',
                confirmButtonColor: '#0056b3',
            }).then(function(result) {
                if (result.isConfirmed) {
                    window.location.href = '<?= Url::to(["payment/index"]) ?>';
                }
            });
        }
    }

    function showManualResult(data) {
        if (data.status === 'granted') {
            var avatarHtml = data.user.avatar
                ? '<img src="' + data.user.avatar + '" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 4px solid #28a745; margin-bottom: 15px;">'
                : '<div style="width: 100px; height: 100px; background: #28a745; border-radius: 50%; color: white; display: flex; align-items: center; justify-content: center; font-size: 40px; font-weight: bold; margin: 0 auto 15px;">' + data.user.nombre.charAt(0) + '</div>';

            Swal.fire({
                icon: 'success',
                title: 'Acceso Permitido',
                html: avatarHtml + '<strong>' + data.user.nombre + '</strong><br>CI: ' + data.user.ci + '<br>Dias restantes: ' + data.remainingDays,
                confirmButtonText: 'OK',
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Acceso Denegado',
                text: data.reason,
                confirmButtonText: 'OK',
            });
        }
    }

    // Start polling
    startPolling();

    // Stop polling when leaving page
    window.addEventListener('beforeunload', function() {
        if (pollInterval) {
            clearInterval(pollInterval);
        }
    });
});
</script>
