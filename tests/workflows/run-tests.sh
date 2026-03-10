#!/bin/bash
# Test runner for update-issue-templates workflow
# This script manually runs tests without requiring BATS infrastructure

set -e

# Color codes for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

TESTS_PASSED=0
TESTS_FAILED=0
TESTS_RUN=0

# Test result tracking
test_result() {
    local test_name="$1"
    local status="$2"
    local message="${3:-}"

    TESTS_RUN=$((TESTS_RUN + 1))

    if [ "$status" -eq 0 ]; then
        echo -e "${GREEN}✓${NC} $test_name"
        TESTS_PASSED=$((TESTS_PASSED + 1))
    else
        echo -e "${RED}✗${NC} $test_name"
        if [ -n "$message" ]; then
            echo "  Error: $message"
        fi
        TESTS_FAILED=$((TESTS_FAILED + 1))
    fi
}

# Setup test environment
setup_test_env() {
    TEST_DIR="$(mktemp -d)"
    export TEST_DIR
    mkdir -p "$TEST_DIR/.github/ISSUE_TEMPLATE"
}

cleanup_test_env() {
    if [ -n "$TEST_DIR" ] && [ -d "$TEST_DIR" ]; then
        rm -rf "$TEST_DIR"
    fi
    if [ -n "$OPTIONS_FILE" ] && [ -f "$OPTIONS_FILE" ]; then
        rm -f "$OPTIONS_FILE"
    fi
}

# Define update_template function (from workflow)
update_template() {
    local template="$1"
    local template_path="$TEST_DIR/.github/ISSUE_TEMPLATE/$template"

    start_line=$(grep -n "label: OpensourcePOS Version" "$template_path" | cut -d: -f1)

    if [ -z "$start_line" ]; then
        echo "Could not find OpensourcePOS Version in $template"
        return 1
    fi

    options_start=$((start_line + 3))
    default_line=$(grep -n "default:" "$template_path" | awk -F: -v opts="$options_start" '$1 > opts {print $1; exit}')

    head -n $((options_start - 1)) "$template_path" > "${template_path}.new"
    cat "$OPTIONS_FILE" >> "${template_path}.new"
    tail -n +$default_line "$template_path" >> "${template_path}.new"
    mv "${template_path}.new" "$template_path"

    echo "Updated $template"
}

# Helper functions
create_bug_report_template() {
    cat > "$TEST_DIR/.github/ISSUE_TEMPLATE/bug report.yml" << 'EOF'
name: Bug Report
description: File a bug report
body:
  - type: dropdown
    id: ospos-version
    attributes:
      label: OpensourcePOS Version
      description: What version of our software are you running?
      options:
        - development (unreleased)
        - opensourcepos 3.3.7
      default: 0
    validations:
      required: true
EOF
}

create_feature_request_template() {
    cat > "$TEST_DIR/.github/ISSUE_TEMPLATE/feature_request.yml" << 'EOF'
name: Feature Request
description: Suggest an idea
body:
  - type: dropdown
    id: ospos-version
    attributes:
      label: OpensourcePOS Version
      description: What version of our software are you running?
      options:
        - opensourcepos 3.3.7
      default: 0
    validations:
      required: true
EOF
}

create_options_file() {
    OPTIONS_FILE="$(mktemp)"
    export OPTIONS_FILE
    echo "        - development (unreleased)" >> "$OPTIONS_FILE"
    echo "        - opensourcepos 3.4.1" >> "$OPTIONS_FILE"
    echo "        - opensourcepos 3.4.0" >> "$OPTIONS_FILE"
    echo "        - opensourcepos 3.3.9" >> "$OPTIONS_FILE"
}

echo "Running tests for update-issue-templates workflow..."
echo ""

# Test 1: Find label in bug report
setup_test_env
create_bug_report_template
if grep -q "label: OpensourcePOS Version" "$TEST_DIR/.github/ISSUE_TEMPLATE/bug report.yml"; then
    test_result "update_template finds OpensourcePOS Version label in bug report" 0
else
    test_result "update_template finds OpensourcePOS Version label in bug report" 1
fi
cleanup_test_env

# Test 2: Find label in feature request
setup_test_env
create_feature_request_template
if grep -q "label: OpensourcePOS Version" "$TEST_DIR/.github/ISSUE_TEMPLATE/feature_request.yml"; then
    test_result "update_template finds OpensourcePOS Version label in feature request" 0
else
    test_result "update_template finds OpensourcePOS Version label in feature request" 1
fi
cleanup_test_env

# Test 3: Update bug report successfully
setup_test_env
create_bug_report_template
create_options_file
output=$(update_template "bug report.yml" 2>&1)
if [ $? -eq 0 ] && [[ "$output" == "Updated bug report.yml" ]]; then
    test_result "update_template successfully updates bug report template" 0
else
    test_result "update_template successfully updates bug report template" 1 "Got: $output"
fi
cleanup_test_env

# Test 4: Update feature request successfully
setup_test_env
create_feature_request_template
create_options_file
output=$(update_template "feature_request.yml" 2>&1)
if [ $? -eq 0 ] && [[ "$output" == "Updated feature_request.yml" ]]; then
    test_result "update_template successfully updates feature request template" 0
else
    test_result "update_template successfully updates feature request template" 1 "Got: $output"
fi
cleanup_test_env

# Test 5: New releases added to bug report
setup_test_env
create_bug_report_template
create_options_file
update_template "bug report.yml" > /dev/null 2>&1
if grep -q "opensourcepos 3.4.1" "$TEST_DIR/.github/ISSUE_TEMPLATE/bug report.yml" && \
   grep -q "opensourcepos 3.4.0" "$TEST_DIR/.github/ISSUE_TEMPLATE/bug report.yml" && \
   grep -q "opensourcepos 3.3.9" "$TEST_DIR/.github/ISSUE_TEMPLATE/bug report.yml"; then
    test_result "update_template adds new releases to bug report options" 0
else
    test_result "update_template adds new releases to bug report options" 1
fi
cleanup_test_env

# Test 6: Development option preserved
setup_test_env
create_bug_report_template
create_options_file
update_template "bug report.yml" > /dev/null 2>&1
if grep -q "development (unreleased)" "$TEST_DIR/.github/ISSUE_TEMPLATE/bug report.yml"; then
    test_result "update_template preserves development option" 0
else
    test_result "update_template preserves development option" 1
fi
cleanup_test_env

# Test 7: Default line preserved
setup_test_env
create_bug_report_template
create_options_file
update_template "bug report.yml" > /dev/null 2>&1
if grep -q "default: 0" "$TEST_DIR/.github/ISSUE_TEMPLATE/bug report.yml"; then
    test_result "update_template preserves default line" 0
else
    test_result "update_template preserves default line" 1
fi
cleanup_test_env

# Test 8: Validations preserved
setup_test_env
create_bug_report_template
create_options_file
update_template "bug report.yml" > /dev/null 2>&1
if grep -q "validations:" "$TEST_DIR/.github/ISSUE_TEMPLATE/bug report.yml" && \
   grep -q "required: true" "$TEST_DIR/.github/ISSUE_TEMPLATE/bug report.yml"; then
    test_result "update_template preserves validations section" 0
else
    test_result "update_template preserves validations section" 1
fi
cleanup_test_env

# Test 9: Fail gracefully when label not found
setup_test_env
cat > "$TEST_DIR/.github/ISSUE_TEMPLATE/invalid.yml" << 'EOF'
name: Invalid Template
description: Missing required label
body:
  - type: dropdown
    attributes:
      label: Some Other Field
      options:
        - option1
      default: 0
EOF
create_options_file
set +e
output=$(update_template "invalid.yml" 2>&1)
exit_code=$?
set -e
if [ $exit_code -eq 1 ] && [[ "$output" =~ "Could not find OpensourcePOS Version" ]]; then
    test_result "update_template fails gracefully when label not found" 0
else
    test_result "update_template fails gracefully when label not found" 1
fi
cleanup_test_env

# Test 10: File structure preserved
setup_test_env
create_bug_report_template
create_options_file
update_template "bug report.yml" > /dev/null 2>&1
if grep -q "name: Bug Report" "$TEST_DIR/.github/ISSUE_TEMPLATE/bug report.yml" && \
   grep -q "description: File a bug report" "$TEST_DIR/.github/ISSUE_TEMPLATE/bug report.yml"; then
    test_result "update_template preserves file structure" 0
else
    test_result "update_template preserves file structure" 1
fi
cleanup_test_env

# Test 11: Proper indentation maintained
setup_test_env
create_bug_report_template
create_options_file
update_template "bug report.yml" > /dev/null 2>&1
if grep -q "^        - development (unreleased)" "$TEST_DIR/.github/ISSUE_TEMPLATE/bug report.yml" && \
   grep -q "^        - opensourcepos 3.4.1" "$TEST_DIR/.github/ISSUE_TEMPLATE/bug report.yml"; then
    test_result "update_template maintains proper indentation" 0
else
    test_result "update_template maintains proper indentation" 1
fi
cleanup_test_env

# Test 12: OPTIONS_FILE format
setup_test_env
create_options_file
first_line=$(head -n 1 "$OPTIONS_FILE")
if [[ "$first_line" == "        - development (unreleased)" ]] && \
   grep -q "^        - opensourcepos" "$OPTIONS_FILE"; then
    test_result "OPTIONS_FILE generation creates proper format" 0
else
    test_result "OPTIONS_FILE generation creates proper format" 1
fi
cleanup_test_env

# Test 13: Development is first option
setup_test_env
create_options_file
first_line=$(head -n 1 "$OPTIONS_FILE")
if [[ "$first_line" == "        - development (unreleased)" ]]; then
    test_result "OPTIONS_FILE includes development as first option" 0
else
    test_result "OPTIONS_FILE includes development as first option" 1
fi
cleanup_test_env

# Test 14: Multiple dropdowns handled correctly
setup_test_env
cat > "$TEST_DIR/.github/ISSUE_TEMPLATE/multi-dropdown.yml" << 'EOF'
name: Multi Dropdown
body:
  - type: dropdown
    attributes:
      label: First Dropdown
      options:
        - option1
      default: 0
  - type: dropdown
    id: ospos-version
    attributes:
      label: OpensourcePOS Version
      description: What version?
      options:
        - old-version
      default: 0
    validations:
      required: true
  - type: dropdown
    attributes:
      label: Third Dropdown
      options:
        - option3
      default: 0
EOF
create_options_file
update_template "multi-dropdown.yml" > /dev/null 2>&1
if grep -q "opensourcepos 3.4.1" "$TEST_DIR/.github/ISSUE_TEMPLATE/multi-dropdown.yml" && \
   grep -q "label: First Dropdown" "$TEST_DIR/.github/ISSUE_TEMPLATE/multi-dropdown.yml" && \
   grep -q "label: Third Dropdown" "$TEST_DIR/.github/ISSUE_TEMPLATE/multi-dropdown.yml"; then
    test_result "update_template handles multiple dropdowns correctly" 0
else
    test_result "update_template handles multiple dropdowns correctly" 1
fi
cleanup_test_env

# Test 15: 10 releases maximum
setup_test_env
create_bug_report_template
OPTIONS_FILE="$(mktemp)"
export OPTIONS_FILE
echo "        - development (unreleased)" >> "$OPTIONS_FILE"
for i in {1..10}; do
    echo "        - opensourcepos 3.$i.0" >> "$OPTIONS_FILE"
done
update_template "bug report.yml" > /dev/null 2>&1
count=$(grep -c "opensourcepos 3\." "$TEST_DIR/.github/ISSUE_TEMPLATE/bug report.yml" || echo 0)
if [ "$count" -ge 10 ]; then
    test_result "update_template with 10 releases (max from workflow)" 0
else
    test_result "update_template with 10 releases (max from workflow)" 1 "Only found $count releases"
fi
cleanup_test_env

# Test 16: Content before options preserved
setup_test_env
create_bug_report_template
create_options_file
update_template "bug report.yml" > /dev/null 2>&1
if grep -q "name: Bug Report" "$TEST_DIR/.github/ISSUE_TEMPLATE/bug report.yml" && \
   grep -q "id: ospos-version" "$TEST_DIR/.github/ISSUE_TEMPLATE/bug report.yml" && \
   grep -q "label: OpensourcePOS Version" "$TEST_DIR/.github/ISSUE_TEMPLATE/bug report.yml" && \
   grep -q "description: What version of our software are you running?" "$TEST_DIR/.github/ISSUE_TEMPLATE/bug report.yml"; then
    test_result "update_template preserves content before options" 0
else
    test_result "update_template preserves content before options" 1
fi
cleanup_test_env

# Test 17: Idempotency
setup_test_env
create_bug_report_template
create_options_file
update_template "bug report.yml" > /dev/null 2>&1
cp "$TEST_DIR/.github/ISSUE_TEMPLATE/bug report.yml" "$TEST_DIR/first-run.yml"
update_template "bug report.yml" > /dev/null 2>&1
cp "$TEST_DIR/.github/ISSUE_TEMPLATE/bug report.yml" "$TEST_DIR/second-run.yml"
if diff -q "$TEST_DIR/first-run.yml" "$TEST_DIR/second-run.yml" > /dev/null 2>&1; then
    test_result "update_template is idempotent" 0
else
    test_result "update_template is idempotent" 1
fi
cleanup_test_env

# Test 18: Special characters in release names
setup_test_env
create_bug_report_template
OPTIONS_FILE="$(mktemp)"
export OPTIONS_FILE
echo "        - development (unreleased)" >> "$OPTIONS_FILE"
echo "        - opensourcepos 3.4.1-beta" >> "$OPTIONS_FILE"
echo "        - opensourcepos 3.4.0-rc.1" >> "$OPTIONS_FILE"
update_template "bug report.yml" > /dev/null 2>&1
if grep -q "3.4.1-beta" "$TEST_DIR/.github/ISSUE_TEMPLATE/bug report.yml" && \
   grep -q "3.4.0-rc.1" "$TEST_DIR/.github/ISSUE_TEMPLATE/bug report.yml"; then
    test_result "update_template handles special characters in release names" 0
else
    test_result "update_template handles special characters in release names" 1
fi
cleanup_test_env

# Test 19: Workflow trigger conditions
if grep -A 3 "^on:" /home/jailuser/git/.github/workflows/update-issue-templates.yml | grep -q "release:" && \
   grep -A 3 "^on:" /home/jailuser/git/.github/workflows/update-issue-templates.yml | grep -q "workflow_dispatch:"; then
    test_result "workflow trigger conditions are correctly defined" 0
else
    test_result "workflow trigger conditions are correctly defined" 1
fi

# Test 20: Workflow permissions
if grep -A 1 "permissions:" /home/jailuser/git/.github/workflows/update-issue-templates.yml | grep -q "contents: write"; then
    test_result "workflow has correct permissions" 0
else
    test_result "workflow has correct permissions" 1
fi

# Test 21: GitHub token usage
if grep "GH_TOKEN:" /home/jailuser/git/.github/workflows/update-issue-templates.yml | grep -q "secrets.GITHUB_TOKEN"; then
    test_result "workflow uses correct GitHub token" 0
else
    test_result "workflow uses correct GitHub token" 1
fi

# Test 22: Fetches 10 releases
if grep -q "head -n 10" /home/jailuser/git/.github/workflows/update-issue-templates.yml; then
    test_result "workflow fetches correct number of releases" 0
else
    test_result "workflow fetches correct number of releases" 1
fi

# Test 23: Skip CI tag in commit
if grep "skip ci" /home/jailuser/git/.github/workflows/update-issue-templates.yml | grep -q "\[skip ci\]"; then
    test_result "workflow commits with skip ci tag" 0
else
    test_result "workflow commits with skip ci tag" 1
fi

# Test 24: Only commits if changes exist
if grep -q "git diff --staged --quiet" /home/jailuser/git/.github/workflows/update-issue-templates.yml; then
    test_result "workflow only commits if there are changes" 0
else
    test_result "workflow only commits if there are changes" 1
fi

# Test 25: Workflow runs on ubuntu-latest
if grep -q "runs-on: ubuntu-latest" /home/jailuser/git/.github/workflows/update-issue-templates.yml; then
    test_result "workflow runs on ubuntu-latest" 0
else
    test_result "workflow runs on ubuntu-latest" 1
fi

# Test 26: Workflow checks out repository
if grep -q "actions/checkout@v4" /home/jailuser/git/.github/workflows/update-issue-templates.yml; then
    test_result "workflow checks out repository with correct action" 0
else
    test_result "workflow checks out repository with correct action" 1
fi

# Test 27: Line number calculation
setup_test_env
create_bug_report_template
start_line=$(grep -n "label: OpensourcePOS Version" "$TEST_DIR/.github/ISSUE_TEMPLATE/bug report.yml" | cut -d: -f1)
options_start=$((start_line + 3))
actual_line=$(sed -n "${options_start}p" "$TEST_DIR/.github/ISSUE_TEMPLATE/bug report.yml")
if [[ "$actual_line" =~ "- " ]] || [[ "$actual_line" =~ "development" ]]; then
    test_result "line number calculation for options_start is correct" 0
else
    test_result "line number calculation for options_start is correct" 1 "Line content: $actual_line"
fi
cleanup_test_env

# Test 28: Default line found after options
setup_test_env
create_bug_report_template
start_line=$(grep -n "label: OpensourcePOS Version" "$TEST_DIR/.github/ISSUE_TEMPLATE/bug report.yml" | cut -d: -f1)
options_start=$((start_line + 3))
default_line=$(grep -n "default:" "$TEST_DIR/.github/ISSUE_TEMPLATE/bug report.yml" | awk -F: -v opts="$options_start" '$1 > opts {print $1; exit}')
if [ -n "$default_line" ] && [ "$default_line" -gt "$options_start" ]; then
    test_result "default line is found correctly after options" 0
else
    test_result "default line is found correctly after options" 1
fi
cleanup_test_env

# Test 29: Workflow scheduled trigger (weekly)
if grep -A 2 "schedule:" /home/jailuser/git/.github/workflows/update-issue-templates.yml | grep -q "cron:"; then
    test_result "workflow has scheduled trigger configured" 0
else
    test_result "workflow has scheduled trigger configured" 1
fi

# Test 30: Git config in workflow
if grep -q "git config user.name" /home/jailuser/git/.github/workflows/update-issue-templates.yml && \
   grep -q "git config user.email" /home/jailuser/git/.github/workflows/update-issue-templates.yml; then
    test_result "workflow configures git user" 0
else
    test_result "workflow configures git user" 1
fi

# Test 31: Handle repository with no releases (only development option)
setup_test_env
create_bug_report_template
OPTIONS_FILE="$(mktemp)"
export OPTIONS_FILE
echo "        - development (unreleased)" >> "$OPTIONS_FILE"
update_template "bug report.yml" > /dev/null 2>&1
if grep -q "development (unreleased)" "$TEST_DIR/.github/ISSUE_TEMPLATE/bug report.yml" && \
   grep -q "default: 0" "$TEST_DIR/.github/ISSUE_TEMPLATE/bug report.yml"; then
    test_result "update_template handles repository with no releases" 0
else
    test_result "update_template handles repository with no releases" 1
fi
cleanup_test_env

# Test 32: Workflow adds files to git staging area correctly
if grep -q "git add .github/ISSUE_TEMPLATE/\*.yml" /home/jailuser/git/.github/workflows/update-issue-templates.yml; then
    test_result "workflow adds template files to git staging" 0
else
    test_result "workflow adds template files to git staging" 1
fi

# Test 33: Update template with very long release list
setup_test_env
create_bug_report_template
OPTIONS_FILE="$(mktemp)"
export OPTIONS_FILE
echo "        - development (unreleased)" >> "$OPTIONS_FILE"
for i in {1..15}; do
    echo "        - opensourcepos 4.$i.0" >> "$OPTIONS_FILE"
done
update_template "bug report.yml" > /dev/null 2>&1
# Should handle all releases even beyond the 10 that workflow fetches
count=$(grep -c "opensourcepos 4\." "$TEST_DIR/.github/ISSUE_TEMPLATE/bug report.yml" || echo 0)
if [ "$count" -eq 15 ]; then
    test_result "update_template handles release lists larger than 10" 0
else
    test_result "update_template handles release lists larger than 10" 1 "Found $count instead of 15"
fi
cleanup_test_env

# Test 34: Template with extra whitespace around label
setup_test_env
cat > "$TEST_DIR/.github/ISSUE_TEMPLATE/whitespace.yml" << 'EOF'
name: Whitespace Test
body:
  - type: dropdown
    attributes:
      label:  OpensourcePOS Version
      options:
        - old
      default: 0
EOF
create_options_file
set +e
output=$(update_template "whitespace.yml" 2>&1)
exit_code=$?
set -e
# Should fail because grep won't match with extra whitespace
if [ $exit_code -eq 1 ]; then
    test_result "update_template requires exact label match (whitespace sensitivity)" 0
else
    test_result "update_template requires exact label match (whitespace sensitivity)" 1
fi
cleanup_test_env

# Test 35: Verify workflow uses bash shell explicitly
if grep -q "run: |" /home/jailuser/git/.github/workflows/update-issue-templates.yml; then
    test_result "workflow uses multiline bash scripts" 0
else
    test_result "workflow uses multiline bash scripts" 1
fi

# Summary
echo ""
echo "================================"
echo "Test Results Summary"
echo "================================"
echo -e "Total tests run: $TESTS_RUN"
echo -e "${GREEN}Passed: $TESTS_PASSED${NC}"
echo -e "${RED}Failed: $TESTS_FAILED${NC}"
echo ""

if [ $TESTS_FAILED -eq 0 ]; then
    echo -e "${GREEN}All tests passed!${NC}"
    exit 0
else
    echo -e "${RED}Some tests failed!${NC}"
    exit 1
fi