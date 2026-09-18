<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { loadYouTubeIframeApi } from '@/composables/useYouTubeIframeApi'

const props = defineProps({
  video: { type: Object, default: null },
  initialWatchedUntil: { type: Number, default: 0,},
})

const emit = defineEmits([
  'ready',
  'timeupdate',
  'durationchange',
  'ended',
  'error',
])

const htmlVideo = ref(null)
const youtubeTarget = ref(null)
const loading = ref(false)
const errorMessage = ref('')
const playerKey = ref(0)

/*
 * Posisi terjauh yang sudah benar-benar dicapai
 * melalui playback normal.
 *
 * Nilai ini tidak berkurang ketika student mundur.
 */

const watchedUntil = ref(0)

const isSeeking = ref(false)
const seekAllowedUntil = ref(0)

const youtubeLastTime = ref(0)
const youtubeSeekGuard = ref(false)
const youtubeCorrectionTarget = ref(0)
const youtubeWasPlayingBeforeCorrection = ref(false)
const youtubeCorrectionAttempts = ref(0)

const youtubeResumeGraceUntil = ref(0)
const youtubeCorrectionInProgress = ref(false)

const YOUTUBE_CORRECTION_MAX_ATTEMPTS = 12
const YOUTUBE_CORRECTION_INTERVAL = 100
const YOUTUBE_RESUME_GRACE_MS = 1200

let youtubeCorrectionTimer = null
let hlsInstance = null
let youtubePlayer = null
let youtubeProgressTimer = null
let initializationId = 0

/*
 * Toleransi kecil untuk perbedaan floating point
 * dan interval polling.
 */
const SEEK_TOLERANCE = 0.75

const sourceType = computed(() => props.video?.source_type || 'hls')

const isYoutube = computed(() => sourceType.value === 'youtube')

const hlsUrl = computed(() => {
  if (!props.video) return null

  if (props.video.hls_url) {
    return props.video.hls_url
  }

  if (!props.video.hls_path) {
    return null
  }

  const suffix = props.video.hls_path.endsWith('.m3u8')
    ? ''
    : '/master.m3u8'

  return `/storage/${props.video.hls_path}${suffix}`
})

function playbackState(currentTime = 0, duration = 0) {
  return {
    sourceType: sourceType.value,
    currentTime: Number.isFinite(currentTime)
      ? currentTime
      : 0,
    duration: Number.isFinite(duration)
      ? duration
      : 0,
    watchedUntil: watchedUntil.value,
  }
}

function reportError(message, code = null) {
  loading.value = false
  errorMessage.value = message

  emit('error', {
    ...playbackState(),
    message,
    code,
  })
}

function updateWatchedProgress(currentTime, duration = 0) {
  if (!Number.isFinite(currentTime)) {
    return
  }

  if (currentTime > watchedUntil.value) {
    watchedUntil.value = currentTime
  }

  return playbackState(currentTime, duration)
}

function isForwardSeek(targetTime) {
  return targetTime > watchedUntil.value + SEEK_TOLERANCE
}

function clampHlsToWatchedPosition() {
  if (!htmlVideo.value) return

  const target = Math.max(0, watchedUntil.value)

  if (
    Math.abs(htmlVideo.value.currentTime - target) > 0.05
  ) {
    htmlVideo.value.currentTime = target
  }
}

function cleanup() {
  initializationId += 1
  playerKey.value += 1

  if (youtubeProgressTimer) {
    window.clearInterval(youtubeProgressTimer)
    youtubeProgressTimer = null
  }

  clearYoutubeCorrectionTimer()
  youtubeSeekGuard.value = false
  youtubeCorrectionAttempts.value = 0
  youtubeResumeGraceUntil.value = 0
  youtubeCorrectionInProgress.value = false

  if (youtubePlayer?.destroy) {
    youtubePlayer.destroy()
  }

  youtubePlayer = null

  if (hlsInstance) {
    hlsInstance.destroy()
  }

  hlsInstance = null

  isSeeking.value = false

  if (htmlVideo.value) {
    htmlVideo.value.pause()
    htmlVideo.value.removeAttribute('src')
    htmlVideo.value.load()
  }
}

function resetWatchedProgress() {
  watchedUntil.value = Math.max(
    0,
    Number(props.initialWatchedUntil) || 0,
  )
  seekAllowedUntil.value = watchedUntil.value
}

async function initialize() {
  cleanup()

  resetWatchedProgress()

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

/*
|--------------------------------------------------------------------------
| HLS
|--------------------------------------------------------------------------
*/

async function initializeHls(currentInitialization) {
  const element = htmlVideo.value

  if (!element || !hlsUrl.value) {
    reportError('Sumber video HLS tidak tersedia.')
    return
  }

  try {
    const { default: Hls } = await import('hls.js')

    if (currentInitialization !== initializationId) {
      return
    }

    if (Hls.isSupported()) {
      const instance = new Hls()

      hlsInstance = instance

      instance.on(Hls.Events.MANIFEST_PARSED, () => {
        if (instance !== hlsInstance) {
          return
        }

        loading.value = false

        emit(
          'ready',
          playbackState(
            element.currentTime,
            element.duration,
          ),
        )
      })

      instance.on(Hls.Events.ERROR, (_event, data) => {
        if (!data.fatal || instance !== hlsInstance) {
          return
        }

        reportError(
          'Video HLS gagal dimuat.',
          data.type || data.details,
        )
      })

      instance.loadSource(hlsUrl.value)
      instance.attachMedia(element)
    } else if (
      element.canPlayType('application/vnd.apple.mpegurl')
    ) {
      element.src = hlsUrl.value
    } else {
      reportError(
        'Browser ini tidak mendukung pemutaran HLS.',
      )
    }
  } catch {
    if (currentInitialization === initializationId) {
      reportError(
        'Komponen pemutar HLS gagal dimuat.',
      )
    }
  }
}

function onHlsReady() {
  if (!htmlVideo.value) {
    return
  }

  loading.value = false

  emit(
    'ready',
    playbackState(
      htmlVideo.value.currentTime,
      htmlVideo.value.duration,
    ),
  )
}

function onHlsSeeking() {
  if (!htmlVideo.value) {
    return
  }
  seekAllowedUntil.value = watchedUntil.value
  isSeeking.value = true
}

function onHlsSeeked() {
  if (!htmlVideo.value) {
    return
  }

  const currentTime = htmlVideo.value.currentTime
  const allowedUntil = seekAllowedUntil.value

  /*
   * Student mencoba melewati posisi yang
   * sudah benar-benar ditonton.
   */
  if (
    currentTime >
    allowedUntil + SEEK_TOLERANCE
  ) {
    htmlVideo.value.currentTime = allowedUntil

    /*
     * Jangan langsung menganggap posisi hasil seek
     * sebagai progress.
     */
    isSeeking.value = true

    /*
     * Browser/HLS biasanya akan menghasilkan
     * event timeupdate setelah currentTime diubah.
     *
     * Biarkan event tersebut diproses oleh guard
     * di onHlsTimeUpdate().
     */
    requestAnimationFrame(() => {
      if (!htmlVideo.value) {
        return
      }

      const correctedTime =
        htmlVideo.value.currentTime

      if (
        correctedTime >
        allowedUntil + SEEK_TOLERANCE
      ) {
        htmlVideo.value.currentTime =
          allowedUntil
      }

      isSeeking.value = false
    })

    return
  }

  /*
   * Seek mundur diperbolehkan.
   *
   * watchedUntil tetap tidak berubah.
   */
  isSeeking.value = false
}

function onHlsDurationChange() {
  if (!htmlVideo.value) {
    return
  }

  emit(
    'durationchange',
    playbackState(
      htmlVideo.value.currentTime,
      htmlVideo.value.duration,
    ),
  )
}

function onHlsEnded() {
  if (!htmlVideo.value) {
    return
  }

  const currentTime = htmlVideo.value.currentTime
  const duration = htmlVideo.value.duration

  updateWatchedProgress(
    currentTime,
    duration,
  )

  emit(
    'ended',
    playbackState(
      currentTime,
      duration,
    ),
  )
}

function onHlsTimeUpdate() {
  if (!htmlVideo.value) {
    return
  }

  const currentTime = htmlVideo.value.currentTime
  const duration = htmlVideo.value.duration

  /*
   * Saat operasi seek sedang berlangsung,
   * currentTime TIDAK BOLEH memperbarui watchedUntil.
   */
  if (isSeeking.value) {
    if (
      currentTime >
      seekAllowedUntil.value + SEEK_TOLERANCE
    ) {
      htmlVideo.value.currentTime =
        seekAllowedUntil.value
    }

    emit(
      'timeupdate',
      playbackState(
        htmlVideo.value.currentTime,
        duration,
      ),
    )

    return
  }

  /*
   * Safety guard setelah seek selesai.
   */
  if (
    currentTime >
    watchedUntil.value + SEEK_TOLERANCE
  ) {
    htmlVideo.value.currentTime =
      watchedUntil.value

    emit(
      'timeupdate',
      playbackState(
        htmlVideo.value.currentTime,
        duration,
      ),
    )

    return
  }

  /*
   * Playback normal.
   */
  updateWatchedProgress(
    currentTime,
    duration,
  )

  emit(
    'timeupdate',
    playbackState(
      currentTime,
      duration,
    ),
  )
}

/*
|--------------------------------------------------------------------------
| YouTube
|--------------------------------------------------------------------------
*/

async function initializeYoutube(currentInitialization) {
  if (
    !props.video?.youtube_video_id ||
    !youtubeTarget.value
  ) {
    reportError('ID video YouTube tidak tersedia.')
    return
  }

  try {
    const YT = await loadYouTubeIframeApi()

    if (
      currentInitialization !== initializationId ||
      !youtubeTarget.value
    ) {
      return
    }

    const player = new YT.Player(
      youtubeTarget.value,
      {
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
            if (player !== youtubePlayer) {
              return
            }

            const iframe = event.target.getIframe()

            iframe.classList.add(
              'video-player__youtube-iframe',
            )

            iframe.setAttribute(
              'title',
              props.video?.title ||
                'YouTube video player',
            )

            iframe.setAttribute(
              'referrerpolicy',
              'strict-origin-when-cross-origin',
            )

            iframe.setAttribute(
              'allow',
              'encrypted-media; picture-in-picture; fullscreen',
            )

            loading.value = false

            const initialPosition =
              Math.max(
                0,
                watchedUntil.value,
              )

            youtubeLastTime.value =
              initialPosition

            youtubeCorrectionTarget.value =
              initialPosition

            youtubeSeekGuard.value = false
            youtubeCorrectionAttempts.value = 0

            /*
            * Resume dari progress terakhir.
            */
            if (initialPosition > 0) {
              event.target.seekTo(
                initialPosition,
                true,
              )
            }

            const state = playbackState(
              initialPosition,
              event.target.getDuration(),
            )

            emit(
              'durationchange',
              state,
            )

            emit(
              'ready',
              state,
            )

            youtubeProgressTimer =
              window.setInterval(
                emitYoutubeProgress,
                250,
              )
          },

          onStateChange: event => {
            if (player !== youtubePlayer) {
              return
            }

            if (
              event.data === YT.PlayerState.ENDED
            ) {
              emit(
                'ended',
                youtubePlaybackState(),
              )
            }
          },

          onError: event => {
            if (player === youtubePlayer) {
              reportError(
                youtubeErrorMessage(event.data),
                event.data,
              )
            }
          },
        },
      },
    )

    youtubePlayer = player
  } catch (error) {
    if (currentInitialization === initializationId) {
      reportError(
        error?.message ||
          'YouTube Player gagal dimuat.',
      )
    }
  }
}

function youtubePlaybackState() {
  if (!youtubePlayer?.getCurrentTime) {
    return playbackState()
  }

  return playbackState(
    youtubePlayer.getCurrentTime(),
    youtubePlayer.getDuration(),
  )
}

function clearYoutubeCorrectionTimer() {
  if (youtubeCorrectionTimer) {
    window.clearTimeout(youtubeCorrectionTimer)
    youtubeCorrectionTimer = null
  }
}

function startYoutubeForwardSeekCorrection() {
  if (!youtubePlayer?.getCurrentTime) {
    return
  }

  if (
    youtubeSeekGuard.value ||
    youtubeCorrectionInProgress.value
  ) {
    return
  }

  const target = Math.max(
    0,
    Math.min(
      youtubeLastTime.value,
      watchedUntil.value,
    ),
  )

  youtubeCorrectionTarget.value = target
  youtubeCorrectionAttempts.value = 0

  youtubeSeekGuard.value = true
  youtubeCorrectionInProgress.value = true

  youtubeWasPlayingBeforeCorrection.value =
    youtubePlayer.getPlayerState() ===
    window.YT.PlayerState.PLAYING

  if (youtubeWasPlayingBeforeCorrection.value) {
    youtubePlayer.pauseVideo()
  }

  youtubePlayer.seekTo(
    target,
    true,
  )

  scheduleYoutubeCorrectionCheck()
}

function scheduleYoutubeCorrectionCheck() {
  clearYoutubeCorrectionTimer()

  youtubeCorrectionTimer =
    window.setTimeout(
      checkYoutubeCorrection,
      YOUTUBE_CORRECTION_INTERVAL,
    )
}

function checkYoutubeCorrection() {
  youtubeCorrectionTimer = null

  if (
    !youtubePlayer?.getCurrentTime
  ) {
    youtubeSeekGuard.value = false
    youtubeCorrectionInProgress.value = false
    return
  }

  const currentTime =
    youtubePlayer.getCurrentTime()

  const target =
    youtubeCorrectionTarget.value

  const correctionCompleted =
    Math.abs(currentTime - target) <=
    SEEK_TOLERANCE

  /*
   * Correction berhasil.
   */
  if (correctionCompleted) {
    youtubeCorrectionAttempts.value = 0

    /*
     * Posisi valid setelah correction.
     *
     * PENTING:
     * jangan mengambil currentTime sebagai sumber
     * kebenaran baru jika YouTube memberikan sedikit
     * perbedaan floating point.
     */
    youtubeLastTime.value = target

    /*
     * Correction selesai.
     */
    youtubeSeekGuard.value = false

    /*
     * Tandai bahwa kita baru saja selesai melakukan
     * programmatic seek.
     */
    youtubeCorrectionInProgress.value = false

    /*
     * Berikan waktu kepada YouTube untuk berpindah
     * dari hasil seek ke playback normal.
     *
     * Selama periode ini emitYoutubeProgress()
     * TIDAK boleh menganggap posisi sebagai
     * forward seek baru.
     */
    youtubeResumeGraceUntil.value =
      Date.now() +
      YOUTUBE_RESUME_GRACE_MS

    /*
     * Resume hanya jika sebelum correction memang
     * sedang playing.
     */
    if (
      youtubeWasPlayingBeforeCorrection.value &&
      youtubePlayer.getPlayerState() !==
        window.YT.PlayerState.PLAYING
    ) {
      youtubePlayer.playVideo()
    }

    emit(
      'timeupdate',
      playbackState(
        target,
        youtubePlayer.getDuration(),
      ),
    )

    return
  }

  /*
   * Belum sampai target.
   */
  youtubeCorrectionAttempts.value += 1

  /*
   * Jika terlalu banyak percobaan,
   * jangan pernah membiarkan posisi maju.
   */
  if (
    youtubeCorrectionAttempts.value >=
    YOUTUBE_CORRECTION_MAX_ATTEMPTS
  ) {
    youtubeCorrectionAttempts.value = 0

    youtubePlayer.seekTo(
      target,
      true,
    )

    scheduleYoutubeCorrectionCheck()

    return
  }

  /*
   * Paksa kembali ke target.
   */
  youtubePlayer.seekTo(
    target,
    true,
  )

  scheduleYoutubeCorrectionCheck()
}

function emitYoutubeProgress() {
  if (
    !youtubePlayer?.getPlayerState ||
    !youtubePlayer?.getCurrentTime
  ) {
    return
  }

  const playerState =
    youtubePlayer.getPlayerState()

  if (
    playerState !==
      window.YT.PlayerState.PLAYING &&
    playerState !==
      window.YT.PlayerState.PAUSED
  ) {
    return
  }

  const currentTime =
    youtubePlayer.getCurrentTime()

  const duration =
    youtubePlayer.getDuration()

  const inResumeGracePeriod =
    Date.now() < youtubeResumeGraceUntil.value

  /*
   * --------------------------------------------------------
   * CORRECTION FORWARD SEEK
   * --------------------------------------------------------
   */
  if (youtubeSeekGuard.value) {
    const target =
      youtubeCorrectionTarget.value

    /*
     * YouTube masih berada di posisi yang
     * terlalu jauh dari posisi valid.
     *
     * Paksa kembali.
     */
    if (
      currentTime >
      target + SEEK_TOLERANCE
    ) {
      youtubePlayer.seekTo(
        target,
        true,
      )

      return
    }

    /*
     * Posisi sudah kembali ke area yang valid.
     */
    youtubeLastTime.value = target

    emit(
      'timeupdate',
      playbackState(
        currentTime,
        duration,
      ),
    )

    return
  }

  /*
   * --------------------------------------------------------
   * BATAS PROGRESS YANG SUDAH VALID
   * --------------------------------------------------------
   */
  const allowedUntil =
    watchedUntil.value

  /*
  * --------------------------------------------------------
  * RESUME GRACE PERIOD
  * --------------------------------------------------------
  *
  * Setelah correction selesai dan playback dilanjutkan,
  * jangan langsung menganggap sedikit pergeseran posisi
  * sebagai forward seek baru.
  */
  if (inResumeGracePeriod) {
    /*
    * Selama grace period, posisi YouTube dianggap
    * sebagai kelanjutan playback dari correction.
    *
    * Jangan jalankan detector forward seek.
    */
    if (
      playerState ===
      window.YT.PlayerState.PLAYING
    ) {
      updateWatchedProgress(
        currentTime,
        duration,
      )
    }

    youtubeLastTime.value =
      Math.max(
        youtubeLastTime.value,
        currentTime,
      )

    emit(
      'timeupdate',
      playbackState(
        currentTime,
        duration,
      ),
    )

    return
  }

  /*
   * --------------------------------------------------------
   * DETEKSI FORWARD SEEK
   * --------------------------------------------------------
   */
  if (
    currentTime >
    allowedUntil + SEEK_TOLERANCE
  ) {
    startYoutubeForwardSeekCorrection()

    emit(
      'timeupdate',
      playbackState(
        youtubeCorrectionTarget.value,
        duration,
      ),
    )

    return
  }

  /*
   * --------------------------------------------------------
   * SEEK MUNDUR
   * --------------------------------------------------------
   */
  if (
    currentTime <
    allowedUntil - SEEK_TOLERANCE
  ) {

    emit(
      'timeupdate',
      playbackState(
        currentTime,
        duration,
      ),
    )

    return
  }

  /*
   * --------------------------------------------------------
   * PLAYBACK NORMAL
   * --------------------------------------------------------
   */
  if (
    playerState ===
    window.YT.PlayerState.PLAYING
  ) {
    updateWatchedProgress(
      currentTime,
      duration,
    )
  }

  youtubeLastTime.value =
    currentTime

  emit(
    'timeupdate',
    playbackState(
      currentTime,
      duration,
    ),
  )
}

function youtubeErrorMessage(code) {
  if (code === 101 || code === 150) {
    return 'Video YouTube tidak tersedia untuk diputar di KITsuluh. Video mungkin telah dihapus, dibatasi, atau pemilik menonaktifkan embed.'
  }

  if (code === 100) {
    return 'Video YouTube tidak tersedia atau telah dihapus.'
  }

  return 'Video YouTube gagal diputar.'
}

/*
|--------------------------------------------------------------------------
| Lifecycle
|--------------------------------------------------------------------------
*/

onMounted(initialize)

watch(
  () => [
    props.video?.id,
    props.video?.source_type,
    props.video?.hls_path,
    props.video?.hls_url,
    props.video?.youtube_video_id,
  ],
  (newValue, oldValue) => {
    const videoChanged =
      JSON.stringify(newValue) !==
      JSON.stringify(oldValue)

    if (videoChanged) {
      resetWatchedProgress()
    }

    initialize()
  },
)

onBeforeUnmount(cleanup)
</script>

<template>
  <div
    class="video-player bg-black"
    :aria-busy="loading"
  >
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
      @seeking="onHlsSeeking"
      @seeked="onHlsSeeked"
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

    <div
      v-else
      class="video-player__placeholder"
    >
      <VIcon
        icon="mdi-play-circle-outline"
        size="64"
        color="grey-lighten-1"
      />

      <p class="mt-3 mb-0 text-grey-lighten-1">
        Tidak ada video yang siap diputar.
      </p>
    </div>

    <div
      v-if="loading && !errorMessage"
      class="video-player__overlay"
      role="status"
    >
      <VProgressCircular
        indeterminate
        color="white"
        size="42"
      />

      <span class="mt-3 text-white">
        Memuat video…
      </span>
    </div>

    <div
      v-if="errorMessage"
      class="video-player__overlay pa-6 text-center"
      role="alert"
    >
      <VIcon
        icon="mdi-alert-circle-outline"
        size="48"
        color="warning"
      />

      <p class="mt-3 mb-3 text-white">
        {{ errorMessage }}
      </p>

      <VBtn
        color="white"
        variant="outlined"
        size="small"
        @click="initialize"
      >
        Coba Lagi
      </VBtn>
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

.video-player :deep(.video-player__youtube-iframe) {
  width: 100% !important;
  height: 100% !important;
  border: 0 !important;
  display: block !important;
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