#!/usr/bin/env bash
set -euo pipefail

PROJECT_PATH="/home/clemoryn/Documents/GitHub/caps-test"
LOG_FILE="$PROJECT_PATH/storage/logs/laravel.log"
ALERTS_LOG="/var/ossec/logs/alerts/alerts.log"

mkdir -p "$PROJECT_PATH/storage/logs"

for i in $(seq 1 5); do
  echo "[$(date '+%Y-%m-%d %H:%M:%S')] local.WARNING: LOGIN_FAILED {\"ip\":\"10.10.10.5\",\"email\":\"attacker@example.com\"}" >> "$LOG_FILE"
done

echo "[$(date '+%Y-%m-%d %H:%M:%S')] local.INFO: LOGIN_SUCCESS {\"ip\":\"10.10.10.5\",\"email\":\"user@example.com\"}" >> "$LOG_FILE"

for i in $(seq 1 10); do
  echo "[$(date '+%Y-%m-%d %H:%M:%S')] local.INFO: FORM_SUBMIT {\"ip\":\"10.10.10.6\",\"form\":\"guestbook\",\"data\":{\"name\":\"spam$i\"}}" >> "$LOG_FILE"
done

echo "[$(date '+%Y-%m-%d %H:%M:%S')] local.WARNING: SQLI_DETECTED ' OR 1=1-- {\"ip\":\"10.10.10.7\",\"field\":\"email\"}" >> "$LOG_FILE"
echo "[$(date '+%Y-%m-%d %H:%M:%S')] local.WARNING: XSS_DETECTED <script>alert(1)</script> {\"ip\":\"10.10.10.8\",\"field\":\"name\"}" >> "$LOG_FILE"

sleep 3

sudo rg -n "Rule: 100100|Rule: 100101|Rule: 100102|Rule: 100103|Rule: 100104|10.10.10.5|10.10.10.6|10.10.10.7|10.10.10.8" "$ALERTS_LOG" | tail -n 80

echo "Simulation complete."
