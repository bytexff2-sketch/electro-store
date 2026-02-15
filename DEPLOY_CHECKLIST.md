Deploy checklist
===============

Steps to prepare and deploy with reproducible PHP dependencies:

1. Generate `composer.lock` locally:

```bash
composer update
```

2. Add the lock file and the `.gitignore` change:

```bash
git add composer.lock .gitignore
git commit -m "Include composer.lock for reproducible builds"
```

3. Push to a branch and open a PR:

```bash
git checkout -b ci/include-composer-lock
git push -u origin ci/include-composer-lock
# then open a PR on GitHub comparing this branch to main/master
```

4. Redeploy after PR is merged (platform-specific). If using Heroku/Buildpacks, the presence of `composer.lock` will allow builds to proceed.

Notes:
- Do not keep `composer.lock` in `.gitignore`.
- Prefer `composer update` locally and `composer install` on CI/deployers.
