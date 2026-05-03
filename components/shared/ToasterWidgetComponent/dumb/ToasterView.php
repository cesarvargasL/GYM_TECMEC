<?php foreach ($flashes as $type => $message): ?>
    <?php 
        $icon = $type === 'error' ? 'error' : ($type === 'success' ? 'success' : 'warning');
        $bgColor = $type === 'error' ? '#dc3545' : '#28a745'; 
    ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 4000,
                timerProgressBar: true,
                background: '<?= $bgColor ?>',
                color: '#ffffff',
                iconColor: '#ffffff'
            });
            Toast.fire({
                icon: '<?= $icon ?>',
                title: '<?= $message ?>'
            });
        });
    </script>
<?php endforeach; ?>