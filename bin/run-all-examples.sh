#!/bin/bash

# Exit immediately if a command exits with a non-zero status
set -e

# Get the root directory of your project
ROOT_DIR=$(pwd)

# Create a temporary file to store issues
ISSUES_FILE=$(mktemp)

# Counter for statistics
TOTAL_FILES=0
SUCCESSFUL_FILES=0
FAILED_FILES=0

# Loop over all PHP files in examples directories of all packages
for FILE in $(find $ROOT_DIR/packages -name 'vendor' -prune -o -name 'examples' -type d -exec find {} -name '*.php' \;); do
    # Exclude bootstrap.php and files starting with an uppercase letter
    FILENAME=$(basename $FILE)
    FIRST_CHAR=${FILENAME:0:1}
    if [[ $FILE != *"bootstrap.php"* ]] && [[ $FIRST_CHAR =~ [a-z] ]]; then
        TOTAL_FILES=$((TOTAL_FILES + 1))
        echo ""
        echo "Running $FILE"

        # Run the PHP file and capture output and exit status
        OUTPUT=$(symfony php $FILE 2>&1) || {
            EXIT_CODE=$?
            FAILED_FILES=$((FAILED_FILES + 1))
            echo "ERROR: Failed with exit code $EXIT_CODE"
            echo "FILE: $FILE" >> $ISSUES_FILE
            echo "EXIT_CODE: $EXIT_CODE" >> $ISSUES_FILE
            echo "OUTPUT:" >> $ISSUES_FILE
            echo "$OUTPUT" >> $ISSUES_FILE
            echo "----------------------------------------" >> $ISSUES_FILE
            continue
        }

        # Check if output contains error/warning messages
        if echo "$OUTPUT" | grep -i -E "error|warning|exception|fatal" > /dev/null; then
            FAILED_FILES=$((FAILED_FILES + 1))
            echo "WARNING: Output contains potential issues"
            echo "FILE: $FILE" >> $ISSUES_FILE
            echo "EXIT_CODE: 0 (but output contains error keywords)" >> $ISSUES_FILE
            echo "OUTPUT:" >> $ISSUES_FILE
            echo "$OUTPUT" >> $ISSUES_FILE
            echo "----------------------------------------" >> $ISSUES_FILE
        else
            SUCCESSFUL_FILES=$((SUCCESSFUL_FILES + 1))
            echo "Successfully ran $FILE"
        fi
    fi
done

# Print summary
echo ""
echo "===== EXECUTION SUMMARY ====="
echo "Total files processed: $TOTAL_FILES"
echo "Successfully executed: $SUCCESSFUL_FILES"
echo "Failed executions: $FAILED_FILES"
echo ""

# Print issues if there are any
if [ $FAILED_FILES -gt 0 ]; then
    echo "===== ISSUES FOUND ====="
    cat $ISSUES_FILE
fi

# Clean up
rm $ISSUES_FILE

# Return error code if any failures
if [ $FAILED_FILES -gt 0 ]; then
    exit 1
fi
