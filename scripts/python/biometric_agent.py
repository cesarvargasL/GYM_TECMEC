"""
Biometric API - Production Agent for Tecmec University Gym
Connects to ZKTeco uFace800 Plus device and communicates with Yii2 backend.

Requirements:
    pip install flask pyzk requests

Usage:
    python biometric_agent.py

Configuration:
    Edit the constants below to match your setup.
"""

import threading
import time
import requests
from flask import Flask, request, jsonify
from zk import ZK, const
from datetime import datetime

# ============================================
# CONFIGURATION - EDIT THESE VALUES
# ============================================
ZK_IP = '192.168.1.200'       # Static IP of ZKTeco uFace800 Plus
ZK_PORT = 4370                 # Default ZKTeco port
ZK_PASSWORD = 0                # Device password (0 = none)

# URL of your Yii2 application (public server)
YII2_BASE_URL = 'http://your-server-domain-or-ip'
# Webhook endpoints in Yii2
YII2_ENROLL_CALLBACK_URL = f'{YII2_BASE_URL}/api/enroll-callback'
YII2_VERIFY_ACCESS_URL = f'{YII2_BASE_URL}/api/verify-access'
YII2_GET_USERS_URL = f'{YII2_BASE_URL}/api/get-users'

# Local Flask API port
FLASK_PORT = 5000
# ============================================

app = Flask(__name__)

# Global state for enrollment
enrollment_in_progress = None
enrollment_connection = None


def send_attendance_to_yii2(user_id, timestamp):
    """Send attendance event to Yii2 backend."""
    try:
        payload = {
            "usuario_id": str(user_id),
            "id_biometrico": int(user_id),
            "timestamp": str(timestamp)
        }
        response = requests.post(YII2_VERIFY_ACCESS_URL, json=payload, timeout=5)
        if response.status_code == 200:
            result = response.json()
            status_icon = "✓" if result.get("status") == "granted" else "✗"
            print(f"  [{status_icon}] {result.get('message', 'OK')}")
        else:
            print(f"  [!] Yii2 returned {response.status_code}")
    except Exception as e:
        print(f"  [!] Error sending to Yii2: {e}")


def start_live_capture():
    """Continuously listen for fingerprint events from ZKTeco."""
    print(f"[*] Connecting to ZKTeco uFace800 Plus at {ZK_IP}:{ZK_PORT}...")

    while True:
        try:
            zk = ZK(ZK_IP, port=ZK_PORT, timeout=30, password=ZK_PASSWORD, force_udp=False, ommit_ping=False)
            conn = zk.connect()
            print("[+] Connected! Listening for fingerprint events...")

            for attendance in conn.live_capture():
                if attendance is None:
                    continue

                timestamp_local = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
                print("\n" + "=" * 50)
                print("  FINGERPRINT DETECTED!")
                print(f"  User ID     : {attendance.user_id}")
                print(f"  Timestamp   : {attendance.timestamp}")
                print(f"  Local Time  : {timestamp_local}")
                print("=" * 50)

                send_attendance_to_yii2(attendance.user_id, attendance.timestamp)

        except Exception as e:
            print(f"[-] Connection error: {e}")
            print("[*] Retrying in 5 seconds...")
            time.sleep(5)


def run_enrollment_in_background(uid_interno, user_id, nombre):
    """Run fingerprint enrollment in a background thread."""
    global enrollment_in_progress, enrollment_connection

    enrollment_in_progress = {
        'uid': uid_interno,
        'user_id': user_id,
        'nombre': nombre,
        'estado': 'esperando_huella'
    }

    zk = ZK(ZK_IP, port=ZK_PORT, timeout=60, password=ZK_PASSWORD, force_udp=False, ommit_ping=False)

    try:
        print(f"\n[*] Starting enrollment for '{nombre}' (ID: {user_id})...")
        enrollment_connection = zk.connect()

        print(f"[*] ========================================")
        print(f"[*]  PLACE YOUR FINGER ON THE SENSOR")
        print(f"[*]  User: {nombre}")
        print(f"[*] ========================================\n")

        try:
            enrollment_connection.enroll_user(uid=int(uid_interno), temp_id=0, user_id=str(user_id))
            print(f"[+] Fingerprint enrolled successfully (attempt 1)")
            enrollment_in_progress['estado'] = 'completado'
        except Exception as e1:
            print(f"[-] Attempt 1 failed: {e1}")
            try:
                enrollment_connection.enroll_user(uid=int(uid_interno), temp_id=1, user_id=str(user_id))
                print(f"[+] Fingerprint enrolled successfully (attempt 2)")
                enrollment_in_progress['estado'] = 'completado'
            except Exception as e2:
                print(f"[-] Attempt 2 failed: {e2}")
                enrollment_in_progress['estado'] = 'error'
                enrollment_in_progress['error'] = str(e2)

        # Notify Yii2 if enrollment completed
        if enrollment_in_progress['estado'] == 'completado':
            try:
                requests.post(YII2_ENROLL_CALLBACK_URL, json={
                    'ci': str(user_id),
                    'status': 'success'
                }, timeout=5)
                print(f"[+] Yii2 notified of successful enrollment")
            except Exception as e:
                print(f"[!] Could not notify Yii2: {e}")

    except Exception as e:
        print(f"[-] Enrollment error: {e}")
        if enrollment_in_progress:
            enrollment_in_progress['estado'] = 'error'
            enrollment_in_progress['error'] = str(e)
    finally:
        if enrollment_connection:
            try:
                enrollment_connection.disconnect()
            except:
                pass
            enrollment_connection = None


@app.route('/api/registrar', methods=['POST'])
def register_user():
    """Start fingerprint enrollment for a user."""
    global enrollment_in_progress

    data = request.json
    uid_interno = data.get('uid')
    user_id = data.get('user_id')
    nombre = data.get('nombre')

    if not uid_interno or not nombre:
        return jsonify({"status": "error", "message": "Missing uid or nombre"}), 400

    print(f"\n[*] Registration request for '{nombre}' (ID: {user_id})")

    zk = ZK(ZK_IP, port=ZK_PORT, timeout=10, password=ZK_PASSWORD, force_udp=False, ommit_ping=False)
    conn = None
    try:
        conn = zk.connect()
        print(f"[+] Connected to biometric device")

        try:
            device_name = conn.get_device_name()
            print(f"[*] Device: {device_name}")
        except:
            print(f"[*] Could not get device name")

        print(f"[*] Creating/updating user on device...")
        conn.set_user(
            uid=int(uid_interno),
            name=nombre,
            privilege=const.USER_DEFAULT,
            password='',
            user_id=str(user_id)
        )
        print(f"[+] User created on device")

        # Start enrollment in background thread
        thread = threading.Thread(
            target=run_enrollment_in_background,
            args=(uid_interno, user_id, nombre),
            daemon=True
        )
        thread.start()

        return jsonify({
            "status": "success",
            "message": f"Place your finger on the sensor (60 seconds timeout)..."
        }), 200

    except Exception as e:
        print(f"[-] Error: {e}")
        return jsonify({"status": "error", "message": str(e)}), 500
    finally:
        if conn:
            try:
                conn.disconnect()
            except:
                pass


@app.route('/api/estado-enrolamiento', methods=['GET'])
def enrollment_status():
    """Check enrollment status."""
    global enrollment_in_progress

    if enrollment_in_progress is None:
        return jsonify({"status": "none", "message": "No enrollment in progress"}), 200

    return jsonify({
        "status": enrollment_in_progress.get('estado'),
        "usuario": enrollment_in_progress.get('nombre'),
        "mensaje": enrollment_in_progress.get('error', 'In progress...')
    }), 200


@app.route('/api/status', methods=['GET'])
def agent_status():
    """Check agent status."""
    return jsonify({
        "status": "online",
        "agent": "Biometric Agent v2.0",
        "port": FLASK_PORT,
        "yii2_server": YII2_BASE_URL,
        "biometric_device": f"{ZK_IP}:{ZK_PORT}",
        "device_model": "ZKTeco uFace800 Plus"
    })


@app.route('/api/sincronizar', methods=['POST'])
def sync_users():
    """Sync users from Yii2 to biometric device."""
    try:
        response = requests.get(YII2_GET_USERS_URL, timeout=5)
        if response.status_code != 200:
            return jsonify({"status": "error", "message": "Could not fetch users from Yii2"}), 500

        users = response.json().get('data', [])
        if not users:
            return jsonify({"status": "warning", "message": "No users to sync"}), 200

        zk = ZK(ZK_IP, port=ZK_PORT, timeout=30, password=ZK_PASSWORD, force_udp=False, ommit_ping=False)
        conn = zk.connect()

        synced = 0
        errors = []

        for user in users:
            try:
                conn.set_user(
                    uid=int(user.get('id', 0)),
                    name=user.get('nombre', 'Unknown'),
                    privilege=const.USER_DEFAULT,
                    password='',
                    user_id=str(user.get('user_id', ''))
                )
                synced += 1
            except Exception as e:
                errors.append(f"Error with user {user.get('nombre')}: {str(e)}")

        conn.disconnect()

        return jsonify({
            "status": "success",
            "message": f"Synced {synced}/{len(users)} users",
            "synced": synced,
            "errors": errors
        })

    except Exception as e:
        return jsonify({"status": "error", "message": str(e)}), 500


if __name__ == '__main__':
    print("\n" + "=" * 60)
    print("       BIOMETRIC AGENT - Tecmec University Gym")
    print("=" * 60)
    print(f"[*] Biometric Device : {ZK_IP}:{ZK_PORT}")
    print(f"[*] Device Model     : ZKTeco uFace800 Plus")
    print(f"[*] Yii2 Server      : {YII2_BASE_URL}")
    print(f"[*] Local API        : http://0.0.0.0:{FLASK_PORT}")
    print("=" * 60 + "\n")

    # Start live capture in background thread
    capture_thread = threading.Thread(target=start_live_capture, daemon=True)
    capture_thread.start()

    print("[*] Available endpoints:")
    print("    POST   /api/registrar              - Start enrollment")
    print("    GET    /api/estado-enrolamiento    - Check enrollment status")
    print("    POST   /api/sincronizar            - Sync users from Yii2")
    print("    GET    /api/status                 - Agent status")
    print("\n")

    app.run(host='0.0.0.0', port=FLASK_PORT, debug=False)
