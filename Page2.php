<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | HTML_Page2                                                           |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997 - 2004 The PHP Group                              |
// +----------------------------------------------------------------------+
// | This source file is subject to version 3.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/3_0.txt.                                  |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Adam Daniel <adaniel1@eesus.jnj.com>                        |
// |          Klaus Guenther <klaus@capitalfocus.org>                     |
// +----------------------------------------------------------------------+
//
// $Id$

/**
 * The PEAR::HTML_Page2 package provides a simple interface for generating an XHTML compliant page.
 * 
 * Features:
 * - supports virtually all HTML doctypes, from HTML 2.0 through XHTML 1.1 and XHTML Basic 1.0
 *   plus preliminary support for XHTML 2.0
 * - namespace support
 * - global language declaration for the document
 * - line ending styles
 * - full META tag support
 * - support for stylesheet declaration in the head section
 * - support for script declaration in the head section
 * - support for linked stylesheets and scripts
 * - full support for header <link> tags
 * - body can be a string, object with toHtml or toString methods or an array (can be combined)
 * 
 * Ideas for use:
 * - Use to validate the output of a class for XHTML compliance
 * - Quick prototyping using PEAR packages is now a breeze
 * @category HTML
 * @package  HTML_Page2
 * @version  2.0
 * @author   Adam Daniel <adaniel1@eesus.jnj.com>
 * @author   Klaus Guenther <klaus@capitalfocus.org>
 */

/**
 * Include PEAR core
 */
require_once 'PEAR.php';

/**
 * Include HTML_Common class
 * 
 * Additional required files
 * 
 * HTML/Page2/Doctypes.php is required in _getDoctype()
 * HTML/Page2/Namespaces.php is required in _getNamespace()
 */
require_once 'HTML/Common.php';

/**
 * (X)HTML Page generation class
 * 
 * <p>This class handles the details for creating a properly constructed XHTML page.
 * Page caching, stylesheets, client side script, and Meta tags can be
 * managed using this class.</p>
 * 
 * <p>The body may be a string, object, or array of objects or strings. Objects with
 * toHtml() and toString() methods are supported.</p>
 * 
 * <p><b>XHTML Examples:</b></p>
 * 
 * <p>Simplest example:</p>
 * <code>
 * // the default doctype is XHTML 1.0 Transitional
 * // All doctypes and defaults are set in HTML/Page/Doctypes.php
 * $p = new HTML_Page2();
 *
 * //add some content
 * $p->addBodyContent("<p>some text</p>");
 * 
 * // print to browser
 * $p->display();
 * ?>
 * </code>
 * 
 * <p>Complex XHTML example:</p>
 * <code>
 * <?php
 * // The array takes an array of attributes that determine many important
 * // aspects of the page generations.
 * 
 * // Possible attributes are: charset, mime, lineend, tab, doctype, namespace,
 * // language and cache
 * 
 * $p = new HTML_Page2(array (
 *
 *                          // Sets the charset encoding (default: utf-8)
 *                          'charset'  => 'utf-8',
 *
 *                          // Sets the line end character (default: unix (\n))
 *                          'lineend'  => 'unix',
 *
 *                          // Sets the tab string for autoindent (default: tab (\t))
 *                          'tab'  => '  ',
 *
 *                          // This is where you define the doctype
 *                          'doctype'  => "XHTML 1.0 Strict",
 *
 *                          // Global page language setting
 *                          'language' => 'en',
 *
 *                          // If cache is set to true, the browser may cache the output.
 *                          'cache'    => 'false'
 *                          ));
 *
 * // Here we go
 *
 * // Set the page title
 * $p->setTitle("My page");
 * 
 * // Add optional meta data
 * $p->setMetaData("author", "My Name");
 * 
 * // Put something into the body
 * $p->addBodyContent("<p>some text</p>");
 *
 * // If at some point you want to clear the page content
 * // and output an error message, you can easily do that
 * // See the source for {@link toHtml} and {@link _getDoctype}
 * // for more details
 * if ($error) {
 *     $p->setTitle("Error!");
 *     $p->setBody("<p>Houston, we have a problem: $error</p>");
 *     $p->display();
 *     die;
 * } // end error handling
 *
 * // print to browser
 * $p->display();
 * // output to file
 * $p->toFile('example.html');
 * ?>
 * </code>
 * 
 * Simple XHTML declaration example:
 * <code>
 * <?php
 * $p = new HTML_Page2();
 * // An XHTML compliant page (with title) is automatically generated
 *
 * // This overrides the XHTML 1.0 Transitional default
 * $p->setDoctype('XHTML 1.0 Strict');
 * 
 * // Put some content in here
 * $p->addBodyContent("<p>some text</p>");
 *
 * // print to browser
 * $p->display();
 * ?>
 * </code>
 * 
 * <p><b>HTML examples:</b></p>
 *
 * <p>HTML 4.01 example:</p>
 * <code>
 * <?php
 * $p = new HTML_Page2('doctype="HTML 4.01 Strict"');
 * $p->addBodyContent = "<p>some text</p>";
 * $p->display();
 * ?>
 * </code>
 * 
 * <p>nuke doctype declaration:</p>
 *
 * <code>
 * <?php
 * $p = new HTML_Page2('doctype="none"');
 * $p->addBodyContent = "<p>some text</p>";
 * $p->display();
 * ?>
 * </code>
 * 
 * 
 * @version 2.0
 * @package HTML_Page2
 * @since   PHP 4.0.3pl1
 */
class HTML_Page2 extends HTML_Common {
    
    /**
     * Contains the content of the <body> tag.
     * 
     * @var     array
     * @access  private
     * @since   2.0
     */
    var $_body = array();
    
    /**
     * Controls caching of the page
     * 
     * @var     bool
     * @access  private
     * @since   2.0
     */
    var $_cache = false;
    
    /**
     * Contains the character encoding string
     * 
     * @var     string
     * @access  private
     * @since   2.0
     */
    var $_charset = 'utf-8';
    
    /**
     * Contains the !DOCTYPE definition
     * 
     * @var array
     * @access private
     * @since   2.0
     */
    var $_doctype = array('type'=>'xhtml','version'=>'1.0','variant'=>'transitional');
    
    /**
     * Contains the page language setting
     * 
     * @var     string
     * @access  private
     * @since   2.0
     */
    var $_language = 'en';
    
    /**
     * Array of meta tags
     * 
     * @var     array
     * @access  private
     * @since   2.0
     */
    var $_metaTags = array( 'standard' => array ( 'Generator' => 'PEAR HTML_Page' ) );
    
    /**
     * Document mime type
     * 
     * @var      string
     * @access   private
     * @since   2.0
     */
    var $_mime = 'text/html';
    
    /**
     * Document namespace
     * 
     * @var      string
     * @access   private
     * @since   2.0
     */
    var $_namespace = '';
    
    /**
     * Array of linked scripts
     * 
     * @var      array
     * @access   private
     * @since   2.0
     */
    var $_scripts = array();
    
    /**
     * Array of scripts placed in the header
     * 
     * @var  array
     * @access   private
     * @since   2.0
     */
    var $_script = array();
    
    /**
     * Suppresses doctype
     * 
     * @var     boolean
     * @access  private
     * @since   2.0
     */
    var $_simple = false;
    
    /**
     * Array of included style declarations
     * 
     * @var     array
     * @access  private
     * @since   2.0
     */
    var $_style = array();
    
    /**
     * Array of linked style sheets
     * 
     * @var     array
     * @access  private
     * @since   2.0
     */
    var $_styleSheets = array();
    
    /**
     * Array of Header <link> tags
     * 
     * @var     array
     * @access  private
     * @since   2.0
     */
    var $_links = array();

    /**
     * HTML page title
     * 
     * @var     string
     * @access  private
     * @since   2.0
     */
    var $_title = '';
    
    /**
     * Defines whether XML prolog should be prepended to XHTML documents
     * 
     * @var  bool
     * @access   private
     * @since   2.0
     */
    var $_xmlProlog = true;
    
    /**
     * Class constructor.
     *
     * <p>Accepts an array of attributes</p>
     * 
     * <p><b>General options:</b></p>
     *     - "lineend" => "unix|win|mac" (Sets line ending style; defaults to 
     *        unix.) See also {@link setLineEnd}. 
     *     - "tab"     => string (Sets line ending style; defaults to \t.)
     *        See also {@link setTab}. 
     *     - "cache"   => "false|true"  See also {@link setCache}. 
     *     - "charset" => charset string (Sets charset encoding; defaults 
     *       to utf-8) See also {@link setCharset} and {@link getCharset}. 
     *     - "mime"    => mime encoding string (Sets document mime type; 
     *       defaults to text/html)  See also {@link setMimeEncoding}. 
     * <p><b>XHTML specific options:</b></p>
     *     - "doctype"  => string (Sets XHTML doctype; defaults to 
     *       XHTML 1.0 Transitional.)  See also {@link setDoctype}. 
     *     - "language" => two letter language designation. (Defines global 
     *       document language; defaults to "en".) See also {@link setLang}.
     *     - "namespace"  => string (Sets document namespace; defaults to the 
     *       W3C defined namespace.) See also {@link setNamespace}. 
     * 
     * @param   mixed   $attributes     Associative array of table tag 
     *                                  attributes 
     * @access  public
     * @since   2.0
     */
    function HTML_Page2($attributes = array())
    {
        
        if ($attributes) {
            $attributes = $this->_parseAttributes($attributes);
        }
        
        if (isset($attributes['lineend'])) {
            $this->setLineEnd($attributes['lineend']);
        }
        
        if (isset($attributes['charset'])) {
            $this->setCharset($attributes['charset']);
        }
        
        if (isset($attributes['doctype'])){
            if ($attributes['doctype'] == 'none') {
                $this->_simple = true;
            } elseif ($attributes['doctype']) {
                $this->setDoctype($attributes['doctype']);
            }
        }
        
        if (isset($attributes['language'])) {
            $this->setLang($attributes['language']);
        }
        
        if (isset($attributes['mime'])) {
            $this->setMimeEncoding($attributes['mime']);
        }
        
        if (isset($attributes['namespace'])) {
            $this->setNamespace($attributes['namespace']);
        }
        
        if (isset($attributes['tab'])) {
            $this->setTab($attributes['tab']);
        }
        
        if (isset($attributes['cache'])) {
            $this->setCache($attributes['cache']);
        }
        
    } // end class constructor

    /**
     * Iterates through an array, returning an HTML string
     * 
     * <p>It also handles objects, calling the toHTML or toString methods
     * and propagating the line endings and tabs for objects that
     * extend HTML_Common.</p>
     *
     * @access  protected
     * @param   mixed   $element   The element to be processed
     * @return  string
     */
    function _elementToHtml(&$element) // It's a reference just to save some memory.
    {
        $lnEnd = $this->_getLineEnd();
        $tab = $this->_getTab();
        $strHtml = '';
            if (is_object($element)) {
                if (is_subclass_of($element, 'html_common')) {
                    $element->setTabOffset(1);
                    $element->setTab($tab);
                    $element->setLineEnd($lnEnd);
                }
                if (is_object($element)) {
                    if (method_exists($element, 'toHtml')) {
                        $strHtml .= $this->_elementToHtml($element->toHtml()) . $lnEnd;
                    } elseif (method_exists($element, 'toString')) {
                        $strHtml .= $this->_elementToHtml($element->toString()) . $lnEnd;
                    }
                } else {
                    $strHtml .= $tab . $element . $lnEnd;
                }
            } elseif (is_array($element)) {
                               foreach ($element as $item) {
                                               $strHtml .= $this->_elementToHtml($item);
                               }
            } else {
                $strHtml .= $tab . $element . $lnEnd;
            }
        return $strHtml;
    } // end func _elementToHtml
    
    /**
     * Generates the HTML string for the <body> tag
     * 
     * @access  private
     * @return  string
     */
    function _generateBody()
    {
        
        // get line endings
        $lnEnd = $this->_getLineEnd();
        $tab = $this->_getTab();
        
        // If body attributes exist, add them to the body tag.
        // Depreciated because of CSS
        $strAttr = $this->_getAttrString($this->_attributes);
        
        if ($strAttr) {
            $strHtml = "<body $strAttr>" . $lnEnd;
        } else {
            $strHtml = '<body>' . $lnEnd;
        }

        // Allow for mixed content in the body array, recursing into inner
        // array serching for non-array types.
        $strHtml .= $this->_elementToHtml($this->_body);

        // Close tag
        $strHtml .= '</body>' . $lnEnd;

        // Let's roll!
        return $strHtml;
    } // end func _generateHead
    
    /**
     * Generates the HTML string for the <head> tag
     * 
     * @return string
     * @access private
     */
    function _generateHead()
    {
        // close empty tags if XHTML
        if ($this->_doctype['type'] == 'html'){
            $tagEnd = '>';
        } else {
            $tagEnd = ' />';
        }
        
        // get line endings
        $lnEnd = $this->_getLineEnd();
        $tab = $this->_getTab();
        
        $strHtml  = '<head>' . $lnEnd;
        
        // Generate META tags
        foreach ($this->_metaTags as $type => $tag) {
            foreach ($tag as $name => $content) {
                if ($type == 'http-equiv') {
                    $strHtml .= $tab . "<meta http-equiv=\"$name\" content=\"$content\"" . $tagEnd . $lnEnd;
                } elseif ($type == 'standard') {
                    $strHtml .= $tab . "<meta name=\"$name\" content=\"$content\"" . $tagEnd . $lnEnd;
                }
            }
        }

        // Generate the title tag.
        // Pre-XHTML compatibility:
        //     This comes after meta tags because of possible
        //     http-equiv character set declarations.
        $strHtml .= $tab . '<title>' . $this->getTitle() . '</title>' . $lnEnd;
        
        // Generate link declarations
        foreach ($this->_links as $link) {
            $strHtml .= $tab . $link . $tagEnd . $lnEnd;
        }
        
        // Generate stylesheet links
        foreach ($this->_styleSheets as $strSrc => $strAttr ) {
            $strHtml .= $tab . "<link rel=\"stylesheet\" href=\"$strSrc\" type=\"".$strAttr['mime'].'"';
            if (!is_null($strAttr['media'])){
                $strHtml .= ' media="'.$strAttr['media'].'"';
            }
            $strHtml .= $tagEnd . $lnEnd;
        }
        
        // Generate stylesheet declarations
        foreach ($this->_style as $type => $content) {
            $strHtml .= $tab . '<style type="' . $type . '">' . $lnEnd;
            
            // This is for full XHTML support.
            if ($this->_mime == 'text/html' ) {
                $strHtml .= $tab . $tab . '<!--' . $lnEnd;
            } else {
                $strHtml .= $tab . $tab . '<![CDATA[' . $lnEnd;
            }
            
            if (is_object($content)) {
                
                // first let's propagate line endings and tabs for other HTML_Common-based objects
                if (is_subclass_of($content, "html_common")) {
                    $content->setTab($tab);
                    $content->setTabOffset(3);
                    $content->setLineEnd($lnEnd);
                }
                
                // now let's get a string from the object
                if (method_exists($content, "toString")) {
                    $strHtml .= $content->toString() . $lnEnd;
                } else {
                    PEAR::raiseError('Error: Style content object does not support  method toString().',
                            0,PEAR_ERROR_TRIGGER);
                }
                
            } else {
                $strHtml .= $content . $lnEnd;
            }
            
            // See above note
            if ($this->_mime == 'text/html' ) {
                $strHtml .= $tab . $tab . '-->' . $lnEnd;
            } else {
                $strHtml .= $tab . $tab . ']]>' . $lnEnd;
            }
            $strHtml .= $tab . '</style>' . $lnEnd;
        }
        
        // Generate script file links
        foreach ($this->_scripts as $strSrc => $strType) {
            $strHtml .= $tab . "<script type=\"$strType\" src=\"$strSrc\"></script>" . $lnEnd;
        }
        
        // Generate script declarations
        foreach ($this->_script as $type => $content) {
            $strHtml .= $tab . '<script type="' . $type . '">' . $lnEnd;
            
            // This is for full XHTML support.
            if ($this->_mime == 'text/html' ) {
                $strHtml .= $tab . $tab . '<!--' . $lnEnd;
            } else {
                $strHtml .= $tab . $tab . '<![CDATA[' . $lnEnd;
            }
            
            if (is_object($content)) {
                
                // first let's propagate line endings and tabs for other HTML_Common-based objects
                if (is_subclass_of($content, "html_common")) {
                    $content->setTab($tab);
                    $content->setTabOffset(3);
                    $content->setLineEnd($lnEnd);
                }
                
                // now let's get a string from the object
                if (method_exists($content, "toString")) {
                    $strHtml .= $content->toString() . $lnEnd;
                } else {
                    PEAR::raiseError('Error: Script content object does not support  method toString().',
                            0,PEAR_ERROR_TRIGGER);
                }
                
            } else {
                $strHtml .= $content . $lnEnd;
            }
            
            // See above note
            if ($this->_mime == 'text/html' ) {
                $strHtml .= $tab . $tab . '-->' . $lnEnd;
            } else {
                $strHtml .= $tab . $tab . ']]>' . $lnEnd;
            }
            $strHtml .= $tab . '</script>' . $lnEnd;
        }
        
        // Close tag
        $strHtml .=  '</head>' . $lnEnd;
        
        // Let's roll!
        return $strHtml;
    } // end func _generateHead
    
    /**
     * Returns the doctype declaration
     *
     * @return mixed
     * @access private
     */
    function _getDoctype()
    {
        require('HTML/Page2/Doctypes.php');
        
        if (isset($this->_doctype['type'])) {
            $type = $this->_doctype['type'];
        }
        
        if (isset($this->_doctype['version'])) {
            $version = $this->_doctype['version'];
        }
        
        if (isset($this->_doctype['variant'])) {
            $variant = $this->_doctype['variant'];
        }
        
        $strDoctype = '';
        
        if (isset($variant)) {
            if (isset($doctype[$type][$version][$variant][0])) {
                foreach ( $doctype[$type][$version][$variant] as $string) {
                    $strDoctype .= $string.$this->_getLineEnd();
                }
            }
        } elseif (isset($version)) {
            if (isset($doctype[$type][$version][0])) {
                foreach ( $doctype[$type][$version] as $string) {
                    $strDoctype .= $string.$this->_getLineEnd();
                }
            } else {
                if (isset($default[$type][$version][0])) {
                    $this->_doctype = $this->_parseDoctypeString($default[$type][$version][0]);
                    $strDoctype = $this->_getDoctype();
                }
            }
        } elseif (isset($type)) {
            if (isset($default[$type][0])){
                $this->_doctype = $this->_parseDoctypeString($default[$type][0]);
                $strDoctype = $this->_getDoctype();
            }
        } else {
            $this->_doctype = $this->_parseDoctypeString($default['default'][0]);
            $strDoctype = $this->_getDoctype();
        }
        
        if ($strDoctype) {
            return $strDoctype;
        } else {
            PEAR::raiseError('Error: "'.$this->getDoctypeString().'" is an unsupported or illegal document type.',
                                    0,PEAR_ERROR_TRIGGER);
            $this->_simple = true;
            return;
        }
        
    } // end func _getDoctype
    
    /**
     * Retrieves the document namespace
     *
     * @return mixed
     * @access private
     */
    function _getNamespace()
    {
        require('HTML/Page2/Namespaces.php');
        
        if (isset($this->_doctype['type'])) {
            $type = $this->_doctype['type'];
        }
        
        if (isset($this->_doctype['version'])) {
            $version = $this->_doctype['version'];
        }
        
        if (isset($this->_doctype['variant'])) {
            $variant = $this->_doctype['variant'];
        }
        
        $strNamespace = '';
        
        if (isset($variant)){
            if (isset($namespace[$type][$version][$variant][0]) && is_string($namespace[$type][$version][$variant][0])) {
                $strNamespace = $namespace[$type][$version][$variant][0];
            } elseif (isset($namespace[$type][$version][0]) && is_string($namespace[$type][$version][0]) ) {
                $strNamespace = $namespace[$type][$version][0];
            } elseif (isset($namespace[$type][0]) && is_string($namespace[$type][0]) ) {
                $strNamespace = $namespace[$type][0];
            }
        } elseif (isset($version)) {
            if (isset($namespace[$type][$version][0]) && is_string($namespace[$type][$version][0]) ) {
                $strNamespace = $namespace[$type][$version][0];
            } elseif (isset($namespace[$type][0]) && is_string($namespace[$type][0]) ) {
                $strNamespace = $namespace[$type][0];
            }
        } else {
            if (isset($namespace[$type][0]) && is_string($namespace[$type][0]) ) {
                $strNamespace = $namespace[$type][0];
            }
        }
            
        
        if ($strNamespace) {
            return $strNamespace;
        } else {
            PEAR::raiseError('Error: "' . $this->getDoctypeString() .
                                    '" does not have a default namespace.' .
                                    ' Use setNamespace() to define your namespace.',
                                    0, PEAR_ERROR_TRIGGER);
            return;
        }
        
    } // end func _getNamespace
    
    /**
     * Parses a doctype declaration like "XHTML 1.0 Strict" to an array
     *
     * @param   string  $string     The string to be parsed
     * @return string
     * @access private
     */
    function _parseDoctypeString($string)
    {
        $split = explode(' ',strtolower($string));
        $elements = count($split);
        
        if (isset($split[2])){
            $array = array('type'=>$split[0],'version'=>$split[1],'variant'=>$split[2]);
        } elseif (isset($split[1])){
            $array = array('type'=>$split[0],'version'=>$split[1]);
        } else {
            $array = array('type'=>$split[0]);
        }
        
        return $array;
    } // end func _parseDoctypeString
    
    /**
     * Sets the content of the <body> tag
     * 
     * <p>If content already exists, the new content is appended.</p>
     *
     * <p>If you wish to overwrite whatever is in the body, use {@link setBody};
     * {@link unsetBody} completely empties the body without inserting new content.
     * It is possible to add objects, strings or an array of strings and/or objects.
     * Objects must have a toString method.</p>
     * 
     * @param mixed $content  New <body> tag content (may be passed as a reference)
     * @param int   $flag     Determines whether to prepend, append or replace the content
     * @access public
     */
    function addBodyContent($content, $flag = 0)
    {
        if ($flag == 2) {
            $this->setBody($content);
        } elseif ($flag == 1) {
            array_unshift($this->_body, $content);
        } else {
            $this->_body[] =& $content;
        }
    } // end addBodyContent    
    
    /**
     * Prepends content to the content of the <body> tag. Wrapper for {@link addBodyContent}
     * 
     * <p>If you wish to overwrite whatever is in the body, use {@link setBody};
     * {@link addBodyContent} provides full functionality including appending;
     * {@link unsetBody} completely empties the body without inserting new content.
     * It is possible to add objects, strings or an array of strings and/or objects
     * Objects must have a toString method.</p>
     *
     * @param mixed $content  New <body> tag content (may be passed as a reference)
     * @access public
     */
    function prependBodyContent($content)
    {
        $this->addBodyContent($content, 1);
    } // end func addScript

    /**
     * Adds a linked script to the page
     * 
     * @param    string  $url        URL to the linked script
     * @param    string  $type       Type of script. Defaults to 'text/javascript'
     * @access   public
     */
    function addScript($url, $type="text/javascript")
    {
        $this->_scripts[$url] = $type;
    } // end func addScript
    
    /**
     * Adds a script to the page
     *
     * <p>Content can be a string or an object with a toString method.
     * Defaults to text/javascript.</p>
     * 
     * @access   public
     * @param    mixed   $content   Script (may be passed as a reference)
     * @param    string  $type      Scripting mime (defaults to 'text/javascript')
     * @return   void
     */
    function addScriptDeclaration($content, $type = 'text/javascript')
    {
        $this->_script[strtolower($type)] =& $content;
    } // end func addScriptDeclaration
    
    /**
     * Adds a linked stylesheet to the page
     * 
     * @param    string  $url    URL to the linked style sheet
     * @param    string  $type   Mime encoding type
     * @param    string  $media  Media type that this stylesheet applies to
     * @access   public
     * @return   void
     */
    function addStyleSheet($url, $type = 'text/css', $media = null)
    {
        $this->_styleSheets[$url]['mime']  = $type;
        $this->_styleSheets[$url]['media'] = $media;
    } // end func addStyleSheet
    
    /**
     * Adds a stylesheet declaration to the page
     * 
     * <p>Content can be a string or an object with a toString method.
     * Defaults to text/css.</p>
     * 
     * @access   public
     * @param    mixed   $content   Style declarations (may be passed as a reference)
     * @param    string  $type      Type of stylesheet (defaults to 'text/css')
     * @return   void
     */
    function addStyleDeclaration($content, $type = 'text/css')
    {
        $this->_style[strtolower($type)] =& $content;
    } // end func addStyleDeclaration
    
    /**
     * Adds a shortcut icon (favicon)
     * 
     * <p>This adds a link to the icon shown in the favorites list or on 
     * the left of the url in the address bar. Some browsers display 
     * it on the tab, as well.</p>
     * 
     * @access    public
     * @param     string  $href        The link that is being related.
     * @param     string  $type        File type
     * @param     string  $relation    Relation of link
     * @return    void
     */
    function addFavicon($href, $type = 'image/x-icon', $relation = 'shortcut icon') {
        $this->_links[] = "<link href=\"$href\" rel=\"$relation\" type=\"$type\"";
    } // end func addFavicon

    /**
     * Adds <link> tags to the head of the document
     * 
     * <p>$relType defaults to 'rel' as it is the most common relation type used.
     * ('rev' refers to reverse relation, 'rel' indicates normal, forward relation.)
     * Typical tag: <link href="index.php" rel="Start"></p>
     * 
     * @access   public
     * @param    string  $href       The link that is being related.
     * @param    string  $relation   Relation of link.
     * @param    string  $relType    Relation type attribute.  Either rel or rev (default: 'rel').
     * @param    array   $attributes Associative array of remaining attributes.
     * @return   void
     */
    function addHeadLink($href, $relation, $relType = 'rel', $attributes = array()) {
        $attributes = $this->_parseAttributes($attributes);
        $generatedTag = $this->_getAttrString($attributes);
        $generatedTag = "<link href=\"$href\" $relType=\"$relation\"" . $generatedTag;
        $this->_links[] = $generatedTag;
    } // end func addHeadLink

    /**
     * Returns the current API version
     * 
     * @access   public
     * @return   double
     */
    function apiVersion()
    {
        return 2.0;
    } // end func apiVersion
    
    /**
     *  Disables prepending the XML prolog for XHTML documents
     * 
     * @access   public
     * @return  void
     */
    function disableXmlProlog()
    {
        $this->_xmlProlog = false;
    } // end func disableXmlProlog
    
    /**
     *  Enables prepending the XML prolog for XHTML documents (default)
     * 
     * @access   public
     * @return   void
     */
    function enableXmlProlog()
    {
        $this->_xmlProlog = true;
    } // end func enableXmlProlog
    
    /**
     * Returns the document charset encoding.
     * 
     * @access public
     * @return string
     */
    function getCharset()
    {
        return $this->_charset;
    } // end setCache
    
    /**
     * Returns the document type string
     *
     * @access public
     * @return string
     */
    function getDoctypeString()
    {
        $strDoctype = strtoupper($this->_doctype['type']);
        $strDoctype .= ' '.ucfirst(strtolower($this->_doctype['version']));
        if ($this->_doctype['variant']) {
            $strDoctype .= ' ' . ucfirst(strtolower($this->_doctype['variant']));
        }
        return trim($strDoctype);
    } // end func getDoctypeString
    
    /**
     * Returns the document language.
     * 
     * @return string
     * @access public
     */
    function getLang()
    {
        return $this->_language;
    } // end func getLang
    
    /**
     * Return the title of the page.
     * 
     * @return   string
     * @access   public
     */
    function getTitle()
    {
        if (!$this->_title){
            if ($this->_simple) {
                return 'New Page';
            } else {
                return 'New '. $this->getDoctypeString() . ' Compliant Page';
            }
        } else {
            return $this->_title;
        }
    } // end func getTitle
    
    /**
     * Sets the content of the <body> tag.
     * 
     * <p>If content exists, it is overwritten.
     * If you wish to use a "safe" version, use {@link addBodyContent}
     * Objects must have a toString method.</p>
     * 
     * @param mixed    $content   New <body> tag content. May be an object. 
     *                            (may be passed as a reference)
     * @access public
     */
    function setBody($content)
    {
        $this->unsetBody();
        $this->_body[] =& $content;
    } // end setBody
    
    /**
     * Unsets the content of the <body> tag.
     * 
     * @access public
     */
    function unsetBody()
    {
        $this->_body = array();
    } // end unsetBody
        
    /**
     * Sets the attributes of the <body> tag.
     * If attributes exist, they are overwritten.
     *
     * @param  array   $attributes   <body> tag attributes.
     * @access public
     */
    function setBodyAttributes($attributes)
    {
        $this->setAttributes($attributes);
    } // end setBodyAttributes

    /**
     * Defines if the document should be cached by the browser
     * 
     * <p>Defaults to false.</p>
     * 
     * @param  string   $cache  Options are currently 'true' or 'false'
     * @access public
     */
    function setCache($cache = 'false')
    {
        if ($cache == 'true'){
            $this->_cache = true;
        } else {
            $this->_cache = false;
        }
    } // end setCache
    
    /**
     * Sets the document charset
     * 
     * <p>By default, HTML_Page2 uses UTF-8 encoding. This is properly handled 
     * by PHP, but remember to use the htmlentities attribute for charset so 
     * that whatever you get from a database is properly handled by the 
     * browser.</p>
     * 
     * <p>The current most popular encoding: iso-8859-1. If it is used,
     * htmlentities and htmlspecialchars can be used without any special 
     * settings.</p>
     * 
     * @param   string   $type  Charset encoding string
     * @access  public
     * @return  void
     */
    function setCharset($type = 'utf-8')
    {
        $this->_charset = $type;
    } // end setCache
    
    /**
     * Sets or alters the !DOCTYPE declaration.
     * 
     * <p>Can be set to "strict", "transitional" or "frameset".
     * Defaults to "XHTML 1.0 Transitional".</p>
     * 
     * <p>This must come <i>after</i> declaring the character encoding with
     * {@link setCharset} or directly when the class is initiated 
     * {@link HTML_Page2}. Use in conjunction with {@link setMimeEncoding}</p>
     * 
     * <p>Framesets are not yet implemented.</p>
     * 
     * @param   string   $type  String containing a document type
     * @access  public
     * @return  void
     */
    function setDoctype($type = "XHTML 1.0 Transitional")
    {
        $this->_doctype = $this->_parseDoctypeString($type);
    } // end func setDoctype
    
    /**
     * Sets the global document language declaration. Default is English.
     * 
     * @access public
     * @param   string   $lang    Two-letter language designation
     */
    function setLang($lang = "en")
    {
        $this->_language = strtolower($lang);
    } // end setLang
    
    /**
     * Sets or alters a meta tag.
     * 
     * @param string  $name           Value of name or http-equiv tag
     * @param string  $content        Value of the content tag
     * @param bool    $http_equiv     META type "http-equiv" defaults to null
     * @return void
     * @access public
     */
    function setMetaData($name, $content, $http_equiv = false)
    {
        if ($content == '') {
            $this->unsetMetaData($name, $http_equiv);
        } else {
            if ($http_equiv == true) {
                $this->_metaTags['http-equiv'][$name] = $content;
            } else {
                $this->_metaTags['standard'][$name] = $content;
            }
        }
    } // end func setMetaData
    
    /**
     * Unsets a meta tag.
     *
     * @param string  $name           Value of name or http-equiv tag
     * @param bool    $http_equiv     META type "http-equiv" defaults to null
     * @return void
     * @access public
     */
    function unsetMetaData($name, $http_equiv = false)
    {
        if ($http_equiv == true) {
            unset($this->_metaTags['http-equiv'][$name]);
        } else {
            unset($this->_metaTags['standard'][$name]);
        }
    } // end func unsetMetaData

    /**
     * Sets an http-equiv Content-Type meta tag
     * 
     * @access   public
     * @return   void
     */
    function setMetaContentType()
    {
        $this->setMetaData('Content-Type', $this->_mime . '; charset=' . $this->_charset , true );
    } // end func setMetaContentType
    
    /**
     * Shortcut to set or alter a refresh meta tag 
     * 
     * <p>If no $url is passed, "self" is presupposed, and the appropriate URL
     * will be automatically generated. In this case, an optional third 
     * boolean parameter enables https redirects to self.</p>
     * 
     * @param int     $time    Time till refresh (in seconds)
     * @param string  $url     Absolute URL or "self"
     * @param bool    $https   If $url == self, this allows for the https 
     *                         protocol defaults to null
     * @return void
     * @access public
     */
    function setMetaRefresh($time, $url = 'self', $https = false)
    {
        if ($url == 'self') {
            if ($https) { 
                $protocol = 'https://';
            } else {
                $protocol = 'http://';
            }
            $url = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        }
        $this->setMetaData("Refresh", "$time; url=$url", true);
    } // end func setMetaRefresh
    
    /**
     * Sets the document MIME encoding that is sent to the browser.
     * 
     * <p>This usually will be text/html because most browsers cannot yet  
     * accept the preferred mime settings for XHTML: application/xhtml+xml 
     * and to a lesser extent text/xml. Here is a possible way of 
     * automatically including the proper mime type for XHTML 1.0 if the 
     * requesting browser supports it:</p>
     * 
     * <code>
     * <?php
     * // Initialize the HTML_Page2 object:
     * require 'HTML/Page2.php';
     * $page = new HTML_Page2();
     * 
     * // Check if browse can take the proper mime type
     * if ( strpos($_SERVER['HTTP_ACCEPT'], 'application/xhtml+xml') ) {
     *     $page->setDoctype('XHTML 1.0 Strict');
     *     $page->setMimeEncoding('application/xhtml+xml');
     * } else {
     *     // HTML that qualifies for XHTML 1.0 Strict automatically
     *     // also complies with XHTML 1.0 Transitional, so if the
     *     // requesting browser doesn't take the necessary mime type
     *     // for XHTML 1.0 Strict, let's give it what it can take.
     *     $page->setDoctype('XHTML 1.0 Transitional');
     * }
     * 
     * // finish building your page here..
     * 
     * $page->display();
     * ?>
     * </code>
     * 
     * @param    string    $type
     * @access   public
     * @return   void
     */
    function setMimeEncoding($type = 'text/html')
    {
        $this->_mime = strtolower($type);
    } // end func setMimeEncoding
    
    /**
     * Sets the document namespace
     * 
     * @param    string    $namespace  Optional. W3C namespaces are used by default.
     * @access   public
     * @return   void
     */
    function setNamespace($namespace = '')
    {
        if (isset($namespace)){
            $this->_namespace = $namespace;
        } else {
            $this->_namespace = $this->_getNamespace();
        }
    } // end func setTitle
    
    /**
     * Sets the title of the page
     * 
     * @param    string    $title
     * @access   public
     * @return   void
     */
    function setTitle($title)
    {
        $this->_title = $title;
    } // end func setTitle
    
    /**
     * Generates and returns the complete page as a string.
     * 
     * @return string
     * @access public
     */
    function toHTML()
    {
        
        // get line endings
        $lnEnd = $this->_getLineEnd();
        
        // get the doctype declaration
        $strDoctype = $this->_getDoctype();
        
        // This determines how the doctype is declared
        if ($this->_simple) {
            
            $strHtml = '<html>' . $lnEnd;
            
        } elseif ($this->_doctype['type'] == 'xhtml') {
            
            // get the namespace if not already set
            if (!$this->_namespace){
                $this->_namespace = $this->_getNamespace();
            }
            
            $strHtml = $strDoctype . $lnEnd;
            $strHtml .= '<html xmlns="' . $this->_namespace . '" xml:lang="' . $this->_language . '">' . $lnEnd;

            // check whether the XML prolog should be prepended
            if ($this->_xmlProlog){
                $strHtml  = '<?xml version="1.0" encoding="' . $this->_charset . '"?>' . $lnEnd . $strHtml;
            }
            
        } else {
            
            $strHtml  = $strDoctype . $lnEnd;
            $strHtml .= '<html>' . $lnEnd;
            
        }

        $strHtml .= $this->_generateHead();
        $strHtml .= $this->_generateBody();
        $strHtml .= '</html>';
        return $strHtml;
    } // end func toHtml
    
    /**
     * Generates the document and outputs it to a file.
     *
     * @return  void
     * @since   2.0
     * @access  public
     */
    function toFile($filename)
    {
        if (function_exists('file_put_content')){
            file_put_content($filename, $this->toHtml());
        } else {
            $file = fopen($filename,'wb');
            fwrite($file, $this->toHtml());
            fclose($file);
        }
        if (!file_exists($filename)){
            PEAR::raiseError("HTML_Page::toFile() error: Failed to write to $filename",0,PEAR_ERROR_TRIGGER);
        }
        
    } // end func toFile
    
    /**
     * Outputs the HTML content to the screen.
     * 
     * @access    public
     */
    function display()
    {
        if(! $this->_cache) {
            header("Expires: Tue, 1 Jan 1980 12:00:00 GMT");
            header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
            header("Cache-Control: no-cache");
            header("Pragma: no-cache");
        }
        
        // set character encoding
        header('Content-Type: ' . $this->_mime .  '; charset=' . $this->_charset);
        
        $strHtml = $this->toHTML();
        print $strHtml;
    } // end func display
    
}
?>
