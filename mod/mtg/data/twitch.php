<?php
$nameList = [];
$nameList[] = 'magic';
// $nameList[] = 'magic2';

foreach ($nameList as $name) {
    $user = \Twitch\Manager::getUser($name);
    $videoList = $user->getVideoList();
    foreach ($videoList as $video) {
        $stream = $video->getStreamData();
        // my_dump($stream);
        $chapter = $video->getChapterData();
        // my_dump($chapter);
    }
}

$video = \Twitch\Manager::getVideo('v8645111');
my_dump($video->getChapterData());
$video = \Twitch\Manager::getVideo('c6183765');
my_dump($video->getChapterData());