<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
                xmlns:sitemap="http://www.sitemaps.org/schemas/sitemap/0.9"
                version="1.0">
    <xsl:output method="html" />

    <xsl:template match="/sitemap:urlset">
        <html>
        <head>
            <title>Sitemap</title>
            <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&amp;display=swap" rel="stylesheet" />
            <style>
                body{
                    font-family: "Open Sans", sans-serif;
                    color: #545353;
                    font-size: 14px;
                }
                #description {
                    background-color: #4275f4;
                    padding: 20px 40px;
                    color: #fff;
                    padding: 30px 30px 20px;
                }
                #description h1, #description p, #description a {
                    color: #fff;
                    margin: 0;
                    font-size: 1.1em;
                }
                #description h1 {
                    font-size: 2em;
                    margin-bottom: 1em;
                }
                #description p {
                    margin-top: 5px;
                }
                #content {
                    padding: 20px 30px;
                    background: #fff;
                    max-width: 75%;
                    margin: 0 auto;
                }
                table {
                    border: none;
                    border-collapse: collapse;
                    font-size: .9em;
                    width: 100%;
                }
                th {
                    background-color: #4275f4;
                    color: #fff;
                    text-align: left;
                    padding: 15px 10px;
                    font-size: 14px;
                    cursor: pointer;
                }
                td {
                    padding: 10px;
                    border-bottom: 1px solid #ddd;
                }
                table td a {
                    display: block;
                }
                td {
                    padding: 10px;
                    border-bottom: 1px solid #ddd;
                }
                a {
                    color: #05809e;
                    text-decoration: none;
                }
                tbody tr:nth-child(even) {
                    background-color: #f7f7f7;
                }
            </style>
        </head>
        <body>
            <div id="description">
                <h1>XML-Sitemap</h1>
                <p>Diese XML-Sitemap ist das, was Suchmaschinen wie Google verwenden, um Beitragsseiten-Produkte-Bildarchive auf deiner Website zu durchsuchen und neu zu durchsuchen.</p>
                <p>Mehr über <a href="http://sitemaps.org" target="_blank">XML Sitemaps</a> erfahren.</p>
            </div>
            <div id="content">
                <p>Diese Indexdatei der XML-Sitemap enthält <strong><xsl:value-of select="count(sitemap:url)" /></strong> Sitemaps.</p>
                <table id="sitemap" cellpadding="3">
                    <thead>
                        <tr>
                            <th width="75%">Sitemap</th>
                            <th height="25%">Zuletzt geändert</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Use the sitemap prefix to handle the namespace -->
                        <xsl:for-each select="//sitemap:url">
                            <tr>
                                <td><a href="{sitemap:loc}"><xsl:value-of select="sitemap:loc" /></a></td>
                                <td><xsl:value-of select="sitemap:lastmod" /></td>
                            </tr>
                        </xsl:for-each>
                    </tbody>
                </table>
            </div>
            
        </body>
        </html>
    </xsl:template>
</xsl:stylesheet>
