<#
.SYNOPSIS
    Sync with upstream OSPOS repository

.DESCRIPTION
    This script fetches the latest changes from the original OSPOS repository
    and merges them into your master branch automatically.

.EXAMPLE
    .\sync-upstream.ps1
#>

Write-Host "🔄 Syncing with upstream OSPOS repository..." -ForegroundColor Cyan

try {
    # Check if we're in a git repository
    if (-not (Test-Path ".git")) {
        throw "Not in a git repository"
    }

    # Fetch latest changes from upstream
    Write-Host "📥 Fetching upstream changes..." -ForegroundColor Yellow
    git fetch upstream
    if ($LASTEXITCODE -ne 0) { throw "Failed to fetch upstream" }

    # Get current branch
    $currentBranch = git branch --show-current
    Write-Host "📍 Current branch: $currentBranch" -ForegroundColor Green

    # Switch to master if not already there
    if ($currentBranch -ne "master") {
        Write-Host "🔀 Switching to master branch..." -ForegroundColor Yellow
        git checkout master
        if ($LASTEXITCODE -ne 0) { throw "Failed to checkout master" }
    }

    # Pull latest changes from your origin/master
    Write-Host "📥 Pulling latest from origin/master..." -ForegroundColor Yellow
    git pull origin master
    if ($LASTEXITCODE -ne 0) { throw "Failed to pull from origin" }

    # Check if there are new commits in upstream
    $upstreamCommits = git rev-list --count master..upstream/master
    if ($upstreamCommits -eq "0") {
        Write-Host "✅ Already up to date with upstream!" -ForegroundColor Green
        exit 0
    }

    Write-Host "📊 Found $upstreamCommits new commits in upstream" -ForegroundColor Cyan

    # Merge upstream changes
    Write-Host "🔀 Merging upstream/master..." -ForegroundColor Yellow
    git merge upstream/master --no-edit
    if ($LASTEXITCODE -ne 0) { throw "Failed to merge upstream changes" }

    # Push updates to your fork
    Write-Host "📤 Pushing updates to your fork..." -ForegroundColor Yellow
    git push origin master
    if ($LASTEXITCODE -ne 0) { throw "Failed to push to origin" }

    Write-Host "✅ Successfully synced with upstream!" -ForegroundColor Green
    Write-Host "📈 Your repository is now up to date with the original OSPOS" -ForegroundColor Green

} catch {
    Write-Host "❌ Error: $($_.Exception.Message)" -ForegroundColor Red
    exit 1
}
