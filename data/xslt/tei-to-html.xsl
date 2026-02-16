<?xml version="1.0" encoding="UTF-8"?>
<!--
    TEI-to-HTML Transformation Stylesheet
    Transforms TEI P5 encoded manuscripts into readable HTML.

    This stylesheet handles common TEI elements used in diplomatic
    transcriptions of literary manuscripts, including:
    - Structural elements (div, head, p, lg, l)
    - Editorial interventions (del, add, sic/corr, gap)
    - Named entities (persName, placeName, geogName)
    - Notes and glosses
    - Verse and poetry markup
-->
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:tei="http://www.tei-c.org/ns/1.0"
    exclude-result-prefixes="tei">

    <xsl:output method="html" encoding="UTF-8" indent="yes"/>

    <!-- ROOT TEMPLATE -->
    <xsl:template match="/">
        <div class="tei-document">
            <xsl:apply-templates select="tei:TEI/tei:teiHeader"/>
            <xsl:apply-templates select="tei:TEI/tei:text"/>
        </div>
    </xsl:template>

    <!-- TEI HEADER / METADATA -->
    <xsl:template match="tei:teiHeader">
        <div class="tei-metadata">
            <div class="metadata-grid">
                <div class="meta-item">
                    <span class="meta-label">Title</span>
                    <span class="meta-value">
                        <xsl:value-of select="tei:fileDesc/tei:titleStmt/tei:title[@type='main']"/>
                    </span>
                </div>
                <xsl:if test="tei:fileDesc/tei:titleStmt/tei:title[@type='sub']">
                    <div class="meta-item">
                        <span class="meta-label">Subtitle</span>
                        <span class="meta-value">
                            <xsl:value-of select="tei:fileDesc/tei:titleStmt/tei:title[@type='sub']"/>
                        </span>
                    </div>
                </xsl:if>
                <div class="meta-item">
                    <span class="meta-label">Author</span>
                    <span class="meta-value">
                        <xsl:value-of select="tei:fileDesc/tei:titleStmt/tei:author/tei:persName/tei:forename"/>
                        <xsl:text> </xsl:text>
                        <xsl:value-of select="tei:fileDesc/tei:titleStmt/tei:author/tei:persName/tei:surname"/>
                    </span>
                </div>
                <xsl:if test="tei:fileDesc/tei:sourceDesc/tei:msDesc">
                    <div class="meta-item">
                        <span class="meta-label">Manuscript</span>
                        <span class="meta-value">
                            <xsl:value-of select="tei:fileDesc/tei:sourceDesc/tei:msDesc/tei:msIdentifier/tei:idno"/>
                            <xsl:text>, </xsl:text>
                            <xsl:value-of select="tei:fileDesc/tei:sourceDesc/tei:msDesc/tei:msIdentifier/tei:repository"/>
                        </span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Date</span>
                        <span class="meta-value">
                            <xsl:value-of select="tei:fileDesc/tei:sourceDesc/tei:msDesc/tei:history/tei:origin/tei:origDate"/>
                        </span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Format</span>
                        <span class="meta-value">
                            <xsl:value-of select="tei:fileDesc/tei:sourceDesc/tei:msDesc/tei:physDesc/tei:objectDesc/@form"/>
                            <xsl:text> — </xsl:text>
                            <xsl:value-of select="tei:fileDesc/tei:sourceDesc/tei:msDesc/tei:physDesc/tei:objectDesc/tei:supportDesc/tei:extent"/>
                        </span>
                    </div>
                    <xsl:if test="tei:fileDesc/tei:sourceDesc/tei:msDesc/tei:physDesc/tei:handDesc/tei:handNote">
                        <div class="meta-item">
                            <span class="meta-label">Hand</span>
                            <span class="meta-value">
                                <xsl:value-of select="tei:fileDesc/tei:sourceDesc/tei:msDesc/tei:physDesc/tei:handDesc/tei:handNote"/>
                            </span>
                        </div>
                    </xsl:if>
                </xsl:if>
            </div>
        </div>
    </xsl:template>

    <!-- TEXT STRUCTURE -->
    <xsl:template match="tei:text">
        <div class="tei-text"><xsl:apply-templates/></div>
    </xsl:template>

    <xsl:template match="tei:front">
        <div class="tei-front"><xsl:apply-templates/></div>
    </xsl:template>

    <xsl:template match="tei:body">
        <div class="tei-body"><xsl:apply-templates/></div>
    </xsl:template>

    <xsl:template match="tei:back">
        <div class="tei-back"><hr class="section-divider"/><xsl:apply-templates/></div>
    </xsl:template>

    <xsl:template match="tei:titlePage">
        <div class="title-page"><xsl:apply-templates/></div>
    </xsl:template>

    <xsl:template match="tei:docTitle/tei:titlePart[@type='main']">
        <h1 class="doc-title"><xsl:apply-templates/></h1>
    </xsl:template>

    <xsl:template match="tei:docAuthor">
        <p class="doc-author"><xsl:apply-templates/></p>
    </xsl:template>

    <xsl:template match="tei:docDate">
        <p class="doc-date"><xsl:apply-templates/></p>
    </xsl:template>

    <xsl:template match="tei:div">
        <div class="tei-div tei-div-{@type}">
            <xsl:if test="@n">
                <xsl:attribute name="data-n"><xsl:value-of select="@n"/></xsl:attribute>
            </xsl:if>
            <xsl:apply-templates/>
        </div>
    </xsl:template>

    <xsl:template match="tei:div/tei:head">
        <h2 class="section-head"><xsl:apply-templates/></h2>
    </xsl:template>

    <xsl:template match="tei:p">
        <p class="tei-p"><xsl:apply-templates/></p>
    </xsl:template>

    <!-- VERSE / POETRY -->
    <xsl:template match="tei:lg[@type='poem']">
        <div class="tei-poem"><xsl:apply-templates/></div>
    </xsl:template>

    <xsl:template match="tei:lg[@type='poem']/tei:head">
        <h3 class="poem-title"><xsl:apply-templates/></h3>
    </xsl:template>

    <xsl:template match="tei:lg[@type='stanza']">
        <div class="tei-stanza"><xsl:apply-templates/></div>
    </xsl:template>

    <xsl:template match="tei:l">
        <div>
            <xsl:attribute name="class">
                <xsl:text>tei-line</xsl:text>
                <xsl:if test="@rend='indent'"> tei-line-indent</xsl:if>
            </xsl:attribute>
            <xsl:apply-templates/>
        </div>
    </xsl:template>

    <!-- EDITORIAL INTERVENTIONS -->
    <xsl:template match="tei:del">
        <span class="tei-del" title="Deleted by author"><xsl:apply-templates/></span>
    </xsl:template>

    <xsl:template match="tei:add">
        <span class="tei-add" title="Added by author ({@place})"><xsl:apply-templates/></span>
    </xsl:template>

    <xsl:template match="tei:sic">
        <span class="tei-sic" title="Original reading (sic)"><xsl:apply-templates/></span>
    </xsl:template>

    <xsl:template match="tei:corr">
        <span class="tei-corr" title="Editorial correction">[<xsl:apply-templates/>]</span>
    </xsl:template>

    <xsl:template match="tei:gap">
        <span class="tei-gap" title="Illegible: {@extent} {@unit}">
            [<xsl:value-of select="@extent"/>&#160;<xsl:value-of select="@unit"/>&#160;illegible]
        </span>
    </xsl:template>

    <!-- NAMED ENTITIES -->
    <xsl:template match="tei:persName">
        <span class="tei-persName entity" data-type="person">
            <xsl:if test="@ref"><xsl:attribute name="data-ref"><xsl:value-of select="@ref"/></xsl:attribute></xsl:if>
            <xsl:apply-templates/>
        </span>
    </xsl:template>

    <xsl:template match="tei:placeName">
        <span class="tei-placeName entity" data-type="place">
            <xsl:if test="@ref"><xsl:attribute name="data-ref"><xsl:value-of select="@ref"/></xsl:attribute></xsl:if>
            <xsl:apply-templates/>
        </span>
    </xsl:template>

    <xsl:template match="tei:geogName">
        <span class="tei-geogName entity" data-type="place"><xsl:apply-templates/></span>
    </xsl:template>

    <xsl:template match="tei:region">
        <span class="tei-region entity" data-type="place"><xsl:apply-templates/></span>
    </xsl:template>

    <!-- INLINE ELEMENTS -->
    <xsl:template match="tei:hi[@rend='italic']">
        <em class="tei-hi-italic"><xsl:apply-templates/></em>
    </xsl:template>

    <xsl:template match="tei:hi[@rend='bold']">
        <strong class="tei-hi-bold"><xsl:apply-templates/></strong>
    </xsl:template>

    <xsl:template match="tei:term">
        <span class="tei-term">
            <xsl:if test="@xml:lang"><xsl:attribute name="lang"><xsl:value-of select="@xml:lang"/></xsl:attribute></xsl:if>
            <xsl:apply-templates/>
        </span>
    </xsl:template>

    <xsl:template match="tei:date">
        <time class="tei-date">
            <xsl:if test="@when"><xsl:attribute name="datetime"><xsl:value-of select="@when"/></xsl:attribute></xsl:if>
            <xsl:apply-templates/>
        </time>
    </xsl:template>

    <xsl:template match="tei:title[@level='m']">
        <cite class="tei-title-monograph"><xsl:apply-templates/></cite>
    </xsl:template>

    <xsl:template match="tei:note[@type='gloss']">
        <span class="tei-note-gloss">
            <span class="note-marker">*</span>
            <span class="note-content"><xsl:apply-templates/></span>
        </span>
    </xsl:template>

    <xsl:template match="tei:note">
        <div class="tei-note"><xsl:apply-templates/></div>
    </xsl:template>

    <xsl:template match="tei:label">
        <strong class="tei-label"><xsl:apply-templates/></strong>
    </xsl:template>

</xsl:stylesheet>
