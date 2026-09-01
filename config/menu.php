<?php

return [
    'admin' => [
        ['label' => 'Workspace', 'items' => [
            ['label' => 'Overview', 'route' => 'admin.dashboard', 'active' => 'admin.dashboard', 'icon' => 'overview'],
        ]],
        ['label' => 'Media catalogue', 'items' => [
            ['label' => 'Radio', 'icon' => 'radio', 'active' => 'admin.radio.*', 'children' => [
                ['label' => 'All stations', 'route' => 'admin.radio.index', 'active' => 'admin.radio.index'], ['label' => 'Add station', 'route' => 'admin.radio.create', 'active' => 'admin.radio.create'], ['label' => 'Duplicates', 'route' => 'admin.radio.duplicates', 'active' => 'admin.radio.duplicates'], ['label' => 'Data quality', 'route' => 'admin.radio.data-quality', 'active' => 'admin.radio.data-quality'],
            ]],
            ['label' => 'Television', 'icon' => 'tv', 'active' => 'admin.television.*', 'children' => [
                ['label' => 'All channels', 'route' => 'admin.television.index', 'active' => 'admin.television.index'], ['label' => 'Add channel', 'route' => 'admin.television.create', 'active' => 'admin.television.create'], ['label' => 'Duplicates', 'route' => 'admin.television.duplicates', 'active' => 'admin.television.duplicates'], ['label' => 'Data quality', 'route' => 'admin.television.data-quality', 'active' => 'admin.television.data-quality'],
            ]],
            ['label' => 'Podcasts', 'icon' => 'podcast', 'active' => 'admin.podcasts.*', 'children' => [
                ['label' => 'All podcasts', 'route' => 'admin.podcasts.index', 'active' => 'admin.podcasts.index'], ['label' => 'Add podcast', 'route' => 'admin.podcasts.create', 'active' => 'admin.podcasts.create'], ['label' => 'Episodes', 'route' => 'admin.podcasts.episodes', 'active' => 'admin.podcasts.episodes'], ['label' => 'RSS feeds', 'route' => 'admin.podcasts.feeds', 'active' => 'admin.podcasts.feeds'],
            ]],
        ]],
        ['label' => 'Discovery', 'items' => [
            ['label' => 'Geography', 'icon' => 'globe', 'active' => 'admin.geography.*', 'children' => [
                ['label' => 'Countries', 'route' => 'admin.geography.countries', 'active' => 'admin.geography.countries'], ['label' => 'Regions', 'route' => 'admin.geography.regions', 'active' => 'admin.geography.regions'], ['label' => 'Cities', 'route' => 'admin.geography.cities', 'active' => 'admin.geography.cities'],
            ]],
            ['label' => 'Taxonomy', 'icon' => 'tag', 'active' => 'admin.taxonomy.*', 'children' => [
                ['label' => 'Categories', 'route' => 'admin.taxonomy.categories', 'active' => 'admin.taxonomy.categories'], ['label' => 'Genres', 'route' => 'admin.taxonomy.genres', 'active' => 'admin.taxonomy.genres'], ['label' => 'Languages', 'route' => 'admin.taxonomy.languages', 'active' => 'admin.taxonomy.languages'], ['label' => 'Tag cleanup', 'route' => 'admin.taxonomy.tag-cleanup', 'active' => 'admin.taxonomy.tag-cleanup'],
            ]],
            ['label' => 'Search', 'icon' => 'search', 'active' => 'admin.search.*', 'children' => [
                ['label' => 'Search index', 'route' => 'admin.search.index', 'active' => 'admin.search.index'], ['label' => 'Popular searches', 'route' => 'admin.search.popular', 'active' => 'admin.search.popular'], ['label' => 'No-result searches', 'route' => 'admin.search.no-results', 'active' => 'admin.search.no-results'],
            ]],
            ['label' => 'Editorial discovery', 'icon' => 'spark', 'active' => 'admin.editorial.*', 'children' => [
                ['label' => 'Trending', 'route' => 'admin.editorial.trending', 'active' => 'admin.editorial.trending'], ['label' => 'Recently added', 'route' => 'admin.editorial.recently-added', 'active' => 'admin.editorial.recently-added'], ['label' => 'Featured media', 'route' => 'admin.editorial.featured', 'active' => 'admin.editorial.featured'], ['label' => 'Popular destinations', 'route' => 'admin.editorial.popular-destinations', 'active' => 'admin.editorial.popular-destinations'],
            ]],
        ]],
        ['label' => 'Sources & playback', 'items' => [
            ['label' => 'Ingestion', 'icon' => 'import', 'active' => 'admin.ingestion.*', 'children' => [
                ['label' => 'Source providers', 'route' => 'admin.ingestion.sources', 'active' => 'admin.ingestion.sources'], ['label' => 'Radio imports', 'route' => 'admin.ingestion.radio', 'active' => 'admin.ingestion.radio'], ['label' => 'TV imports', 'route' => 'admin.ingestion.television', 'active' => 'admin.ingestion.television'], ['label' => 'Podcast imports', 'route' => 'admin.ingestion.podcasts', 'active' => 'admin.ingestion.podcasts'], ['label' => 'Import history', 'route' => 'admin.ingestion.history', 'active' => 'admin.ingestion.history'],
            ]],
            ['label' => 'Stream operations', 'icon' => 'signal', 'active' => 'admin.stream*', 'children' => [
                ['label' => 'Stream health', 'route' => 'admin.stream-health', 'active' => 'admin.stream-health'], ['label' => 'Broken reports', 'route' => 'admin.streams.reports', 'active' => 'admin.streams.reports'], ['label' => 'Unverified streams', 'route' => 'admin.streams.unverified', 'active' => 'admin.streams.unverified'], ['label' => 'Offline streams', 'route' => 'admin.streams.offline', 'active' => 'admin.streams.offline'], ['label' => 'Health-check history', 'route' => 'admin.streams.history', 'active' => 'admin.streams.history'],
            ]],
            ['label' => 'Playback policy', 'icon' => 'play', 'active' => 'admin.playback.*', 'children' => [
                ['label' => 'Stream formats', 'route' => 'admin.playback.formats', 'active' => 'admin.playback.formats'], ['label' => 'Geoblocking', 'route' => 'admin.playback.geoblocking', 'active' => 'admin.playback.geoblocking'], ['label' => 'Rights verification', 'route' => 'admin.playback.rights', 'active' => 'admin.playback.rights'], ['label' => 'Playback messages', 'route' => 'admin.playback.messages', 'active' => 'admin.playback.messages'],
            ]],
        ]],
        ['label' => 'Audience', 'items' => [
            ['label' => 'Users', 'icon' => 'users', 'children' => [
                ['label' => 'All users'], ['label' => 'Administrators'], ['label' => 'Roles & permissions'], ['label' => 'Account activity'],
            ]],
            ['label' => 'Engagement', 'icon' => 'heart', 'children' => [
                ['label' => 'Favorites'], ['label' => 'Listening history'], ['label' => 'Watch history'], ['label' => 'Shared media'],
            ]],
            ['label' => 'Recommendations', 'icon' => 'recommend', 'children' => [
                ['label' => 'Recommendation rules'], ['label' => 'Personalization'], ['label' => 'AI discovery'],
            ]],
        ]],
        ['label' => 'Partners & growth', 'items' => [
            ['label' => 'Broadcasters', 'icon' => 'broadcast', 'children' => [
                ['label' => 'Organizations'], ['label' => 'Station managers'], ['label' => 'Ownership claims'], ['label' => 'Verification requests'],
            ]],
            ['label' => 'Analytics', 'icon' => 'analytics', 'children' => [
                ['label' => 'Audience overview'], ['label' => 'Radio performance'], ['label' => 'TV performance'], ['label' => 'Podcast performance'], ['label' => 'Country insights'],
            ]],
            ['label' => 'Monetization', 'icon' => 'money', 'children' => [
                ['label' => 'Plans'], ['label' => 'Subscriptions'], ['label' => 'Promotions'], ['label' => 'Revenue'],
            ]],
        ]],
        ['label' => 'Platform', 'items' => [
            ['label' => 'API & mobile', 'icon' => 'api', 'children' => [
                ['label' => 'API clients'], ['label' => 'Access tokens'], ['label' => 'Rate limits'], ['label' => 'API activity'], ['label' => 'Mobile releases'],
            ]],
            ['label' => 'SEO & discoverability', 'icon' => 'seo', 'children' => [
                ['label' => 'Search metadata'], ['label' => 'Sitemaps'], ['label' => 'Structured data'], ['label' => 'AI discovery files'], ['label' => 'Indexing health'],
            ]],
            ['label' => 'System', 'icon' => 'settings', 'children' => [
                ['label' => 'General settings'], ['label' => 'Queue monitor'], ['label' => 'Scheduler'], ['label' => 'Cache'], ['label' => 'Mail'], ['label' => 'Storage'], ['label' => 'System logs'],
            ]],
            ['label' => 'Audit & security', 'icon' => 'shield', 'children' => [
                ['label' => 'Audit log'], ['label' => 'Login activity'], ['label' => 'Security settings'], ['label' => 'Data exports'],
            ]],
        ]],
    ],
];
