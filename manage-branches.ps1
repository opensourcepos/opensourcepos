#!/usr/bin/env pwsh
<#
.SYNOPSIS
    Manage feature branches for OSPOS development

.DESCRIPTION
    This script helps create, switch, and manage feature branches
    to keep your features isolated from each other and from the upstream.

.PARAMETER Action
    The action to perform: create, switch, list, merge, delete

.PARAMETER BranchName
    The name of the feature branch

.EXAMPLE
    .\manage-branches.ps1 -Action create -BranchName "feature/turnstile-enhancement"
    .\manage-branches.ps1 -Action list
    .\manage-branches.ps1 -Action switch -BranchName "feature/payment-gateway"
#>

param(
    [Parameter(Mandatory=$true)]
    [ValidateSet("create", "switch", "list", "merge", "delete", "sync")]
    [string]$Action,
    
    [Parameter(Mandatory=$false)]
    [string]$BranchName
)

function Write-ColorOutput {
    param($Text, $Color = "White")
    Write-Host $Text -ForegroundColor $Color
}

function Get-CurrentBranch {
    return git branch --show-current
}

function Test-GitRepository {
    if (-not (Test-Path ".git")) {
        throw "Not in a git repository"
    }
}

function Sync-WithUpstream {
    Write-ColorOutput "🔄 Syncing master with upstream..." "Cyan"
    git checkout master
    git fetch upstream
    git merge upstream/master --no-edit
    git push origin master
    Write-ColorOutput "✅ Master branch synced!" "Green"
}

try {
    Test-GitRepository

    switch ($Action) {
        "create" {
            if (-not $BranchName) {
                throw "Branch name is required for create action"
            }
            
            if (-not $BranchName.StartsWith("feature/")) {
                $BranchName = "feature/$BranchName"
            }
            
            Write-ColorOutput "🌟 Creating new feature branch: $BranchName" "Cyan"
            
            # Ensure we're on master and up to date
            Write-ColorOutput "📍 Switching to master and updating..." "Yellow"
            git checkout master
            git pull origin master
            
            # Create and switch to new branch
            git checkout -b $BranchName
            git push -u origin $BranchName
            
            Write-ColorOutput "✅ Feature branch '$BranchName' created and pushed!" "Green"
            Write-ColorOutput "💡 You can now work on your feature in isolation" "Blue"
        }
        
        "switch" {
            if (-not $BranchName) {
                throw "Branch name is required for switch action"
            }
            
            Write-ColorOutput "🔀 Switching to branch: $BranchName" "Cyan"
            git checkout $BranchName
            Write-ColorOutput "✅ Switched to '$BranchName'" "Green"
        }
        
        "list" {
            Write-ColorOutput "📋 Available branches:" "Cyan"
            Write-ColorOutput "Local branches:" "Yellow"
            git branch
            Write-ColorOutput "`nRemote branches:" "Yellow"
            git branch -r
        }
        
        "merge" {
            if (-not $BranchName) {
                throw "Branch name is required for merge action"
            }
            
            $currentBranch = Get-CurrentBranch
            
            Write-ColorOutput "🔀 Merging '$BranchName' into master..." "Cyan"
            
            # Switch to master and update
            git checkout master
            git pull origin master
            
            # Merge feature branch
            git merge $BranchName --no-edit
            
            # Push to origin
            git push origin master
            
            Write-ColorOutput "✅ Feature branch '$BranchName' merged into master!" "Green"
            Write-ColorOutput "💡 Consider deleting the feature branch if no longer needed" "Blue"
        }
        
        "delete" {
            if (-not $BranchName) {
                throw "Branch name is required for delete action"
            }
            
            $currentBranch = Get-CurrentBranch
            
            if ($currentBranch -eq $BranchName) {
                Write-ColorOutput "🔀 Switching to master first..." "Yellow"
                git checkout master
            }
            
            Write-ColorOutput "🗑️ Deleting branch: $BranchName" "Red"
            
            # Delete local branch
            git branch -d $BranchName
            
            # Delete remote branch
            git push origin --delete $BranchName
            
            Write-ColorOutput "✅ Branch '$BranchName' deleted!" "Green"
        }
        
        "sync" {
            Sync-WithUpstream
        }
    }
    
} catch {
    Write-ColorOutput "❌ Error: $_" "Red"
    exit 1
}
