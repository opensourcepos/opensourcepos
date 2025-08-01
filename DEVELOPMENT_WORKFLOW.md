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
```cmd
# Manual sync
git fetch upstream
git checkout master
git pull origin master
git merge upstream/master --no-edit
git push origin master

# Or use the batch script
.\sync-upstream.bat
```

### Feature Branch Management
```cmd
# Create new feature branch
git checkout master
git pull origin master
git checkout -b feature/your-feature-name
git push -u origin feature/your-feature-name

# List all branches
git branch -a

# Switch to feature branch
git checkout feature/your-feature-name

# Merge feature to master
git checkout master
git pull origin master
git merge feature/your-feature-name --no-edit
git push origin master

# Delete feature branch
git branch -d feature/your-feature-name
git push origin --delete feature/your-feature-name
```

## Workflow Steps

### 1. Starting a New Feature

```cmd
# Create and switch to new feature branch
git checkout master
git pull origin master
git checkout -b feature/my-new-feature
git push -u origin feature/my-new-feature

# Make your changes
# ... edit files ...

# Commit your changes
git add .
git commit -m "Add my new feature"
git push origin feature/my-new-feature
```

### 2. Keeping Your Fork Updated

The repository automatically syncs with upstream every Sunday, or you can manually sync:

```cmd
# Manual sync
git fetch upstream
git checkout master
git pull origin master
git merge upstream/master --no-edit
git push origin master
```

### 3. Merging Features

```cmd
# Merge your feature to master
git checkout master
git pull origin master
git merge feature/my-new-feature --no-edit
git push origin master

# Clean up (optional)
git branch -d feature/my-new-feature
git push origin --delete feature/my-new-feature
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

```cmd
# 1. Create feature branch
git checkout master
git pull origin master
git checkout -b feature/enhanced-reporting
git push -u origin feature/enhanced-reporting

# 2. Develop your feature
# ... make changes to reports ...
git add app/Controllers/Reports.php
git commit -m "Add enhanced reporting features"
git push origin feature/enhanced-reporting

# 3. Merge when ready
git checkout master
git pull origin master
git merge feature/enhanced-reporting --no-edit
git push origin master

# 4. Clean up
git branch -d feature/enhanced-reporting
git push origin --delete feature/enhanced-reporting
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
