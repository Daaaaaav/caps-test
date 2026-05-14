#!/usr/bin/env bash
set -euo pipefail

PROJECT_PATH="/home/clemoryn/Documents/GitHub/caps-test"
LARAVEL_LOG="$PROJECT_PATH/storage/logs/laravel.log"
OSSEC_CONF="/var/ossec/etc/ossec.conf"
RULES_FILE="/var/ossec/etc/rules/local_rules.xml"

sudo dnf update -y
sudo rpm --import https://packages.wazuh.com/key/GPG-KEY-WAZUH

sudo tee /etc/yum.repos.d/wazuh.repo >/dev/null <<'EOF'
[wazuh]
gpgcheck=1
gpgkey=https://packages.wazuh.com/key/GPG-KEY-WAZUH
enabled=1
name=Wazuh repository
baseurl=https://packages.wazuh.com/4.x/yum/
protect=1
EOF

sudo dnf install -y wazuh-manager --best --allowerasing --setopt=install_weak_deps=False
sudo systemctl enable --now wazuh-manager

sudo cp "$OSSEC_CONF" "$OSSEC_CONF.bak.$(date +%Y%m%d%H%M%S)"
sudo cp "$RULES_FILE" "$RULES_FILE.bak.$(date +%Y%m%d%H%M%S)"

if ! sudo grep -q "$LARAVEL_LOG" "$OSSEC_CONF"; then
  sudo perl -0777 -i -pe "s#</ossec_config>#\n  <localfile>\n    <log_format>syslog</log_format>\n    <location>$LARAVEL_LOG</location>\n  </localfile>\n</ossec_config>#s" "$OSSEC_CONF"
fi

if ! sudo grep -q "<directories>$PROJECT_PATH</directories>" "$OSSEC_CONF"; then
  sudo sed -i "/<directories>\\/bin,\\/sbin,\\/boot<\\/directories>/a\\    <directories>$PROJECT_PATH</directories>" "$OSSEC_CONF"
fi

sudo cp "$PROJECT_PATH/local_rules.wazuh.xml" "$RULES_FILE"
sudo chown wazuh:wazuh "$RULES_FILE"
sudo chmod 660 "$RULES_FILE"

sudo systemctl restart wazuh-manager
systemctl is-active wazuh-manager

echo "Wazuh manager setup complete."
