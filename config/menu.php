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
            ['label' => 'Podcasts', 'icon' => 'podcast', 'children' => [
                ['label' => 'All podcasts'], ['label' => 'Add podcast'], ['label' => 'Episodes'], ['label' => 'RSS feeds'],
            ]],
        ]],
        ['label' => 'Discovery', 'items' => [
            ['label' => 'Geography', 'icon' => 'globe', 'children' => [
                ['label' => 'Countries'], ['label' => 'Regions'], ['label' => 'Cities'],
            ]],
            ['label' => 'Taxonomy', 'icon' => 'tag', 'children' => [
                ['label' => 'Categories'], ['label' => 'Genres'], ['label' => 'Languages'], ['label' => 'Tag cleanup'],
            ]],
            ['label' => 'Search', 'icon' => 'search', 'children' => [
                ['label' => 'Search index'], ['label' => 'Popular searches'], ['label' => 'No-result searches'],
            ]],
            ['label' => 'Editorial discovery', 'icon' => 'spark', 'children' => [
                ['label' => 'Trending'], ['label' => 'Recently added'], ['label' => 'Featured media'], ['label' => 'Popular destinations'],
            ]],
        ]],
        ['label' => 'Sources & playback', 'items' => [
            ['label' => 'Ingestion', 'icon' => 'import', 'children' => [
                ['label' => 'Source providers'], ['label' => 'Radio imports'], ['label' => 'TV imports'], ['label' => 'Podcast imports'], ['label' => 'Import history'],
            ]],
            ['label' => 'Stream operations', 'icon' => 'signal', 'children' => [
                ['label' => 'Stream health', 'route' => 'admin.stream-health', 'active' => 'admin.stream-health'], ['label' => 'Broken reports'], ['label' => 'Unverified streams'], ['label' => 'Offline streams'], ['label' => 'Health-check history'],
            ]],
            ['label' => 'Playback policy', 'icon' => 'play', 'children' => [
                ['label' => 'Stream formats'], ['label' => 'Geoblocking'], ['label' => 'Rights verification'], ['label' => 'Playback messages'],
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
