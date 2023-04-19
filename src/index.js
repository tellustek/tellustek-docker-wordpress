import got from 'got'
import dotenv from 'dotenv'
import browserSync from 'browser-sync'
import { spawn } from 'child_process'

dotenv.config()
let postStartProcess
const bsServer = browserSync.create()

let timer = setInterval(async () => {
  try {
    console.log('Check server status...')
    const res = await got.get('http://127.0.0.1:1337')
    console.log('complete')
    clearInterval(timer)
    browserSync.init({
      ui: false,

      watchOptions: {
        ignoreInitial: true,
        ignored: 'node_modules'
      },

      ignore: [
        '**/node_modules/**/*',
        './src/wordpress/themes/twentytwentyone/**/*',
        './src/wordpress/themes/twentytwentytwo/**/*',
        './src/wordpress/themes/twentytwentythree/**/*'
      ],

      files: [
        './src/wordpress/themes/**/*.html',
        './src/wordpress/themes/**/*.css',
        './src/wordpress/themes/**/*.php',
        './src/wordpress/themes/**/*.js'
      ],

      proxy: `${process.env.DEV_HOST || 'http://127.0.0.1'}:${process.env.DEV_WEB_PORT || '1337'}`
    })
  } catch (err) {
    console.log('check fail...')
  }
}, 5000)

process.on('SIGINT', () => {
  console.log('Browsersync關閉中...如需停止 docker container 請執行 npm run stop')
  process.exit(0);
})
