<?php

/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * @see Zend_Gdata_App_Extension
 */
require_once 'Zend/Gdata/App/Extension.php';

/**
 * Represents the atom:category element
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Gdata_App_Extension_Category extends Zend_Gdata_App_Extension
{

    protected $_rootElement = 'category';
    protected $_term = null;
    protected $_scheme = null;
    protected $_label = null;

    public function __construct($term = null, $scheme = null, $label =null)
    {
        parent::__construct();
        $this->_term = $term;
        $this->_scheme = $scheme;
        $this->_label = $label;
    }

    public function getDOM($doc = null)
    {
        $element = parent::getDOM($doc);
        if ($this->_term != null) {
            $element->setAttribute('term', $this->_term);
        }
        if ($this->_scheme != null) {
            $element->setAttribute('scheme', $this->_scheme);
        }
        if ($this->_label != null) {
            $element->setAttribute('label', $this->_label);
        }
        return $element;
    }

    protected function takeAttributeFromDOM($attribute)
    {
        switch ($attribute->localName) {
        case 'term':
            $this->_term = $attribute->nodeValue;
            break;
        case 'scheme':
            $this->_scheme = $attribute->nodeValue;
            break;
        case 'label':
            $this->_label = $attribute->nodeValue;
            break;
        default:
            parent::takeAttributeFromDOM($attribute);
        }
    }

    /**
     * @return Zend_Gdata_App_Extension_Term
     */
    public function getTerm()
    {
        return $this->_term;
    }

    /**
     * @param Zend_Gdata_App_Extension_Term $value
     * @return Zend_Gdata_App_Extension_Category Provides a fluent interface
     */
    public function setTerm($value)
    {
        $this->_term = $value;
        return $this;
    }

    /**
     * @return Zend_Gdata_App_Extension_Scheme
     */
    public function getScheme()
    {
        return $this->_scheme;
    }

    /**
     * @param Zend_Gdata_App_Extension_Scheme $value
     * @return Zend_Gdata_App_Extension_Category Provides a fluent interface
     */
    public function setScheme($value)
    {
        $this->_scheme = $value;
        return $this;
    }

    /**

    /**
     * @return Zend_Gdata_App_Extension_Label
     */
    public function getLabel()
    {
        return $this->_label;
    }

    /**
     * @param Zend_Gdata_App_Extension_Label $value
     * @return Zend_Gdata_App_Extension_Category Provides a fluent interface
     */
    public function setLabel($value)
    {
        $this->_label = $value;
        return $this;
    }

}
