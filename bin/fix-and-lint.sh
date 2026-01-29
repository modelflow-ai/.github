#!/bin/bash

# Script to run composer fix and lint commands in all packages, integrations, and projects
# Usage: ./bin/fix-and-lint.sh [--symfony]

# Determine which composer command to use
if ! COMPOSER=$(command -v composer); then
    echo "Error: composer command not found in PATH" >&2
    exit 1
fi

if [ "$1" = "--symfony" ]; then
    if command -v symfony &> /dev/null; then
        COMPOSER="symfony php $COMPOSER"
        echo "Using symfony composer"
    else
        echo "Warning: symfony command not found, falling back to composer"
    fi
else
    echo "Using composer"
fi

# Get the repository root directory
REPO_ROOT="$(cd "$(dirname "$0")/.." && pwd)" || {
    echo "Error: Failed to determine repository root directory" >&2
    exit 1
}

if ! cd "$REPO_ROOT"; then
    echo "Error: Failed to change to repository root: $REPO_ROOT" >&2
    exit 1
fi

# Count total directories
TOTAL=0
for dir in packages/* integrations/* projects/*; do
    if [ -d "$dir" ] && [ -f "$dir/composer.json" ]; then
        TOTAL=$((TOTAL + 1))
    fi
done

# Process each directory
CURRENT=0
FAILED=()

for dir in packages/* integrations/* projects/*; do
    if [ -d "$dir" ] && [ -f "$dir/composer.json" ]; then
        CURRENT=$((CURRENT + 1))
        echo ""
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
        echo "[$CURRENT/$TOTAL] Processing: $dir"
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

        if ! cd "$REPO_ROOT/$dir"; then
            echo "❌ $dir - failed to change directory"
            FAILED+=("$dir")
            continue
        fi

        if $COMPOSER fix && $COMPOSER lint; then
            echo "✅ $dir completed successfully"
        else
            echo "❌ $dir failed"
            FAILED+=("$dir")
        fi

        if ! cd "$REPO_ROOT"; then
            echo "Error: Failed to return to repository root" >&2
            exit 1
        fi
    fi
done

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "SUMMARY"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "Total processed: $TOTAL"
echo "Successful: $((TOTAL - ${#FAILED[@]}))"
echo "Failed: ${#FAILED[@]}"

if [ ${#FAILED[@]} -gt 0 ]; then
    echo ""
    echo "Failed directories:"
    for dir in "${FAILED[@]}"; do
        echo "  - $dir"
    done
    exit 1
else
    echo ""
    echo "✅ All directories completed successfully!"
fi
