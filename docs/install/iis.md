# IIS Deployment (Windows Server)

Community guide — not officially supported. Everything except the IIS-specific
steps below follows [INSTALL.md](../INSTALL.md).

1. Point the IIS web root at the `<ospos-path>/public` directory directly.
2. Install the **URL Rewrite** module in IIS Manager.
3. Create a `web.config` file in the `public` directory with the rewrite rules
   (equivalent of the `.htaccess`):

```xml
<?xml version="1.0" encoding="UTF-8"?>
<configuration>
    <system.webServer>
        <rewrite>
            <rules>
                <rule name="Imported Rule 1" stopProcessing="true">
                    <match url="^(.*)$" ignoreCase="false" />
                    <conditions logicalGrouping="MatchAll">
                        <add input="{REQUEST_FILENAME}" matchType="IsFile" ignoreCase="false" negate="true" />
                        <add input="{REQUEST_FILENAME}" matchType="IsDirectory" ignoreCase="false" negate="true" />
                    </conditions>
                    <action type="Rewrite" url="index.php?/{R:1}" appendQueryString="true" />
                </rule>
            </rules>
        </rewrite>
    </system.webServer>
</configuration>
```

4. Follow the remaining steps in [INSTALL.md](../INSTALL.md) (`.env`,
   database, log in).
