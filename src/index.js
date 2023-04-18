require('dotenv').config()
const browserSync = require('browser-sync').create()

browserSync.init({
  ui: false,

  files: [
    './src/wordpress/themes/**/*.html',
    './src/wordpress/themes/**/*.css',
    './src/wordpress/themes/**/*.php',
    './src/wordpress/themes/**/*.js'
  ],

  proxy: `${process.env.DEV_HOST || 'http://127.0.0.1'}:${process.env.DEV_WEB_PORT || '1337'}`
})
