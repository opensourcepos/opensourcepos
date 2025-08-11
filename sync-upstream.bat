@echo off
REM Simple script to sync with upstream OSPOS repository

echo 🔄 Syncing with upstream OSPOS repository...

REM Check if we're in a git repository
if not exist ".git" (
    echo ❌ Error: Not in a git repository
    exit /b 1
)

echo 📥 Fetching upstream changes...
git fetch upstream
if %errorlevel% neq 0 (
    echo ❌ Failed to fetch upstream
    exit /b 1
)

REM Get current branch
for /f %%i in ('git branch --show-current') do set currentBranch=%%i
echo 📍 Current branch: %currentBranch%

REM Switch to master if not already there
if not "%currentBranch%"=="master" (
    echo 🔀 Switching to master branch...
    git checkout master
    if %errorlevel% neq 0 (
        echo ❌ Failed to checkout master
        exit /b 1
    )
)

echo 📥 Pulling latest from origin/master...
git pull origin master
if %errorlevel% neq 0 (
    echo ❌ Failed to pull from origin
    exit /b 1
)

REM Check if there are new commits in upstream
for /f %%i in ('git rev-list --count master..upstream/master') do set upstreamCommits=%%i
if "%upstreamCommits%"=="0" (
    echo ✅ Already up to date with upstream!
    exit /b 0
)

echo 📊 Found %upstreamCommits% new commits in upstream

echo 🔀 Merging upstream/master...
git merge upstream/master --no-edit
if %errorlevel% neq 0 (
    echo ❌ Failed to merge upstream changes
    exit /b 1
)

echo 📤 Pushing updates to your fork...
git push origin master
if %errorlevel% neq 0 (
    echo ❌ Failed to push to origin
    exit /b 1
)

echo ✅ Successfully synced with upstream!
echo 📈 Your repository is now up to date with the original OSPOS
