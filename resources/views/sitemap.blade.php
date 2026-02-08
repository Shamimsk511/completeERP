<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" 
        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"
        xmlns:news="http://www.google.com/schemas/sitemap-news/0.9">
    @foreach($pages as $page)
    <url>
        <loc>{{ $page['url'] }}</loc>
        <lastmod>{{ $page['lastmod'] }}</lastmod>
        <changefreq>{{ $page['changefreq'] }}</changefreq>
        <priority>{{ $page['priority'] }}</priority>
        @if($page['url'] === url('/'))
        <image:image>
            <image:loc>{{ url('/') }}/images/rahman-tiles-logo.jpg</image:loc>
            <image:title>Rahman Tiles and Sanitary Logo</image:title>
            <image:caption>Premier tiles and sanitary store in Shariatpur since 2002</image:caption>
        </image:image>
        <image:image>
            <image:loc>{{ url('/') }}/images/berger-paints-display.jpg</image:loc>
            <image:title>Berger Paints Collection</image:title>
            <image:caption>Premium Berger Paints available at Rahman Tiles</image:caption>
        </image:image>
        <image:image>
            <image:loc>{{ url('/') }}/images/tiles-showroom.jpg</image:loc>
            <image:title>Tiles Showroom</image:title>
            <image:caption>Wide variety of premium tiles at our Shariatpur showroom</image:caption>
        </image:image>
        @endif
    </url>
    @endforeach
</urlset>