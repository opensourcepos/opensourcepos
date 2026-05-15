<!-- START doctoc generated TOC please keep comment here to allow auto update -->
<!-- DON'T EDIT THIS SECTION, INSTEAD RE-RUN doctoc TO UPDATE -->

- [Security Policy](#security-policy)
  - [Supported Versions](#supported-versions)
  - [Security Advisories](#security-advisories)
  - [Reporting a Vulnerability](#reporting-a-vulnerability)
  - [Disclosure Process](#disclosure-process)

<!-- END doctoc generated TOC please keep comment here to allow auto update -->

# Security Policy

## Supported Versions

We release patches for security vulnerabilities.

| Version   | Supported          |
| --------- | ------------------ |
| >= 3.4.2  | :white_check_mark: |
| < 3.4.2   | :x:                |

## Security Advisories

The following security vulnerabilities have been published:

### High Severity

| CVE | Vulnerability | CVSS | Published | Fixed In | Credit |
|-----|--------------|------|-----------|----------|--------|
| [CVE-2026-41306](https://github.com/opensourcepos/opensourcepos/security/advisories/GHSA-2w4j-mm2p-g28q) | Blind SQL Injection in Tax Controllers | 7.4 | 2026-04-05 | 3.4.3 | @jiva |
| [CVE-2026-41307](https://github.com/opensourcepos/opensourcepos/security/advisories/GHSA-w2px-qm8j-jj26) | OS Command Injection via Sendmail Path | 9.8 | 2026-04-04 | 3.4.3 | @jiva |
| [CVE-2025-68434](https://github.com/opensourcepos/opensourcepos/security/advisories/GHSA-wjm4-hfwg-5w5r) | CSRF leading to Admin Creation | 8.8 | 2025-12-17 | 3.4.2 | @Nixon-H, @jekkos |
| [CVE-2025-68147](https://github.com/opensourcepos/opensourcepos/security/advisories/GHSA-xgr7-7pvw-fpmh) | Stored XSS in Return Policy | 8.1 | 2025-12-17 | 3.4.2 | @Nixon-H, @jekkos |
| [CVE-2025-66924](https://github.com/opensourcepos/opensourcepos/security/advisories/GHSA-gv8j-f6gq-g59m) | Stored XSS in Item Kits | 7.2 | 2026-03-04 | 3.4.2 | @hungnqdz, @omkaryepre |

### Medium Severity

| CVE | Vulnerability | CVSS | Published | Fixed In | Credit |
|-----|--------------|------|-----------|----------|--------|
| [CVE-2025-68658](https://github.com/opensourcepos/opensourcepos/security/advisories/GHSA-32r8-8r9r-9chw) | Stored XSS in Company Name | 4.3 | 2026-01-13 | 3.4.2 | @hungnqdz |

For a complete list including draft advisories, see our [GitHub Security Advisories page](https://github.com/opensourcepos/opensourcepos/security/advisories).

## Reporting a Vulnerability

**Option 1: GitHub Security Advisory (Preferred)**

1. Create a draft security advisory directly on GitHub:
   - Go to https://github.com/opensourcepos/opensourcepos/security/advisories
   - Click "New draft security advisory"
   - Fill in the vulnerability details using our [template below](#vulnerability-template)
   - Submit as **draft** (not published)

2. Notify us for triage:
   - Send an email to **[jeroen@steganos.dev](mailto:jeroen@steganos.dev)** with:
     - Subject: `[GHSA] Brief description of vulnerability`
     - Link to the draft advisory
     - Brief summary

**Option 2: Email Report**

Send vulnerability details to **[jeroen@steganos.dev](mailto:jeroen@steganos.dev)**.

You will receive a response within 48 hours. Confirmed vulnerabilities will be patched within a few days depending on complexity.

## Disclosure Process

### Timeline

| Step | Timeline | Action |
|------|----------|--------|
| 1. Report received | Day 0 | We acknowledge within 48 hours |
| 2. Triage & confirmation | Day 1-3 | We validate the vulnerability |
| 3. Fix development | Day 3-7 | We develop and test the fix |
| 4. Patch release | Day 7-10 | We release a security patch |
| 5. CVE request | Day 7-14 | We request CVE from GitHub (if applicable) |
| 6. Advisory published | Day 14 | We publish the advisory with credit |
| 7. Public disclosure | Day 14+ | Full disclosure after patch release |

### What to Expect

- **Acknowledgment**: We'll confirm receipt within 48 hours
- **Validation**: We'll verify the vulnerability within 72 hours
- **CVSS Assessment**: We'll provide a severity rating using CVSS 3.1
- **Credit**: We'll credit you in the published advisory (unless you prefer anonymity)
- **CVE**: For confirmed vulnerabilities, we'll request a CVE identifier through GitHub

### Vulnerability Template

When creating a draft advisory, please include:

```
## Summary
[Brief description of the vulnerability]

## Impact
- **Confidentiality:** [High/Medium/Low - what data can be exposed]
- **Integrity:** [High/Medium/Low - what can be modified]
- **Availability:** [High/Medium/Low - service disruption potential]
- **Privilege Required:** [None/Low/High - authentication level needed]
- **CVSS v3.1:** [Score] ([Vector string])

## Details
[Technical details about the vulnerability]

**Affected Code:**
```php
// Path to affected file and vulnerable code
```

**Attack Vector:**
[How an attacker can exploit this]

## Proof of Concept
```bash
# Steps to reproduce
```

## Patch
[Suggested fix or approach]

## Affected Versions
- OpenSourcePOS X.Y.Z and earlier

## Credit
[Your GitHub username or preferred name]
```

### Security Best Practices for Researchers

- **Do not** access, modify, or delete data that doesn't belong to you
- **Do not** perform denial of service attacks
- **Do** provide sufficient information to reproduce the vulnerability
- **Do** allow us reasonable time to fix before public disclosure
- **Do** report through official channels (GitHub advisories or email)

## Draft Advisory Status

We currently have [check GitHub for count](https://github.com/opensourcepos/opensourcepos/security/advisories) draft advisories under review. Each will be processed according to the disclosure timeline above.