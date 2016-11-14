<?php

require 'util.php';

use PHPUnit\Framework\TestCase;


class MergeRSSTest extends TestCase {

    private function assertXmlMerged($xmls, $expected, $blah = null) {
        $merged = MergeRSS\merge_rss($xmls);
        $equal = xml_is_equal($merged, $expected);
        if ($equal !== true) {
            // debug help
            echo "\ngot:\n" . $merged->asXML() . "\nexpected:\n" . $expected->asXML() . "\n";
        }
        return $this->assertTrue($equal, $blah);
    }

    private function assertXmlStringMerged($a, $b, $blah = null) {
        $xmlify = function($s) {
            return simplexml_load_string(trim($s));
        };
        $xmls = array_map($xmlify, $a);
        $expected = $xmlify($b);
        $this->assertXmlMerged($xmls, $expected, $blah);
    }

    public function test_trivial_merges() {
        $this->assertXmlStringMerged(
            array('
                <?xml version="1.0"?>
                <rss version="2.0">
                    <channel></channel>
                </rss>
            '),
        '
            <?xml version="1.0"?>
            <rss version="2.0">
                <channel></channel>
            </rss>
        ', "one empty feed");

        $this->assertXmlStringMerged(
            array('
                <?xml version="1.0"?>
                <rss version="2.0">
                    <channel></channel>
                </rss>
            ', '
                <?xml version="1.0"?>
                <rss version="2.0">
                    <channel></channel>
                </rss>
            '),
        '
            <?xml version="1.0"?>
            <rss version="2.0">
                <channel></channel>
            </rss>
        ', "two empty feeds");
    }

    public function test_item_merges() {
        $this->assertXmlStringMerged(
            array('
                <?xml version="1.0"?>
                <rss version="2.0">
                    <channel>
                        <item>hello</item>
                        <item>hi</item>
                    </channel>
                </rss>
            '),
        '
            <?xml version="1.0"?>
            <rss version="2.0">
                <channel>
                    <item>hello</item>
                    <item>hi</item>
                </channel>
            </rss>
        ', "one feed passes through its item");

        $this->assertXmlStringMerged(
            array('
                <?xml version="1.0"?>
                <rss version="2.0">
                    <channel>
                        <item>hello</item>
                    </channel>
                </rss>
            ', '
                <?xml version="1.0"?>
                <rss version="2.0">
                    <channel>
                    </channel>
                </rss>
            '),
        '
            <?xml version="1.0"?>
            <rss version="2.0">
                <channel>
                    <item>hello</item>
                </channel>
            </rss>
        ', "a second empty feed does nothing");

        $this->assertXmlStringMerged(
            array('
                <?xml version="1.0"?>
                <rss version="2.0">
                    <channel>
                        <item>hello</item>
                    </channel>
                </rss>
            ', '
                <?xml version="1.0"?>
                <rss version="2.0">
                    <channel>
                        <item>hi</item>
                    </channel>
                </rss>
            '),
        '
            <?xml version="1.0"?>
            <rss version="2.0">
                <channel>
                    <item>hello</item>
                    <item>hi</item>
                </channel>
            </rss>
        ', "two feeds' items are merged");
    }

    public function test_item_sort() {
        $this->assertXmlStringMerged(
            array('
                <?xml version="1.0"?>
                <rss version="2.0">
                    <channel>
                        <item>
                            <pubDate>Tue, 05 Jan 2016 18:17:30 +0000</pubDate>
                        </item>
                    </channel>
                </rss>
            ', '
                <?xml version="1.0"?>
                <rss version="2.0">
                    <channel>
                        <item>
                            <pubDate>Mon, 01 Feb 2016 16:30:55 +0000</pubDate>
                        </item>
                    </channel>
                </rss>
            '),
        '
            <?xml version="1.0"?>
            <rss version="2.0">
                <channel>
                    <item>
                        <pubDate>Mon, 01 Feb 2016 16:30:55 +0000</pubDate>
                    </item>
                    <item>
                        <pubDate>Tue, 05 Jan 2016 18:17:30 +0000</pubDate>
                    </item>
                </channel>
            </rss>
        ', "shoudl sort by pubDate");
    }

    public function test_ns_merge() {
        $this->assertXmlStringMerged(
            array('
                <?xml version="1.0"?>
                <rss
                    version="2.0"
                    xmlns:atom="http://www.w3.org/2005/Atom"
                    xmlns:dc="http://purl.org/dc/elements/1.1/">
                    <channel>
                        <item>
                            <pubDate>Tue, 05 Jan 2016 18:17:30 +0000</pubDate>
                            <atom:blah>blah</atom:blah>
                            <dc:blah>blah</dc:blah>
                        </item>
                    </channel>
                </rss>
            ', '
                <?xml version="1.0"?>
                <rss
                    version="2.0"
                    xmlns:atom="http://www.w3.org/2005/Atom"
                    xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd">
                    <channel>
                        <item>
                            <pubDate>Mon, 01 Feb 2016 16:30:55 +0000</pubDate>
                            <atom:blah>blah</atom:blah>
                            <itunes:blah>blah</itunes:blah>
                        </item>
                    </channel>
                </rss>
            '),
        '
            <?xml version="1.0"?>
            <rss
                version="2.0"
                xmlns:atom="http://www.w3.org/2005/Atom"
                xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd"
                xmlns:dc="http://purl.org/dc/elements/1.1/">
                <channel>
                    <item>
                        <pubDate>Mon, 01 Feb 2016 16:30:55 +0000</pubDate>
                        <atom:blah>blah</atom:blah>
                        <itunes:blah>blah</itunes:blah>
                    </item>
                    <item>
                        <pubDate>Tue, 05 Jan 2016 18:17:30 +0000</pubDate>
                        <atom:blah>blah</atom:blah>
                        <dc:blah>blah</dc:blah>
                    </item>
                </channel>
            </rss>
        ', "should merge ns stuff");
    }

    public function test_alt_tagnames() {
        $this->assertXmlStringMerged(
            array('
                <?xml version="1.0"?>
                <rss version="2.0" xmlns:dc="http://purl.org/dc/elements/1.1/">
                    <channel>
                        <item>
                            <dc:date>Sat, 08 Oct 2016 06:45:54 +0000</dc:date>
                        </item>
                        <item>
                            <pubDate>Fri, 07 Oct 2016 06:45:54 +0000</pubDate>
                        </item>
                    </channel>
                </rss>
            ', '
                <?xml version="1.0"?>
                <feed xmlns="http://www.w3.org/2005/Atom">
                    <entry>
                        <published>2016-11-08T22:30:26+00:00</published>
                    </entry>
                </feed>
            '),
        '
            <?xml version="1.0"?>
            <rss version="2.0">
                <channel>
                    <entry xmlns="http://www.w3.org/2005/Atom">
                        <published>2016-11-08T22:30:26+00:00</published>
                    </entry>
                    <item xmlns:dc="http://purl.org/dc/elements/1.1/">
                        <dc:date>Sat, 08 Oct 2016 06:45:54 +0000</dc:date>
                    </item>
                    <item>
                        <pubDate>Fri, 07 Oct 2016 06:45:54 +0000</pubDate>
                    </item>
                </channel>
            </rss>
        ', "");
    }

    public function test_real_feeds() {
        $feeds = array(
            simplexml_load_file("tests/rss-samples/nytimes-blog.xml"),
            simplexml_load_file("tests/rss-samples/soundcloud.xml"),
            simplexml_load_file("tests/rss-samples/youtube-channel.xml"),
        );
        $expected = simplexml_load_file("tests/rss-samples/expected-merged.xml");
        $this->assertXmlMerged($feeds, $expected);
    }
}
