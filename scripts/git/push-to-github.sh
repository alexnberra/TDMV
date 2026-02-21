#!/usr/bin/env bash
set -Eeuo pipefail

if [ "$#" -lt 1 ]; then
  echo "Usage: $0 <github-remote-url> [branch]"
  exit 1
fi

REMOTE_URL="$1"
BRANCH="${2:-main}"

git remote remove origin 2>/dev/null || true
git remote add origin "$REMOTE_URL"
git push -u origin "$BRANCH"
