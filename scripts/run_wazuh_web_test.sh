
#!/usr/bin/env bash
set -euo pipefail

PROJECT_PATH="/home/clemoryn/Documents/GitHub/caps-test"
APP_URL="http://127.0.0.1:8000"
DEV_LOG="$PROJECT_PATH/storage/logs/dev-stack.log"
PID_FILE="$PROJECT_PATH/storage/run/dev-stack.pid"

cd "$PROJECT_PATH"
mkdir -p "$PROJECT_PATH/storage/logs" "$PROJECT_PATH/storage/run"

if ! systemctl is-active --quiet wazuh-manager; then
  echo "Wazuh manager is not running. Start it first with: sudo systemctl start wazuh-manager"
  exit 1
fi

if [ ! -f "$PID_FILE" ] || ! kill -0 "$(cat "$PID_FILE")" 2>/dev/null; then
  nohup composer run dev > "$DEV_LOG" 2>&1 &
  echo $! > "$PID_FILE"
  echo "Started Laravel stack in the background. Logs: $DEV_LOG"
else
  echo "Laravel stack is already running with PID $(cat "$PID_FILE")"
fi

echo "Waiting for the app to respond at $APP_URL ..."
for attempt in $(seq 1 30); do
  if curl -fsS "$APP_URL" >/dev/null 2>&1; then
    break
  fi

  if [ "$attempt" -eq 30 ]; then
    echo "App did not become ready in time. Check $DEV_LOG"
    exit 1
  fi

  sleep 1
done

echo "Running Wazuh alert simulation ..."
./scripts/wazuh_test_simulation.sh

echo
echo "Open this page in the browser:"
echo "$APP_URL/ai-security"
echo
echo "If you want to stop the background stack later:"
echo "kill $(cat \"$PID_FILE\")"