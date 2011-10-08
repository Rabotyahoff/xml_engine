<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  <xsl:output method="html" version="1.0" omit-xml-declaration="no" indent="yes" encoding="utf-8"/>
  <xsl:decimal-format grouping-separator=" " NaN=""/>
	<xsl:param name="res_site_url"></xsl:param>
	<xsl:param name="res_engine_url"></xsl:param>
	<xsl:param name="current_url"></xsl:param>	
	
	<xsl:param name="crumbs_style"></xsl:param>	

  <xsl:template match="page_crumbs">
    <xsl:choose>
      <xsl:when test="count(crumbs/item)>1">
      	<div class="crumbs s11 black" style="{$crumbs_style}">
          <xsl:for-each select="crumbs/item">
            <xsl:if test="label!=''">
              <!-- ссылка -->
              <xsl:choose>              
                <xsl:when test="link!='' and position()!=last()">
                  <xsl:if test="position()>1">&#160;&#8594;&#160;</xsl:if>
                  <a href="{link}"><xsl:value-of select="label"/></a>
                </xsl:when>
                <xsl:otherwise>
                  <xsl:if test="position()>1">&#160;&#8594;&#160;</xsl:if>
                  <xsl:value-of select="label"/>
                </xsl:otherwise>
              </xsl:choose>
            </xsl:if>
          </xsl:for-each>          
        </div>        
        
      	<!--<script src="{$res_engine_url}js/jquery.keyboard.js"></script>
        <script>
          function art_cramps_pager_doleft(){
          window.location="<xsl:value-of select="//crumbs/item[position()=last()-1]/link" disable-output-escaping="yes"/>";
          }
          function art_cramps_pager_doright(){
          window.location="<xsl:value-of select="//crumbs/item[position()=last()]/link" disable-output-escaping="yes"/>";
          }
          $(document).keyboard('shift aleft',art_cramps_pager_doleft);
          $(document).keyboard('shift aright',art_cramps_pager_doright);
        </script>-->        
        
        <xsl:value-of select="content/html" disable-output-escaping="yes"/>        
      </xsl:when>
      <xsl:otherwise>
        <div class="crumbs s11 black" style="{$crumbs_style}">&#160;</div>
        <xsl:value-of select="content/html" disable-output-escaping="yes"/>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>
</xsl:stylesheet>