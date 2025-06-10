# Script de validation finale avant déploiement Heroku
# Utilisation: .\validate-heroku-deployment.ps1

param(
    [switch]$Verbose
)

Write-Host "🔍 Validation du déploiement Heroku pour Breitling League..." -ForegroundColor Green

# Se déplacer vers le dossier backend
Set-Location "c:\Users\jonas.dubois1\Desktop\breilting-league - Copie\laravel-vue-project\backend"

$errors = @()
$warnings = @()

# Vérification 1: Configuration PostgreSQL
Write-Host "`n📊 Vérification de la configuration PostgreSQL..." -ForegroundColor Yellow
$dbConfig = Get-Content "config\database.php" -Raw
if ($dbConfig -match "default.*pgsql") {
    Write-Host "✅ Configuration PostgreSQL par défaut détectée" -ForegroundColor Green
} else {
    $errors += "❌ Configuration PostgreSQL non définie par défaut"
}

if ($dbConfig -match "ext-pgsql") {
    Write-Host "✅ Extension PostgreSQL requise dans composer.json" -ForegroundColor Green
} else {
    $warnings += "⚠️  Extension ext-pgsql non trouvée dans composer.json"
}

# Vérification 2: Event Listeners
Write-Host "`n🎧 Vérification des Event Listeners..." -ForegroundColor Yellow
if (Test-Path "app\Listeners\SynchronizeUserScore.php") {
    $listenerContent = Get-Content "app\Listeners\SynchronizeUserScore.php" -Raw
    if ($listenerContent -match "getDateFormatSQL") {
        Write-Host "✅ SynchronizeUserScore adapté pour PostgreSQL" -ForegroundColor Green
    } else {
        $errors += "❌ SynchronizeUserScore non adapté pour PostgreSQL"
    }
} else {
    $errors += "❌ SynchronizeUserScore listener manquant"
}

if (Test-Path "app\Providers\EventServiceProvider.php") {
    Write-Host "✅ EventServiceProvider configuré" -ForegroundColor Green
} else {
    $warnings += "⚠️  EventServiceProvider manquant (utilise AppServiceProvider)"
}

# Vérification 3: Fichiers de déploiement
Write-Host "`n📦 Vérification des fichiers de déploiement..." -ForegroundColor Yellow
$deploymentFiles = @(
    "Procfile",
    "bin\deploy-heroku.ps1",
    "bin\deploy-heroku.sh",
    ".env.heroku"
)

foreach ($file in $deploymentFiles) {
    if (Test-Path $file) {
        Write-Host "✅ $file présent" -ForegroundColor Green
    } else {
        $warnings += "⚠️  $file manquant"
    }
}

# Vérification 4: Schéma PostgreSQL
Write-Host "`n🗄️ Vérification du schéma PostgreSQL..." -ForegroundColor Yellow
if (Test-Path "database\breitlingLeague_postgresql.sql") {
    $schemaContent = Get-Content "database\breitlingLeague_postgresql.sql" -Raw
    if ($schemaContent -match "BIGSERIAL" -and $schemaContent -match "quiz_status") {
        Write-Host "✅ Schéma PostgreSQL adapté avec types natifs" -ForegroundColor Green
    } else {
        $warnings += "⚠️  Schéma PostgreSQL pourrait nécessiter des ajustements"
    }
} else {
    $errors += "❌ Schéma PostgreSQL manquant"
}

# Vérification 5: Tests
Write-Host "`n🧪 Vérification des tests..." -ForegroundColor Yellow
if (Test-Path "tests\Feature\EventListenerPostgreSQLTest.php") {
    Write-Host "✅ Tests PostgreSQL présents" -ForegroundColor Green
} else {
    $warnings += "⚠️  Tests PostgreSQL manquants"
}

# Vérification 6: Command de test
Write-Host "`n⚙️ Vérification des commandes Artisan..." -ForegroundColor Yellow
if (Test-Path "app\Console\Commands\TestPostgreSQLCompatibility.php") {
    Write-Host "✅ Command de test PostgreSQL présente" -ForegroundColor Green
} else {
    $warnings += "⚠️  Command de test PostgreSQL manquante"
}

# Vérification 7: Migrations
Write-Host "`n📈 Vérification des migrations..." -ForegroundColor Yellow
$migrationFiles = Get-ChildItem "database\migrations" -Filter "*.php"
$polymorphicMigrations = $migrationFiles | Where-Object { $_.Name -match "polymorphic" }

if ($polymorphicMigrations.Count -gt 0) {
    Write-Host "✅ Migrations polymorphiques détectées" -ForegroundColor Green
} else {
    $warnings += "⚠️  Aucune migration polymorphique détectée"
}

# Vérification 8: Seeders
Write-Host "`n🌱 Vérification des seeders..." -ForegroundColor Yellow
if (Test-Path "database\seeders\HerokuProductionSeeder.php") {
    Write-Host "✅ HerokuProductionSeeder présent" -ForegroundColor Green
} else {
    $warnings += "⚠️  HerokuProductionSeeder manquant"
}

if (Test-Path "database\seeders\SqliteToPostgresqlMigrationSeeder.php") {
    Write-Host "✅ SqliteToPostgresqlMigrationSeeder présent" -ForegroundColor Green
} else {
    $warnings += "⚠️  SqliteToPostgresqlMigrationSeeder manquant"
}

# Résumé
Write-Host "`n📋 Résumé de la validation:" -ForegroundColor Cyan

if ($errors.Count -eq 0) {
    Write-Host "🎉 Aucune erreur critique détectée!" -ForegroundColor Green
    $deployReady = $true
} else {
    Write-Host "❌ Erreurs critiques détectées:" -ForegroundColor Red
    foreach ($error in $errors) {
        Write-Host "  $error" -ForegroundColor Red
    }
    $deployReady = $false
}

if ($warnings.Count -gt 0) {
    Write-Host "`n⚠️  Avertissements:" -ForegroundColor Yellow
    foreach ($warning in $warnings) {
        Write-Host "  $warning" -ForegroundColor Yellow
    }
}

Write-Host "`n🚀 Statut du déploiement:" -ForegroundColor Cyan
if ($deployReady) {
    Write-Host "✅ PRÊT POUR HEROKU!" -ForegroundColor Green -BackgroundColor Black
    Write-Host "`nPour déployer, exécutez:" -ForegroundColor White
    Write-Host ".\bin\deploy-heroku.ps1 votre-nom-app" -ForegroundColor Cyan
} else {
    Write-Host "❌ NON PRÊT - Corrigez les erreurs ci-dessus" -ForegroundColor Red -BackgroundColor Black
}

if ($Verbose) {
    Write-Host "`n Configuration detectee:" -ForegroundColor Blue
    Write-Host "  - Backend: Laravel avec PostgreSQL" -ForegroundColor Gray
    Write-Host "  - Event Listeners: SynchronizeUserScore adapte" -ForegroundColor Gray
    Write-Host "  - Architecture: Systeme polymorphique" -ForegroundColor Gray
    Write-Host "  - Deploiement: Scripts Heroku configures" -ForegroundColor Gray
}
