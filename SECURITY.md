<!-- START doctoc generated TOC please keep comment here to allow auto update -->
<!-- DON'T EDIT THIS SECTION, INSTEAD RE-RUN doctoc TO UPDATE -->

- [Security Policy](#security-policy)
  - [Supported Versions](#supported-versions)
  - [Security Advisories](#security-advisories)
  - [Reporting a Vulnerability](#reporting-a-vulnerability)
  - [Disclosure Process](#disclosure-process)

<!-- END doctoc generated TOC please keep comment here to allow update -->

# Security Policy

## Supported Versions

We release patches for security vulnerabilities.

| Version   | Supported          |
| --------- | ------------------ |
| >= 3.4.2  | :white_check_mark: |
| < 3.4.2   | :x:                |

## Security Advisories

For a complete list of published and draft security advisories with CVE details, see our [GitHub Security Advisories page](https://github.com/opensourcepos/opensourcepos/security/advisories).

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

### CVE Process

**We request CVE identifiers through GitHub's security advisory system.** This is the preferred and easiest method:

1. After we confirm and fix the vulnerability, we'll request a CVE through GitHub
2. GitHub coordinates with MITRE on our behalf
3. The CVE is automatically linked to the advisory
4. You'll be credited as the reporter in the published advisory

**Already have a CVE?** If you've already obtained a CVE from another source (e.g., VulDB, CVE.MITRE.ORG), please include it in your report or advisory. We'll update our advisory to reference the existing CVE.

### No Bug Bounty Program

**Important:** Open Source Point of Sale does not offer a bug bounty program.

- All security research and vulnerability triage is done on a **voluntary basis** in our free time
- We do not offer monetary rewards for vulnerability reports
- We do credit reporters in published advisories (unless anonymity is requested)
- We greatly appreciate the security research community's efforts to help improve project security

### Security Best Practices for Researchers

- **Do not** access, modify, or delete data that doesn't belong to you
- **Do not** perform denial of service attacks
- **Do not** publicly disclose vulnerabilities before we've had time to fix them
- **Do** provide sufficient information to reproduce the vulnerability
- **Do** allow us reasonable time to fix before public disclosure
- **Do** report through official channels (GitHub advisories or email)

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

---

**Thank you to all security researchers who have contributed to making Open Source Point of Sale more secure.** Your voluntary efforts help protect thousands of users worldwide and contribute to a safer, more trustworthy free and open-source software ecosystem. We deeply appreciate your responsible disclosure and the time you invest in improving our project.

If you've reported a vulnerability and would like to discuss CVE coordination or have questions about the process, please reach out to us at [jeroen@steganos.dev](mailto:jeroen@steganos.dev).