<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
                xmlns:sitemap="http://www.sitemaps.org/schemas/sitemap/0.9"
                version="1.0">
    <xsl:output method="html" />

    <xsl:template match="/">
        <html>
        <head>
            <title>Sitemap</title>
            <style>
                table {
                    width: 100%;
                    border-collapse: collapse;
                }
                table, th, td {
                    border: 1px solid #ddd;
                }
                th, td {
                    padding: 8px;
                    text-align: left;
                }
                th {
                    background-color: #ddd;
                }
            </style>
        </head>
        <body>
            <h1>Sitemap</h1>
            <table>
                <thead>
                    <tr>
                        <th>URL</th>
                        <th>Last Modified</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Use the sitemap prefix to handle the namespace -->
                    <xsl:for-each select="//sitemap:sitemap">
                        <tr>
                            <td><xsl:value-of select="sitemap:loc" /></td>
                            <td><xsl:value-of select="sitemap:lastmod" /></td>
                        </tr>
                    </xsl:for-each>
                </tbody>
            </table>
        </body>
        </html>
    </xsl:template>
</xsl:stylesheet>
