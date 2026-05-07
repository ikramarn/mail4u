#!/usr/bin/env bash
# =============================================================================
# commit-all.sh — Stage and commit every tracked/untracked file individually
# Usage (Git Bash): bash commit-all.sh
#         OR (PowerShell): & "C:\Program Files\Git\bin\bash.exe" commit-all.sh
# =============================================================================

set -euo pipefail

REMOTE="origin"
BRANCH="main"

echo ">>> Initialising git (safe to run if already initialised)"
git init 2>/dev/null || true
git remote add "$REMOTE" https://github.com/ikramarn/mail4u.git 2>/dev/null || \
    git remote set-url "$REMOTE" https://github.com/ikramarn/mail4u.git

echo ">>> Ensuring we are on branch: $BRANCH"
git checkout -B "$BRANCH" 2>/dev/null || true

echo ">>> Committing each changed / new file individually ..."

# Collect all files: modified/staged AND files inside untracked directories
mapfile -t FILES < <(
    {
        # Modified or staged files
        git status --porcelain | grep -v '^??' | awk '{print $2}'
        # Untracked files (expand directories recursively)
        git status --porcelain | grep '^??' | awk '{print $2}' | while read -r entry; do
            if [ -d "$entry" ]; then
                find "$entry" -type f
            else
                echo "$entry"
            fi
        done
    } | sort -u
)

if [ ${#FILES[@]} -eq 0 ]; then
    echo "Nothing to commit – working tree is clean."
    exit 0
fi

for FILE in "${FILES[@]}"; do
    if [ -f "$FILE" ]; then
        git add -- "$FILE"
        git commit -m "chore: update ${FILE}" || true
        echo "  committed: $FILE"
    fi
done

echo ""
echo ">>> Pushing to GitHub ($REMOTE/$BRANCH) ..."
git push -u "$REMOTE" "$BRANCH"
echo ">>> Done."
