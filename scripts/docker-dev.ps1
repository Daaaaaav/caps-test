param(
    [ValidateSet('init','start','stop','logs','test','help')]
    [string]$Action = 'help',
    [switch]$Wazuh,
    [switch]$Volumes
)

$ErrorActionPreference = 'Stop'

function Ensure-EnvFile {
    if (-not (Test-Path '.env')) {
        Copy-Item '.env.example' '.env'
        Write-Host 'Created .env from .env.example'
    }
}

function Set-EnvValue {
    param(
        [string]$Key,
        [string]$Value
    )

    $envPath = '.env'
    $content = Get-Content $envPath -Raw
    $pattern = "(?m)^$([regex]::Escape($Key))=.*$"
    $replacement = "$Key=$Value"

    if ($content -match $pattern) {
        $content = [regex]::Replace($content, $pattern, $replacement)
    } else {
        if ($content.Length -gt 0 -and -not $content.EndsWith("`n")) {
            $content += "`n"
        }
        $content += "$replacement`n"
    }

    Set-Content -Path $envPath -Value $content -NoNewline
}

function Set-DockerEnv {
    Set-EnvValue -Key 'DB_CONNECTION' -Value 'mysql'
    Set-EnvValue -Key 'DB_HOST' -Value 'mysql'
    Set-EnvValue -Key 'DB_PORT' -Value '3306'
    Set-EnvValue -Key 'WAZUH_ALERT_LOG_PATH' -Value '/var/ossec/logs/alerts/alerts.log'
}

function Show-Usage {
    @"
Usage:
  .\scripts\docker-dev.ps1 -Action init
  .\scripts\docker-dev.ps1 -Action start [-Wazuh]
  .\scripts\docker-dev.ps1 -Action stop [-Volumes]
  .\scripts\docker-dev.ps1 -Action logs [-Wazuh]
  .\scripts\docker-dev.ps1 -Action test

Commands:
  init   Prepare .env, install dependencies, generate app key, migrate DB.
  start  Start app services; add -Wazuh to include wazuh-manager.
  stop   Stop services; add -Volumes to remove DB volume.
  logs   Follow app/queue/mysql logs; add -Wazuh for Wazuh logs too.
  test   Run Laravel test suite in Docker.
"@
}

switch ($Action) {
    'init' {
        Ensure-EnvFile
        Set-DockerEnv
        docker compose run --rm composer install
        docker compose run --rm npm install
        docker compose run --rm artisan key:generate
        docker compose up -d mysql
        docker compose run --rm artisan migrate
        Write-Host 'Initialization complete. Run .\scripts\docker-dev.ps1 -Action start'
    }
    'start' {
        Ensure-EnvFile
        if ($Wazuh) {
            docker compose --profile wazuh up -d app queue vite mysql wazuh-manager
        } else {
            docker compose up -d app queue vite mysql
        }

        docker compose exec -T app php artisan migrate --force --seed --seeder=DatabaseSeeder

        $runningApp = docker compose ps --status running app
        if (-not ($runningApp -match 'app')) {
            Write-Host 'App container is not running. Showing recent app logs:'
            docker compose logs --tail=80 app
            exit 1
        }

        Write-Host 'App: http://localhost:8000'
        Write-Host 'Vite: http://localhost:5173'
    }
    'stop' {
        if ($Volumes) {
            docker compose down -v
        } else {
            docker compose down
        }
    }
    'logs' {
        if ($Wazuh) {
            docker compose --profile wazuh logs -f app queue mysql wazuh-manager
        } else {
            docker compose logs -f app queue mysql
        }
    }
    'test' {
        docker compose run --rm artisan test
    }
    Default {
        Show-Usage
    }
}
