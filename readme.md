## ⚠️ Unmaintained ⚠️

I originally wrote this up for a client on Upwork, who disappeared and never paid. So I published it at some point, but it's really not something I have bandwidth for maintaining.

I do not use it, and no future changes or updates are planned. I'm archiving it, but leaving it up in case it saves someone else a couple hours of work. Read the source and don't expect the most robust code in the world :)


# merge-rss-php

## Requirements

- php with SimpleXML enabled


## Installation

```bash
$ composer require uniphil/merge-rss
```


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

$merged = MergeRSS\merge_rss(array($feed1, $feed2));
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
