@plan.md @activity.md @PRD.md

We are building a Laravel package from scratch in this repo, following the structure of https://github.com/blamodex/laravel-addresses

First read activity.md to see what was recently accomplished.

Open plan.md and choose the single highest priority task where passes is false.

Work on exactly ONE task: implement the change following Laravel package best practices.

After implementing the task:
1. Run tests if applicable: `composer test` or `vendor/bin/phpunit`
2. Run linter if applicable: `composer lint` or `vendor/bin/phpcs`
3. Run static analysis if applicable: `composer analyze` or `vendor/bin/phpstan`
4. Take a screenshot of the terminal output showing passing tests/checks
5. Save screenshot as screenshots/[task-name].png

Append a dated progress entry to activity.md describing:
- What you changed
- Files created/modified
- Test results (if applicable)
- Screenshot filename

Update that task's passes in plan.md from false to true.

Make one git commit for that task only with a clear, conventional commit message (e.g., "feat: add List model with relationships").

Do not git init, do not change remotes, do not push.

ONLY WORK ON A SINGLE TASK. Follow the exact structure and patterns from laravel-addresses reference.

When ALL tasks have passes true, output <promise>COMPLETE</promise>