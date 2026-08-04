<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { loadYouTubeIframeApi } from '@/composables/useYouTubeIframeApi'

const props = defineProps({
  video: { type: Object, default: null },
})

const emit = defineEmits(['ready', 'timeupdate', 'durationchange', 'ended', 'error'])

const htmlVideo = ref(null)
const youtubeTarget = ref(null)
const loading = ref(false)
const errorMessage = ref('')
const playerKey = ref(0)
let hlsInstance = null
let youtubePlayer = null
let youtubeProgressTimer = null
let initializationId = 0

const sourceType = computed(() => props.video?.source_type || 'hls')
const isYoutube = computed(() => sourceType.value === 'youtube')
const hlsUrl = computed(() => {
  if (!props.video) return null
  if (props.video.hls_url) return props.video.hls_url
  if (!props.video.hls_path) return null

  const suffix = props.video.hls_path.endsWith('.m3u8') ? '' : '/master.m3u8'

  return `/storage/${props.video.hls_path}${suffix}`
})

function playbackState(currentTime = 0, duration = 0) {
  return {
    sourceType: sourceType.value,
    currentTime: Number.isFinite(currentTime) ? currentTime : 0,
    duration: Number.isFinite(duration) ? duration : 0,
  }
}

function reportError(message, code = null) {
  loading.value = false
  errorMessage.value = message
  emit('error', { ...playbackState(), message, code })
}

function cleanup() {
  initializationId += 1
  playerKey.value += 1

  if (youtubeProgressTimer) {
    window.clearInterval(youtubeProgressTimer)
    youtubeProgressTimer = null
  }

  if (youtubePlayer?.destroy) youtubePlayer.destroy()
  youtubePlayer = null

  if (hlsInstance) hlsInstance.destroy()
  hlsInstance = null

  if (htmlVideo.value) {
    htmlVideo.value.pause()
    htmlVideo.value.removeAttribute('src')
    htmlVideo.value.load()
  }
}

async function initialize() {
  cleanup()
  errorMessage.value = ''

  if (!props.video) {
    loading.value = false
    return
  }

  loading.value = true
  const currentInitialization = initializationId
  await nextTick()

  if (isYoutube.value) {
    await initializeYoutube(currentInitialization)
  } else {
    await initializeHls(currentInitialization)
  }
}

async function initializeHls(currentInitialization) {
  const element = htmlVideo.value

  if (!element || !hlsUrl.value) {
    reportError('Sumber video HLS tidak tersedia.')
    return
  }

  try {
    const { default: Hls } = await import('hls.js')
    if (currentInitialization !== initializationId) return

    if (Hls.isSupported()) {
      const instance = new Hls()
      hlsInstance = instance
      instance.on(Hls.Events.MANIFEST_PARSED, () => {
        if (instance !== hlsInstance) return
        loading.value = false
        emit('ready', playbackState(element.currentTime, element.duration))
      })
      instance.on(Hls.Events.ERROR, (_event, data) => {
        if (!data.fatal || instance !== hlsInstance) return
        reportError('Video HLS gagal dimuat.', data.type || data.details)
      })
      instance.loadSource(hlsUrl.value)
      instance.attachMedia(element)
    } else if (element.canPlayType('application/vnd.apple.mpegurl')) {
      element.src = hlsUrl.value
    } else {
      reportError('Browser ini tidak mendukung pemutaran HLS.')
    }
  } catch {
    if (currentInitialization === initializationId) {
      reportError('Komponen pemutar HLS gagal dimuat.')
    }
  }
}

async function initializeYoutube(currentInitialization) {
  if (!props.video?.youtube_video_id || !youtubeTarget.value) {
    reportError('ID video YouTube tidak tersedia.')
    return
  }

  try {
    const YT = await loadYouTubeIframeApi()
    if (currentInitialization !== initializationId || !youtubeTarget.value) return

    const player = new YT.Player(youtubeTarget.value, {
      host: 'https://www.youtube-nocookie.com',
      videoId: props.video.youtube_video_id,
      playerVars: {
        autoplay: 0,
        controls: 1,
        enablejsapi: 1,
        playsinline: 1,
        rel: 0,
      },
      events: {
        onReady: event => {
          if (player !== youtubePlayer) return
          const iframe = event.target.getIframe()
          iframe.setAttribute('title', props.video?.title || 'YouTube video player')
          iframe.setAttribute('referrerpolicy', 'strict-origin-when-cross-origin')
          iframe.setAttribute('allow', 'encrypted-media; picture-in-picture; fullscreen')
          iframe.setAttribute('allowfullscreen', '')
          loading.value = false
          const state = playbackState(event.target.getCurrentTime(), event.target.getDuration())
          emit('durationchange', state)
          emit('ready', state)
          youtubeProgressTimer = window.setInterval(emitYoutubeProgress, 1000)
        },
        onStateChange: event => {
          if (player !== youtubePlayer || event.data !== YT.PlayerState.ENDED) return
          emit('ended', youtubePlaybackState())
        },
        onError: event => {
          if (player === youtubePlayer) reportError(youtubeErrorMessage(event.data), event.data)
        },
      },
    })

    youtubePlayer = player
  } catch (error) {
    if (currentInitialization === initializationId) {
      reportError(error?.message || 'YouTube Player gagal dimuat.')
    }
  }
}

function youtubePlaybackState() {
  if (!youtubePlayer?.getCurrentTime) return playbackState()

  return playbackState(youtubePlayer.getCurrentTime(), youtubePlayer.getDuration())
}

function emitYoutubeProgress() {
  if (!youtubePlayer?.getPlayerState) return

  const playerState = youtubePlayer.getPlayerState()
  if (playerState === window.YT.PlayerState.PLAYING || playerState === window.YT.PlayerState.PAUSED) {
    emit('timeupdate', youtubePlaybackState())
  }
}

function youtubeErrorMessage(code) {
  // YouTube also returns 101/150 for some removed or restricted videos,
  // so these codes cannot safely be described as embed-disabled only.
  if (code === 101 || code === 150) {
    return 'Video YouTube tidak tersedia untuk diputar di KITsuluh. Video mungkin telah dihapus, dibatasi, atau pemilik menonaktifkan embed.'
  }
  if (code === 100) return 'Video YouTube tidak tersedia atau telah dihapus.'

  return 'Video YouTube gagal diputar.'
}

function onHlsReady() {
  if (!htmlVideo.value || hlsInstance) return
  loading.value = false
  emit('ready', playbackState(htmlVideo.value.currentTime, htmlVideo.value.duration))
}

function onHlsTimeUpdate() {
  if (!htmlVideo.value) return
  emit('timeupdate', playbackState(htmlVideo.value.currentTime, htmlVideo.value.duration))
}

function onHlsDurationChange() {
  if (!htmlVideo.value) return
  emit('durationchange', playbackState(htmlVideo.value.currentTime, htmlVideo.value.duration))
}

function onHlsEnded() {
  if (!htmlVideo.value) return
  emit('ended', playbackState(htmlVideo.value.currentTime, htmlVideo.value.duration))
}

onMounted(initialize)

watch(() => [
  props.video?.id,
  props.video?.source_type,
  props.video?.hls_path,
  props.video?.hls_url,
  props.video?.youtube_video_id,
], initialize)

onBeforeUnmount(cleanup)
</script>

<template>
  <div class="video-player bg-black" :aria-busy="loading">
    <video
      v-if="video && !isYoutube"
      ref="htmlVideo"
      class="video-player__media"
      controls
      playsinline
      preload="metadata"
      @loadedmetadata="onHlsReady"
      @durationchange="onHlsDurationChange"
      @timeupdate="onHlsTimeUpdate"
      @ended="onHlsEnded"
      @error="reportError('Video HLS gagal diputar.')"
    />

    <div
      v-else-if="video && isYoutube"
      :key="playerKey"
      ref="youtubeTarget"
      class="video-player__media"
      title="YouTube video player"
    />

    <div v-else class="video-player__placeholder">
      <VIcon icon="mdi-play-circle-outline" size="64" color="grey-lighten-1" />
      <p class="mt-3 mb-0 text-grey-lighten-1">Tidak ada video yang siap diputar.</p>
    </div>

    <div v-if="loading && !errorMessage" class="video-player__overlay" role="status">
      <VProgressCircular indeterminate color="white" size="42" />
      <span class="mt-3 text-white">Memuat video…</span>
    </div>

    <div v-if="errorMessage" class="video-player__overlay pa-6 text-center" role="alert">
      <VIcon icon="mdi-alert-circle-outline" size="48" color="warning" />
      <p class="mt-3 mb-3 text-white">{{ errorMessage }}</p>
      <VBtn color="white" variant="outlined" size="small" @click="initialize">Coba Lagi</VBtn>
    </div>
  </div>
</template>

<style scoped>
.video-player {
  position: relative;
  width: 100%;
  aspect-ratio: 16 / 9;
  overflow: hidden;
}

.video-player__media {
  width: 100%;
  height: 100%;
  border: 0;
  object-fit: contain;
}

.video-player__placeholder,
.video-player__overlay {
  position: absolute;
  inset: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.video-player__overlay {
  background: rgb(0 0 0 / 72%);
  z-index: 2;
}
</style>
