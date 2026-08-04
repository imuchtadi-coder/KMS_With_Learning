let youtubeApiPromise

export function loadYouTubeIframeApi() {
  if (typeof window === 'undefined') {
    return Promise.reject(new Error('YouTube Player hanya tersedia di browser.'))
  }

  if (window.YT?.Player) return Promise.resolve(window.YT)
  if (youtubeApiPromise) return youtubeApiPromise

  youtubeApiPromise = new Promise((resolve, reject) => {
    const previousCallback = window.onYouTubeIframeAPIReady

    window.onYouTubeIframeAPIReady = () => {
      if (typeof previousCallback === 'function') previousCallback()
      resolve(window.YT)
    }

    let script = document.querySelector('script[data-kitsuluh-youtube-api]')

    if (!script) {
      script = document.createElement('script')
      script.src = 'https://www.youtube.com/iframe_api'
      script.async = true
      script.dataset.kitsuluhYoutubeApi = 'true'
      document.head.appendChild(script)
    }

    script.addEventListener('error', () => {
      youtubeApiPromise = undefined
      reject(new Error('YouTube Player API gagal dimuat.'))
    }, { once: true })
  })

  return youtubeApiPromise
}
