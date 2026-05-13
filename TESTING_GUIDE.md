# Testing Guide - Biometric & Payments Feature

## Prerequisites

1. **Database Setup**
   - Run the migration SQL file to create the new tables:
     ```bash
     mysql -u root -p gym_universitario < migrations/m260511_create_membership_payment_attendance.sql
     ```

2. **Python Environment** (for biometric testing)
   ```bash
   cd scripts/python
   pip install -r requirements.txt
   ```

3. **Biometric Device Connection**
   - Connect ZKTeco uFace800 Plus to your PC via Ethernet
   - Assign static IPs:
     - PC: e.g., 192.168.1.100
     - Device: e.g., 192.168.1.200
   - Verify connectivity: `ping 192.168.1.200`

## Step-by-Step Testing

### Step 1: Start the Python Biometric Agent

```bash
cd scripts/python
# Edit biometric_agent.py and update ZK_IP and YII2_BASE_URL
python biometric_agent.py
```

Expected output:
```
============================================================
       BIOMETRIC AGENT - Tecmec University Gym
============================================================
[*] Biometric Device : 192.168.1.200:4370
[*] Device Model     : ZKTeco uFace800 Plus
[*] Yii2 Server      : http://localhost:8080
[*] Local API        : http://0.0.0.0:5000
============================================================
```

### Step 2: Start the Yii2 Application

```bash
php yii serve --port=8080
```

### Step 3: Test Fingerprint Enrollment

1. Log in to Yii2 as Admin/Super Admin
2. Go to "Usuarios" or "Crear Usuario"
3. Create a user (or use existing one)
4. Click "Enrolar Huella" button
5. The Flask agent will start enrollment mode on the device
6. Place your finger on the ZKTeco sensor
7. Upon success, the Yii2 `HUELLA` column will be updated to 1

### Step 4: Test Access Control (Real-Time)

1. Go to "Control de Entrada" tab
2. The page will connect via SSE to listen for events
3. Place your enrolled finger on the ZKTeco sensor
4. A SweetAlert2 popup will appear:
   - **GREEN** if membership is active (access granted)
   - **RED** if no active membership (access denied)
5. The attendance is automatically recorded in the `ASISTENCIA` table

### Step 5: Test Manual Search (Fallback)

1. In "Control de Entrada", type a CI in the search input
2. Click "Verificar"
3. Same popup behavior as biometric scan

### Step 6: Test Payment Processing

1. Go to "Pagos" tab
2. Select a client from the dropdown
3. Select a plan from the dropdown
4. Payment summary appears
5. Click "Pagar con QR" or "Pago en Efectivo"
6. On success, redirects to "Historial"

### Step 7: Test Client Self-Renewal

1. Log in as a CLIENT user
2. Go to "Mi Panel" (Dashboard)
3. Click "Pagar" tab
4. Select a plan and click "Pagar QR"
5. Confirms and processes dummy QR payment

### Step 8: Test History

1. Go to "Historial" tab
2. Filter by date range (From/To)
3. Search by name or CI
4. Verify pagination works

### Step 9: Test Responsive Mobile

1. Open browser dev tools (F12)
2. Toggle device toolbar (Ctrl+Shift+M)
3. Select a mobile device
4. Verify hamburger menu works
5. Verify sidebar closes on link click
6. Verify tables scroll horizontally

## API Endpoints Reference

### Yii2 Endpoints

| Method | URL | Description |
|--------|-----|-------------|
| POST | `/api/enroll-callback` | Webhook from Flask when enrollment completes |
| POST | `/api/verify-access` | Webhook from Flask when fingerprint detected |
| GET | `/api/get-users` | Flask fetches users for sync |
| POST | `/payment/process-qr` | Process QR payment (admin) |
| POST | `/payment/process-cash` | Process cash payment (admin) |
| POST | `/payment/client-self-renew` | Client self-renewal via QR |
| GET | `/access-control/stream` | SSE stream for real-time events |
| POST | `/access-control/manual-search` | Manual CI verification |

### Flask Endpoints

| Method | URL | Description |
|--------|-----|-------------|
| POST | `/api/registrar` | Start fingerprint enrollment |
| GET | `/api/estado-enrolamiento` | Check enrollment status |
| POST | `/api/sincronizar` | Sync users from Yii2 |
| GET | `/api/status` | Agent health check |

## Troubleshooting

### "No se pudo contactar al Agente Local"
- Verify `python biometric_agent.py` is running
- Check `http://localhost:5000/api/status` in browser
- Verify firewall allows port 5000

### "Reloj no responde"
- Verify ZK_IP in `biometric_agent.py` matches device IP
- Test connectivity: `ping <ZK_IP>`
- Verify device is powered on and connected

### SSE not connecting
- Check browser console for errors
- Verify PHP allows long-running connections
- Check `runtime/access_events.json` is writable

### Payment fails
- Verify `PLAN` table has active plans
- Check client exists and has ROL = 'CLIENTE'
