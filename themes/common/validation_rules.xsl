<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
  <xsl:output method="text"/>
  <xsl:template match="text()" />	
	
  <xsl:template match="/">
  	rules: {
    <xsl:for-each select="/rules/child::*">
      <xsl:variable  name = "field" select="name(.)"/>
      <xsl:value-of select="$field"/>: {
 
      <xsl:if test="./enum">
        enum:
        [
        <xsl:for-each select="./enum/*">
          "<xsl:value-of select="name(.)"/>"<xsl:if test="position() &lt; last()">,</xsl:if>
        </xsl:for-each>
        ],
      </xsl:if>

      <xsl:if test="./limit">
        <xsl:if test="./length/min">
          min: <xsl:value-of select="./limit/min"/>,
        </xsl:if>
        <xsl:if test="./length/max">
          max: <xsl:value-of select="./limit/max"/>,
        </xsl:if>
      </xsl:if>

      <xsl:if test="./length">
        <xsl:if test="./length/min">
          minlength: <xsl:value-of select="./length/min"/>,
        </xsl:if>
        <xsl:if test="./length/max">
          maxlength: <xsl:value-of select="./length/max"/>,
        </xsl:if>
      </xsl:if>

      <xsl:if test="./regexp">
        regexp: "<xsl:value-of select="./regexp"/>",
      </xsl:if>

    	<xsl:if test="./id=1">
        number:true,
        min:0,
      </xsl:if>

    	<xsl:if test="./url=1">
        url:true,          
      </xsl:if>

      <xsl:if test="./email=1">
        email:true,
      </xsl:if>

    	<xsl:if test="./required=1">
        required:true,
      </xsl:if>

      <xsl:if test="./checkbox"></xsl:if>    
      }<xsl:if test="position()!=last()">,</xsl:if>
    </xsl:for-each>
  	},

  	<xsl:if test="count(/rules/child::*/err_text_js)>0">
  		messages:{
  		<xsl:for-each select="/rules/child::*">
  			<xsl:variable  name = "field" select="name(.)"/>
  			
  			<xsl:if test="./err_text_js">
  				<xsl:value-of select="$field"/>:"<xsl:value-of select="err_text_js"/>"<xsl:if test="position()!=last()">,
  				</xsl:if>  				
  			</xsl:if>    
  		</xsl:for-each>
  		},  		
  	</xsl:if>
  </xsl:template>
</xsl:stylesheet>

