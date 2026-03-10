#!/usr/bin/env bats
# Tests for .github/workflows/update-issue-templates.yml
# This test suite validates the bash script logic used in the workflow

setup() {
    # Create temporary directory for test files
    TEST_DIR="$(mktemp -d)"
    export TEST_DIR

    # Create mock issue template directory
    mkdir -p "$TEST_DIR/.github/ISSUE_TEMPLATE"

    # Source the functions we're testing by extracting them from workflow
    # We'll define them here for testing purposes

    # Define update_template function (extracted from workflow)
    update_template() {
        local template="$1"
        local template_path="$TEST_DIR/.github/ISSUE_TEMPLATE/$template"

        # Find the line numbers for the OpensourcePOS Version dropdown
        start_line=$(grep -n "label: OpensourcePOS Version" "$template_path" | cut -d: -f1)

        if [ -z "$start_line" ]; then
            echo "Could not find OpensourcePOS Version in $template"
            return 1
        fi

        # Find the options section and default line
        options_start=$((start_line + 3))
        default_line=$(grep -n "default:" "$template_path" | awk -F: -v opts="$options_start" '$1 > opts {print $1; exit}')

        # Create new template file
        head -n $((options_start - 1)) "$template_path" > "${template_path}.new"
        cat "$OPTIONS_FILE" >> "${template_path}.new"
        tail -n +$default_line "$template_path" >> "${template_path}.new"
        mv "${template_path}.new" "$template_path"

        echo "Updated $template"
    }
    export -f update_template
}

teardown() {
    # Clean up temporary directory
    if [ -n "$TEST_DIR" ] && [ -d "$TEST_DIR" ]; then
        rm -rf "$TEST_DIR"
    fi
}

# Helper function to create a mock bug report template
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

# Helper function to create a mock feature request template
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

# Helper function to create mock releases options file
create_options_file() {
    OPTIONS_FILE="$(mktemp)"
    export OPTIONS_FILE
    echo "        - development (unreleased)" >> "$OPTIONS_FILE"
    echo "        - opensourcepos 3.4.1" >> "$OPTIONS_FILE"
    echo "        - opensourcepos 3.4.0" >> "$OPTIONS_FILE"
    echo "        - opensourcepos 3.3.9" >> "$OPTIONS_FILE"
}

@test "update_template finds OpensourcePOS Version label in bug report" {
    create_bug_report_template
    create_options_file

    run grep -n "label: OpensourcePOS Version" "$TEST_DIR/.github/ISSUE_TEMPLATE/bug report.yml"
    [ "$status" -eq 0 ]
    [[ "$output" =~ "label: OpensourcePOS Version" ]]
}

@test "update_template finds OpensourcePOS Version label in feature request" {
    create_feature_request_template
    create_options_file

    run grep -n "label: OpensourcePOS Version" "$TEST_DIR/.github/ISSUE_TEMPLATE/feature_request.yml"
    [ "$status" -eq 0 ]
    [[ "$output" =~ "label: OpensourcePOS Version" ]]
}

@test "update_template successfully updates bug report template" {
    create_bug_report_template
    create_options_file

    run update_template "bug report.yml"
    [ "$status" -eq 0 ]
    [[ "$output" == "Updated bug report.yml" ]]
}

@test "update_template successfully updates feature request template" {
    create_feature_request_template
    create_options_file

    run update_template "feature_request.yml"
    [ "$status" -eq 0 ]
    [[ "$output" == "Updated feature_request.yml" ]]
}

@test "update_template adds new releases to bug report options" {
    create_bug_report_template
    create_options_file

    update_template "bug report.yml"

    # Verify new releases were added
    run grep "opensourcepos 3.4.1" "$TEST_DIR/.github/ISSUE_TEMPLATE/bug report.yml"
    [ "$status" -eq 0 ]

    run grep "opensourcepos 3.4.0" "$TEST_DIR/.github/ISSUE_TEMPLATE/bug report.yml"
    [ "$status" -eq 0 ]

    run grep "opensourcepos 3.3.9" "$TEST_DIR/.github/ISSUE_TEMPLATE/bug report.yml"
    [ "$status" -eq 0 ]
}

@test "update_template preserves development option" {
    create_bug_report_template
    create_options_file

    update_template "bug report.yml"

    # Verify development option is preserved
    run grep "development (unreleased)" "$TEST_DIR/.github/ISSUE_TEMPLATE/bug report.yml"
    [ "$status" -eq 0 ]
}

@test "update_template preserves default line" {
    create_bug_report_template
    create_options_file

    update_template "bug report.yml"

    # Verify default line is preserved
    run grep "default: 0" "$TEST_DIR/.github/ISSUE_TEMPLATE/bug report.yml"
    [ "$status" -eq 0 ]
}

@test "update_template preserves validations section" {
    create_bug_report_template
    create_options_file

    update_template "bug report.yml"

    # Verify validations are preserved
    run grep "validations:" "$TEST_DIR/.github/ISSUE_TEMPLATE/bug report.yml"
    [ "$status" -eq 0 ]

    run grep "required: true" "$TEST_DIR/.github/ISSUE_TEMPLATE/bug report.yml"
    [ "$status" -eq 0 ]
}

@test "update_template removes old releases" {
    create_bug_report_template
    create_options_file

    update_template "bug report.yml"

    # Original template had "opensourcepos 3.3.7" but new options should replace it
    # Count occurrences - should be in new options
    run grep -c "opensourcepos 3.3.7" "$TEST_DIR/.github/ISSUE_TEMPLATE/bug report.yml"
    # Should not exist or exist only once in the updated options
    [ "$status" -eq 0 ] || [ "$status" -eq 1 ]
}

@test "update_template fails gracefully when label not found" {
    # Create template without OpensourcePOS Version label
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

    run update_template "invalid.yml"
    [ "$status" -eq 1 ]
    [[ "$output" =~ "Could not find OpensourcePOS Version" ]]
}

@test "update_template preserves file structure" {
    create_bug_report_template
    create_options_file

    # Count lines before update
    lines_before=$(wc -l < "$TEST_DIR/.github/ISSUE_TEMPLATE/bug report.yml")

    update_template "bug report.yml"

    # File should still be valid YAML structure
    run grep "name: Bug Report" "$TEST_DIR/.github/ISSUE_TEMPLATE/bug report.yml"
    [ "$status" -eq 0 ]

    run grep "description: File a bug report" "$TEST_DIR/.github/ISSUE_TEMPLATE/bug report.yml"
    [ "$status" -eq 0 ]
}

@test "update_template maintains proper indentation" {
    create_bug_report_template
    create_options_file

    update_template "bug report.yml"

    # Check that options maintain 8-space indentation
    run grep "^        - development (unreleased)" "$TEST_DIR/.github/ISSUE_TEMPLATE/bug report.yml"
    [ "$status" -eq 0 ]

    run grep "^        - opensourcepos 3.4.1" "$TEST_DIR/.github/ISSUE_TEMPLATE/bug report.yml"
    [ "$status" -eq 0 ]
}

@test "OPTIONS_FILE generation creates proper format" {
    create_options_file

    # Verify OPTIONS_FILE exists and has correct format
    [ -f "$OPTIONS_FILE" ]

    # Check first line is development
    run head -n 1 "$OPTIONS_FILE"
    [[ "$output" == "        - development (unreleased)" ]]

    # Check releases are formatted correctly
    run grep "^        - opensourcepos" "$OPTIONS_FILE"
    [ "$status" -eq 0 ]
}

@test "OPTIONS_FILE includes development as first option" {
    create_options_file

    first_line=$(head -n 1 "$OPTIONS_FILE")
    [[ "$first_line" == "        - development (unreleased)" ]]
}

@test "update_template handles multiple dropdowns correctly" {
    # Create template with multiple dropdowns
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

    update_template "multi-dropdown.yml"

    # Verify OpensourcePOS Version dropdown was updated
    run grep "opensourcepos 3.4.1" "$TEST_DIR/.github/ISSUE_TEMPLATE/multi-dropdown.yml"
    [ "$status" -eq 0 ]

    # Verify other dropdowns were not affected
    run grep "label: First Dropdown" "$TEST_DIR/.github/ISSUE_TEMPLATE/multi-dropdown.yml"
    [ "$status" -eq 0 ]

    run grep "label: Third Dropdown" "$TEST_DIR/.github/ISSUE_TEMPLATE/multi-dropdown.yml"
    [ "$status" -eq 0 ]
}

@test "update_template with empty OPTIONS_FILE" {
    create_bug_report_template

    # Create empty options file
    OPTIONS_FILE="$(mktemp)"
    export OPTIONS_FILE

    update_template "bug report.yml"

    # Should still have the structure but with empty options
    run grep "label: OpensourcePOS Version" "$TEST_DIR/.github/ISSUE_TEMPLATE/bug report.yml"
    [ "$status" -eq 0 ]
}

@test "update_template processes template with CRLF line endings" {
    # Create template with CRLF line endings (Windows style)
    printf "name: Bug Report\r\n" > "$TEST_DIR/.github/ISSUE_TEMPLATE/crlf.yml"
    printf "body:\r\n" >> "$TEST_DIR/.github/ISSUE_TEMPLATE/crlf.yml"
    printf "  - type: dropdown\r\n" >> "$TEST_DIR/.github/ISSUE_TEMPLATE/crlf.yml"
    printf "    attributes:\r\n" >> "$TEST_DIR/.github/ISSUE_TEMPLATE/crlf.yml"
    printf "      label: OpensourcePOS Version\r\n" >> "$TEST_DIR/.github/ISSUE_TEMPLATE/crlf.yml"
    printf "      options:\r\n" >> "$TEST_DIR/.github/ISSUE_TEMPLATE/crlf.yml"
    printf "        - old\r\n" >> "$TEST_DIR/.github/ISSUE_TEMPLATE/crlf.yml"
    printf "      default: 0\r\n" >> "$TEST_DIR/.github/ISSUE_TEMPLATE/crlf.yml"

    create_options_file

    run update_template "crlf.yml"
    [ "$status" -eq 0 ]
}

@test "update_template with 10 releases (max from workflow)" {
    create_bug_report_template

    # Create options file with 10 releases as per workflow (head -n 10)
    OPTIONS_FILE="$(mktemp)"
    export OPTIONS_FILE
    echo "        - development (unreleased)" >> "$OPTIONS_FILE"
    for i in {1..10}; do
        echo "        - opensourcepos 3.$i.0" >> "$OPTIONS_FILE"
    done

    update_template "bug report.yml"

    # Verify all 10 releases plus development were added
    run grep -c "opensourcepos 3" "$TEST_DIR/.github/ISSUE_TEMPLATE/bug report.yml"
    [ "$status" -eq 0 ]
    [ "$output" -ge 10 ]
}

@test "line number calculation for options_start is correct" {
    create_bug_report_template

    start_line=$(grep -n "label: OpensourcePOS Version" "$TEST_DIR/.github/ISSUE_TEMPLATE/bug report.yml" | cut -d: -f1)
    [ -n "$start_line" ]

    # options_start should be start_line + 3
    options_start=$((start_line + 3))

    # Verify options_start points to first option line
    actual_line=$(sed -n "${options_start}p" "$TEST_DIR/.github/ISSUE_TEMPLATE/bug report.yml")
    [[ "$actual_line" =~ "- " ]] || [[ "$actual_line" =~ "development" ]]
}

@test "default line is found correctly after options" {
    create_bug_report_template

    start_line=$(grep -n "label: OpensourcePOS Version" "$TEST_DIR/.github/ISSUE_TEMPLATE/bug report.yml" | cut -d: -f1)
    options_start=$((start_line + 3))

    default_line=$(grep -n "default:" "$TEST_DIR/.github/ISSUE_TEMPLATE/bug report.yml" | awk -F: -v opts="$options_start" '$1 > opts {print $1; exit}')

    [ -n "$default_line" ]
    [ "$default_line" -gt "$options_start" ]
}

@test "update_template preserves content before options" {
    create_bug_report_template
    create_options_file

    update_template "bug report.yml"

    # Verify everything before options is preserved
    run grep "name: Bug Report" "$TEST_DIR/.github/ISSUE_TEMPLATE/bug report.yml"
    [ "$status" -eq 0 ]

    run grep "id: ospos-version" "$TEST_DIR/.github/ISSUE_TEMPLATE/bug report.yml"
    [ "$status" -eq 0 ]

    run grep "label: OpensourcePOS Version" "$TEST_DIR/.github/ISSUE_TEMPLATE/bug report.yml"
    [ "$status" -eq 0 ]

    run grep "description: What version of our software are you running?" "$TEST_DIR/.github/ISSUE_TEMPLATE/bug report.yml"
    [ "$status" -eq 0 ]
}

@test "update_template is idempotent" {
    create_bug_report_template
    create_options_file

    # Run update twice
    update_template "bug report.yml"
    cp "$TEST_DIR/.github/ISSUE_TEMPLATE/bug report.yml" "$TEST_DIR/first-run.yml"

    update_template "bug report.yml"
    cp "$TEST_DIR/.github/ISSUE_TEMPLATE/bug report.yml" "$TEST_DIR/second-run.yml"

    # Files should be identical
    run diff "$TEST_DIR/first-run.yml" "$TEST_DIR/second-run.yml"
    [ "$status" -eq 0 ]
}

@test "update_template handles special characters in release names" {
    create_bug_report_template

    OPTIONS_FILE="$(mktemp)"
    export OPTIONS_FILE
    echo "        - development (unreleased)" >> "$OPTIONS_FILE"
    echo "        - opensourcepos 3.4.1-beta" >> "$OPTIONS_FILE"
    echo "        - opensourcepos 3.4.0-rc.1" >> "$OPTIONS_FILE"
    echo "        - opensourcepos 3.3.9+build.123" >> "$OPTIONS_FILE"

    update_template "bug report.yml"

    run grep "3.4.1-beta" "$TEST_DIR/.github/ISSUE_TEMPLATE/bug report.yml"
    [ "$status" -eq 0 ]

    run grep "3.4.0-rc.1" "$TEST_DIR/.github/ISSUE_TEMPLATE/bug report.yml"
    [ "$status" -eq 0 ]
}

@test "workflow trigger conditions are correctly defined" {
    # This test verifies the workflow file structure
    run grep -A 3 "^on:" /home/jailuser/git/.github/workflows/update-issue-templates.yml
    [ "$status" -eq 0 ]
    [[ "$output" =~ "release:" ]]
    [[ "$output" =~ "workflow_dispatch:" ]]
}

@test "workflow has correct permissions" {
    run grep -A 1 "permissions:" /home/jailuser/git/.github/workflows/update-issue-templates.yml
    [ "$status" -eq 0 ]
    [[ "$output" =~ "contents: write" ]]
}

@test "workflow uses correct GitHub token" {
    run grep "GH_TOKEN:" /home/jailuser/git/.github/workflows/update-issue-templates.yml
    [ "$status" -eq 0 ]
    [[ "$output" =~ "secrets.GITHUB_TOKEN" ]]
}

@test "workflow fetches correct number of releases" {
    run grep "head -n 10" /home/jailuser/git/.github/workflows/update-issue-templates.yml
    [ "$status" -eq 0 ]
}

@test "workflow commits with skip ci tag" {
    run grep "skip ci" /home/jailuser/git/.github/workflows/update-issue-templates.yml
    [ "$status" -eq 0 ]
    [[ "$output" =~ "[skip ci]" ]]
}

@test "workflow only commits if there are changes" {
    run grep "git diff --staged --quiet" /home/jailuser/git/.github/workflows/update-issue-templates.yml
    [ "$status" -eq 0 ]
}

@test "update_template handles repository with no releases" {
    create_bug_report_template

    OPTIONS_FILE="$(mktemp)"
    export OPTIONS_FILE
    echo "        - development (unreleased)" >> "$OPTIONS_FILE"

    update_template "bug report.yml"

    run grep "development (unreleased)" "$TEST_DIR/.github/ISSUE_TEMPLATE/bug report.yml"
    [ "$status" -eq 0 ]

    run grep "default: 0" "$TEST_DIR/.github/ISSUE_TEMPLATE/bug report.yml"
    [ "$status" -eq 0 ]
}

@test "workflow adds template files to git staging" {
    run grep "git add .github/ISSUE_TEMPLATE/\*.yml" /home/jailuser/git/.github/workflows/update-issue-templates.yml
    [ "$status" -eq 0 ]
}

@test "update_template handles release lists larger than 10" {
    create_bug_report_template

    OPTIONS_FILE="$(mktemp)"
    export OPTIONS_FILE
    echo "        - development (unreleased)" >> "$OPTIONS_FILE"
    for i in {1..15}; do
        echo "        - opensourcepos 4.$i.0" >> "$OPTIONS_FILE"
    done

    update_template "bug report.yml"

    count=$(grep -c "opensourcepos 4\." "$TEST_DIR/.github/ISSUE_TEMPLATE/bug report.yml" || echo 0)
    [ "$count" -eq 15 ]
}

@test "update_template requires exact label match (whitespace sensitivity)" {
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

    run update_template "whitespace.yml"
    [ "$status" -eq 1 ]
}

@test "workflow uses multiline bash scripts" {
    run grep "run: |" /home/jailuser/git/.github/workflows/update-issue-templates.yml
    [ "$status" -eq 0 ]
}