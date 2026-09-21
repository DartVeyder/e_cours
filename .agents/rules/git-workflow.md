---
description: Rule requiring explicit user confirmation before executing any git commit or push.
---

# Git Workflow: Mandatory User Approval

## Rules for Git Operations
1. **Never commit automatically**: Do not execute `git commit` or `git push` autonomously.
2. **Review & Propose**:
   - Present a concise diff/status of modified and created files.
   - Propose an English commit message following Conventional Commits format (`feat`, `fix`, `docs`, `refactor`, `test`).
3. **Wait for confirmation**: Ask the user explicitly for confirmation before running `git commit`.
4. **Execute upon approval**: Only execute `git add` and `git commit` once the user responds with approval.
