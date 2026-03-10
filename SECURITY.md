<!-- START doctoc generated TOC please keep comment here to allow auto update -->
<!-- DON'T EDIT THIS SECTION, INSTEAD RE-RUN doctoc TO UPDATE -->

- [Security Policy](#security-policy)
  - [Supported Versions](#supported-versions)
  - [Security Advisories](#security-advisories)
  - [Reporting a Vulnerability](#reporting-a-vulnerability)

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
| [CVE-2025-68434](https://github.com/opensourcepos/opensourcepos/security/advisories/GHSA-wjm4-hfwg-5w5r) | CSRF leading to Admin Creation | 8.8 | 2025-12-17 | 3.4.2 | @Nixon-H, @jekkos |
| [CVE-2025-68147](https://github.com/opensourcepos/opensourcepos/security/advisories/GHSA-xgr7-7pvw-fpmh) | Stored XSS in Return Policy | 8.1 | 2025-12-17 | 3.4.2 | @Nixon-H, @jekkos |
| [CVE-2025-66924](https://github.com/opensourcepos/opensourcepos/security/advisories/GHSA-gv8j-f6gq-g59m) | Stored XSS in Item Kits | 7.2 | 2026-03-04 | 3.4.2 | @hungnqdz, @omkaryepre |

### Medium Severity

| CVE | Vulnerability | CVSS | Published | Fixed In | Credit |
|-----|--------------|------|-----------|----------|--------|
| [CVE-2025-68658](https://github.com/opensourcepos/opensourcepos/security/advisories/GHSA-32r8-8r9r-9chw) | Stored XSS in Company Name | 4.3 | 2026-01-13 | 3.4.2 | @hungnqdz |

For a complete list including draft advisories, see our [GitHub Security Advisories page](https://github.com/opensourcepos/opensourcepos/security/advisories).

## Reporting a Vulnerability

Please report (suspected) security vulnerabilities to **[jeroen@steganos.dev](mailto:jeroen@steganos.dev)**. 

You will receive a response from us within 48 hours. If the issue is confirmed, we will release a patch as soon as possible depending on complexity but historically within a few days.