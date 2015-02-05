<?php
/**
 * @package Valiton\Payment\DatatransBundle\Utils
 * @author Anna Ostrovskaya <anna.ostrovskaya@valiton.com>
 * 11.09.14 10:21
 */

namespace Valiton\Payment\DatatransBundle\Utils;

use DOMDocument;

class ArrayToXml {

    /**
     * The main function for converting to an XML document.
     * Pass in a multi dimensional array and this recrusively loops through and builds up an XML document.
     *
     * @param array $data
     * @param string $rootNodeName - what you want the root node to be - defaultsto data.
     * @param SimpleXMLElement $xml - should only be used recursively
     * @return string XML
     */
    public static function toXml($data, $rootNodeName = 'data', $xml=null)
    {
        // turn off compatibility mode as simple xml throws a wobbly if you don't.
        if (ini_get('zend.ze1_compatibility_mode') == 1)
        {
            ini_set ('zend.ze1_compatibility_mode', 0);
        }

        if ($xml == null)
        {
            $xml = simplexml_load_string("<?xml version='1.0' encoding='utf-8'?><$rootNodeName />");
        }

        if(is_array($data) && isset($data['@attributes'])) {
            foreach($data['@attributes'] as $attKey => $attVal) {

                $xml->addAttribute($attKey, $attVal);
            }

            unset($data['@attributes']); //remove the key from the array once done.
        }


        // loop through the data passed in.
        foreach($data as $key => $value)
        {
            // no numeric keys in our xml please!
            if (is_numeric($key))
            {
                // make string key...
                $key = "unknownNode_". (string) $key;
            }

            // replace alpha numeric
            $key = preg_replace('/[^a-z]/i', '', $key);

            // if there is another array found recrusively call this function
            if (is_array($value))
            {
                $node = $xml->addChild($key);
                // recrusive call.
                ArrayToXML::toXml($value, $rootNodeName, $node);
            }
            else
            {
                // add single node.
                $value = htmlentities($value);
                $xml->addChild($key,$value);
            }

        }
        // pass back as string.
        return $xml->asXML();
    }

} 