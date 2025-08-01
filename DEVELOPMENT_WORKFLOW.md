# OSPOS Fork Development Workflow

This document explains how to manage your OSPOS fork with automatic upstream synchronization and isolated feature development.

## Repository Structure

```
sahand009/opensourcepos (your fork)
├── master (synced with upstream)
├── feature/turnstile-enhancement
├── feature/custom-reports
├── feature/payment-gateway
└── develop (optional staging branch)
```

## Quick Commands

### Sync with Upstream
```powershell
# Manual sync
.\sync-upstream.ps1

# Or using the branch manager
.\manage-branches.ps1 -Action sync
```

### Feature Branch Management
```powershell
# Create new feature branch
.\manage-branches.ps1 -Action create -BranchName "turnstile-enhancement"

# List all branches
.\manage-branches.ps1 -Action list

# Switch to feature branch
.\manage-branches.ps1 -Action switch -BranchName "feature/turnstile-enhancement"

# Merge feature to master
.\manage-branches.ps1 -Action merge -BranchName "feature/turnstile-enhancement"

# Delete feature branch
.\manage-branches.ps1 -Action delete -BranchName "feature/turnstile-enhancement"
```

## Workflow Steps

### 1. Starting a New Feature

```powershell
# Create and switch to new feature branch
.\manage-branches.ps1 -Action create -BranchName "my-new-feature"

# Make your changes
# ... edit files ...

# Commit your changes
git add .
git commit -m "Add my new feature"
git push origin feature/my-new-feature
```

### 2. Keeping Your Fork Updated

The repository automatically syncs with upstream every Sunday, or you can manually sync:

```powershell
# Manual sync
.\sync-upstream.ps1
```

### 3. Merging Features

```powershell
# Merge your feature to master
.\manage-branches.ps1 -Action merge -BranchName "feature/my-new-feature"

# Clean up (optional)
.\manage-branches.ps1 -Action delete -BranchName "feature/my-new-feature"
```

## Automatic Synchronization

- **GitHub Actions** automatically sync with upstream every Sunday
- If conflicts occur, a PR is created for manual resolution
- Your custom features in separate branches remain unaffected

## Best Practices

### ✅ Do's
- Always create feature branches from latest master
- Use descriptive branch names: `feature/turnstile-integration`
- Keep features small and focused
- Test before merging to master
- Regularly sync with upstream

### ❌ Don'ts
- Don't develop directly on master
- Don't merge feature branches into each other
- Don't force push to master
- Don't ignore upstream updates

## Branch Protection

- **master**: Always synced with upstream + your merged features
- **feature/\***: Isolated development, doesn't affect other branches
- **upstream**: Original OSPOS repository (read-only)

## Conflict Resolution

When upstream changes conflict with your changes:

1. The auto-sync will create a PR with conflicts
2. Manually resolve conflicts in the PR
3. Test the resolved code
4. Merge the PR when ready

## Example Feature Development

```powershell
# 1. Create feature branch
.\manage-branches.ps1 -Action create -BranchName "enhanced-reporting"

# 2. Develop your feature
# ... make changes to reports ...
git add app/Controllers/Reports.php
git commit -m "Add enhanced reporting features"
git push origin feature/enhanced-reporting

# 3. Merge when ready
.\manage-branches.ps1 -Action merge -BranchName "feature/enhanced-reporting"

# 4. Clean up
.\manage-branches.ps1 -Action delete -BranchName "feature/enhanced-reporting"
```

## Monitoring

- Check GitHub Actions tab for sync status
- Watch for PR notifications about conflicts
- Monitor upstream repository for important updates

## Support

If you encounter issues:
1. Check the GitHub Actions logs
2. Run `git status` to see current state
3. Use `.\manage-branches.ps1 -Action list` to see all branches
4. Manual resolution using standard Git commands if needed
