<?php
use yii\helpers\Html;
use yii\helpers\Url;
?>

<div style="padding: 20px;">
    <div style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
        <h2 style="margin-bottom: 20px; color: #333; border-bottom: 2px solid #eee; padding-bottom: 10px;">
            Registro de Pagos
        </h2>

        <form id="filter-form" method="GET" action="<?= Url::to(['payment/index']) ?>" style="display: flex; gap: 15px; margin-bottom: 20px;">
            <input type="text" name="search" value="<?= Html::encode($search) ?>" placeholder="Buscar cliente por nombre o CI..." style="padding: 10px; border-radius: 5px; border: 1px solid #ccc; flex-grow: 1;">
            <button type="submit" class="btn btn-primary" style="border-radius: 5px;">Buscar</button>
            <a href="<?= Url::to(['payment/index']) ?>" class="btn btn-light" style="border-radius: 5px;">Limpiar</a>
        </form>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
            <div>
                <h4 style="color: #0056b3; margin-bottom: 15px;">Seleccionar Cliente</h4>
                <select id="client-select" class="form-control" style="padding: 10px; border-radius: 5px; border: 1px solid #ccc;">
                    <option value="">-- Seleccione un cliente --</option>
                    <?php foreach ($clients as $client): ?>
                        <option value="<?= Html::encode($client->CI) ?>" data-name="<?= Html::encode($client->NOMBRE_COMPLETO)">
                            <?= Html::encode($client->CI) ?> - <?= Html::encode($client->NOMBRE_COMPLETO) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <h4 style="color: #0056b3; margin-bottom: 15px;">Seleccionar Plan</h4>
                <select id="plan-select" class="form-control" style="padding: 10px; border-radius: 5px; border: 1px solid #ccc;">
                    <option value="">-- Seleccione un plan --</option>
                    <?php foreach ($plans as $plan): ?>
                        <option value="<?= $plan->ID_PLAN ?>" data-amount="<?= $plan->MONTO ?>" data-type="<?= Html::encode($plan->TIPO_PLAN) ?>">
                            <?= Html::encode($plan->NOMBRE_PLAN) ?> - <?= Html::encode($plan->getTipoPlanLabel()) ?> (<?= Html::encode($plan->TIPO_CLIENTE) ?>) - Bs. <?= number_format($plan->MONTO, 2) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div id="payment-summary" style="display: none; margin-top: 20px; padding: 20px; background: #f8f9fa; border-radius: 10px; border: 1px solid #e9ecef;">
            <h4 style="margin-bottom: 15px;">Resumen del Pago</h4>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 20px;">
                <div><strong>Cliente:</strong> <span id="summary-client"></span></div>
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
    const clientSelect = document.getElementById('client-select');
    const planSelect = document.getElementById('plan-select');
    const paymentSummary = document.getElementById('payment-summary');
    const btnPayQr = document.getElementById('btn-pay-qr');
    const btnPayCash = document.getElementById('btn-pay-cash');

    function updateSummary() {
        const clientOption = clientSelect.options[clientSelect.selectedIndex];
        const planOption = planSelect.options[planSelect.selectedIndex];

        if (clientSelect.value && planSelect.value) {
            document.getElementById('summary-client').textContent = clientOption.dataset.name;
            document.getElementById('summary-plan').textContent = planOption.text.split(' - ')[0];
            document.getElementById('summary-amount').textContent = 'Bs. ' + parseFloat(planOption.dataset.amount).toFixed(2);
            document.getElementById('summary-type').textContent = planOption.dataset.type;
            paymentSummary.style.display = 'block';
        } else {
            paymentSummary.style.display = 'none';
        }
    }

    clientSelect.addEventListener('change', updateSummary);
    planSelect.addEventListener('change', updateSummary);

    function processPayment(method) {
        const clientCi = clientSelect.value;
        const planId = planSelect.value;

        if (!clientCi || !planId) {
            Swal.fire('Error', 'Seleccione cliente y plan', 'error');
            return;
        }

        const btn = method === 'QR' ? btnPayQr : btnPayCash;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Procesando...';

        const url = method === 'QR' ? '<?= Url::to(["payment/process-qr"]) ?>' : '<?= Url::to(["payment/process-cash"]) ?>';

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: `client_ci=${encodeURIComponent(clientCi)}&plan_id=${encodeURIComponent(planId)}`
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Pago Exitoso',
                    html: `Membresia: <strong>${data.membership_code}</strong><br>Recibo: <strong>${data.payment_id}</strong>`,
                    confirmButtonText: 'Ir al Historial'
                }).then(() => {
                    window.location.href = '<?= Url::to(["history/index"]) ?>';
                });
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        })
        .catch(err => {
            Swal.fire('Error', 'Error de conexion', 'error');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = method === 'QR' ? 'Pagar con QR' : 'Pago en Efectivo';
        });
    }

    btnPayQr.addEventListener('click', () => processPayment('QR'));
    btnPayCash.addEventListener('click', () => processPayment('EFECTIVO'));
});
</script>
