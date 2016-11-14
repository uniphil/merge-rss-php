<?php
// by jevon
// MIT licensed: http://www.jevon.org/wiki/Comparing_Two_SimpleXML_Documents

function _util_zip($a, $b) {
    $out = array();
    for ($i=0; $i<max(count($a), count($b)); $i++) {
        $out[] = array($a[$i], $b[$i]);
    }
    return $out;
}

function xml_is_equal(SimpleXMLElement $xml1, SimpleXMLElement $xml2, $text_strict = false) {
    // compare text content
    if ($text_strict) {
        if ("$xml1" != "$xml2") return "mismatched text content (strict)";
    } else {
        if (trim("$xml1") != trim("$xml2")) return "mismatched text content:\n\t'$xml1' and\n\t'$xml2'\n";
    }

    // check tag names
    if ($xml1->getName() !== $xml2->getName()) {
        return "mismatched tag names '" . $xml1->getName() . "' and '" . $xml2->getName() . "'\n";
    }

    // check all attributes
    $search1 = array();
    $search2 = array();
    foreach ($xml1->attributes() as $a => $b) {
        $search1[$a] = "$b";        // force string conversion
    }
    foreach ($xml2->attributes() as $a => $b) {
        $search2[$a] = "$b";
    }
    if ($search1 != $search2) return "mismatched attributes";

    // check all namespaces
    $ns1 = array();
    $ns2 = array();
    foreach ($xml1->getNamespaces(true) as $a => $b) {
        $ns1[$a] = "$b";
    }
    foreach ($xml2->getNamespaces(true) as $a => $b) {
        $ns2[$a] = "$b";
    }
    if ($ns1 != $ns2) return "mismatched namespaces";

    // get all namespace attributes
    foreach ($ns1 as $ns) {         // don't need to cycle over ns2, since its identical to ns1
        $search1 = array();
        $search2 = array();
        foreach ($xml1->attributes($ns) as $a => $b) {
            $search1[$a] = "$b";
        }
        foreach ($xml2->attributes($ns) as $a => $b) {
            $search2[$a] = "$b";
        }
        if ($search1 != $search2) return "mismatched ns:$ns attributes";
    }

    // check all children
    foreach(_util_zip($xml1->children(), $xml2->children()) as $i => $child_pairs) {
        $child1 = $child_pairs[0];
        $child2 = $child_pairs[1];
        if ($child1 === null) {
            $name = $child2->getName();
            return "xml1 is missing child #$i: $name";
        }
        if ($child2 === null) {
            $name = $child1->getName();
            return "xml2 is missing child #$i: $name";
        }
        if (($r = xml_is_equal($child1, $child2)) !== true) {
            $name1 = $child1->getName();
            $name2 = $child2->getName();
            return "child #$i, <$name1> and <$name2>:\n\t$r";
        }
    }

    // if we've got through all of THIS, then we can say that xml1 has the same attributes and children as xml2.
    return true;
}
