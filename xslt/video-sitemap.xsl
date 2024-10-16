<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:sitemap="http://www.sitemaps.org/schemas/sitemap/0.9"
    xmlns:video="http://www.google.com/schemas/sitemap-video/1.1">

  <!-- Output as HTML -->
  <xsl:output method="html" indent="yes"/>

  <!-- Template for the root element -->
  <xsl:template match="/sitemap:urlset">
    <html>
      <head>
        <title>Video Sitemap</title>
        <style>
          body {
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
            <h1>XML-Video Sitemap</h1>
            <p>This XML Sitemap is used by search engines like Google to crawl video content on your website.</p>
            <p>Learn more about <a href="http://sitemaps.org" target="_blank">XML Sitemaps</a>.</p>
        </div>
        <div id="content">
         <p>Diese Indexdatei der XML-Sitemap enthält <strong><xsl:value-of select="count(sitemap:url)" /></strong> Sitemaps.</p>
          <table id="sitemap" cellpadding="3">
            <thead>
              <tr>
                <th>Post URL</th>
                <th>Video URL</th>
                <th>Title</th>
              </tr>
            </thead>
            <tbody>
              <!-- Loop through each <url> element -->
              <xsl:for-each select="//sitemap:url">
                <tr>
                  <!-- Display the post URL (loc element) -->
                  <td>
                    <a href="{sitemap:loc}">
                      <xsl:value-of select="sitemap:loc"/>
                    </a>
                  </td>

                  <!-- Loop through each video element inside the URL -->
                  <xsl:for-each select="video:video">
                    <td>
                      <!-- Display video content location -->
                      <a href="{video:content_loc}">
                        <xsl:value-of select="video:content_loc"/>
                      </a>
                    </td>

                    <!-- Display the video title -->
                    <td>
                      <xsl:value-of select="video:title"/>
                    </td>

                  </xsl:for-each>
                </tr>
              </xsl:for-each>
            </tbody>
          </table>
        </div>
      </body>
    </html>
  </xsl:template>
</xsl:stylesheet>
