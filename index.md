---
layout: default
title: Home
---

<p class="text-center"><img src="https://raw.githubusercontent.com/opensourcepos/opensourcepos/master/branding/emblem.svg" alt="Open Source Point of Sale Logo" width="auto" height="200"></p>
<h3 class="text-center">Open Source Point of Sale</h3>

<p class="text-center">
  <a href="#-introduction">Introduction</a> · <a href="#-live-demo">Demo</a> · <a href="#-installation">Installation</a> · 
  <a href="#-contributing">Contributing</a> · <a href="#-reporting-bugs">Bugs</a> · <a href="#-faq">FAQ</a> · 
  <a href="#-keep-the-machine-running">Donate</a> · <a href="#-license">License</a> · <a href="#-credits">Credits</a>
</p>

<p class="text-center">
<a href="https://app.travis-ci.com/opensourcepos/opensourcepos" target="_blank" rel="noopener"><img src="https://api.travis-ci.com/opensourcepos/opensourcepos.svg?branch=master" alt="Build Status"></a>
<a href="https://app.gitter.im/#/room/#opensourcepos_Lobby:gitter.im?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge" target="_blank" rel="noopener"><img src="https://badges.gitter.im/jekkos/opensourcepos.svg" alt="Join the chat at https://app.gitter.im"></a>
<a href="https://github.com/opensourcepos/opensourcepos/releases" target="_blank" rel="noopener"><img src="https://img.shields.io/github/v/release/opensourcepos/opensourcepos" alt="Project Version"></a>
<a href="https://translate.opensourcepos.org/engage/opensourcepos/?utm_source=widget" target="_blank" rel="noopener"><img src="https://translate.opensourcepos.org/widgets/opensourcepos/-/svg-badge.svg" alt="Translation Status"></a>
</p>

<br>
## 👋 Introduction
---

Open Source Point of Sale is a web-based point of sale system. The application is written in PHP, uses MySQL (or MariaDB) as the data storage back-end, and has a simple but intuitive user interface.

The latest `3.4` version is a complete overhaul of the original software. It uses CodeIgniter 4 as a framework and is based on Bootstrap 3 using Bootswatch themes. Along with improved functionality and security.

The features include:

- Stock management (items and kits with an extensible list of attributes)
- VAT, GST, customer, and multi tiers taxation
- Sale register with transactions logging
- Quotation and invoicing
- Expenses logging
- Cash up function
- Printing and emailing of receipts, invoices and quotations
- Barcode generation and printing
- Database of customers and suppliers
- Multiuser with permission control
- Reporting on sales, orders, expenses, inventory status and more
- Receivings
- Gift cards
- Rewards
- Restaurant tables
- Messaging (SMS)
- Multilanguage
- Selectable Bootstrap based UI theme with Bootswatch
- MailChimp integration
- Optional Google reCAPTCHA to protect the login page from brute force attacks
- GDPR ready

<br>
## 🧪 Live Demo
---

We've got a live version of our latest master running for you to play around with and test everything out. It's a containerized install that will reinitialize when new functionality is merged into our code repository.

You can <a href="https://demo.opensourcepos.org/" target="_blank" rel="noopener">find the demo here</a> and log in with these credentials.  
👤 Username `admin`  
🔒 Password `pointofsale`

If you bump into an issue, please check <a href="https://status.opensourcepos.org/" target="_blank" rel="noopener">the status page here</a> to confirm if the server is up and running.

<br>
## 🖥️ Development Demo
---

Besides the demo of the latest master, we also have a development server that builds when there's a new commit to our repository. It's mainly used for testing out new code before merging it into the master. <a href="https://dev.opensourcepos.org/" target="_blank" rel="noopener">It can be found here</a>.

The log in credentials are the same as the regular live demo.

<br>
## 💾 Installation
---

Please **refrain from creating issues** about installation problems before having read the FAQ and going through existing GitHub issues. We have a build pipeline that checks the sanity of our latest repository commit, and in case the application itself is broken then our build will be as well.

This application can be set up in _many_ different ways and we only support the ones described in <a href="https://github.com/opensourcepos/opensourcepos/blob/master/INSTALL.md" target="_blank" rel="noopener">the INSTALL.md file</a>.

For more information and recommendations on support hardware, like receipt printers and barcode scanners, read <a href="https://github.com/opensourcepos/opensourcepos/wiki/Supported-hardware-datasheet" target="_blank" rel="noopener">this page</a> on our wiki.

<br>
## ✨ Contributing
---

Everyone is more than welcome to help us improve this project. If you think you've got something to help us go forward, feel free to open a <a href="https://github.com/opensourcepos/opensourcepos/pulls" target="_blank" rel="noopener">pull request</a> or join the conversation on <a href="https://app.gitter.im/#/room/#opensourcepos_Lobby:gitter.im" target="_blank" rel="noopener">Element</a>.

Want to help translate Open Source Point of Sale in your language? You can find <a href="https://translate.opensourcepos.org" target="_blank" rel="noopener">our Weblate here</a>, sign up, and start translating. You can subscribe to different languages to receive a notification once a new string is added or needs updating. Have a look at our <a href="https://github.com/opensourcepos/opensourcepos/wiki/Adding-translations" target="_blank" rel="noopener">guidelines</a> to help you get started.

Only with the help of the community, we can keep language translations up to date. Thanks!

<br>
## 🐛 Reporting Bugs
---

Before creating a new issue, you'll need copy and include the info under the `System Info` tab in the configuration section in most cases. If that information is not provided in full, your issue might be tagged as pending.

If you're reporting a potential security issue, please refer to our security policy found in the <a href="https://github.com/opensourcepos/opensourcepos/blob/master/SECURITY.md" target="_blank" rel="noopener">SECURITY.md</a> file.

NOTE: If you're running non-release code, please make sure you always run the latest database upgrade script and download the latest master code.

<br>
## 📖 FAQ
---

- If you get the message `system folder missing`, then you have cloned the source using git and you need to run a build first. Check <a href="https://github.com/opensourcepos/opensourcepos/blob/master/INSTALL.md" target="_blank" rel="noopener">INSTALL.md</a> for instructions or download latest zip file from <a href="https://github.com/opensourcepos/opensourcepos/releases" target="_blank" rel="noopener">GitHub releases</a> instead.

- If at login time you read `The installation is not correct, check your php.ini file.`, please check the error_log in `public` folder to understand what's wrong and make sure you read the <a href="https://github.com/opensourcepos/opensourcepos/blob/master/INSTALL.md" target="_blank" rel="noopener">INSTALL.md</a>. To know how to enable `error_log`, please read the comment in <a href="https://github.com/opensourcepos/opensourcepos/issues/1770#issuecomment-355177943" target="_blank" rel="noopener">issue #1770</a>.

- If you installed your OSPOS under a web server subdir, please edit `public/.htaccess` and go to the lines with the comments `if in web root` or `if in subdir`, uncomment one and replace `<OSPOS path>` with your path, and follow the instruction on the second comment line. If you face more issues, please read <a href="https://github.com/opensourcepos/opensourcepos/issues/920" target="_blank" rel="noopener">issue #920</a> for more information.

- Apache server configurations are SysAdmin issues and not strictly related to OSPOS. Please make sure you can show a "Hello world" HTML page before pointing to OSPOS public directory. Make sure `.htaccess` is correctly configured.

- If the avatar pictures are not shown in items or at item save you get an error, please make sure your `public` and subdirs are assigned to the correct owner and the access permission is set to `750`.

- If you install OSPOS in Docker behind a proxy that performs `ssloffloading`, you can enable the URL generated to be HTTPS instead of HTTP, by activating the environment variable `FORCE_HTTPS = 1`.

- If you install OSPOS behind a proxy and OSPOS constantly drops your session, consider whitelisting the proxy IP address by setting `$config['proxy_ips'] = '<proxy ip>';` in the <a href="https://github.com/opensourcepos/opensourcepos/blob/master/app/Config/App.php" target="_blank" rel="noopener">main PHP config file</a>. In extreme instances, changing `$config['sess_match_ip'] = true;` to `FALSE` may also help.

- If you have suhosin installed and face an issue with CSRF, please make sure you read <a href="https://github.com/opensourcepos/opensourcepos/issues/1492" target="_blank" rel="noopener">issue #1492</a>.

- PHP `≥ 8.1` is required to run this app.

<br>
## 🏃 Keep the Machine Running
---

If you like our project, please consider buying us a coffee through the button below so we can keep adding features.

<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=MUN6AEG7NY6H8" target="_blank" rel="noopener"><img src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif" alt="Donate button"></a>\
Or refer to the <a href="https://github.com/opensourcepos/opensourcepos/blob/master/.github/FUNDING.yml" target="_blank" rel="noopener">FUNDING.yml</a> file.

If you choose to deploy OSPOS in the cloud, you can contribute to the project by using DigitalOcean and signing up through our referral link. You'll receive a <a href="https://m.do.co/c/ac38c262507b" target="_blank" rel="noopener">free $200, 60-day credit</a> if you run OSPOS in a DigitalOcean droplet through <a href="https://m.do.co/c/ac38c262507b" target="_blank" rel="noopener">our referral link</a>.

<br>
## 📄 License
---

Open Source Point of Sale is licensed under MIT terms with an important addition:

The footer signature "© 2010 - _current year_ · opensourcepos.org · 3.x.x - _hash_" including the version, hash and link to our website MUST BE RETAINED, MUST BE VISIBLE IN EVERY PAGE and CANNOT BE MODIFIED.

Also worth noting:

_The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software._

For more details please read the <a href="https://github.com/opensourcepos/opensourcepos/blob/master/LICENSE" target="_blank" rel="noopener">LICENSE</a> file.

It's important to understand that although you are free to use the application, the copyright has to stay and the license agreement applies in all cases. Therefore, any actions like:

- Removing LICENSE and/or any license files is prohibited
- Authoring the footer notice replacing it with your own or even worse claiming the copyright is absolutely prohibited
- Claiming full ownership of the code is prohibited

In short, you are free to use the application, but you cannot claim any property on it.

Any person or company found breaching the license agreement might find a bunch of monkeys at the door ready to destroy their servers.

<br>
## 🙏 Credits
---

<table>
    <thead class="text-center">
        <tr>
            <th>JetBrains</th>
            <th>Travis CI</th>
        </tr>
    </thead>
    <tbody>
        <tr class="text-center">
            <td>
                <a href="https://www.jetbrains.com/idea/" target="_blank" rel="noopener">
                    <img src="https://github.com/opensourcepos/opensourcepos/assets/12870258/187f9bbe-4484-475c-9b58-5e5d5f931f09" alt="IntelliJ IDEA Logo" height="50">
                </a>
            </td>
            <td>
                <a href="https://www.travis-ci.com/" target="_blank" rel="noopener">
                    <img src="https://github.com/opensourcepos/opensourcepos/assets/12870258/71cc2b44-83af-4510-a543-6358285f43c6" alt="Travis CI Logo" height="50">
                </a>
            </td>
        </tr>
        <tr>
            <td>Many thanks to <a href="https://www.jetbrains.com/" target="_blank" rel="noopener">JetBrains</a> for providing a free license of <a href="https://www.jetbrains.com/idea/" target="_blank" rel="noopener">IntelliJ IDEA</a> to kindly support the development of OSPOS.</td>
            <td>Many thanks to <a href="https://www.travis-ci.com/" target="_blank" rel="noopener">Travis CI</a> for providing a free continuous integration service for open source projects.</td>
        </tr>
    </tbody>
</table>
<br>
