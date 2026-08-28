<?php

namespace App\Enums;

enum MediaType: string
{
    case Radio = 'radio';
    case Television = 'tv';
    case Podcast = 'podcast';
    case PodcastEpisode = 'podcast_episode';
}
