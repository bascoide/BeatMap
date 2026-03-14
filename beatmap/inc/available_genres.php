<?php

if (!function_exists('getAvailableArtistGenres')) {
    function getAvailableArtistGenres(): array
    {
        return [
            'Rock', 'Pop', 'Hip-Hop', 'Rap', 'Trap', 'Drill', 'R&B', 'Soul', 'Funk',
            'Jazz', 'Blues', 'Gospel', 'Reggae', 'Dancehall', 'Ska',
            'Fado', 'Pimba', 'Folclore', 'Música Popular Portuguesa',
            'Eletrónica', 'House', 'Techno', 'Trance', 'EDM', 'Dubstep', 'Drum and Bass',
            'Ambient', 'Lo-fi', 'Synthwave',
            'Indie', 'Alternative', 'Grunge', 'Punk', 'Metal', 'Hard Rock', 'Progressive Rock',
            'Kizomba', 'Kuduro', 'Afrobeats', 'Semba', 'Morna', 'Funaná',
            'Samba', 'Bossa Nova', 'Forró', 'MPB', 'Sertanejo', 'Bachata', 'Salsa', 'Tango',
            'Flamenco', 'Classical', 'Instrumental', 'Experimental',
        ];
    }
}
