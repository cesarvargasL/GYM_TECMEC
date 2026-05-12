<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\LinkPager;
use app\shared\enums\Roles;

/* @var $users app\models\User[] */
/* @var $pages yii\data\Pagination */
/* @var $search string */
/* @var $role string */
/* @var $huella string */
?>

<div style="padding: 20px;">
    
    <div style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
        <h2 style="margin-bottom: 20px; color: #333; border-bottom: 2px solid #eee; padding-bottom: 10px;">Gestión de Usuarios</h2>

        <form id="filter-form" method="GET" action="<?= Url::to(['user-management/index']) ?>" style="display: flex; gap: 15px; margin-bottom: 20px; flex-wrap: wrap;">
            
            <input type="text" id="search-input" name="search" value="<?= Html::encode($search) ?>" placeholder="Buscar por Nombre, CI o Correo..." style="padding: 10px; border-radius: 5px; border: 1px solid #ccc; flex-grow: 1; min-width: 250px;">
            
            <select name="role" onchange="this.form.submit()" style="padding: 10px; border-radius: 5px; border: 1px solid #ccc; cursor: pointer;">
                <option value="">Todos los Roles</option>
                <option value="<?= Roles::ADMINISTRATOR->value ?>" <?= $role === Roles::ADMINISTRATOR->value ? 'selected' : '' ?>>Administradores</option>
                <option value="<?= Roles::CLIENT->value ?>" <?= $role === Roles::CLIENT->value ? 'selected' : '' ?>>Clientes</option>
                <option value="<?= Roles::SUPER_ADMIN->value ?>" <?= $role === Roles::SUPER_ADMIN->value ? 'selected' : '' ?>>Super Admin</option>
            </select>

            <select name="huella" onchange="this.form.submit()" style="padding: 10px; border-radius: 5px; border: 1px solid #ccc; cursor: pointer;">
                <option value="">Estado de Huella (Todos)</option>
                <option value="1" <?= $huella === '1' ? 'selected' : '' ?>>Registrada</option>
                <option value="0" <?= $huella === '0' ? 'selected' : '' ?>>No Registrada</option>
            </select>

            <a href="<?= Url::to(['user-management/index']) ?>" class="btn btn-light" style="font-weight: bold; border-radius: 5px; padding: 10px 20px; border: 1px solid #ccc; text-decoration: none; color: #333;" title="Limpiar todos los filtros">Limpiar</a>
        </form>

        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 15px;">
                <thead>
                    <tr style="background-color: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                        <th style="padding: 15px; color: #495057;">CI</th>
                        <th style="padding: 15px; color: #495057;">Usuario</th>
                        <th style="padding: 15px; color: #495057;">Rol</th>
                        <th style="padding: 15px; color: #495057; text-align: center;">Huella</th>
                        <th style="padding: 15px; color: #495057; text-align: center;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 50px; color: #6c757d; font-size: 18px;">No se encontraron usuarios con esos filtros.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $u): ?>
                            <tr style="border-bottom: 1px solid #e9ecef; transition: background-color 0.2s;">
                                <td style="padding: 15px; color: #666; font-weight: bold; vertical-align: middle;"> <?= Html::encode($u->CI) ?> </td>
                                
                                <td style="padding: 15px; vertical-align: middle;">
                                    <div style="display: flex; align-items: center; gap: 15px;">
                                        <?php if ($u->AVATAR): ?>
                                            <img src="<?= Html::encode($u->AVATAR) ?>" alt="Avatar" style="width: 45px; height: 45px; border-radius: 50%; object-fit: cover; border: 2px solid #0056b3;">
                                        <?php else: ?>
                                            <div style="width: 45px; height: 45px; background: #0056b3; border-radius: 50%; color: white; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: bold;">
                                                <?= strtoupper(substr($u->NOMBRE_COMPLETO, 0, 1)) ?>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div style="line-height: 1.2;">
                                            <strong style="color: #0056b3; font-size: 16px;"><?= Html::encode($u->NOMBRE_COMPLETO) ?></strong><br>
                                            <small style="color: #888;"><?= Html::encode($u->CORREO_ELECTRONICO) ?></small>
                                        </div>
                                    </div>
                                </td>

                                <td style="padding: 15px; color: #666; vertical-align: middle;">
                                    <span style="background: #e9ecef; padding: 5px 10px; border-radius: 15px; font-size: 12px; font-weight: bold;">
                                        <?= Html::encode($u->ROL) ?>
                                    </span>
                                </td>
                                
                                <td style="padding: 15px; text-align: center; vertical-align: middle;">
                                    <?php if ($u->HUELLA): ?>
                                        <span style="background-color: #28a745; color: white; padding: 5px 12px; border-radius: 15px; font-weight: bold; font-size: 13px; display: inline-block;">
                                            ✓ Registrada
                                        </span>
                                    <?php else: ?>
                                        <button class="btn btn-warning btn-sm" style="border-radius: 15px; font-weight: bold; color: #333; font-size: 13px;" onclick="enrolarHuella('<?= $u->CI ?>', '<?= $u->ID_BIOMETRICO ?>', '<?= Html::encode($u->NOMBRE_COMPLETO) ?>')">
                                            + Enrolar Huella
                                        </button>
                                    <?php endif; ?>
                                </td>

                                <td style="padding: 15px; text-align: center; vertical-align: middle;">
                                    
                                    <button type="button" class="btn btn-info btn-sm" style="border-radius: 20px; font-weight: bold; margin-right: 5px; color: white;" 
                                        onclick="verUsuario({
                                            nombre: '<?= Html::encode($u->NOMBRE_COMPLETO) ?>',
                                            ci: '<?= Html::encode($u->CI) ?>',
                                            correo: '<?= Html::encode($u->CORREO_ELECTRONICO) ?>',
                                            telefono: '<?= Html::encode($u->TELEFONO ?? 'No registrado') ?>',
                                            rol: '<?= Html::encode($u->ROL) ?>',
                                            tipoCliente: '<?= Html::encode($u->TIPO_CLIENTE) ?>',
                                            estado: '<?= Html::encode($u->ESTADO) ?>',
                                            avatar: '<?= Html::encode($u->AVATAR ?? '') ?>'
                                        })">
                                        👁️ Ver
                                    </button>

                                    <?= Html::a('✏️ Editar', ['user-management/update', 'id' => $u->CI], [
                                        'class' => 'btn btn-primary btn-sm',
                                        'style' => 'border-radius: 20px; font-weight: bold; margin-right: 5px; color: red;'
                                    ]) ?>

                                    <?= Html::a('Eliminar', ['user-management/soft-delete', 'id' => $u->CI], [
                                        'class' => 'btn btn-outline-danger btn-sm',
                                        'style' => 'border-radius: 20px; font-weight: bold;',
                                        'data' => [
                                            'confirm' => '¿Estás seguro de que deseas eliminar a "' . Html::encode($u->NOMBRE_COMPLETO) . '" del sistema?',
                                            'method' => 'post',
                                        ]
                                    ]) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div style="margin-top: 20px; display: flex; justify-content: center;">
            <?= LinkPager::widget([
                'pagination' => $pages,
                'options' => ['class' => 'pagination mb-0'],
                'linkOptions' => ['class' => 'page-link'],
                'pageCssClass' => 'page-item',
                'activePageCssClass' => 'active',
                'disabledPageCssClass' => 'disabled',
            ]) ?>
        </div>
    </div>
</div>

<script>
    let typingTimer;
    const searchInput = document.getElementById('search-input');
    const filterForm = document.getElementById('filter-form');

    if(searchInput) {
        searchInput.addEventListener('keyup', function () {
            clearTimeout(typingTimer);
            typingTimer = setTimeout(() => {
                filterForm.submit();
            }, 600);
        });
        searchInput.addEventListener('keydown', function () {
            clearTimeout(typingTimer);
        });
    }

    function verUsuario(user) {
        let avatarHtml = user.avatar 
            ? `<img src="${user.avatar}" style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 4px solid #0056b3; margin-bottom: 20px; box-shadow: 0 4px 8px rgba(0,0,0,0.2);">` 
            : `<div style="width: 120px; height: 120px; background: #0056b3; border-radius: 50%; color: white; display: flex; align-items: center; justify-content: center; font-size: 50px; font-weight: bold; margin: 0 auto 20px auto; box-shadow: 0 4px 8px rgba(0,0,0,0.2);">${user.nombre.charAt(0).toUpperCase()}</div>`;

        Swal.fire({
            html: `
                <div style="padding: 10px;">
                    ${avatarHtml}
                    <h3 style="color: #333; margin-top: 0; font-weight: bold;">${user.nombre}</h3>
                    <p style="color: #666; font-size: 15px; margin-bottom: 20px;">${user.correo}</p>
                    
                    <hr style="border-top: 1px solid #eee; margin-bottom: 20px;">
                    
                    <div style="text-align: left; font-size: 15px; line-height: 2; background: #f8f9fa; padding: 20px; border-radius: 10px; border: 1px solid #e9ecef;">
                        <div><strong style="color: #495057;">Carnet (CI):</strong> <span style="float: right; color: #0056b3; font-weight: bold;">${user.ci}</span></div>
                        <div><strong style="color: #495057;">Teléfono:</strong> <span style="float: right;">${user.telefono}</span></div>
                        <div><strong style="color: #495057;">Rol del Sistema:</strong> <span style="float: right;">${user.rol}</span></div>
                        <div><strong style="color: #495057;">Tipo de Cliente:</strong> <span style="float: right;">${user.tipoCliente}</span></div>
                        <div><strong style="color: #495057;">Estado Actual:</strong> <span style="float: right; color: ${user.estado === 'ACTIVO' ? '#28a745' : '#dc3545'}; font-weight: bold;">${user.estado}</span></div>
                    </div>
                </div>
            `,
            showCloseButton: true,
            showConfirmButton: false,
            width: '450px'
        });
    }

    function enrolarHuella(ci, idBiometrico, nombre) {
        Swal.fire({
            title: '¿Enrolar Huella?',
            text: "Se activara el dispositivo ZKTeco para: " + nombre,
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: '#0056b3',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Si, activar lector'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Enrolando...',
                    text: 'Por favor ponga el dedo en el dispositivo ZKTeco',
                    icon: 'info',
                    showConfirmButton: false,
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();

                        fetch('http://localhost:5000/api/registrar', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                uid: parseInt(ci),
                                user_id: ci,
                                nombre: nombre
                            })
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.status === 'success') {
                                pollEnrollmentStatus(ci, nombre);
                            } else {
                                Swal.fire('Error', data.message || 'Error al iniciar enrolamiento', 'error');
                            }
                        })
                        .catch(err => {
                            Swal.fire('Error', 'No se pudo conectar al agente local. Verifique que Python esta corriendo.', 'error');
                        });
                    }
                });
            }
        });
    }

    function pollEnrollmentStatus(ci, nombre) {
        let attempts = 0;
        const maxAttempts = 60;

        const interval = setInterval(() => {
            attempts++;

            fetch('http://localhost:5000/api/estado-enrolamiento')
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'completado') {
                        clearInterval(interval);
                        Swal.fire({
                            icon: 'success',
                            title: 'Huella Registrada',
                            text: 'La huella de ' + nombre + ' fue enrolada exitosamente.',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            location.reload();
                        });
                    } else if (data.status === 'error') {
                        clearInterval(interval);
                        Swal.fire('Error', data.mensaje || 'Error en el enrolamiento', 'error');
                    } else if (attempts >= maxAttempts) {
                        clearInterval(interval);
                        Swal.fire('Timeout', 'Tiempo de espera agotado. Intente nuevamente.', 'warning');
                    }
                })
                .catch(() => {
                    if (attempts >= maxAttempts) {
                        clearInterval(interval);
                        Swal.fire('Error', 'Error de conexion con el agente', 'error');
                    }
                });
        }, 2000);
    }
    }
</script>