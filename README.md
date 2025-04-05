<p align="center"> <img src="https://raw.githubusercontent.com/opensourcepos/opensourcepos/master/branding/emblem.svg" alt="Open Source Point of Sale Logo" height="200"> </p> <h3 align="center">Open Source Point of Sale</h3> <p align="center"> <a href="#-introduction">Introduction</a> · <a href="#-live-demo">Live Demo</a> · <a href="#-installation">Installation</a> · <a href="#-contributing">Contributing</a> · <a href="#-reporting-bugs">Bug Reporting</a> · <a href="#-faq">FAQ</a> · <a href="#-keep-the-machine-running">Donate</a> · <a href="#-license">License</a> · <a href="#-credits">Credits</a> </p> <p align="center"> <a href="https://app.travis-ci.com/opensourcepos/opensourcepos"><img src="https://api.travis-ci.com/opensourcepos/opensourcepos.svg?branch=master" alt="Build Status"></a> <a href="https://app.gitter.im/#/room/#opensourcepos_Lobby:gitter.im"><img src="https://badges.gitter.im/jekkos/opensourcepos.svg" alt="Chat with Us"></a> <a href="https://badge.fury.io/gh/opensourcepos%2Fopensourcepos"><img src="https://badge.fury.io/gh/opensourcepos%2Fopensourcepos.svg" alt="Version"></a> <a href="https://translate.opensourcepos.org/engage/opensourcepos"><img src="https://translate.opensourcepos.org/widgets/opensourcepos/-/svg-badge.svg" alt="Translation Status"></a> </p>
👋 Introduction
Open Source Point of Sale (OSPOS) is a web-based POS system built with PHP and MySQL/MariaDB. It features a user-friendly interface and robust functionality.

Latest Version: 3.4

Built on CodeIgniter 4

Bootstrap 3 with Bootswatch themes

Enhanced security and features

Key Features
Inventory & Stock Management (items & kits with custom attributes)

Tax (VAT, GST, multi-tier), customer & supplier management

Sales register, invoicing, quotations, expenses

Receipts (print/email), barcode generation

Multi-user permissions, multilingual support, reporting

Gift cards, rewards, restaurant tables, SMS messaging

MailChimp, Google reCAPTCHA, GDPR compliance

Modern UI with theme selection

🧪 Live Demo
Try out the latest version here:

👤 Username: admin

🔒 Password: pointofsale

For service status, check the status page.

🖥️ Development Demo
Test in-progress features here using the same credentials.

💾 Installation
Before creating an issue, please:

Read the FAQ

Review existing GitHub issues

Follow instructions in INSTALL.md

💡 For compatible hardware (e.g. barcode scanners), check the supported hardware list.

✨ Contributing
We welcome contributions! 🚀

Open a pull request

Join our Gitter community

Help translate via Weblate

📝 Translation help: Guide

🐛 Reporting Bugs
To report bugs:

Include your full System Info (found in the config section).

Missing info may result in delays.

🔐 For security issues, see our SECURITY.md.

⚠️ Using unreleased code? Make sure you're running the latest upgrade script and master branch.

📖 FAQ
Missing system folder?
Run a build or download from GitHub Releases

Login issue (php.ini)?
Check public/error_log and INSTALL.md

Installing in subdirectory?
Edit .htaccess as described in Issue #920

Permission errors?
Ensure writable/ has proper ownership and 750 permissions

HTTPS behind proxy?
Set FORCE_HTTPS = 1 in environment

Session issues behind proxy?
Whitelist proxy IP in Config/App.php

CSRF with Suhosin?
See Issue #1492

Requires PHP 8.1 or higher

🏃 Keep the Machine Running
Like OSPOS? Buy us a ☕:



Or check FUNDING.yml

🌩️ Launching on DigitalOcean? Get a free $200 credit via our referral link

📄 License
Licensed under the MIT License with this requirement:

The footer signature “© 2010 - current year · opensourcepos.org · 3.x.x - hash” must remain visible and unmodified on all pages.

🔒 Don't:

Remove or alter license/footer

Claim code ownership

Omit attribution

🚫 Violations may result in unexpected monkey attacks 🐒💥

🙏 Credits
DigitalOcean	JetBrains	Travis CI
<img src="https://github.com/user-attachments/assets/fbbf7433-ed35-407d-8946-fd03d236d350" height="50">	<img src="https://github.com/opensourcepos/opensourcepos/assets/12870258/187f9bbe-4484-475c-9b58-5e5d5f931f09" height="50">	<img src="https://github.com/opensourcepos/opensourcepos/assets/12870258/71cc2b44-83af-4510-a543-6358285f43c6" height="50">
Special thanks to our partners for supporting OSPOS development ❤️