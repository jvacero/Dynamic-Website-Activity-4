<?php
// music_list.php - sidebar widget: play/pause/stop/skip a small YouTube playlist
// using the YouTube IFrame Player API. The player is a hidden 1x1 iframe; only
// our custom pixel-styled buttons are visible.

$playlist = [
    ['id' => 'jfKfPfyJRdk', 'title' => 'lofi hip hop radio - beats to relax/study to'],
    ['id' => '5qap5aO4i9A', 'title' => 'lofi hip hop radio - beats to sleep/chill to'],
    ['id' => 'DWcJFNfaw9c', 'title' => 'Chillhop Radio - jazzy & lofi hip hop beats'],
];
?>
<div class="music-widget">
    <h3><span class="material-icons">music_note</span> Playlist</h3>

    <ul id="playlistItems">
        <?php foreach ($playlist as $index => $track): ?>
            <li data-video-id="<?= htmlspecialchars($track['id']) ?>" data-index="<?= $index ?>">
                <?= htmlspecialchars($track['title']) ?>
            </li>
        <?php endforeach; ?>
    </ul>

    <div class="music-controls">
        <button id="btnPlay" title="Play"><span class="material-icons">play_arrow</span></button>
        <button id="btnPause" title="Pause"><span class="material-icons">pause</span></button>
        <button id="btnStop" title="Stop"><span class="material-icons">stop</span></button>
        <button id="btnNext" title="Next Track"><span class="material-icons">skip_next</span></button>
    </div>

    <div id="yt-player-frame"></div>
</div>

<script src="https://www.youtube.com/iframe_api"></script>
<script>
let ytPlayer = null;
let currentTrackIndex = 0;
const playlistData = <?= json_encode($playlist) ?>;

// Required callback name the YouTube IFrame API invokes once loaded.
function onYouTubeIframeAPIReady() {
    ytPlayer = new YT.Player('yt-player-frame', {
        height: '1',
        width: '1',
        videoId: playlistData[0].id,
        playerVars: { autoplay: 0, controls: 0 }
    });
}

function playTrack(index) {
    if (!ytPlayer || !playlistData[index]) return;
    currentTrackIndex = index;
    ytPlayer.loadVideoById(playlistData[index].id);
    document.querySelectorAll('#playlistItems li').forEach(li => li.classList.remove('playing'));
    document.querySelector(`#playlistItems li[data-index="${index}"]`).classList.add('playing');
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('#playlistItems li').forEach(li => {
        li.addEventListener('click', () => playTrack(parseInt(li.dataset.index, 10)));
    });

    document.getElementById('btnPlay').addEventListener('click', () => {
        if (ytPlayer && ytPlayer.playVideo) ytPlayer.playVideo();
    });

    document.getElementById('btnPause').addEventListener('click', () => {
        if (ytPlayer && ytPlayer.pauseVideo) ytPlayer.pauseVideo();
    });

    document.getElementById('btnStop').addEventListener('click', () => {
        if (ytPlayer && ytPlayer.stopVideo) ytPlayer.stopVideo();
    });

    document.getElementById('btnNext').addEventListener('click', () => {
        const nextIndex = (currentTrackIndex + 1) % playlistData.length;
        playTrack(nextIndex);
        if (ytPlayer && ytPlayer.playVideo) ytPlayer.playVideo();
    });
});
</script>
