# Security Policy

This is an independent, unofficial open-source Laravel package and is not affiliated with Al Rajhi Bank.

## Supported Versions

| Version | Supported          |
| ------- | ------------------ |
| 2.x     | :white_check_mark: |
| 1.x     | :x:                |
| < 1.0   | :x:                |

Only the latest `2.x` releases receive security updates. The abandoned Packagist package `alrajhi/payment-gateway` is no longer maintained; use `yacoubalhaidari/alrajhi-payment-gateway` instead.

## Reporting a Vulnerability

If you discover a security vulnerability in this package, please report it privately.

**Do not open a public GitHub issue for security reports.**

### How to report

Email: **yacoub@yacoubalhaidari.com**

Please include:

- A clear description of the vulnerability
- Steps to reproduce
- Affected version(s) / commit hash if known
- Potential impact
- Any suggested fix (optional)

### What to expect

- Initial response within **7 days**
- Status update after triage (accepted, declined, or needs more info)
- A fix or mitigation plan for accepted reports, usually within **14–30 days** depending on severity

### Scope notes

- This package integrates with Al Rajhi Bank’s **Bank Hosted** payment flow only.
- Card data, passwords, and payment credentials must never be collected by merchant application pages using this package; customers enter sensitive data on the bank’s hosted payment page.
- Do not include real production credentials, live card data, or private merchant keys in your report. Use redacted or sandbox examples only.

Thank you for helping keep merchants and customers safe.
