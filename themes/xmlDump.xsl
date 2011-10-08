<?xml version="1.0" encoding="UTF-8"?>
<!--
  XSLT 1.0 compliant version of the IE default stylesheet
 
  Author:      Jonathan Marsh
  Adapted by:  Derek-Denny Brown, Anton Lapounov
-->
<xsl:stylesheet
    version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:ie5xsl="http://www.w3.org/TR/WD-xsl"
    xmlns:dt="urn:schemas-microsoft-com:datatypes"
    xmlns:d2="uuid:C2F41010-65B3-11d1-A29F-00AA00C14882">
    
   <xsl:output method="html" encoding="utf-8"/>
 
  <xsl:template match="node()"/>
 
  <xsl:template name="element-name">
    <SPAN>
      <xsl:attribute name="class">
        <xsl:if test="self::xsl:*">x</xsl:if>
        <xsl:text>t</xsl:text>
      </xsl:attribute>
      <xsl:value-of select="name()"/>
    </SPAN>
  </xsl:template>
  
  <xsl:template name="spacer"></xsl:template>
  
  <xsl:template name="attributes">
    <xsl:apply-templates select="@*"/>
    <xsl:for-each select="namespace::*">	
      <xsl:variable name="p" select="name()"/>
      <xsl:variable name="u" select="string()"/>
      <xsl:if test="name()!='xml' and not(../../namespace::*[name()=$p][string()=$u])">
        <SPAN class="t">
          <xsl:text xml:space="preserve"> xmlns</xsl:text>
          <xsl:if test="name()!=''">
            <xsl:text>:</xsl:text>
          </xsl:if>
          <xsl:value-of select="$p"/>
        </SPAN>
        <SPAN class="m">="</SPAN>
        <B><xsl:value-of select="$u"/></B>
        <SPAN class="m">"</SPAN>
      </xsl:if>
    </xsl:for-each>
  </xsl:template>
 
  <xsl:template match="/">
  
    <div>
      <h4 style="font-size: 16px; color: white; background-color:black; margin:5px 0px; padding: 3px">&lt;XML/&gt; Dump</h4>
       <STYLE>
        
      <!-- container for expanding/collapsing content -->
        .c  {cursor:pointer}
      <!-- button - contains +/-/nbsp -->
        .b  {color:red; font-family:'Courier New'; font-weight:bold; text-decoration:none}
      <!-- element container -->
        .e  {margin-left:1em; text-indent:-1em; margin-right:1em}
      <!-- comment or cdata -->
        .k  {margin-left:1em; text-indent:-1em; margin-right:1em}
      <!-- tag -->
        .t  {color:#990000}
      <!-- tag in xsl namespace -->
        .xt {color:#990099}
      <!-- attribute in xml or xmlns namespace -->
        .ns {color:red}
      <!-- attribute in dt namespace -->
        .dt {color:green}
      <!-- markup characters -->
        .m  {color:blue}
      <!-- text node -->
        .tx {font-weight:bold}
      <!-- multi-line (block) cdata -->
        .db {text-indent:0px; margin-left:1em; margin-top:0px; margin-bottom:0px;
             padding-left:.3em; border-left:1px solid #CCCCCC; font:small Courier}
      <!-- single-line (inline) cdata -->
        .di {font:small Courier}
      <!-- DOCTYPE declaration -->
        .d  {color:blue}
      <!-- pi -->
        .pi {color:blue}
      <!-- xml declaration -->
        .x {color:black}
      <!-- multi-line (block) comment -->
        .cb {text-indent:0px; margin-left:1em; margin-top:0px; margin-bottom:0px;
             padding-left:.3em; font:small Courier; color:#888888}
      <!-- single-line (inline) comment -->
        .ci {font:small Courier; color:#888888}
        PRE {margin:0px; display:inline}
        .st {font-size: 12px;}
      </STYLE>
      
      <script><xsl:comment>
        // Detect and switch the display of CDATA and comments from an inline view
        //  to a block view if the comment or CDATA is multi-line.
        function f(e)
        {
          // if this element is an inline comment, and contains more than a single
          //  line, turn it into a block comment.
          if (e.className == "ci") {
            if (e.childNodes[0].innerHTML.indexOf("\n") &gt; 0)
              fix(e, "cb");
          }
          
          // if this element is an inline cdata, and contains more than a single
          //  line, turn it into a block cdata.
          if (e.className == "di") {
            if (e.childNodes[0].innerHTML.indexOf("\n") &gt; 0)
              fix(e, "db");
          }
          
          // remove the id since we only used it for cleanup
          e.id = "";
        }
        
        // Fix up the element as a "block" display and enable expand/collapse on it
        function fix(e, cl)
        {
          // change the class name and display value
          e.className = cl;
          e.style.display = "block";
          
          // mark the comment or cdata display as a expandable container
          j = e.parentNode.childNodes[0];
          j.className = "c";
 
          // find the +/- symbol and make it visible - the dummy link enables tabbing
          k = j.childNodes[0];
          k.style.visibility = "visible";
          k.href = "#";
        }
 
        // Change the +/- symbol and hide the children.  This function works on "element"
        //  displays
        function ch(e)
        {
          // find the +/- symbol
          var mark = e.childNodes[1].childNodes[1];
          
          // if it is already collapsed, expand it by showing the children
          if (mark.innerHTML == "+")
          {
            mark.innerHTML = "-";
            for (var i = 1; i &lt; e.childNodes.length; i++)
              if(e.childNodes[i].className=='')
              e.childNodes[i].style.display = "block";
          }
          
          // if it is expanded, collapse it by hiding the children
          else if (mark.innerHTML == "-")
          {
            mark.innerHTML = "+";
            for (var i = 1; i &lt; e.childNodes.length; i++){
              if(e.childNodes[i].className=='')
              e.childNodes[i].style.display="none";
            }
          }
        }
        
        // Change the +/- symbol and hide the children.  This function work on "comment"
        //  and "cdata" displays
        function ch2(e)
        {
          // find the +/- symbol, and the "PRE" element that contains the content
          mark = e.children(0).children(0);
          contents = e.children(1);
          
          // if it is already collapsed, expand it by showing the children
          if (mark.innerHTML == "+")
          {
            mark.innerHTML = "-";
            // restore the correct "block"/"inline" display type to the PRE
            if (contents.className == "db" || contents.className == "cb")
              contents.style.display = "block";
            else contents.style.display = "inline";
          }
          
          // if it is expanded, collapse it by hiding the children
          else if (mark.innerHTML == "-")
          {
            mark.innerHTML = "+";
            contents.style.display = "none";
          }
        }
        
        // Handle a mouse click
        function cl(event)
        {          
          var e = event.target;
          //alert(var_dump(e))
          // make sure we are handling clicks upon expandable container elements
          if (e.className != "c")
          {
            e = e.parentNode;
            if (e.className != "c")
            {
              return;
            }
          }
          e = e.parentNode;          
          // call the correct funtion to change the collapse/expand state and display
          if (e.className == "e")
            ch(e);
          if (e.className == "k")
            ch2(e);
        }
 
        // Erase bogus link info from the status window
        function h()
        {
          window.status=" ";
        }
 
        // Set the onclick handler
        document.onclick = cl;
        
      </xsl:comment></script>      
      <div class="st">
        <xsl:apply-templates/>
      </div>    
    </div>
  </xsl:template>
 
  <!-- Templates for each node type follows.  The output of each template has a similar structure
  to enable script to walk the result tree easily for handling user interaction. -->
  
  <!-- Template for processing instructions -->
  <xsl:template match="processing-instruction()">
    <DIV class="e">
      <xsl:call-template name="spacer"/>
      <SPAN class="m">&lt;?</SPAN>
      <SPAN class="pi">
        <xsl:value-of select="name()"/>
        <xsl:text> </xsl:text>
        <xsl:value-of select="."/>
      </SPAN>
      <SPAN class="m">?&gt;</SPAN>
    </DIV>
  </xsl:template>
 
  <!-- Template for attributes not handled elsewhere -->
  <xsl:template match="@*">
    <SPAN>
      <xsl:attribute name="class">
        <xsl:if test="parent::xsl:* | parent::ie5xsl:*">x</xsl:if>
        <xsl:text>t</xsl:text>
      </xsl:attribute>
      <xsl:text xml:space="preserve"> </xsl:text>
      <xsl:value-of select="name()"/>
    </SPAN>
    <SPAN class="m">="</SPAN>
    <B><xsl:value-of select="."/></B>
    <SPAN class="m">"</SPAN>
  </xsl:template>
 
  <!-- Template for attributes in the xml namespace -->
  <xsl:template match="@xml:*">
    <SPAN class="ns">
      <xsl:text xml:space="preserve"> </xsl:text>
      <xsl:value-of select="name()"/>
    </SPAN>
    <SPAN class="m">="</SPAN>
    <B class="ns"><xsl:value-of select="."/></B>
    <SPAN class="m">"</SPAN>
  </xsl:template>
      
  <!-- Template for attributes in the dt namespace -->
  <xsl:template match="@dt:*">
    <SPAN class="dt">
      <xsl:text xml:space="preserve"> </xsl:text>
      <xsl:value-of select="name()"/>
    </SPAN>
    <SPAN class="m">="</SPAN>
    <B class="dt"><xsl:value-of select="."/></B>
    <SPAN class="m">"</SPAN>
  </xsl:template>
  <xsl:template match="@d2:*">
    <SPAN class="dt">
      <xsl:text xml:space="preserve"> </xsl:text>
      <xsl:value-of select="name()"/>
    </SPAN>
    <SPAN class="m">="</SPAN>
    <B class="dt"><xsl:value-of select="."/></B>
    <SPAN class="m">"</SPAN>
  </xsl:template>
 
  <!-- Template for text nodes -->
  <xsl:template match="text()">
    <DIV class="e">
      <xsl:call-template name="spacer"/>
      <SPAN class="tx">
        <xsl:value-of select="."/>
      </SPAN>
    </DIV>
  </xsl:template>
  
  <!-- Note that in the following templates for comments and cdata, by default we apply a style
  appropriate for single line content (e.g. non-expandable, single line display).  But we also
  inject the attribute 'id="clean"' and a script call 'f(clean)'.  As the output is read by the
  browser, it executes the function immediately.  The function checks to see if the comment or
  cdata has multi-line data, in which case it changes the style to a expandable, multi-line
  display.  Performing this switch in the DHTML instead of from script in the XSL increases
  the performance of the style sheet, especially in the browser's asynchronous case -->
 
  <!-- Template for comment nodes -->
  <xsl:template match="comment()">
    <DIV class="k">
      <SPAN>
        <A class="b" onclick="return false" onfocus="h()" STYLE="visibility:hidden">-</A>
        <xsl:text> </xsl:text>
        <SPAN class="m">&lt;!--</SPAN>
      </SPAN>
      <SPAN id="clean" class="ci">
        <PRE><xsl:value-of select="."/></PRE>
      </SPAN>
      <xsl:call-template name="spacer"/>
      <SPAN class="m">--&gt;</SPAN>
      <SCRIPT>f(clean);</SCRIPT>
    </DIV>
  </xsl:template>
  
  <!-- Note the following templates for elements may examine children.  This harms to some extent
  the ability to process a document asynchronously - we can't process an element until we have
  read and examined at least some of its children.  Specifically, the first element child must
  be read before any template can be chosen.  And any element that does not have element
  children must be read completely before the correct template can be chosen. This seems 
  an acceptable performance loss in the light of the formatting possibilities available 
  when examining children. -->
  <!-- Template for elements not handled elsewhere (leaf nodes) -->
  <xsl:template match="*">
    <DIV class="e">
      <DIV STYLE="margin-left:1em;text-indent:-2em">
        <xsl:call-template name="spacer"/>
        <SPAN class="m">&lt;</SPAN>
        <xsl:call-template name="element-name"/>
        <xsl:call-template name="attributes"/>
        <SPAN class="m"> /&gt;</SPAN>
      </DIV>
    </DIV>
  </xsl:template>
  
  <!-- Template for elements with comment, pi and/or cdata children -->
  <xsl:template match="*[node()]">
    <DIV class="e">
      <DIV class="c">
        <A href="#" onclick="return false" onfocus="h()" class="b">-</A>
        <xsl:text> </xsl:text>
        <SPAN class="m">&lt;</SPAN>
        <xsl:call-template name="element-name"/>
        <xsl:call-template name="attributes"/>
        <SPAN class="m">&gt;</SPAN>
      </DIV>
      <DIV>
        <xsl:apply-templates/>
        <DIV>
          <xsl:call-template name="spacer"/>
          <SPAN class="m">&lt;/</SPAN>
          <xsl:call-template name="element-name"/>
          <SPAN class="m">&gt;</SPAN>
        </DIV>
      </DIV>
    </DIV>
  </xsl:template>
  
  <!-- Template for elements with only text children -->
  <xsl:template match="*[text() and not(comment() or processing-instruction())]">
    <DIV class="e">
      <DIV STYLE="margin-left:1em;text-indent:-2em">
        <xsl:call-template name="spacer"/>
        <SPAN class="m">&lt;</SPAN>
        <xsl:call-template name="element-name"/>
        <xsl:call-template name="attributes"/>
        <SPAN class="m">&gt;</SPAN>
        <SPAN class="tx">
          <xsl:value-of select="."/>
        </SPAN>
        <SPAN class="m">&lt;/</SPAN>
        <xsl:call-template name="element-name"/>
        <SPAN class="m">&gt;</SPAN>
      </DIV>
    </DIV>
  </xsl:template>
  
  <!-- Template for elements with element children -->
  <xsl:template match="*[*]">
    <DIV class="e">
      <DIV class="c" STYLE="margin-left:1em;text-indent:-2em; ">
        <A href="#" onclick="return false" onfocus="h()" class="b">
        <xsl:if test="generate-id(//node()) != generate-id(.)">+</xsl:if>
        <xsl:if test="generate-id(//node()) = generate-id(.)">-</xsl:if></A>
        <xsl:text> </xsl:text>
        <SPAN class="m">&lt;</SPAN>
        <xsl:call-template name="element-name"/>
        <xsl:call-template name="attributes"/>
        <SPAN class="m">&gt;</SPAN> 
        <span style="color:#999999; font-size:9px">&#160;<xsl:value-of select="count(*)"/> nodes</span>
      </DIV>
      <DIV>
        <xsl:if test="generate-id(//node()) != generate-id(.)">
          <xsl:attribute name="style">display:none;</xsl:attribute>
        </xsl:if>
        <xsl:apply-templates/>
        <DIV>
          <xsl:call-template name="spacer"/>
          <SPAN class="m">&lt;/</SPAN>
          <xsl:call-template name="element-name"/>
          <SPAN class="m">&gt;</SPAN>
        </DIV>
      </DIV>
    </DIV>
  </xsl:template>
 
</xsl:stylesheet>