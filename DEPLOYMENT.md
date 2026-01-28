# Deployment Guide to Packagist

## Prerequisites

- GitHub account with repository access
- Packagist account (chandan07cse)
- Git installed locally

## Step 1: Create GitHub Repository

1. Go to https://github.com/chandan07cse
2. Click "New Repository"
3. Name it: `robi-sms`
4. Description: "Laravel package for Robi/AdaReach SMS API"
5. Make it **Public**
6. Don't initialize with README (we already have one)
7. Click "Create repository"

## Step 2: Push Your Package to GitHub

```bash
# Navigate to your package directory
cd adarearch-laravel

# Initialize git repository
git init

# Add all files
git add .

# Commit
git commit -m "Initial commit: Robi SMS Laravel package v1.0.0"

# Add GitHub remote (replace with your actual repo URL)
git remote add origin https://github.com/chandan07cse/robi-sms.git

# Create and push to main branch
git branch -M main
git push -u origin main

# Create a release tag
git tag -a v1.0.0 -m "Release version 1.0.0"
git push origin v1.0.0
```

## Step 3: Submit to Packagist

1. Go to https://packagist.org/
2. Log in with your account (chandan07cse)
3. Click "Submit" in the top menu
4. Enter your repository URL: `https://github.com/chandan07cse/robi-sms`
5. Click "Check"
6. If validation passes, click "Submit"

## Step 4: Setup Auto-Update Hook

### Option A: Using GitHub Service Hook (Recommended)

1. Go to your GitHub repository settings
2. Navigate to "Webhooks" → "Add webhook"
3. Payload URL: `https://packagist.org/api/github?username=chandan07cse`
4. Content type: `application/json`
5. Secret: Get from Packagist profile → "Show API Token"
6. Select "Just the push event"
7. Click "Add webhook"

### Option B: Using Packagist API Token

1. Go to your Packagist profile
2. Click "Show API Token"
3. Copy the token
4. In GitHub repo settings → Secrets → Actions
5. Add secret: Name: `PACKAGIST_TOKEN`, Value: your token

Add this workflow file: `.github/workflows/packagist-update.yml`

```yaml
name: Update Packagist

on:
  push:
    tags:
      - 'v*'

jobs:
  update-packagist:
    runs-on: ubuntu-latest
    steps:
      - name: Update Packagist
        run: |
          curl -X POST https://packagist.org/api/update-package?username=chandan07cse \
          -H "Content-Type: application/json" \
          -d '{"repository":{"url":"https://github.com/chandan07cse/robi-sms"}}'
```

## Step 5: Verify Installation

Once published, test installation:

```bash
composer require chandan07cse/robi-sms
```

## Step 6: Create Releases

For future updates:

```bash
# Make your changes
git add .
git commit -m "Description of changes"
git push

# Create new version tag
git tag -a v1.0.1 -m "Bug fixes and improvements"
git push origin v1.0.1
```

## Package Information

**Package Name:** `chandan07cse/robi-sms`
**GitHub URL:** https://github.com/chandan07cse/robi-sms
**Packagist URL:** https://packagist.org/packages/chandan07cse/robi-sms (after submission)

## Installation Command for Users

```bash
composer require chandan07cse/robi-sms
```

## Composer.json for User Projects

Users will install like this:

```json
{
    "require": {
        "chandan07cse/robi-sms": "^1.0"
    }
}
```

## Best Practices

### Semantic Versioning

- **Major (v2.0.0)**: Breaking changes
- **Minor (v1.1.0)**: New features, backward compatible
- **Patch (v1.0.1)**: Bug fixes

### Before Each Release

1. Update CHANGELOG.md
2. Update version in composer.json if needed
3. Test thoroughly
4. Create git tag
5. Push tag to trigger auto-update

### Documentation

- Keep README.md updated
- Document breaking changes clearly
- Provide migration guides for major versions

## Troubleshooting

**Package not found after submission:**
- Wait 5-10 minutes for Packagist to index
- Check repository is public
- Verify composer.json is valid

**Updates not showing:**
- Check webhook is configured
- Manually trigger update on Packagist
- Verify tags are pushed to GitHub

**Installation fails:**
- Check minimum PHP version requirement
- Verify Laravel version compatibility
- Review composer.json dependencies

## Marketing Your Package

1. Add topics to GitHub repo: `laravel`, `sms`, `robi`, `bangladesh`
2. Create detailed README with badges
3. Share on Laravel communities
4. Write a blog post
5. Share on social media

## Support

For package-specific issues:
- GitHub Issues: https://github.com/chandan07cse/robi-sms/issues
- Email: chandan07cse@gmail.com

For API-related issues:
- AdaReach Support: helpdesk@adaglobal.com
