# merge-rss-php

## Requirements

- php with SimpleXML enabled


## Installation

_coming soon!_


## Usage

```php
$feed1 = simplexml_load_string(
    '<?xml version="1.0">
    <rss version="2.0">
      <channel>
        <item>
          <title>An older post from one feed</title>
          <pubDate>2016-01-01</pubDate>
        </item>
      </channel>
    </rss>');

$feed2 = simplexml_load_string(
    '<?xml version="1.0">
    <rss version="2.0">
      <channel>
        <item>
          <title>A recent post from another</title>
          <pubDate>2016-11-12</pubDate>
        </item>
      </channel>
    </rss>');

$merged = MergeRSS\merge_rss(array($xml1, $xml2));
echo $merged->asXML();
```

should output an xml document that looks like this:

```xml
<?xml version="1.0"?>
<rss version="2.0">
  <channel>
    <item>
      <title>A recent post from another</title>
      <pubDate>2016-11-12</pubDate>
    </item>
    <item>
      <title>An older post from one feed</title>
      <pubDate>2016-01-01</pubDate>
    </item>
  </channel>
</rss>
```
