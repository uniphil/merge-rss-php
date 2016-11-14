<?php

namespace MergeRSS;

use \SimpleXMLElement;
use \SimplePie_Misc;


// try several alternative paths to the thing we want
// woo rss and all its variety of ways to mark up a document
function find_xml_from($searches, $xml) {
    foreach ($searches as $path) {
        $tmp = $xml;
        foreach ($path as $ns => $tag_name) {
            if (is_string($ns)) {
                $tmp = $tmp->children($ns, true)->{$tag_name};
            } else {
                $tmp = $tmp->{$tag_name};
            }
        }
        if (!empty($tmp)) {
            break;
        }
    }
    return $tmp;
}


function get_item_date($item) {
    $date = find_xml_from(array(
        array('pubDate'),
        array('published'),
        array('dc' => 'date'),
    ), $item);
    return SimplePie_Misc::parse_date($date);
}


function merge_rss($xmls) {
    // 1. set up a standard xml structure
    $out = new SimpleXMLElement('<?xml version="1.0"?>
        <rss version="2.0">
            <channel>
            </channel>
        </rss>');

    // 2. pull all feed items out into a big flat array
    $items = array();
    foreach ($xmls as $feed) {
        $things = find_xml_from(array(
            array("channel", "item"),
            array("entry"),
        ), $feed);

        // don't die on bad feeds missing <channel>
        // just skip the feed instead
        if (empty($things)) {
            continue;
        }

        foreach ($things as $item) {
            $items[] = $item;
        }
    }

    // 3. sort the items by date
    usort($items, function($a, $b) {
        return get_item_date($b) - get_item_date($a);
    });

    // 4. stick them in the output feed
    // SimpleXML doesn't do tree splicing, we need DOM for that
    $out_channel = $out->channel;
    $out_channel_dom = dom_import_simplexml($out_channel);
    foreach ($items as $item) {
        $to_add_dom = $out_channel_dom->ownerDocument->importNode(dom_import_simplexml($item), true);
        $out_channel_dom->appendChild($to_add_dom);
    }

    return $out;
}
