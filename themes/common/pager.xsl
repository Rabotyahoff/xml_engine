<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  <xsl:output method="html" version="1.0" omit-xml-declaration="no" indent="yes" encoding="utf-8"/>
  <xsl:decimal-format grouping-separator=" " NaN=""/>
	<xsl:param name="res_site_url"></xsl:param>
	<xsl:param name="res_engine_url"></xsl:param>
	<xsl:param name="current_url"></xsl:param>	

  <xsl:template match="pager">
    <xsl:if test="count(//pages/item)>1 or //page_selected>1">
    	<script src="{$res_engine_url}js/jquery.keyboard.js"></script>
      
      <script>
        $(document).keyboard('ctrl aleft',
	        function (){
	      	<xsl:if test="page_prev!=''">
	      		window.location="<xsl:value-of select="//page_prev/link" disable-output-escaping="yes"/>";
	      	</xsl:if>
	      	}        
        );
        $(document).keyboard('ctrl aright',
	        function (){
	      	<xsl:if test="page_next!=''">
	      		window.location="<xsl:value-of select="//page_next/link" disable-output-escaping="yes"/>";
	      	</xsl:if>
	      	}        
        );
      </script>      
      
      <div class="pager">
        <xsl:choose>
          <xsl:when test="page_prev!=''">
            <!--&#8592;&#160;Ctrl <a href="{//page_prev/link}">предыдущая страница</a>-->
            &#8592;&#160;Ctrl <a href="{//page_prev/link}">previous page</a>
          </xsl:when>
          <xsl:otherwise>
            <!--<span class="gray">&#8592; Ctrl предыдущая страница</span>-->    
          	<span class="gray">&#8592; Ctrl previous page</span>    
          </xsl:otherwise>
        </xsl:choose>        
        <xsl:text> &#160; </xsl:text>
        <xsl:choose>
          <xsl:when test="page_next!=''">
            <!--<a href="{//page_next/link}">следующая</a> Ctrl&#160;&#8594;-->
            <a href="{//page_next/link}">next</a> Ctrl&#160;&#8594;
          </xsl:when>
          <xsl:otherwise>
            <!--<span class="gray">следующая Ctrl &#8594;</span>-->    
            <span class="gray">next Ctrl &#8594;</span>    
          </xsl:otherwise>
        </xsl:choose>
        
        <xsl:if test="count(//pages/item)>0 or //page_selected>1">
          <div class="pages">
            <xsl:if test="//pages/item[position()=1]/num>1">
              <a href="{//page_first/link}">...</a><xsl:text> </xsl:text>  
            </xsl:if>          
            
            <xsl:for-each select="//pages/item">
              <xsl:choose>
                <xsl:when test="num=//page_selected">
                  <xsl:value-of select="format-number(num,'### ###.##')" disable-output-escaping="yes"/>
                </xsl:when>
                <xsl:otherwise>
                  <a href="{link}"><xsl:value-of select="format-number(num,'### ###.##')" disable-output-escaping="yes"/></a>
                </xsl:otherwise>
              </xsl:choose>
              <xsl:text> </xsl:text>
            </xsl:for-each>
            
            <xsl:if test="//page_last/num>//pages/item[position()=last()]/num">
              <a href="{//page_last/link}">...</a>  
            </xsl:if>            
          </div>
        </xsl:if>        
      </div>
    </xsl:if>
    
  </xsl:template>
</xsl:stylesheet>