import 'vite/modulepreload-polyfill'

// Let Vite process assets.
import.meta.glob([
  '../images/**',
  '../fonts/**',
])

// Import styles
import '@/styles/app.css'

// Run app
document.addEventListener("DOMContentLoaded", function () {
  document.querySelector('#btn').addEventListener('click', () => {

    console.log('hoihoi 11223222')
    console.log('hoihoi sddsddds')
  })
    // Your code to run since DOM is loaded and ready
})
