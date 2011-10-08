<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  <xsl:output method="html" version="1.0" omit-xml-declaration="no" indent="yes" encoding="utf-8"/>
  <xsl:decimal-format grouping-separator=" " NaN=""/>
	<xsl:param name="res_site_url"></xsl:param>
	<xsl:param name="res_engine_url"></xsl:param>
	<xsl:param name="current_url"></xsl:param>	
	<xsl:param name="script_name">pager_get_page</xsl:param><!-- имя вызываемого скрипта с префиксом -->	
	<xsl:param name="scr"></xsl:param><!-- префикс вызываемого скрипта -->

  <xsl:template match="pager">
    <xsl:if test="count(//pages/item)>1 or //page_selected>1">
      <div class="pager">
        <xsl:if test="count(//pages/item)>0 or //page_selected>1">
        	
        	<div class="pages pages_ajax">
        		<xsl:choose>
        			<xsl:when test="page_prev!=''">
        				<span class="lnk" onclick="{$script_name}('{//page_prev/num}','{$scr}')">&#8592;</span>&#160;
        			</xsl:when>
        			<xsl:otherwise>    
        				<span class="gray">&#8592;</span>&#160;    
        			</xsl:otherwise>
        		</xsl:choose>        		
        		
            <xsl:if test="//pages/item[position()=1]/num>1">
            	<a href="javascript: void(0)" onclick="{$script_name}('{//page_first/num}','{$scr}')">...</a><xsl:text> </xsl:text>  
            </xsl:if>          
            
            <xsl:for-each select="//pages/item">
              <xsl:choose>
                <xsl:when test="num=//page_selected">
                  <xsl:value-of select="format-number(num,'### ###.##')" disable-output-escaping="yes"/>
                </xsl:when>
                <xsl:otherwise>
                	<a href="javascript: void(0)"  onclick="{$script_name}('{num}','{$scr}')"><xsl:value-of select="format-number(num,'### ###.##')" disable-output-escaping="yes"/></a>
                </xsl:otherwise>
              </xsl:choose>
              <xsl:text> </xsl:text>
            </xsl:for-each>
            
            <xsl:if test="//page_last/num>//pages/item[position()=last()]/num">
            	<a href="javascript: void(0)"  onclick="{$script_name}('{//page_last/num}','{$scr}')">...</a>  
            </xsl:if>  
        		
        		<xsl:choose>
        			<xsl:when test="page_next!=''">
        				&#160;<span class="lnk" onclick="{$script_name}('{//page_next/num}','{$scr}')">&#8594;</span>
        			</xsl:when>
        			<xsl:otherwise>    
        				&#160;<span class="gray">&#8594;</span>    
        			</xsl:otherwise>
        		</xsl:choose>        		
          </div>
        </xsl:if>        
      </div>
    </xsl:if>
    
  </xsl:template>
</xsl:stylesheet>