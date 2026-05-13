<?php
use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
use app\shared\enums\ClientType;
use app\shared\enums\ClientStatus; 
use yii\helpers\Url;
?>
<div class="create-user-form-view" style="padding: 20px;">

    <div style="<?= $isPublicContext ? 'margin: 0 auto; background: white;' : 'background: white;' ?> padding: 40px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); max-width: 600px;">
        
        <h2 style="margin-bottom: 30px; color: #333; text-align: <?= $isPublicContext ? 'center' : 'left' ?>;">
            Crear / Registrar Usuario
        </h2>

        <?php $form = ActiveForm::begin([
            'id' => 'create-user-form',
            'layout' => 'horizontal',
            'fieldConfig' => [
                'template' => "{label}\n{input}\n{error}",
                'labelOptions' => ['style' => 'font-weight:bold;'],
                'horizontalCssClasses' => ['input' => 'col-sm-8'],
            ],
        ]); ?>

        <?php if (!$isPublicContext): ?>
            <?= $form->field($model, 'ROL')->dropDownList($availableRoles, ['prompt' => 'Seleccione un Rol', 'id' => 'drop-rol']) ?>
        <?php endif; ?>

        <div style="display: flex; gap: 10px; align-items: flex-end;">
            <div style="flex-grow: 1;">
                <?= $form->field($model, 'CI')->textInput(['placeholder' => 'Carnet de Identidad', 'id' => 'input-ci']) ?>
            </div>
            <div id="btn-universidad-wrapper" style="display: none;">
                <?= Html::button('Consultar Universidad', ['class' => 'btn btn-info', 'id' => 'btn-consultar-universidad', 'style' => 'border-radius: 20px; height: 40px;']) ?>
            </div>
        </div>

        <div id="university-result" style="display: none; padding: 15px; border-radius: 8px; margin-bottom: 15px;"></div>

        <?= $form->field($model, 'NOMBRE_COMPLETO')->textInput(['id' => 'input-nombre']) ?>
        <?= $form->field($model, 'TELEFONO')->textInput(['id' => 'input-telefono']) ?>
        <?= $form->field($model, 'CORREO_ELECTRONICO')->textInput(['placeholder' => 'email@gym.com', 'id' => 'input-correo']) ?>
        
        <?= $form->field($model, 'ESTADO')->hiddenInput(['value' => ClientStatus::ACTIVE->value, 'id' => 'input-estado'])->label(false) ?>
        <?= $form->field($model, 'TIPO_CLIENTE')->hiddenInput(['id' => 'input-tipo-cliente'])->label(false) ?>

        <div id="section-huella" style="display: none; border: 1px solid #ddd; padding: 20px; border-radius: 5px; margin-bottom: 20px;">
            <h4 style="margin-top: 0;">Registro de Huella Digital</h4>
            <p style="color: #666; font-size: 13px; margin-bottom: 15px;">
                <?= $isPublicContext ? 'Podra registrar su huella cuando llegue al gimnasio.' : 'Debe registrar la huella digital antes de continuar.' ?>
            </p>
            <?php if (!$isPublicContext): ?>
                <div style="display: flex; gap: 20px; align-items: center; margin-bottom: 15px;">
                    <div style="width: 100px; height: 100px; background: #eee; border: 1px solid #ccc; display: flex; align-items: center; justify-content: center; border-radius: 5px;">
                        <span id="label-huella-status" style="font-size: 12px; color: #666;">Sin Huella</span>
                    </div>
                    <?= Html::button('Registrar Huella (Flask API)', ['class' => 'btn btn-secondary', 'id' => 'btn-hardware-huella', 'style' => 'border-radius: 20px;']) ?>
                </div>
            <?php else: ?>
                <div style="padding: 15px; background: #fff3cd; border: 1px solid #ffeeba; border-radius: 5px;">
                    <p style="margin: 0; color: #856404;"><strong>Huella pendiente:</strong> El usuario podra registrar su huella al llegar al gimnasio.</p>
                </div>
            <?php endif; ?>
        </div>

        <div id="section-foto" style="display: none; border: 1px solid #ddd; padding: 20px; border-radius: 5px; margin-bottom: 20px;">
            <h4 style="margin-top: 0;">Fotografia (Webcam)</h4>
            <div style="display: flex; gap: 10px; align-items: end;">
                <video id="webcam-video" width="200" height="150" autoplay style="border: 1px solid #ccc; background: #000; border-radius: 5px;"></video>
                <img id="webcam-preview" style="display: none; width: 200px; height: 150px; border: 1px solid #ccc; border-radius: 5px; object-fit: cover;">
                <canvas id="webcam-canvas" width="200" height="150" style="display: none;"></canvas>
                
                <?= Html::button('Tomar Foto', ['class' => 'btn btn-outline-primary', 'id' => 'btn-hardware-foto', 'style' => 'border-radius: 20px;']) ?>
            </div>
            <input type="hidden" name="foto_webcam" id="input-foto-webcam" value="">
            <span id="label-foto-status" style="font-size: 12px; color: green; display: none; margin-top: 5px;">Foto capturada exitosamente!</span>
        </div>

        <div class="form-group text-center">
            <?= Html::submitButton($isPublicContext ? 'COMPLETAR REGISTRO' : 'CREAR USUARIO', ['class' => 'btn btn-success', 'id' => 'btn-action-main', 'style' => 'width: 100%; font-weight: bold; border-radius: 20px;', 'disabled' => true]) ?>
        </div>

        <?php if ($isPublicContext): ?>
            <div style="margin-top: 15px; text-align: center;">
                <?= Html::a('Volver al Login', ['login/login'], ['style' => 'color: #0056b3; text-decoration: none; font-size: 14px;']) ?>
            </div>
        <?php endif; ?>

        <?php ActiveForm::end(); ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dropRol = document.getElementById('drop-rol');
    const btnActionMain = document.getElementById('btn-action-main');
    const inputCi = document.getElementById('input-ci');
    const inputNombre = document.getElementById('input-nombre');
    const inputTelefono = document.getElementById('input-telefono');
    const inputCorreo = document.getElementById('input-correo');
    const inputEstado = document.getElementById('input-estado');
    const inputTipoCliente = document.getElementById('input-tipo-cliente');
    const universityResult = document.getElementById('university-result');
    const btnConsultarUniversidad = document.getElementById('btn-consultar-universidad');
    const btnUniversidadWrapper = document.getElementById('btn-universidad-wrapper');
    const sectionHuella = document.getElementById('section-huella');
    const sectionFoto = document.getElementById('section-foto');
    
    const video = document.getElementById('webcam-video');
    const canvas = document.getElementById('webcam-canvas');
    const btnFoto = document.getElementById('btn-hardware-foto');
    const inputFotoWebcam = document.getElementById('input-foto-webcam');
    const labelFotoStatus = document.getElementById('label-foto-status');

    const btnHuella = document.getElementById('btn-hardware-huella');
    const labelHuellaStatus = document.getElementById('label-huella-status');

    const isPublic = <?= $isPublicContext ? 'true' : 'false' ?>;
    const plans = <?= json_encode(array_map(function($p) {
        return [
            'id' => $p->ID_PLAN,
            'nombre' => $p->NOMBRE_PLAN,
            'tipo_plan' => $p->TIPO_PLAN,
            'tipo_cliente' => $p->TIPO_CLIENTE,
            'monto' => $p->MONTO,
        ];
    }, $plans ?? [])) ?>;

    var registrationData = {
        ci: '',
        nombre: '',
        telefono: '',
        correo: '',
        estado: '',
        tipoCliente: '',
        huellaRegistrada: false,
        fotoBase64: '',
        planId: null,
        paymentMethod: null
    };
    
    function startCamera() {
        if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
            navigator.mediaDevices.getUserMedia({ video: true })
                .then(function(stream) { video.srcObject = stream; })
                .catch(function(err) { console.error("Error al acceder a la camara: ", err); });
        }
    }

    if (dropRol) {
        dropRol.addEventListener('change', function() {
            const rol = this.value;
            sectionHuella.style.display = 'none';
            sectionFoto.style.display = 'none';
            universityResult.style.display = 'none';
            btnActionMain.disabled = true;

            if (rol === '<?= \app\shared\enums\Roles::ADMINISTRATOR->value ?>' || rol === '<?= \app\shared\enums\Roles::SUPER_ADMIN->value ?>') {
                btnUniversidadWrapper.style.display = 'none';
                sectionFoto.style.display = 'block';
                startCamera();
                btnActionMain.innerText = 'CREAR USUARIO';
                btnActionMain.type = "submit";
                btnActionMain.disabled = false;
            } else if (rol === '<?= \app\shared\enums\Roles::CLIENT->value ?>') {
                btnUniversidadWrapper.style.display = 'block';
                btnActionMain.disabled = true;
            } else {
                btnUniversidadWrapper.style.display = 'none';
            }
        });
    } else {
        btnUniversidadWrapper.style.display = 'block';
    }

    btnConsultarUniversidad.addEventListener('click', function() {
        const ci = inputCi.value.trim();
        if (!ci) {
            Swal.fire('Error', 'Ingrese un CI valido', 'warning');
            return;
        }

        fetch('<?= Url::to(["user-management/check-ci"]) ?>?ci=' + encodeURIComponent(ci))
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data.exists) {
                    Swal.fire('Error', 'El CI ' + ci + ' ya existe en el sistema. No se puede registrar un usuario duplicado.', 'error');
                    return;
                }

                btnConsultarUniversidad.disabled = true;
                btnConsultarUniversidad.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Consultando...';

                return fetch('<?= Url::to(["payment/validate-student"]) ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: 'ci=' + encodeURIComponent(ci)
                }).then(function(res) { return res.json(); });
            })
            .then(function(data) {
                btnConsultarUniversidad.disabled = false;
                btnConsultarUniversidad.innerText = 'Consultar Universidad';

                if (!data) return;

                if (data.es_universitario && data.es_activo) {
                    universityResult.style.display = 'block';
                    universityResult.style.background = '#d4edda';
                    universityResult.style.border = '1px solid #c3e6cb';
                    universityResult.innerHTML = '<strong style="color: #155724;">Estudiante Universitario Activo</strong><br>' +
                        'Nombre: ' + data.datos.nombre_completo + '<br>' +
                        'Correo: ' + data.datos.correo_electronico + '<br>' +
                        'Telefono: ' + data.datos.telefono;

                    inputNombre.value = data.datos.nombre_completo;
                    inputTelefono.value = data.datos.telefono;
                    inputCorreo.value = data.datos.correo_electronico;
                    inputEstado.value = 'ACTIVO';
                    inputTipoCliente.value = 'UNIVERSITARIO';

                    registrationData.ci = ci;
                    registrationData.nombre = data.datos.nombre_completo;
                    registrationData.telefono = data.datos.telefono;
                    registrationData.correo = data.datos.correo_electronico;
                    registrationData.estado = 'ACTIVO';
                    registrationData.tipoCliente = 'UNIVERSITARIO';

                    showPlanSelectionPopup(ci, 'UNIVERSITARIO');
                } else {
                    universityResult.style.display = 'block';
                    universityResult.style.background = '#fff3cd';
                    universityResult.style.border = '1px solid #ffeeba';
                    universityResult.innerHTML = '<strong style="color: #856404;">No es estudiante universitario activo</strong><br>Se registrara como cliente Externo.';

                    inputEstado.value = 'ACTIVO';
                    inputTipoCliente.value = 'EXTERNO';

                    registrationData.ci = ci;
                    registrationData.estado = 'ACTIVO';
                    registrationData.tipoCliente = 'EXTERNO';

                    showPlanSelectionPopup(ci, 'EXTERNO');
                }
            })
            .catch(function() {
                btnConsultarUniversidad.disabled = false;
                btnConsultarUniversidad.innerText = 'Consultar Universidad';
                Swal.fire('Error', 'Error de conexion con el servicio de universidad', 'error');
            });
    });

    function showPlanSelectionPopup(ci, tipoCliente) {
        const availablePlans = plans.filter(function(p) { return p.tipo_cliente === tipoCliente; });
        
        if (availablePlans.length === 0) {
            Swal.fire('Info', 'No hay planes disponibles para tipo ' + tipoCliente, 'info');
            enableUserCreation();
            return;
        }

        var planOptions = '';
        availablePlans.forEach(function(p) {
            planOptions += '<option value="' + p.id + '">' + p.nombre + ' - ' + p.tipo_plan + ' - Bs. ' + p.monto + '</option>';
        });

        Swal.fire({
            title: 'Seleccionar Plan para ' + (inputNombre.value || ci),
            html: '<div style="text-align: left;">' +
                '<p><strong>CI:</strong> ' + ci + '</p>' +
                '<p><strong>Nombre:</strong> ' + (inputNombre.value || 'N/A') + '</p>' +
                '<p><strong>Tipo:</strong> ' + tipoCliente + '</p>' +
                '<label><strong>Seleccione un plan:</strong></label>' +
                '<select id="swal-plan-select" class="form-control" style="margin-top: 10px;">' + planOptions + '</select>' +
                '</div>',
            showCancelButton: true,
            confirmButtonText: 'Continuar',
            cancelButtonText: 'Cancelar',
            preConfirm: function() {
                return document.getElementById('swal-plan-select').value;
            }
        }).then(function(result) {
            if (result.isConfirmed && result.value) {
                registrationData.planId = result.value;
                showFingerprintPopup(ci);
            }
        });
    }

    function showFingerprintPopup(ci) {
        if (isPublic) {
            registrationData.huellaRegistrada = false;
            showPaymentMethodPopup(ci);
            return;
        }

        sectionHuella.style.display = 'block';

        Swal.fire({
            title: 'Registro de Huella Requerido',
            html: '<div style="text-align: center; padding: 20px;">' +
                '<p>Debe registrar la huella digital del usuario antes de continuar.</p>' +
                '<p style="color: #666; font-size: 13px;">Use el boton "Registrar Huella" en el formulario de abajo.</p>' +
                '</div>',
            showCancelButton: true,
            confirmButtonText: 'Ya registre la huella',
            cancelButtonText: 'Cancelar',
            preConfirm: function() {
                if (!registrationData.huellaRegistrada) {
                    Swal.showValidationMessage('Debe registrar la huella primero');
                    return false;
                }
                return true;
            }
        }).then(function(result) {
            if (result.isConfirmed) {
                showPaymentMethodPopup(ci);
            }
        });
    }

    function showPaymentMethodPopup(ci) {
        Swal.fire({
            title: 'Metodo de Pago',
            html: '<div style="text-align: center; padding: 20px;">' +
                '<p>Seleccione el metodo de pago</p>' +
                '<div style="display: flex; gap: 15px; justify-content: center; margin-top: 20px;">' +
                '<button id="swal-btn-qr" class="btn btn-success" style="border-radius: 20px; padding: 15px 30px;">Pagar con QR</button>' +
                '<button id="swal-btn-cash" class="btn btn-primary" style="border-radius: 20px; padding: 15px 30px;">Pago en Efectivo</button>' +
                '</div>' +
                '</div>',
            showConfirmButton: false,
            showCancelButton: true,
            cancelButtonText: 'Cancelar',
            didOpen: function() {
                document.getElementById('swal-btn-qr').addEventListener('click', function() {
                    registrationData.paymentMethod = 'QR';
                    createUserThenPayment();
                });
                document.getElementById('swal-btn-cash').addEventListener('click', function() {
                    registrationData.paymentMethod = 'EFECTIVO';
                    createUserThenPayment();
                });
            }
        });
    }

    function createUserThenPayment() {
        Swal.fire({
            title: 'Creando usuario...',
            allowOutsideClick: false,
            didOpen: function() { Swal.showLoading(); }
        });

        fetch('<?= Url::to(["user-management/api-create"]) ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: 'User[CI]=' + encodeURIComponent(registrationData.ci) +
                '&User[NOMBRE_COMPLETO]=' + encodeURIComponent(registrationData.nombre || inputNombre.value) +
                '&User[TELEFONO]=' + encodeURIComponent(registrationData.telefono || inputTelefono.value) +
                '&User[CORREO_ELECTRONICO]=' + encodeURIComponent(registrationData.correo || inputCorreo.value) +
                '&User[ESTADO]=' + encodeURIComponent(registrationData.estado) +
                '&User[TIPO_CLIENTE]=' + encodeURIComponent(registrationData.tipoCliente) +
                '&User[ROL]=CLIENTE' +
                '&foto_webcam=' + encodeURIComponent(inputFotoWebcam.value)
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.status === 'success') {
                processPayment(registrationData.ci, registrationData.planId, registrationData.paymentMethod);
            } else {
                Swal.fire('Error', 'Error al crear usuario: ' + data.message, 'error');
            }
        })
        .catch(function(err) {
            Swal.fire('Error', 'Error de conexion: ' + err.message, 'error');
        });
    }

    function processPayment(ci, planId, paymentMethod) {
        Swal.fire({
            title: 'Procesando pago...',
            allowOutsideClick: false,
            didOpen: function() { Swal.showLoading(); }
        });

        var url = paymentMethod === 'QR' ? '<?= Url::to(["payment/process-qr"]) ?>' : '<?= Url::to(["payment/process-cash"]) ?>';

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: 'client_ci=' + encodeURIComponent(ci) + '&plan_id=' + encodeURIComponent(planId)
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
        });
    }

    function enableUserCreation() {
        btnActionMain.disabled = false;
        btnActionMain.type = "submit";
        btnActionMain.innerText = 'CREAR USUARIO';
        sectionFoto.style.display = 'block';
        startCamera();
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
            title: 'Registro y Pago Exitosos',
            html: html,
            width: 600,
            showCancelButton: true,
            confirmButtonText: 'Imprimir Recibo',
            cancelButtonText: 'Ir al Dashboard',
            showDenyButton: true,
            denyButtonText: 'Descargar PDF'
        }).then(function(result) {
            if (result.isConfirmed) {
                printReceipt(receipt.id_recibo);
            } else if (result.isDenied) {
                downloadReceiptPdf(receipt.id_recibo);
            }
            if (!result.isConfirmed && !result.isDenied) {
                window.location.href = '<?= Url::to(["dashboard/index"]) ?>';
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

    btnFoto.addEventListener('click', function() {
        const preview = document.getElementById('webcam-preview');
        if (video.style.display === 'none') {
            video.style.display = 'block';
            preview.style.display = 'none';
            btnFoto.innerText = 'Tomar Foto';
            labelFotoStatus.style.display = 'none';
            inputFotoWebcam.value = '';
        } else {
            const context = canvas.getContext('2d');
            context.drawImage(video, 0, 0, canvas.width, canvas.height);
            const dataUrl = canvas.toDataURL('image/jpeg');
            
            inputFotoWebcam.value = dataUrl;
            preview.src = dataUrl; 
            
            video.style.display = 'none';
            preview.style.display = 'block';
            
            btnFoto.innerText = 'Retomar Foto';
            labelFotoStatus.style.display = 'block';
        }
    });

    if (btnHuella) {
        btnHuella.addEventListener('click', function() {
            const ci = registrationData.ci || inputCi.value;
            const nombre = registrationData.nombre || inputNombre.value;
            if(!ci || !nombre) { alert("Complete CI y Nombre primero."); return; }

            btnHuella.disabled = true;
            btnHuella.innerText = "Registrando...";
            
            fetch('<?= Url::to(["access-control/simulate-access"]) ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: 'ci=' + encodeURIComponent(ci)
            })
            .then(function(res) { return res.json(); })
            .then(function() {
                labelHuellaStatus.innerText = "Huella OK";
                labelHuellaStatus.style.color = "green";
                btnHuella.innerText = "Huella Registrada";
                registrationData.huellaRegistrada = true;
            })
            .catch(function() {
                labelHuellaStatus.innerText = "Huella OK (Simulado)";
                labelHuellaStatus.style.color = "green";
                btnHuella.innerText = "Huella Registrada";
                registrationData.huellaRegistrada = true;
            })
            .finally(function() {
                btnHuella.disabled = false;
            });
        });
    }
});
</script>
