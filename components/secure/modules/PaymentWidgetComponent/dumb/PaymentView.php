<?php
use yii\helpers\Url;
?>

<div style="padding: 20px;">
    <div style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
        <h2 style="margin-bottom: 20px; color: #333; border-bottom: 2px solid #eee; padding-bottom: 10px;">
            <?= $isClient ? 'Renovar Mi Membresia' : 'Registro de Pagos' ?>
        </h2>

        <?php if (!$isClient): ?>
        <div style="margin-bottom: 20px;">
            <input type="text" id="client-search" placeholder="Buscar cliente por nombre, CI o correo..." style="padding: 10px; border-radius: 5px; border: 1px solid #ccc; width: 100%; box-sizing: border-box;">
            <div id="search-results" style="max-height: 200px; overflow-y: auto; border: 1px solid #eee; border-radius: 5px; margin-top: 5px; display: none;"></div>
        </div>
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: <?= $isClient ? '1fr' : '1fr 1fr' ?>; gap: 30px;">
            <?php if (!$isClient): ?>
            <div>
                <h4 style="color: #0056b3; margin-bottom: 15px;">Cliente Seleccionado</h4>
                <div id="selected-client-info" style="padding: 15px; background: #f8f9fa; border-radius: 5px; border: 1px solid #e9ecef; min-height: 50px;">
                    <span style="color: #999;">Ningun cliente seleccionado</span>
                </div>
                <input type="hidden" id="client-ci" value="">
            </div>
            <?php endif; ?>

            <div>
                <h4 style="color: #0056b3; margin-bottom: 15px;">Seleccionar Plan</h4>
                <select id="plan-select" class="form-control" style="padding: 10px; border-radius: 5px; border: 1px solid #ccc;">
                    <option value="">-- Seleccione un plan --</option>
                    <?php foreach ($plans as $plan): ?>
                        <option value="<?= $plan->ID_PLAN ?>" data-amount="<?= $plan->MONTO ?>" data-type="<?= htmlspecialchars($plan->TIPO_PLAN) ?>" data-client-type="<?= htmlspecialchars($plan->TIPO_CLIENTE) ?>">
                            <?= htmlspecialchars($plan->NOMBRE_PLAN) ?> - <?= htmlspecialchars($plan->getTipoPlanLabel()) ?> (<?= htmlspecialchars($plan->TIPO_CLIENTE) ?>) - Bs. <?= number_format($plan->MONTO, 2) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div id="payment-summary" style="display: none; margin-top: 20px; padding: 20px; background: #f8f9fa; border-radius: 10px; border: 1px solid #e9ecef;">
            <h4 style="margin-bottom: 15px;">Resumen del Pago</h4>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 20px;">
                <?php if (!$isClient): ?>
                <div><strong>Cliente:</strong> <span id="summary-client"></span></div>
                <?php endif; ?>
                <div><strong>Plan:</strong> <span id="summary-plan"></span></div>
                <div><strong>Monto:</strong> <span id="summary-amount" style="color: #28a745; font-weight: bold;"></span></div>
                <div><strong>Tipo:</strong> <span id="summary-type"></span></div>
            </div>

            <div style="display: flex; gap: 15px; justify-content: center;">
                <button type="button" id="btn-pay-qr" class="btn btn-success" style="border-radius: 20px; font-weight: bold; padding: 12px 30px;">
                    Pagar con QR
                </button>
                <button type="button" id="btn-pay-cash" class="btn btn-primary" style="border-radius: 20px; font-weight: bold; padding: 12px 30px;">
                    Pago en Efectivo
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var clientSearch = document.getElementById('client-search');
    var searchResults = document.getElementById('search-results');
    var selectedClientInfo = document.getElementById('selected-client-info');
    var clientCiInput = document.getElementById('client-ci');
    var planSelect = document.getElementById('plan-select');
    var paymentSummary = document.getElementById('payment-summary');
    var btnPayQr = document.getElementById('btn-pay-qr');
    var btnPayCash = document.getElementById('btn-pay-cash');
    var isClient = <?= $isClient ? 'true' : 'false' ?>;
    var searchTimeout = null;

    if (clientSearch) {
        clientSearch.addEventListener('input', function() {
            var query = this.value.trim();
            clearTimeout(searchTimeout);
            
            if (query.length < 2) {
                searchResults.style.display = 'none';
                return;
            }

            searchTimeout = setTimeout(function() {
                fetch('<?= Url::to(["payment/search-clients"]) ?>?q=' + encodeURIComponent(query))
                    .then(function(res) { return res.json(); })
                    .then(function(data) {
                        if (data.results && data.results.length > 0) {
                            var html = '';
                            data.results.forEach(function(client) {
                                html += '<div class="search-result-item" data-ci="' + client.ci + '" data-name="' + client.nombre + '" style="padding: 10px; cursor: pointer; border-bottom: 1px solid #eee;">' +
                                    '<strong>' + client.ci + '</strong> - ' + client.nombre + '<br>' +
                                    '<small style="color: #666;">' + client.correo + ' (' + client.tipo_cliente + ')</small>' +
                                    '</div>';
                            });
                            searchResults.innerHTML = html;
                            searchResults.style.display = 'block';

                            searchResults.querySelectorAll('.search-result-item').forEach(function(item) {
                                item.addEventListener('click', function() {
                                    selectClient(this.dataset.ci, this.dataset.name);
                                    searchResults.style.display = 'none';
                                    clientSearch.value = this.dataset.name;
                                });
                            });
                        } else {
                            searchResults.innerHTML = '<div style="padding: 10px; color: #999;">No se encontraron clientes</div>';
                            searchResults.style.display = 'block';
                        }
                    });
            }, 300);
        });

        document.addEventListener('click', function(e) {
            if (!clientSearch.contains(e.target) && !searchResults.contains(e.target)) {
                searchResults.style.display = 'none';
            }
        });
    }

    function selectClient(ci, name) {
        clientCiInput.value = ci;
        selectedClientInfo.innerHTML = '<strong>CI:</strong> ' + ci + '<br><strong>Nombre:</strong> ' + name;
        updateSummary();
    }

    function updateSummary() {
        var clientCi = isClient ? '<?= Yii::$app->user->identity->CI ?>' : clientCiInput.value;
        var planOption = planSelect.options[planSelect.selectedIndex];

        if ((!isClient && clientCi) && planSelect.value) {
            document.getElementById('summary-client').textContent = selectedClientInfo.querySelector('strong + span, strong') ? selectedClientInfo.textContent : '';
            document.getElementById('summary-plan').textContent = planOption.text.split(' - ')[0];
            document.getElementById('summary-amount').textContent = 'Bs. ' + parseFloat(planOption.dataset.amount).toFixed(2);
            document.getElementById('summary-type').textContent = planOption.dataset.type;
            paymentSummary.style.display = 'block';
        } else if (isClient && planSelect.value) {
            document.getElementById('summary-plan').textContent = planOption.text.split(' - ')[0];
            document.getElementById('summary-amount').textContent = 'Bs. ' + parseFloat(planOption.dataset.amount).toFixed(2);
            document.getElementById('summary-type').textContent = planOption.dataset.type;
            paymentSummary.style.display = 'block';
        } else {
            paymentSummary.style.display = 'none';
        }
    }

    planSelect.addEventListener('change', updateSummary);

    function processPayment(method) {
        var clientCi = isClient ? '<?= Yii::$app->user->identity->CI ?>' : clientCiInput.value;
        var planId = planSelect.value;

        if (!clientCi || !planId) {
            Swal.fire('Error', 'Seleccione cliente y plan', 'error');
            return;
        }

        fetch('<?= Url::to(["payment/check-active-membership"]) ?>?ci=' + encodeURIComponent(clientCi))
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data.hasActive) {
                    Swal.fire('Error', 'El cliente ya tiene una membresia activa hasta ' + data.endDate + ' (' + data.remainingDays + ' dias restantes). Debe esperar a que venza para realizar un nuevo pago.', 'warning');
                    return;
                }

                var btn = method === 'QR' ? btnPayQr : btnPayCash;
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Procesando...';

                var url = method === 'QR' ? '<?= Url::to(["payment/process-qr"]) ?>' : '<?= Url::to(["payment/process-cash"]) ?>';

                return fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: 'client_ci=' + encodeURIComponent(clientCi) + '&plan_id=' + encodeURIComponent(planId)
                })
                .then(function(res) { return res.json(); })
                .then(function(data) {
                    if (data.status === 'success') {
                        showReceiptPopup(data);
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                })
                .catch(function() {
                    Swal.fire('Error', 'Error de conexion', 'error');
                })
                .finally(function() {
                    btn.disabled = false;
                    btn.innerHTML = method === 'QR' ? 'Pagar con QR' : 'Pago en Efectivo';
                });
            });
    }

    function showReceiptPopup(data) {
        var receipt = data.receipt;
        var invoice = data.invoice;
        
        var html = '<div style="text-align: left; font-size: 14px; position: relative;">';
        html += '<span onclick="Swal.close();" style="position: absolute; top: -10px; right: -10px; cursor: pointer; font-size: 24px; color: #999; background: white; border-radius: 50%; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 5px rgba(0,0,0,0.2);">&times;</span>';
        html += '<div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 15px;">';
        html += '<h4 style="margin: 0 0 10px; color: #0056b3;">Recibo #' + receipt.id_recibo + '</h4>';
        html += '<p><strong>Fecha:</strong> ' + receipt.fecha + '</p>';
        html += '<p><strong>Cliente:</strong> ' + receipt.cliente.nombre + ' (CI: ' + receipt.cliente.ci + ')</p>';
        html += '<p><strong>Tipo Cliente:</strong> ' + receipt.cliente.tipo_cliente + '</p>';
        if (receipt.administrador) {
            html += '<p><strong>Atendido por:</strong> ' + receipt.administrador.nombre + '</p>';
        }
        html += '</div>';
        
        html += '<div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 15px;">';
        html += '<p><strong>Plan:</strong> ' + (receipt.plan ? receipt.plan.nombre : 'N/A') + '</p>';
        html += '<p><strong>Tipo Plan:</strong> ' + (receipt.plan ? receipt.plan.tipo_plan : 'N/A') + '</p>';
        html += '<p><strong>Membresia:</strong> ' + receipt.membresia.codigo + '</p>';
        html += '<p><strong>Vigencia:</strong> ' + receipt.membresia.fecha_inicio + ' a ' + receipt.membresia.fecha_fin + '</p>';
        html += '</div>';
        
        html += '<div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 15px;">';
        html += '<p><strong>Metodo de Pago:</strong> ' + receipt.tipo_pago_label + '</p>';
        if (receipt.codigo_pago) html += '<p><strong>Codigo Pago:</strong> ' + receipt.codigo_pago + '</p>';
        if (receipt.nro_documento) html += '<p><strong>Nro Documento:</strong> ' + receipt.nro_documento + '</p>';
        html += '<p style="font-size: 24px; color: #28a745; font-weight: bold; text-align: center; margin: 10px 0;">Bs. ' + parseFloat(receipt.monto).toFixed(2) + '</p>';
        html += '</div>';
        
        if (invoice && invoice.success) {
            html += '<div style="background: #d4edda; padding: 10px; border-radius: 8px; border: 1px solid #c3e6cb;">';
            html += '<p style="margin: 0; color: #155724;"><strong>Factura electronica emitida a:</strong> ' + invoice.sent_to + '</p>';
            html += '</div>';
        }
        
        html += '</div>';

        Swal.fire({
            title: 'Pago Exitoso',
            html: html,
            width: 600,
            showCancelButton: true,
            confirmButtonText: 'Imprimir Recibo',
            cancelButtonText: 'Cerrar',
            showDenyButton: true,
            denyButtonText: 'Descargar PDF'
        }).then(function(result) {
            if (result.isConfirmed) {
                printReceipt(receipt.id_recibo);
            } else if (result.isDenied) {
                downloadReceiptPdf(receipt.id_recibo);
            }
        });
    }

    function printReceipt(paymentId) {
        var url = '<?= Url::to(["payment/export-receipt-pdf"]) ?>?id=' + paymentId;
        var win = window.open('', '_blank');
        fetch(url)
            .then(function(res) { return res.text(); })
            .then(function(html) {
                win.document.open();
                win.document.write(html);
                win.document.close();
                setTimeout(function() { win.print(); }, 500);
            });
    }

    function downloadReceiptPdf(paymentId) {
        var url = '<?= Url::to(["payment/export-receipt-pdf"]) ?>?id=' + paymentId;
        var win = window.open('', '_blank');
        fetch(url)
            .then(function(res) { return res.text(); })
            .then(function(html) {
                win.document.open();
                win.document.write(html);
                win.document.close();
            });
    }

    btnPayQr.addEventListener('click', function() { processPayment('QR'); });
    btnPayCash.addEventListener('click', function() { processPayment('EFECTIVO'); });
});
</script>
