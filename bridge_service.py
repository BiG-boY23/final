import asyncio
import websockets
import json
import serial
import threading
import time
import subprocess
import sys
import requests
import os
from datetime import datetime
import logging

# Silence noise from invalid handshake attempts (health checks/probes)
logging.getLogger('websockets').setLevel(logging.ERROR)

# ──────────────────────────────────────────────
# CONFIGURATION
# ──────────────────────────────────────────────
SERIAL_PORT      = os.getenv('SERIAL_PORT', '/dev/ttyUSB0' if os.name != 'nt' else 'COM3')
BAUD_RATE        = int(os.getenv('BAUD_RATE', 9600))
WS_HOST          = '0.0.0.0'
WS_PORT          = int(os.getenv('WS_PORT', 8080))
DEBOUNCE_SECONDS = 0.5

# LARAVEL INTEGRATION
LARAVEL_BASE_URL = os.getenv('LARAVEL_URL', 'http://127.0.0.1:8000')
HEARTBEAT_PATH   = '/bridge/heartbeat'
SYNC_PATH        = '/bridge/sync'
BUFFER_FILE      = 'offline_buffer.json'

# ──────────────────────────────────────────────
# SHARED STATE
# ──────────────────────────────────────────────
TAG_QUEUE         = asyncio.Queue()
CONNECTED_CLIENTS = set()

# ──────────────────────────────────────────────
# BUFFER MANAGEMENT
# ──────────────────────────────────────────────
def save_to_buffer(tag_id):
    """Saves a scan to a local JSON file if Laravel/WebSocket is offline."""
    scan = {
        "tagId": tag_id,
        "timestamp": datetime.now().isoformat()
    }
    
    data = []
    if os.path.exists(BUFFER_FILE):
        try:
            with open(BUFFER_FILE, 'r') as f:
                data = json.load(f)
        except: data = []
        
    data.append(scan)
    
    with open(BUFFER_FILE, 'w') as f:
        json.dump(data, f)
    print(f"[BUFFER] Saved offline scan: {tag_id}")

def sync_buffer():
    """Attempts to push buffered scans to Laravel."""
    if not os.path.exists(BUFFER_FILE):
        return

    try:
        with open(BUFFER_FILE, 'r') as f:
            data = json.load(f)
        
        if not data:
            return

        print(f"[SYNC] Attempting to sync {len(data)} buffered scans...")
        response = requests.post(f"{LARAVEL_BASE_URL}{SYNC_PATH}", json={"scans": data}, timeout=5)
        
        if response.status_code == 200:
            print(f"[SYNC] Successfully synced {len(data)} scans. Clearing buffer.")
            os.remove(BUFFER_FILE)
        else:
            print(f"[SYNC] Failed to sync. Server returned {response.status_code}")
    except Exception as e:
        print(f"[SYNC] Error during sync: {e}")

# ──────────────────────────────────────────────
# HEARTBEAT
# ──────────────────────────────────────────────
def heartbeat_worker():
    """Background thread to send diagnostics to Laravel."""
    while True:
        try:
            # Send immediate diagnostic data on every loop check
            requests.post(f"{LARAVEL_BASE_URL}{HEARTBEAT_PATH}", json={"port": SERIAL_PORT}, timeout=5)
            sync_buffer()
        except:
            pass # Laravel might be down
        time.sleep(10) # 10s heartbeat for smoother live status

# ──────────────────────────────────────────────
# PORT CLEANUP
# ──────────────────────────────────────────────
# ──────────────────────────────────────────────
# PORT CLEANUP
# ──────────────────────────────────────────────
def kill_existing_port(port):
    """Attempts to kill any process currently using the specified port."""
    try:
        if os.name == 'nt':
            # Windows
            result = subprocess.run(f'netstat -ano | findstr :{port}', shell=True, capture_output=True, text=True)
            for line in result.stdout.strip().splitlines():
                if f':{port}' in line and 'LISTENING' in line:
                    pid = line.strip().split()[-1]
                    subprocess.run(f'taskkill /PID {pid} /F', shell=True, capture_output=True)
                    print(f"[PORT] Killed Windows PID {pid} on port {port}")
        else:
            # Linux (BusyBox/Alpine compatible)
            result = subprocess.run(f'fuser -k {port}/tcp', shell=True, capture_output=True)
            print(f"[PORT] Attempted Linux port cleanup on {port}")
        time.sleep(0.2)
    except: pass

# ──────────────────────────────────────────────
# SERIAL READER
# ──────────────────────────────────────────────
def serial_reader_thread(port, baud, loop):
    try:
        ser = serial.Serial(port, baud, timeout=0.1)
        print(f"[SERIAL] Connected: {port}")
    except Exception as e:
        print(f"[SERIAL] ERROR: {e}")
        return

    buffer = ""
    last_tags = {}

    while True:
        try:
            if ser.in_waiting > 0:
                raw = ser.read(ser.in_waiting).decode('ascii', errors='ignore')
                buffer += raw
                if '\n' in buffer or '\r' in buffer or '\x03' in buffer:
                    parts = buffer.replace('\x02', '\n').replace('\x03', '\n').replace('\r', '\n').split('\n')
                    buffer = parts.pop()
                    for raw_id in parts:
                        id_str = "".join(c for c in raw_id if c.isdigit())
                        if len(id_str) < 7: continue
                        now = time.time()
                        if (now - last_tags.get(id_str, 0)) >= DEBOUNCE_SECONDS:
                            print(f"[TAG] Detected: {id_str}")
                            # Try to send to WebSocket, if no clients, save to buffer?
                            # User said: "if it fails to send ... save to local_buffer.json"
                            loop.call_soon_threadsafe(TAG_QUEUE.put_nowait, id_str)
                            last_tags[id_str] = now
            time.sleep(0.01)
        except Exception as e:
            print(f"[SERIAL] Loop error: {e}")
            break

# ──────────────────────────────────────────────
# BROADCASTER
# ──────────────────────────────────────────────
async def broadcaster():
    while True:
        tag_id = await TAG_QUEUE.get()
        
        payload = json.dumps({
            "tagId": tag_id,
            "status": "scanned",
            "timestamp": datetime.now().strftime("%H:%M:%S")
        })

        if not CONNECTED_CLIENTS:
            # SAVE TO BUFFER if no one is listening (network loss or browser closed)
            save_to_buffer(tag_id)
            TAG_QUEUE.task_done()
            continue

        dead = set()
        for client in CONNECTED_CLIENTS.copy():
            try:
                await client.send(payload)
            except:
                dead.add(client)
        
        CONNECTED_CLIENTS.difference_update(dead)
        TAG_QUEUE.task_done()

async def websocket_handler(websocket, path=None):
    CONNECTED_CLIENTS.add(websocket)
    addr = websocket.remote_address[0] if websocket.remote_address else '?'
    print(f"[WS] +Client {addr}")

    try:
        async for message in websocket:
            try:
                data = json.loads(message)
                if data.get('cmd') == 'restart':
                    print("[WS] RESTART COMMAND RECEIVED. Rebooting bridge...")
                    # Small delay to allow the response (if any) to be sent, though we just restart
                    os.execv(sys.executable, ['python'] + sys.argv)
            except json.JSONDecodeError:
                pass 
    except websockets.exceptions.ConnectionClosed:
        pass
    finally:
        CONNECTED_CLIENTS.discard(websocket)
        print(f"[WS] -Client {addr}")

async def main():
    loop = asyncio.get_running_loop()
    threading.Thread(target=serial_reader_thread, args=(SERIAL_PORT, BAUD_RATE, loop), daemon=True).start()
    threading.Thread(target=heartbeat_worker, daemon=True).start()
    asyncio.create_task(broadcaster())
    async with websockets.serve(websocket_handler, WS_HOST, WS_PORT):
        print(f"[READY] ws://{WS_HOST}:{WS_PORT}")
        await asyncio.Future()

if __name__ == "__main__":
    kill_existing_port(WS_PORT)
    try:
        asyncio.run(main())
    except KeyboardInterrupt:
        print("\n[STOP]")
