User-agent: *
Allow: /
Disallow: /dashboard
Disallow: /admin
Disallow: /login
Disallow: /register

Sitemap: {{ url('/sitemap.xml') }}

# Allow search engines to crawl homepage and public content
User-agent: Googlebot
Allow: /
Disallow: /dashboard
Disallow: /admin

User-agent: Bingbot
Allow: /
Disallow: /dashboard
Disallow: /admin