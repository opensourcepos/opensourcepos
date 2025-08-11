@echo off
REM Simple script to manage feature branches

set action=%1
set branchName=%2

if "%action%"=="" (
    echo Usage: manage-branches.bat [create^|switch^|list^|merge^|delete] [branch-name]
    echo.
    echo Examples:
    echo   manage-branches.bat create my-feature
    echo   manage-branches.bat list
    echo   manage-branches.bat switch feature/my-feature
    echo   manage-branches.bat merge feature/my-feature
    echo   manage-branches.bat delete feature/my-feature
    exit /b 1
)

REM Check if we're in a git repository
if not exist ".git" (
    echo ❌ Error: Not in a git repository
    exit /b 1
)

if "%action%"=="create" (
    if "%branchName%"=="" (
        echo ❌ Error: Branch name is required for create action
        exit /b 1
    )
    
    REM Add feature/ prefix if not present
    echo %branchName% | findstr /b "feature/" >nul
    if %errorlevel% neq 0 (
        set branchName=feature/%branchName%
    )
    
    echo 🌟 Creating new feature branch: !branchName!
    echo 📍 Switching to master and updating...
    git checkout master
    git pull origin master
    
    echo 🔀 Creating and switching to new branch...
    git checkout -b !branchName!
    git push -u origin !branchName!
    
    echo ✅ Feature branch '!branchName!' created and pushed!
    echo 💡 You can now work on your feature in isolation
)

if "%action%"=="switch" (
    if "%branchName%"=="" (
        echo ❌ Error: Branch name is required for switch action
        exit /b 1
    )
    
    echo 🔀 Switching to branch: %branchName%
    git checkout %branchName%
    echo ✅ Switched to '%branchName%'
)

if "%action%"=="list" (
    echo 📋 Available branches:
    echo Local branches:
    git branch
    echo.
    echo Remote branches:
    git branch -r
)

if "%action%"=="merge" (
    if "%branchName%"=="" (
        echo ❌ Error: Branch name is required for merge action
        exit /b 1
    )
    
    echo 🔀 Merging '%branchName%' into master...
    git checkout master
    git pull origin master
    git merge %branchName% --no-edit
    git push origin master
    
    echo ✅ Feature branch '%branchName%' merged into master!
    echo 💡 Consider deleting the feature branch if no longer needed
)

if "%action%"=="delete" (
    if "%branchName%"=="" (
        echo ❌ Error: Branch name is required for delete action
        exit /b 1
    )
    
    for /f %%i in ('git branch --show-current') do set currentBranch=%%i
    
    if "%currentBranch%"=="%branchName%" (
        echo 🔀 Switching to master first...
        git checkout master
    )
    
    echo 🗑️ Deleting branch: %branchName%
    git branch -d %branchName%
    git push origin --delete %branchName%
    
    echo ✅ Branch '%branchName%' deleted!
)
